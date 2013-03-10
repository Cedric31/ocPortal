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

class Hook_content_meta_aware_catalogue
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

			'content_type_label'=>'CATALOGUES',

			'connection'=>$GLOBALS['SITE_DB'],
			'table'=>'catalogues',
			'id_field'=>'c_name',
			'id_field_numeric'=>false,
			'parent_category_field'=>NULL,
			'parent_category_meta_aware_type'=>NULL,
			'is_category'=>true,
			'is_entry'=>false,
			'category_field'=>'c_name', // For category permissions
			'category_type'=>'catalogues_catalogue', // For category permissions
			'category_is_string'=>true,

			'title_field'=>'c_title',
			'title_field_dereference'=>true,

			'view_pagelink_pattern'=>'_SEARCH:catalogues:index:_WILD',
			'edit_pagelink_pattern'=>'_SEARCH:cms_catalogues:_edit_catalogue:_WILD',
			'view_category_pagelink_pattern'=>NULL,
			'add_url'=>(has_submit_permission('mid',get_member(),get_ip_address(),'cms_catalogues'))?(get_module_zone('cms_catalogues').':cms_catalogues:add_entry:catalogue_name=!'):NULL,
			'archive_url'=>((!is_null($zone))?$zone:get_module_zone('catalogues')).':catalogues',

			'support_url_monikers'=>false,

			'views_field'=>NULL,
			'submitter_field'=>NULL,
			'add_time_field'=>'c_add_date',
			'edit_time_field'=>NULL,
			'date_field'=>'c_add_date',
			'validated_field'=>NULL,

			'seo_type_code'=>NULL,

			'feedback_type_code'=>NULL,

			'permissions_type_code'=>NULL, // NULL if has no permissions

			'search_hook'=>NULL,

			'addon_name'=>'catalogues',

			'cms_page'=>'cms_catalogues',
			'module'=>'catalogues',

			'occle_filesystem_hook'=>NULL, // TODO, #218 on tracker

			'rss_hook'=>NULL,

			'actionlog_regexp'=>'\w+_CATALOGUE',
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
		require_code('catalogues2');
		delete_catalogue($content_id);
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

		return render_catalogue_box($row,$zone,$give_context,$guid);
	}

}
