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
 * @package		core_ocf
 */

class Hook_content_meta_aware_member
{

	/**
	 * Standard modular info function for content_meta_aware hooks. Allows progmattic identification of ocPortal entity model (along with db_meta table contents).
	 *
	 * @return ?array	Map of award content-type info (NULL: disabled).
	 */
	function info()
	{
		return array(
			'table'=>'f_members',
			'id_field'=>'id',
			'id_field_numeric'=>true,
			'parent_category_field'=>NULL,
			'parent_category_meta_aware_type'=>NULL,
			'title_field'=>'m_username',
			'title_field_dereference'=>false,

			'is_category'=>false,
			'is_entry'=>true,
			'seo_type_code'=>NULL,
			'feedback_type_code'=>NULL,
			'permissions_type_code'=>NULL, // NULL if has no permissions
			'view_pagelink_pattern'=>'_SEARCH:members:view:_WILD',
			'edit_pagelink_pattern'=>'_SEARCH:editprofile:misc:_WILD',
			'view_category_pagelink_pattern'=>NULL,
			'support_url_monikers'=>(get_value('username_profile_links')!=='1'),
			'search_hook'=>'ocf_members',
			'submitter_field'=>'id',
			'add_time_field'=>'m_join_time',
			'edit_time_field'=>NULL,
			'validated_field'=>'m_validated',
		);
	}
	
}
