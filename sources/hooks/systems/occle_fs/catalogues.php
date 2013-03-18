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

require_code('content_fs');

class Hook_occle_fs_catalogues extends content_fs_base
{
	var $folder_content_type=array('catalogue','catalogue_category');
	var $file_content_type='catalogue_entry';

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
		require_code('catalogues2');

		if (TODO)
		{
			$catalogue_name=TODO;
			$description=$this->_default_property_str($properties,'description');
			$notes=$this->_default_property_str($properties,'notes');
			$parent_id=$this->_integer_category($category);
			$rep_image=$this->_default_property_str($properties,'rep_image');
			$move_days_lower=$this->_default_property_int_null($properties,'move_days_lower');
			$move_days_higher=$this->_default_property_int_null($properties,'move_days_higher');
			$move_target=$this->_default_property_int_null($properties,'move_target');
			$add_date=$this->_default_property_int_null($properties,'add_date');
			$id=actual_add_catalogue_category($catalogue_name,$title,$description,$notes,$parent_id,$rep_image,$move_days_lower,$move_days_higher,$move_target,$add_date);
			return strval($id);
		} else
		{
			$description=$this->_default_property_str($properties,'description');
			$display_type=$this->_default_property_int($properties,'display_type');
			$is_tree=$this->_default_property_int($properties,'is_tree');
			$notes=$this->_default_property_str($properties,'notes');
			$submit_points=$this->_default_property_int($properties,'submit_points');
			$ecommerce=$this->_default_property_int($properties,'ecommerce');
			$send_view_reports=$this->_default_property_int($properties,'send_view_reports');
			$default_review_freq=$this->_default_property_int_null($properties,'default_review_freq');
			$add_time=$this->_default_property_int_null($properties,'add_time');
			$name=$this->_create_name_from_title($title);
			actual_add_catalogue($name,$title,$description,$display_type,$is_tree,$notes,$submit_points,$ecommerce,$send_view_reports,$default_review_freq,$add_time);
			return $name;
		}

		return '';
	}

	/**
	 * Standard modular delete function for content hooks. Deletes the content.
	 *
	 * @param  ID_TEXT	The content ID
	 */
	function _folder_delete($content_id)
	{
		require_code('catalogues2');

		if (TODO)
		{
			delete_catalogue($content_id);
		} else
		{
			delete_catalogue_category(intval($content_id));
		}
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
		require_code('catalogues2');

		$category_id=$this->_integer_category($category);

		$catalogue_name=$GLOBALS['SITE_DB']->query_select_value('catalogue_categories','c_name',array('id'=>$category_id));
		$_fields=list_to_map('id',$GLOBALS['SITE_DB']->query_select('catalogue_fields',array('id','cf_type'),array('c_name'=>$catalogue_name),'ORDER BY cf_order'));
		$map=array();
		$i=0;
		foreach ($_fields as $field_id=>$field_type)
		{
			if ($i==0)
			{
				$map[$field_id]=$title;
			} else
			{
				// TODO
			}
			$i++;
		}

		$validated=$this->_default_property_int($properties,'validated');
		$notes=$this->_default_property_str($properties,'notes');
		$allow_rating=$this->_default_property_int($properties,'allow_rating');
		$allow_comments=$this->_default_property_int($properties,'allow_comments');
		$allow_trackbacks=$this->_default_property_int($properties,'allow_trackbacks');
		$time=$this->_default_property_int_null($properties,'time');
		$submitter=$this->_default_property_int_null($properties,'submitter');
		$edit_date=$this->_default_property_int_null($properties,'edit_date');
		$views=$this->_default_property_int($properties,'views');

		$id=actual_add_catalogue_entry($category_id,$validated,$notes,$allow_rating,$allow_comments,$allow_trackbacks,$map,$time,$submitter,$edit_date,$views);
		return strval($id);
	}

	/**
	 * Standard modular delete function for content hooks. Deletes the content.
	 *
	 * @param  ID_TEXT	The content ID
	 */
	function _file_delete($content_id)
	{
		require_code('catalogues2');
		delete_catalogue_entry(intval($content_id));
	}
}
