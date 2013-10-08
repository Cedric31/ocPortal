<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		debrand
 */

/**
 * Module page class.
 */
class Module_admin_debrand
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=false;
		return $info;
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('misc'=>'SUPER_DEBRAND');
	}

	var $title;

	/**
	 * Standard modular pre-run function, so we know meta-data for <head> before we start streaming output.
	 *
	 * @return ?tempcode		Tempcode indicating some kind of exceptional output (NULL: none).
	 */
	function pre_run()
	{
		$type=get_param('type','misc');

		require_lang('debrand');

		set_helper_panel_pic('pagepics/debrand');
		set_helper_panel_text(comcode_lang_string('DOC_SUPERDEBRAND'));

		$this->title=get_screen_title('SUPER_DEBRAND');

		return NULL;
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		if (get_file_base()!=get_custom_file_base()) warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));

		require_lang('config');

		$type=get_param('type','misc');
		if ($type=='misc') return $this->misc();
		if ($type=='actual') return $this->actual();

		return new ocp_tempcode();
	}

	/**
	 * The UI for managing super debranding.
	 *
	 * @return tempcode		The UI
	 */
	function misc()
	{
		require_code('form_templates');

		$rebrand_name=get_value('rebrand_name');
		if (is_null($rebrand_name)) $rebrand_name='ocPortal';
		$rebrand_base_url=brand_base_url();
		$company_name=get_value('company_name');
		if (is_null($company_name)) $company_name='ocProducts';
		$keyboard_map=file_exists(get_file_base().'/pages/comcode/'.get_site_default_lang().'/keymap.txt')?file_get_contents(get_file_base().'/pages/comcode/'.get_site_default_lang().'/keymap.txt'):file_get_contents(get_file_base().'/pages/comcode/'.fallback_lang().'/keymap.txt');
		if (file_exists(get_file_base().'/pages/comcode_custom/'.get_site_default_lang().'/keymap.txt')) $keyboard_map=file_get_contents(get_file_base().'/pages/comcode_custom/'.get_site_default_lang().'/keymap.txt');
		if (file_exists(get_file_base().'/adminzone/pages/comcode_custom/'.get_site_default_lang().'/website.txt'))
		{
			$adminguide=file_get_contents(get_file_base().'/adminzone/pages/comcode_custom/'.get_site_default_lang().'/website.txt');
		} else $adminguide=do_lang('ADMINGUIDE_DEFAULT_TRAINING');
		if (file_exists(get_file_base().'/adminzone/pages/comcode_custom/'.get_site_default_lang().'/start.txt'))
		{
			$start_page=file_get_contents(get_file_base().'/adminzone/pages/comcode_custom/'.get_site_default_lang().'/start.txt');
		} elseif (file_exists(get_file_base().'/adminzone/pages/comcode/'.get_site_default_lang().'/start.txt'))
		{
			$start_page=file_exists(get_file_base().'/adminzone/pages/comcode/'.get_site_default_lang().'/start.txt')?file_get_contents(get_file_base().'/adminzone/pages/comcode/'.get_site_default_lang().'/start.txt'):file_get_contents(get_file_base().'/adminzone/pages/comcode/'.fallback_lang().'/start.txt');
		} else $start_page=do_lang('REBRAND_FRONT_PAGE');

		$fields=new ocp_tempcode();
		$fields->attach(form_input_line(do_lang_tempcode('REBRAND_NAME'),do_lang_tempcode('DESCRIPTION_REBRAND_NAME'),'rebrand_name',$rebrand_name,true));
		$fields->attach(form_input_line(do_lang_tempcode('REBRAND_BASE_URL'),do_lang_tempcode('DESCRIPTION_BRAND_BASE_URL',escape_html('docs'.strval(ocp_version()))),'rebrand_base_url',$rebrand_base_url,true));
		$fields->attach(form_input_line(do_lang_tempcode('COMPANY_NAME'),'','company_name',$company_name,true));
		$fields->attach(form_input_text_comcode(do_lang_tempcode('ADMINGUIDE'),do_lang_tempcode('DESCRIPTION_ADMINGUIDE'),'adminguide',$adminguide,true));
		$fields->attach(form_input_text_comcode(do_lang_tempcode('ADMINSTART_PAGE'),do_lang_tempcode('DESCRIPTION_ADMINSTART_PAGE'),'start_page',$start_page,true));
		$fields->attach(form_input_text_comcode(do_lang_tempcode('KEYBOARD_MAP'),'','keyboard_map',$keyboard_map,true));
		$fields->attach(form_input_tick(do_lang_tempcode('DELETE_UN_PC'),do_lang_tempcode('DESCRIPTION_DELETE_UN_PC'),'churchy',false));
		$fields->attach(form_input_tick(do_lang_tempcode('SHOW_DOCS'),do_lang_tempcode('DESCRIPTION_SHOW_DOCS'),'show_docs',get_option('show_docs')=='1'));
		$fields->attach(form_input_upload(do_lang_tempcode('FAVICON'),do_lang_tempcode('DESCRIPTION_FAVICON'),'favicon',false,find_theme_image('favicon'),NULL,true,str_replace(' ','',get_option('valid_images'))));
		$fields->attach(form_input_upload(do_lang_tempcode('APPLEICON'),do_lang_tempcode('DESCRIPTION_APPLEICON'),'appleicon',false,find_theme_image('appleicon'),NULL,true,str_replace(' ','',get_option('valid_images'))));
		if (addon_installed('ocf_avatars'))
			$fields->attach(form_input_upload(do_lang_tempcode('SYSTEM_AVATAR'),do_lang_tempcode('DESCRIPTION_SYSTEM_AVATAR'),'system_avatar',false,find_theme_image('ocf_default_avatars/default_set/ocp_fanatic'),NULL,true,str_replace(' ','',get_option('valid_images'))));

		$post_url=build_url(array('page'=>'_SELF','type'=>'actual'),'_SELF');
		$submit_name=do_lang_tempcode('SUPER_DEBRAND');

		return do_template('FORM_SCREEN',array('_GUID'=>'fd47f191ac51f7754eb17e3233f53bcc','HIDDEN'=>'','TITLE'=>$this->title,'URL'=>$post_url,'FIELDS'=>$fields,'TEXT'=>do_lang_tempcode('WARNING_SUPER_DEBRAND_MAJOR_CHANGES'),'SUBMIT_NAME'=>$submit_name));
	}

	/**
	 * The actualiser for super debranding.
	 *
	 * @return tempcode		The UI
	 */
	function actual()
	{
		require_code('config2');

		if (get_file_base()==get_custom_file_base()) // Only if not a shared install
		{
			require_code('abstract_file_manager');
			force_have_afm_details();
		}

		set_value('rebrand_name',post_param('rebrand_name'));
		set_value('rebrand_base_url',post_param('rebrand_base_url'));
		set_value('company_name',post_param('company_name'));
		set_option('show_docs',post_param('show_docs','0'));

		require_code('database_action');
		//set_option('allow_member_integration','off');

		foreach (array(get_file_base().'/pages/comcode_custom/'.get_site_default_lang(),get_file_base().'/adminzone/pages/comcode_custom/'.get_site_default_lang()) as $dir)
		{
			if (!file_exists($dir))
			{
				require_code('files');
				if (@mkdir($dir,0777)===false)
				{
					warn_exit(do_lang_tempcode('WRITE_ERROR_DIRECTORY_REPAIR',escape_html($dir)));
				}
				fix_permissions($dir,0777);
				sync_file($dir);
			}
		}

		$keyboard_map_path=get_file_base().'/pages/comcode_custom/'.get_site_default_lang().'/keymap.txt';
		$myfile=@fopen($keyboard_map_path,'wb');
		if ($myfile===false) intelligent_write_error($keyboard_map_path);
		$km=post_param('keyboard_map');
		if (fwrite($myfile,$km)<strlen($km)) warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));
		fclose($myfile);
		fix_permissions($keyboard_map_path);
		sync_file($keyboard_map_path);

		$adminguide_path=get_file_base().'/adminzone/pages/comcode_custom/'.get_site_default_lang().'/website.txt';
		$adminguide=post_param('adminguide');
		$adminguide=str_replace('__company__',post_param('company_name'),$adminguide);
		$myfile=@fopen($adminguide_path,'wb');
		if ($myfile===false) intelligent_write_error($adminguide_path);
		if (fwrite($myfile,$adminguide)<strlen($adminguide)) warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));
		fclose($myfile);
		fix_permissions($adminguide_path);
		sync_file($adminguide_path);

		$start_path=get_file_base().'/adminzone/pages/comcode_custom/'.get_site_default_lang().'/start.txt';
		if (!file_exists($start_path))
		{
			$start=post_param('start_page');
			$myfile=@fopen($start_path,'wb');
			if ($myfile===false) intelligent_write_error($start_path);
			if (fwrite($myfile,$start)<strlen($start)) warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));
			fclose($myfile);
			fix_permissions($start_path);
			sync_file($start_path);
		}

		if (get_file_base()==get_custom_file_base()) // Only if not a shared install
		{
			$critical_errors=file_get_contents(get_file_base().'/sources/critical_errors.php');
			$critical_errors=str_replace('ocPortal',addslashes(post_param('rebrand_name')),$critical_errors);
			$critical_errors=str_replace('http://ocportal.com',addslashes(post_param('rebrand_base_url')),$critical_errors);
			$critical_errors=str_replace('ocProducts','ocProducts/'.addslashes(post_param('company_name')),$critical_errors);
			$critical_errors_path='sources_custom/critical_errors.php';

			afm_make_file($critical_errors_path,$critical_errors,false);
		}

		$save_header_path=get_file_base().'/themes/'.$GLOBALS['FORUM_DRIVER']->get_theme().'/templates_custom/GLOBAL_HTML_WRAP.tpl';
		$header_path=$save_header_path;
		if (!file_exists($header_path)) $header_path=get_file_base().'/themes/default/templates/GLOBAL_HTML_WRAP.tpl';
		$header_tpl=file_get_contents($header_path);
		$header_tpl=str_replace('Copyright ocProducts Limited','',$header_tpl);
		$myfile=@fopen($save_header_path,'wb');
		if ($myfile===false) intelligent_write_error($save_header_path);
		if (fwrite($myfile,$header_tpl)<strlen($header_tpl)) warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));
		fclose($myfile);
		fix_permissions($save_header_path);
		sync_file($save_header_path);

		if (post_param_integer('churchy',0)==1)
		{
			if (is_object($GLOBALS['FORUM_DB']))
			{
				$GLOBALS['FORUM_DB']->query_delete('f_emoticons',array('e_code'=>':devil:'),'',1);
			} else
			{
				$GLOBALS['SITE_DB']->query_delete('f_emoticons',array('e_code'=>':devil:'),'',1);
			}
		}

		// Make sure some stuff is disabled for non-admin staff
		$staff_groups=$GLOBALS['FORUM_DRIVER']->get_moderator_groups();
		$disallowed_pages=array('admin_setupwizard','admin_addons','admin_backup','admin_errorlog','admin_import','admin_occle','admin_phpinfo','admin_debrand');
		foreach (array_keys($staff_groups) as $id)
		{
			foreach ($disallowed_pages as $page)
			{
				$GLOBALS['SITE_DB']->query_delete('group_page_access',array('page_name'=>$page,'zone_name'=>'adminzone','group_id'=>$id),'',1); // in case already exists
				$GLOBALS['SITE_DB']->query_insert('group_page_access',array('page_name'=>$page,'zone_name'=>'adminzone','group_id'=>$id));
			}
		}

		// Clean up the theme images
		//  background-image
		$theme=$GLOBALS['FORUM_DRIVER']->get_theme();
		find_theme_image('background_image');
		//  logo/*
		if (addon_installed('zone_logos'))
		{
			find_theme_image('logo/adminzone-logo');
			find_theme_image('logo/cms-logo');
			find_theme_image('logo/collaboration-logo');
			$main_logo_url=find_theme_image('logo/-logo',false,true);
			$GLOBALS['SITE_DB']->query_update('theme_images',array('path'=>$main_logo_url),array('id'=>'logo/adminzone-logo','theme'=>$theme),'',1);
			$GLOBALS['SITE_DB']->query_update('theme_images',array('path'=>$main_logo_url),array('id'=>'logo/cms-logo','theme'=>$theme),'',1);
			$GLOBALS['SITE_DB']->query_update('theme_images',array('path'=>$main_logo_url),array('id'=>'logo/collaboration-logo','theme'=>$theme),'',1);
		}

		// Various other icons
		require_code('uploads');
		$path=get_url('','favicon','themes/default/images_custom');
		if ($path[0]!='')
			$GLOBALS['SITE_DB']->query_update('theme_images',array('path'=>$path[0]),array('id'=>'favicon'));
		$path=get_url('','appleicon','themes/default/images_custom');
		if ($path[0]!='')
			$GLOBALS['SITE_DB']->query_update('theme_images',array('path'=>$path[0]),array('id'=>'appleicon'));
		if (addon_installed('ocf_avatars'))
		{
			$path=get_url('','system_avatar','themes/default/images_custom');
			if ($path[0]!='')
				$GLOBALS['SITE_DB']->query_update('theme_images',array('path'=>$path[0]),array('id'=>'ocf_default_avatars/default_set/ocp_fanatic'));
		}

		// Redirect them back to editing screen
		$url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		return redirect_screen($this->title,$url,do_lang_tempcode('SUCCESS'));
	}

}


