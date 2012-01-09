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
 * @package		installer
 */

class Hook_addon_registry_installer
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
		return 'The installer files (can be removed immediately after installing; in-fact ocPortal makes you remove install.php manually).';
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

			'sources/hooks/systems/addon_registry/installer.php',
			'INSTALLER_FORUM_CHOICE.tpl',
			'INSTALLER_FORUM_CHOICE_VERSION.tpl',
			'INSTALLER_STEP_4_SECTION.tpl',
			'INSTALLER_STEP_4_SECTION_HIDE.tpl',
			'INSTALLER_STEP_4_SECTION_OPTION.tpl',
			'INSTALLER_WRAP.tpl',
			'INSTALLER_WARNING_LONG.tpl',
			'INSTALLER_DONE_SOMETHING.tpl',
			'INSTALLER_INPUT_LINE.tpl',
			'INSTALLER_INPUT_PASSWORD.tpl',
			'INSTALLER_INPUT_TICK.tpl',
			'INSTALLER_STEP_1.tpl',
			'INSTALLER_STEP_2.tpl',
			'INSTALLER_STEP_3.tpl',
			'INSTALLER_STEP_4.tpl',
			'INSTALLER_STEP_LOG.tpl',
			'INSTALLER_STEP_10.tpl',
			'INSTALLER_WARNING.tpl',
			'INSTALLER_NOTICE.tpl',
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
				'INSTALLER_WRAP.tpl'=>'administrative__installer_wrap',
				'INSTALLER_WARNING.tpl'=>'administrative__installer_step_1',
				'INSTALLER_WARNING_LONG.tpl'=>'administrative__installer_step_1',
				'INSTALLER_NOTICE.tpl'=>'administrative__installer_step_1',
				'INSTALLER_STEP_1.tpl'=>'administrative__installer_step_1',
				'INSTALLER_STEP_2.tpl'=>'administrative__installer_step_2',
				'INSTALLER_FORUM_CHOICE_VERSION.tpl'=>'administrative__installer_step_3',
				'INSTALLER_FORUM_CHOICE.tpl'=>'administrative__installer_step_3',
				'INSTALLER_STEP_3.tpl'=>'administrative__installer_step_3',
				'INSTALLER_STEP_4_SECTION.tpl'=>'administrative__installer_step_4',
				'INSTALLER_STEP_4_SECTION_HIDE.tpl'=>'administrative__installer_step_4',
				'INSTALLER_STEP_4.tpl'=>'administrative__installer_step_4',
				'INSTALLER_STEP_LOG.tpl'=>'administrative__installer_step_log',
				'INSTALLER_DONE_SOMETHING.tpl'=>'administrative__installer_step_log',
				'INSTALLER_STEP_10.tpl'=>'administrative__installer_step_10',
				'INSTALLER_INPUT_PASSWORD.tpl'=>'administrative__installer_step_4',
				'INSTALLER_STEP_4_SECTION_OPTION.tpl'=>'administrative__installer_step_4',
				'INSTALLER_INPUT_LINE.tpl'=>'administrative__installer_step_4',
				'INSTALLER_INPUT_TICK.tpl'=>'administrative__installer_step_4',
				);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__installer_wrap()
	{
		require_css('install');
		require_lang('installer');
		require_lang('version');
		return array(
			lorem_globalise(
				do_lorem_template('INSTALLER_WRAP',array(
					'CSS_NOCACHE'=>".nocss{}",
					'DEFAULT_FORUM'=>lorem_phrase(),
					'PASSWORD_PROMPT'=>lorem_phrase(),
					'CSS_URL'=>get_base_url().'/themes/default/css/installer.css',
					'CSS_URL_2'=>get_base_url().'/themes/default/css/installer.css',
					'LOGO_URL'=>placeholder_image_url(),
					'STEP'=>'1',
					'CONTENT'=>lorem_paragraph_html(),
					'VERSION'=>lorem_phrase(),
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
	function tpl_preview__administrative__installer_step_1()
	{
		require_css('install');
		require_lang('installer');
		require_lang('version');

		$warning = do_lorem_template('INSTALLER_WARNING_LONG',array(
					'FILES'=>placeholder_array(),
					'MESSAGE'=>lorem_sentence_html(),
						));
		$warning->attach(do_lorem_template('INSTALLER_WARNING',array(
					'MESSAGE'=>lorem_sentence_html(),
						)));
		$warning->attach(do_lorem_template('INSTALLER_NOTICE',array(
					'MESSAGE'=>lorem_sentence_html(),
						)));
		$languages=new ocp_tempcode();
		foreach (placeholder_array() as $lang)
		{
			$entry=do_lorem_template('FORM_SCREEN_INPUT_LIST_ENTRY',array('SELECTED'=>false,'DISABLED'=>false,'NAME'=>$lang,'CLASS'=>'','TEXT'=>$lang));
			$languages->attach($entry);
		}
		$content = do_lorem_template('INSTALLER_STEP_1',array(
					'WARNINGS'=>$warning,
					'HIDDEN'=>'',
					'LANGUAGES'=>$languages,
						)
			);
		return array(
			lorem_globalise(
				do_lorem_template('INSTALLER_WRAP',array(
					'CSS_NOCACHE'=>".nocss{}",
					'DEFAULT_FORUM'=>lorem_phrase(),
					'PASSWORD_PROMPT'=>lorem_phrase(),
					'CSS_URL'=>get_base_url().'/themes/default/css/installer.css',
					'CSS_URL_2'=>get_base_url().'/themes/default/css/installer.css',
					'LOGO_URL'=>placeholder_image_url(),
					'STEP'=>'1',
					'CONTENT'=>$content,
					'VERSION'=>lorem_phrase(),
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
	function tpl_preview__administrative__installer_step_2()
	{
		require_css('install');
		require_lang('installer');
		require_lang('version');
		$content = do_lorem_template('INSTALLER_STEP_2',array(
					'HIDDEN'=>'',
					'LICENCE'=>lorem_chunk(),
						)
			);
		return array(
			lorem_globalise(
				do_lorem_template('INSTALLER_WRAP',array(
					'CSS_NOCACHE'=>".nocss{}",
					'DEFAULT_FORUM'=>lorem_phrase(),
					'PASSWORD_PROMPT'=>lorem_phrase(),
					'CSS_URL'=>get_base_url().'/themes/default/css/installer.css',
					'CSS_URL_2'=>get_base_url().'/themes/default/css/installer.css',
					'LOGO_URL'=>placeholder_image_url(),
					'STEP'=>'1',
					'CONTENT'=>$content,
					'VERSION'=>lorem_phrase(),
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
	function tpl_preview__administrative__installer_step_3()
	{
		require_css('install');
		require_lang('installer');
		require_lang('version');
		$forum_array	=	array(array('1'=>'forum1','2'=>'forum2'));
		$tforums=new ocp_tempcode();
		$default_version=new ocp_tempcode();
		$simple_forums=new ocp_tempcode();
		foreach ($forum_array as $key=>$forums)
		{
			$versions=new ocp_tempcode();
			foreach ($forums as $key=>$forum)
			{
				$version=do_lang('VERSION_NUM',$key);
				$versions->attach(do_lorem_template('INSTALLER_FORUM_CHOICE_VERSION',array('IS_DEFAULT'=>false,'CLASS'=>'','NAME'=>$forum,'VERSION'=>$version,'EXTRA'=>'')));
				$simple_forums->attach(do_lorem_template('INSTALLER_FORUM_CHOICE_VERSION',array('IS_DEFAULT'=>false,'CLASS'=>'','NAME'=>$forum,'VERSION'=>$version,'EXTRA'=>'')));
			}
			$tforums->attach(do_lorem_template('INSTALLER_FORUM_CHOICE',array('CLASS'=>'','REC'=>'','TEXT'=>lorem_phrase(),'VERSIONS'=>$versions,'EXTRA'=>'')));
		}

		// Database chooser
		$tdatabase	= new ocp_tempcode();
		foreach (placeholder_array() as $dbname)
		{
			$entry=do_lorem_template('FORM_SCREEN_INPUT_LIST_ENTRY',array('SELECTED'=>false,'DISABLED'=>false,'NAME'=>$dbname,'CLASS'=>'','TEXT'=>$dbname));
			$tdatabase->attach($entry);
		}

		$step3	=	do_lorem_template('INSTALLER_STEP_3',array('JS'=>'','HIDDEN'=>'','SIMPLE_FORUMS'=>$simple_forums,'FORUM_PATH_DEFAULT'=>get_file_base().'/forums','FORUMS'=>$tforums,'DATABASES'=>$tdatabase,'VERSION'=>$default_version));

		return array(
			lorem_globalise(
				do_lorem_template('INSTALLER_WRAP',array(
					'CSS_NOCACHE'=>".nocss{}",
					'DEFAULT_FORUM'=>lorem_phrase(),
					'PASSWORD_PROMPT'=>lorem_phrase(),
					'CSS_URL'=>get_base_url().'/themes/default/css/installer.css',
					'CSS_URL_2'=>get_base_url().'/themes/default/css/installer.css',
					'LOGO_URL'=>placeholder_image_url(),
					'STEP'=>'1',
					'CONTENT'=>$step3,
					'VERSION'=>lorem_phrase(),
						)
			),NULL,'',true),
		);
	}

	/**
	 * Make the UI for an installer tick option.
	 *
	 * @param  tempcode		The human readable name for the option
	 * @param  tempcode		A description of the option
	 * @param  ID_TEXT		The name of the option
	 * @param  BINARY			The default/current value of the option
	 * @return tempcode		The list of usergroups
	 */
	function make_tick($nice_name,$description,$name,$value)
	{
		$input=do_lorem_template('INSTALLER_INPUT_TICK',array('CHECKED'=>$value==1,'NAME'=>$name));
		return do_lorem_template('INSTALLER_STEP_4_SECTION_OPTION',array('_GUID'=>'0723f86908f66da7f67ebc4cd07bff2e','NAME'=>$name,'INPUT'=>$input,'NICE_NAME'=>$nice_name,'DESCRIPTION'=>$description));
	}

	/**
	 * Make the UI for an installer textual option.
	 *
	 * @param  tempcode		The human readable name for the option
	 * @param  tempcode		A description of the option
	 * @param  ID_TEXT		The name of the option
	 * @param  string			The default/current value of the option
	 * @param  boolean		Whether the options value should be kept star'red out (e.g. it is a password)
	 * @param  boolean		Whether the option is required
	 * @return tempcode		The option
	 */
	function make_option($nice_name,$description,$name,$value,$hidden=false,$required=false)
	{
		if ($hidden)
		{
			$input = do_lorem_template('INSTALLER_INPUT_PASSWORD',array('REQUIRED'=>$required,'NAME'=>$name,'VALUE'=>$value));
		}
		else
		{
			$input = do_lorem_template('INSTALLER_INPUT_LINE',array('REQUIRED'=>$required,'NAME'=>$name,'VALUE'=>$value));
		}

		return do_lorem_template('INSTALLER_STEP_4_SECTION_OPTION',array('NAME'=>$name,'INPUT'=>$input,'NICE_NAME'=>$nice_name,'DESCRIPTION'=>$description));
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__installer_step_4()
	{
		require_css('install');
		require_lang('installer');
		require_lang('version');
		$options=new ocp_tempcode();
			$options->attach($this->make_option(lorem_phrase(),new ocp_tempcode(),'ftp_username','',false,true));
		$options->attach($this->make_option(lorem_phrase(),new ocp_tempcode(),'ftp_password','',true));

		$options->attach($this->make_tick(lorem_word(),lorem_phrase(),'tick',1));

		$section	=	do_lorem_template('INSTALLER_STEP_4_SECTION',array(
					'HIDDEN'=>'',
					'TITLE'=>lorem_word(),
					'TEXT'=>lorem_sentence_html(),
					'OPTIONS'=>$options,
						));

		$section->attach(do_lorem_template('INSTALLER_STEP_4_SECTION_HIDE',array('TITLE'=>lorem_phrase(),'CONTENT'=>lorem_phrase())));

		$content = do_lorem_template('INSTALLER_STEP_4',array(
					'JS'=>'',
					'MESSAGE'=>'',
					'LANG'=>fallback_lang(),
					'DB_TYPE'=>lorem_phrase(),
					'FORUM_TYPE'=>lorem_phrase(),
					'BOARD_PATH'=>lorem_phrase(),
					'SECTIONS'=>$section,
					'MAX'=>'1000',
						)
			);
		return array(
			lorem_globalise(
				do_lorem_template('INSTALLER_WRAP',array(
					'CSS_NOCACHE'=>".nocss{}",
					'DEFAULT_FORUM'=>lorem_phrase(),
					'PASSWORD_PROMPT'=>lorem_phrase(),
					'CSS_URL'=>get_base_url().'/themes/default/css/installer.css',
					'CSS_URL_2'=>get_base_url().'/themes/default/css/installer.css',
					'LOGO_URL'=>placeholder_image_url(),
					'STEP'=>'1',
					'CONTENT'=>$content,
					'VERSION'=>lorem_phrase(),
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
	function tpl_preview__administrative__installer_step_log()
	{
		$message = do_lorem_template('INSTALLER_DONE_SOMETHING',array(
					'SOMETHING'=>lorem_sentence(),
						));

		require_css('install');
		return array(
			lorem_globalise(
				do_lorem_template('INSTALLER_STEP_LOG',array(
					'PREVIOUS_STEP'=>lorem_phrase(),
					'URL'=>placeholder_url(),
					'LOG'=>$message,
					'HIDDEN'=>'',
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
	function tpl_preview__administrative__installer_step_10()
	{
		require_css('install');
		require_lang('installer');
		require_lang('version');

		return array(
			lorem_globalise(
				do_lorem_template('INSTALLER_STEP_10',array(
					'PREVIOUS_STEP'=>lorem_phrase(),
					'FINAL'=>lorem_phrase(),
					'LOG'=>lorem_sentence(),
						)
			),NULL,'',true),
		);
	}

}