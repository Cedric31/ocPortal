<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_ocf
 */

/**
 * Edit a forum poll.
 *
 * @param  AUTO_LINK		The ID of the poll we're editing.
 * @param  SHORT_TEXT	The question.
 * @param  BINARY			Whether the result tallies are kept private until the poll is made non-private.
 * @param  BINARY			Whether the poll is open for voting.
 * @param  integer		The minimum number of selections that may be made.
 * @param  integer		The maximum number of selections that may be made.
 * @param  BINARY			Whether members must have a post in the topic before they made vote.
 * @param  array			A list of the potential voteable answers.
 * @param  LONG_TEXT		The reason for editing the poll.
 * @return AUTO_LINK		The ID of the topic the poll is on.
 */
function ocf_edit_poll($poll_id,$question,$is_private,$is_open,$minimum_selections,$maximum_selections,$requires_reply,$answers,$reason)
{
	require_code('ocf_polls');

	$topic_info=$GLOBALS['FORUM_DB']->query_select('f_topics',array('*'),array('t_poll_id'=>$poll_id),'',1);
	if (!ocf_may_edit_poll_by($topic_info[0]['t_forum_id'],$topic_info[0]['t_cache_first_member_id']))
		access_denied('I_ERROR');
	$topic_id=$topic_info[0]['id'];
	$poll_info=$GLOBALS['FORUM_DB']->query_select('f_polls',array('*'),array('id'=>$poll_id),'',1);

	if ((!has_specific_permission(get_member(),'may_unblind_own_poll')) && ($is_private==0) && ($poll_info[0]['po_is_private']==1))
		access_denied('PRIVILEGE','may_unblind_own_poll');

	$GLOBALS['FORUM_DB']->query_update('f_polls',array(
		'po_question'=>$question,
		'po_is_private'=>$is_private,
		'po_is_open'=>$is_open,
		'po_minimum_selections'=>$minimum_selections,
		'po_maximum_selections'=>$maximum_selections,
		'po_requires_reply'=>$requires_reply
	),array('id'=>$poll_id),'',1);

	$current_answers=$GLOBALS['FORUM_DB']->query_select('f_poll_answers',array('*'),array('pa_poll_id'=>$poll_id));
	$total_after=count($answers);
	foreach ($current_answers as $i=>$current_answer)
	{
		if ($i<$total_after)
		{
			$new_answer=$answers[$i];
			$GLOBALS['FORUM_DB']->query_update('f_poll_answers',array('pa_answer'=>$new_answer),array('id'=>$current_answer['id']),'',1);
		} else
		{
			$GLOBALS['FORUM_DB']->query_delete('f_poll_answers',array('id'=>$current_answer['id']),'',1);
			$GLOBALS['FORUM_DB']->query_delete('f_poll_votes',array('pv_answer_id'=>$current_answer['id']),'',1);
		}
	}
	$i++;
	for (;$i<$total_after;$i++)
	{
		$new_answer=$answers[$i];
		$GLOBALS['FORUM_DB']->query_insert('f_poll_answers',array(
			'pa_poll_id'=>$poll_id,
			'pa_answer'=>$new_answer,
			'pa_cache_num_votes'=>0
		));
	}

	$name=$GLOBALS['FORUM_DB']->query_value('f_polls','po_question',array('id'=>$poll_id));
	require_code('ocf_general_action2');
	ocf_mod_log_it('EDIT_TOPIC_POLL',strval($poll_id),$name,$reason);

	return $topic_id;
}

/**
 * Delete a forum poll.
 *
 * @param  AUTO_LINK The ID of the poll we're deleting.
 * @param  LONG_TEXT The reason for deleting the poll.
 * @return AUTO_LINK The ID of the topic the poll is on.
 */
function ocf_delete_poll($poll_id,$reason)
{
	require_code('ocf_polls');

	$topic_info=$GLOBALS['FORUM_DB']->query_select('f_topics',array('*'),array('t_poll_id'=>$poll_id),'',1);
	if (!ocf_may_delete_poll_by($topic_info[0]['t_forum_id'],$topic_info[0]['t_cache_first_member_id']))
		access_denied('I_ERROR');
	$topic_id=$topic_info[0]['id'];

	$name=$GLOBALS['FORUM_DB']->query_value('f_polls','po_question',array('id'=>$poll_id));

	$GLOBALS['FORUM_DB']->query_delete('f_polls',array('id'=>$poll_id),'',1);
	$GLOBALS['FORUM_DB']->query_delete('f_poll_answers',array('pa_poll_id'=>$poll_id));
	$GLOBALS['FORUM_DB']->query_delete('f_poll_votes',array('pv_poll_id'=>$poll_id));

	$GLOBALS['FORUM_DB']->query_update('f_topics',array('t_poll_id'=>NULL),array('t_poll_id'=>$poll_id),'',1);

	require_code('ocf_general_action2');
	ocf_mod_log_it('DELETE_TOPIC_POLL',strval($poll_id),$name,$reason);

	return $topic_id;
}

/**
 * Place a vote on a specified poll.
 *
 * @param  AUTO_LINK The ID of the poll we're voting in.
 * @param  array 		A list of poll answers that are being voted for.
 * @param  ?MEMBER	The member that's voting (NULL: current member).
 * @param  ?array		The row of the topic the poll is for (NULL: get it from the DB).
 */
function ocf_vote_in_poll($poll_id,$votes,$member_id=NULL,$topic_info=NULL)
{
	// Who's voting
	if (is_null($member_id)) $member_id=get_member();
	if ($member_id==$GLOBALS['OCF_DRIVER']->get_guest_id()) warn_exit(do_lang_tempcode('GUESTS_CANT_VOTE_IN_POLLS'));

	// Check they're allowed to vote
	if (!has_specific_permission($member_id,'vote_in_polls')) warn_exit(do_lang_tempcode('VOTE_DENIED'));
	if (is_null($topic_info)) $topic_info=$GLOBALS['FORUM_DB']->query_select('f_topics',array('id','t_forum_id'),array('t_poll_id'=>$poll_id),'',1);
	if (!array_key_exists(0,$topic_info)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$topic_id=$topic_info[0]['id'];
	$forum_id=$topic_info[0]['t_forum_id'];
	if ((!has_category_access($member_id,'forums',strval($forum_id))) && (!is_null($forum_id))) warn_exit(do_lang_tempcode('VOTE_CHEAT'));
	$test=$GLOBALS['FORUM_DB']->query_value_null_ok('f_poll_votes','pv_member_id',array('pv_poll_id'=>$poll_id,'pv_member_id'=>$member_id));
	if (!is_null($test)) warn_exit(do_lang_tempcode('NOVOTE'));

	// Check their vote is valid
	$rows=$GLOBALS['FORUM_DB']->query_select('f_polls',array('po_is_open','po_minimum_selections','po_maximum_selections','po_requires_reply'),array('id'=>$poll_id),'',1);
	if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	if ((count($votes)<$rows[0]['po_minimum_selections'])
		|| (count($votes)>$rows[0]['po_maximum_selections']) || ($rows[0]['po_is_open']==0)) warn_exit(do_lang_tempcode('VOTE_CHEAT'));
	$answers=collapse_1d_complexity('id',$GLOBALS['FORUM_DB']->query_select('f_poll_answers',array('id'),array('pa_poll_id'=>$poll_id)));
	if (($rows[0]['po_requires_reply']==1) && (!ocf_has_replied_topic($topic_id,$member_id)))
		warn_exit(do_lang_tempcode('POLL_REQUIRES_REPLY'));

	foreach ($votes as $vote)
	{
		if (!in_array($vote,$answers)) warn_exit(do_lang_tempcode('VOTE_CHEAT'));

		$GLOBALS['FORUM_DB']->query_insert('f_poll_votes',array(
			'pv_poll_id'=>$poll_id,
			'pv_member_id'=>$member_id,
			'pv_answer_id'=>$vote
		));

		$GLOBALS['FORUM_DB']->query('UPDATE '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_poll_answers SET pa_cache_num_votes=(pa_cache_num_votes+1) WHERE id='.strval((integer)$vote),1);
	}
	$GLOBALS['FORUM_DB']->query('UPDATE '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_polls SET po_cache_total_votes=(po_cache_total_votes+1) WHERE id='.strval((integer)$poll_id),1);
}


