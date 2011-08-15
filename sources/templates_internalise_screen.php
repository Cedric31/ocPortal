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
 * Put the contents of a page inside an iframe. This is typically used when a page is being used to traverse a result-set that spans multiple screens.
 *
 * @param  tempcode		The title
 * @param  ?integer		The time between refreshes (NULL: do not refresh)
 * @param  ?mixed			Data. A refresh will only happen if an AJAX-check indicates this data has changed (NULL: no check)
 * @return ?tempcode		The page output to finish off our current page stream such that it will spawn the iframe (NULL: not internalised)
 */
function internalise_own_screen($title,$refresh_time=NULL,$refresh_if_changed=NULL)
{
	if ((get_value('no_frames')==='1') || (get_param_integer('keep_no_frames',0)==1)) return NULL;
	
	if (!has_js()) return NULL; // We need JS to make this a seamless process
	if (strpos(ocp_srv('REQUEST_URI'),'/iframe.php')!==false) return NULL; // This is already in the iframe

	require_javascript('javascript_ajax');
	require_javascript('javascript_iframe_screen');

	$url=find_script('iframe').'?zone='.rawurlencode(get_zone_name()).'&wide_high=1&utheme='.rawurlencode($GLOBALS['FORUM_DRIVER']->get_theme());
	foreach (array_merge($_GET,$_POST) as $key=>$param)
	{
		if (!is_string($param)) continue;
		if ((substr($key,0,5)=='keep_') && (skippable_keep($key,$param))) continue;
		if (get_magic_quotes_gpc()) $param=stripslashes($param);
		$url.='&'.$key.'='.urlencode($param);
	}

	if (!is_null($refresh_if_changed))
	{
		require_javascript('javascript_sound');
		$change_detection_url=find_script('change_detection').'?whatever=1';
		foreach ($_GET as $key=>$param)
		{
			if (!is_string($param)) continue;
			if ((substr($key,0,5)=='keep_') && (skippable_keep($key,$param))) continue;
			if (get_magic_quotes_gpc()) $param=stripslashes($param);
			$change_detection_url.='&'.$key.'='.urlencode($param);
		}
	} else
	{
		$refresh_if_changed='';
		$change_detection_url='';
	}
	return do_template('IFRAME_SCREEN',array('_GUID'=>'06554eb227428fd5c648dee3c5b38185','TITLE'=>$title,'REFRESH_IF_CHANGED'=>md5(serialize($refresh_if_changed)),'CHANGE_DETECTION_URL'=>$change_detection_url,'REFRESH_TIME'=>is_null($refresh_time)?'':strval($refresh_time),'IFRAME_URL'=>$url));
}

