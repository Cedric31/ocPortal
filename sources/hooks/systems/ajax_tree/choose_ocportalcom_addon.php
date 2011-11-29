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
 * @package		core_addon_management
 */

class Hook_choose_ocportalcom_addon
{

	/**
	 * This will get the XML file from ocportal.com.
	 *
	 * @param  ?ID_TEXT		The ID to do under (NULL: root)
	 * @return string			The XML file
	 */
	function get_file($id)
	{
		$stub=(get_param_integer('localhost',0)==1)?get_base_url():'http://ocportal.com';
		$v='Version '.float_to_raw_string(ocp_version_number(),1);
		if (!is_null($id))
		{
			$v=$id;
		}
		$url=$stub.'/data/ajax_tree.php?hook=choose_download&id='.rawurlencode($v).'&file_type=tar';
		require_code('character_sets');
		$contents=http_download_file($url);
		$utf=strpos(substr($contents,0,200),'utf-8')!==false; // We have to use 'U' in the regexp to work around a Chrome parser bug (we can't rely on convert_to_internal_encoding being 100% correct)
		$contents=preg_replace('#^\s*\<'.'\?xml version="1.0" encoding="[^"]*"\?'.'\>\<request\>#'.($utf?'U':''),'',$contents);
		$contents=preg_replace('#</request>#'.($utf?'U':''),'',$contents);
		$contents=preg_replace('#<category [^>]*has_children="false"[^>]*>[^>]*</category>#'.($utf?'U':''),'',$contents);
		$contents=preg_replace('#<category [^>]*title="Manual install required"[^>]*>[^>]*</category>#'.($utf?'U':''),'',$contents);
		$contents=convert_to_internal_encoding($contents);
		return $contents;
	}

	/**
	 * Standard modular run function for ajax-tree hooks. Generates XML for a tree list, which is interpreted by Javascript and expanded on-demand (via new calls).
	 *
	 * @param  ?ID_TEXT		The ID to do under (NULL: root)
	 * @param  array			Options being passed through
	 * @param  ?ID_TEXT		The ID to select by default (NULL: none)
	 * @return string			XML in the special category,entry format
	 */
	function run($id,$options,$default=NULL)
	{
		unset($options);
		unset($default);

		return $this->get_file($id);
	}

	/**
	 * Standard modular simple function for ajax-tree hooks. Returns a normal <select> style <option>-list, for fallback purposes
	 *
	 * @param  ?ID_TEXT		The ID to do under (NULL: root) - not always supported
	 * @param  array			Options being passed through
	 * @param  ?ID_TEXT		The ID to select by default (NULL: none)
	 * @return tempcode		The nice list
	 */
	function simple($id,$options,$it=NULL)
	{
		unset($options);

		$file=$this->get_file($id);
		$matches=array();
		$num_matches=preg_match_all('#<entry id="(\d+)" title="([^"]+)"#',$file,$matches);
		$list=new ocp_tempcode();
		$list=form_input_list_entry('',false,do_lang_tempcode('NA_EM'));
		for ($i=0;$i<$num_matches;$i++)
		{
			$list->attach(form_input_list_entry('http://ocportal.com/dload.php?id='.$matches[1][$i],false,$matches[2][$i]));
		}
		return $list;
	}

}


