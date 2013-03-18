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
 * @package		banners
 */

class Hook_content_meta_aware_banner_type
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

			'content_type_label'=>'banners:_BANNER_TYPE',

			'connection'=>$GLOBALS['SITE_DB'],
			'table'=>'banner_types',
			'id_field'=>'id',
			'id_field_numeric'=>false,
			'parent_category_field'=>NULL,
			'parent_category_meta_aware_type'=>NULL,
			'is_category'=>true,
			'is_entry'=>false,
			'category_field'=>NULL, // For category permissions
			'category_type'=>NULL, // For category permissions
			'category_is_string'=>true,

			'title_field'=>'id',
			'title_field_dereference'=>false,

			'view_pagelink_pattern'=>NULL,
			'edit_pagelink_pattern'=>NULL,
			'view_category_pagelink_pattern'=>NULL,
			'add_url'=>(has_submit_permission('cat_high',get_member(),get_ip_address(),'cms_banners'))?(get_module_zone('cms_banners').':cms_banners:ac'):NULL,
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

			'addon_name'=>'banners',

			'cms_page'=>'cms_banners',
			'module'=>'banners',

			'occle_filesystem_hook'=>'banners',
			'occle_filesystem__is_folder'=>true,

			'rss_hook'=>NULL,

			'actionlog_regexp'=>'\w+_BANNER',
		);
	}

	/**
	 * Standard modular delete function for content hooks. Deletes the content.
	 *
	 * @param  ID_TEXT	The content ID
	 */
	function delete($content_id)
	{
		// TODO: This will be moved into OcCLE filesystem hook at some point, #218 on tracker
		require_code('banners2');
		delete_banner_type($content_id);
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
		require_code('banners');

		return render_banner_type_box($row,$zone,$give_context,$guid);
	}

}
