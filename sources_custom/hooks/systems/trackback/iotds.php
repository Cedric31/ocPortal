<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		iotds
 */

class Hook_trackback_iotds
{
	/**
	 * Standard modular run function for trackback hooks. They see if content of an ID relating to this content has trackback enabled.
	 *
	 * @param  ID_TEXT		The ID
	 * @return boolean		Whether trackback is enabled
	 */
	function run($id)
	{
		$rows=$GLOBALS['SITE_DB']->query_select('iotd',array('allow_trackbacks'),array('id'=>intval($id)),'',1);
		if (!array_key_exists(0,$rows)) return false;
		return $rows[0]['allow_trackbacks']==1;
	}
}

