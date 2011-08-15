<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

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
 * Check to see if a member deserves promotion, and handle it.
 *
 * @param  ?MEMBER	The member (NULL: current member).
 */
function ocf_member_handle_promotion($member_id=NULL)
{
	if (!addon_installed('points')) return;
	if (get_page_name()=='admin_import') return;

	if (is_null($member_id)) $member_id=get_member();

	require_code('ocf_members');
	if (ocf_is_ldap_member($member_id)) return;

	require_code('points');
	$total_points=total_points($member_id);
	$groups=$GLOBALS['OCF_DRIVER']->get_members_groups($member_id,false,true);
	$or_list='';
	foreach ($groups as $id)
	{
		if ($or_list!='') $or_list.=' OR ';
		$or_list.='id='.strval($id);
	}
	$promotions=$GLOBALS['FORUM_DB']->query('SELECT id,g_promotion_target FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_groups WHERE ('.$or_list.') AND g_promotion_target IS NOT NULL AND g_promotion_threshold<='.strval((integer)$total_points));
	$promotes_today=array();
	foreach ($promotions as $promotion)
	{
		$_p=$promotion['g_promotion_target'];
		if ((!array_key_exists($_p,$groups)) && (!array_key_exists($_p,$promotes_today))) // If we're not already in the
		{
			// If it is our primary
			if ($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id,'m_primary_group')==$promotion['id'])
			{
				$GLOBALS['FORUM_DB']->query_update('f_members',array('m_primary_group'=>$_p),array('id'=>$member_id),'',1);
			} else
			{
				$GLOBALS['FORUM_DB']->query_insert('f_group_members',array('gm_validated'=>1,'gm_member_id'=>$member_id,'gm_group_id'=>$_p),false,true);
				$GLOBALS['FORUM_DB']->query_delete('f_group_members',array('gm_member_id'=>$member_id,'gm_group_id'=>$promotion['id']),'',1); // It's a transition, so remove old membership
			}

			// Carefully update run-time cacheing
			global $USERS_GROUPS_CACHE;
			foreach (array(true,false) as $a)
			{
				foreach (array(true,false) as $b)
				{
					if (isset($USERS_GROUPS_CACHE[$member_id][$a][$b]))
					{
						$groups=$USERS_GROUPS_CACHE[$member_id][$a][$b];
						$pos=array_search($_p,$groups);
						if ($pos!==false)
							unset($groups[$pos]);
						$groups[]=$promotion['id'];
						$USERS_GROUPS_CACHE[$member_id][$a][$b]=$groups;
					}
				}
			}

			$promotes_today[$_p]=1;
		}
	}

	if (count($promotes_today)!=0)
	{
		$name=$GLOBALS['OCF_DRIVER']->get_member_row_field($member_id,'m_username');
		log_it('MEMBER_PROMOTED_AUTOMATICALLY',strval($member_id),$name);
	}
}

/**
 * Send out tracker information, as a topic just got a new post.
 *
 * @param  URLPATH		The URL to view the new post.
 * @param  AUTO_LINK		The ID of the topic that got posted in.
 * @param  ?AUTO_LINK	The forum that the topic is in (NULL: find out from the DB).
 * @param  MEMBER			The member that made the post triggering this tracking notification.
 * @param  boolean		Whether the post started a new topic.
 * @param  LONG_TEXT		The post, in Comcode format.
 * @param  SHORT_TEXT	The topic title.
 * @param  ?MEMBER		Only send the notification to this member (NULL: no such limit).
 * @param  boolean		Whether this is for a personal topic.
 */
function ocf_send_tracker_about($url,$topic_id,$forum_id,$sender_member_id,$is_starter,$post,$topic_title,$limit_to=NULL,$is_pt=false)
{
	if (running_script('stress_test_loader')) return;

	if ((is_null($forum_id)) && ($is_starter)) return;

	$their_username=$GLOBALS['OCF_DRIVER']->get_member_row_field($sender_member_id,'m_username');

	// Find a list of people who potential track this
	// ==============================================

	if (function_exists('set_time_limit')) @set_time_limit(0);

	$topic_info=$GLOBALS['FORUM_DB']->query_select('f_topics',array('t_pt_to','t_pt_from','t_cache_first_title'),array('id'=>$topic_id),'',1);
	if (!array_key_exists(0,$topic_info)) return; // Topic's gone missing somehow (e.g. race condition)
	$topic_title=$topic_info[0]['t_cache_first_title'];

	$start=0;
	do
	{
		$trackers=array();
		if ($start==0) // Only on first iteration as not ranged
		{
			if (!is_null($limit_to)) $trackers[$limit_to]=1;
			if ((is_null($forum_id)) && (get_value('ocf_optional_pt_tracking')!=='1'))
			{
				if ($topic_info[0]['t_pt_from']!=$sender_member_id) $trackers[$topic_info[0]['t_pt_from']]=1;
				if ($topic_info[0]['t_pt_to']!=$sender_member_id) $trackers[$topic_info[0]['t_pt_to']]=1;
			}
		}
		if ((!is_null($forum_id)) || (get_value('ocf_optional_pt_tracking')==='1'))
		{
			// Who tracks this topic? (all stored trackers definitely have permission to)
			$members1=$GLOBALS['FORUM_DB']->query_select('f_topic_tracking',array('r_member_id','r_last_message_time'),array('r_topic_id'=>$topic_id),'',100,$start);
			foreach ($members1 as $member)
			{
				$member_id=$member['r_member_id'];
				if ($member_id==$sender_member_id) continue;
	
				// We do not send out tracking if there have been tracking mails before the members last read and now
				if ($member['r_last_message_time']!=0)
					$last_time_read=$GLOBALS['FORUM_DB']->query_value_null_ok('f_read_logs','l_time',array('l_member_id'=>$member_id,'l_topic_id'=>$topic_id));
				if (($member['r_last_message_time']==0) || ($last_time_read>$member['r_last_message_time']) || (is_null($last_time_read)))
					$trackers[$member_id]=1;
			}
	
			// Who tracks this forum? (all stored trackers definitely have permission to)
			$members2=$GLOBALS['FORUM_DB']->query_select('f_forum_tracking',array('r_member_id'),array('r_forum_id'=>$forum_id),'',100,$start);
			foreach ($members2 as $member)
			{
				if ($member['r_member_id']==$sender_member_id) continue;

				if (has_specific_permission($member['r_member_id'],'may_track_forums'))
				{
					$trackers[$member['r_member_id']]=1;
				} else
				{
					$GLOBALS['FORUM_DB']->query_delete('f_forum_tracking',array('r_member_id'=>$member['r_member_id']));
				}
			}
		} else
		{
			$members1=array();
			$members2=array();
		}

		// Send out tracking mails
		// =======================

		$emails=array();
		$usernames=array();
		foreach (array_keys($trackers) as $tracking_member_id)
		{
			if ((!is_null($limit_to)) && ($limit_to!=$tracking_member_id)) continue;

			$lang=$GLOBALS['OCF_DRIVER']->get_member_row_field($tracking_member_id,'m_language');

			if (!array_key_exists($lang,$emails))
			{
				$emails[$lang]=array();
				$usernames[$lang]=array();
			}

			$emails[$lang][]=$GLOBALS['OCF_DRIVER']->get_member_row_field($tracking_member_id,'m_email_address');
			$usernames[$lang][]=$GLOBALS['OCF_DRIVER']->get_member_row_field($tracking_member_id,'m_username');
		}
		if (count($emails)!=0)
		{
			require_code('mail');
			foreach (array_keys($emails) as $lang)
			{
				$subject=do_lang($is_starter?'TOPIC_TRACKING_MAIL_SUBJECT':'TRACKING_MAIL_SUBJECT',get_site_name(),$topic_title,NULL,$lang);
				$mail=do_lang($is_starter?'TOPIC_TRACKING_MAIL':'TRACKING_MAIL',get_site_name(),comcode_escape($url),array(comcode_escape($their_username),$post,$topic_title),$lang);

				mail_wrap($subject,$mail,$emails[$lang],$usernames[$lang],'','',3,NULL,true,get_member());
			}
		}
		
		$start+=100;
	}
	while ((array_key_exists(0,$members1)) || (array_key_exists(0,$members2)));

	$GLOBALS['FORUM_DB']->query_update('f_topic_tracking',array('r_last_message_time'=>time()),array('r_topic_id'=>$topic_id));
}

/**
 * Update a topic's cacheing.
 *
 * @param  AUTO_LINK		The ID of the topic to update cacheing of.
 * @param  ?integer		The post count difference we know the topic has undergone (NULL: we'll need to work out from scratch how many posts are in the topic)
 * @param  boolean		Whether this is the latest post in the topic.
 * @param  boolean		Whether this is the first post in the topic.
 * @param  ?AUTO_LINK	The ID of the last post in the topic (NULL: unknown).
 * @param  ?TIME			The time of the last post in the topic (NULL: unknown).
 * @param  ?string		The title of the last post in the topic (NULL: unknown).
 * @param  ?AUTO_LINK	The ID of the last posts language string for the topic (NULL: unknown).
 * @param  ?string		The last username to post in the topic (NULL: unknown).
 * @param  ?MEMBER		The ID of the last member to post in the topic (NULL: unknown).
 */
function ocf_force_update_topic_cacheing($topic_id,$post_count_dif=NULL,$last=true,$first=false,$last_post_id=NULL,$last_time=NULL,$last_title=NULL,$last_post=NULL,$last_username=NULL,$last_member_id=NULL)
{
	$first_title='';
	if (is_null($last_post_id))
	{
		if ($first) // We're updating cacheing of the first
		{
			$posts=$GLOBALS['FORUM_DB']->query_select('f_posts',array('*'),array('p_topic_id'=>$topic_id),'ORDER BY p_time ASC,id ASC',1);
			if (!array_key_exists(0,$posts))
			{
				$first_post_id=NULL;
				$first_time=NULL;
				$first_post=NULL;
				$first_title='';
				$first_username='';
				$first_member_id=NULL;
			} else
			{
				$first_post_id=$posts[0]['id'];
				$first_post=$posts[0]['p_post'];
				$first_time=$posts[0]['p_time'];
				$first_title=$posts[0]['p_title'];
				$first_username=$posts[0]['p_poster_name_if_guest'];
				$first_member_id=$posts[0]['p_poster'];
			}
		}
		if ($last) // We're updating cacheing of the last
		{
			$posts=$GLOBALS['FORUM_DB']->query('SELECT * FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_posts WHERE p_intended_solely_for IS NULL AND p_topic_id='.strval((integer)$topic_id).' ORDER BY p_time DESC,id DESC',1);
			if (!array_key_exists(0,$posts))
			{
				$last_post_id=NULL;
				$last_time=NULL;
				$last_title='';
				$last_username='';
				$last_member_id=NULL;
			} else
			{
				$last_post_id=$posts[0]['id'];
				$last_time=$posts[0]['p_time'];
				$last_title=$posts[0]['p_title'];
				$last_username=$posts[0]['p_poster_name_if_guest'];
				$last_member_id=$posts[0]['p_poster'];
			}
		}
	} else
	{
		$first_post_id=$last_post_id;
		$first_time=$last_time;
		$first_post=$last_post;
		$first_title=$last_title;
		$first_username=$last_username;
		$first_member_id=$last_member_id;
	}

	if ($first_title=='') $first_title=do_lang('NO_TOPIC_TITLE',strval($topic_id));

	if ($first) $update_first=
		't_cache_first_post_id='.(is_null($first_post_id)?'NULL':strval((integer)$first_post_id)).',
		'.(($first_title=='')?'':('t_cache_first_title=\''.db_escape_string($first_title).'\'').',').'
		t_cache_first_time='.(is_null($first_time)?'NULL':strval((integer)$first_time)).',
		t_cache_first_post='.(is_null($first_post)?'NULL':strval((integer)$first_post)).',
		t_cache_first_username=\''.db_escape_string($first_username).'\',
		t_cache_first_member_id='.(is_null($first_member_id)?'NULL':strval((integer)$first_member_id)).',';

	if ($last) $update_last=
		't_cache_last_post_id='.(is_null($last_post_id)?'NULL':strval((integer)$last_post_id)).',
		t_cache_last_title=\''.db_escape_string($last_title).'\',
		t_cache_last_time='.(is_null($last_time)?'NULL':strval((integer)$last_time)).',
		t_cache_last_username=\''.db_escape_string($last_username).'\',
		t_cache_last_member_id='.(is_null($last_member_id)?'NULL':strval((integer)$last_member_id)).',';

	$GLOBALS['FORUM_DB']->query('UPDATE '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_topics SET '.
		($first?$update_first:'').
		($last?$update_last:'').
		((!is_null($post_count_dif)?
		('t_cache_num_posts=(t_cache_num_posts+'.strval($post_count_dif).')')
		:
		('t_cache_num_posts='.strval($GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_posts WHERE p_topic_id='.strval($topic_id).' AND p_intended_solely_for IS NULL'))))).
		' WHERE id='.strval($topic_id)
	);
}

/**
 * Update a forums cached details.
 *
 * @param  AUTO_LINK		The ID of the forum to update the cached details of.
 * @param  ?integer		How much to increment the topic count by (NULL: It has to be completely recalculated).
 * @param  ?integer		How much to increment the post count by (NULL: It has to be completely recalculated).
 * @param  ?AUTO_LINK	The ID of the last topic (NULL: Unknown, it will have to be looked up).
 * @param  ?string		The title of the last topic (NULL: Unknown, it will have to be looked up).
 * @param  ?TIME			The last post time of the last topic (NULL: Unknown, it will have to be looked up).
 * @param  ?string		The last post username of the last topic (NULL: Unknown, it will have to be looked up).
 * @param  ?MEMBER		The last post member of the last topic (NULL: Unknown, it will have to be looked up).
 * @param  ?AUTO_LINK	The forum the last post was in (note this makes sense, because there may be subforums under this forum that we have to take into account). (NULL: Unknown, it will have to be looked up).
 */
function ocf_force_update_forum_cacheing($forum_id,$num_topics_increment=NULL,$num_posts_increment=NULL,$last_topic_id=NULL,$last_title=NULL,$last_time=NULL,$last_username=NULL,$last_member_id=NULL,$last_forum_id=NULL)
{
	if ((is_null($num_topics_increment)) && (!is_null($num_posts_increment))) $num_topics_increment=0;
	if ((!is_null($num_topics_increment)) && (is_null($num_posts_increment))) $num_posts_increment=0;

	if (is_null($last_topic_id)) // We don't know what was last, so we'll have to work it out
	{
		require_code('ocf_forums');
		$or_list=ocf_get_all_subordinate_forums($forum_id,'t_forum_id',NULL,true);
		$last_topic=$GLOBALS['FORUM_DB']->query('SELECT * FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_topics WHERE ('.$or_list.') AND t_validated=1 ORDER BY t_cache_last_time DESC',1);
		if (!array_key_exists(0,$last_topic)) // No topics left apparently
		{
			$last_topic_id=NULL;
			$last_title='';
			$last_time=NULL;
			$last_username='';
			$last_member_id=NULL;
			$last_forum_id=NULL;
		} else
		{
			$last_topic_id=$last_topic[0]['id'];
			$last_title=$last_topic[0]['t_cache_first_title']; // Actually, the first title of the last topic
			$last_time=$last_topic[0]['t_cache_last_time'];
			$last_username=$last_topic[0]['t_cache_last_username'];
			$last_member_id=$last_topic[0]['t_cache_last_member_id'];
			$last_forum_id=$last_topic[0]['t_forum_id'];
		}
	} else
	{
		if (is_null($num_topics_increment)) $or_list=ocf_get_all_subordinate_forums($forum_id,'t_forum_id',NULL,true);
	}
	if (is_null($num_topics_increment)) // Apparently we're doing a recount
	{
		$_num_topics=$GLOBALS['FORUM_DB']->query('SELECT COUNT(*) AS topic_count FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_topics WHERE '.$or_list);
		$num_topics=$_num_topics[0]['topic_count'];
		$or_list_2=str_replace('t_forum_id','p_cache_forum_id',$or_list);
		$_num_posts=$GLOBALS['FORUM_DB']->query('SELECT COUNT(*) AS post_count FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_posts WHERE p_intended_solely_for IS NULL AND ('.$or_list_2.')');
		$num_posts=$_num_posts[0]['post_count'];
	}

	$GLOBALS['FORUM_DB']->query('UPDATE '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_forums SET '.
		(!is_null($num_posts_increment)?('
		f_cache_num_topics=(f_cache_num_topics+'.strval((integer)$num_topics_increment).'),
		f_cache_num_posts=(f_cache_num_posts+'.strval((integer)$num_posts_increment).'),')
		:
		('
		f_cache_num_topics='.strval((integer)$num_topics).',
		f_cache_num_posts='.strval((integer)$num_posts).',
		')).
		'f_cache_last_topic_id='.(!is_null($last_topic_id)?strval($last_topic_id):'NULL').',
		f_cache_last_title=\''.db_escape_string($last_title).'\',
		f_cache_last_time='.(!is_null($last_time)?strval($last_time):'NULL').',
		f_cache_last_username=\''.db_escape_string($last_username).'\',
		f_cache_last_member_id='.(!is_null($last_member_id)?strval($last_member_id):'NULL').',
		f_cache_last_forum_id='.(!is_null($last_forum_id)?strval($last_forum_id):'NULL').'
			WHERE id='.strval((integer)$forum_id),1);

	// Now, are there any parents who need updating?
	if (!is_null($forum_id))
	{
		$parent_forum=$GLOBALS['FORUM_DB']->query_value_null_ok('f_forums','f_parent_forum',array('id'=>$forum_id));
		if ((!is_null($parent_forum)) && ($parent_forum!=db_get_first_id()))
		{
			ocf_force_update_forum_cacheing($parent_forum,$num_topics_increment,$num_posts_increment);
		}
	}
}


