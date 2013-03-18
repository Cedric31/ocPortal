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
 * @package		wiki
 */

require_code('content_fs');

class Hook_occle_fs_wiki_page extends content_fs_base
{
	var $folder_content_type='wiki_page';
	var $file_content_type='wiki_post';

	/**
	 * Standard modular add function for content hooks. Adds some content with the given title and properties.
	 *
	 * @param  SHORT_TEXT	Content title
	 * @param  ID_TEXT		Parent category (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return ID_TEXT		The content ID
	 */
	function _folder_add($title,$category,$properties)
	{
		require_code('wiki');

		$description=$this->_default_property_str($properties,'description');
		$notes=$this->_default_property_str($properties,'notes');
		$hide_posts=$this->_default_property_int($properties,'hide_posts');
		$member=$this->_default_property_int_null($properties,'member');
		$add_time=$this->_default_property_int_null($properties,'add_time');
		$views=$this->_default_property_int($properties,'views');
		$id=wiki_add_page($title,$description,$notes,$hide_posts,$member,$add_time,$views);
		$the_order=$GLOBALS['SITE_DB']->query_value('wiki_children','MAX(the_order)',array('parent_id'=>$parent_id));

		if (is_null($the_order)) $the_order=-1;
		$the_order++;
		$GLOBALS['SITE_DB']->query_insert('wiki_children',array('parent_id'=>$parent_id,'child_id'=>$id,'the_order'=>$the_order,'title'=>$title));

		return strval($id);
	}

	/**
	 * Standard modular delete function for content hooks. Deletes the content.
	 *
	 * @param  ID_TEXT	The content ID
	 */
	function _folder_delete($content_id)
	{
		require_code('wiki');
		wiki_delete_page(intval($content_id));
	}

	/**
	 * Standard modular add function for content hooks. Adds some content with the given title and properties.
	 *
	 * @param  SHORT_TEXT	Content title
	 * @param  ID_TEXT		Parent category (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return ID_TEXT		The content ID
	 */
	function _file_add($title,$category,$properties)
	{
		require_code('wiki');

		$page_id=$this->_integer_category($category);
		$validated=$this->_default_property_int($properties,'validated');
		$member=$this->_default_property_int_null($properties,'member');
		$send_notification=$this->_default_property_int($properties,'send_notification');
		$add_time=$this->_default_property_int_null($properties,'add_time');
		$views=$this->_default_property_int($properties,'views');
		$id=wiki_add_post($page_id,$title,$validated,$member,$send_notification,$add_time,$views);
		return strval($id);
	}

	/**
	 * Standard modular delete function for content hooks. Deletes the content.
	 *
	 * @param  ID_TEXT	The content ID
	 */
	function _file_delete($content_id)
	{
		require_code('wiki');
		wiki_delete_post(intval($content_id));
	}
}
