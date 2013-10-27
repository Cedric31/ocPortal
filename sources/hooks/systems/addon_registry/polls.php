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
 * @package		polls
 */

class Hook_addon_registry_polls
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
		return 'A poll (voting) system.';
	}

	/**
	 * Get a list of tutorials that apply to this addon
	 *
	 * @return array			List of tutorials
	 */
	function get_applicable_tutorials()
	{
		return array(
			'tut_featured',
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
			'sources/polls2.php',
			'sources/hooks/systems/block_ui_renderers/polls.php',
			'sources/hooks/systems/notifications/poll_chosen.php',
			'sources/hooks/systems/config/points_ADD_POLL.php',
			'sources/hooks/systems/config/points_CHOOSE_POLL.php',
			'sources/hooks/systems/config/poll_update_time.php',
			'sources/hooks/systems/realtime_rain/polls.php',
			'themes/default/templates/BLOCK_MAIN_POLL.tpl',
			'sources/hooks/systems/content_meta_aware/poll.php',
			'sources/hooks/systems/occle_fs/polls.php',
			'sources/hooks/systems/addon_registry/polls.php',
			'sources/hooks/systems/preview/poll.php',
			'sources/hooks/modules/admin_setupwizard/polls.php',
			'sources/hooks/modules/admin_import_types/polls.php',
			'themes/default/templates/POLL_BOX.tpl',
			'themes/default/templates/POLL_ANSWER.tpl',
			'themes/default/templates/POLL_ANSWER_RESULT.tpl',
			'themes/default/templates/POLL_SCREEN.tpl',
			'themes/default/templates/POLL_LIST_ENTRY.tpl',
			'themes/default/templates/POLL_RSS_SUMMARY.tpl',
			'themes/default/css/polls.css',
			'themes/default/images/bigicons/polls.png',
			'themes/default/images/pagepics/polls.png',
			'cms/pages/modules/cms_polls.php',
			'lang/EN/polls.ini',
			'site/pages/modules/polls.php',
			'sources/hooks/blocks/main_staff_checklist/polls.php',
			'sources/hooks/modules/search/polls.php',
			'sources/hooks/systems/do_next_menus/polls.php',
			'sources/hooks/systems/rss/polls.php',
			'sources/hooks/systems/trackback/polls.php',
			'sources/polls.php',
			'sources/blocks/main_poll.php',
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
			'BLOCK_MAIN_POLL.tpl'=>'block_main_poll',
			'POLL_RSS_SUMMARY.tpl'=>'poll_rss_summary',
			'POLL_ANSWER.tpl'=>'poll_answer',
			'POLL_ANSWER_RESULT.tpl'=>'poll_answer_result',
			'POLL_BOX.tpl'=>'poll_answer',
			'POLL_LIST_ENTRY.tpl'=>'poll_list_entry',
			'POLL_SCREEN.tpl'=>'poll_screen'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__block_main_poll()
	{
		return array(
			lorem_globalise(do_lorem_template('BLOCK_MAIN_POLL',array(
				'CONTENT'=>$this->poll('poll'),
				'BLOCK_PARAMS'=>'',
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
	function tpl_preview__poll_rss_summary()
	{
		require_code('xml');

		$_summary=do_lorem_template('POLL_RSS_SUMMARY',array(
			'ANSWERS'=>placeholder_array()
		));
		$summary=xmlentities($_summary->evaluate());

		$if_comments=do_lorem_template('RSS_ENTRY_COMMENTS',array(
			'COMMENT_URL'=>placeholder_url(),
			'ID'=>placeholder_id()
		));

		return array(
			lorem_globalise(do_lorem_template('RSS_ENTRY',array(
				'VIEW_URL'=>placeholder_url(),
				'SUMMARY'=>$summary,
				'EDIT_DATE'=>placeholder_date(),
				'IF_COMMENTS'=>$if_comments,
				'TITLE'=>lorem_phrase(),
				'CATEGORY_RAW'=>NULL,
				'CATEGORY'=>'',
				'AUTHOR'=>lorem_word(),
				'ID'=>placeholder_id(),
				'NEWS'=>lorem_paragraph(),
				'DATE'=>placeholder_time()
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
	function tpl_preview__poll_answer()
	{
		return $this->poll('poll');
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__poll_answer_result()
	{
		return $this->poll('result');
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @param  string			View type.
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function poll($section='')
	{
		$tpl=new ocp_tempcode();
		switch ($section)
		{
			case 'poll':
				foreach (placeholder_array() as $k=>$v)
				{
					$tpl->attach(do_lorem_template('POLL_ANSWER',array(
						'PID'=>placeholder_id(),
						'I'=>strval($k),
						'CAST'=>strval($k),
						'VOTE_URL'=>placeholder_url(),
						'ANSWER'=>lorem_phrase(),
						'ANSWER_PLAIN'=>lorem_phrase()
					)));
				}
				break;

			case 'result':
				foreach (placeholder_array() as $k=>$v)
				{
					$tpl->attach(do_lorem_template('POLL_ANSWER_RESULT',array(
						'PID'=>placeholder_id(),
						'I'=>strval($k),
						'VOTE_URL'=>placeholder_url(),
						'ANSWER'=>lorem_phrase(),
						'ANSWER_PLAIN'=>lorem_phrase(),
						'WIDTH'=>strval($k),
						'VOTES'=>placeholder_number()
					)));
				}
				break;

			default:
				foreach (placeholder_array() as $k=>$v)
				{
					$tpl->attach(do_lorem_template('POLL_ANSWER',array(
						'PID'=>placeholder_id(),
						'I'=>strval($k),
						'CAST'=>strval($k),
						'VOTE_URL'=>placeholder_url(),
						'ANSWER'=>lorem_phrase(),
						'ANSWER_PLAIN'=>lorem_phrase()
					)));
				}
				foreach (placeholder_array() as $k=>$v)
				{
					$tpl->attach(do_lorem_template('POLL_ANSWER_RESULT',array(
						'PID'=>placeholder_id(),
						'I'=>strval($k),
						'VOTE_URL'=>placeholder_url(),
						'ANSWER'=>lorem_phrase(),
						'ANSWER_PLAIN'=>lorem_phrase(),
						'WIDTH'=>strval($k),
						'VOTES'=>placeholder_number()
					)));
				}
		}

		$wrap_content=do_lorem_template('POLL_BOX',array(
			'_GUID'=>'4c6b026f7ed96f0b5b8408eb5e5affb5',
			'VOTE_URL'=>placeholder_url(),
			'GIVE_CONTEXT'=>true,
			'SUBMITTER'=>placeholder_id(),
			'RESULT_URL'=>placeholder_url(),
			'SUBMIT_URL'=>placeholder_url(),
			'ARCHIVE_URL'=>placeholder_url(),
			'PID'=>placeholder_id(),
			'COMMENT_COUNT'=>placeholder_number(),
			'QUESTION_PLAIN'=>lorem_phrase(),
			'QUESTION'=>lorem_phrase(),
			'CONTENT'=>$tpl,
			'FULL_URL'=>placeholder_url()
		));

		return array(
			lorem_globalise($wrap_content, NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__poll_list_entry()
	{
		return array(
			lorem_globalise(do_lorem_template('POLL_LIST_ENTRY',array(
				'QUESTION'=>lorem_phrase(),
				'STATUS'=>lorem_phrase()
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
	function tpl_preview__poll_screen()
	{
		require_lang('trackbacks');
		$trackbacks=new ocp_tempcode();
		foreach (placeholder_array(1) as $k=>$v)
		{
			$trackbacks->attach(do_lorem_template('TRACKBACK',array(
				'ID'=>placeholder_id(),
				'TIME_RAW'=>placeholder_date_raw(),
				'TIME'=>placeholder_date(),
				'URL'=>placeholder_url(),
				'TITLE'=>lorem_phrase(),
				'EXCERPT'=>lorem_paragraph(),
				'NAME'=>lorem_phrase()
			)));
		}
		$trackback_details=do_lorem_template('TRACKBACK_WRAPPER',array(
			'TRACKBACKS'=>$trackbacks,
			'TRACKBACK_PAGE'=>placeholder_id(),
			'TRACKBACK_ID'=>placeholder_id(),
			'TRACKBACK_TITLE'=>lorem_phrase()
		));

		$rating_details='';
		$comments='';
		$comment_details=do_lorem_template('COMMENTS_WRAPPER',array(
			'TYPE'=>lorem_word(),
			'ID'=>placeholder_id(),
			'REVIEW_RATING_CRITERIA'=>array(),
			'AUTHORISED_FORUM_URL'=>placeholder_url(),
			'FORM'=>placeholder_form(),
			'COMMENTS'=>$comments,
			'SORT'=>'relevance',
		));

		$poll_details=$this->poll('poll');

		return array(
			lorem_globalise(do_lorem_template('POLL_SCREEN',array(
				'TITLE'=>lorem_title(),
				'DATE_RAW'=>placeholder_date_raw(),
				'ADD_DATE_RAW'=>placeholder_date_raw(),
				'EDIT_DATE_RAW'=>placeholder_date_raw(),
				'DATE'=>placeholder_time(),
				'ADD_DATE'=>placeholder_date(),
				'EDIT_DATE'=>placeholder_date(),
				'VIEWS'=>placeholder_number(),
				'TRACKBACK_DETAILS'=>$trackback_details,
				'RATING_DETAILS'=>$rating_details,
				'COMMENT_DETAILS'=>$comment_details,
				'EDIT_URL'=>placeholder_url(),
				'POLL_DETAILS'=>$poll_details,
				'SUBMITTER'=>placeholder_id(),
				'ID'=>placeholder_id()
			)), NULL, '', true)
		);
	}
}
