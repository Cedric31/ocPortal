<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    syndication_blocks
 */

/**
 * Hook class.
 */
class Hook_addon_registry_syndication_blocks
{
    /**
     * Get a list of file permissions to set
     *
     * @return array                    File permissions to set
     */
    public function get_chmod_array()
    {
        return array();
    }

    /**
     * Get the version of ocPortal this addon is for
     *
     * @return float                    Version number
     */
    public function get_version()
    {
        return ocp_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string                   Description of the addon
     */
    public function get_description()
    {
        return 'Show RSS and Atom feeds from other websites.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array                    List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_news',
            'tut_adv_news',
        );
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array                    File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(
                'news'
            ),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH                  Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/links/rss.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array                    List of files
     */
    public function get_file_list()
    {
        return array(
            'sources/hooks/systems/notifications/error_occurred_rss.php',
            'sources/hooks/systems/config/is_on_rss.php',
            'sources/hooks/systems/config/is_rss_advertised.php',
            'sources/hooks/systems/config/rss_update_time.php',
            'themes/default/templates/BLOCK_MAIN_RSS.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_CATEGORY.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_CATEGORY_NO_IMG.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_FROM_TITLE.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_FULL.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_LIST_FIRST.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_LIST_LAST.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_LIST_MIDDLE.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_SUMMARY.tpl',
            'themes/default/templates/BLOCK_MAIN_RSS_TITLE.tpl',
            'themes/default/templates/BLOCK_SIDE_RSS.tpl',
            'themes/default/templates/BLOCK_SIDE_RSS_SUMMARY.tpl',
            'themes/default/css/rss.css',
            'sources/blocks/bottom_rss.php',
            'sources/blocks/main_rss.php',
            'sources/blocks/side_rss.php',
            'sources/hooks/systems/occle_commands/feed_display.php',
            'sources/hooks/systems/addon_registry/syndication_blocks.php',
            'sources/hooks/modules/admin_setupwizard/syndication_blocks.php',
        );
    }


    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array                    The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/BLOCK_SIDE_RSS_SUMMARY.tpl' => 'block_side_rss',
            'templates/BLOCK_SIDE_RSS.tpl' => 'block_side_rss',
            'templates/BLOCK_MAIN_RSS_TITLE.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS_FULL.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS_LIST_FIRST.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS_LIST_MIDDLE.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS_LIST_LAST.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS_CATEGORY.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS_CATEGORY_NO_IMG.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS_FROM_TITLE.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS_SUMMARY.tpl' => 'block_main_rss',
            'templates/BLOCK_MAIN_RSS.tpl' => 'block_main_rss'
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_side_rss()
    {
        $content = new Tempcode();
        foreach (placeholder_array() as $k => $v) {
            $content->attach(do_lorem_template('BLOCK_SIDE_RSS_SUMMARY', array(
                'FEED_URL' => placeholder_url(),
                'FULL_URL' => placeholder_url(),
                'NEWS_TITLE' => lorem_phrase(),
                'DATE' => placeholder_time(),
                'SUMMARY' => lorem_paragraph(),
                'TICKER' => lorem_word(),
            )));
        }

        return array(
            lorem_globalise(do_lorem_template('BLOCK_SIDE_RSS', array(
                'FEED_URL' => placeholder_url(),
                'TITLE' => lorem_phrase(),
                'CONTENT' => $content,
                'TICKER' => true,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_main_rss()
    {
        require_lang('news');
        require_css('news');
        $content = new Tempcode();
        foreach (placeholder_array() as $k => $v) {
            $news_full = do_lorem_template('BLOCK_MAIN_RSS_FULL', array(
                'NEWS_FULL' => lorem_paragraph(),
            ));

            $tails = do_lorem_template('BLOCK_MAIN_RSS_LIST_FIRST', array(
                'X' => lorem_phrase(),
            ));
            $tails->attach(do_lorem_template('BLOCK_MAIN_RSS_LIST_MIDDLE', array(
                'X' => placeholder_url(),
            )));
            $tails->attach(do_lorem_template('BLOCK_MAIN_RSS_LIST_LAST', array(
                'X' => placeholder_url(),
            )));

            $category = do_lorem_template('BLOCK_MAIN_RSS_CATEGORY', array(
                'IMG' => placeholder_image_url(),
                'CATEGORY' => lorem_phrase(),
            ));
            $category->attach(do_lorem_template('BLOCK_MAIN_RSS_CATEGORY_NO_IMG', array(
                'CATEGORY' => lorem_phrase(),
            )));

            $_title = do_lorem_template('BLOCK_MAIN_RSS_TITLE', array(
                'CATEGORY' => lorem_phrase(),
                'TITLE' => lorem_phrase(),
            ));
            $__title = do_lorem_template('BLOCK_MAIN_RSS_FROM_TITLE', array(
                'FEED_URL' => placeholder_url(),
                'NEWS_TITLE' => lorem_phrase(),
                'DATE' => placeholder_time(),
            ));

            $content->attach(do_lorem_template('BLOCK_MAIN_RSS_SUMMARY', array(
                'FEED_URL' => placeholder_url(),
                'NEWS_FULL' => $news_full,
                'DATE' => placeholder_time(),
                'TAILS' => $tails,
                'AUTHOR' => lorem_phrase(),
                'CATEGORY' => $category,
                'FULL_URL' => placeholder_link(),
                'FULL_URL_RAW' => placeholder_url(),
                'NEWS_TITLE' => $__title,
                'NEWS' => lorem_paragraph(),
            )));
        }

        return array(
            lorem_globalise(do_lorem_template('BLOCK_MAIN_RSS', array(
                'FEED_URL' => placeholder_url(),
                'TITLE' => lorem_phrase(),
                'COPYRIGHT' => lorem_phrase(),
                'AUTHOR' => lorem_phrase(),
                'CONTENT' => $content,
            )), null, '', true)
        );
    }
}
