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
 * @package		core
 */

/**
 * Standard code module initialisation function.
 */
function init__zones2()
{
	global $CLASS_CACHE;
	$CLASS_CACHE=array();
}

/**
 * Add a zone.
 *
 * @param  ID_TEXT		Name of the zone
 * @param  SHORT_TEXT	The zone title
 * @param  ID_TEXT		The zones default page
 * @param  SHORT_TEXT	The header text
 * @param  ID_TEXT		The theme
 * @param  BINARY			Whether the zone is wide
 * @param  BINARY			Whether the zone requires a session for pages to be used
 * @param  BINARY			Whether the zone in displayed in the menu coded into some themes
 */
function actual_add_zone($zone,$title,$default_page='start',$header_text='',$theme='default',$wide=0,$require_session=0,$displayed_in_menu=1)
{
	require_code('type_validation');
	if (!is_alphanumeric($zone)) warn_exit(do_lang_tempcode('BAD_CODENAME'));

	if (get_file_base()!=get_custom_file_base()) warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));

	// Check doesn't already exist
	$test=$GLOBALS['SITE_DB']->query_value_null_ok('zones','zone_header_text',array('zone_name'=>$zone));
	if (!is_null($test))
	{
		if (file_exists(get_file_base().'/'.$zone)) // Ok it's here completely, so we can't create
		{
			warn_exit(do_lang_tempcode('ALREADY_EXISTS',escape_html($zone)));
		} else // In DB, not on disk, so we'll just delete DB record
		{
			persistant_cache_delete(array('ZONE',$zone));
			$GLOBALS['SITE_DB']->query_delete('zones',array('zone_name'=>$zone),'',1);
		}
	}

	if (!file_exists(get_file_base().'/'.$zone))
	{
		// Create structure
		afm_make_directory($zone.'/pages/minimodules_custom',true,true);
		afm_make_directory($zone.'/pages/minimodules',false,true);
		afm_make_directory($zone.'/pages/modules_custom',true,true);
		afm_make_directory($zone.'/pages/modules',false,true);
		$langs=array_keys(find_all_langs(true));
		foreach ($langs as $lang)
		{
			afm_make_directory($zone.'/pages/comcode_custom/'.$lang,true,true);
			afm_make_directory($zone.'/pages/comcode/'.$lang,false,true);
			afm_make_directory($zone.'/pages/html_custom/'.$lang,true,true);
			afm_make_directory($zone.'/pages/html/'.$lang,false,true);
		}
		afm_make_file($zone.'/index.php',file_get_contents(get_file_base().'/index.php'),false);
		if (file_exists(get_file_base().'/pages/.htaccess'))
			afm_make_file($zone.'/pages/.htaccess',file_get_contents(get_file_base().'/pages/.htaccess'),false);
		$index_php=array('pages/comcode','pages/comcode/EN','pages/comcode_custom','pages/comcode_custom/EN',
								'pages/html','pages/html/EN','pages/html_custom','pages/html_custom/EN',
								'pages/modules','pages/modules_custom','pages');
		foreach ($index_php as $i)
		{
			afm_make_file($zone.'/'.$i.'/index.html','',false);
		}
		$default_menu=<<<END
[block="zone_{$zone}_menu" type="tree" caption="Menu"]side_stored_menu[/block]
[block failsafe="1"]side_users_online[/block]
[block failsafe="1"]side_stats[/block]
[block]side_personal_stats[/block]
END;
		afm_make_file($zone.'/pages/comcode_custom/EN/panel_left.txt',$default_menu,true);
	}
	afm_make_file($zone.'/pages/comcode_custom/EN/start.txt','[title]'.do_lang('YOUR_NEW_ZONE').'[/title]'.chr(10).chr(10).do_lang('YOUR_NEW_ZONE_PAGE',$zone.':'.$default_page).chr(10).chr(10).'[block]main_comcode_page_children[/block]',true);

	$GLOBALS['SITE_DB']->query_insert('zones',array('zone_name'=>$zone,'zone_title'=>insert_lang($title,1),'zone_default_page'=>$default_page,'zone_header_text'=>insert_lang($header_text,1),'zone_theme'=>$theme,'zone_wide'=>$wide,'zone_require_session'=>$require_session,'zone_displayed_in_menu'=>$displayed_in_menu));

	require_code('menus2');
	$menu_item_count=$GLOBALS['SITE_DB']->query_value('menu_items','COUNT(*)',array('i_menu'=>'zone_menu'));
	if ($menu_item_count<40)
		add_menu_item_simple('zone_menu',NULL,$title,$zone.':',0,1);

	log_it('ADD_ZONE',$zone);
	persistant_cache_delete('ALL_ZONES');

	decache('main_sitemap');
	decache('side_stored_menu');
	decache('side_zone_jump');
}

/**
 * Get a list of overridable SP's for a module.
 *
 * @param  ID_TEXT		The zone it is in
 * @param  ID_TEXT		The page name
 * @return array			A pair: List of overridable SP's, SP-page
 */
function get_module_overridables($zone,$page)
{
	$overridables=array();
	$sp_page=$page;

	$_pagelinks=extract_module_functions_page($zone,$page,array('get_page_links'),array(NULL,false,NULL,true));
	if (!is_null($_pagelinks[0])) // If it's a CMS-supporting module (e.g. downloads)
	{
		$pagelinks=is_array($_pagelinks[0])?call_user_func_array($_pagelinks[0][0],$_pagelinks[0][1]):eval($_pagelinks[0]);
		if ((!is_null($pagelinks[0])) && (!is_null($pagelinks[1]))) // If it's not disabled
		{
			$_overridables=extract_module_functions_page(get_module_zone($pagelinks[1]),$pagelinks[1],array('get_sp_overrides'));
			if (!is_null($_overridables[0])) // If it's a CMS-supporting module with SP overrides
			{
				$overridables=is_array($_overridables[0])?call_user_func_array($_overridables[0][0],$_overridables[0][1]):eval($_overridables[0]);
			}
			$sp_page=$pagelinks[1];
		}
	} else
	{
		$_overridables=extract_module_functions_page($zone,$page,array('get_sp_overrides'));
		if (!is_null($_overridables[0])) // If it's a CMS-supporting module with SP overrides
		{
			$overridables=is_array($_overridables[0])?call_user_func_array($_overridables[0][0],$_overridables[0][1]):eval($_overridables[0]);
		}
	}

	return array($overridables,$sp_page);
}

/**
 * Upgrade the specified module.
 *
 * @param  ID_TEXT		The zone name
 * @param  ID_TEXT		The module name
 * @return integer		0=No upgrade. -2=Not installed, 1=Upgrade
 */
function upgrade_module($zone,$module)
{
	require_code('database_action');
	require_code('config2');
	require_code('menus2');

	$rows=$GLOBALS['SITE_DB']->query_select('modules',array('*'),array('module_the_name'=>$module),'',1);
	if (!array_key_exists(0,$rows)) return (-2); // Not installed, so can't upgrade

	$upgrade_from=$rows[0]['module_version'];
	$upgrade_from_hack=$rows[0]['module_hack_version'];

	$module_path=get_file_base().'/'._get_module_path($zone,$module);

	$functions=extract_module_functions($module_path,array('info','install'),array($upgrade_from,$upgrade_from_hack));
	if ((is_null($functions[1])) && (strpos($module_path,'/modules_custom/')!==false))
	{
		if ((strpos($module_path,'/modules_custom/')!==false) && (file_exists(str_replace('/modules_custom/','/modules/',$module_path))) && ((strpos(file_get_contents($module_path),'function install(')===false) || (strpos(file_get_contents($module_path),'function info(')===false)))
		{
			$module_path=str_replace('/modules_custom/','/modules/',$module_path);
		}
		$functions=extract_module_functions($module_path,array('info','install'),array($upgrade_from,$upgrade_from_hack));
	}
	if (is_null($functions[0]))
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=true;
	} else
	{
		$info=is_array($functions[0])?call_user_func_array($functions[0][0],$functions[0][1]):eval($functions[0]);
	}

	$ret=0;
	if ((!is_null($functions[1])) && (array_key_exists('update_require_upgrade',$info)))
	{
		if ((($upgrade_from<$info['version']) && (array_key_exists('update_require_upgrade',$info)))
			|| (($upgrade_from_hack<$info['hack_version']) && (array_key_exists('hack_require_upgrade',$info))))
		{
			if (is_array($functions[1]))
			{
				call_user_func_array($functions[1][0],$functions[1][1]);
			} else
			{
				eval($functions[1]);
			}
			$ret=1;
		}
	}
	if (is_null($info['hacked_by'])) $info['installed_hacked_by']='';
	$GLOBALS['SITE_DB']->query_update('modules',array('module_version'=>$info['version'],'module_hack_version'=>$info['hack_version'],'module_hacked_by'=>is_null($info['hacked_by'])?'':$info['hacked_by']),array('module_the_name'=>$module),'',1);

	return $ret;
}

/**
 * Reinstall the specified module.
 *
 * @param  ID_TEXT		The zone name
 * @param  ID_TEXT		The module name
 * @return boolean		Whether a module installer had to be run
 */
function reinstall_module($zone,$module)
{
	$GLOBALS['NO_QUERY_LIMIT']=true;
	
	$module_path=get_file_base().'/'._get_module_path($zone,$module);
	require_code('database_action');
	require_code('config2');
	require_code('menus2');
	require_code('files2');

	$GLOBALS['SITE_DB']->query_delete('modules',array('module_the_name'=>$module),'',1);

	$functions=extract_module_functions($module_path,array('info','install','uninstall'));
	if ((is_null($functions[1])) && (strpos($module_path,'/modules_custom/')!==false))
	{
		if ((strpos($module_path,'/modules_custom/')!==false) && (file_exists(str_replace('/modules_custom/','/modules/',$module_path))) && ((strpos(file_get_contents($module_path),'function install(')===false) || (strpos(file_get_contents($module_path),'function info(')===false)))
		{
			$module_path=str_replace('/modules_custom/','/modules/',$module_path);
		}
		$functions=extract_module_functions($module_path,array('info','install','uninstall'));
	}
	if (is_null($functions[0]))
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=true;
	} else
	{
		$info=is_array($functions[0])?call_user_func_array($functions[0][0],$functions[0][1]):eval($functions[0]);
	}

	if (!is_null($functions[2]))
	{
		if (is_array($functions[2]))
		{
			call_user_func_array($functions[2][0],$functions[2][1]);
		} else
		{
			eval($functions[2]);
		}
	}
	if (is_null($info)) return false;
	if (is_null($info['hacked_by'])) $info['hacked_by']='';
	if (!is_null($functions[1]))
	{
		if (is_array($functions[1]))
		{
			call_user_func_array($functions[1][0],$functions[1][1]);
		} else
		{
			eval($functions[1]);
		}
	}
	$GLOBALS['SITE_DB']->query_insert('modules',array('module_the_name'=>$module,'module_author'=>$info['author'],'module_organisation'=>$info['organisation'],'module_hacked_by'=>is_null($info['hacked_by'])?'':$info['hacked_by'],'module_hack_version'=>$info['hack_version'],'module_version'=>$info['version']));
	return (!is_null($functions[1]));
}

/**
 * Completely uninstall the specified module from the system.
 *
 * @param  ID_TEXT		The zone name
 * @param  ID_TEXT		The module name
 */
function uninstall_module($zone,$module)
{
	$module_path=get_file_base().'/'._get_module_path($zone,$module);

	require_code('database_action');
	require_code('config2');
	require_code('files2');
	$GLOBALS['SITE_DB']->query_delete('modules',array('module_the_name'=>$module),'',1);
	$GLOBALS['SITE_DB']->query_delete('group_page_access',array('page_name'=>$module)); // As some modules will try and install this themselves. Entry point permissions they won't.
	$GLOBALS['SITE_DB']->query_delete('gsp',array('the_page'=>$module)); // Ditto

	if (file_exists($module_path))
	{
		$functions=extract_module_functions($module_path,array('uninstall'));
		if ((is_null($functions[0])) && (strpos($module_path,'/modules_custom/')!==false))
		{
			if ((strpos($module_path,'/modules_custom/')!==false) && (file_exists(str_replace('/modules_custom/','/modules/',$module_path))) && ((strpos(file_get_contents($module_path),'function install(')===false) || (strpos(file_get_contents($module_path),'function info(')===false)))
			{
				$module_path=str_replace('/modules_custom/','/modules/',$module_path);
			}
			$functions=extract_module_functions($module_path,array('uninstall'));
		}
		if (is_null($functions[0])) return;

		if (is_array($functions[0]))
		{
			call_user_func_array($functions[0][0],$functions[0][1]);
		} else
		{
			eval($functions[0]);
		}
	}
}

/**
 * Get an array of all the blocks that are currently installed (miniblocks not included).
 *
 * @return array			Map of all blocks (name->[sources/sources_custom])
 */
function find_all_blocks()
{
	$out=array();

	$dh=opendir(get_file_base().'/sources/blocks');
	while (($file=readdir($dh))!==false)
	{
		if ((substr($file,-4)=='.php') && (preg_match('#^[\w\-]*$#',substr($file,0,strlen($file)-4))!=0))
		{
			$out[substr($file,0,strlen($file)-4)]='sources';
		}
	}
	closedir($dh);
	if (!in_safe_mode())
	{
		$dh=@opendir(get_file_base().'/sources_custom/blocks');
		if ($dh!==false)
		{
			while (($file=readdir($dh))!==false)
			{
				if ((substr($file,-4)=='.php') && (preg_match('#^[\w\-]*$#',substr($file,0,strlen($file)-4))!=0))
				{
					$out[substr($file,0,strlen($file)-4)]='sources_custom';
				}
			}
			closedir($dh);
		}
	}	

	return $out;
}

/**
 * Make a block codename look nice
 *
 * @param  ID_TEXT		The raw block codename
 * @return string			A nice human readable version of the name
 */
function cleanup_block_name($block)
{
	$title=do_lang('BLOCK_TRANS_NAME_'.$block,NULL,NULL,NULL,NULL,false);
	if (!is_null($title)) return $title;

	$block=str_replace('_ocf_','_',$block);
	return ucwords(str_replace('_',' ',str_replace('block_bottom_','Bottom: ',str_replace('block_side_','Side: ',str_replace('block_main_','Main: ',$block)))));
}

/**
 * Gets parameters for a block
 *
 * @param  ID_TEXT		The name of the block to get parameters for
 * @return array			A list of parameters the block takes
 */
function get_block_parameters($block)
{
	$block_path=_get_block_path($block);
	$info=extract_module_info($block_path);
	if (is_null($info)) return array();

	$ret=array_key_exists('parameters',$info)?$info['parameters']:array();
	if (is_null($ret)) return array();
	return $ret;
}

/**
 * Upgrades a block to the latest version available on your ocPortal installation. [b]This function can only upgrade to the latest version put into the block directory.[/b] You should not need to use this function.
 *
 * @param  ID_TEXT		The name of the block to upgrade
 * @return integer		0=No upgrade. -2=Not installed, 1=Upgrade
 */
function upgrade_block($block)
{
	require_code('database_action');
	$rows=$GLOBALS['SITE_DB']->query_select('blocks',array('*'),array('block_name'=>$block),'',1);
	if (!array_key_exists(0,$rows)) return (-2); // Not installed, so can't upgrade

	$upgrade_from=$rows[0]['block_version'];
	$upgrade_from_hack=$rows[0]['block_hack_version'];

	$block_path=_get_block_path($block);

	$functions=extract_module_functions($block_path,array('info','install'),array($upgrade_from,$upgrade_from_hack));
	if (is_null($functions[0])) return 0;

	$info=is_array($functions[0])?call_user_func_array($functions[0][0],$functions[0][1]):eval($functions[0]);
	if ((!is_null($functions[1])) && (array_key_exists('update_require_upgrade',$info)))
	{
		if ((($upgrade_from<$info['version']) && (array_key_exists('update_require_upgrade',$info)))
			|| (($upgrade_from_hack<$info['hack_version']) && (array_key_exists('hack_require_upgrade',$info))))
		{
			if (is_array($functions[1]))
			{
				call_user_func_array($functions[1][0],$functions[1][1]);
			} else
			{
				eval($functions[1]);
			}
			if (is_null($info['hacked_by'])) $info['installed_hacked_by']='';
			$GLOBALS['SITE_DB']->query_update('blocks',array('block_version'=>$info['version'],'block_hack_version'=>$info['hack_version'],'block_hacked_by'=>is_null($info['hacked_by'])?'':$info['hacked_by']),array('block_name'=>$block),'',1);
			return 1;
		}
	}
	return 0;
}

/**
 * Reinstall a block if it has become corrupted for any reason.
 * Again, you should not need to use this function.
 *
 * @param  ID_TEXT		The name of the block to reinstall
 * @return boolean		Whether installation was required
 */
function reinstall_block($block)
{
	//echo $block.'<br />';
	$block_path=_get_block_path($block);

	$GLOBALS['SITE_DB']->query_delete('blocks',array('block_name'=>$block),'',1);

	require_code('database_action');
	require_code('menus2');
	require_code('config2');
	require_code('files2');

	$functions=extract_module_functions($block_path,array('info','install','uninstall'));
	if (is_null($functions[0])) return false;

	if (!is_null($functions[2]))
	{
		if (is_array($functions[2]))
		{
			call_user_func_array($functions[2][0],$functions[2][1]);
		} else
		{
			eval($functions[2]);
		}
	}
	$info=is_array($functions[0])?call_user_func_array($functions[0][0],$functions[0][1]):eval($functions[0]);
	if (is_null($info)) return false;
	if (is_null($info['hacked_by'])) $info['hacked_by']='';

	$GLOBALS['SITE_DB']->query_insert('blocks',array('block_name'=>$block,'block_author'=>$info['author'],'block_organisation'=>$info['organisation'],'block_hacked_by'=>is_null($info['hacked_by'])?'':$info['hacked_by'],'block_hack_version'=>$info['hack_version'],'block_version'=>$info['version']));
	if (!is_null($functions[1]))
	{
		if (is_array($functions[1]))
		{
			call_user_func_array($functions[1][0],$functions[1][1]);
		} else
		{
			eval($functions[1]);
		}
		return true;
	}
	return false;
}

/**
 * This function totally uninstalls a block from the system. Yet again, you should not need to use this function.
 *
 * @param  ID_TEXT		The name of the block to uninstall
 */
function uninstall_block($block)
{
	$block_path=_get_block_path($block);

	require_code('database_action');
	require_code('menus2');
	require_code('files2');
	$GLOBALS['SITE_DB']->query_delete('blocks',array('block_name'=>$block),'',1);
	$GLOBALS['SITE_DB']->query_delete('cache_on',array('cached_for'=>$block),'',1);
	$GLOBALS['SITE_DB']->query_delete('cache',array('cached_for'=>$block));

	if (file_exists($block_path))
	{
		$functions=extract_module_functions($block_path,array('uninstall'));
		if (is_null($functions[0])) return;

		if (is_array($functions[0]))
		{
			call_user_func_array($functions[0][0],$functions[0][1]);
		} else
		{
			eval($functions[0]);
		}
	}
}

/**
 * Extract code to execute the requested functions with the requested parameters from the module requested.
 *
 * @param  ID_TEXT		The zone it is in
 * @param  ID_TEXT		The page name
 * @param  array			Array of functions to be executing
 * @param  ?array			A list of parameters to pass to our functions (NULL: none)
 * @return array			A list of pieces of code to do the equivalent of executing the requested functions with the requested parameters
 */
function extract_module_functions_page($zone,$page,$functions,$params=NULL)
{
	$path=zone_black_magic_filterer(get_file_base().'/'.filter_naughty_harsh($zone).(($zone=='')?'':'/').'pages/modules_custom/'.filter_naughty_harsh($page).'.php');
	if (file_exists($path))
	{
		$ret=extract_module_functions($path,$functions,$params);
		if (array_unique(array_values($ret))!=array(NULL)) return $ret;
	}

	$path=zone_black_magic_filterer(get_file_base().'/'.filter_naughty_harsh($zone).(($zone=='')?'':'/').'pages/modules/'.filter_naughty_harsh($page).'.php');
	if (!file_exists($path))
	{
		$ret=array();
		for ($i=0;$i<count($functions);$i++)
			array_push($ret,NULL);
		return $ret;
	}
	return extract_module_functions($path,$functions,$params);
}

/**
 * Extract the info function from a module at a given path.
 *
 * @param  PATH			The path to the module
 * @return ?array			A module information map (NULL: module contains no info method)
 */
function extract_module_info($path)
{
	$functions=extract_module_functions($path,array('info'));
	if (is_null($functions[0])) return NULL;
	return is_array($functions[0])?call_user_func_array($functions[0][0],$functions[0][1]):eval($functions[0]);
}

/**
 * Get an array of all the pages everywhere in the zone (for small sites everything will be returned, for larger ones it depends on the show method).
 *
 * @param  ID_TEXT		The zone name
 * @param  boolean		Whether to leave file extensions on the page name
 * @param  boolean		Whether to take redirects into account
 * @param  integer		Selection algorithm constant
 * @set 0 1 2
 * @param  ?ID_TEXT		Page type to show (NULL: all)
 * @return array			A map of page name to type (modules_custom, etc)
 */
function _find_all_pages_wrap($zone,$keep_ext_on=false,$consider_redirects=false,$show_method=0,$page_type=NULL)
{
	$pages=array();
	if ((is_null($page_type)) || ($page_type=='modules'))
	{
		if (!in_safe_mode())
		{
			$pages+=find_all_pages($zone,'modules_custom','php',$keep_ext_on,NULL,$show_method);
		}
		$pages+=find_all_pages($zone,'modules','php',$keep_ext_on,NULL,$show_method);
	}
	$langs=multi_lang()?array_keys(find_all_langs()):array(get_site_default_lang());
	foreach ($langs as $lang)
	{
		if ((is_null($page_type)) || ($page_type=='comcode'))
		{
			if (!in_safe_mode())
			{
				$pages+=find_all_pages($zone,'comcode_custom/'.$lang,'txt',$keep_ext_on,NULL,$show_method);
			}
			$pages+=find_all_pages($zone,'comcode/'.$lang,'txt',$keep_ext_on,NULL,$show_method);
		}
		if ((is_null($page_type)) || ($page_type=='html'))
		{
			if (!in_safe_mode())
			{
				$pages+=find_all_pages($zone,'html_custom/'.$lang,'htm',$keep_ext_on,NULL,$show_method);
			}
			$pages+=find_all_pages($zone,'html/'.$lang,'htm',$keep_ext_on,NULL,$show_method);
		}
	}
	if ((is_null($page_type)) || ($page_type=='minimodules'))
	{
		if (!in_safe_mode())
		{
			$pages+=find_all_pages($zone,'minimodules_custom','php',$keep_ext_on,NULL,$show_method);
		}
		$pages+=find_all_pages($zone,'minimodules','php',$keep_ext_on,NULL,$show_method);
	}

	if (addon_installed('redirects_editor'))
	{
		if ($consider_redirects)
		{
			$redirects=$GLOBALS['SITE_DB']->query_select('redirects',array('*'),array('r_from_zone'=>$zone));
			foreach ($redirects as $r)
			{
				if ($r['r_is_transparent']==0)
				{
					//unset($pages[$r['r_from_page']]); // We don't want to link to anything that is a full redirect		-	Actually, we don't want to hide things too much, could be confusing
				} else
				{
					$pages[$r['r_from_page']]='redirect:'.$r['r_to_zone'].':'.$r['r_to_page'];
				}
			}
		}
	}

	return $pages;
}

/**
 * Get an array of all the pages of the specified type (module, etc) and extension (for small sites everything will be returned, for larger ones it depends on the show method).
 *
 * @param  ID_TEXT		The zone name
 * @param  ID_TEXT		The type (including language, if appropriate)
 * @set    modules modules_custom comcode/EN comcode_custom/EN html/EN html_custom/EN
 * @param  string			The file extension to limit us to (without a dot)
 * @param  boolean		Whether to leave file extensions on the page name
 * @param  ?TIME			Only show pages newer than (NULL: no restriction)
 * @param  integer		Selection algorithm constant
 * @set 0 1 2
 * @param  ?boolean		Whether to search under the custom-file-base (NULL: auto-decide)
 * @return array			A map of page name to type (modules_custom, etc)
 */
function _find_all_pages($zone,$type,$ext='php',$keep_ext_on=false,$cutoff_time=NULL,$show_method=0,$custom=NULL)
{
	$out=array();

	$module_path=($zone=='')?('pages/'.filter_naughty($type)):(filter_naughty($zone).'/pages/'.filter_naughty($type));

	if (is_null($custom))
	{
		$custom=((strpos($type,'comcode_custom')!==false) || (strpos($type,'html_custom')!==false));
		if (($custom) && (get_custom_file_base()!=get_file_base())) $out=_find_all_pages($zone,$type,$ext,false,NULL,$show_method,false);
	}
	$stub=$custom?get_custom_file_base():get_file_base();
	$dh=@opendir($stub.'/'.$module_path);
	if ($dh!==false)
	{
		while (($file=readdir($dh))!==false)
		{
			if ((substr($file,-4)=='.'.$ext) && (file_exists($stub.'/'.$module_path.'/'.$file)) && (preg_match('#^[\w\-]*$#',substr($file,0,strlen($file)-4))!=0))
			{
				if (!is_null($cutoff_time))
					if (filectime($stub.'/'.$module_path.'/'.$file)<$cutoff_time) continue;

				if ($ext=='txt')
				{
					switch ($show_method)
					{
						case FIND_ALL_PAGES__NEWEST: // Only gets newest if it's a large site
							if (count($out)>300)
							{
								$out=array();
								$records=$GLOBALS['SITE_DB']->query_select('comcode_pages',array('the_page'),array('the_zone'=>$zone),'ORDER BY p_add_date DESC',300);
								foreach ($records as $record)
								{
									$file=$record['the_page'].'.txt';
								
									if (!file_exists($stub.'/'.$module_path.'/'.$file)) continue;
								
									if (!is_null($cutoff_time))
										if (filectime($stub.'/'.$module_path.'/'.$file)<$cutoff_time) continue;

									$out[$keep_ext_on?$file:substr($file,0,strlen($file)-4)]=$type;
								}
							} else break;
							//break; Actually, no, let it roll on to the next one to get key files too

						case FIND_ALL_PAGES__PERFORMANT: // Default, chooses selection carefully based on site size
							if (($show_method==FIND_ALL_PAGES__NEWEST) || (count($out)>300))
							{
								if ($show_method!=FIND_ALL_PAGES__NEWEST) $out=array();
								$records=$GLOBALS['SITE_DB']->query('SELECT the_page FROM '.get_table_prefix().'comcode_pages WHERE '.db_string_equal_to('the_zone',$zone).' AND ('.db_string_equal_to('the_page',get_zone_default_page($zone)).' OR the_page LIKE \''.db_encode_like('panel\_%').'\') ORDER BY p_add_date DESC');
								foreach ($records as $record)
								{
									$file=$record['the_page'].'.txt';

									if (!file_exists($stub.'/'.$module_path.'/'.$file)) continue;

									if (!is_null($cutoff_time))
										if (filectime($stub.'/'.$module_path.'/'.$file)<$cutoff_time) continue;

									$out[$keep_ext_on?$file:substr($file,0,strlen($file)-4)]=$type;
								}
								break 2;
							}
							break;

						case FIND_ALL_PAGES__ALL: // Nothing special
							break;
					}
				}

				$out[$keep_ext_on?$file:substr($file,0,strlen($file)-4)]=$type;
			}
		}
		closedir($dh);
	}

	if (($zone=='') && (get_option('collapse_user_zones',true)==='1'))
		$out+=_find_all_pages('site',$type,$ext,$keep_ext_on);

	ksort($out);
	return $out;
}

/**
 * Get an array of all the modules.
 *
 * @param  ID_TEXT		The zone name
 * @return array			A map of page name to type (modules_custom, etc)
 */
function _find_all_modules($zone)
{
	if (in_safe_mode())
	{
		return find_all_pages($zone,'modules');
	}
	return find_all_pages($zone,'modules')+find_all_pages($zone,'modules_custom');
}


