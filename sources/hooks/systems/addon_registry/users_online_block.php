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
 * @package		users_online_block
 */

class Hook_addon_registry_users_online_block
{

	/**
	 * Get a list of file permissions to set
	 *
	 * @return array			File permissions to set
	 */
	function get_chmod_array()
	{
		return array();
	}

	/**
	 * Get the version of ocPortal this addon is for
	 *
	 * @return float			Version number
	 */
	function get_version()
	{
		return ocp_version_number();
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'A block to show which users who are currently visiting the website, and birthdays.';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array(),
		);
	}

	/**
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(

			'sources/hooks/systems/config_default/usersonline_show_birthdays.php',
			'sources/hooks/systems/config_default/usersonline_show_newest_member.php',
			'sources/hooks/systems/addon_registry/users_online_block.php',
			'BLOCK_SIDE_USERS_ONLINE.tpl',
			'BLOCK_SIDE_USERS_ONLINE_USER.tpl',
			'sources/blocks/side_users_online.php',
		);
	}


	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array                 The mapping
	*/
	function tpl_previews()
	{
		return array(
			'BLOCK_SIDE_USERS_ONLINE_USER.tpl'=>'block_side_users_online',
			'BLOCK_SIDE_USERS_ONLINE.tpl'=>'block_side_users_online',
			);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__block_side_users_online()
	{
		$out = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$out->attach(do_lorem_template('BLOCK_SIDE_USERS_ONLINE_USER',array('URL'=>placeholder_url(),'NAME'=>lorem_phrase(),'COLOUR'=>lorem_word())));
		}

		$newest = new ocp_tempcode();
		$birthdays = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$newest->attach(lorem_phrase());

			$birthday = do_lorem_template('OCF_USER_MEMBER',array('COLOUR'=>lorem_word(),'AGE'=>placeholder_number(),'PROFILE_URL'=>placeholder_url(),'USERNAME'=>lorem_phrase(),'AT'=>lorem_phrase()));
			$birthdays->attach($birthday);
		}

		return array(
			lorem_globalise(
			do_lorem_template('BLOCK_SIDE_USERS_ONLINE',array(
			'CONTENT'=>$out,
			'GUESTS'=>placeholder_number(),
			'MEMBERS'=>placeholder_number(),
			'_GUESTS'=>lorem_phrase(),
			'_MEMBERS'=>lorem_phrase(),
			'BIRTHDAYS'=>$birthdays,
			'NEWEST'=>$newest,
			)
			),NULL,'',true)
			);
	}
}