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
 * @package    filedump
 */

/**
 * Hook class.
 */
class Hook_addon_registry_filedump
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
        return 'File/media library, for use in attachments or for general ad-hoc sharing.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array                    List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_collaboration',
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
            'requires' => array(),
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
        return 'themes/default/images/icons/48x48/menu/cms/filedump.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array                    List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/cms/filedump.png',
            'themes/default/images/icons/48x48/menu/cms/filedump.png',
            'sources/hooks/systems/notifications/filedump.php',
            'sources/hooks/systems/config/filedump_show_stats_count_total_files.php',
            'sources/hooks/systems/config/filedump_show_stats_count_total_space.php',
            'sources/hooks/blocks/side_stats/stats_filedump.php',
            'sources/hooks/systems/addon_registry/filedump.php',
            'sources/hooks/systems/ajax_tree/choose_filedump_file.php',
            'sources/hooks/systems/page_groupings/filedump.php',
            'sources/hooks/modules/admin_import_types/filedump.php',
            'themes/default/templates/FILEDUMP_SCREEN.tpl',
            'themes/default/templates/FILEDUMP_EMBED_SCREEN.tpl',
            'uploads/filedump/index.html',
            'cms/pages/modules/filedump.php',
            'lang/EN/filedump.ini',
            'sources/hooks/systems/config/is_on_folder_create.php',
            'sources/hooks/modules/search/filedump.php',
            'sources/hooks/systems/rss/filedump.php',
            'sources/hooks/systems/occle_fs/home.php',
            'uploads/filedump/.htaccess',
            'themes/default/css/filedump.css',
            'sources/filedump.php',
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
            'templates/FILEDUMP_SCREEN.tpl' => 'filedump_screen',
            'templates/FILEDUMP_EMBED_SCREEN.tpl' => 'filedump_embed_screen',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__filedump_screen()
    {
        require_css('forms');

        $thumbnails = array();
        $thumbnails[] = array(
            'FILENAME' => lorem_word(),
            'PLACE' => placeholder_id(),
            'THUMBNAIL' => placeholder_image(),
            'IS_IMAGE' => true,
            'URL' => placeholder_url(),
            'DESCRIPTION' => lorem_paragraph(),
            'ACTIONS' => lorem_paragraph(),
            '_SIZE' => placeholder_number(),
            'SIZE' => placeholder_number(),
            '_TIME' => placeholder_date_raw(),
            'TIME' => placeholder_date(),
            'WIDTH' => placeholder_number(),
            'HEIGHT' => placeholder_number(),
            'IS_DIRECTORY' => false,
            'CHOOSABLE' => false,
            'EMBED_URL' => placeholder_url(),
        );

        return array(
            lorem_globalise(do_lorem_template('FILEDUMP_SCREEN', array(
                'TITLE' => lorem_title(),
                'PLACE' => placeholder_id(),
                'THUMBNAILS' => $thumbnails,
                'LISTING' => placeholder_table(),
                'UPLOAD_FORM' => placeholder_form(),
                'CREATE_FOLDER_FORM' => placeholder_form(),
                'TYPE_FILTER' => '',
                'SEARCH' => '',
                'SORT' => 'time ASC',
                'PAGINATION_LISTING' => placeholder_pagination(),
                'PAGINATION_THUMBNAILS' => placeholder_pagination(),
                'POST_URL' => placeholder_url(),
                'DIRECTORIES' => array(lorem_word()),
                'OTHER_DIRECTORIES' => array(lorem_word()),
                'FILTERED_DIRECTORIES' => array(lorem_word()),
                'FILTERED_DIRECTORIES_MISSES' => array(),
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
    public function tpl_preview__filedump_embed_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('FILEDUMP_EMBED_SCREEN', array(
                'TITLE' => lorem_title(),
                'FORM' => placeholder_form(),
                'IMAGE_SIZES' => array(
                    array(
                        'LABEL' => do_lang_tempcode('FILEDUMP_IMAGE_URLS_SMALL', escape_html(get_option('thumb_width')), escape_html(get_option('thumb_width'))),
                        'SIZE_URL' => placeholder_image_url(),
                        'SIZE_WIDTH' => get_option('thumb_width'),
                        'SIZE_HEIGHT' => get_option('thumb_width'),
                    ),
                ),
                'URL' => placeholder_image_url(),
                'EXISTING_COUNT' => placeholder_number(),
            )), null, '', true)
        );
    }
}
