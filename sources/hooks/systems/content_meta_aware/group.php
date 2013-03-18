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
 * @package		core_ocf
 */

class Hook_content_meta_aware_group
{

	/**
	 * Standard modular info function for content hooks. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
	 *
	 * @param  ?ID_TEXT	The zone to link through to (NULL: autodetect).
	 * @return ?array		Map of award content-type info (NULL: disabled).
	 */
	function info($zone=NULL)
	{
		if (get_forum_type()!='ocf') return NULL;

		return array(
			'supports_custom_fields'=>true,

			'content_type_label'=>'GROUP',

			'connection'=>$GLOBALS['FORUM_DB'],
			'table'=>'f_groups',
			'id_field'=>'id',
			'id_field_numeric'=>true,
			'parent_category_field'=>NULL,
			'parent_category_meta_aware_type'=>NULL,
			'is_category'=>false,
			'is_entry'=>true,
			'category_type'=>NULL, // For category permissions
			'category_field'=>NULL, // For category permissions
			'category_is_string'=>false,

			'title_field'=>'g_name',
			'title_field_dereference'=>true,

			'view_pagelink_pattern'=>'_SEARCH:groups:view:_WILD',
			'edit_pagelink_pattern'=>'adminzone:admin_ocf_groups:_ed:_WILD',
			'view_category_pagelink_pattern'=>NULL,
			'add_url'=>'',
			'archive_url'=>((!is_null($zone))?$zone:get_module_zone('groups')).':groups',

			'support_url_monikers'=>true,

			'views_field'=>NULL,
			'submitter_field'=>'g_group_leader',
			'add_time_field'=>NULL,
			'edit_time_field'=>NULL,
			'date_field'=>NULL,
			'validated_field'=>NULL,

			'seo_type_code'=>NULL,

			'feedback_type_code'=>NULL,

			'permissions_type_code'=>NULL, // NULL if has no permissions

			'search_hook'=>NULL,

			'addon_name'=>'core_ocf',

			'cms_page'=>'groups',
			'module'=>NULL,

			'occle_filesystem_hook'=>'groups',
			'occle_filesystem__is_folder'=>true,

			'rss_hook'=>NULL,

			'actionlog_regexp'=>'\w+_GROUP',
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
		ocf_groups_action2('TODO2');
		ocf_delete_group(intval($content_id));
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
		require_code('ocf_groups');

		return render_group_box($row,$zone,$give_context);
	}

}
