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
 * @package		catalogues
 */

class Hook_catalogue_field_integer
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
	// Backend: catalogues
	// ===================

	/**
	 * Get some info bits relating to our field type, that helps us look it up / set defaults.
	 *
	 * @param  AUTO_LINK		The field ID
	 * @param  ?boolean		Whether the row is required (NULL: don't try and find a default value)
	 * @param  ?string		The given default value (NULL: don't try and find a default value)
	 * @return array			Tuple of details (row-type,default-value-to-use,db row-type)
	 */
	function get_field_value_row_bits($cf_id,$required=NULL,$default=NULL)
	{
		unset($cf_id);
		if (!is_null($required))
		{
			if (($required) && ($default=='')) $default='0';
		}
		return array('short_unescaped',$default,'short');
	}

	/**
	 * Convert a field value to something renderable.
	 *
	 * @param  mixed			The raw value
	 * @return mixed			Rendered field (tempcode or string)
	 */
	function render_field_value($ev)
	{
		if (is_object($ev)) return $ev;
		return escape_html(preg_replace('#^0*#','',$ev));
	}

	// ======================
	// Module: cms_catalogues
	// ======================

	/**
	 * Convert a field value to something renderable.
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
		return form_input_integer($_cf_name,$_cf_description,'field_'.strval($field['id']),(is_null($actual_value) || ($actual_value===''))?NULL:intval($actual_value),$field['cf_required']==1);
	}

	/**
	 * Find the posted value from the get_field_inputter field
	 *
	 * @param  boolean		Whether we were editing (because on edit, files might need deleting)
	 * @param  AUTO_LINK		The ID of the catalogue field
	 * @return string			The value
	 */
	function inputted_to_field_value($editing,$id)
	{
		$tmp_name='field_'.strval($id);
		$ret=post_param($tmp_name,STRING_MAGIC_NULL);
		if ($ret!=STRING_MAGIC_NULL) $ret=str_pad($ret,10,'0',STR_PAD_LEFT);
		return $ret;
	}

}


