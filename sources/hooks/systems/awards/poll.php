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
 * @package		polls
 */

class Hook_awards_poll
{

	/**
	 * Standard modular info function for award hooks. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
	 *
	 * @return ?array	Map of award content-type info (NULL: disabled).
	 */
	function info()
	{
		$info=array();
		$info['connection']=$GLOBALS['SITE_DB'];
		$info['table']='poll';
		$info['date_field']='add_time';
		$info['id_field']='id';
		$info['add_url']=(has_submit_permission('mid',get_member(),get_ip_address(),'cms_polls'))?build_url(array('page'=>'cms_polls','type'=>'ad'),get_module_zone('cms_polls')):new ocp_tempcode();
		$info['category_field']=NULL;
		$info['category_type']=NULL;
		$info['parent_spec__table_name']=NULL;
		$info['parent_spec__parent_name']=NULL;
		$info['parent_spec__field_name']=NULL;
		$info['parent_field_name']=NULL;
		$info['submitter_field']='submitter';
		$info['id_is_string']=false;
		require_lang('polls');
		$info['title']=do_lang_tempcode('POLL');
		$info['validated_field']=NULL;
		$info['category_is_string']=false;
		$info['archive_url']=build_url(array('page'=>'polls'),get_module_zone('polls'));
		$info['cms_page']='cms_polls';
		$info['seo_type']='polls';
		$info['feedback_type']='polls';
		$info['views_field']='poll_views';

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
		require_code('polls');
		return poll_script(true,$row['id']);
	}

}


