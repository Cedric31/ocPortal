<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core
 */

// Find ocPortal base directory, and chdir into it
global $FILE_BASE,$RELATIVE_PATH;
$FILE_BASE=(strpos(__FILE__,'./')===false)?__FILE__:realpath(__FILE__);
$FILE_BASE=dirname($FILE_BASE);
if (!is_file($FILE_BASE.'/sources/global.php')) // Need to navigate up a level further perhaps?
{
	$RELATIVE_PATH=basename($FILE_BASE);
	$FILE_BASE=dirname($FILE_BASE);
} else
{
	$RELATIVE_PATH='';
}
@chdir($FILE_BASE);

global $NON_PAGE_SCRIPT;
$NON_PAGE_SCRIPT=1;
global $FORCE_INVISIBLE_GUEST;
$FORCE_INVISIBLE_GUEST=false;
if (!is_file($FILE_BASE.'/sources/global.php')) exit('<!DOCTYPE html>'."\n".'<html lang="EN"><head><title>Critical startup error</title></head><body><h1>ocPortal startup error</h1><p>The second most basic ocPortal startup file, sources/global.php, could not be located. This is almost always due to an incomplete upload of the ocPortal system, so please check all files are uploaded correctly.</p><p>Once all ocPortal files are in place, ocPortal must actually be installed by running the installer. You must be seeing this message either because your system has become corrupt since installation, or because you have uploaded some but not all files from our manual installer package: the quick installer is easier, so you might consider using that instead.</p><p>ocProducts maintains full documentation for all procedures and tools, especially those for installation. These may be found on the <a href="http://ocportal.com">ocPortal website</a>. If you are unable to easily solve this problem, we may be contacted from our website and can help resolve it for you.</p><hr /><p style="font-size: 0.8em">ocPortal is a website engine created by ocProducts.</p></body></html>'); require($FILE_BASE.'/sources/global.php');


$mode=get_param('mode'); // bundle | unbundle
$addon=get_param('addon');

if ($mode=='unbundle')
{
	require_code('hooks/systems/addon_registry/'.$addon);
	$ob=object_factory('Hook_addon_registry_'.$addon);
	$files=$ob->get_file_list();
	foreach ($files as $file)
	{
		$new_file=NULL;
		$matches=array();

		if (preg_match('#^themes/default/images/(.*)$#',$file,$matches)!=0)
		{
			$new_file='themes/default/images_custom/'.$matches[1];
		}
		if (preg_match('#^themes/default/css/(.*)$#',$file,$matches)!=0)
		{
			$new_file='themes/default/css_custom/'.$matches[1];
		}
		if (preg_match('#^themes/default/templates/(.*)$#',$file,$matches)!=0)
		{
			$new_file='themes/default/templates_custom/'.$matches[1];
		}
		if (preg_match('#^sources/(.*)$#',$file,$matches)!=0)
		{
			$new_file='sources_custom/'.$matches[1];
		}
		if (preg_match('#^pages/modules/(.*)$#',$file,$matches)!=0)
		{
			$new_file='pages/modules_custom/'.$matches[1];
		}
		if (preg_match('#^(.*)/pages/modules/(.*)$#',$file,$matches)!=0)
		{
			$new_file=$matches[1].'/pages/modules_custom/'.$matches[2];
		}
		if (preg_match('#^lang/(.*)$#',$file,$matches)!=0)
		{
			$new_file='lang_custom/'.$matches[1];
		}

		if (!is_null($new_file))
		{
			//var_dump($new_file);continue;
			if (!file_exists(get_file_base().'/'.$new_file))
			{
				@mkdir(dirname($new_file),0777,true);
				rename(get_file_base().'/'.$file,get_file_base().'/'.$new_file);
			} // else already moved
		} else
		{
			//var_dump($file);
		}
	}
}

if ($mode=='bundle')
{
	// Not currently implemented
}

echo 'Done.';
