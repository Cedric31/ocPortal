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
 * @package		unit_testing
 */

/**
 * ocPortal test case class (unit testing).
 */
class modularisation_test_set extends ocp_test_case
{
	function setUp()
	{
		parent::setUp();
	}

	function testModularisation()
	{
		global $GFILE_ARRAY,$DIR_ARRAY;

		$addon_data=array();
		$dh=opendir(get_file_base().'/sources/hooks/systems/addon_registry');
		while (($file=readdir($dh))!==false)
		{
			if (substr($file,-4)!='.php') continue;
			require_once(get_file_base().'/sources/hooks/systems/addon_registry/'.$file);
			$ob=eval('return new Hook_addon_registry_'.basename($file,'.php').'();');
			$addon_data[basename($file,'.php')]=$ob->get_file_list();
		}

		$seen=array();
		foreach ($addon_data as $d)
		{
			foreach ($d as $file)
			{
				if (array_key_exists($file,$seen))
				{
					$this->assertTrue(false,'Double referenced: '.$file);
				}
				$seen[$file]=1;
			}
		}

		$GFILE_ARRAY=array();
		//$DIR_ARRAY=array();
		$this->do_dir();
		$unput_files=array();
		foreach ($GFILE_ARRAY as $path)
		{
			if ((substr($path,-4)=='.tpl') || ((substr($path,-4)=='.css') && (substr($path,0,6)=='themes')))
			{
				$path=basename($path);
			}

			$found=false;
			foreach ($addon_data as $section_name=>$section)
			{
				foreach ($section as $fileindex=>$file)
				{
					if ($file==$path)
					{
						if (substr($file,-4)=='.php')
						{
							$data=file_get_contents(get_file_base().'/'.$file);
							$matches=array();
							$m_count=preg_match('#@package\s+(\w+)#',$data,$matches);
							if (($m_count!=0) && ($matches[1]!=$section_name))
							{
								$this->assertTrue(false,'@package wrong for <a href="txmt://open?url=file://'.htmlentities(get_file_base().'/'.$file).'">'.htmlentities($path).'</a> (should be '.$section_name.')');
							} elseif (($m_count==0) && ($file!='info.php') && ($file!='data_custom/errorlog.php'))
							{
								$this->assertTrue(false,'No @package for <a href="txmt://open?url=file://'.htmlentities(get_file_base().'/'.$file).'">'.htmlentities($path).'</a> (should be '.$section_name.')');
							}
						}

						$found=true;
						unset($section[$fileindex]);
						$addon_data[$section_name]=$section;
						break 2;
					}
				}
			}
			if (!$found)
			{
				$data=@file_get_contents(get_file_base().'/'.$path);
				$matches=array();
				$m_count=preg_match('#@package\s+(\w+)#',$data,$matches);
				if ($m_count!=0)
				{
					$unput_files[$matches[1]][]=$path;
				} else
				{
					$this->assertTrue(false,'Could not find the addon for... \''.htmlentities($path).'\',');
				}
			}
		}
		ksort($unput_files);
		foreach ($unput_files as $addon=>$paths)
		{
			echo '<br /><strong>'.htmlentities($addon).'</strong>';
			foreach ($paths as $path)
			{
				$this->assertTrue(false,'Could not find the addon for... \''.$path.'\',');
			}
		}
		foreach ($addon_data as $section_name=>$section)
		{
			if (!file_exists(get_file_base().'/sources/hooks/systems/addon_registry/'.$section_name.'.php'))
				$this->assertTrue(false,'Addon files missing / not in main distribution / referenced twice... \'sources/hooks/systems/addon_registry/'.$section_name.'.php\',');
			foreach ($section as $file)
			{
				if (($file!='_notes_') && ($file!='_requires_'))
				{
					$this->assertTrue(false,'Addon files missing / not in main distribution / referenced twice... \''.htmlentities($file).'\',');
				}
			}
		}
	}

	function do_dir($dir='')
	{
		global $GFILE_ARRAY,$DIR_ARRAY;

		$full_dir=get_file_base().'/'.$dir;
		$dh=opendir($full_dir);
		while (($file=readdir($dh))!==false)
		{
			$is_dir=is_dir(get_file_base().'/'.$dir.$file);

			if (must_skip($is_dir,$file,$dir,false,true)) continue;

			if ($is_dir)
			{
	//			$DIR_ARRAY[]=$dir.$file;
				$this->do_dir($dir.$file.'/');
			}
			else
			{
				$GFILE_ARRAY[]=$dir.$file;
			}
		}
	}
	
	function tearDown()
	{
		parent::tearDown();
	}
}
