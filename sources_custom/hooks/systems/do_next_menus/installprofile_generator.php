<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		installprofile_generator
 */

class Hook_do_next_menus_installprofile_generator
{

	/**
	 * Standard modular run function for do_next_menu hooks. They find links to put on standard navigation menus of the system.
	 *
	 * @return array			Array of links and where to show
	 */
	function run()
	{
		require_lang('facebook');

		return array(
			array('setup','admin_home',array('installprofile_generator',array(),'adminzone'),'Export installprofile')
		);
	}

}

