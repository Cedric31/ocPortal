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
 * @package		downloads
 */

class Hook_gu_downloads
{

	/**
	 * Add in new icons to a worked-within gallery.
	 *
	 * @param  ID_TEXT	Gallery name
	 * @return array		Results
	 */
	function new_donext_icons($cat)
	{
		if (!addon_installed('downloads')) return array();
		
		if (substr($cat,0,9)!='download_') return array();

		$id=intval(substr($cat,9));
		return array(
			array('downloads',array('downloads',array('type'=>'entry','id'=>$id),get_module_zone('downloads')),do_lang('VIEW'))
		);
	}

}


