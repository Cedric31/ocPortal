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
 * @package		catalogues
 */

class Hook_awards_catalogue_entry
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
		$info['table']='catalogue_entries';
		$info['date_field']='ce_add_date';
		$info['id_field']='id';
		$info['add_url']=(has_submit_permission('mid',get_member(),get_ip_address(),'cms_catalogues'))?build_url(array('page'=>'cms_catalogues','type'=>'add_entry'),get_module_zone('cms_catalogues')):new ocp_tempcode();
		$info['category_field']=array('c_name','cc_id');
		$info['category_type']=array('catalogues_catalogue','catalogues_category');
		$info['parent_spec__table_name']='catalogue_categories';
		$info['parent_spec__parent_name']='cc_parent_id';
		$info['parent_spec__field_name']='id';
		$info['parent_field_name']='cc_id';
		$info['submitter_field']='ce_submitter';
		$info['id_is_string']=false;
		require_lang('catalogues');
		$info['title']=do_lang_tempcode('CATALOGUE_ENTRIES');
		$info['validated_field']='ce_validated';
		$info['category_is_string']=array(true,false);
		$info['archive_url']=build_url(array('page'=>'catalogues'),get_module_zone('catalogues'));
		$info['cms_page']='cms_catalogues';
		$info['views_field']='ce_views';

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

		require_code('catalogues');
		$catalogue_name=$row['c_name'];
		$catalogues=$GLOBALS['SITE_DB']->query_select('catalogues',array('*'),array('c_name'=>$catalogue_name),'',1);
		$tpl_set=$catalogue_name;
		$display=get_catalogue_entry_map($row,$catalogues[0],'SEARCH',$tpl_set,-1,NULL,NULL,false,true);
		return do_template('CATALOGUE_'.$tpl_set.'_ENTRY_EMBED',$display,NULL,false,'CATALOGUE_DEFAULT_ENTRY_EMBED');//put_in_standard_box(hyperlink($url,do_lang_tempcode('VIEW')),do_lang_tempcode('CATALOGUE_ENTRY').' ('.do_lang_tempcode('IN',get_translated_text($catalogue['c_title'])).')');
	}

}


