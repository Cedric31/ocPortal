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
 * @package		core_abstract_interfaces
 */

/**
 * Get the tempcode for a view space page. (a view space shows a single entry, with the field name for each field to the left of the value)
 *
 * @param  tempcode		The title of the view space; should be out of get_page_title
 * @param  array			An array of mappings between title and value (each mapping being a field)
 * @return tempcode		The generated view space
 */
function view_space($title,$fields)
{
	$_fields=new ocp_tempcode();
	foreach ($fields as $key=>$val)
	{
		if (!is_array($val))
		{
			$raw=true;
		} else
		{
			list($val,$raw)=$val;
		}
		$_fields->attach(view_space_field(do_lang_tempcode($key),$val,$raw));
	}

	return do_template('VIEW_SPACE_SCREEN',array('_GUID'=>'c8c6cbc8e7b5a47a3078fd69feb057a0','TITLE'=>$title,'FIELDS'=>$_fields));
}

/**
 * Get the tempcode for a view space field.
 *
 * @param  mixed			The field title (Tempcode or string). Assumed unescaped.
 * @param  mixed			The field value (Tempcode or string). Assumed unescaped.
 * @param  boolean		Whether the field should be shown as untitled... because it is an element of a subblock of raw rows
 * @param  string			Field abbreviation (blank: none)
 * @return tempcode		The generated view space field
 */
function view_space_field($name,$value,$raw=false,$abbr='') // Not for use with the above, which takes the fields as a raw map
{
	return do_template('VIEW_SPACE_FIELD'.($raw?'_RAW':'').(($abbr!='')?'_ABBR':''),array('ABBR'=>$abbr,'NAME'=>$name,'VALUE'=>$value));
}

