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
 * @package		points
 */

class Hook_sw_leader_board
{

	/**
	 * Standard modular run function for blocks in the setup wizard.
	 *
	 * @return array		Map of block names, to display types.
	 */
	function get_blocks()
	{
		if (!addon_installed('points')) return array();
		
		return array(array('main_leader_board'=>array('NO','YES')),array());
	}

}


