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
 * @package		downloads
 */

class Hook_content_meta_aware_download
{

	/**
	 * Standard modular info function for content_meta_aware hooks. Allows progmattic identification of ocPortal entity model (along with db_meta table contents).
	 *
	 * @return ?array	Map of award content-type info (NULL: disabled).
	 */
	function info()
	{
		return array(
			'table'=>'download_downloads',
			'id_field'=>'id',
			'id_field_numeric'=>true,
			'parent_category_field'=>'category_id',
			'parent_category_meta_aware_type'=>'download_category',
			'title_field'=>'name',
			'title_field_dereference'=>true,

			'is_category'=>false,
			'is_entry'=>true,
			'seo_type_code'=>'downloads_download',
			'feedback_type_code'=>'downloads',
			'permissions_type_code'=>'downloads', // NULL if has no permissions
			'view_pagelink_pattern'=>'_SEARCH:downloads:entry:_WILD',
			'edit_pagelink_pattern'=>'_SEARCH:cms_downloads:_ed:_WILD',
			'view_category_pagelink_pattern'=>'_SEARCH:downloads:misc:_WILD',
			'support_url_monikers'=>true,
			'search_hook'=>'downloads',
			'views_field'=>'download_views',
			'submitter_field'=>'submitter',
			'add_time_field'=>'add_date',
			'edit_time_field'=>'edit_date',
			'validated_field'=>'validated',
		);
	}
	
}
