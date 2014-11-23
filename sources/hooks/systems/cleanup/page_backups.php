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
 * @package    core_cleanup_tools
 */

/**
 * Hook class.
 */
class Hook_cleanup_page_backups
{
    /**
     * Find details about this cleanup hook.
     *
     * @return ?array                   Map of cleanup hook info (null: hook is disabled).
     */
    public function info()
    {
        if (!is_suexec_like()) {
            return null;
        }

        $info = array();
        $info['title'] = do_lang_tempcode('ARCHIVE_PAGE_BACKUPS');
        $info['description'] = do_lang_tempcode('DESCRIPTION_ARCHIVE_PAGE_BACKUPS');
        $info['type'] = 'optimise';

        return $info;
    }

    /**
     * Run the cleanup hook action.
     *
     * @return tempcode                 Results
     */
    public function run()
    {
        $langs = array_keys(find_all_langs());

        // Zones: Comcode pages
        $start = 0;
        do {
            $zones = find_all_zones(false, false, false, $start, 50);
            foreach ($zones as $zone) {
                foreach ($langs as $lang) {
                    $path = get_custom_file_base() . '/' . filter_naughty($zone) . '/pages/comcode_custom/' . filter_naughty($lang);
                    $this->process($path);
                }
            }
            $start += 50;
        }
        while (count($zones) != 0);

        // Themes: Templates (various kinds, including CSS files)
        $themes = find_all_themes();
        foreach ($themes as $theme) {
            $path = get_custom_file_base() . '/themes/' . filter_naughty($theme) . '/templates_custom';
            $this->process($path);

            $path = get_custom_file_base() . '/themes/' . filter_naughty($theme) . '/javascript_custom';
            $this->process($path);

            $path = get_custom_file_base() . '/themes/' . filter_naughty($theme) . '/xml_custom';
            $this->process($path);

            $path = get_custom_file_base() . '/themes/' . filter_naughty($theme) . '/text_custom';
            $this->process($path);

            $path = get_custom_file_base() . '/themes/' . filter_naughty($theme) . '/css_custom';
            $this->process($path);
        }

        return new Tempcode();
    }

    /**
     * Move revision files from the given path, to a subdirectory.
     *
     * @param  PATH                     Path
     */
    public function process($path)
    {
        $dh = @opendir($path);
        if ($dh !== false) {
            if (!file_exists($path . '/_old_backups')) {
                mkdir($path . '/_old_backups', 0777);
                fix_permissions($path . '/_old_backups', 0777);
            }

            while (($f = readdir($dh)) !== false) {
                if (is_numeric(get_file_extension($f))) {
                    rename($path . '/' . $f, $path . '/_old_backups/' . $f);
                }
            }
            closedir($dh);
        }
    }
}
