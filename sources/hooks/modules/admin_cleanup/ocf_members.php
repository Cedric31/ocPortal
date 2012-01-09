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

class Hook_ocf_members
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		if (get_forum_type()!='ocf') return NULL; else ocf_require_all_forum_stuff();

		if (($GLOBALS['FORUM_DB']->query_value('f_members','COUNT(*)')>5000) && ($GLOBALS['FORUM_DB']->query_value('f_members','MAX(m_cache_num_posts)')>50)) // Too much work, unless we have due to an obvious issue
			return NULL;

		require_lang('ocf');
	
		$info=array();
		$info['title']=do_lang_tempcode('MEMBERS');
		$info['description']=do_lang_tempcode('DESCRIPTION_CACHE_MEMBERS');
		$info['type']='cache';

		return $info;
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	Results
	 */
	function run()
	{
		if (get_forum_type()!='ocf') return new ocp_tempcode(); else ocf_require_all_forum_stuff();
	
		if (function_exists('set_time_limit')) @set_time_limit(0);

		require_code('ocf_posts_action');
		require_code('ocf_posts_action2');
	
		// Members
		$start=0;
		do
		{
			$members=$GLOBALS['FORUM_DB']->query_select('f_members',array('id'),NULL,'',500,$start);
			foreach ($members as $member)
			{
				ocf_force_update_member_post_count($member['id']);
				$num_warnings=$GLOBALS['FORUM_DB']->query_value('f_warnings','COUNT(*)',array('w_member_id'=>$member['id']));
				$GLOBALS['FORUM_DB']->query_update('f_members',array('m_cache_warnings'=>$num_warnings),array('id'=>$member['id']),'',1);
			}
			$start+=500;
		}
		while (array_key_exists(0,$members));

		return new ocp_tempcode();
	}

}


