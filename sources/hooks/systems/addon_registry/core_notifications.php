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
 * @package    core_notifications
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_notifications
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
        return 'Sends out action-triggered notifications to members listening to them.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array                    List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_notifications',
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
        return 'themes/default/images/icons/48x48/tool_buttons/notifications2.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array                    List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/tool_buttons/notifications.png',
            'themes/default/images/icons/48x48/tool_buttons/notifications.png',
            'themes/default/images/icons/24x24/tool_buttons/notifications2.png',
            'themes/default/images/icons/48x48/tool_buttons/notifications2.png',
            'themes/default/images/icons/24x24/menu/adminzone/setup/notifications.png',
            'themes/default/images/icons/48x48/menu/adminzone/setup/notifications.png',
            'themes/default/images/icons/24x24/buttons/disable_notifications.png',
            'themes/default/images/icons/24x24/buttons/enable_notifications.png',
            'themes/default/images/icons/48x48/buttons/disable_notifications.png',
            'themes/default/images/icons/48x48/buttons/enable_notifications.png',
            'sources/hooks/systems/addon_registry/core_notifications.php',
            'sources/hooks/systems/occle_fs_extended_config/notification_lockdown.php',
            'sources/notifications.php',
            'sources/notifications2.php',
            'lang/EN/notifications.ini',
            'sources/hooks/systems/cron/notification_digests.php',
            'sources/hooks/systems/notifications/.htaccess',
            'sources/hooks/systems/notifications/index.html',
            'sources/hooks/systems/profiles_tabs_edit/notifications.php',
            'themes/default/css/notifications.css',
            'themes/default/javascript/notifications.js',
            'themes/default/templates/NOTIFICATIONS_MANAGE.tpl',
            'themes/default/templates/NOTIFICATIONS_MANAGE_SCREEN.tpl',
            'themes/default/templates/NOTIFICATIONS_MANAGE_ADVANCED_SCREEN.tpl',
            'themes/default/templates/NOTIFICATIONS_TREE.tpl',
            'themes/default/templates/NOTIFICATION_TYPES.tpl',
            'themes/default/templates/NOTIFICATION_BUTTONS.tpl',
            'site/pages/modules/notifications.php',
            'adminzone/pages/modules/admin_notifications.php',
            'sources/hooks/systems/page_groupings/notifications.php',
            'sources/hooks/systems/config/allow_auto_notifications.php',
            'sources/hooks/systems/config/pt_notifications_as_web.php',
            'sources/hooks/systems/config/notification_keep_days.php',
            'sources/hooks/systems/config/web_notifications_enabled.php',
            'sources/hooks/systems/config/notification_poll_frequency.php',
            'data/notifications.php',
            'sources/blocks/top_notifications.php',
            'sources/hooks/systems/startup/notification_poller_init.php',
            'sources/notification_poller.php',
            'themes/default/javascript/notification_poller.js',
            'themes/default/templates/NOTIFICATION_POLLER.tpl',
            'themes/default/templates/NOTIFICATION_WEB.tpl',
            'themes/default/templates/NOTIFICATION_WEB_DESKTOP.tpl',
            'themes/default/templates/NOTIFICATION_PT_DESKTOP.tpl',
            'themes/default/templates/BLOCK_TOP_NOTIFICATIONS.tpl',
            'themes/default/templates/NOTIFICATION_BROWSE_SCREEN.tpl',
            'themes/default/templates/NOTIFICATION_VIEW_SCREEN.tpl',
            'themes/default/images/notifications/notifications.ico',
            'themes/default/images/notifications/index.html',
            'data_custom/modules/web_notifications/.htaccess',
            'data_custom/modules/web_notifications/index.html',
            'sources/hooks/systems/tasks/dispatch_notification.php',
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
            'templates/NOTIFICATIONS_MANAGE.tpl' => 'notifications_regular',
            'templates/NOTIFICATIONS_MANAGE_SCREEN.tpl' => 'notifications_regular',
            'templates/NOTIFICATIONS_MANAGE_ADVANCED_SCREEN.tpl' => 'notifications_advanced',
            'templates/NOTIFICATIONS_TREE.tpl' => 'notifications_advanced',
            'templates/NOTIFICATION_TYPES.tpl' => 'notifications_regular',
            'templates/NOTIFICATION_WEB.tpl' => 'notification_web',
            'templates/NOTIFICATION_WEB_DESKTOP.tpl' => 'notification_web_desktop',
            'templates/NOTIFICATION_PT_DESKTOP.tpl' => 'notification_pt_desktop',
            'templates/BLOCK_TOP_NOTIFICATIONS.tpl' => 'block_top_notifications',
            'templates/NOTIFICATION_POLLER.tpl' => 'notification_poller',
            'templates/NOTIFICATION_BROWSE_SCREEN.tpl' => 'notification_browse_screen',
            'templates/NOTIFICATION_VIEW_SCREEN.tpl' => 'notification_view_screen',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__notification_browse_screen()
    {
        $notifications = new Tempcode();
        $notifications->attach(
            do_lorem_template('NOTIFICATION_WEB', array(
                'ID' => placeholder_id(),
                'SUBJECT' => lorem_phrase(),
                'MESSAGE' => lorem_paragraph(),
                'FROM_USERNAME' => lorem_phrase(),
                'FROM_MEMBER_ID' => placeholder_id(),
                'FROM_URL' => placeholder_url(),
                'FROM_AVATAR_URL' => placeholder_image_url(),
                'PRIORITY' => '3',
                'DATE_TIMESTAMP' => placeholder_date_raw(),
                'DATE_WRITTEN_TIME' => placeholder_time(),
                'NOTIFICATION_CODE' => placeholder_id(),
                'CODE_CATEGORY' => placeholder_id(),
                'HAS_READ' => false,
            ))
        );

        $out = do_lorem_template('NOTIFICATION_BROWSE_SCREEN', array(
            'TITLE' => lorem_title(),
            'NOTIFICATIONS' => $notifications,
            'PAGINATION' => placeholder_pagination(),
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__notification_view_screen()
    {
        $out = do_lorem_template('NOTIFICATION_VIEW_SCREEN', array(
            'TITLE' => lorem_title(),
            'ID' => placeholder_id(),
            'SUBJECT' => lorem_phrase(),
            'MESSAGE' => lorem_paragraph(),
            'FROM_USERNAME' => lorem_phrase(),
            'FROM_MEMBER_ID' => placeholder_id(),
            'FROM_URL' => placeholder_url(),
            'FROM_AVATAR_URL' => placeholder_image_url(),
            'PRIORITY' => '3',
            'DATE_TIMESTAMP' => placeholder_date_raw(),
            'DATE_WRITTEN_TIME' => placeholder_time(),
            'NOTIFICATION_CODE' => placeholder_id(),
            'CODE_CATEGORY' => placeholder_id(),
            'HAS_READ' => true,
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__notification_poller()
    {
        $out = do_lorem_template('NOTIFICATION_POLLER', array());

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__notification_web()
    {
        $out = do_lorem_template('NOTIFICATION_WEB', array(
            'ID' => placeholder_id(),
            'SUBJECT' => lorem_phrase(),
            'MESSAGE' => lorem_paragraph(),
            'FROM_USERNAME' => lorem_phrase(),
            'FROM_MEMBER_ID' => placeholder_id(),
            'FROM_URL' => placeholder_url(),
            'FROM_AVATAR_URL' => placeholder_image_url(),
            'PRIORITY' => '3',
            'DATE_TIMESTAMP' => placeholder_date_raw(),
            'DATE_WRITTEN_TIME' => placeholder_time(),
            'NOTIFICATION_CODE' => placeholder_id(),
            'CODE_CATEGORY' => placeholder_id(),
            'HAS_READ' => true,
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__notification_web_desktop()
    {
        $out = do_lorem_template('NOTIFICATION_WEB_DESKTOP', array(
            'ID' => placeholder_id(),
            'SUBJECT' => lorem_phrase(),
            'MESSAGE' => lorem_paragraph(),
            'FROM_USERNAME' => lorem_phrase(),
            'FROM_MEMBER_ID' => placeholder_id(),
            'FROM_URL' => placeholder_url(),
            'FROM_AVATAR_URL' => placeholder_image_url(),
            'PRIORITY' => '3',
            'DATE_TIMESTAMP' => placeholder_date_raw(),
            'DATE_WRITTEN_TIME' => placeholder_time(),
            'NOTIFICATION_CODE' => placeholder_id(),
            'CODE_CATEGORY' => placeholder_id(),
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__notification_pt_desktop()
    {
        $out = do_lorem_template('NOTIFICATION_PT_DESKTOP', array(
            'ID' => placeholder_id(),
            'SUBJECT' => lorem_phrase(),
            'MESSAGE' => lorem_paragraph(),
            'FROM_USERNAME' => lorem_phrase(),
            'FROM_MEMBER_ID' => placeholder_id(),
            'URL' => placeholder_url(),
            'FROM_AVATAR_URL' => placeholder_image_url(),
            'DATE_TIMESTAMP' => placeholder_date_raw(),
            'DATE_WRITTEN_TIME' => placeholder_time(),
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_top_notifications()
    {
        $out = do_lorem_template('BLOCK_TOP_NOTIFICATIONS', array(
            'NUM_UNREAD_WEB_NOTIFICATIONS' => placeholder_number(),
            'NUM_UNREAD_PTS' => placeholder_number(),
            'NOTIFICATIONS' => '',
            'PTS' => '',
            'MAX' => '5',
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__notifications_regular()
    {
        require_css('notifications');
        require_javascript('notifications');

        $notification_types = array();
        $notification_types[] = array(
            'NTYPE' => placeholder_id(),
            'LABEL' => lorem_phrase(),
            'CHECKED' => true,
            'RAW' => placeholder_number(),
            'AVAILABLE' => true,
            'SCOPE' => placeholder_id(),
        );
        $notification_types_titles = array();
        $notification_types_titles[] = array(
            'NTYPE' => placeholder_id(),
            'LABEL' => lorem_phrase(),
            'RAW' => placeholder_number(),
        );
        $notification_code_map = array(
            'NOTIFICATION_CODE' => placeholder_id(),
            'NOTIFICATION_LABEL' => lorem_phrase(),
            'NOTIFICATION_TYPES' => $notification_types,
            'SUPPORTS_CATEGORIES' => true,
        );
        do_lorem_template('NOTIFICATION_TYPES', $notification_code_map); // To make coverage test pass (is actually INCLUDE'd)
        $notification_sections = array();
        $notification_sections[lorem_phrase()] = array(
            'NOTIFICATION_SECTION' => lorem_phrase(),
            'NOTIFICATION_CODES' => array(
                $notification_code_map
            )
        );
        $interface = do_lorem_template('NOTIFICATIONS_MANAGE', array(
            'COLOR' => 'FFFFFF',
            'NOTIFICATION_TYPES_TITLES' => $notification_types_titles,
            'NOTIFICATION_SECTIONS' => $notification_sections,
            'AUTO_NOTIFICATION_CONTRIB_CONTENT' => false,
            'SMART_TOPIC_NOTIFICATION_CONTENT' => false,
            'MEMBER_ID' => placeholder_id(),
        ));
        $out = do_lorem_template('NOTIFICATIONS_MANAGE_SCREEN', array(
            'TITLE' => lorem_title(),
            'INTERFACE' => $interface,
            'ACTION_URL' => get_self_url(),
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__notifications_advanced()
    {
        require_css('notifications');
        require_javascript('notifications');

        $notification_types = array();
        $notification_types[] = array(
            'NTYPE' => placeholder_id(),
            'LABEL' => lorem_phrase(),
            'CHECKED' => true,
            'RAW' => placeholder_number(),
            'AVAILABLE' => true,
            'SCOPE' => placeholder_id(),
        );
        $notification_categories = array();
        $notification_categories[] = array(
            'NUM_CHILDREN' => '0',
            'DEPTH' => '0',
            'NOTIFICATION_CATEGORY' => placeholder_id(),
            'NOTIFICATION_TYPES' => $notification_types,
            'CATEGORY_TITLE' => lorem_phrase(),
            'CHECKED' => true,
            'CHILDREN' => '',
        );
        $tree = do_lorem_template('NOTIFICATIONS_TREE', array(
            'NOTIFICATION_CODE' => placeholder_id(),
            'NOTIFICATION_CATEGORIES' => $notification_categories,
        ));
        $notification_types_titles = array();
        $notification_types_titles[] = array(
            'NTYPE' => placeholder_id(),
            'LABEL' => lorem_phrase(),
            'RAW' => placeholder_number(),
        );
        $out = do_lorem_template('NOTIFICATIONS_MANAGE_ADVANCED_SCREEN', array(
            'TITLE' => lorem_title(),
            '_TITLE' => lorem_phrase(),
            'COLOR' => 'FFFFFF',
            'ACTION_URL' => placeholder_url(),
            'NOTIFICATION_TYPES_TITLES' => $notification_types_titles,
            'TREE' => $tree,
            'NOTIFICATION_CODE' => placeholder_id(),
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }
}
