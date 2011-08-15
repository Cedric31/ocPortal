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
 * @package		occle
 */

class Hook_write
{
	/**
	* Standard modular run function for OcCLE hooks.
	*
	* @param  array	The options with which the command was called
	* @param  array	The parameters with which the command was called
	* @param  object  A reference to the OcCLE filesystem object
	* @return array	Array of stdcommand, stdhtml, stdout, and stderr responses
	*/
	function run($options,$parameters,&$occle_fs)
	{
		if ((array_key_exists('h',$options)) || (array_key_exists('help',$options))) return array('',do_command_help('write',array('h'),array(true,true)),'','');
		else
		{
			if (!array_key_exists(0,$parameters)) return array('','','',do_lang('MISSING_PARAM','1','write'));
			else $parameters[0]=$occle_fs->_pwd_to_array($parameters[0]);
			if (!array_key_exists(1,$parameters)) return array('','','',do_lang('MISSING_PARAM','2','write'));

			//NOTE: Lack of existence-checking for the file, since this command can create files.
			$success=$occle_fs->write_file($parameters[0],$parameters[1]);
			if ($success) return array('','',do_lang('SUCCESS'),'');
			else return array('','','',do_lang('INCOMPLETE_ERROR'));
		}
	}

}

