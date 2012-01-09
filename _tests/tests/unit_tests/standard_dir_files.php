<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

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
class standard_dir_files_test_set extends ocp_test_case
{
	function setUp()
	{
		parent::setUp();
	}

	function testStandardDirFiles()
	{
		$this->do_dir(get_file_base());
	}
	
	function do_dir($dir)
	{
		if ((!file_exists($dir.'/index.php')) && (!file_exists($dir.'/index.html')) && (strpos($dir,'ckeditor')===false) && (strpos($dir,'nbproject')===false) && (strpos($dir,'areaedit')===false) && (strpos($dir,'themes')===false) && (strpos($dir,'personal_dicts')===false))
		{
			$this->assertTrue(false,'touch "'.$dir.'/index.html" ; svn add "'.$dir.'/index.html"');
		}

		if (($dh=opendir($dir))!==false)
		{
			while (($file=readdir($dh))!==false)
			{
				if ($file[0]=='.') continue;

				if (is_dir($dir.'/'.$file))
				{
					$this->do_dir($dir.'/'.$file);
				}
			}
		}

		if ((!file_exists($dir.'/.htaccess')) && (!file_exists($dir.'/index.php')) && (!file_exists($dir.'/html_custom')) && (!file_exists($dir.'/EN')) && (strpos($dir,'ckeditor')===false) && (strpos($dir,'transcoder')===false) && (strpos($dir,'nbproject')===false) && (strpos($dir,'facebook')===false) && (strpos($dir,'uploads')===false) && (preg_match('#/data(/|$|\_)#',$dir)==0) && (strpos($dir,'themes')===false) && (strpos($dir,'exports')===false))
		{
			$this->assertTrue(false,'cp "'.get_file_base().'/sources/.htaccess" "'.$dir.'/.htaccess" ; svn add "'.$dir.'/.htaccess"');
		}
	}

	function tearDown()
	{
		parent::tearDown();
	}
}
