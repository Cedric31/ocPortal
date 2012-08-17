<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: shell_exec*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_addon_management
 */

/**
 * Find updated addons via checking the ocPortal.com web service.
 *
 * @return array		List of addons updated
 */
function find_updated_addons()
{
	$addons=find_installed_addons(true);
	$url='http://ocportal.com/uploads/website_specific/ocportal.com/scripts/addon_manifest.php?version='.urlencode(float_to_raw_string(ocp_version_number()));
	foreach (array_keys($addons) as $i=>$addon)
	{
		$url.='&addon_'.strval($i).'='.urlencode($addon);
	}

	require_code('files');
	$addon_data=http_download_file($url);
	if ($addon_data=='') warn_exit(do_lang('INTERNAL_ERROR'));

	$available_addons=find_available_addons();

	$updated_addons=array();
	foreach (unserialize($addon_data) as $i=>$addon)
	{
		$found=false;

		foreach ($available_addons as $available_addon)
		{
			if ($available_addon['name']==$addon[3])
			{
				$found=true;
				if ((!is_null($addon[0])) && ($available_addon['mtime']<$addon[0])) // If known to server, and updated
				{
					$updated_addons[$addon[3]]=array($addon[1]); // Is known to server though
				}
			}
		}
		if (!$found) // Don't have our original .tar, so lets say we need to reinstall
		{
			$mtime=find_addon_effective_mtime($addon[3]);
			if ((!is_null($addon[0])) && (!is_null($mtime)) && ($mtime<$addon[0])) // If server has it and is newer
				$updated_addons[$addon[3]]=array($addon[1]);
		}
	}
	return $updated_addons;
}

/**
 * Change an ocProducts new style addon file list to have real paths.
 *
 * @param  array		Shorthand list
 * @return array		Real list
 */
function make_global_file_list($list)
{
	foreach ($list as $i=>$file)
	{
		if ((strpos($file,'/')===false) && ((substr($file,-4)=='.tpl') || (substr($file,-4)=='.css')))
		{
			switch (substr($file,-4))
			{
				case '.tpl':
					$file='themes/default/templates/'.$file;
					break;

				case '.css':
					$file='themes/default/css/'.$file;
					break;
			}

			$list[$i]=$file;
		}
	}

	return $list;
}

/**
 * Find all the installed addons.
 *
 * @param  boolean	Whether to only return details on on-bundled addons
 * @return array		List of maps describing the available addons (simulating partial-extended versions of the traditional ocPortal-addon database row)
 */
function find_installed_addons($just_non_bundled=false)
{
	// Find installed addons- database registration method
	$_rows=$GLOBALS['SITE_DB']->query_select('addons',array('*'));
	$addons_installed=array();
	foreach ($_rows as $row)
	{
		$files_rows=array_unique(collapse_1d_complexity('filename',$GLOBALS['SITE_DB']->query_select('addons_files',array('filename'),array('addon_name'=>$row['addon_name']))));
		$row['addon_files']='';
		foreach ($files_rows as $file_row_name)
		{
			$row['addon_files'].=$file_row_name.chr(10);
		}
		$addons_installed[$row['addon_name']]=$row;
	}
	if ($just_non_bundled) return $addons_installed;

	// Find installed addons- file system method (for ocProducts addons). ocProducts addons don't need to be in the DB, although they will be if they are (re)installed after the original ocPortal installation finished.
	$hooks=find_all_hooks('systems','addon_registry');
	foreach (array_keys($hooks) as $hook)
	{
		if (substr($hook,0,4)!='core')
		{
			if (false) // Inefficient old way
			{
				require_code('hooks/systems/addon_registry/'.filter_naughty_harsh($hook));
				$hook_ob=object_factory('Hook_addon_registry_'.$hook,true);
				if (is_null($hook_ob)) continue;

				$description=$hook_ob->get_description();
				$file_list=$hook_ob->get_file_list();
				$version=$hook_ob->get_version();
			} else
			{
				$path=get_file_base().'/sources_custom/hooks/systems/addon_registry/'.filter_naughty_harsh($hook).'.php';
				if (!file_exists($path))
					$path=get_file_base().'/sources/hooks/systems/addon_registry/'.filter_naughty_harsh($hook).'.php';

				if (!file_exists($path)) continue; // Race condition?

				$_hook_bits=extract_module_functions($path,array('get_description','get_file_list','get_version'));
				if (is_null($_hook_bits[0]))
				{
					$description='';
				} else
				{
					$description=is_array($_hook_bits[0])?call_user_func_array($_hook_bits[0][0],$_hook_bits[0][1]):@eval($_hook_bits[0]);
				}
				if (is_null($_hook_bits[1]))
				{
					$file_list=array();
				} else
				{
					$file_list=is_array($_hook_bits[1])?call_user_func_array($_hook_bits[1][0],$_hook_bits[1][1]):@eval($_hook_bits[1]);
				}
				if (is_null($_hook_bits[2]))
				{
					$version='';
				} else
				{
					$version=is_array($_hook_bits[2])?call_user_func_array($_hook_bits[2][0],$_hook_bits[2][1]):@eval($_hook_bits[2]);
				}
			}

			$addons_installed[$hook]=array(
				'addon_name'=>$hook,
				'addon_author'=>'Core Team',
				'addon_organisation'=>'ocProducts',
				'addon_version'=>($version==ocp_version_number())?ocp_version_pretty():float_format($version,1),
				'addon_description'=>$description,
				'addon_install_time'=>filemtime(get_file_base().'/sources/hooks/systems/addon_registry/'.$hook.'.php'),
				'addon_files'=>implode(chr(10),make_global_file_list($file_list)),
			);
		}
	}

	return $addons_installed;
}

/**
 * Find effective modification date of an addon.
 *
 * @param  string		The name of the addon
 * @return ?TIME		Modification time (NULL: could not find any files)
 */
function find_addon_effective_mtime($addon_name)
{
	$files_rows=array_unique(collapse_1d_complexity('filename',$GLOBALS['SITE_DB']->query_select('addons_files',array('filename'),array('addon_name'=>$addon_name))));
	$mtime=mixed();
	foreach ($files_rows as $filename)
	{
		if (file_exists(get_file_base().'/'.$filename))
		{
			$_mtime=filemtime(get_file_base().'/'.$filename);
			$mtime=is_null($mtime)?$_mtime:max($mtime,$_mtime);
		}
	}
	return $mtime;
}

/**
 * Find all the available addons (addons in imports/addons that are not necessarily installed).
 *
 * @return array		List of maps describing the available addons
 */
function find_available_addons()
{
	$addons_available_for_installation=array();
	$files=array();

	// Find addons available for installation
	$dh=@opendir(get_custom_file_base().'/imports/addons/');
	if ($dh!==false)
	{
		while (($file=readdir($dh))!==false)
		{
			if (substr($file,-4)=='.tar')
			{
				$files[]=array($file,filemtime(get_custom_file_base().'/imports/addons/'.$file));
			}
		}
		closedir($dh);
	}

	global $M_SORT_KEY;
	$M_SORT_KEY='1';
	usort($files,'multi_sort');

	foreach ($files as $_file)
	{
		$file=$_file[0];

		$full=get_custom_file_base().'/imports/addons/'.$file;
		require_code('tar');
		$tar=tar_open($full,'rb');
		$info_file=tar_get_file($tar,'mod.inf',true);
		if (!is_null($info_file))
		{
			$info=better_parse_ini_file(NULL,$info_file['data']);
			tar_close($tar);

			$files_rows=tar_get_directory($tar);
			$info['files']='';
			$mtime=filemtime($full);
			foreach ($files_rows as $file_row)
			{
				$info['files'].=$file_row['path'].chr(10);
			}
			$info['mtime']=$mtime;

			$addons_available_for_installation[$file]=$info;
		}
	}

	return $addons_available_for_installation;
}

/**
 * Find addon dependencies.
 *
 * @param  string		The name of the addon
 * @return array		List of dependencies
 */
function find_addon_dependencies_on($name)
{
	// From DB
	$list_a=collapse_1d_complexity('addon_name',$GLOBALS['SITE_DB']->query_select('addons_dependencies',array('addon_name'),array('addon_name_dependant_upon'=>$name,'addon_name_incompatibility'=>0)));

	// From ocProducts addons
	$list_b=array();
	$hooks=find_all_hooks('systems','addon_registry');
	foreach (array_keys($hooks) as $hook)
	{
		$path=get_file_base().'/sources_custom/hooks/systems/addon_registry/'.filter_naughty_harsh($hook).'.php';
		if (!file_exists($path))
			$path=get_file_base().'/sources/hooks/systems/addon_registry/'.filter_naughty_harsh($hook).'.php';
		if (!file_exists($path)) continue; // May have been uninstalled, find_all_hooks could have stale caching

		$_hook_bits=extract_module_functions($path,array('get_dependencies'));
		if (is_null($_hook_bits[0]))
		{
			$dep=array();
		} else
		{
			$dep=is_array($_hook_bits[0])?call_user_func_array($_hook_bits[0][0],$_hook_bits[0][1]):@eval($_hook_bits[0]);
		}

		if (in_array($name,$dep['requires'])) $list_b[]=$hook;
	}

	return array_unique(array_merge($list_a,$list_b));
}

/**
 * Get info about an addon, simulating an extended version of the traditional ocPortal-addon database row.
 *
 * @param  string		The name of the addon
 * @return array		The map of details
 */
function read_addon_info($name)
{
	$addon_rows=$GLOBALS['SITE_DB']->query_select('addons',array('*'),array('addon_name'=>$name));
	if (array_key_exists(0,$addon_rows))
	{
		$addon_row=$addon_rows[0];
		$addon_row['addon_files']=array_unique(collapse_1d_complexity('filename',$GLOBALS['SITE_DB']->query_select('addons_files',array('filename'),array('addon_name'=>$name))));
		$addon_row['addon_dependencies']=collapse_1d_complexity('addon_name_dependant_upon',$GLOBALS['SITE_DB']->query_select('addons_dependencies',array('addon_name_dependant_upon'),array('addon_name'=>$name,'addon_name_incompatibility'=>0)));
		$addon_row['addon_incompatibilities']=collapse_1d_complexity('addon_name_dependant_upon',$GLOBALS['SITE_DB']->query_select('addons_dependencies',array('addon_name_dependant_upon'),array('addon_name'=>$name,'addon_name_incompatibility'=>1)));
		$addon_row['addon_dependencies_on_this']=find_addon_dependencies_on($name);
	} else
	{
		if (!file_exists(get_file_base().'/sources/hooks/systems/addon_registry/'.filter_naughty_harsh($name).'.php'))
		{
			warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		}

		$path=get_file_base().'/sources_custom/hooks/systems/addon_registry/'.filter_naughty_harsh($name).'.php';
		if (!file_exists($path))
			$path=get_file_base().'/sources/hooks/systems/addon_registry/'.filter_naughty_harsh($name).'.php';

		$_hook_bits=extract_module_functions($path,array('get_dependencies','get_version','get_description','get_file_list'));
		if (is_null($_hook_bits[0]))
		{
			$dep=array();
		} else
		{
			$dep=is_array($_hook_bits[0])?call_user_func_array($_hook_bits[0][0],$_hook_bits[0][1]):@eval($_hook_bits[0]);
		}
		$version=is_array($_hook_bits[1])?call_user_func_array($_hook_bits[1][0],$_hook_bits[1][1]):@eval($_hook_bits[1]);
		$description=is_array($_hook_bits[2])?call_user_func_array($_hook_bits[2][0],$_hook_bits[2][1]):@eval($_hook_bits[2]);
		if (is_null($_hook_bits[3]))
		{
			$file_list=array();
		} else
		{
			$file_list=is_array($_hook_bits[3])?call_user_func_array($_hook_bits[3][0],$_hook_bits[3][1]):@eval($_hook_bits[3]);
		}

		$addon_row=array(
			'addon_name'=>$name,
			'addon_author'=>'Core Team',
			'addon_organisation'=>'ocProducts',
			'addon_version'=>($version==ocp_version_number())?ocp_version_pretty():float_format($version,1),
			'addon_description'=>$description,
			'addon_install_time'=>filemtime(get_file_base().'/sources/hooks/systems/addon_registry/'.$name.'.php'),
			'addon_files'=>make_global_file_list($file_list),
			'addon_dependencies'=>$dep['requires'],
			'addon_dependencies_on_this'=>find_addon_dependencies_on($name),
			'addon_incompatibilities'=>$dep['conflicts_with'],
		);
	}

	return $addon_row;
}

/**
 * Create an addon to spec.
 *
 * @param  string			Filename to create in exports/addons directory (should end in .tar)
 * @param  array			List of files to include
 * @param  string			Addon name
 * @param  string			Addon incompatibilities (comma-separated)
 * @param  string			Addon dependencies (comma-separated)
 * @param  string			Addon author
 * @param  string			Addon organisation
 * @param  string			Addon version
 * @param  string			Addon description
 * @param  PATH			Directory to save to
 */
function create_addon($file,$files,$name,$incompatibilities,$dependencies,$author,$organisation,$version,$description,$dir='exports/addons')
{
	require_code('tar');

	$_full=get_custom_file_base().'/'.$dir.'/'.$file;
	$tar=tar_open($_full,'wb');

	$max_mtime=0;

	foreach ($files as $val)
	{
		if ($val=='mod.inf') continue;

		$full=get_file_base().'/'.filter_naughty($val);

		$themed_suffix=get_param('theme',$GLOBALS['FORUM_DRIVER']->get_theme()).'__';
		$themed_version=dirname($full).'/'.$themed_suffix.basename($full);

		if ((!file_exists($full)) && (!file_exists($themed_version)))
		{
			continue;
		}

		if ((get_param_integer('keep_theme_test',0)==1) && (file_exists($themed_version)))
		{
			$mode=fileperms($themed_version);
			$mtime=0;
			//if ((file_exists(get_file_base().'/.git')) && (function_exists('json_decode')) && (filemtime($themed_version)>60*60*24-31*4/*If newer than 4 months it is likely git has garbled the modification date during a checkout*/))
			//{
			//	$_themed_version=dirname($val).'/'.$themed_suffix.basename($val);
			//	require_code('files');
			//	$json_data=@json_decode(http_download_file('http://github.com/api/v2/json/commits/list/chrisgraham/ocPortal/master/'.$_themed_version));
			//	if (isset($json_data->commits[0]->committed_date)) $mtime=strtotime($json_data->commits[0]->committed_date);
			//}
			if ($mtime==0) $mtime=filemtime($themed_version);
			if ($mtime>$max_mtime) $max_mtime=$mtime;
			tar_add_file($tar,$val,$themed_version,$mode,$mtime,true);
		} else
		{
			$mode=fileperms($full);
			$mtime=0;
			//if ((file_exists(get_file_base().'/.git')) && (function_exists('json_decode')) && (filemtime($full)>60*60*24-31*4/*If newer than 4 months it is likely git has garbled the modification date during a checkout*/))
			//{
			//	require_code('files');
			//	$json_data=@json_decode(http_download_file('http://github.com/api/v2/json/commits/list/chrisgraham/ocPortal/master/'.$val));
			//	if (isset($json_data->commits[0]->committed_date)) $mtime=strtotime($json_data->commits[0]->committed_date);
			//}
			if ($mtime==0) $mtime=filemtime($full);
			if ($mtime>$max_mtime) $max_mtime=$mtime;
			tar_add_file($tar,$val,$full,$mode,$mtime,true);

			$full=get_file_base().'/'.filter_naughty($val).'.editfrom';
			if (file_exists($full))
			{
				$mode=fileperms($full);
				$mtime=filemtime($full);
				tar_add_file($tar,$val.'.editfrom',$full,$mode,$mtime,true);
			}
		}

		// If it's a theme, make a mod.php for the theme to restore images_custom mappings
		if ((substr($val,0,7)=='themes/') && (substr($val,-10)=='/theme.ini'))
		{
			$theme=substr($val,7,strpos($val,'/theme.ini')-7);

			$images=$GLOBALS['SITE_DB']->query_select('theme_images',array('*'),array('theme'=>$theme));
			$data='<'.'?php'."\n";
			foreach ($images as $image)
			{
				$data.='$GLOBALS[\'SITE_DB\']->query_insert(\'theme_images\',array(\'id\'=>\''.db_escape_string($image['id']).'\',\'theme\'=>\''.db_escape_string($image['theme']).'\',\'path\'=>\''.db_escape_string($image['path']).'\',\'lang\'=>\''.db_escape_string($image['lang']).'\'),false,true);'."\n";
			}
			$data.="?".">\n";
			tar_add_file($tar,'mod.php',$data,0444,time());
		}
	}

	// Our special file
	$name=str_replace('"','\'',$name);
	$author=str_replace('"','\'',$author);
	$organisation=str_replace('"','\'',$organisation);
	$version=str_replace('"','\'',$version);
	$incompatibilities=str_replace('"','\'',$incompatibilities);
	$dependencies=str_replace('"','\'',$dependencies);
	$description=str_replace(chr(13),'',str_replace(chr(10),'\n',str_replace('"','\'',$description)));
	$mod_inf="name=".$name."
author=".$author."
organisation=".$organisation."
version=".$version."
incompatibilities=".$incompatibilities."
dependencies=".$dependencies."
description=".$description."
";
	tar_add_file($tar,'mod.inf',$mod_inf,0644,time());

	tar_close($tar);

	@touch($_full,$max_mtime);

	fix_permissions($_full);
	sync_file($_full);
}

/**
 * Uninstall an addon.
 *
 * @param  string			Name of the addon
 * @param  ?array			The files to install (NULL: all)
 */
function install_addon($file,$files=NULL)
{
	$full=get_custom_file_base().'/imports/addons/'.$file;

	require_code('zones2');
	require_code('zones3');

	require_code('tar');
	$tar=tar_open($full,'rb');
	$info_file=tar_get_file($tar,'mod.inf');
	if (is_null($info_file)) warn_exit(do_lang_tempcode('NOT_ADDON'));
	$info=better_parse_ini_file(NULL,$info_file['data']);
	$directory=tar_get_directory($tar);
	tar_extract_to_folder($tar,'',true,$files,true);

	$addon=$info['name'];
	$author=$info['author'];
	$organisation=$info['organisation'];
	$version=$info['version'];
	if ($version=='(version-synched)') $version=float_to_raw_string(ocp_version_number());
	$dependencies=explode(',',array_key_exists('dependencies',$info)?$info['dependencies']:'');
	$incompatibilities=explode(',',array_key_exists('incompatibilities',$info)?$info['incompatibilities']:'');
	$description=$info['description'];

	$GLOBALS['SITE_DB']->query_delete('addons_files',array('addon_name'=>$addon));
	$GLOBALS['SITE_DB']->query_delete('addons_dependencies',array('addon_name'=>$addon));
	$GLOBALS['SITE_DB']->query_delete('addons',array('addon_name'=>$addon),'',1);

	$GLOBALS['SITE_DB']->query_delete('addons',array('addon_name'=>$addon),'',1);
	$GLOBALS['SITE_DB']->query_insert('addons',array(
		'addon_name'=>$addon,
		'addon_author'=>$author,
		'addon_organisation'=>$organisation,
		'addon_version'=>$version,
		'addon_description'=>$description,
		'addon_install_time'=>time()
	));

	foreach ($dependencies as $dependency)
	{
		$GLOBALS['SITE_DB']->query_insert('addons_dependencies',array(
			'addon_name'=>$addon,
			'addon_name_dependant_upon'=>trim($dependency),
			'addon_name_incompatibility'=>0
		));
	}
	foreach ($incompatibilities as $dependency)
	{
		$GLOBALS['SITE_DB']->query_insert('addons_dependencies',array(
			'addon_name'=>$addon,
			'addon_name_dependant_upon'=>trim($dependency),
			'addon_name_incompatibility'=>1
		));
	}

	foreach ($directory as $dir)
	{
		$addon_file=$dir['path'];
		if (substr($addon_file,-1)=='/') continue;
		if ((is_null($files)) || (in_array($addon_file,$files)))
		{
			$GLOBALS['SITE_DB']->query_insert('addons_files',array(
				'addon_name'=>$addon,
				'filename'=>$addon_file
			));
		}
	}

	// Install new zones
	$zones=array('');
	foreach ($directory as $dir)
	{
		$addon_file=$dir['path'];

		if ((is_null($files)) || (in_array($addon_file,$files)))
		{
			$matches=array();
			if (preg_match('#(\w*)/index.php#',$addon_file,$matches)!=0)
			{
				$zone=$matches[1];

				$test=$GLOBALS['SITE_DB']->query_value_null_ok('zones','zone_name',array('zone_name'=>$zone));
				if (is_null($test))
				{
					require_code('menus2');
					add_menu_item_simple('zone_menu',NULL,$zone,$zone.':',0,1);
					$GLOBALS['SITE_DB']->query_insert('zones',array('zone_name'=>$zone,'zone_title'=>insert_lang($zone,1),'zone_default_page'=>'start','zone_header_text'=>insert_lang('???',2),'zone_theme'=>'default','zone_wide'=>0,'zone_require_session'=>0,'zone_displayed_in_menu'=>1));

					$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(false,true);
					foreach (array_keys($groups) as $group_id)
						$GLOBALS['SITE_DB']->query_insert('group_zone_access',array('zone_name'=>$zone,'group_id'=>$group_id));
				}

				$zones[]=$zone;
			}
		}
	}

	// Install new modules
	$zones=array_unique(array_merge(find_all_zones(),$zones));
	if (get_option('collapse_user_zones')=='1') $zones[]='site';
	foreach ($zones as $zone)
	{
		$prefix=($zone=='')?'':($zone.'/');

		foreach ($directory as $dir)
		{
			$addon_file=$dir['path'];

			if ((is_null($files)) || (in_array($addon_file,$files)))
			{
				if (preg_match('#^'.$prefix.'pages/(modules|modules\_custom)/([^/]*)\.php$#',$addon_file,$matches)!=0)
				{
					if (!module_installed($matches[2]))
						reinstall_module($zone,$matches[2]);
				}
			}
		}
	}

	// Install news blocks
	foreach ($directory as $dir)
	{
		$addon_file=$dir['path'];

		if ((is_null($files)) || (in_array($addon_file,$files)))
		{
			if (preg_match('#^(sources|sources\_custom)/blocks/([^/]*)\.php$#',$addon_file,$matches)!=0)
			{
				if (!block_installed($matches[2]))
					reinstall_block($matches[2]);
			}
		}
	}

	// Clear some cacheing
	require_code('view_modes');
	require_code('zones2');
	require_code('zones3');
	erase_comcode_page_cache();
	erase_tempcode_cache();
	persistent_cache_empty();
	erase_cached_templates();
	erase_cached_language();

	// Load mod.php if it exists
	$_modphp_file=tar_get_file($tar,'mod.php');
	if (!is_null($_modphp_file))
	{
		$modphp_file=trim($_modphp_file['data']);

		if (!defined('HIPHOP_PHP'))
		{
			if (substr($modphp_file,0,5)=='<'.'?php') $modphp_file=substr($modphp_file,5);
			if (substr($modphp_file,-2)=='?'.'>') $modphp_file=substr($modphp_file,0,strlen($modphp_file)-2);
			if (eval($modphp_file)===false) fatal_exit(@strval($php_errormsg));
		} else
		{
			$matches=array();
			$num_matches=preg_match_all('#\$GLOBALS[\'SITE_DB\']->query_insert(\'theme_images\',array(\'id\'=>\'([^\']*)\',\'theme\'=>\'([^\']*)\',\'path\'=>\'([^\']*)\',\'lang\'=>\'([^\']*)\'),false,true);#',$modphp_file,$matches);
			for ($i=0;$i<$num_matches;$i++)
			{
				$GLOBALS['SITE_DB']->query_insert('theme_images',array('id'=>$matches[1][$i],'theme'=>$matches[2][$i],'path'=>$matches[3][$i],'lang'=>$matches[4][$i]),false,true);
			}
		}
	}

	tar_close($tar);

	// Call install script, if it exists
	$path='/data_custom/'.strtolower(basename($file,'.tar')).'_install.php';
	if (file_exists(get_file_base().$path))
	{
		require_code('files');
		http_download_file(get_base_url().$path);
	}

	log_it('INSTALL_ADDON',$addon);
}

/**
 * Uninstall an addon.
 *
 * @param  string			Name of the addon
 */
function uninstall_addon($name)
{
	global $ADDON_INSTALLED_CACHE;

	$addon_row=read_addon_info($name);

	require_code('zones2');
	require_code('zones3');

	// Clear some cacheing
	require_code('view_modes');
	require_code('zones2');
	require_code('zones3');
	erase_comcode_page_cache();
	erase_tempcode_cache();
	persistent_cache_empty();
	erase_cached_templates();
	erase_cached_language();
	global $HOOKS_CACHE;
	$HOOKS_CACHE=array();

	// Remove addon info from database, modules, blocks, and files
	$last=array();
	foreach ($addon_row['addon_files'] as $filename)
	{
		if (file_exists(get_file_base().'/'.$filename))
		{
			$test=$GLOBALS['SITE_DB']->query_value('addons_files','COUNT(*)',array('filename'=>$filename));
			if ($test<=1) // Make sure it's not shared with other addons
			{
				if (substr($filename,0,37)=='sources/hooks/systems/addon_registry/')
				{
					$last[]=$filename;
					continue;
				}

				$matches=array();
				if (preg_match('#([^/]*)/?pages/modules(_custom)?/(.*)\.php#',$filename,$matches)!=0)
					uninstall_module($matches[1],$matches[3]);
				if (preg_match('#sources(_custom)?/blocks/(.*)\.php#',$filename,$matches)!=0)
					uninstall_block($matches[2]);
				if (preg_match('#^([^/]*)/index.php#',$filename,$matches)!=0)
					actual_delete_zone_lite($matches[1]);
				if (($filename!='mod.inf') && ($filename!='mod.php') && ($filename!='') && (substr($filename,-1)!='/'))
				{
					$last[]=$filename;
				}
			}
		}
	}
	foreach ($last as $filename)
	{
		afm_delete_file($filename);
	}
	$GLOBALS['SITE_DB']->query_delete('addons_files',array('addon_name'=>$addon_row['addon_name']));
	$GLOBALS['SITE_DB']->query_delete('addons_dependencies',array('addon_name'=>$addon_row['addon_name']));
	$GLOBALS['SITE_DB']->query_delete('addons',array('addon_name'=>$addon_row['addon_name']),'',1);

	log_it('UNINSTALL_ADDON',$addon_row['addon_name']);

	unset($ADDON_INSTALLED_CACHE[$name]);
}

/**
 * Get information for the user relating to an addon that they are intending to install.
 *
 * @param  string			Filename of the addon TAR file
 * @param  ?array			List of addons that we're currently uninstalling (so dependencies from these are irrelevant). (NULL: none)
 * @param  ?array			List of addons that we're currently installing (so dependencies to these are irrelevant). (NULL: none)
 * @param  boolean		Whether to make sure we always return, rather than possibly bombing out with a dependency management UI
 * @return array			Triple: warnings, files, addon info array
 */
function inform_about_addon_install($file,$also_uninstalling=NULL,$also_installing=NULL,$always_return=false)
{
	if (is_null($also_uninstalling)) $also_uninstalling=array();
	if (is_null($also_installing)) $also_installing=array();

	$full=get_custom_file_base().'/imports/addons/'.$file;

	// Look in the tar
	require_code('tar');
	if (!file_exists($full)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$tar=tar_open($full,'rb');
	$directory=tar_get_directory($tar);
	$info_file=tar_get_file($tar,'mod.inf');
	if (is_null($info_file)) warn_exit(do_lang_tempcode('NOT_ADDON'));
	$info=better_parse_ini_file(NULL,$info_file['data']);
	$addon=$info['name'];
	$php=false;
	$overwrite=new ocp_tempcode();
	$dirs=array();
	$files=new ocp_tempcode();
	$files_warnings=new ocp_tempcode();

	global $M_SORT_KEY;
	$M_SORT_KEY='path';
	usort($directory,'multi_sort');

	foreach ($directory as $i=>$entry)
	{
		if ($entry['path']=='mod.inf') continue;
		if ($entry['path']=='mod.php') continue;
		if (substr($entry['path'],-1)=='/') continue;

		$data=(strtolower(substr($entry['path'],-4,4))=='.tpl')?tar_get_file($tar,$entry['path'],true):NULL;

		// .php?
		if ((strtolower(substr($entry['path'],-4,4))=='.php') || ((!is_null($data)) && ((strpos($data['data'],'{+START,PHP')!==false) || (strpos($data['data'],'<'.'?php')!==false))))
		{
			$php=true;
			$this_php=true;
		} else $this_php=false;

		// chmod?
		$pos=strrpos($entry['path'],'/');
		if ($pos!==false) $dirs[substr($entry['path'],0,$pos)]=1; else $dirs['']=1;

		// overwrite?
		if (file_exists(get_file_base().'/'.$entry['path']))
		{
			if (!$overwrite->is_empty()) $overwrite->attach(do_lang_tempcode('LIST_SEP'));
			$overwrite->attach(escape_html(/*do_lang('ROOT').'/'.*/(($entry['path'][0]=='/')?substr($entry['path'],1):$entry['path'])));
			$this_overwrite=true;
		} else $this_overwrite=false;

		// Comcode?
		if ((strtolower(substr($entry['path'],-4,4))=='.txt') && (strpos($entry['path'],'pages/comcode')!==false))
		{
			$this_comcode_page=true;
		} else $this_comcode_page=false;

		// Template
		if ($this_comcode_page)
		{
			$files_warnings->attach(do_template('ADDON_INSTALL_FILES_WARNING',array('_GUID'=>'d0cf99f96262296df4afe2387f4cd3e8','I'=>strval($i),'PATH'=>$entry['path'],'ABOUT'=>do_lang_tempcode('ADDON_FILE_IS_COMCODE_PAGE'))));
		}
		elseif ($this_overwrite)
		{
			$backup=(substr($entry['path'],-4)=='.txt');
			$files_warnings->attach(do_template('ADDON_INSTALL_FILES_WARNING',array('_GUID'=>'c62168dee316d8f73d20a0d70d41b1a4','I'=>strval($i),'PATH'=>$entry['path'],'ABOUT'=>do_lang_tempcode($backup?'ADDON_FILE_WILL_OVERWRITE_BACKUP':'ADDON_FILE_WILL_OVERWRITE'))));
		}
		elseif ($this_php)
		{
			$files_warnings->attach(do_template('ADDON_INSTALL_FILES_WARNING',array('_GUID'=>'c0cf99f96262296df4afe2387f4cd3e8','I'=>strval($i),'PATH'=>$entry['path'],'ABOUT'=>do_lang_tempcode('ADDON_FILE_IS_PHP'))));
		}
		else
		{
			$files->attach(do_template('ADDON_INSTALL_FILES',array('_GUID'=>'74edcf396387c842cab5cfd0ab74b8f6','I'=>strval($i),'PATH'=>$entry['path'],'ABOUT'=>do_lang_tempcode('ADDON_FILE_NORMAL'))));
		}

	}
	tar_close($tar);
	$chmod=new ocp_tempcode();
	$root_chmod=false;
	foreach (array_keys($dirs) as $dir)
	{
		if ((is_writable_wrap(get_file_base().'/'.$dir)) && (file_exists(get_file_base().'/'.$dir)))
		{
			if ($dir=='')
			{
				$root_chmod=true;
				continue;
			}

			if (!$chmod->is_empty()) $chmod->attach(do_lang_tempcode('LIST_SEP'));
			$chmod->attach(escape_html(do_lang('ROOT').(($dir[0]!='/')?'/':'').$dir));
		}
		elseif ((substr_count($dir,'/')==1) && (!file_exists(get_file_base().'/'.$dir)))
		{
			$root_chmod=true;
		}
	}
	if ($root_chmod)
	{
		if (!$chmod->is_empty()) $chmod->attach(', ');
		$chmod->attach(do_lang('ROOT'));
	}

	// Check incompatibilities, and show general warning
	// NB: It's theoretically possible that there may be incompatibilities between two addons installing together, and we can't detect this (only incompatibilities for what is already installed). However it's very unlikely as multi-install is only really going to happen with official addons which have no such problems.
	$warnings=new ocp_tempcode();
	if ($info['author']!='Core Team') $warnings->attach(do_template('ADDON_INSTALL_WARNING',array('_GUID'=>'dd66b2c540908de60753a1ced73b8ac0','WARNING'=>do_lang_tempcode('ADDON_WARNING_GENERAL'))));
	$incompatibilities=collapse_1d_complexity('addon_name',$GLOBALS['SITE_DB']->query_select('addons_dependencies',array('addon_name'),array('addon_name_dependant_upon'=>$addon,'addon_name_incompatibility'=>1)));
	$_incompatibilities=new ocp_tempcode();
	foreach ($incompatibilities as $in)
	{
		if (!$_incompatibilities->is_empty()) $_incompatibilities->attach(do_lang_tempcode('LIST_SEP'));
		$_incompatibilities->attach(escape_html($in));
	}
	if (count($incompatibilities)!=0) $warnings->attach(do_template('ADDON_INSTALL_WARNING',array('WARNING'=>do_lang_tempcode('ADDON_WARNING_INCOMPATIBILITIES',$_incompatibilities,escape_html($file)))));

	// Check dependencies
	$_dependencies=explode(',',array_key_exists('dependencies',$info)?$info['dependencies']:'');
	$dependencies=array();
	foreach ($_dependencies as $dependency)
	{
		if ($dependency=='') continue;
		if (in_array($dependency.'.tar',$also_installing)) continue;
		if (in_array($dependency.'.tar',$also_uninstalling))
		{
			$dependencies[]=$dependency;
			continue;
		}
		if (!has_feature($dependency)) $dependencies[]=$dependency;
	}
	$_dependencies_str=new ocp_tempcode();
	foreach ($dependencies as $in)
	{
		if (!$_dependencies_str->is_empty()) $_dependencies_str->attach(do_lang_tempcode('LIST_SEP'));
		if (file_exists(get_custom_file_base().'/imports/addons/'.$in.'.tar'))
		{
			$in_tpl=hyperlink(build_url(array('page'=>'admin_addons','type'=>'addon_install','file'=>$in.'.tar'),get_module_zone('admin_addons')),$in,true,true);
		} else
		{
			$in_tpl=make_string_tempcode(escape_html($in));
		}
		$_dependencies_str->attach($in_tpl);
	}
	if (count($dependencies)!=0)
	{
		if (($info['author']=='Core Team') && (!$always_return))
		{
			$post_fields=build_keep_post_fields();
			foreach ($dependencies as $in)
				$post_fields->attach(form_input_hidden('install_'.$in.'.tar',$in.'.tar'));

			if (get_param('type','misc')=='addon_install')
			{
				$post_fields->attach(form_input_hidden('install_'.$file,$file));
				$url=static_evaluate_tempcode(build_url(array('page'=>'_SELF','type'=>'multi_action'),'_SELF'));
			} else
			{
				$url=get_self_url(true);
			}
			warn_exit(do_lang_tempcode('_ADDON_WARNING_MISSING_DEPENDENCIES',$_dependencies_str->evaluate(),escape_html($addon),array(escape_html($url),$post_fields)));
		} else
		{
			$warnings->attach(do_template('ADDON_INSTALL_WARNING',array('WARNING'=>do_lang_tempcode('ADDON_WARNING_MISSING_DEPENDENCIES',$_dependencies_str,escape_html($file)))));
		}
	}

//	if (!$overwrite->is_empty()) $warnings->attach(do_template('ADDON_INSTALL_WARNING',array('_GUID'=>'fe40ed8192a452a835be4c0fde64406b','WARNING'=>do_lang_tempcode('ADDON_WARNING_OVERWRITE',escape_html($overwrite),escape_html($file)))));
	if ($info['author']!='Core Team') if ($php) $warnings->attach(do_template('ADDON_INSTALL_WARNING',array('_GUID'=>'8cf249a119d10b2e97fc94cb9981dcea','WARNING'=>do_lang_tempcode('ADDON_WARNING_PHP',escape_html($file)))));
//	if ($chmod!='') $warnings->attach(do_template('ADDON_INSTALL_WARNING',array('_GUID'=>'78121e40b9a26c2f33d09f7eee7b74be','WARNING'=>do_lan g_tempcode('ADDON_WARNING_CHMOD',escape_html($chmod))))); // Now uses AFM

	$files_combined=new ocp_tempcode();
	$files_combined->attach($files_warnings);
	$files_combined->attach($files);

	return array($warnings,$files_combined,$info);
}

/**
 * Find whether a particular feature is available to ocPortal (e.g. it's an addon).
 *
 * @param  ID_TEXT		Feature name
 * @return boolean		Whether it is
 */
function has_feature($dependency)
{
	$dependency=str_replace(' ','',strtolower(preg_replace('# (enabled|needed|required)$#','',$dependency)));

	if ($dependency=='yes') return true; // Buggy addon definition

	$remapping=array( // HACKHACK: Remove these for next major version
		'chatrooms'=>'chat',
		'side_stats'=>'stats_block',
	);
	if (array_key_exists($dependency,$remapping)) $dependency=$remapping[$dependency];

	// Non-bundled addon
	$test=$GLOBALS['SITE_DB']->query_value_null_ok('addons','addon_name',array('addon_name'=>$dependency));
	if (!is_null($test)) return true;

	// Bundled addon
	if (file_exists(get_file_base().'/sources/hooks/systems/addon_registry/'.$dependency.'.php')) return true;

	// Some other features
	if (($dependency=='javascript') && (has_js())) return true;
	if (($dependency=='cron') && (cron_installed())) return true;
	if (($dependency=='ocf') && (get_forum_type()=='ocf')) return true;
	if (($dependency=='gd') && (get_option('is_on_gd')=='1') && (function_exists('imagecreatefromstring'))) return true;
	if ($dependency=='adobeflash') return true;
	if (substr($dependency,0,3)=='php')
	{
		$phpv=phpversion();
		if (version_compare(substr($phpv,0,strlen(substr($dependency,3))),substr($dependency,3),'>=')) return true;
	}

	// ---

	// Try plural form
	if (substr($dependency,-1)!='s')
		return has_feature($dependency.'s');

	return false;
}

/**
 * Get information for the user relating to an addon that they are intending to uninstall.
 *
 * @param  string			Name of the addon
 * @param  ?array			List of addons that we're currently uninstalling (so dependencies from these are irrelevant). (NULL: none)
 * @param  ?array			Addon details. (NULL: load in function)
 * @param  boolean		Whether to make sure we always return, rather than possibly bombing out with a dependency management UI
 * @return array			Pair: warnings, files
 */
function inform_about_addon_uninstall($name,$also_uninstalling=NULL,$addon_row=NULL,$always_return=false)
{
	if (is_null($also_uninstalling)) $also_uninstalling=array();

	// Read/show info
	if (is_null($addon_row)) $addon_row=read_addon_info($name);
	$files=new ocp_tempcode();
	// The files can come in as either a newline-separated string or an array.
	// If its an array then we use it as-is, if it's a string then we explode it first.
	if (is_array($addon_row['addon_files']))
	{
		$loopable=$addon_row['addon_files'];
	} else
	{
		$loopable=explode(chr(10),$addon_row['addon_files']);
	}
	foreach ($loopable as $i=>$filename)
	{
		$files->attach(do_template('ADDON_INSTALL_FILES',array('I'=>strval($i),'DISABLED'=>true,'PATH'=>$filename)));
	}

	// Check dependencies
	$dependencies=$addon_row['addon_dependencies_on_this'];
	foreach ($also_uninstalling as $d)
	{
		if (in_array($d,$dependencies)) unset($dependencies[array_search($d,$dependencies)]);
	}
	$warnings=new ocp_tempcode();
	$_dependencies_str=new ocp_tempcode();
	foreach ($dependencies as $in)
	{
		if (!$_dependencies_str->is_empty()) $_dependencies_str->attach(do_lang_tempcode('LIST_SEP'));
		$_dependencies_str->attach(escape_html($in));
	}
	if (count($dependencies)!=0)
	{
		if (($addon_row['addon_author']=='Core Team') && (!$always_return))
		{
			$post_fields=build_keep_post_fields();
			foreach ($dependencies as $in)
				$post_fields->attach(form_input_hidden('uninstall_'.$in,$in));
			if (get_param('type','misc')=='addon_uninstall')
			{
				$post_fields->attach(form_input_hidden('uninstall_'.$name,$name));
				$url=static_evaluate_tempcode(build_url(array('page'=>'_SELF','type'=>'multi_action'),'_SELF'));
			} else
			{
				$url=get_self_url(true);
			}
			warn_exit(do_lang_tempcode('_ADDON_WARNING_PRESENT_DEPENDENCIES',$_dependencies_str->evaluate(),escape_html($name),array(escape_html($url),$post_fields)));
		} else
		{
			$warnings->attach(do_template('ADDON_INSTALL_WARNING',array('WARNING'=>do_lang_tempcode('ADDON_WARNING_PRESENT_DEPENDENCIES',$_dependencies_str,escape_html($name)))));
		}
	}

	return array($warnings,$files);
}
