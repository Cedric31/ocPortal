<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.

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
		if (function_exists('set_time_limit')) @set_time_limit(0);

		parent::setUp();
	}

	function testStandardDirFiles()
	{
		$this->do_dir(get_file_base());
	}

	function do_dir($dir)
	{
		if ((!file_exists($dir.'/index.php')) && (!file_exists($dir.'/index.html')) && (strpos($dir,'ckeditor')===false) && (strpos($dir,'hphp')===false) && (strpos($dir,'static')===false) && (strpos($dir,'nbproject')===false) && (strpos($dir,'areaedit')===false) && (strpos($dir,'themes')===false) && (strpos($dir,'personal_dicts')===false))
		{
			$this->assertTrue(false,'touch "'.$dir.'/index.html" ; git add -f "'.$dir.'/index.html"');
		}

		if (($dh=opendir($dir))!==false)
		{
			while (($file=readdir($dh))!==false)
			{
				if (should_ignore_file(preg_replace('#^'.preg_quote(get_file_base().'/','#').'#','',$dir.'/').$file,IGNORE_NONBUNDLED_SCATTERED | IGNORE_CUSTOM_DIR_CONTENTS | IGNORE_CUSTOM_ZONES | IGNORE_CUSTOM_THEMES | IGNORE_NON_EN_SCATTERED_LANGS | IGNORE_BUNDLED_UNSHIPPED_VOLATILE,0))
					continue;

				if ($file=='tracker') continue; // Ideally would not hard-code for a non-bundled addon, but we need to for this one

				if (is_dir($dir.'/'.$file))
				{
					$this->do_dir($dir.'/'.$file);
				}
			}
		}

		if ((!file_exists($dir.'/.htaccess')) && (!file_exists($dir.'/index.php')) && (!file_exists($dir.'/html_custom')) && (strpos($dir,'hphp')===false) && (strpos($dir,'static')===false) && (!file_exists($dir.'/EN')) && (strpos($dir,'ckeditor')===false) && (strpos($dir,'transcoder')===false) && (strpos($dir,'nbproject')===false) && (strpos($dir,'facebook')===false) && (strpos($dir,'uploads')===false) && (preg_match('#/data(/|$|\_)#',$dir)==0) && (strpos($dir,'themes')===false) && (strpos($dir,'exports')===false))
		{
			$this->assertTrue(false,'cp "'.get_file_base().'/sources/.htaccess" "'.$dir.'/.htaccess" ; git add "'.$dir.'/.htaccess"');
		}
	}

	function tearDown()
	{
		parent::tearDown();
	}
}
