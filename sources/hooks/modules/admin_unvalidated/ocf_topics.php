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
 * @package		ocf_forum
 */

class Hook_unvalidated_ocf_topics
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		if (get_forum_type()!='ocf') return NULL;
	
		require_lang('ocf');
	
		$info=array();
		$info['db_table']='f_topics';
		$info['db_identifier']='id';
		$info['db_validated']='t_validated';
		$info['db_title']='t_cache_first_title';
		$info['db_title_dereference']=false;
		$info['db_add_date']='t_cache_first_time';
		$info['db_edit_date']='t_cache_last_time';
		$info['edit_module']='topics';
		$info['edit_type']='edit_topic';
		$info['edit_identifier']='id';
		$info['title']=do_lang_tempcode('FORUM_TOPICS');
		$info['is_minor']=true;
		$info['db']=$GLOBALS['FORUM_DB'];

		return $info;
	}

}


