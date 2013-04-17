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

require_code('resource_fs');

class Hook_occle_fs_banners extends resource_fs_base
{
	var $folder_resource_type='banner_type';
	var $file_resource_type='banner';

	/**
	 * Standard modular function for seeing how many resources are. Useful for determining whether to do a full rebuild.
	 *
	 * @param  ID_TEXT		The resource type
	 * @return integer		How many resources there are
	 */
	function get_resources_count($resource_type)
	{
		switch ($resource_type)
		{
			case 'banner':
				return $GLOBALS['SITE_DB']->query_select_value('banners','COUNT(*)');

			case 'banner_type':
				return $GLOBALS['SITE_DB']->query_select_value('banner_types','COUNT(*)');
		}
		return 0;
	}

	/**
	 * Standard modular function for searching for a resource by label.
	 *
	 * @param  ID_TEXT		The resource type
	 * @param  LONG_TEXT		The resource label
	 * @return array			A list of resource IDs
	 */
	function find_resource_by_label($resource_type,$label)
	{
		switch ($resource_type)
		{
			case 'banner':
				$ret=$GLOBALS['SITE_DB']->query_select('banners',array('name'),array('name'=>$label));
				return collapse_1d_complexity('name',$ret);

			case 'banner_type':
				$ret=$GLOBALS['SITE_DB']->query_select('banner_types',array('id'),array('id'=>$label));
				return collapse_1d_complexity('id',$ret);
		}
		return array();
	}

	/**
	 * Standard modular introspection function.
	 *
	 * @return array			The properties available for the resource type
	 */
	function _enumerate_folder_properties()
	{
		return array(
			'is_textual'=>'BINARY',
			'image_width'=>'INTEGER',
			'image_height'=>'INTEGER',
			'max_file_size'=>'INTEGER',
			'comcode_inline'=>'BINARY',
		);
	}

	/**
	 * Standard modular date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
	 *
	 * @param  array			Resource row (not full, but does contain the ID)
	 * @return ?TIME			The edit date or add date, whichever is higher (NULL: could not find one)
	 */
	function _get_folder_edit_date($row)
	{
		$query='SELECT MAX(date_and_time) FROM '.get_table_prefix().'adminlogs WHERE '.db_string_equal_to('param_a',$row['id']).' AND  ('.db_string_equal_to('the_type','ADD_BANNER_TYPE').' OR '.db_string_equal_to('the_type','EDIT_BANNER_TYPE').')';
		return $GLOBALS['SITE_DB']->query_value_if_there($query);
	}

	/**
	 * Standard modular add function for resource-fs hooks. Adds some resource with the given label and properties.
	 *
	 * @param  LONG_TEXT		Filename OR Resource label
	 * @param  string			The path (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return ~ID_TEXT		The resource ID (false: error)
	 */
	function folder_add($filename,$path,$properties)
	{
		if ($path!='') return false; // Only one depth allowed for this resource type

		list($properties,$label)=$this->_folder_magic_filter($filename,$path,$properties);

		require_code('banners2');

		$is_textual=$this->_default_property_int($properties,'is_textual');
		$image_width=$this->_default_property_int_null($properties,'image_width');
		if ($image_width===NULL) $image_width=300;
		$image_height=$this->_default_property_int_null($properties,'image_height');
		if ($image_height===NULL) $image_height=250;
		$max_file_size=$this->_default_property_int_null($properties,'max_file_size');
		if ($max_file_size===NULL) $max_file_size=100*1024;
		$comcode_inline=$this->_default_property_int($properties,'comcode_inline');
		$name=$this->_create_name_from_label($label);
		$name=add_banner_type($name,$is_textual,$image_width,$image_height,$max_file_size,$comcode_inline,true);
		return $name;
	}

	/**
	 * Standard modular load function for resource-fs hooks. Finds the properties for some resource.
	 *
	 * @param  SHORT_TEXT	Filename
	 * @param  string			The path (blank: root / not applicable)
	 * @return ~array			Details of the resource (false: error)
	 */
	function folder_load($filename,$path)
	{
		list($resource_type,$resource_id)=$this->folder_convert_filename_to_id($filename);

		$rows=$GLOBALS['SITE_DB']->query_select('banner_types',array('*'),array('id'=>$resource_id),'',1);
		if (!array_key_exists(0,$rows)) return false;
		$row=$rows[0];

		return array(
			'label'=>$row['id'],
			'is_textual'=>$row['t_is_textual'],
			'image_width'=>$row['t_image_width'],
			'image_height'=>$row['t_image_height'],
			'max_file_size'=>$row['t_max_file_size'],
			'comcode_inline'=>$row['t_comcode_inline'],
		);
	}

	/**
	 * Standard modular edit function for resource-fs hooks. Edits the resource to the given properties.
	 *
	 * @param  ID_TEXT		The filename
	 * @param  string			The path (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return boolean		Success status
	 */
	function folder_edit($filename,$path,$properties)
	{
		list($resource_type,$resource_id)=$this->folder_convert_filename_to_id($filename);

		require_code('banners2');

		$label=$this->_default_property_str($properties,'label');
		$is_textual=$this->_default_property_int($properties,'is_textual');
		$image_width=$this->_default_property_int_null($properties,'image_width');
		if ($image_width===NULL) $image_width=300;
		$image_height=$this->_default_property_int_null($properties,'image_height');
		if ($image_height===NULL) $image_height=250;
		$max_file_size=$this->_default_property_int_null($properties,'max_file_size');
		if ($max_file_size===NULL) $max_file_size=100*1024;
		$comcode_inline=$this->_default_property_int($properties,'comcode_inline');
		$name=$this->_create_name_from_label($label);

		$name=edit_banner_type($resource_id,$name,$is_textual,$image_width,$image_height,$max_file_size,$comcode_inline,true);

		return true;
	}

	/**
	 * Standard modular delete function for resource-fs hooks. Deletes the resource.
	 *
	 * @param  ID_TEXT		The filename
	 * @param  string			The path (blank: root / not applicable)
	 * @return boolean		Success status
	 */
	function folder_delete($filename,$path)
	{
		list($resource_type,$resource_id)=$this->folder_convert_filename_to_id($filename);

		require_code('banners2');
		delete_banner_type($resource_id);

		return true;
	}

	/**
	 * Standard modular introspection function.
	 *
	 * @return array			The properties available for the resource type
	 */
	function _enumerate_file_properties()
	{
		return array(
			'image_url'=>'URLPATH',
			'title_text'=>'SHORT_TRANS',
			'direct_code'=>'LONG_TEXT',
			'campaignremaining'=>'INTEGER',
			'site_url'=>'URLPATH',
			'importancemodulus'=>'INTEGER',
			'notes'=>'LONG_TEXT',
			'the_type'=>'SHORT_INTEGER',
			'expiry_date'=>'?TIME',
			'validated'=>'BINARY',
			'hits_from'=>'INTEGER',
			'hits_to'=>'INTEGER',
			'views_from'=>'INTEGER',
			'views_to'=>'INTEGER',
			'submitter'=>'member',
			'add_date'=>'TIME',
			'edit_date'=>'?TIME',
		);
	}

	/**
	 * Standard modular date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
	 *
	 * @param  array			Resource row (not full, but does contain the ID)
	 * @return ?TIME			The edit date or add date, whichever is higher (NULL: could not find one)
	 */
	function _get_file_edit_date($row)
	{
		$query='SELECT MAX(date_and_time) FROM '.get_table_prefix().'adminlogs WHERE '.db_string_equal_to('param_a',$row['name']).' AND  ('.db_string_equal_to('the_type','ADD_BANNER').' OR '.db_string_equal_to('the_type','EDIT_BANNER').')';
		return $GLOBALS['SITE_DB']->query_value_if_there($query);
	}

	/**
	 * Standard modular add function for resource-fs hooks. Adds some resource with the given label and properties.
	 *
	 * @param  LONG_TEXT		Filename OR Resource label
	 * @param  string			The path (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return ~ID_TEXT		The resource ID (false: error, could not create via these properties / here)
	 */
	function file_add($filename,$path,$properties)
	{
		list($category_resource_type,$category)=$this->folder_convert_filename_to_id($path);
		list($properties,$label)=$this->_file_magic_filter($filename,$path,$properties);

		if (is_null($category)) return false; // Folder not found

		require_code('banners2');

		$name=$this->_create_name_from_label($label);
		$imgurl=$this->_default_property_str($properties,'image_url');
		$title_text=$this->_default_property_str($properties,'title_text');
		$direct_code=$this->_default_property_str($properties,'direct_code');
		$campaignremaining=$this->_default_property_int($properties,'campaignremaining');
		$site_url=$this->_default_property_str($properties,'site_url');
		$importancemodulus=$this->_default_property_int_null($properties,'importancemodulus');
		$notes=$this->_default_property_str($properties,'notes');
		$the_type=$this->_default_property_int($properties,'the_type');
		$expiry_date=$this->_default_property_int_null($properties,'expiry_date');
		$submitter=$this->_default_property_int_null($properties,'submitter');
		$validated=$this->_default_property_int_null($properties,'validated');
		if (is_null($validated)) $validated=1;
		$b_type=$category;
		$time=$this->_default_property_int_null($properties,'add_date');
		$hits_from=$this->_default_property_int($properties,'hits_from');
		$hits_to=$this->_default_property_int($properties,'hits_to');
		$views_from=$this->_default_property_int($properties,'views_from');
		$views_to=$this->_default_property_int($properties,'views_to');
		$edit_date=$this->_default_property_int_null($properties,'edit_date');
		$name=add_banner($name,$imgurl,$title_text,$label,$direct_code,$campaignremaining,$site_url,$importancemodulus,$notes,$the_type,$expiry_date,$submitter,$validated,$b_type,$time,$hits_from,$hits_to,$views_from,$views_to,$edit_date,true);
		return $name;
	}

	/**
	 * Standard modular load function for resource-fs hooks. Finds the properties for some resource.
	 *
	 * @param  SHORT_TEXT	Filename
	 * @param  string			The path (blank: root / not applicable)
	 * @return ~array			Details of the resource (false: error)
	 */
	function file_load($filename,$path)
	{
		list($resource_type,$resource_id)=$this->file_convert_filename_to_id($filename);

		$rows=$GLOBALS['SITE_DB']->query_select('banners',array('*'),array('name'=>$resource_id),'',1);
		if (!array_key_exists(0,$rows)) return false;
		$row=$rows[0];

		return array(
			'label'=>$row['name'],
			'image_url'=>$row['img_url'],
			'title_text'=>$row['b_title_text'],
			'direct_code'=>$row['b_direct_code'],
			'campaignremaining'=>$row['campaign_remaining'],
			'site_url'=>$row['site_url'],
			'importancemodulus'=>$row['importance_modulus'],
			'notes'=>$row['notes'],
			'the_type'=>$row['the_type'],
			'expiry_date'=>$row['expiry_date'],
			'validated'=>$row['validated'],
			'hits_from'=>$row['hits_from'],
			'hits_to'=>$row['hits_to'],
			'views_from'=>$row['views_from'],
			'views_to'=>$row['views_to'],
			'submitter'=>$row['submitter'],
			'add_date'=>$row['add_date'],
			'edit_date'=>$row['edit_date'],
		);
	}

	/**
	 * Standard modular edit function for resource-fs hooks. Edits the resource to the given properties.
	 *
	 * @param  ID_TEXT		The filename
	 * @param  string			The path (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return boolean		Success status
	 */
	function file_edit($filename,$path,$properties)
	{
		list($resource_type,$resource_id)=$this->file_convert_filename_to_id($filename);
		list($category_resource_type,$category)=$this->folder_convert_filename_to_id($path);
		list($properties,)=$this->_file_magic_filter($filename,$path,$properties);

		if (is_null($category)) return false; // Folder not found

		require_code('banners2');

		$label=$this->_default_property_str($properties,'label');
		$name=$this->_create_name_from_label($label);
		$imgurl=$this->_default_property_str($properties,'image_url');
		$title_text=$this->_default_property_str($properties,'title_text');
		$direct_code=$this->_default_property_str($properties,'direct_code');
		$campaignremaining=$this->_default_property_int($properties,'campaignremaining');
		$site_url=$this->_default_property_str($properties,'site_url');
		$importancemodulus=$this->_default_property_int($properties,'importancemodulus');
		$notes=$this->_default_property_str($properties,'notes');
		$the_type=$this->_default_property_int($properties,'the_type');
		$expiry_date=$this->_default_property_int_null($properties,'expiry_date');
		$submitter=$this->_default_property_int_null($properties,'submitter');
		$validated=$this->_default_property_int_null($properties,'validated');
		if (is_null($validated)) $validated=1;
		$b_type=$category;
		$add_time=$this->_default_property_int_null($properties,'add_date');
		$hits_from=$this->_default_property_int($properties,'hits_from');
		$hits_to=$this->_default_property_int($properties,'hits_to');
		$views_from=$this->_default_property_int($properties,'views_from');
		$views_to=$this->_default_property_int($properties,'views_to');
		$edit_date=$this->_default_property_int_null($properties,'edit_date');

		$name=edit_banner($resource_id,$name,$imgurl,$title_text,$label,$direct_code,$campaignremaining,$site_url,$importancemodulus,$notes,$the_type,$expiry_date,$submitter,$validated,$b_type,$edit_date,$add_time,true,true);

		return true;
	}

	/**
	 * Standard modular delete function for resource-fs hooks. Deletes the resource.
	 *
	 * @param  ID_TEXT		The filename
	 * @param  string			The path (blank: root / not applicable)
	 * @return boolean		Success status
	 */
	function file_delete($filename,$path)
	{
		list($resource_type,$resource_id)=$this->file_convert_filename_to_id($filename);

		require_code('banners2');
		delete_banner($resource_id);

		return true;
	}
}
