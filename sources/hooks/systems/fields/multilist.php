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

class Hook_fields_multilist
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
		$fields=array();
		$type='_LIST';
		$special=new ocp_tempcode();
		$special->attach(form_input_list_entry('',get_param('option_'.strval($row['id']),'')=='','---'));
		$list=($row['cf_default']=='')?array():explode('|',$row['cf_default']);
		$display=array_key_exists('trans_name',$row)?$row['trans_name']:get_translated_text($row['cf_name']); // 'trans_name' may have been set in CPF retrieval API, might not correspond to DB lookup if is an internal field
		foreach ($list as $l)
		{
			$special->attach(form_input_list_entry($l,get_param('option_'.strval($row['id']),'')==$l));
		}
		$fields[]=array('NAME'=>strval($row['id']),'DISPLAY'=>$display,'TYPE'=>$type,'SPECIAL'=>$special);
		return $fields;
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
		return nl_delim_match_sql($row,$i,'long');
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
		unset($field);
		if (!is_null($required))
		{
			if (($required) && ($default=='')) $default=preg_replace('#\|.*#','',$default);
		}
		return array('long_unescaped',$default,'long');
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
		$all=array();
		$exploded=($ev=='')?array():explode(chr(10),$ev);
		foreach (explode('|',$field['cf_default']) as $option)
		{
			if (in_array($option,$exploded)) $all[]=array('OPTION'=>$option,'HAS'=>true);
		}
		if (!array_key_exists('c_name',$field)) $field['c_name']='other';
		return do_template('CATALOGUE_'.$field['c_name'].'_FIELD_FIELD_MULTILIST',array('ALL'=>$all),NULL,false,'CATALOGUE_DEFAULT_FIELD_MULTILIST');
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
	 * @return ?tempcode		The Tempcode for the input field (NULL: skip the field - it's not input)
	 */
	function get_field_inputter($_cf_name,$_cf_description,$field,$actual_value)
	{
		$default=$field['cf_default'];
		$list=($default=='')?array():explode('|',$default);
		$_list=new ocp_tempcode();
		$exploded=explode(chr(10),$actual_value);
		if (($field['cf_required']==0) && (($actual_value=='') || (is_null($actual_value))))
			$_list->attach(form_input_list_entry('',true,do_lang_tempcode('NA_EM')));
		foreach ($list as $l)
		{
			$_list->attach(form_input_list_entry($l,in_array($l,$exploded)));
		}
		return form_input_multi_list($_cf_name,$_cf_description,'field_'.strval($field['id']),$_list,NULL,5,$field['cf_required']==1);
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
		if (!isset($_POST[$tmp_name]))
		{
			return ($editing && (is_null(post_param('require__field_'.strval($field['id']),NULL))))?STRING_MAGIC_NULL:'';
		}
		return implode(chr(10),$_POST[$tmp_name]);
	}

}


