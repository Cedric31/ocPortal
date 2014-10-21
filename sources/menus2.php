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
 * Standard code module initialisation function.
 */
function init__menus2()
{
    global $ADD_MENU_COUNTER;
    $ADD_MENU_COUNTER = 10;
}

/**
 * Export a menu structure to a CSV file.
 *
 * @param  ?PATH			            The path to the CSV file (NULL: uploads/website_specific/ocp_menu_items.csv).
 */
function export_menu_csv($file_path=NULL)
{
	if (is_null($file_path)) {
		$file_path = get_custom_file_base() . '/uploads/website_specific/ocp_menu_items.csv';
	}

	if (!multi_lang_content())
	{
		$sql = 'SELECT i_menu, i_order, i_parent, i_url, i_check_permissions, i_expanded, i_new_window, i_page_only, i_theme_img_code, i_caption, i_caption_long FROM ocp_menu_items m';
	} else {
		$sql = 'SELECT i_menu, i_order, i_parent, i_url, i_check_permissions, i_expanded, i_new_window, i_page_only, i_theme_img_code, t1.text_original AS i_caption, t2.text_original AS i_caption_long FROM ocp_menu_items m JOIN ocp_translate t1 ON t1.id=m.i_caption JOIN ocp_translate t2 ON t2.id=m.i_caption_long';
	}

	$data = $GLOBALS['SITE_DB']->query($sql, NULL, NULL, false, true);

	require_code('files2');
	$csv = make_csv($data, 'data.csv', false, false);
	file_put_contents($file_path, $csv);
	fix_permissions($file_path);
	sync_file($file_path);
}

/**
* Import a CSV menu structure, after ERASING whole current menu structure.
* This function is intended for programmers, writing upgrade scripts for a custom site (dev>staging>live).
* Assumes CSV was generated with export_menu_csv.
 *
 * @param  ?PATH			            The path to the CSV file (NULL: uploads/website_specific/ocp_menu_items.csv).
 */
function import_menu_csv($file_path = NULL)
{
	$old_menu_items = $GLOBALS['SITE_DB']->query_select('menu_items', array('i_caption', 'i_caption_long'));
	foreach ($old_menu_items as $old_menu_item) {
		delete_lang($old_menu_item['i_caption']);
		delete_lang($old_menu_item['i_caption_long']);
	}
	$GLOBALS['SITE_DB']->query_delete('menu_items');

	if (is_null($file_path)) {
		$file_path = get_custom_file_base() . '/uploads/website_specific/ocp_menu_items.csv';
	}
	$myfile = fopen($file_path, 'rt');
	while (($record = fgetcsv($myfile, 8192)) !== false) {
		if (!isset($record[9])) continue;
		if (!isset($record[10])) $records[10] = '';
		if ($record[0] == 'i_menu') continue;

		$menu = $record[0];
		$order = intval($record[1]);
		$parent = ($record[2] == '' || $record[2] == 'NULL') ? NULL : intval($record[2]);
		$caption = $record[9];
		$url = $record[3];
		$check_permissions = intval($record[4]);
		$page_only = $record[7];
		$expanded = intval($record[5]);
		$new_window = intval($record[6]);
		$caption_long = $record[10];
		$theme_image_code = $record[8];

		add_menu_item($menu, $order, $parent, $caption, $url, $check_permissions, $page_only, $expanded, $new_window, $caption_long, $theme_image_code);
	}
	fclose($myfile);

	decache('side_stored_menu');
}

/**
 * Move a menu branch.
 */
function menu_management_script()
{
    $id = get_param_integer('id');
    $to_menu = get_param('menu');
    $changes = array('i_menu' => $to_menu);

    $rows = $GLOBALS['SITE_DB']->query_select('menu_items', array('*'), array('id' => $id), '', 1);
    if (array_key_exists(0, $rows)) {
        $row = $rows[0];
    } else {
        $row = null;
    }

    $test = false;

    foreach (array_keys($test ? $_GET : $_POST) as $key) {
        $val = $test ? get_param($key) : post_param($key);
        $key = preg_replace('#\_\d+$#', '', $key);
        if (($key == 'caption') || ($key == 'caption_long')) {
            if (is_null($row)) {
                $changes += insert_lang('i_' . $key, $val, 2);
            } else {
                $changes += lang_remap('i_' . $key, $row['i_' . $key], $val);
            }
        } elseif (($key == 'url') || ($key == 'theme_img_code')) {
            $changes['i_' . $key] = $val;
        } elseif ($key == 'match_tags') {
            $changes['i_page_only'] = $val;
        }
    }
    $changes['i_order'] = post_param_integer('order_' . strval($id), 0);
    $changes['i_new_window'] = post_param_integer('new_window_' . strval($id), 0);
    $changes['i_check_permissions'] = post_param_integer('check_perms_' . strval($id), 0);
    $changes['i_include_sitemap'] = post_param_integer('include_sitemap_' . strval($id), 0);
    $changes['i_expanded'] = 0;
    $changes['i_parent'] = null;

    if (is_null($row)) {
        $GLOBALS['SITE_DB']->query_insert('menu_items', $changes);
    } else {
        $GLOBALS['SITE_DB']->query_update('menu_items', $changes, array('id' => $id), '', 1);
    }
}

/**
 * Add a menu item, without giving tedious/unnecessary detail.
 *
 * @param  SHORT_TEXT                   The name of the menu to add the item to.
 * @param  ?mixed                       The menu item ID of the parent branch of the menu item (AUTO_LINK) / the URL of something else on the same menu (URLPATH) (NULL: is on root).
 * @param  SHORT_TEXT                   The caption.
 * @param  SHORT_TEXT                   The URL (in entry point form).
 * @param  BINARY                       Whether it is an expanded branch.
 * @param  BINARY                       Whether people who may not view the entry point do not see the link.
 * @param  boolean                      Whether the caption is a language code.
 * @param  SHORT_TEXT                   The tooltip (blank: none).
 * @param  BINARY                       Whether the link will open in a new window.
 * @param  ID_TEXT                      The theme image code.
 * @param  SHORT_INTEGER                An INCLUDE_SITEMAP_* constant
 * @param  ?integer                     Order to use (NULL: automatic, after the ones that have it specified).
 * @return AUTO_LINK                    The ID of the newly added menu item.
 */
function add_menu_item_simple($menu, $parent, $caption, $url = '', $expanded = 0, $check_permissions = 0, $dereference_caption = true, $caption_long = '', $new_window = 0, $theme_image_code = '', $include_sitemap = 0, $order = null)
{
    global $ADD_MENU_COUNTER;

    $id = $GLOBALS['SITE_DB']->query_select_value_if_there('menu_items', 'id', array('i_url' => $url, 'i_menu' => $menu));
    if (!is_null($id)) {
        return $id;
    } // Already exists
    if (is_string($parent)) {
        $parent = $GLOBALS['SITE_DB']->query_select_value_if_there('menu_items', 'i_parent', array('i_url' => $parent));
    }

    $_caption = (strpos($caption, ':') === false) ? do_lang($caption, null, null, null, null, false) : null;
    if (is_null($_caption)) {
        $_caption = $caption;
    }
    $id = add_menu_item($menu, $ADD_MENU_COUNTER, $parent, $dereference_caption ? $_caption : $caption, $url, $check_permissions, '', $expanded, $new_window, $caption_long, $theme_image_code, $include_sitemap);

    $ADD_MENU_COUNTER++;

    return $id;
}

/**
 * Delete a menu item, without giving tedious/unnecessary detail.
 *
 * @param  SHORT_TEXT                   The URL (in entry point form).
 */
function delete_menu_item_simple($url)
{
    $GLOBALS['SITE_DB']->query_delete('menu_items', array('i_url' => $url));

    $_id = $GLOBALS['SITE_DB']->query_select('menu_items', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('i_caption') => $url));
    foreach ($_id as $id) {
        $GLOBALS['SITE_DB']->query_delete('menu_items', array('i_caption' => $id['id']));
    }
}

/**
 * Add a menu item.
 *
 * @param  SHORT_TEXT                   The name of the menu to add the item to.
 * @param  integer                      The relative order of this item on the menu.
 * @param  ?AUTO_LINK                   The menu item ID of the parent branch of the menu item (NULL: is on root).
 * @param  SHORT_TEXT                   The caption.
 * @param  SHORT_TEXT                   The URL (in entry point form).
 * @param  BINARY                       Whether people who may not view the entry point do not see the link.
 * @param  SHORT_TEXT                   Match-keys to identify what pages the item is shown on.
 * @param  BINARY                       Whether it is an expanded branch.
 * @param  BINARY                       Whether the link will open in a new window.
 * @param  SHORT_TEXT                   The tooltip (blank: none).
 * @param  ID_TEXT                      The theme image code.
 * @param  SHORT_INTEGER                An INCLUDE_SITEMAP_* constant
 * @return AUTO_LINK                    The ID of the newly added menu item.
 */
function add_menu_item($menu, $order, $parent, $caption, $url, $check_permissions, $page_only, $expanded, $new_window, $caption_long, $theme_image_code = '', $include_sitemap = 0)
{
    $map = array(
        'i_menu' => $menu,
        'i_order' => $order,
        'i_parent' => $parent,
        'i_url' => $url,
        'i_check_permissions' => $check_permissions,
        'i_page_only' => $page_only,
        'i_include_sitemap' => $include_sitemap,
        'i_expanded' => $expanded,
        'i_new_window' => $new_window,
        'i_theme_img_code' => $theme_image_code,
    );
    $map += insert_lang_comcode('i_caption', $caption, 1);
    $map += insert_lang_comcode('i_caption_long', $caption_long, 1);
    $id = $GLOBALS['SITE_DB']->query_insert('menu_items', $map, true);

    log_it('ADD_MENU_ITEM', strval($id), $caption);

    if ((addon_installed('occle')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resourcefs_moniker('menu_item', strval($id), null, null, true);
    }

    return $id;
}

/**
 * Edit a menu item.
 *
 * @param  AUTO_LINK                    The ID of the menu item to edit.
 * @param  SHORT_TEXT                   The name of the menu to add the item to.
 * @param  integer                      The relative order of this item on the menu.
 * @param  ?AUTO_LINK                   The menu item ID of the parent branch of the menu item (NULL: is on root).
 * @param  SHORT_TEXT                   The caption.
 * @param  SHORT_TEXT                   The URL (in entry point form).
 * @param  BINARY                       Whether people who may not view the entry point do not see the link.
 * @param  SHORT_TEXT                   Match-keys to identify what pages the item is shown on.
 * @param  BINARY                       Whether it is an expanded branch.
 * @param  BINARY                       Whether the link will open in a new window.
 * @param  SHORT_TEXT                   The tooltip (blank: none).
 * @param  ID_TEXT                      The theme image code.
 * @param  SHORT_INTEGER                An INCLUDE_SITEMAP_* constant
 */
function edit_menu_item($id, $menu, $order, $parent, $caption, $url, $check_permissions, $page_only, $expanded, $new_window, $caption_long, $theme_image_code, $include_sitemap)
{
    $_caption = $GLOBALS['SITE_DB']->query_select_value('menu_items', 'i_caption', array('id' => $id));
    $_caption_long = $GLOBALS['SITE_DB']->query_select_value('menu_items', 'i_caption_long', array('id' => $id));

    $map = array(
        'i_menu' => $menu,
        'i_order' => $order,
        'i_parent' => $parent,
        'i_url' => $url,
        'i_check_permissions' => $check_permissions,
        'i_page_only' => $page_only,
        'i_expanded' => $expanded,
        'i_new_window' => $new_window,
        'i_include_sitemap' => $include_sitemap,
    );
    $map += lang_remap_comcode('i_caption', $_caption, $caption);
    $map += lang_remap_comcode('i_caption_long', $_caption_long, $caption_long);
    $GLOBALS['SITE_DB']->query_update('menu_items', $map, array('id' => $id), '', 1);

    log_it('EDIT_MENU_ITEM', strval($id), $caption);

    if ((addon_installed('occle')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resourcefs_moniker('menu_item', strval($id));
    }
}

/**
 * Delete a menu item.
 *
 * @param  AUTO_LINK                    The ID of the menu item to delete.
 */
function delete_menu_item($id)
{
    $_caption = $GLOBALS['SITE_DB']->query_select_value('menu_items', 'i_caption', array('id' => $id));
    $_caption_long = $GLOBALS['SITE_DB']->query_select_value('menu_items', 'i_caption_long', array('id' => $id));
    $GLOBALS['SITE_DB']->query_delete('menu_items', array('id' => $id), '', 1);
    $caption = get_translated_text($_caption);
    delete_lang($_caption);
    delete_lang($_caption_long);

    log_it('DELETE_MENU_ITEM', strval($id), $caption);

    if ((addon_installed('occle')) && (!running_script('install'))) {
        require_code('resource_fs');
        expunge_resourcefs_moniker('menu_item', strval($id));
    }
}

/**
 * Delete a menu.
 *
 * @param  ID_TEXT                      The ID of the menu.
 */
function delete_menu($menu_id)
{
    // Get language codes currently used
    $old_menu_bits = list_to_map('id', $GLOBALS['SITE_DB']->query_select('menu_items', array('id', 'i_caption', 'i_caption_long'), array('i_menu' => $menu_id)));

    // Erase old stuff
    foreach ($old_menu_bits as $menu_item_id => $lang_code) {
        $GLOBALS['SITE_DB']->query_delete('menu_items', array('id' => $menu_item_id));
        delete_lang($lang_code['i_caption']);
        delete_lang($lang_code['i_caption_long']);
    }

    decache('menu');
    persistent_cache_delete(array('MENU', $menu_id));

    if ((addon_installed('occle')) && (!running_script('install'))) {
        require_code('resource_fs');
        expunge_resourcefs_moniker('menu', $menu_id);
    }
}

/**
 * Copy a part of the Sitemap to a new menu.
 *
 * @param  ID_TEXT                      The ID of the menu to save into.
 * @param  SHORT_TEXT                   Sitemap details.
 */
function copy_from_sitemap_to_new_menu($target_menu, $source)
{
    require_code('comcode_from_html');
    require_code('menus');
    $root = _build_sitemap_menu($source);
    $order = 0;
    _copy_from_sitemap_to_new_menu($target_menu, $root, $order);
}

/**
 * Copy a Sitemap node's children into a new menu.
 *
 * @param  ID_TEXT                      The ID of the menu to save into.
 * @param  array                        Sitemap node, containing children.
 * @param  integer                      Sequence order to save with.
 * @param  ?AUTO_LINK                   Menu parent ID (NULL: root).
 */
function _copy_from_sitemap_to_new_menu($target_menu, $node, &$order, $parent = null)
{
    if (isset($node['children'])) {
        foreach ($node['children'] as $child) {
            $theme_image_code = mixed();
            if (!is_null($child['extra_meta']['image'])) {
                $_theme_image_code = $child['extra_meta']['image'];
                if (substr($_theme_image_code, 0, strlen(get_custom_base_url() . '/')) == get_custom_base_url() . '/') {
                    $_theme_image_code = substr($_theme_image_code, strlen(get_custom_base_url() . '/'));
                    $theme_image_code = $GLOBALS['SITE_DB']->query_select_value_if_there('theme_images', 'id', array('path' => $_theme_image_code));
                }
            }

            $branch_id = add_menu_item(
                $target_menu,
                $order,
                $parent,
                semihtml_to_comcode($child['title']->evaluate(), true),
                is_null($child['page_link']) ? '' : $child['page_link'],
                1,
                '',
                1,
                0,
                is_null($child['extra_meta']['description']) ? '' : semihtml_to_comcode($child['extra_meta']['description']->evaluate(), true),
                is_null($theme_image_code) ? '' : $theme_image_code,
                0
            );

            $order++;

            _copy_from_sitemap_to_new_menu($target_menu, $child, $order, $branch_id);
        }
    }
}
