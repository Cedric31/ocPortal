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
 * Add a multi moderation to the system.
 *
 * @param  SHORT_TEXT	The name of the multi moderation.
 * @param  LONG_TEXT		The post text to add when applying (blank: don't add a post).
 * @param  ?AUTO_LINK	The forum to move the topic when applying (NULL: do not move).
 * @param  BINARY			The pin state after applying.
 * @param  BINARY			The sink state after applying.
 * @param  BINARY			The open state after applying.
 * @param  SHORT_TEXT	The forum multi code for where this multi moderation may be applied.
 * @param  SHORT_TEXT	The title suffix.
 * @return AUTO_LINK		The ID of the multi moderation just added.
 */
function ocf_make_multi_moderation($name,$post_text,$move_to,$pin_state,$sink_state,$open_state,$forum_multi_code='*',$title_suffix='')
{
	if ($move_to==-1) $move_to=NULL;
	if ($pin_state==-1) $pin_state=NULL;
	if ($open_state==-1) $open_state=NULL;
	if ($sink_state==-1) $sink_state=NULL;

	$id=$GLOBALS['FORUM_DB']->query_insert('f_multi_moderations',array(
		'mm_name'=>insert_lang($name,3,$GLOBALS['FORUM_DB']),
		'mm_post_text'=>$post_text,
		'mm_move_to'=>$move_to,
		'mm_pin_state'=>$pin_state,
		'mm_sink_state'=>$sink_state,
		'mm_open_state'=>$open_state,
		'mm_forum_multi_code'=>$forum_multi_code,
		'mm_title_suffix'=>$title_suffix,
	),true);

	log_it('ADD_MULTI_MODERATION',strval($id),$name);

	return $id;
}

