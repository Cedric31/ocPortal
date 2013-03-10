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
 * @package		core_comcode_pages
 */

class Hook_content_meta_aware_comcode_page
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

			'content_type_label'=>'zones:COMCODE_PAGE',

			'connection'=>$GLOBALS['SITE_DB'],
			'table'=>'comcode_pages',
			'id_field'=>'the_page',
			'id_field_numeric'=>false,
			'parent_category_field'=>'the_zone',
			'parent_category_meta_aware_type'=>NULL,
			'is_category'=>false,
			'is_entry'=>true,
			'category_field'=>array('the_zone','the_page'), // For category permissions
			'category_type'=>'!', // For category permissions
			'category_is_string'=>true,

			'title_field'=>NULL,
			'title_field_dereference'=>true,

			'view_pagelink_pattern'=>'_WILD:_WILD',
			'edit_pagelink_pattern'=>'_SEARCH:cms_comcode_pages:_ed:_WILD',
			'view_category_pagelink_pattern'=>'_WILD:',
			'add_url'=>(has_submit_permission('high',get_member(),get_ip_address(),'cms_comcode_pages'))?(get_module_zone('cms_comcode_pages').':cms_comcode_pages:ed'):NULL,
			'archive_url'=>((!is_null($zone))?$zone:get_page_zone('sitemap')).':sitemap',

			'support_url_monikers'=>false,

			'views_field'=>NULL,
			'submitter_field'=>'p_submitter',
			'add_time_field'=>'p_add_date',
			'edit_time_field'=>'p_edit_date',
			'date_field'=>'p_add_date',
			'validated_field'=>'p_validated',

			'seo_type_code'=>'comcode_page',

			'feedback_type_code'=>NULL,

			'permissions_type_code'=>NULL, // NULL if has no permissions

			'search_hook'=>'comcode_pages',

			'addon_name'=>'core_comcode_pages',

			'module'=>NULL,
			'cms_page'=>'cms_comcode_pages',

			'occle_filesystem_hook'=>NULL, // TODO, #218 on tracker

			'rss_hook'=>'comcode_pages',

			'actionlog_regexp'=>'\w+_COMCODE_PAGE',
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
		require_code('zones3');
		list($zone,$page)=explode(':',$content_id,2);
		delete_ocp_page($zone,$page);
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
		unset($zone); // Meaningless here

		require_code('zones2');

		return render_comcode_page_box($row,$give_context,$include_breadcrumbs,$root,$guid);
	}

}
