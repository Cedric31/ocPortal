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

class Hook_sw_iotds
{

	/**
	 * Standard modular run function for blocks in the setup wizard.
	 *
	 * @return array		Map of block names, to display types.
	 */
	function get_blocks()
	{
		if (!addon_installed('iotds')) return array();

		return array(array('main_iotd'=>array('YES_CELL','YES_CELL')),array());
	}

}

