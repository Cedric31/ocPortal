<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		justgiving
 */

class Hook_addon_registry_justgiving
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
	 * Get the addon category
	 *
	 * @return string			The category
	 */
	function get_category()
	{
		return 'Third Party Integration';
	}

	/**
	 * Get the addon author
	 *
	 * @return string			The author
	 */
	function get_author()
	{
		return 'Kamen Blaginov';
	}

	/**
	 * Find other authors
	 *
	 * @return array			A list of co-authors that should be attributed
	 */
	function get_copyright_attribution()
	{
		return array();
	}

	/**
	 * Get the addon licence (one-line summary only)
	 *
	 * @return string			The licence
	 */
	function get_licence()
	{
		return 'Licensed on the same terms as ocPortal';
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'Display a current amount which has been collected and a link to donate via JustGiving.com. To use this addon you will need your eggid from your justgiving account page. To find this:
1) Log into your account and click \"Your pages\"
2) Click the page you want to add the banner for
3) Click use our widget, this will show you some code that looks like: [code]<object type=\"application/x-shockwave-flash\" allowScriptAccess=\"always\" height=\"230\" width=\"150\" align=\"middle\" data=\"http://www.justgiving.com/widgets/jgwidget.swf\" flashvars=\"EggId=2306991&IsMS=0\"><param name=\"movie\" value=\"http://www.justgiving.com/widgets/jgwidget.swf\" /><param name=\"allowScriptAccess\" value=\"always\" /><param name=\"allowNetworking\" value=\"all\" /><param name=\"quality\" value=\"high\" /><param name=\"wmode\" value=\"transparent\" /><param name=\"flashvars\" value=\"EggId=2306991&IsMS=0\" /></object>[/code]
4) You need the text after where it says [tt]Eggid=[/tt] and before the [tt]\"[/tt], so in the example above the eggid code would be [tt]2306991&IsMS=0[/tt]
5) Paste this into where the creation assistant asks for the code or use as the example below shows: [code=\"Comcode\"][block eggid=\"2306991&IsMS=0\"]side_justgiving_donate[/block][/code]';
	}

	/**
	 * Get a list of tutorials that apply to this addon
	 *
	 * @return array			List of tutorials
	 */
	function get_applicable_tutorials()
	{
		return array(
		);
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(
			),
			'recommends'=>array(
			),
			'conflicts_with'=>array(
			)
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
			'sources_custom/hooks/systems/addon_registry/justgiving.php',
			'sources_custom/blocks/side_justgiving_donate.php',
			'lang_custom/EN/justgiving_donate.ini',
			'themes/default/templates_custom/BLOCK_MAIN_JUSTGIVING_DONATE.tpl',
			'themes/default/templates_custom/BLOCK_SIDE_JUSTGIVING_DONATE.tpl',
		);
	}
}