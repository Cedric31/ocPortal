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

class Hook_awards_catalogue
{

	/**
	 * Standard modular info function for award hooks. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
	 *
	 * @param  ?ID_TEXT	The zone to link through to (NULL: autodetect).
	 * @return ?array		Map of award content-type info (NULL: disabled).
	 */
	function info($zone=NULL)
	{
		$info=array();
		$info['connection']=$GLOBALS['SITE_DB'];
		$info['table']='catalogues';
		$info['date_field']='c_add_date';
		$info['id_field']='c_name';
		$info['add_url']=(has_submit_permission('mid',get_member(),get_ip_address(),'cms_catalogues'))?build_url(array('page'=>'cms_catalogues','type'=>'add_entry','catalogue_name'=>'!'),get_module_zone('cms_catalogues')):new ocp_tempcode();
		$info['category_field']='c_name';
		$info['category_type']='catalogues_catalogue';
		$info['id_is_string']=true;
		require_lang('catalogues');
		$info['title']=do_lang_tempcode('CATALOGUES');
		$info['category_is_string']=true;
		$info['archive_url']=build_url(array('page'=>'catalogues'),(!is_null($zone))?$zone:get_module_zone('catalogues'));
		$info['cms_page']='cms_catalogues';

		return $info;
	}

	/**
	 * Standard modular run function for award hooks. Renders a content box for an award/randomisation.
	 *
	 * @param  array		The database row for the content
	 * @param  ID_TEXT	The zone to display in
	 * @param  boolean	Whether to include context (i.e. say WHAT this is, not just show the actual content)
	 * @param  boolean	Whether to include breadcrumbs (if there are any)
	 * @param  ?ID_TEXT	Virtual root to use (NULL: none)
	 * @return tempcode	Results
	 */
	function run($row,$zone,$give_context=true,$include_breadcrumbs=true,$root=NULL)
	{
		require_code('catalogues');

		return render_catalogue_box($row,$zone,$give_context,$include_breadcrumbs);
	}

}


