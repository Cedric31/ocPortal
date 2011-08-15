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
 * @package		quizzes
 */

class Hook_addon_registry_quizzes
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
		return 'Construct competitions, surveys, and tests, for members to perform. Highly configurable, and comes with administrative tools to handle the results.';
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

			'sources/hooks/systems/config_default/points_ADD_QUIZ.php',
			'sources/hooks/systems/config_default/quiz_show_stats_count_total_open.php',
			'sources/hooks/systems/meta/quiz.php',
			'themes/default/images/bigicons/findwinners.png',
			'themes/default/images/pagepics/findwinners.png',
			'sources/hooks/blocks/side_stats/stats_quiz.php',
			'QUIZ_ANSWERS_MAIL.tpl',
			'QUIZ_ARCHIVE_SCREEN.tpl',
			'QUIZ_TEST_ANSWERS_MAIL.tpl',
			'sources/hooks/systems/content_meta_aware/quiz.php',
			'sources/hooks/systems/addon_registry/quizzes.php',
			'sources/hooks/modules/admin_import_types/quizzes.php',
			'QUIZ_LINK.tpl',
			'QUIZ_SCREEN.tpl',
			'QUIZ_DONE_SCREEN.tpl',
			'SURVEY_RESULTS_SCREEN.tpl',
			'adminzone/pages/modules/admin_quiz.php',
			'themes/default/images/bigicons/quiz.png',
			'themes/default/images/pagepics/quiz.png',
			'cms/pages/modules/cms_quiz.php',
			'lang/EN/quiz.ini',
			'site/pages/modules/quiz.php',
			'sources/hooks/modules/admin_newsletter/quiz.php',
			'sources/hooks/modules/admin_unvalidated/quiz.php',
			'sources/hooks/modules/search/quiz.php',
			'sources/hooks/systems/awards/quiz.php',
			'sources/hooks/systems/do_next_menus/quiz.php',
			'sources/quiz.php',
			'themes/default/images/bigicons/survey_results.png',
			'themes/default/images/pagepics/survey_results.png',
			'sources/hooks/systems/preview/quiz.php',
			'quizzes.css',
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
				'SURVEY_RESULTS_SCREEN.tpl'=>'survey_results_screen',
				'QUIZ_LINK.tpl'=>'quiz_archive_screen',
				'QUIZ_ARCHIVE_SCREEN.tpl'=>'quiz_archive_screen',
				'QUIZ_SCREEN.tpl'=>'quiz_screen',
				'QUIZ_TEST_ANSWERS_MAIL.tpl'=>'quiz_test_answers_mail',
				'QUIZ_ANSWERS_MAIL.tpl'=>'quiz_answers_mail',
				'QUIZ_DONE_SCREEN.tpl'=>'quiz_done_screen',
				);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__survey_results_screen()
	{
		$fields = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$fields->attach(do_lorem_template('VIEW_SPACE_FIELD_RAW',array('ABBR'=>'','NAME'=>lorem_phrase(),'VALUE'=>lorem_phrase())));
		}
		$summary = do_lorem_template('VIEW_SPACE',array('WIDTH'=>placeholder_number(),'FIELDS'=>$fields));

		$browse = do_lorem_template('NEXT_BROWSER_BROWSE_NEXT',array('PREVIOUS_LINK'=>placeholder_link(),'NEXT_LINK'=>placeholder_link(),'PAGE_NUM'=>placeholder_number(),'NUM_PAGES'=>placeholder_number()));

		$cells = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>$v)));
		}
		$fields_title = $cells;

		$results = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells = new ocp_tempcode();
			foreach (placeholder_array() as $k=>$v)
			{
				$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>lorem_word()),NULL,false,'RESULTS_TABLE_FIELD'));
			}
			$results->attach(do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false,'RESULTS_TABLE_ENTRY'));
		}

		$selectors = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$selectors->attach(do_lorem_template('RESULTS_BROWSER_SORTER',array('SELECTED'=>'','NAME'=>lorem_word(),'VALUE'=>lorem_word())));
		}
		$sort = do_lorem_template('RESULTS_BROWSER_SORT',array('HIDDEN'=>'','SORT'=>lorem_word(),'RAND'=>placeholder_random(),'URL'=>placeholder_url(),'SELECTORS'=>$selectors));

		$results_browser = placeholder_result_browser();

		$results = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$results,'MESSAGE'=>new ocp_tempcode(),'SORT'=>$sort,'BROWSER'=>$results_browser,'WIDTHS'=>array()),NULL,false,'RESULTS_TABLE');

		return array(
			lorem_globalise(
				do_lorem_template('SURVEY_RESULTS_SCREEN',array(
					'TITLE'=>lorem_title(),
					'SUMMARY'=>$summary,
					'RESULTS'=>$results,
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
	function tpl_preview__quiz_archive_screen()
	{
		$content_tests=new ocp_tempcode();
		$content_competitions=new ocp_tempcode();
		$content_surveys=new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$link = do_lorem_template('QUIZ_LINK',array('TYPE'=>lorem_word(),'DATE'=>placeholder_time(),'URL'=>placeholder_url(),'NAME'=>lorem_phrase(),'START_TEXT'=>lorem_phrase(),'TIMEOUT'=>placeholder_number(),'REDO_TIME'=>placeholder_number(),'_TYPE'=>lorem_word(),'POINTS'=>placeholder_id()));
		}
		$content_surveys->attach($link);
		$content_tests->attach($link);
		$content_competitions->attach($link);

		$browse = do_lorem_template('NEXT_BROWSER_BROWSE_NEXT',array('NEXT_LINK'=>placeholder_url(),'PREVIOUS_LINK'=>placeholder_url(),'PAGE_NUM'=>placeholder_number(),'NUM_PAGES'=>placeholder_number()));

		return array(
			lorem_globalise(
				do_lorem_template('QUIZ_ARCHIVE_SCREEN',array(
					'TITLE'=>lorem_title(),
					'CONTENT_SURVEYS'=>$content_surveys,
					'CONTENT_COMPETITIONS'=>$content_competitions,
					'CONTENT_TESTS'=>$content_tests,
					'BROWSE'=>$browse,
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
	function tpl_preview__quiz_screen()
	{
		//This is for getting the load_XML_doc() javascript function.
		require_javascript('javascript_ajax');

		$warning_details = do_lorem_template('WARNING_TABLE',array('WARNING'=>lorem_phrase()));

		return array(
			lorem_globalise(
				do_lorem_template('QUIZ_SCREEN',array(
					'TAGS'=>lorem_word_html(),
					'ID'=>placeholder_id(),
					'WARNING_DETAILS'=>$warning_details,
					'URL'=>placeholder_url(),
					'TITLE'=>lorem_title(),
					'START_TEXT'=>lorem_sentence_html(),
					'FIELDS'=>placeholder_fields(),
					'TIMEOUT'=>'5',
					'EDIT_URL'=>placeholder_url(),
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
	function tpl_preview__quiz_test_answers_mail()
	{
		$_unknowns = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$_unknowns->attach(lorem_phrase());
		}

		$_corrections = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$_corrections->attach(lorem_phrase());
		}

		return array(
			lorem_globalise(
				do_lorem_template('QUIZ_TEST_ANSWERS_MAIL',array(
					'UNKNOWNS'=>$_unknowns,
					'CORRECTIONS'=>$_corrections,
					'RESULT'=>lorem_phrase(),
					'USERNAME'=>lorem_phrase(),
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
	function tpl_preview__quiz_answers_mail()
	{
		$_answers=new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$_answers->attach(lorem_phrase());
		}

		return array(
			lorem_globalise(
				do_lorem_template('QUIZ_ANSWERS_MAIL',array(
					'ANSWERS'=>$_answers,
					'MEMBER_PROFILE_URL'=>placeholder_url(),
					'USERNAME'=>lorem_phrase(),
					'FORUM_DRIVER'=>NULL,
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
	function tpl_preview__quiz_done_screen()
	{
		return array(
			lorem_globalise(
				do_lorem_template('QUIZ_DONE_SCREEN',array(
					'RESULT'=>lorem_phrase(),
					'TITLE'=>lorem_title(),
					'TYPE'=>lorem_phrase(),
					'MESSAGE'=>lorem_phrase(),
					'CORRECTIONS_TO_SHOW'=>lorem_phrase(),
					'POINTS_DIFFERENCE'=>placeholder_number(),
				)
			),NULL,'',true),
		);
	}
}