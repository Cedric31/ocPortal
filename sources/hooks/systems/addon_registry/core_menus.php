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
 * @package		core_menus
 */

class Hook_addon_registry_core_menus
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
		return 'Edit menus.';
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

			'themes/default/images/menu_items/management_navigation/cms.png',
			'themes/default/images/menu_items/management_navigation/docs.png',
			'themes/default/images/menu_items/management_navigation/index.html',
			'themes/default/images/menu_items/management_navigation/security.png',
			'themes/default/images/menu_items/management_navigation/setup.png',
			'themes/default/images/menu_items/management_navigation/start.png',
			'themes/default/images/menu_items/management_navigation/structure.png',
			'themes/default/images/menu_items/management_navigation/style.png',
			'themes/default/images/menu_items/management_navigation/tools.png',
			'themes/default/images/menu_items/management_navigation/usage.png',
			'sources/hooks/systems/addon_registry/core_menus.php',
			'MENU_dropdown.tpl',
			'MENU_embossed.tpl',
			'MENU_popup.tpl',
			'MENU_select.tpl',
			'MENU_top.tpl',
			'MENU_tree.tpl',
			'MENU_zone.tpl',
			'MENU_BRANCH_dropdown.tpl',
			'MENU_BRANCH_embossed.tpl',
			'MENU_BRANCH_popup.tpl',
			'MENU_BRANCH_select.tpl',
			'MENU_BRANCH_top.tpl',
			'MENU_BRANCH_tree.tpl',
			'MENU_BRANCH_zone.tpl',
			'MENU_SPACER_dropdown.tpl',
			'MENU_SPACER_embossed.tpl',
			'MENU_SPACER_popup.tpl',
			'MENU_SPACER_select.tpl',
			'MENU_SPACER_top.tpl',
			'MENU_SPACER_tree.tpl',
			'MENU_SPACER_zone.tpl',
			'JAVASCRIPT_MENU_POPUP.tpl',
			'MENU_STAFF_LINK.tpl',
			'MENU_EDITOR_BRANCH.tpl',
			'MENU_EDITOR_SCREEN.tpl',
			'MENU_EDITOR_BRANCH_WRAP.tpl',
			'JAVASCRIPT_MENU_EDITOR.tpl',
			'BLOCK_SIDE_STORED_MENU.tpl',
			'MENU_LINK_PROPERTIES.tpl',
			'adminzone/pages/modules/admin_menus.php',
			'adminzone/menu_management.php',
			'themes/default/images/bigicons/menus.png',
			'themes/default/images/bottom/managementmenu.png',
			'themes/default/images/bottom/managementmenu_off.png',
			'themes/default/images/menus/index.html',
			'themes/default/images/menus/menu.png',
			'themes/default/images/menus/menu_bullet.png',
			'themes/default/images/menus/menu_bullet_hover.png',
			'themes/default/images/menus/menu_bullet_expand.png',
			'themes/default/images/menus/menu_bullet_expand_hover.png',
			'themes/default/images/menus/menu_bullet_current.png',
			'themes/default/images/pagepics/menus.png',
			'lang/EN/menus.ini',
			'sources/blocks/side_stored_menu.php',
			'sources/hooks/systems/snippets/management_menu.php',
			'sources/menus.php',
			'sources/menus_sitemap.php',
			'sources/menus_bookmarks.php',
			'sources/menus_comcode.php',
			'sources/menus2.php',
			'themes/default/images/menu_items/index.html',
			'PAGE_LINK_CHOOSER.tpl',
			'data/page_link_chooser.php',
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
				'MENU_EDITOR_BRANCH.tpl'=>'administrative__menu_editor_screen',
				'MENU_EDITOR_BRANCH_WRAP.tpl'=>'administrative__menu_editor_screen',
				'MENU_EDITOR_SCREEN.tpl'=>'administrative__menu_editor_screen',
				'PAGE_LINK_CHOOSER.tpl'=>'page_link_chooser',
				'BLOCK_SIDE_STORED_MENU.tpl'=>'block_side_stored_menu__tree',
				'MENU_STAFF_LINK.tpl'=>'block_side_stored_menu__tree',

				'MENU_SPACER_tree.tpl'=>'block_side_stored_menu__tree',
				'MENU_BRANCH_tree.tpl'=>'block_side_stored_menu__tree',
				'MENU_tree.tpl'=>'block_side_stored_menu__tree',

				'MENU_SPACER_dropdown.tpl'=>'block_side_stored_menu__dropdown',
				'MENU_BRANCH_dropdown.tpl'=>'block_side_stored_menu__dropdown',
				'MENU_dropdown.tpl'=>'block_side_stored_menu__dropdown',

				'MENU_SPACER_embossed.tpl'=>'block_side_stored_menu__embossed',
				'MENU_BRANCH_embossed.tpl'=>'block_side_stored_menu__embossed',
				'MENU_embossed.tpl'=>'block_side_stored_menu__embossed',

				'MENU_SPACER_popup.tpl'=>'block_side_stored_menu__popup',
				'MENU_BRANCH_popup.tpl'=>'block_side_stored_menu__popup',
				'MENU_popup.tpl'=>'block_side_stored_menu__popup',

				'MENU_SPACER_select.tpl'=>'block_side_stored_menu__select',
				'MENU_BRANCH_select.tpl'=>'block_side_stored_menu__select',
				'MENU_select.tpl'=>'block_side_stored_menu__select',

				'MENU_SPACER_top.tpl'=>'block_side_stored_menu__top',
				'MENU_BRANCH_top.tpl'=>'block_side_stored_menu__top',
				'MENU_top.tpl'=>'block_side_stored_menu__top',

				'MENU_SPACER_zone.tpl'=>'block_side_stored_menu__zone',
				'MENU_BRANCH_zone.tpl'=>'block_side_stored_menu__zone',
				'MENU_zone.tpl'=>'block_side_stored_menu__zone',

				'MENU_LINK_PROPERTIES.tpl'=>'block_side_stored_menu__top',
			);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__menu_editor_screen()
	{
		$branch=do_lorem_template('MENU_EDITOR_BRANCH',array('CLICKABLE_SECTIONS'=>'true','I'=>placeholder_id(),'CHILD_BRANCH_TEMPLATE'=>'','CHILD_BRANCHES'=>''));

		$child_branch_template=do_lorem_template('MENU_EDITOR_BRANCH_WRAP',array('DISPLAY'=>'display: block','CLICKABLE_SECTIONS'=>true,'ORDER'=>'replace_me_with_order','PARENT'=>'replace_me_with_parent','BRANCH_TYPE'=>'0','NEW_WINDOW'=>'0','CHECK_PERMS'=>'0','CAPTION_LONG'=>'','CAPTION'=>'','URL'=>'','PAGE_ONLY'=>'','THEME_IMG_CODE'=>'','I'=>placeholder_id(),'BRANCH'=>$branch));

		$root_branch=do_lorem_template('MENU_EDITOR_BRANCH',array('CLICKABLE_SECTIONS'=>'true','CHILD_BRANCH_TEMPLATE'=>$child_branch_template,'CHILD_BRANCHES'=>'','I'=>''));

		return array(
			lorem_globalise(
				do_lorem_template('MENU_EDITOR_SCREEN',array(
					'ALL_MENUS'=>placeholder_array(),
					'MENU_NAME'=>lorem_word(),
					'DELETE_URL'=>placeholder_url(),
					'PING_URL'=>placeholder_url(),
					'WARNING_DETAILS'=>'',
					'FIELDS_TEMPLATE'=>placeholder_fields(),
					'HIGHEST_ORDER'=>lorem_phrase(),
					'URL'=>placeholder_url(),
					'CHILD_BRANCH_TEMPLATE'=>$child_branch_template,
					'ROOT_BRANCH'=>$root_branch,
					'TITLE'=>lorem_title(),
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
	function tpl_preview__page_link_chooser()
	{
		require_javascript('javascript_tree_list');
		return array(
			lorem_globalise(
				do_lorem_template('PAGE_LINK_CHOOSER',array(
					'NAME'=>lorem_word(),
					'VALUE'=>lorem_word(),
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
	function tpl_preview__block_side_stored_menu__tree()
	{
		$child = new ocp_tempcode();
		$content = new ocp_tempcode();
		foreach(placeholder_array(3) as $v)
		{
			$child->attach(do_lorem_template('MENU_BRANCH_tree',array(
					'RANDOM'=>placeholder_random(),
					'CAPTION'=>lorem_word(),
					'IMG'=>'',
					'URL'=>placeholder_url(),
					'PAGE_LINK'=>placeholder_link(),
					'ACCESSKEY'=>'',
					'POPUP'=>false,
					'POPUP_WIDTH'=>'',
					'POPUP_HEIGHT'=>'',
					'NEW_WINDOW'=>false,
					'TOOLTIP'=>lorem_phrase(),
					'CHILDREN'=>'',
					'DISPLAY'=>'block',
					'MENU'=>lorem_word_2(),
					'TOP_LEVEL'=>false,
					'THE_LEVEL'=>'2',
					'POSITION'=>'1',
					'LAST'=>false,
					'BRETHREN_COUNT'=>'3',
					'CURRENT'=>false,
					'CURRENT_ZONE'=>false,
						)
				));
		}
		foreach(placeholder_array(3) as $v)
		{
			$content->attach(do_lorem_template('MENU_BRANCH_tree',array(
				'RANDOM'=>placeholder_random(),
				'CAPTION'=>lorem_word(),
				'IMG'=>'',
				'URL'=>placeholder_url(),
				'PAGE_LINK'=>placeholder_link(),
				'ACCESSKEY'=>'',
				'POPUP'=>true,
				'POPUP_WIDTH'=>'500',
				'POPUP_HEIGHT'=>'500',
				'NEW_WINDOW'=>false,
				'TOOLTIP'=>lorem_phrase(),
				'CHILDREN'=>$child,
				'DISPLAY'=>'block',
				'MENU'=>lorem_word_2(),
				'TOP_LEVEL'=>true,
				'THE_LEVEL'=>'0',
				'POSITION'=>'2',
				'LAST'=>false,
				'BRETHREN_COUNT'=>'3',
				'CURRENT'=>false,
				'CURRENT_ZONE'=>False,
					))
				);

				$content->attach(do_lorem_template('MENU_SPACER_tree',array()));
		}
		$menu = do_lorem_template('MENU_tree',array(
					'CONTENT'=>$content,
					'MENU'=>'test',
						));

		$menu->attach(do_lorem_template('MENU_STAFF_LINK',array('TYPE'=>'tree','EDIT_URL'=>placeholder_url(),'NAME'=>lorem_phrase())));

		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_STORED_MENU',array(
					'CONTENT'=>$menu,
					'PARAM'=>lorem_phrase(),
					'TRAY_STATUS'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
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
	function tpl_preview__block_side_stored_menu__dropdown()
	{
		$child = new ocp_tempcode();
		$content = new ocp_tempcode();
		foreach(placeholder_array(3) as $v)
		{
			$child->attach(do_lorem_template('MENU_BRANCH_dropdown',array(
					'RANDOM'=>placeholder_random(),
					'CAPTION'=>lorem_word(),
					'IMG'=>'',
					'URL'=>placeholder_url(),
					'PAGE_LINK'=>placeholder_link(),
					'ACCESSKEY'=>'',
					'POPUP'=>false,
					'POPUP_WIDTH'=>'',
					'POPUP_HEIGHT'=>'',
					'NEW_WINDOW'=>false,
					'TOOLTIP'=>lorem_phrase(),
					'CHILDREN'=>'',
					'DISPLAY'=>'block',
					'MENU'=>'test',
					'TOP_LEVEL'=>false,
					'THE_LEVEL'=>'2',
					'POSITION'=>'1',
					'LAST'=>false,
					'BRETHREN_COUNT'=>'3',
					'CURRENT'=>false,
					'CURRENT_ZONE'=>false,
						)
				));

			$child->attach(do_lorem_template('MENU_SPACER_dropdown',array()));
		}
		foreach(placeholder_array(3) as $v)
		{
			$content->attach(do_lorem_template('MENU_BRANCH_dropdown',array(
				'RANDOM'=>placeholder_random(),
				'CAPTION'=>lorem_word(),
				'IMG'=>'',
				'URL'=>placeholder_url(),
				'PAGE_LINK'=>placeholder_link(),
				'ACCESSKEY'=>'',
				'POPUP'=>true,
				'POPUP_WIDTH'=>'500',
				'POPUP_HEIGHT'=>'500',
				'NEW_WINDOW'=>false,
				'TOOLTIP'=>lorem_phrase(),
				'CHILDREN'=>$child,
				'DISPLAY'=>'block',
				'MENU'=>'test',
				'TOP_LEVEL'=>true,
				'THE_LEVEL'=>'0',
				'POSITION'=>'2',
				'LAST'=>false,
				'BRETHREN_COUNT'=>'3',
				'CURRENT'=>false,
				'CURRENT_ZONE'=>False,
					))
				);
		}
		$menu = do_lorem_template('MENU_dropdown',array(
					'CONTENT'=>$content,
					'MENU'=>'test',
						));

		$menu->attach(do_lorem_template('MENU_STAFF_LINK',array('TYPE'=>'dropdown','EDIT_URL'=>placeholder_url(),'NAME'=>lorem_phrase())));

		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_STORED_MENU',array(
					'CONTENT'=>$menu,
					'PARAM'=>lorem_phrase(),
					'TRAY_STATUS'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
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
	function tpl_preview__block_side_stored_menu__embossed()
	{
		$child = new ocp_tempcode();
		$content = new ocp_tempcode();
		foreach(placeholder_array(3) as $v)
		{
			$child->attach(do_lorem_template('MENU_BRANCH_embossed',array(
					'RANDOM'=>placeholder_random(),
					'CAPTION'=>lorem_word(),
					'IMG'=>'',
					'URL'=>placeholder_url(),
					'PAGE_LINK'=>placeholder_link(),
					'ACCESSKEY'=>'',
					'POPUP'=>false,
					'POPUP_WIDTH'=>'',
					'POPUP_HEIGHT'=>'',
					'NEW_WINDOW'=>false,
					'TOOLTIP'=>lorem_phrase(),
					'CHILDREN'=>'',
					'DISPLAY'=>'block',
					'MENU'=>lorem_word_2(),
					'TOP_LEVEL'=>false,
					'THE_LEVEL'=>'2',
					'POSITION'=>'1',
					'LAST'=>false,
					'BRETHREN_COUNT'=>'3',
					'CURRENT'=>false,
					'CURRENT_ZONE'=>false,
						)
				));
		}
		foreach(placeholder_array(3) as $v)
		{
			$content->attach(do_lorem_template('MENU_BRANCH_embossed',array(
				'RANDOM'=>placeholder_random(),
				'CAPTION'=>lorem_word(),
				'IMG'=>'',
				'URL'=>placeholder_url(),
				'PAGE_LINK'=>placeholder_link(),
				'ACCESSKEY'=>'',
				'POPUP'=>true,
				'POPUP_WIDTH'=>'500',
				'POPUP_HEIGHT'=>'500',
				'NEW_WINDOW'=>false,
				'TOOLTIP'=>lorem_phrase(),
				'CHILDREN'=>$child,
				'DISPLAY'=>'block',
				'MENU'=>lorem_word_2(),
				'TOP_LEVEL'=>true,
				'THE_LEVEL'=>'0',
				'POSITION'=>'2',
				'LAST'=>false,
				'BRETHREN_COUNT'=>'3',
				'CURRENT'=>false,
				'CURRENT_ZONE'=>False,
					))
				);

				$content->attach(do_lorem_template('MENU_SPACER_embossed',array()));
		}
		$menu = do_lorem_template('MENU_embossed',array(
					'CONTENT'=>$content,
					'MENU'=>'test',
						));

		$menu->attach(do_lorem_template('MENU_STAFF_LINK',array('TYPE'=>'embossed','EDIT_URL'=>placeholder_url(),'NAME'=>lorem_phrase())));

		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_STORED_MENU',array(
					'CONTENT'=>$menu,
					'PARAM'=>lorem_phrase(),
					'TRAY_STATUS'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
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
	function tpl_preview__block_side_stored_menu__popup()
	{
		$child = new ocp_tempcode();
		$content = new ocp_tempcode();
		foreach(placeholder_array(3) as $v)
		{
			$child->attach(do_lorem_template('MENU_BRANCH_popup',array(
					'RANDOM'=>placeholder_random(),
					'CAPTION'=>lorem_word(),
					'IMG'=>'',
					'URL'=>placeholder_url(),
					'PAGE_LINK'=>placeholder_link(),
					'ACCESSKEY'=>'',
					'POPUP'=>false,
					'POPUP_WIDTH'=>'',
					'POPUP_HEIGHT'=>'',
					'NEW_WINDOW'=>false,
					'TOOLTIP'=>lorem_phrase(),
					'CHILDREN'=>'',
					'DISPLAY'=>'block',
					'MENU'=>lorem_word_2(),
					'TOP_LEVEL'=>false,
					'THE_LEVEL'=>'2',
					'POSITION'=>'1',
					'LAST'=>false,
					'BRETHREN_COUNT'=>'3',
					'CURRENT'=>false,
					'CURRENT_ZONE'=>false,
						)
				));
		}
		foreach(placeholder_array(3) as $v)
		{
			$content->attach(do_lorem_template('MENU_BRANCH_popup',array(
				'RANDOM'=>placeholder_random(),
				'CAPTION'=>lorem_word(),
				'IMG'=>'',
				'URL'=>placeholder_url(),
				'PAGE_LINK'=>placeholder_link(),
				'ACCESSKEY'=>'',
				'POPUP'=>true,
				'POPUP_WIDTH'=>'500',
				'POPUP_HEIGHT'=>'500',
				'NEW_WINDOW'=>false,
				'TOOLTIP'=>lorem_phrase(),
				'CHILDREN'=>$child,
				'DISPLAY'=>'block',
				'MENU'=>lorem_word_2(),
				'TOP_LEVEL'=>true,
				'THE_LEVEL'=>'0',
				'POSITION'=>'2',
				'LAST'=>false,
				'BRETHREN_COUNT'=>'3',
				'CURRENT'=>false,
				'CURRENT_ZONE'=>False,
					))
				);

				$content->attach(do_lorem_template('MENU_SPACER_popup',array()));
		}
		$menu = do_lorem_template('MENU_popup',array(
					'CONTENT'=>$content,
					'MENU'=>'test',
						));

		$menu->attach(do_lorem_template('MENU_STAFF_LINK',array('TYPE'=>'popup','EDIT_URL'=>placeholder_url(),'NAME'=>lorem_phrase())));

		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_STORED_MENU',array(
					'CONTENT'=>$menu,
					'PARAM'=>lorem_phrase(),
					'TRAY_STATUS'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
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
	function tpl_preview__block_side_stored_menu__select()
	{
		$child = new ocp_tempcode();
		$content = new ocp_tempcode();
		foreach(placeholder_array(3) as $v)
		{
			$child->attach(do_lorem_template('MENU_BRANCH_select',array(
					'RANDOM'=>placeholder_random(),
					'CAPTION'=>lorem_word(),
					'IMG'=>'',
					'URL'=>placeholder_url(),
					'PAGE_LINK'=>placeholder_link(),
					'ACCESSKEY'=>'',
					'POPUP'=>false,
					'POPUP_WIDTH'=>'',
					'POPUP_HEIGHT'=>'',
					'NEW_WINDOW'=>false,
					'TOOLTIP'=>lorem_phrase(),
					'CHILDREN'=>'',
					'DISPLAY'=>'block',
					'MENU'=>lorem_word_2(),
					'TOP_LEVEL'=>false,
					'THE_LEVEL'=>'2',
					'POSITION'=>'1',
					'LAST'=>false,
					'BRETHREN_COUNT'=>'3',
					'CURRENT'=>false,
					'CURRENT_ZONE'=>false,
						)
				));
		}
		foreach(placeholder_array(3) as $v)
		{
			$content->attach(do_lorem_template('MENU_BRANCH_select',array(
				'RANDOM'=>placeholder_random(),
				'CAPTION'=>lorem_word(),
				'IMG'=>'',
				'URL'=>placeholder_url(),
				'PAGE_LINK'=>placeholder_link(),
				'ACCESSKEY'=>'',
				'POPUP'=>true,
				'POPUP_WIDTH'=>'500',
				'POPUP_HEIGHT'=>'500',
				'NEW_WINDOW'=>false,
				'TOOLTIP'=>lorem_phrase(),
				'CHILDREN'=>$child,
				'DISPLAY'=>'block',
				'MENU'=>lorem_word_2(),
				'TOP_LEVEL'=>true,
				'THE_LEVEL'=>'0',
				'POSITION'=>'2',
				'LAST'=>false,
				'BRETHREN_COUNT'=>'3',
				'CURRENT'=>false,
				'CURRENT_ZONE'=>False,
					))
				);

				$content->attach(do_lorem_template('MENU_SPACER_select',array()));
		}
		$menu = do_lorem_template('MENU_select',array(
					'CONTENT'=>$content,
					'MENU'=>'test',
						));

		$menu->attach(do_lorem_template('MENU_STAFF_LINK',array('TYPE'=>'select','EDIT_URL'=>placeholder_url(),'NAME'=>lorem_phrase())));

		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_STORED_MENU',array(
					'CONTENT'=>$menu,
					'PARAM'=>lorem_phrase(),
					'TRAY_STATUS'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
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
	function tpl_preview__block_side_stored_menu__top()
	{
		$child = new ocp_tempcode();
		$content = new ocp_tempcode();
		foreach(placeholder_array() as $v)
		{
			$child->attach(do_lorem_template('MENU_BRANCH_top',array(
					'RANDOM'=>placeholder_random(),
					'CAPTION'=>lorem_word(),
					'IMG'=>'',
					'URL'=>placeholder_url(),
					'PAGE_LINK'=>placeholder_link(),
					'ACCESSKEY'=>'',
					'POPUP'=>false,
					'POPUP_WIDTH'=>'',
					'POPUP_HEIGHT'=>'',
					'NEW_WINDOW'=>false,
					'TOOLTIP'=>lorem_phrase(),
					'CHILDREN'=>'',
					'DISPLAY'=>'block',
					'MENU'=>lorem_word_2(),
					'TOP_LEVEL'=>false,
					'THE_LEVEL'=>'2',
					'POSITION'=>'1',
					'LAST'=>false,
					'BRETHREN_COUNT'=>'3',
					'CURRENT'=>false,
					'CURRENT_ZONE'=>false,
						)
				));
		}
		foreach(placeholder_array(3) as $k=>$v)
		{
			if($k == 1)
				$content->attach(do_lorem_template('MENU_SPACER_top',array()));
			else
				$content->attach(do_lorem_template('MENU_BRANCH_top',array(
					'RANDOM'=>placeholder_random(),
					'CAPTION'=>lorem_word(),
					'IMG'=>'',
					'URL'=>placeholder_url(),
					'PAGE_LINK'=>placeholder_link(),
					'ACCESSKEY'=>'',
					'POPUP'=>true,
					'POPUP_WIDTH'=>'500',
					'POPUP_HEIGHT'=>'500',
					'NEW_WINDOW'=>false,
					'TOOLTIP'=>lorem_phrase(),
					'CHILDREN'=>$child,
					'DISPLAY'=>'block',
					'MENU'=>lorem_word_2(),
					'TOP_LEVEL'=>true,
					'THE_LEVEL'=>'0',
					'POSITION'=>'2',
					'LAST'=>false,
					'BRETHREN_COUNT'=>'3',
					'CURRENT'=>false,
					'CURRENT_ZONE'=>False,
						))
					);
		}
		$menu = do_lorem_template('MENU_top',array(
					'CONTENT'=>$content,
					'MENU'=>'test',
						));

		$menu->attach(do_lorem_template('MENU_STAFF_LINK',array('TYPE'=>'top','EDIT_URL'=>placeholder_url(),'NAME'=>lorem_phrase())));

		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_STORED_MENU',array(
					'CONTENT'=>$menu,
					'PARAM'=>lorem_phrase(),
					'TRAY_STATUS'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
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
	function tpl_preview__block_side_stored_menu__zone()
	{
		$child = new ocp_tempcode();
		$content = new ocp_tempcode();
		foreach(placeholder_array(3) as $v)
		{
			$child->attach(do_lorem_template('MENU_BRANCH_zone',array(
					'RANDOM'=>placeholder_random(),
					'CAPTION'=>lorem_word(),
					'IMG'=>'',
					'URL'=>placeholder_url(),
					'PAGE_LINK'=>placeholder_link(),
					'ACCESSKEY'=>'',
					'POPUP'=>false,
					'POPUP_WIDTH'=>'',
					'POPUP_HEIGHT'=>'',
					'NEW_WINDOW'=>false,
					'TOOLTIP'=>lorem_phrase(),
					'CHILDREN'=>'',
					'DISPLAY'=>'block',
					'MENU'=>lorem_word_2(),
					'TOP_LEVEL'=>false,
					'THE_LEVEL'=>'2',
					'POSITION'=>'1',
					'LAST'=>false,
					'BRETHREN_COUNT'=>'3',
					'CURRENT'=>false,
					'CURRENT_ZONE'=>false,
						)
				));
		}
		foreach(placeholder_array(3) as $v)
		{
			$content->attach(do_lorem_template('MENU_BRANCH_zone',array(
				'RANDOM'=>placeholder_random(),
				'CAPTION'=>lorem_word(),
				'IMG'=>'',
				'URL'=>placeholder_url(),
				'PAGE_LINK'=>placeholder_link(),
				'ACCESSKEY'=>'',
				'POPUP'=>true,
				'POPUP_WIDTH'=>'500',
				'POPUP_HEIGHT'=>'500',
				'NEW_WINDOW'=>false,
				'TOOLTIP'=>lorem_phrase(),
				'CHILDREN'=>$child,
				'DISPLAY'=>'block',
				'MENU'=>lorem_word_2(),
				'TOP_LEVEL'=>true,
				'THE_LEVEL'=>'0',
				'POSITION'=>'2',
				'LAST'=>false,
				'BRETHREN_COUNT'=>'3',
				'CURRENT'=>false,
				'CURRENT_ZONE'=>False,
					))
				);

				$content->attach(do_lorem_template('MENU_SPACER_zone',array()));
		}
		$menu = do_lorem_template('MENU_zone',array(
					'CONTENT'=>$content,
					'MENU'=>'test',
						));

		$menu->attach(do_lorem_template('MENU_STAFF_LINK',array('TYPE'=>'zone','EDIT_URL'=>placeholder_url(),'NAME'=>lorem_phrase())));

		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_STORED_MENU',array(
					'CONTENT'=>$menu,
					'PARAM'=>lorem_phrase(),
					'TRAY_STATUS'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
				)
			),NULL,'',true),
		);
	}
}