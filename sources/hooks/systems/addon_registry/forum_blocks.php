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
 * @package		forum_blocks
 */

class Hook_addon_registry_forum_blocks
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
		return 'Blocks to draw forum posts and topics into the main website.';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array('news_shared'),
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

			'BLOCK_MAIN_FORUM_NEWS.tpl',
			'BLOCK_MAIN_FORUM_TOPICS.tpl',
			'BLOCK_MAIN_FORUM_TOPICS_TOPIC.tpl',
			'BLOCK_SIDE_FORUM_NEWS.tpl',
			'BLOCK_SIDE_FORUM_NEWS_SUMMARY.tpl',
			'sources/blocks/bottom_forum_news.php',
			'sources/blocks/main_forum_news.php',
			'sources/blocks/main_forum_topics.php',
			'sources/blocks/side_forum_news.php',
			'sources/hooks/systems/addon_registry/forum_blocks.php',
			'sources/hooks/modules/admin_setupwizard/forum_blocks.php',
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
				'BLOCK_MAIN_FORUM_TOPICS_TOPIC.tpl'=>'block_main_forum_topics',
				'BLOCK_MAIN_FORUM_TOPICS.tpl'=>'block_main_forum_topics',
				'BLOCK_SIDE_FORUM_NEWS_SUMMARY.tpl'=>'block_side_forum_news',
				'BLOCK_SIDE_FORUM_NEWS.tpl'=>'block_side_forum_news',
				'BLOCK_MAIN_FORUM_NEWS.tpl'=>'block_main_forum_news',
				);
	}

   /**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__block_main_forum_topics()
	{
		require_lang('ocf');
		//Create the 'BLOCK_MAIN_FORUM_TOPICS_TOPIC' template value
		$out=new ocp_tempcode();
		foreach(placeholder_array() as $k=>$v)
		{
			$out->attach(do_lorem_template('BLOCK_MAIN_FORUM_TOPICS_TOPIC',array('FORUM_ID'=>NULL,'FORUM_NAME'=>lorem_word(),'TOPIC_LINK'=>placeholder_url(),'TITLE'=>lorem_word(),'DATE'=>placeholder_time(),'DATE_RAW'=>placeholder_date_raw(),'USERNAME'=>lorem_word(),'MEMBER_ID'=>NULL,'NUM_POSTS'=>placeholder_number())));
		}

		//Create the 'BLOCK_MAIN_FORUM_TOPICS' with 'BLOCK_MAIN_FORUM_TOPICS_TOPIC' as sub-template.
		return array(
							lorem_globalise(do_lorem_template(
																		'BLOCK_MAIN_FORUM_TOPICS',
																		array(
		 		                 											'TITLE'=>lorem_word(),
																				'CONTENT'=>$out,
																				'FORUM_NAME'=>lorem_word_html(),
																				'SUBMIT_URL'=>placeholder_url(),
			               											)
																		),NULL,'',true
												)
						);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__block_side_forum_news()
	{
		require_lang('news');
		require_lang('ocf');
		//Create the 'BLOCK_SIDE_FORUM_NEWS_SUMMARY' template value
		$out=new ocp_tempcode();
		foreach(placeholder_array() as $k=>$v)
		{
			$out->attach(do_lorem_template('BLOCK_SIDE_FORUM_NEWS_SUMMARY',array('REPLIES'=>lorem_word(),'FIRSTTIME'=>lorem_word(),'LASTTIME'=>lorem_word(),'CLOSED'=>lorem_word(),'FIRSTUSERNAME'=>lorem_word(),'LASTUSERNAME'=>lorem_word(),'FIRSTMEMBERID'=>lorem_word(),'LASTMEMBERID'=>lorem_word(),'_DATE'=>placeholder_date_raw(),'DATE'=>placeholder_time(),'FULL_URL'=>placeholder_url(),'NEWS_TITLE'=>escape_html(lorem_word()))));
		}

		//Create the 'BLOCK_SIDE_FORUM_NEWS' with 'BLOCK_SIDE_FORUM_NEWS_SUMMARY' as sub-template.
		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_FORUM_NEWS',array(
					'FORUM_NAME'=>lorem_word_html(),
					'TITLE'=>lorem_phrase(),
					'CONTENT'=>$out,
					'SUBMIT_URL'=>placeholder_url(),
					'ARCHIVE_URL'=>placeholder_url(),
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
	function tpl_preview__block_main_forum_news()
	{
		require_lang('news');
		//Create the 'NEWS_PIECE_SUMMARY' template value
		$out=new ocp_tempcode();
		foreach(placeholder_array() as $k=>$v)
		{
			$out->attach(do_lorem_template('NEWS_PIECE_SUMMARY',array('TRUNCATE'=>false,'BLOG'=>false,'FIRSTTIME'=>lorem_word(),'LASTTIME'=>lorem_word(),'CLOSED'=>lorem_word(),'FIRSTUSERNAME'=>lorem_word(),'LASTUSERNAME'=>lorem_word(),'FIRSTMEMBERID'=>lorem_word(),'LASTMEMBERID'=>lorem_word(),'ID'=>lorem_word(),'FULL_URL'=>placeholder_url(),'SUBMITTER'=>lorem_word(),'DATE'=>placeholder_time(),'DATE_RAW'=>placeholder_date_raw(),'NEWS_TITLE'=>lorem_word(),'CATEGORY'=>'','IMG'=>'','AUTHOR'=>lorem_word(),'AUTHOR_URL'=>placeholder_url(),'NEWS'=>lorem_paragraph())));
		}

		//Create the 'BLOCK_MAIN_FORUM_NEWS' with 'NEWS_PIECE_SUMMARY' as sub-template.
		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_MAIN_FORUM_NEWS',array(
					'TITLE'=>lorem_word(),
					'FORUM_NAME'=>lorem_word_html(),
					'CONTENT'=>$out,
					'BRIEF'=>lorem_phrase(),
					'ARCHIVE_URL'=>placeholder_url(),
					'SUBMIT_URL'=>placeholder_url(),
					'RSS_URL'=>placeholder_url(),
					'ATOM_URL'=>placeholder_url(),
						)
			),NULL,'',true),
		);
	}
}