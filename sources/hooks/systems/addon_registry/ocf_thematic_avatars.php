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
 * @package		ocf_thematic_avatars
 */

class Hook_addon_registry_ocf_thematic_avatars
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
		return 'A selection of avatars for OCF';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array('ocf_member_avatars'),
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

			'sources/hooks/systems/addon_registry/ocf_thematic_avatars.php',
			'themes/default/images/ocf_default_avatars/default_set/thematic/animals.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/books.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/business.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/chess.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/food.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/games.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/index.html',
			'themes/default/images/ocf_default_avatars/default_set/thematic/money.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/music.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/nature.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/outdoors.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/space.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/sports.png',
			'themes/default/images/ocf_default_avatars/default_set/thematic/tech.png',
		);
	}

}
