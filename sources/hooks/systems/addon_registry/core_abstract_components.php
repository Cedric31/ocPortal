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
 * @package		core_abstract_components
 */

class Hook_addon_registry_core_abstract_components
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
		return 'Core rendering functionality.';
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
			'sources/hooks/systems/addon_registry/core_abstract_components.php',
			'CROP_TEXT_MOUSE_OVER.tpl',
			'CROP_TEXT_MOUSE_OVER_INLINE.tpl',
			'IMG_THUMB.tpl',
			'POST.tpl',
			'POST_CHILD_LOAD_LINK.tpl',
			'SCREEN_BUTTON.tpl',
			'SCREEN_ITEM_BUTTON.tpl',
			'STANDARDBOX_default.tpl',
			'STANDARDBOX_accordion.tpl',
			'REVISION_HISTORY_LINE.tpl',
			'REVISION_HISTORY_WRAP.tpl',
			'REVISION_RESTORE.tpl',
			'HANDLE_CONFLICT_RESOLUTION.tpl',
			'FRACTIONAL_EDIT.tpl',
			'JAVASCRIPT_FRACTIONAL_EDIT.tpl',
			'data/fractional_edit.php',
			'data/edit_ping.php',
			'data/change_detection.php',
			'STAFF_ACTIONS.tpl',
			'sources/hooks/systems/change_detection/.htaccess',
			'sources/hooks/systems/change_detection/index.html'
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
			'REVISION_HISTORY_LINE.tpl'=>'administrative__show_revision_history',
			'REVISION_HISTORY_WRAP.tpl'=>'administrative__show_revision_history',
			'REVISION_RESTORE.tpl'=>'administrative__revision_restore',
			'SCREEN_ITEM_BUTTON.tpl'=>'screen_item_button',
			'FRACTIONAL_EDIT.tpl'=>'administrative__fractional_edit',
			'CROP_TEXT_MOUSE_OVER_INLINE.tpl'=>'crop_text_mouse_over_inline',
			'IMG_THUMB.tpl'=>'img_thumb',
			'CROP_TEXT_MOUSE_OVER.tpl'=>'crop_text_mouse_over',
			'SCREEN_BUTTON.tpl'=>'screen_button',
			'STANDARDBOX_default.tpl'=>'standardbox_default',
			'STANDARDBOX_accordion.tpl'=>'standardbox_accordion',
			'HANDLE_CONFLICT_RESOLUTION.tpl'=>'administrative__handle_conflict_resolution',
			'STAFF_ACTIONS.tpl'=>'staff_actions'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__show_revision_history()
	{
		$revision_history=do_lorem_template('REVISION_HISTORY_LINE', array(
			'RENDERED_DIFF'=>lorem_phrase(),
			'EDITOR'=>lorem_phrase(),
			'DATE'=>placeholder_time(),
			'DATE_RAW'=>placeholder_date_raw(),
			'RESTORE_URL'=>placeholder_url(),
			'URL'=>placeholder_url(),
			'SIZE'=>placeholder_filesize()
		));

		return array(
			lorem_globalise(do_lorem_template('REVISION_HISTORY_WRAP', array(
				'CONTENT'=>$revision_history
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
	function tpl_preview__staff_actions()
	{
		return array(
			lorem_globalise(do_lorem_template('STAFF_ACTIONS', array(
				'1_TITLE'=>lorem_phrase(),
				'1_URL'=>placeholder_url(),
				'2_TITLE'=>lorem_phrase(),
				'2_URL'=>placeholder_url(),
				'3_TITLE'=>lorem_phrase(),
				'3_URL'=>placeholder_url(),
				'4_TITLE'=>lorem_phrase(),
				'4_URL'=>placeholder_url()
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
	function tpl_preview__administrative__revision_restore()
	{
		return array(
			lorem_globalise(do_lorem_template('REVISION_RESTORE', array()), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__screen_item_button()
	{
		return array(
			lorem_globalise(do_lorem_template('SCREEN_ITEM_BUTTON', array(
				'REL'=>lorem_phrase(),
				'IMMEDIATE'=>lorem_phrase(),
				'URL'=>placeholder_url(),
				'TITLE'=>lorem_word(),
				'IMG'=>'edit'
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
	function tpl_preview__administrative__fractional_edit()
	{
		return array(
			lorem_globalise(do_lorem_template('FRACTIONAL_EDIT', array(
				'VALUE'=>lorem_phrase(),
				'URL'=>placeholder_url(),
				'EDIT_TEXT'=>lorem_sentence_html(),
				'EDIT_PARAM_NAME'=>lorem_word_html()
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
	function tpl_preview__crop_text_mouse_over_inline()
	{
		return array(
			lorem_globalise(do_lorem_template('CROP_TEXT_MOUSE_OVER_INLINE', array(
				'TEXT_SMALL'=>lorem_sentence_html(),
				'TEXT_LARGE'=>lorem_sentence_html()
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
	function tpl_preview__img_thumb()
	{
		return array(
			lorem_globalise(do_lorem_template('IMG_THUMB', array(
				'JS_TOOLTIP'=>lorem_phrase(),
				'CAPTION'=>lorem_phrase(),
				'URL'=>placeholder_image_url()
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
	function tpl_preview__crop_text_mouse_over()
	{
		return array(
			lorem_globalise(do_lorem_template('CROP_TEXT_MOUSE_OVER', array(
				'TEXT_LARGE'=>lorem_phrase(),
				'TEXT_SMALL'=>lorem_phrase()
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
	function tpl_preview__screen_button()
	{
		$img="login";
		return array(
			lorem_globalise(do_lorem_template('SCREEN_BUTTON', array(
				'IMMEDIATE'=>true,
				'URL'=>placeholder_url(),
				'TITLE'=>lorem_word(),
				'IMG'=>$img
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
	function tpl_preview__standardbox_default()
	{
		return $this->_tpl_preview__standardbox('default');
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__standardbox_accordion()
	{
		return $this->_tpl_preview__standardbox('accordion');
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @param  string			View type.
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function _tpl_preview__standardbox($type)
	{
		$links=array();
		foreach (placeholder_array() as $k=>$v)
		{
			$links[]=placeholder_link();
		}

		$meta=array();
		foreach (placeholder_array() as $k=>$v)
		{
			$meta[]=array(
				'KEY'=>strval($k),
				'VALUE'=>$v
			);
		}

		$boxes=new ocp_tempcode();
		$box=do_lorem_template('STANDARDBOX_' . $type, array(
				'CONTENT'=>lorem_sentence(),
				'LINKS'=>$links,
				'META'=>$meta,
				'OPTIONS'=>placeholder_array(),
				'TITLE'=>lorem_phrase(),
				'TOP_LINKS'=>placeholder_link(),
				'WIDTH'=>'',
		));
		$boxes->attach($box);
		$box=do_lorem_template('STANDARDBOX_' . $type, array(
				'CONTENT'=>lorem_sentence(),
				'LINKS'=>$links,
				'META'=>$meta,
				'OPTIONS'=>placeholder_array(),
				'TITLE'=>'',
				'TOP_LINKS'=>placeholder_link(),
				'WIDTH'=>'',
		));
		$boxes->attach($box);

		return array(
			lorem_globalise($boxes, NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__handle_conflict_resolution()
	{
		return array(
			lorem_globalise(do_lorem_template('HANDLE_CONFLICT_RESOLUTION', array()))
		);
	}
}
