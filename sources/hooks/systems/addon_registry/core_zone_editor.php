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
 * @package    core_zone_editor
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_zone_editor
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
        return 'Manage zones (sub-sites).';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array                    List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_structure',
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
        return 'themes/default/images/icons/48x48/menu/adminzone/structure/zones/zone_editor.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array                    List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/structure/zones/zone_editor.png',
            'themes/default/images/icons/48x48/menu/adminzone/structure/zones/zone_editor.png',
            'sources/hooks/systems/resource_meta_aware/zone.php',
            'themes/default/css/zone_editor.css',
            'sources/hooks/systems/snippets/exists_zone.php',
            'sources/hooks/systems/addon_registry/core_zone_editor.php',
            'themes/default/templates/ZONE_EDITOR_SCREEN.tpl',
            'themes/default/templates/ZONE_EDITOR_PANEL.tpl',
            'themes/default/javascript/zone_editor.js',
            'adminzone/pages/modules/admin_zones.php',
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
            'templates/ZONE_EDITOR_PANEL.tpl' => 'administrative__zone_editor_screen',
            'templates/ZONE_EDITOR_SCREEN.tpl' => 'administrative__zone_editor_screen'
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__zone_editor_screen()
    {
        require_lang('zones');
        $comcode_editor = do_lorem_template('COMCODE_EDITOR_BUTTON', array(
            'DIVIDER' => true,
            'FIELD_NAME' => lorem_word(),
            'TITLE' => lorem_word(),
            'B' => 'block',
        ));

        $editor = array();
        foreach (array( 'panel_left', 'panel_middle', 'panel_right') as $i => $for) {
            $editor[$for] = do_lorem_template('ZONE_EDITOR_PANEL', array(
                'CLASS' => '',
                'ZONES' => '',
                'CURRENT_ZONE' => '',
                'ZONE' => '',
                'COMCODE' => lorem_phrase(),
                'PREVIEW' => lorem_paragraph_html(),
                'ID' => $for,
                'IS_PANEL' => true,
                'TYPE' => lorem_phrase(),
                'EDIT_URL' => placeholder_url(),
                'SETTINGS' => null,
                'COMCODE_EDITOR' => $comcode_editor,
            ));
        }

        return array(
            lorem_globalise(do_lorem_template('ZONE_EDITOR_SCREEN', array(
                'PING_URL' => placeholder_url(),
                'WARNING_DETAILS' => '',
                'TITLE' => lorem_title(),
                'ID' => '',
                'LANG' => fallback_lang(),
                'URL' => placeholder_url(),
                'LEFT_EDITOR' => $editor['panel_left'],
                'RIGHT_EDITOR' => $editor['panel_right'],
                'MIDDLE_EDITOR' => $editor['panel_middle'],
            )), null, '', true)
        );
    }
}
