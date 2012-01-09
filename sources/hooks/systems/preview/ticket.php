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
 * @package		tickets
 */

class Hook_Preview_ticket
{

	/**
	 * Find whether this preview hook applies.
	 *
	 * @return array			Quartet: Whether it applies, the attachment ID type, whether the forum DB is used [optional], list of fields to limit to [optional]
	 */
	function applies()
	{
		$applies=(get_param('page','')=='tickets');
		return array($applies,'ocf_post',false,array('post'));
	}

}


