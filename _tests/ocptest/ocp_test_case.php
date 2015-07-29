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

function must_skip($is_dir,$file,$dir,$upgrading=false,$allow_other_addons=false)
{
	global $ocPortal_path;

	if (($file=='.') || ($file=='..')) return true;

	if (is_dir($ocPortal_path.$dir.$file))
	{
		// Wrong lang packs
		if (($dir!='lang/') && (((strlen($file)==2) && (strtoupper($file)==$file) && (strtolower($file)!=$file)) || ($file=='EN_us') || ($file=='ZH-TW') || ($file=='ZH-CN')) && ($file!='EN'))
		{
			return true;
		}

		// Wrong zones
		if ((file_exists($ocPortal_path.$dir.$file.'/index.php')) && (file_exists($ocPortal_path.$dir.$file.'/pages')) && (!in_array($file,array('adminzone','collaboration','cms','forum','site','personalzone'))))
		{
			return true;
		}

		// Wrong data_custom files
		if (($dir=='data_custom/') && ($file!='fonts') && ($file!='modules') && ($file!='sifr') && ($file!='spelling')) return true;

		// Wrong sources_custom files
		if (($dir=='sources_custom/') && ($file!='blocks') && ($file!='database') && ($file!='hooks') && ($file!='miniblocks')) return true;
		if ($dir=='sources_custom/hooks/blocks/') return true;
		if ($dir=='sources_custom/hooks/modules/') return true;
		if ($dir=='sources_custom/hooks/systems/') return true;

		if (($dir=='exports/') && ($file=='static')) return true;

		// Wrong images_custom files
		if ($dir=='themes/default/images_custom/') return true;
	}

	$bad_root_files=array('.editorconfig','.idea','hphp-static-cache','hphp.files.list','www.pid','hphp','facebook_connect.php','.gitignore','.gitattributes','transcoder','killjunk.sh','restore.php','subs.inc','nbproject','_tests','info-override.php','make_files-output-log.html','manifest.xml','parameters.xml','BingSiteAuth.xml','ocportal.heap','ocportal.sch','libocportal_u.dll','gc.log','.bash_history','ocportal.fcgi','.htaccess','info.php.template','install1.sql','install2.sql','install3.sql','install4.sql','user.sql','postinstall.sql','install.sql','install.php','no_mem_cache','install_ok','install_locked','registered.php','ocp.zpj','.project');
	if (((!$allow_other_addons) && ($file=='Gibb')) || ($file=='Gibberish') || ($file=='ocworld') || ($file=='.svn') || ($file=='.git') || ($file=='myocp') || ($file=='docsformer') || ($file=='if_hosted_service.txt') || ($file=='Thumbs.db') || ($file=='Thumbs.db:encryptable') || ($file=='.DS_Store') || (!$allow_other_addons) && (substr($file,-4)=='.tar') || (substr($file,-7)=='.clpprj') || (substr($file,-7)=='.tmproj') || (substr($file,-3)=='.gz') || (substr($file,-2)=='.o') || (substr($file,-4)=='.scm') || ($file=='php.ini') || ($file=='CVS') || ($file=='WEB-INF') || (substr($file,0,5)=='_vti_')) return true;

	if ($dir=='')
	{
		if (in_array($file,$bad_root_files)) return true;
	}
	else
	{
		if (($dir=='uploads/website_specific/') && ($file!='index.html')) return true;
		if (($dir=='uploads/') && ((strpos($file,'addon_')!==false) || (strpos($file,'_addon')!==false))) return true;
		if ($dir=='data/images/docs/') return true;
		if ($dir=='data/images/ocproducts/') return true;
		if (($file=='ocp_sitemap.xml') && ($upgrading)) return true;
		if (($dir=='data/modules/admin_stats/') && (substr($file,-4)=='.xml')) return true;
		if (
			(
				(substr($dir,0,8)=='uploads/')
				 || (substr($dir,0,8)=='exports/')
				 || (substr($dir,0,8)=='imports/')
				 || ((strpos($dir,'_custom')!==false) && ($dir!='sources/hooks/blocks/main_custom_gfx/'))
			)
			&& (!$is_dir)
			&& ($file!='index.html')
			&& ($file!='.htaccess')
			&& ($file!='functions.dat')
			&& (($file!='fields.xml') || ($upgrading))
			&& (($file!='breadcrumbs.xml') || ($upgrading))
			&& (($file!='execute_temp.php') || ($upgrading))
			&& ($file!='ecommerce.log')
			&& (($file!='errorlog.php') || ($upgrading))
			&& (($file!='ocp_sitemap.xml') || ($upgrading))
			&& (($file!='write.log') || ($upgrading))
			&& (($file!='output.log') || ($upgrading))
			&& (($file!='download_tree_made.htm') || ($upgrading))
			&& (($file!='cedi_tree_made.htm') || ($upgrading))
		)
		 return true;
		if ($dir=='data/areaedit/plugins/SpellChecker/aspell/') return true;
		if ((($dir=='themes/default/templates_cached/EN/') || ($dir=='lang_cached/EN/') || ($dir=='persistant_cache/')) && ($file!='index.html') && ($file!='.htaccess')) return true;
		if ((($dir=='themes/default/templates_cached/') || ($dir=='lang_cached/')) && ($file!='index.html') && ($file!='.htaccess') && ($file!='EN')) return true;
		if (($dir=='themes/') && (!in_array($file,array('default','index.html','map.ini')))) return true;
	}

	return false;
}

/**
 * ocPortal test case class (unit testing).
 */
class ocp_test_case extends WebTestCase
{
	var $site_closed;

	function setUp()
	{
		// Make sure the site is open
		$this->site_closed=get_option('site_closed');
		require_code('config2');
		set_option('site_closed','0');
	}

	function tearDown()
	{
		set_option('site_closed',$this->site_closed);
	}

	function get($url,$parameters=NULL)
	{
		$parts=array();
		if ((preg_match('#([\w-]*):([\w-]+|[^/]|$)((:(.*))*)#',$url,$parts)!=0) && ($parts[1]!='mailto')) // Specially encoded page link. Complex regexp to make sure URLs do not match
		{
			list($zone_name,$vars,$hash)=page_link_decode($url);
			$real_url=_build_url($vars,$zone_name,NULL,false,false,false,$hash);

			$ret=parent::get($real_url,$parameters);
		} else
		{
			$ret=parent::get($url,$parameters);
		}

		// Save, so we can run validation on it later
		$path=get_file_base().'/_tests/html_dump/'.get_class($this);
		if (!file_exists($path)) mkdir($path,0777);
		$content=$this->_browser->getContent();
		$outfile=fopen($path.'/'.url_to_filename($url).'.htm','wb');
		fwrite($outfile,$content);
		fclose($outfile);
		sync_file($path.'/'.url_to_filename($url).'.htm');
		fix_permissions($path.'/'.url_to_filename($url).'.htm');

		// Save the text so we can run through Word's grammar checker
		$text_content=$content;
		$text_content=preg_replace('#<[^>]* title="([^"]+)"<[^>]*>#U','\\1',$text_content);
		$text_content=preg_replace('#<[^>]* alt="([^"]+)"<[^>]*>#U','\\1',$text_content);
		$text_content=preg_replace('#<style[^>]*>.*</style>#Us','',$text_content);
		$text_content=preg_replace('#<script[^>]*>.*</script>#Us','',$text_content);
		$text_content=preg_replace('#<[^>]*>#U','',$text_content);
		$text_content=preg_replace('#\s\s+#','. ',$text_content);
		$text_content=str_replace('&ndash;','-',$text_content);
		$text_content=str_replace('&mdash;','-',$text_content);
		$text_content=str_replace('&hellip;','...',$text_content);
		$text_content=@html_entity_decode($text_content,ENT_QUOTES);
		$outfile=fopen($path.'/'.url_to_filename($url).'.txt','wb');
		fwrite($outfile,$text_content);
		fclose($outfile);

		return $ret;
	}

	function establish_admin_session()
	{
		global $MEMBER_CACHED;
		require_code('users_active_actions');
		$MEMBER_CACHED=restricted_manually_enabled_backdoor();
		$this->dump($this->_browser->getContent());
	}
}
