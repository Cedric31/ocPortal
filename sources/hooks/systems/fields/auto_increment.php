<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_fields
 */

class Hook_fields_auto_increment
{

	// ==============
	// Module: search
	// ==============

	/**
	 * Get special Tempcode for inputting this field.
	 *
	 * @param  array			The row for the field to input
	 * @return ?array			List of specially encoded input detail rows (NULL: nothing special)
	 */
	function get_search_inputter($row)
	{
		return NULL;
	}

	/**
	 * Get special SQL from POSTed parameters for this field.
	 *
	 * @param  array			The row for the field to input
	 * @param  integer		We're processing for the ith row
	 * @return ?array			Tuple of SQL details (array: extra trans fields to search, array: extra plain fields to search, string: an extra table segment for a join, string: the name of the field to use as a title, if this is the title, extra WHERE clause stuff) (NULL: nothing special)
	 */
	function inputted_to_sql_for_search($row,$i)
	{
		return exact_match_sql($row,$i);
	}

	// ===================
	// Backend: fields API
	// ===================

	/**
	 * Get some info bits relating to our field type, that helps us look it up / set defaults.
	 *
	 * @param  ?array			The field details (NULL: new field)
	 * @param  ?boolean		Whether a default value cannot be blank (NULL: don't "lock in" a new default value)
	 * @param  ?string		The given default value as a string (NULL: don't "lock in" a new default value)
	 * @return array			Tuple of details (row-type,default-value-to-use,db row-type)
	 */
	function get_field_value_row_bits($field,$required=NULL,$default=NULL)
	{
		if (!is_null($required))
		{
			$default=is_null($field)?'0':strval($this->get_field_auto_increment($field['id'],intval($default)));
		}
		return array('integer_unescaped',$default,'integer');
	}

	/**
	 * Convert a field value to something renderable.
	 *
	 * @param  array			The field details
	 * @param  mixed			The raw value
	 * @return mixed			Rendered field (tempcode or string)
	 */
	function render_field_value($field,$ev)
	{
		if (is_object($ev)) return $ev;
		return escape_html(preg_replace('#^0*#','',$ev));
	}

	// ======================
	// Frontend: fields input
	// ======================

	/**
	 * Get form inputter.
	 *
	 * @param  string			The field name
	 * @param  string			The field description
	 * @param  array			The field details
	 * @param  ?string		The actual current value of the field (NULL: none)
	 * @param  boolean		Whether this is for a new entry
	 * @return ?tempcode		The Tempcode for the input field (NULL: skip the field - it's not input)
	 */
	function get_field_inputter($_cf_name,$_cf_description,$field,$actual_value,$new)
	{
		if ($new) return NULL;

		return form_input_integer($_cf_name,$_cf_description,'field_'.strval($field['id']),is_null($actual_value)?NULL:intval($actual_value),$field['cf_required']==1);
	}

	/**
	 * Find the posted value from the get_field_inputter field
	 *
	 * @param  boolean		Whether we were editing (because on edit, it could be a fractional edit)
	 * @param  array			The field details
	 * @param  string			Where the files will be uploaded to
	 * @param  ?string		Former value of field (NULL: none)
	 * @return string			The value
	 */
	function inputted_to_field_value($editing,$field,$upload_dir='uploads/catalogues',$old_value=NULL)
	{
		$id=$field['id'];
		$tmp_name='field_'.strval($id);
		if (!$editing)
		{
			return $this->get_field_auto_increment($id,$field['cf_default']);
		}
		$ret=post_param($tmp_name,STRING_MAGIC_NULL);
		if ($ret!=STRING_MAGIC_NULL) $ret=str_pad($ret,10,'0',STR_PAD_LEFT);
		return $ret;
	}

	/**
	 * Get a fresh value for an auto_increment valued field.
	 *
	 * @param  AUTO_LINK		The field ID
	 * @param  string			The field default
	 * @return string			The value
	 */
	function get_field_auto_increment($field_id,$default='')
	{
		// Get most recent value, to start with- we will iterate forward on it
		$value=$GLOBALS['SITE_DB']->query_value_null_ok('catalogue_efv_integer','cv_value',array('cf_id'=>$field_id),'ORDER BY ce_id DESC');
		if (is_null($value))
		{
			$value=strval(intval($default)-1);
		}
	
		$test=NULL;
		do
		{
			$value=strval(intval($value)+1);
	
			$test=$GLOBALS['SITE_DB']->query_value_null_ok('catalogue_efv_integer','ce_id',array('cv_value'=>$value,'cf_id'=>$field_id));
		}
		while (!is_null($test));
	
		return $value;
	}

}


