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
 * @package		catalogues
 */

class Hook_awards_catalogue_category
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
		$info['table']='catalogue_categories';
		$info['date_field']='cc_add_date';
		$info['id_field']='id';
		$info['add_url']=(has_submit_permission('mid',get_member(),get_ip_address(),'cms_catalogues'))?build_url(array('page'=>'cms_catalogues','type'=>'add_category','parent_id'=>'!'),get_module_zone('cms_catalogues')):new ocp_tempcode();
		$info['category_field']=array('c_name','id');
		$info['category_type']=array('catalogues_catalogue','cc_parent_id');
		$info['parent_spec__table_name']='catalogue_categories';
		$info['parent_spec__parent_name']='cc_parent_id';
		$info['parent_spec__field_name']='id';
		$info['parent_field_name']='cc_parent_id';
		$info['id_is_string']=false;
		require_lang('catalogues');
		$info['title']=do_lang_tempcode('CATALOGUE_CATEGORY');
		$info['category_is_string']=false;
		$info['archive_url']=build_url(array('page'=>'catalogues'),get_module_zone('catalogues'));
		$info['cms_page']='cms_catalogues';
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
		require_code('catalogues');
		return get_catalogue_category_html($row,$zone);
	}

}


