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
 * @package    core_menus
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_menus
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
        return 'Edit menus.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array                    List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_menus',
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
        return 'themes/default/images/icons/48x48/menu/adminzone/structure/menus.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array                    List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/structure/menus.png',
            'themes/default/images/icons/48x48/menu/adminzone/structure/menus.png',
            'sources/hooks/systems/resource_meta_aware/menu.php',
            'sources/hooks/systems/resource_meta_aware/menu_item.php',
            'sources/hooks/systems/occle_fs/menus.php',
            'themes/default/css/menu_editor.css',
            'sources/hooks/systems/addon_registry/core_menus.php',
            'themes/admin/templates/MENU_mobile.tpl',
            'themes/default/images/mobile_menu.png',
            'themes/default/css/menu__mobile.css',
            'themes/default/css/menu__sitemap.css',
            'themes/default/css/menu__dropdown.css',
            'themes/default/css/menu__popup.css',
            'themes/default/css/menu__embossed.css',
            'themes/default/css/menu__select.css',
            'themes/default/css/menu__tree.css',
            'themes/admin/css/menu__dropdown.css',
            'themes/admin/templates/MENU_BRANCH_dropdown.tpl',
            'themes/admin/templates/MENU_dropdown.tpl',
            'themes/default/templates/MENU_dropdown.tpl',
            'themes/default/templates/MENU_embossed.tpl',
            'themes/default/templates/MENU_popup.tpl',
            'themes/default/templates/MENU_select.tpl',
            'themes/default/templates/MENU_sitemap.tpl',
            'themes/default/templates/MENU_tree.tpl',
            'themes/default/templates/MENU_mobile.tpl',
            'themes/default/templates/MENU_BRANCH_dropdown.tpl',
            'themes/default/templates/MENU_BRANCH_embossed.tpl',
            'themes/default/templates/MENU_BRANCH_popup.tpl',
            'themes/default/templates/MENU_BRANCH_select.tpl',
            'themes/default/templates/MENU_BRANCH_sitemap.tpl',
            'themes/default/templates/MENU_BRANCH_tree.tpl',
            'themes/default/templates/MENU_BRANCH_mobile.tpl',
            'themes/default/templates/MENU_SPACER_dropdown.tpl',
            'themes/default/templates/MENU_SPACER_embossed.tpl',
            'themes/default/templates/MENU_SPACER_popup.tpl',
            'themes/default/templates/MENU_SPACER_select.tpl',
            'themes/default/templates/MENU_SPACER_sitemap.tpl',
            'themes/default/templates/MENU_SPACER_tree.tpl',
            'themes/default/templates/MENU_SPACER_mobile.tpl',
            'themes/default/javascript/menu_popup.js',
            'themes/default/javascript/menu_sitemap.js',
            'themes/default/templates/MENU_STAFF_LINK.tpl',
            'themes/default/templates/MENU_EDITOR_BRANCH.tpl',
            'themes/default/templates/MENU_EDITOR_SCREEN.tpl',
            'themes/default/templates/MENU_EDITOR_BRANCH_WRAP.tpl',
            'themes/default/javascript/menu_editor.js',
            'themes/default/templates/BLOCK_MENU.tpl',
            'themes/default/templates/MENU_LINK_PROPERTIES.tpl',
            'adminzone/pages/modules/admin_menus.php',
            'adminzone/menu_management.php',
            'themes/default/images/1x/menus/index.html',
            'themes/default/images/1x/menus/menu.png',
            'themes/default/images/1x/menus/menu_bullet.png',
            'themes/default/images/1x/menus/menu_bullet_hover.png',
            'themes/default/images/1x/menus/menu_bullet_expand.png',
            'themes/default/images/1x/menus/menu_bullet_expand_hover.png',
            'themes/default/images/1x/menus/menu_bullet_current.png',
            'themes/default/images/2x/menus/index.html',
            'themes/default/images/2x/menus/menu.png',
            'themes/default/images/2x/menus/menu_bullet.png',
            'themes/default/images/2x/menus/menu_bullet_hover.png',
            'themes/default/images/2x/menus/menu_bullet_expand.png',
            'themes/default/images/2x/menus/menu_bullet_expand_hover.png',
            'themes/default/images/2x/menus/menu_bullet_current.png',
            'lang/EN/menus.ini',
            'sources/blocks/menu.php',
            'sources/hooks/systems/snippets/management_menu.php',
            'sources/menus.php',
            'sources/menus_comcode.php',
            'sources/menus2.php',
            'themes/default/templates/PAGE_LINK_CHOOSER.tpl',
            'data/page_link_chooser.php',
        );
    }


    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array                     The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/MENU_EDITOR_BRANCH.tpl' => 'administrative__menu_editor_screen',
            'templates/MENU_EDITOR_BRANCH_WRAP.tpl' => 'administrative__menu_editor_screen',
            'templates/MENU_EDITOR_SCREEN.tpl' => 'administrative__menu_editor_screen',
            'templates/PAGE_LINK_CHOOSER.tpl' => 'page_link_chooser',
            'templates/BLOCK_MENU.tpl' => 'block_menu__tree',
            'templates/MENU_STAFF_LINK.tpl' => 'block_menu__tree',

            'templates/MENU_SPACER_tree.tpl' => 'block_menu__tree',
            'templates/MENU_BRANCH_tree.tpl' => 'block_menu__tree',
            'templates/MENU_tree.tpl' => 'block_menu__tree',

            'templates/MENU_SPACER_mobile.tpl' => 'block_menu__mobile',
            'templates/MENU_BRANCH_mobile.tpl' => 'block_menu__mobile',
            'templates/MENU_mobile.tpl' => 'block_menu__mobile',

            'templates/MENU_SPACER_dropdown.tpl' => 'block_menu__dropdown',
            'templates/MENU_BRANCH_dropdown.tpl' => 'block_menu__dropdown',
            'templates/MENU_dropdown.tpl' => 'block_menu__dropdown',

            'templates/MENU_SPACER_embossed.tpl' => 'block_menu__embossed',
            'templates/MENU_BRANCH_embossed.tpl' => 'block_menu__embossed',
            'templates/MENU_embossed.tpl' => 'block_menu__embossed',

            'templates/MENU_SPACER_popup.tpl' => 'block_menu__popup',
            'templates/MENU_BRANCH_popup.tpl' => 'block_menu__popup',
            'templates/MENU_popup.tpl' => 'block_menu__popup',

            'templates/MENU_SPACER_select.tpl' => 'block_menu__select',
            'templates/MENU_BRANCH_select.tpl' => 'block_menu__select',
            'templates/MENU_select.tpl' => 'block_menu__select',

            'templates/MENU_SPACER_sitemap.tpl' => 'block_menu__sitemap',
            'templates/MENU_BRANCH_sitemap.tpl' => 'block_menu__sitemap',
            'templates/MENU_sitemap.tpl' => 'block_menu__sitemap',

            'templates/MENU_LINK_PROPERTIES.tpl' => 'block_menu__sitemap',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__menu_editor_screen()
    {
        $branch = do_lorem_template('MENU_EDITOR_BRANCH', array('CLICKABLE_SECTIONS' => 'true', 'I' => placeholder_id(), 'CHILD_BRANCH_TEMPLATE' => '', 'CHILD_BRANCHES' => ''));

        $child_branch_template = do_lorem_template('MENU_EDITOR_BRANCH_WRAP', array(
            'DISPLAY' => 'display: block',
            'CLICKABLE_SECTIONS' => true,
            'ORDER' => 'replace_me_with_order',
            'PARENT' => 'replace_me_with_parent',
            'BRANCH_TYPE' => '0',
            'NEW_WINDOW' => '0',
            'CHECK_PERMS' => '0',
            'INCLUDE_SITEMAP' => '0',
            'CAPTION_LONG' => '',
            'CAPTION' => '',
            'URL' => '',
            'PAGE_ONLY' => '',
            'THEME_IMG_CODE' => '',
            'I' => placeholder_id(),
            'BRANCH' => $branch,
        ));

        $root_branch = do_lorem_template('MENU_EDITOR_BRANCH', array('CLICKABLE_SECTIONS' => 'true', 'CHILD_BRANCH_TEMPLATE' => $child_branch_template, 'CHILD_BRANCHES' => '', 'I' => ''));

        return array(
            lorem_globalise(
                do_lorem_template('MENU_EDITOR_SCREEN', array(
                        'ALL_MENUS' => placeholder_array(),
                        'MENU_NAME' => lorem_word(),
                        'DELETE_URL' => placeholder_url(),
                        'PING_URL' => placeholder_url(),
                        'WARNING_DETAILS' => '',
                        'FIELDS_TEMPLATE' => placeholder_fields(),
                        'HIGHEST_ORDER' => lorem_phrase(),
                        'URL' => placeholder_url(),
                        'CHILD_BRANCH_TEMPLATE' => $child_branch_template,
                        'ROOT_BRANCH' => $root_branch,
                        'TITLE' => lorem_title(),
                    )
                ), null, '', true),
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__page_link_chooser()
    {
        require_javascript('tree_list');
        return array(
            lorem_globalise(
                do_lorem_template('PAGE_LINK_CHOOSER', array(
                        'NAME' => lorem_word(),
                        'VALUE' => lorem_word(),
                    )
                ), null, '', true),
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_menu__mobile()
    {
        $child = new Tempcode();
        $content = new Tempcode();
        foreach (placeholder_array(3) as $v) {
            $child->attach(do_lorem_template('MENU_BRANCH_mobile', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => '',
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => false,
                'THE_LEVEL' => '2',
                'POSITION' => '1',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));
        }
        foreach (placeholder_array(3) as $v) {
            $content->attach(do_lorem_template('MENU_BRANCH_mobile', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => $child,
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
                'POSITION' => '2',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));

            $content->attach(do_lorem_template('MENU_SPACER_mobile', array(
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
            )));
        }
        $menu = do_lorem_template('MENU_mobile', array(
            'CONTENT' => $content,
            'MENU' => lorem_word_2(),
        ));

        $menu->attach(do_lorem_template('MENU_STAFF_LINK', array('TYPE' => 'mobile', 'EDIT_URL' => placeholder_url(), 'NAME' => lorem_phrase())));

        return array(
            lorem_globalise(
                do_lorem_template('BLOCK_MENU', array(
                        'CONTENT' => $menu,
                        'PARAM' => lorem_phrase(),
                        'TRAY_STATUS' => lorem_phrase(),
                        'TITLE' => lorem_phrase(),
                        'TYPE' => 'mobile',
                    )
                ), null, '', true),
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_menu__tree()
    {
        $child = new Tempcode();
        $content = new Tempcode();
        foreach (placeholder_array(3) as $v) {
            $child->attach(do_lorem_template('MENU_BRANCH_tree', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => '',
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => false,
                'THE_LEVEL' => '2',
                'POSITION' => '1',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));
        }
        foreach (placeholder_array(3) as $v) {
            $content->attach(do_lorem_template('MENU_BRANCH_tree', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => $child,
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
                'POSITION' => '2',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));

            $content->attach(do_lorem_template('MENU_SPACER_tree', array(
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
            )));
        }
        $menu = do_lorem_template('MENU_tree', array(
            'CONTENT' => $content,
            'MENU' => lorem_word_2(),
        ));

        $menu->attach(do_lorem_template('MENU_STAFF_LINK', array('TYPE' => 'tree', 'EDIT_URL' => placeholder_url(), 'NAME' => lorem_phrase())));

        return array(
            lorem_globalise(
                do_lorem_template('BLOCK_MENU', array(
                        'CONTENT' => $menu,
                        'PARAM' => lorem_phrase(),
                        'TRAY_STATUS' => lorem_phrase(),
                        'TITLE' => lorem_phrase(),
                        'TYPE' => 'tree',
                    )
                ), null, '', true),
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_menu__dropdown()
    {
        $child = new Tempcode();
        $content = new Tempcode();
        foreach (placeholder_array(3) as $v) {
            $child->attach(do_lorem_template('MENU_BRANCH_dropdown', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => '',
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => false,
                'THE_LEVEL' => '2',
                'POSITION' => '1',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));

            $child->attach(do_lorem_template('MENU_SPACER_dropdown', array(
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
            )));
        }
        foreach (placeholder_array(3) as $v) {
            $content->attach(do_lorem_template('MENU_BRANCH_dropdown', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => $child,
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
                'POSITION' => '2',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));
        }
        $menu = do_lorem_template('MENU_dropdown', array(
            'CONTENT' => $content,
            'MENU' => lorem_word_2(),
        ));

        $menu->attach(do_lorem_template('MENU_STAFF_LINK', array('TYPE' => 'dropdown', 'EDIT_URL' => placeholder_url(), 'NAME' => lorem_phrase())));

        return array(
            lorem_globalise($menu, null, '', true),
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_menu__embossed()
    {
        $child = new Tempcode();
        $content = new Tempcode();
        foreach (placeholder_array(3) as $v) {
            $child->attach(do_lorem_template('MENU_BRANCH_embossed', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => '',
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => false,
                'THE_LEVEL' => '2',
                'POSITION' => '1',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));
        }
        foreach (placeholder_array(3) as $v) {
            $content->attach(do_lorem_template('MENU_BRANCH_embossed', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => $child,
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
                'POSITION' => '2',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));

            $content->attach(do_lorem_template('MENU_SPACER_embossed', array(
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
            )));
        }
        $menu = do_lorem_template('MENU_embossed', array(
            'CONTENT' => $content,
            'MENU' => lorem_word_2(),
        ));

        $menu->attach(do_lorem_template('MENU_STAFF_LINK', array('TYPE' => 'embossed', 'EDIT_URL' => placeholder_url(), 'NAME' => lorem_phrase())));

        return array(
            lorem_globalise(
                do_lorem_template('BLOCK_MENU', array(
                        'CONTENT' => $menu,
                        'PARAM' => lorem_phrase(),
                        'TRAY_STATUS' => lorem_phrase(),
                        'TITLE' => lorem_phrase(),
                        'TYPE' => 'embossed',
                    )
                ), null, '', true),
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_menu__popup()
    {
        $child = new Tempcode();
        $content = new Tempcode();
        foreach (placeholder_array(3) as $v) {
            $child->attach(do_lorem_template('MENU_BRANCH_popup', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => '',
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => false,
                'THE_LEVEL' => '2',
                'POSITION' => '1',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));
        }
        foreach (placeholder_array(3) as $v) {
            $content->attach(do_lorem_template('MENU_BRANCH_popup', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => $child,
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
                'POSITION' => '2',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));

            $content->attach(do_lorem_template('MENU_SPACER_popup', array(
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
            )));
        }
        $menu = do_lorem_template('MENU_popup', array(
            'CONTENT' => $content,
            'MENU' => lorem_word_2(),
        ));

        $menu->attach(do_lorem_template('MENU_STAFF_LINK', array('TYPE' => 'popup', 'EDIT_URL' => placeholder_url(), 'NAME' => lorem_phrase())));

        return array(
            lorem_globalise(
                do_lorem_template('BLOCK_MENU', array(
                        'CONTENT' => $menu,
                        'PARAM' => lorem_phrase(),
                        'TRAY_STATUS' => lorem_phrase(),
                        'TITLE' => lorem_phrase(),
                        'TYPE' => 'popup',
                    )
                ), null, '', true),
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_menu__select()
    {
        $child = new Tempcode();
        $content = new Tempcode();
        foreach (placeholder_array(3) as $v) {
            $child->attach(do_lorem_template('MENU_BRANCH_select', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => '',
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => false,
                'THE_LEVEL' => '2',
                'POSITION' => '1',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));
        }
        foreach (placeholder_array(3) as $v) {
            $content->attach(do_lorem_template('MENU_BRANCH_select', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => $child,
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
                'POSITION' => '2',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));

            $content->attach(do_lorem_template('MENU_SPACER_select', array(
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => true,
                'THE_LEVEL' => '0',
            )));
        }
        $menu = do_lorem_template('MENU_select', array(
            'CONTENT' => $content,
            'MENU' => lorem_word_2(),
        ));

        $menu->attach(do_lorem_template('MENU_STAFF_LINK', array('TYPE' => 'select', 'EDIT_URL' => placeholder_url(), 'NAME' => lorem_phrase())));

        return array(
            lorem_globalise(
                do_lorem_template('BLOCK_MENU', array(
                        'CONTENT' => $menu,
                        'PARAM' => lorem_phrase(),
                        'TRAY_STATUS' => lorem_phrase(),
                        'TITLE' => lorem_phrase(),
                        'TYPE' => 'select',
                    )
                ), null, '', true),
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_menu__sitemap()
    {
        $child = new Tempcode();
        $content = new Tempcode();
        foreach (placeholder_array() as $v) {
            $child->attach(do_lorem_template('MENU_BRANCH_sitemap', array(
                'CAPTION' => lorem_word(),
                'IMG' => '',
                'IMG_2X' => '',
                'URL' => placeholder_url(),
                'PAGE_LINK' => placeholder_link(),
                'ACCESSKEY' => '',
                'NEW_WINDOW' => false,
                'TOOLTIP' => lorem_phrase(),
                'CHILDREN' => '',
                'DISPLAY' => 'block',
                'MENU' => lorem_word_2(),
                'TOP_LEVEL' => false,
                'THE_LEVEL' => '2',
                'POSITION' => '1',
                'FIRST' => false,
                'LAST' => false,
                'BRETHREN_COUNT' => '3',
                'CURRENT' => false,
                'CURRENT_ZONE' => false,
            )));
        }
        foreach (placeholder_array(3) as $k => $v) {
            if ($k == 1) {
                $content->attach(do_lorem_template('MENU_SPACER_sitemap', array(
                    'MENU' => lorem_word_2(),
                    'TOP_LEVEL' => true,
                    'THE_LEVEL' => '0',
                )));
            } else {
                $content->attach(do_lorem_template('MENU_BRANCH_sitemap', array(
                    'CAPTION' => lorem_word(),
                    'IMG' => '',
                    'IMG_2X' => '',
                    'URL' => placeholder_url(),
                    'PAGE_LINK' => placeholder_link(),
                    'ACCESSKEY' => '',
                    'NEW_WINDOW' => false,
                    'TOOLTIP' => lorem_phrase(),
                    'CHILDREN' => $child,
                    'DISPLAY' => 'block',
                    'MENU' => lorem_word_2(),
                    'TOP_LEVEL' => true,
                    'THE_LEVEL' => '0',
                    'POSITION' => '2',
                    'FIRST' => false,
                    'LAST' => false,
                    'BRETHREN_COUNT' => '3',
                    'CURRENT' => false,
                    'CURRENT_ZONE' => false,
                )));
            }
        }
        $menu = do_lorem_template('MENU_sitemap', array(
            'CONTENT' => $content,
            'MENU' => lorem_word_2(),
        ));

        $menu->attach(do_lorem_template('MENU_STAFF_LINK', array('TYPE' => 'sitemap', 'EDIT_URL' => placeholder_url(), 'NAME' => lorem_phrase())));

        return array(
            lorem_globalise($menu, null, '', true),
        );
    }
}
