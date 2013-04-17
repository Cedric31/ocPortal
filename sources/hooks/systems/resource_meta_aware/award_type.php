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
 * @package		awards
 */

class Hook_resource_meta_aware_award_type
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
			'supports_custom_fields'=>false,

			'content_type_label'=>'AWARDS',

			'connection'=>$GLOBALS['SITE_DB'],
			'table'=>'award_types',
			'id_field'=>'id',
			'id_field_numeric'=>true,
			'parent_category_field'=>NULL,
			'parent_category_meta_aware_type'=>NULL,
			'is_category'=>true,
			'is_entry'=>false,
			'category_field'=>NULL, // For category permissions
			'category_type'=>NULL, // For category permissions
			'parent_spec__table_name'=>NULL,
			'parent_spec__parent_name'=>NULL,
			'parent_spec__field_name'=>NULL,
			'category_is_string'=>true,

			'title_field'=>'a_title',
			'title_field_dereference'=>true,

			'view_pagelink_pattern'=>NULL,
			'edit_pagelink_pattern'=>'_SEARCH:admin_awards:ad:_WILD',
			'view_category_pagelink_pattern'=>NULL,
			'add_url'=>(function_exists('get_member') && has_actual_page_access(get_member(),'admin_awards'))?(get_module_zone('admin_awards').':admin_awards:ad'):NULL,
			'archive_url'=>NULL,

			'support_url_monikers'=>false,

			'views_field'=>NULL,
			'submitter_field'=>NULL,
			'add_time_field'=>NULL,
			'edit_time_field'=>NULL,
			'date_field'=>NULL,
			'validated_field'=>NULL,

			'seo_type_code'=>NULL,

			'feedback_type_code'=>NULL,

			'permissions_type_code'=>NULL, // NULL if has no permissions

			'search_hook'=>NULL,

			'addon_name'=>'awards',

			'cms_page'=>'admin_awards',
			'module'=>'awards',

			'occle_filesystem_hook'=>'award_types',
			'occle_filesystem__is_folder'=>false,

			'rss_hook'=>NULL,

			'actionlog_regexp'=>'\w+_AWARD_TYPE',
		);
	}

}
