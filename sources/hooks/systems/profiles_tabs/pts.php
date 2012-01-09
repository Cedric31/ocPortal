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
 * @package		ocf_forum
 */

class Hook_Profiles_Tabs_pts
{

	/**
	 * Find whether this hook is active.
	 *
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 * @return boolean		Whether this hook is active
	 */
	function is_active($member_id_of,$member_id_viewing)
	{
		return (($member_id_of==$member_id_viewing) || (has_specific_permission($member_id_viewing,'assume_any_member')));
	}

	/**
	 * Standard modular render function for profile tab hooks.
	 *
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 * @return array			A triple: The tab title, the tab contents, the suggested tab order
	 */
	function render_tab($member_id_of,$member_id_viewing)
	{
		$title=do_lang_tempcode('PERSONAL_TOPICS');

		$order=80;

		require_code('ocf_forumview');

		$id=NULL;
		$current_filter_cat=get_param('category','');

		$root=get_param_integer('keep_forum_root',db_get_first_id());

		$max=get_param_integer('max',intval(get_option('forum_topics_per_page')));
		$start=get_param_integer('start',get_param_integer('kfs',0));

		$root=db_get_first_id();

		list($content,,,)=ocf_render_forumview($id,$current_filter_cat,$max,$start,$root,$member_id_of);

		return array($title,$content,$order);
	}

}


