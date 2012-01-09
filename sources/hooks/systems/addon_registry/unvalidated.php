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
 * @package		unvalidated
 */

class Hook_addon_registry_unvalidated
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
		return 'Manage the validation (approval) of content.';
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
			'previously_in_addon'=>array('core_unvalidated'),
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

			'sources/hooks/systems/notifications/content_validated.php',
			'sources/hooks/systems/notifications/needs_validation.php',
			'sources/hooks/systems/addon_registry/unvalidated.php',
			'UNVALIDATED_SCREEN.tpl',
			'UNVALIDATED_SECTION.tpl',
			'VALIDATION_REQUEST.tpl',
			'adminzone/pages/modules/admin_unvalidated.php',
			'themes/default/images/pagepics/unvalidated.png',
			'lang/EN/unvalidated.ini',
			'sources/hooks/blocks/main_staff_checklist/unvalidated.php',
			'sources/hooks/modules/admin_unvalidated/.htaccess',
			'sources/hooks/modules/admin_unvalidated/index.html',
		);
	}


	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array			The mapping
	*/
	function tpl_previews()
	{
		return array(
				'UNVALIDATED_SECTION.tpl'=>'administrative__unvalidated_screen',
				'UNVALIDATED_SCREEN.tpl'=>'administrative__unvalidated_screen',
				'VALIDATION_REQUEST.tpl'=>'administrative__validation_request',
				);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__validation_request()
	{
		require_lang('unvalidated');
		return array(
			lorem_globalise(
				do_lorem_template('VALIDATION_REQUEST',array(
					'USERNAME'=>lorem_word(),
					'TYPE'=>lorem_phrase(),
					'ID'=>placeholder_id(),
					'URL'=>placeholder_url(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__unvalidated_screen()
	{
		$section = do_lorem_template('UNVALIDATED_SECTION',array(
				'TITLE'=>lorem_phrase(),
				'CONTENT'=>lorem_phrase(),
					)
			);
		return array(
			lorem_globalise(
				do_lorem_template('UNVALIDATED_SCREEN',array(
					'TITLE'=>lorem_title(),
					'SECTIONS'=>$section,
						)
			),NULL,'',true),
		);
	}
}