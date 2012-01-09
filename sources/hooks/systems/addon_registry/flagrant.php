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
 * @package		flagrant
 */

class Hook_addon_registry_flagrant
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
		return 'Flagrant text messages, designed to work with the pointstore to allow members to buy text advertising on the website.';
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
			'recommends'=>array('pointstore'),
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

			'sources/hooks/systems/config_default/system_flagrant.php',
			'themes/default/images/pagepics/flagrant.png',
			'sources/hooks/systems/addon_registry/flagrant.php',
			'sources/hooks/modules/admin_import_types/flagrant.php',
			'FLAGRANT_DETAILS.tpl',
			'FLAGRANT_STORE_LIST_LINE.tpl',
			'adminzone/pages/modules/admin_flagrant.php',
			'lang/EN/flagrant.ini',
			'sources/flagrant.php',
			'sources/hooks/blocks/main_staff_checklist/flagrant.php',
			'sources/hooks/modules/pointstore/flagrant.php',
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
					'FLAGRANT_DETAILS.tpl'=>'administrative__flagrant_manage_screen',
					'FLAGRANT_STORE_LIST_LINE.tpl'=>'administrative__flagrant_manage_screen',
				);
	}

   /**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__flagrant_manage_screen()
	{
		$about_current = do_lorem_template('FLAGRANT_DETAILS',array(
								                                          'USERNAME'=>lorem_word_html(),
								                                          'DAYS_ORDERED'=>lorem_phrase(),
								                                          'DATE_RAW'=>placeholder_time(),
								                                          'DATE'=>placeholder_time(),
			                                                		   )
                         				  );

		$out = new ocp_tempcode();
		foreach(placeholder_array() as $key=>$value)
		{
			$text = do_lorem_template('FLAGRANT_STORE_LIST_LINE',array('MESSAGE'=>$value,'STATUS'=>do_lang('NEW')));
		   $out->attach(do_lorem_template('FORM_SCREEN_INPUT_LIST_ENTRY',array('SELECTED'=>false,'DISABLED'=>false,'CLASS'=>'','NAME'=>strval($key),'TEXT'=>$text->evaluate())));
		}

		$input = do_lorem_template('FORM_SCREEN_INPUT_LIST',array('TABINDEX'=>'5','REQUIRED'=>'_required','NAME'=>lorem_word(),'CONTENT'=>$out,'INLINE_LIST'=>true));
		$fields = do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'BORING_NAME'=>lorem_word(),'NAME'=>lorem_word(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>''));

		//Create 'FLAGRANT_MANAGE_SCREEN' using the sub-templates 'FLAGRANT_DETAILS' and 'FLAGRANT_STORE_LIST_LINE'
		return array(
			lorem_globalise(
				do_lorem_template('FORM_SCREEN',array(
					'TITLE'=>lorem_title(),
					'TEXT'=>$about_current,
					'HIDDEN'=>'',
					'URL'=>placeholder_url(),
					'GET'=>true,
					'FIELDS'=>$fields,
					'SUBMIT_NAME'=>lorem_word(),
					)
			),NULL,'',true)
		);
	}
}