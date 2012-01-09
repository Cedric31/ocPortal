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

class Hook_awards_group
{

	/**
	 * Standard modular info function for award hooks. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
	 *
	 * @return ?array	Map of award content-type info (NULL: disabled).
	 */
	function info()
	{
		if (get_forum_type()!='ocf') return NULL;

		$info=array();
		$info['connection']=$GLOBALS['FORUM_DB'];
		$info['table']='f_groups';
		$info['date_field']=NULL;
		$info['id_field']='id';
		$info['add_url']='';
		$info['category_field']=NULL;
		$info['submitter_field']='g_group_leader';
		$info['id_is_string']=false;
		require_lang('ocf');
		$info['title']=do_lang_tempcode('USERGROUPS');
		$info['category_is_string']=false;
		$info['archive_url']=build_url(array('page'=>'groups'),get_module_zone('groups'));
		$info['cms_page']='groups';
		$info['supports_custom_fields']=true;

		return $info;
	}

	/**
	 * Standard modular run function for award hooks. Renders a content box for an award/randomisation.
	 *
	 * @param  array		The database row for the content
	 * @param  ID_TEXT	The zone to display in
	 * @return tempcode	Results
	 */
	function run($row,$zone)
	{
		unset($zone);

		$url=build_url(array('page'=>'groups','type'=>'view','id'=>$row['id']),get_module_zone('groups'));

		return put_in_standard_box(do_template('SIMPLE_PREVIEW_BOX',array('SUMMARY'=>get_translated_text($row['g_name'],$GLOBALS['FORUM_DB']),'URL'=>$url)),ocf_get_group_name($row['id']));
	}

}


