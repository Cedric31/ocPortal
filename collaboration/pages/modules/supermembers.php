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
 * @package		supermember_directory
 */

/**
 * Module page class.
 */
class Module_supermembers
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
		$info['update_require_upgrade']=1;
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
		return array('!'=>'SUPER_MEMBERS');
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		delete_config_option('supermembers_groups');
		delete_config_option('supermembers_text');
		delete_config_option('is_on_supermember_filter');
		delete_menu_item_simple('_SEARCH:supermembers');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		if ($upgrade_from==2)
		{
			set_option('supermembers_text','[html]'.get_option('supermembers_text').'[/html]');
			return;
		}

		add_config_option('PAGE_TEXT','supermembers_text','transtext','return do_lang(\'SUPERMEMBERS_TEXT\');','SECURITY','SUPER_MEMBERS');

		require_lang('supermembers');
		add_menu_item_simple('collab_website',NULL,'SUPER_MEMBERS','_SEARCH:supermembers');
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		if (addon_installed('authors')) require_lang('authors');
		if (addon_installed('points')) require_lang('points');
		require_lang('supermembers');

		$title=get_screen_title('SUPER_MEMBERS');

		$message=get_option('supermembers_text');
		if (has_actual_page_access(get_member(),'admin_config'))
		{
			if ($message!='') $message.=' [semihtml]<span class="associated_link"><a href="{$PAGE_LINK*,_SEARCH:admin_config:category:SECURITY#group_SUPER_MEMBERS}">'.do_lang('EDIT').'</a></span>[/semihtml]'; // XHTMLXHTML
		}
		$text=comcode_to_tempcode($message,NULL,true);

		$supermember_groups=collapse_1d_complexity('group_id',$GLOBALS['SITE_DB']->query_select('group_zone_access',array('group_id'),array('zone_name'=>get_zone_name())));
		$supermember_groups=array_merge($supermember_groups,$GLOBALS['FORUM_DRIVER']->get_super_admin_groups());
		$rows=$GLOBALS['FORUM_DRIVER']->member_group_query($supermember_groups,1000);
		if (count($rows)>=1000)
			warn_exit(do_lang_tempcode('TOO_MANY_TO_CHOOSE_FROM'));
		$all_usergroups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list();

		// Calculate
		$groups=new ocp_tempcode();
		$groups_current=new ocp_tempcode();
		$old_group=mixed();
		foreach ($rows as $r)
		{
			$id=$GLOBALS['FORUM_DRIVER']->pname_id($r);
			$current_group=$GLOBALS['FORUM_DRIVER']->pname_group($r);
			$name=$GLOBALS['FORUM_DRIVER']->pname_name($r);

			if (!array_key_exists($current_group,$all_usergroups)) continue;

			if (($current_group!=$old_group) && (!is_null($old_group)))
			{
				$group_name=$all_usergroups[$old_group];
				$groups->attach(do_template('SUPERMEMBERS_SCREEN_GROUP',array('_GUID'=>'32c8427ff18523fcd6b89fb5df365a88','ENTRIES'=>$groups_current,'GROUP_NAME'=>$group_name)));
				$groups_current=new ocp_tempcode();
			}

			if (addon_installed('authors'))
			{
				// Work out their skills from their author profile
				$_skills=$GLOBALS['SITE_DB']->query_value_null_ok('authors','skills',array('forum_handle'=>$id));
				if (is_null($_skills)) $_skills=$GLOBALS['SITE_DB']->query_value_null_ok('authors','skills',array('author'=>$name));
				$skills=(!is_null($_skills))?get_translated_tempcode($_skills):new ocp_tempcode();
			} else $skills=new ocp_tempcode();

			$days=intval(round(floatval(time()-$GLOBALS['FORUM_DRIVER']->pnamelast_visit($r))/(60.0*60.0*24.0)));

			// URL's to them
			if (addon_installed('authors'))
			{
				$author_url=build_url(array('page'=>'authors','type'=>'misc','id'=>$name),get_module_zone('authors'));
			} else $author_url=new ocp_tempcode();
			$points_url=addon_installed('points')?build_url(array('page'=>'points','type'=>'member','id'=>$id),get_module_zone('points')):new ocp_tempcode();
			$pm_url=$GLOBALS['FORUM_DRIVER']->member_pm_url($id);
			$profile_url=$GLOBALS['FORUM_DRIVER']->member_profile_url($id,false,true);

			// Template
			$groups_current->attach(do_template('SUPERMEMBERS_SCREEN_ENTRY',array('_GUID'=>'7fdddfe09a33a36762c281e8993327e3','NAME'=>$name,'DAYS'=>integer_format($days),'PROFILE_URL'=>$profile_url,'AUTHOR_URL'=>$author_url,'POINTS_URL'=>$points_url,'PM_URL'=>$pm_url,'SKILLS'=>$skills)));

			$old_group=$current_group;
		}
		if (!$groups_current->is_empty())
		{
			$group_name=$all_usergroups[$old_group];
			$groups->attach(do_template('SUPERMEMBERS_SCREEN_GROUP',array('_GUID'=>'d2cbe67dafa0dc9872f90fc8834d21ca','ENTRIES'=>$groups_current,'GROUP_NAME'=>$group_name)));
		}

		return do_template('SUPERMEMBERS_SCREEN',array('_GUID'=>'93b875bc00b094810ca9cc3e2f4968b8','TITLE'=>$title,'GROUPS'=>$groups,'TEXT'=>$text));
	}

}


