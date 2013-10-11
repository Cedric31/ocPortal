<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

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

class Hook_content_meta_aware_catalogue_category
{

	/**
	 * Standard modular info function for content hooks. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
	 *
	 * @param  ?ID_TEXT	The zone to link through to (NULL: autodetect).
	 * @return ?array		Map of award content-type info (NULL: disabled).
	 */
	function info($zone=NULL)
	{
		return array(
			'supports_custom_fields'=>true,

			'content_type_label'=>'catalogues:CATALOGUE_CATEGORY',

			'connection'=>$GLOBALS['SITE_DB'],
			'table'=>'catalogue_categories',
			'id_field'=>'id',
			'id_field_numeric'=>true,
			'parent_category_field'=>'cc_parent_id',
			'parent_category_meta_aware_type'=>'catalogue_category',
			'is_category'=>true,
			'is_entry'=>false,
			'category_field'=>array('c_name','id'), // For category permissions
			'category_type'=>array('catalogues_catalogue','cc_parent_id'), // For category permissions
			'parent_spec__table_name'=>'catalogue_categories',
			'parent_spec__parent_name'=>'cc_parent_id',
			'parent_spec__field_name'=>'id',
			'category_is_string'=>false,

			'title_field'=>'cc_title',
			'title_field_dereference'=>true,

			'view_pagelink_pattern'=>'_SEARCH:catalogues:category:_WILD',
			'edit_pagelink_pattern'=>'_SEARCH:cms_catalogues:_ec:_WILD',
			'view_category_pagelink_pattern'=>'_SEARCH:catalogues:category:_WILD',
			'add_url'=>(function_exists('has_submit_permission') && has_submit_permission('mid',get_member(),get_ip_address(),'cms_catalogues'))?(get_module_zone('cms_catalogues').':cms_catalogues:add_category:catalogue_name=!'):NULL,
			'archive_url'=>((!is_null($zone))?$zone:get_module_zone('catalogues')).':catalogues',

			'support_url_monikers'=>true,

			'views_field'=>NULL,
			'submitter_field'=>NULL,
			'add_time_field'=>'cc_add_date',
			'edit_time_field'=>NULL,
			'date_field'=>'cc_add_date',
			'validated_field'=>NULL,

			'seo_type_code'=>'catalogue_category',

			'feedback_type_code'=>NULL,

			'permissions_type_code'=>(get_value('disable_cat_cat_perms')==='1')?NULL:'catalogues_category', // NULL if has no permissions

			'search_hook'=>'catalogue_categories',

			'addon_name'=>'catalogues',

			'cms_page'=>'cms_catalogues',
			'module'=>'catalogues',

			'occle_filesystem_hook'=>'catalogues',
			'occle_filesystem__is_folder'=>true,

			'rss_hook'=>NULL,

			'actionlog_regexp'=>'\w+_CATALOGUE_CATEGORY',
		);
	}

	/**
	 * Standard modular run function for content hooks. Renders a content box for an award/randomisation.
	 *
	 * @param  array		The database row for the content
	 * @param  ID_TEXT	The zone to display in
	 * @param  boolean	Whether to include context (i.e. say WHAT this is, not just show the actual content)
	 * @param  boolean	Whether to include breadcrumbs (if there are any)
	 * @param  ?ID_TEXT	Virtual root to use (NULL: none)
	 * @param  boolean	Whether to copy through any filter parameters in the URL, under the basis that they are associated with what this box is browsing
	 * @param  ID_TEXT	Overridden GUID to send to templates (blank: none)
	 * @return tempcode	Results
	 */
	function run($row,$zone,$give_context=true,$include_breadcrumbs=true,$root=NULL,$attach_to_url_filter=false,$guid='')
	{
		require_code('catalogues');

		return render_catalogue_category_box($row,$zone,$give_context,$include_breadcrumbs,is_null($root)?NULL:intval($root),$attach_to_url_filter,$guid);
	}

}
