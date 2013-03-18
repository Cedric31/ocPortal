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
 * @package		syndication_blocks
 */

class Hook_addon_registry_syndication_blocks
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
		return 'Show RSS and Atom feeds from other websites.';
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
				'news'
			),
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
			'sources/hooks/systems/notifications/error_occurred_rss.php',
			'sources/hooks/systems/config_default/is_on_rss.php',
			'sources/hooks/systems/config_default/is_rss_advertised.php',
			'sources/hooks/systems/config_default/rss_update_time.php',
			'BLOCK_MAIN_RSS.tpl',
			'BLOCK_MAIN_RSS_CATEGORY.tpl',
			'BLOCK_MAIN_RSS_CATEGORY_NO_IMG.tpl',
			'BLOCK_MAIN_RSS_FROM_TITLE.tpl',
			'BLOCK_MAIN_RSS_FULL.tpl',
			'BLOCK_MAIN_RSS_LIST_FIRST.tpl',
			'BLOCK_MAIN_RSS_LIST_LAST.tpl',
			'BLOCK_MAIN_RSS_LIST_MIDDLE.tpl',
			'BLOCK_MAIN_RSS_SUMMARY.tpl',
			'BLOCK_MAIN_RSS_TITLE.tpl',
			'BLOCK_SIDE_RSS.tpl',
			'BLOCK_SIDE_RSS_SUMMARY.tpl',
			'rss.css',
			'sources/blocks/bottom_rss.php',
			'sources/blocks/main_rss.php',
			'sources/blocks/side_rss.php',
			'sources/hooks/systems/occle_commands/feed_display.php',
			'sources/hooks/systems/addon_registry/syndication_blocks.php',
			'sources/hooks/modules/admin_setupwizard/syndication_blocks.php'
		);
	}


	/**
	 * Get mapping between template names and the method of this class that can render a preview of them
	 *
	 * @return array						The mapping
	 */
	function tpl_previews()
	{
		return array(
			'BLOCK_SIDE_RSS_SUMMARY.tpl'=>'block_side_rss',
			'BLOCK_SIDE_RSS.tpl'=>'block_side_rss',
			'BLOCK_MAIN_RSS_TITLE.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS_FULL.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS_LIST_FIRST.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS_LIST_MIDDLE.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS_LIST_LAST.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS_CATEGORY.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS_CATEGORY_NO_IMG.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS_FROM_TITLE.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS_SUMMARY.tpl'=>'block_main_rss',
			'BLOCK_MAIN_RSS.tpl'=>'block_main_rss'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array						Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__block_side_rss()
	{
		$content=new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$content->attach(do_lorem_template('BLOCK_SIDE_RSS_SUMMARY', array(
				'FEED_URL'=>placeholder_url(),
				'FULL_URL'=>placeholder_url(),
				'NEWS_TITLE'=>lorem_phrase(),
				'DATE'=>placeholder_time(),
				'SUMMARY'=>lorem_paragraph(),
				'TICKER'=>lorem_word()
			)));
		}

		return array(
			lorem_globalise(do_lorem_template('BLOCK_SIDE_RSS', array(
				'FEED_URL'=>placeholder_url(),
				'TITLE'=>lorem_phrase(),
				'CONTENT'=>$content,
				'TICKER'=>true
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array						Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__block_main_rss()
	{
		require_lang('news');
		require_css('news');
		$content=new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$news_full=do_lorem_template('BLOCK_MAIN_RSS_FULL', array(
				'NEWS_FULL'=>lorem_paragraph()
			));

			$tails=do_lorem_template('BLOCK_MAIN_RSS_LIST_FIRST', array(
				'X'=>lorem_phrase()
			));
			$tails->attach(do_lorem_template('BLOCK_MAIN_RSS_LIST_MIDDLE', array(
				'X'=>placeholder_url()
			)));
			$tails->attach(do_lorem_template('BLOCK_MAIN_RSS_LIST_LAST', array(
				'X'=>placeholder_url()
			)));

			$category=do_lorem_template('BLOCK_MAIN_RSS_CATEGORY', array(
				'IMG'=>placeholder_image_url(),
				'CATEGORY'=>lorem_phrase()
			));
			$category->attach(do_lorem_template('BLOCK_MAIN_RSS_CATEGORY_NO_IMG', array(
				'CATEGORY'=>lorem_phrase()
			)));

			$_title=do_lorem_template('BLOCK_MAIN_RSS_TITLE', array(
				'CATEGORY'=>lorem_phrase(),
				'TITLE'=>lorem_phrase()
			));
			$__title=do_lorem_template('BLOCK_MAIN_RSS_FROM_TITLE', array(
				'FEED_URL'=>placeholder_url(),
				'NEWS_TITLE'=>lorem_phrase(),
				'DATE'=>placeholder_time()
			));

			$content->attach(do_lorem_template('BLOCK_MAIN_RSS_SUMMARY', array(
				'FEED_URL'=>placeholder_url(),
				'NEWS_FULL'=>$news_full,
				'DATE'=>placeholder_time(),
				'TAILS'=>$tails,
				'AUTHOR'=>lorem_phrase(),
				'CATEGORY'=>$category,
				'FULL_URL'=>placeholder_link(),
				'FULL_URL_RAW'=>placeholder_url(),
				'NEWS_TITLE'=>$__title,
				'NEWS'=>lorem_paragraph()
			)));
		}

		return array(
			lorem_globalise(do_lorem_template('BLOCK_MAIN_RSS', array(
				'FEED_URL'=>placeholder_url(),
				'TITLE'=>lorem_phrase(),
				'COPYRIGHT'=>lorem_phrase(),
				'AUTHOR'=>lorem_phrase(),
				'CONTENT'=>$content
			)), NULL, '', true)
		);
	}
}
