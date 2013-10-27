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
 * @package		import
 */

class Hook_addon_registry_import
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
		return 'Switch to ocPortal from other software. This addon provides the architecture for importing, and a number of prewritten importers.';
	}

	/**
	 * Get a list of tutorials that apply to this addon
	 *
	 * @return array			List of tutorials
	 */
	function get_applicable_tutorials()
	{
		return array(
			'tut_importer',
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
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array()
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
			'themes/default/css/importing.css',
			'sources/hooks/modules/admin_import/html_site.php',
			'sources/hooks/modules/admin_import/index.html',
			'sources/hooks/modules/admin_import/shared/.htaccess',
			'sources/hooks/modules/admin_import/shared/index.html',
			'sources/hooks/systems/addon_registry/import.php',
			'sources/hooks/modules/admin_import_types/.htaccess',
			'sources/hooks/modules/admin_import_types/core.php',
			'sources/hooks/modules/admin_import_types/index.html',
			'themes/default/templates/IMPORT_ACTION_LINE.tpl',
			'themes/default/templates/IMPORT_ACTION_SCREEN.tpl',
			'themes/default/templates/IMPORT_MESSAGE.tpl',
			'themes/default/templates/IMPORT_PHPNUKE_FCOMCODEPAGE.tpl',
			'themes/default/templates/IMPORT_MKPORTAL_FCOMCODEPAGE.tpl',
			'adminzone/pages/modules/admin_import.php',
			'themes/default/images/pagepics/xml.png',
			'lang/EN/import.ini',
			'sources/hooks/modules/admin_import/.htaccess',
			'sources/hooks/modules/admin_import/ipb1.php',
			'sources/hooks/modules/admin_import/ipb2.php',
			'sources/hooks/modules/admin_import/ocp_merge.php',
			'sources/hooks/modules/admin_import/phpbb2.php',
			'sources/hooks/modules/admin_import/shared/ipb.php',
			'sources/hooks/modules/admin_import/vb3.php',
			'sources/hooks/modules/admin_import/mybb.php',
			'sources/hooks/modules/admin_import/wowbb.php',
			'sources/hooks/modules/admin_import/phpbb3.php',
			'sources/hooks/modules/admin_import/aef.php',
			'sources/hooks/modules/admin_import/smf.php',
			'sources/hooks/modules/admin_import/smf2.php',
			'sources/hooks/systems/do_next_menus/import.php',
			'sources/hooks/modules/admin_import/wordpress.php',
			'sources/hooks/systems/ocf_auth/wordpress.php',
			'sources/import.php',
			'themes/default/images/pagepics/importdata.png',
			'lang/EN/xml_storage.ini',
			'themes/default/templates/XML_STORAGE_SCREEN.tpl',
			'themes/default/templates/XML_STORAGE_EXPORT_RESULTS_SCREEN.tpl',
			'themes/default/templates/XML_STORAGE_IMPORT_RESULTS_SCREEN.tpl',
			'adminzone/pages/modules/admin_xml_storage.php',
			'sources/hooks/systems/occle_commands/continue_import.php',
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
			'IMPORT_ACTION_LINE.tpl'=>'administrative__import_action_screen',
			'IMPORT_ACTION_SCREEN.tpl'=>'administrative__import_action_screen',
			'IMPORT_MESSAGE.tpl'=>'administrative__import_action_screen',
			'XML_STORAGE_SCREEN.tpl'=>'administrative__xml_storage_screen',
			'XML_STORAGE_IMPORT_RESULTS_SCREEN.tpl'=>'administrative__xml_storage_import_results_screen',
			'XML_STORAGE_EXPORT_RESULTS_SCREEN.tpl'=>'administrative__xml_storage_export_results_screen',
			'IMPORT_MKPORTAL_FCOMCODEPAGE.tpl'=>'administrative__import_mkportal_fcomcodepage',
			'IMPORT_PHPNUKE_FCOMCODEPAGE.tpl'=>'administrative__import_phpnuke_fcomcodepage'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__import_action_screen()
	{
		$extra=do_lorem_template('IMPORT_MESSAGE',array(
			'MESSAGE'=>lorem_phrase()
		));
		$import_list=do_lorem_template('IMPORT_ACTION_LINE',array(
			'CHECKED'=>false,
			'DISABLED'=>true,
			'NAME'=>lorem_word(),
			'TEXT'=>lorem_phrase(),
			'ADVANCED_URL'=>placeholder_url()
		));

		return array(
			lorem_globalise(do_lorem_template('IMPORT_ACTION_SCREEN',array(
				'EXTRA'=>$extra,
				'MESSAGE'=>lorem_phrase(),
				'TITLE'=>lorem_title(),
				'FIELDS'=>lorem_phrase(),
				'HIDDEN'=>'',
				'IMPORTER'=>lorem_phrase(),
				'IMPORT_LIST'=>$import_list,
				'URL'=>placeholder_url()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__xml_storage_screen()
	{
		return array(
			lorem_globalise(do_lorem_template('XML_STORAGE_SCREEN',array(
				'TITLE'=>lorem_title(),
				'IMPORT_FORM'=>placeholder_form(),
				'EXPORT_FORM'=>placeholder_form()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__xml_storage_import_results_screen()
	{
		$ops_nice=array();
		foreach (placeholder_array() as $v)
		{
			$ops_nice[]=array(
				'OP'=>lorem_word(),
				'PARAM_A'=>lorem_word_2(),
				'PARAM_B'=>lorem_word_2()
			);
		}
		return array(
			lorem_globalise(do_lorem_template('XML_STORAGE_IMPORT_RESULTS_SCREEN',array(
				'TITLE'=>lorem_title(),
				'OPS'=>$ops_nice
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__xml_storage_export_results_screen()
	{
		return array(
			lorem_globalise(do_lorem_template('XML_STORAGE_EXPORT_RESULTS_SCREEN',array(
				'TITLE'=>lorem_title(),
				'XML'=>lorem_phrase()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__import_mkportal_fcomcodepage()
	{
		return array(
			lorem_globalise(do_lorem_template('IMPORT_MKPORTAL_FCOMCODEPAGE',array(
				'TITLE'=>lorem_phrase(),
				'SUBTITLE'=>lorem_phrase(),
				'PAGE_HEADER'=>lorem_phrase(),
				'TEXT'=>lorem_sentence_html(),
				'PAGE_FOOTER'=>lorem_phrase(),
				'SIGNATURE'=>lorem_phrase()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__import_phpnuke_fcomcodepage()
	{
		return array(
			lorem_globalise(do_lorem_template('IMPORT_PHPNUKE_FCOMCODEPAGE',array(
				'TITLE'=>lorem_phrase(),
				'SUBTITLE'=>lorem_phrase(),
				'PAGE_HEADER'=>lorem_phrase(),
				'TEXT'=>lorem_sentence_html(),
				'PAGE_FOOTER'=>lorem_phrase(),
				'SIGNATURE'=>lorem_phrase()
			)), NULL, '', true)
		);
	}
}
