<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		banners
 */

class Hook_addon_registry_banners
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
		return 'An advanced banner system, with support for multiple banner rotations, commercial banner campaigns, and webring-style systems. Support for graphical, text, and flash banners. Hotword activation support.';
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
			'banners.css',
			'sources/hooks/systems/snippets/exists_banner.php',
			'sources/hooks/systems/snippets/exists_banner_type.php',
			'sources/hooks/systems/config_default/admin_banners.php',
			'sources/hooks/systems/config_default/banner_autosize.php',
			'sources/hooks/systems/config_default/points_ADD_BANNER.php',
			'sources/hooks/systems/config_default/use_banner_permissions.php',
			'sources/hooks/systems/realtime_rain/banners.php',
			'adminzone/pages/modules/admin_banners.php',
			'uploads/banners/.htaccess',
			'BANNER_PREVIEW.tpl',
			'BANNERS_NONE.tpl',
			'sources/hooks/systems/preview/banner.php',
			'sources/hooks/modules/admin_import_types/banners.php',
			'sources/hooks/systems/addon_registry/banners.php',
			'BANNER_FLASH.tpl',
			'BANNER_TEXT.tpl',
			'BANNER_VIEW_SCREEN.tpl',
			'BANNER_IFRAME.tpl',
			'BANNER_IMAGE.tpl',
			'BANNER_SHOW_CODE.tpl',
			'BANNER_ADDED_SCREEN.tpl',
			'BLOCK_MAIN_TOPSITES.tpl',
			'BLOCK_MAIN_BANNER_WAVE.tpl',
			'BLOCK_MAIN_BANNER_WAVE_BWRAP.tpl',
			'banner.php',
			'uploads/banners/index.html',
			'themes/default/images/bigicons/banners.png',
			'themes/default/images/pagepics/banners.png',
			'cms/pages/modules/cms_banners.php',
			'lang/EN/banners.ini',
			'site/pages/modules/banners.php',
			'sources/banners.php',
			'sources/banners2.php',
			'sources/blocks/main_topsites.php',
			'sources/blocks/main_banner_wave.php',
			'sources/hooks/modules/admin_setupwizard/banners.php',
			'sources/hooks/modules/admin_unvalidated/banners.php',
			'sources/hooks/modules/pointstore/banners.php',
			'POINTSTORE_BANNERS_SCREEN.tpl',
			'POINTSTORE_BANNERS_2.tpl',
			'POINTSTORE_BANNERS_ACTIVATE.tpl',
			'POINTSTORE_BANNERS_UPGRADE.tpl',
			'sources/hooks/systems/do_next_menus/banners.php',
			'sources/hooks/systems/content_meta_aware/banner.php',
			'sources/hooks/systems/content_meta_aware/banner_type.php',
			'data/images/advertise_here.png',
			'data/images/donate.png',
			'site/pages/comcode/EN/advertise.txt',
			'site/pages/comcode/EN/donate.txt',
			'sources/hooks/systems/block_ui_renderers/banners.php',
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
			'BANNER_PREVIEW.tpl'=>'banner_preview',
			'BANNER_SHOW_CODE.tpl'=>'banner_show_code',
			'BANNER_ADDED_SCREEN.tpl'=>'administrative__banner_added_screen',
			'BLOCK_MAIN_TOPSITES.tpl'=>'block_main_topsites',
			'BLOCK_MAIN_BANNER_WAVE_BWRAP.tpl'=>'block_main_banner_wave',
			'BLOCK_MAIN_BANNER_WAVE.tpl'=>'block_main_banner_wave',
			'BANNERS_NONE.tpl'=>'banners_none',
			'BANNER_FLASH.tpl'=>'banner_flash',
			'BANNER_IMAGE.tpl'=>'banner_image',
			'BANNER_IFRAME.tpl'=>'banner_iframe',
			'BANNER_TEXT.tpl'=>'banner_text',
			'POINTSTORE_BANNERS_2.tpl'=>'pointstore_banners_2',
			'POINTSTORE_BANNERS_UPGRADE.tpl'=>'pointstore_banners_upgrade',
			'POINTSTORE_BANNERS_ACTIVATE.tpl'=>'pointstore_banners_activate',
			'POINTSTORE_BANNERS_SCREEN.tpl'=>'pointstore_banners_screen',
			'BANNER_VIEW_SCREEN.tpl'=>'administrative__banner_view_screen'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__banner_preview()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNER_PREVIEW', array(
				'PREVIEW'=>lorem_phrase()
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
	function tpl_preview__banner_show_code()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNER_SHOW_CODE', array(
				'NAME'=>lorem_word(),
				'WIDTH'=>placeholder_number(),
				'HEIGHT'=>placeholder_number(),
				'TYPE'=>lorem_word()
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
	function tpl_preview__administrative__banner_added_screen()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNER_ADDED_SCREEN', array(
				'TITLE'=>lorem_title(),
				'TEXT'=>lorem_sentence_html(),
				'BANNER_CODE'=>lorem_phrase(),
				'STATS_URL'=>placeholder_url(),
				'DO_NEXT'=>lorem_phrase()
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
	function tpl_preview__block_main_topsites()
	{
		return array(
			lorem_globalise(do_lorem_template('BLOCK_MAIN_TOPSITES', array(
				'TYPE'=>lorem_phrase(),
				'BANNERS'=>placeholder_array(),
				'SUBMIT_URL'=>placeholder_url(),
				'DESCRIPTION'=>lorem_word(),
				'BANNER'=>lorem_word_2(),
				'HITSFROM'=>placeholder_number(),
				'HITSTO'=>placeholder_number()
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
	function tpl_preview__block_main_banner_wave()
	{
		$banners=new ocp_tempcode();
		$banners->attach(do_lorem_template('BANNER_FLASH', array(
			'B_TYPE'=>lorem_phrase(),
			'WIDTH'=>placeholder_number(),
			'HEIGHT'=>placeholder_number(),
			'SOURCE'=>lorem_phrase(),
			'DEST'=>lorem_phrase(),
			'CAPTION'=>lorem_phrase(),
			'IMG'=>placeholder_image_url()
		)));
		$banners->attach(do_lorem_template('BANNER_IMAGE', array(
			'URL'=>placeholder_url(),
			'B_TYPE'=>lorem_phrase(),
			'WIDTH'=>placeholder_number(),
			'HEIGHT'=>placeholder_number(),
			'SOURCE'=>lorem_phrase(),
			'DEST'=>lorem_phrase(),
			'CAPTION'=>lorem_phrase(),
			'IMG'=>placeholder_image_url()
		)));
		$banners->attach(do_lorem_template('BANNER_IFRAME', array(
			'B_TYPE'=>lorem_phrase(),
			'IMG'=>placeholder_image_url(),
			'WIDTH'=>placeholder_number(),
			'HEIGHT'=>placeholder_number()
		)));

		$assemble=do_lorem_template('BLOCK_MAIN_BANNER_WAVE_BWRAP', array(
			'EXTRA'=>lorem_phrase(),
			'TYPE'=>lorem_phrase(),
			'BANNER'=>$banners,
			'MORE_COMING'=>lorem_phrase()
		));

		return array(
			lorem_globalise(do_lorem_template('BLOCK_MAIN_BANNER_WAVE', array(
				'EXTRA'=>lorem_phrase(),
				'TYPE'=>lorem_phrase(),
				'ASSEMBLE'=>$assemble
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
	function tpl_preview__banners_none()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNERS_NONE', array(
				'ADD_BANNER_URL'=>placeholder_url()
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
	function tpl_preview__banner_flash()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNER_FLASH', array(
				'B_TYPE'=>lorem_phrase(),
				'WIDTH'=>placeholder_number(),
				'HEIGHT'=>placeholder_number(),
				'SOURCE'=>lorem_phrase(),
				'DEST'=>lorem_phrase(),
				'CAPTION'=>lorem_phrase(),
				'IMG'=>placeholder_image_url()
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
	function tpl_preview__banner_image()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNER_IMAGE', array(
				'URL'=>placeholder_url(),
				'B_TYPE'=>lorem_phrase(),
				'WIDTH'=>placeholder_number(),
				'HEIGHT'=>placeholder_number(),
				'SOURCE'=>lorem_phrase(),
				'DEST'=>lorem_phrase(),
				'CAPTION'=>lorem_phrase(),
				'IMG'=>placeholder_image_url()
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
	function tpl_preview__banner_iframe()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNER_IFRAME', array(
				'B_TYPE'=>lorem_phrase(),
				'IMG'=>placeholder_image_url(),
				'WIDTH'=>placeholder_number(),
				'HEIGHT'=>placeholder_number()
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
	function tpl_preview__banner_text()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNER_TEXT', array(
				'B_TYPE'=>lorem_phrase(),
				'TITLE_TEXT'=>lorem_phrase(),
				'CAPTION'=>lorem_phrase(),
				'SOURCE'=>lorem_phrase(),
				'DEST'=>lorem_phrase(),
				'URL'=>placeholder_url(),
				'FILTERED_URL'=>placeholder_url()
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
	function tpl_preview__pointstore_banners_2()
	{
		require_lang('pointstore');
		return array(
			lorem_globalise(do_lorem_template('POINTSTORE_BANNERS_2', array(
				'BANNER_URL'=>placeholder_url()
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
	function tpl_preview__pointstore_banners_upgrade()
	{
		require_lang('pointstore');
		return array(
			lorem_globalise(do_lorem_template('POINTSTORE_BANNERS_UPGRADE', array(
				'UPGRADE_URL'=>placeholder_url()
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
	function tpl_preview__pointstore_banners_activate()
	{
		require_lang('pointstore');
		return array(
			lorem_globalise(do_lorem_template('POINTSTORE_BANNERS_ACTIVATE', array(
				'ACTIVATE_URL'=>placeholder_url()
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
	function tpl_preview__pointstore_banners_screen()
	{
		require_lang('pointstore');
		return array(
			lorem_globalise(do_lorem_template('POINTSTORE_BANNERS_SCREEN', array(
				'TITLE'=>lorem_title(),
				'ACTIVATE'=>lorem_phrase(),
				'UPGRADE'=>lorem_phrase()
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
	function tpl_preview__administrative__banner_view_screen()
	{
		return array(
			lorem_globalise(do_lorem_template('BANNER_VIEW_SCREEN', array(
				'TITLE'=>lorem_title(),
				'EDIT_URL'=>placeholder_url(),
				'MAP_TABLE'=>lorem_phrase(),
				'BANNER'=>lorem_phrase(),
				'NAME'=>placeholder_id(),
			)), NULL, '', true)
		);
	}
}
