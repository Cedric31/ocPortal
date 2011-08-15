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
 * @package		cedi
 */

class Hook_ocf_cpf_filter_points_cedi
{

	/**
	 * Find which special CPF's to enable.
	 *
	 * @return array			A list of CPF's to enable
	 */
	function to_enable()
	{
		return array();//Actually, hide all these  array('points_gained_seedy'=>1);
	}

}


