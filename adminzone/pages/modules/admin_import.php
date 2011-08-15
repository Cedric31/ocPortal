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
 * @package		import
 */

/**
 * Module page class.
 */
class Module_admin_import
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
		$info['version']=5;
		$info['locked']=false;
		$info['update_require_upgrade']=1;
		return $info;
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		$GLOBALS['SITE_DB']->drop_if_exists('import_id_remap');
		$GLOBALS['SITE_DB']->drop_if_exists('import_session');
		$GLOBALS['SITE_DB']->drop_if_exists('import_parts_done');
	}
	
	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		if ((!is_null($upgrade_from)) && ($upgrade_from<5))
		{
			$GLOBALS['SITE_DB']->alter_table_field('import_id_remap','id_old','ID_TEXT');
		}

		if ((is_null($upgrade_from)) || ($upgrade_from<4))
		{
			$GLOBALS['SITE_DB']->create_table('import_parts_done',array(
				'imp_id'=>'*SHORT_TEXT',
				'imp_session'=>'*INTEGER'
			));
	
			$GLOBALS['SITE_DB']->create_table('import_session',array(
				'imp_old_base_dir'=>'SHORT_TEXT',
				'imp_db_name'=>'ID_TEXT',
				'imp_db_user'=>'ID_TEXT',
				'imp_hook'=>'ID_TEXT',
				'imp_db_table_prefix'=>'ID_TEXT',
				'imp_refresh_time'=>'INTEGER',
				'imp_session'=>'*INTEGER'
			));
		}
	
		if (is_null($upgrade_from))
		{
			$usergroups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(false,true);
			foreach (array_keys($usergroups) as $id)
			{
				$GLOBALS['SITE_DB']->query_insert('group_page_access',array('page_name'=>'admin_import','zone_name'=>'adminzone','group_id'=>$id)); // Import very dangerous
			}

			$GLOBALS['SITE_DB']->create_table('import_id_remap',array(
				'id_old'=>'*ID_TEXT',
				'id_new'=>'AUTO_LINK',
				'id_type'=>'*ID_TEXT',
				'id_session'=>'*INTEGER'
			));
		}
	}
	
	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('misc'=>'IMPORT');
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		$GLOBALS['HELPER_PANEL_PIC']='pagepics/importdata';
		$GLOBALS['HELPER_PANEL_TUTORIAL']='tut_importer';

		if (defined('HIPHOP_PHP')) warn_exit(do_lang_tempcode('NO_HIPHOP'));

		if (get_file_base()!=get_custom_file_base()) warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));

		disable_php_memory_limit();

		require_all_lang();
		require_code('import');
		require_code('config2');
		require_code('ocf_moderation_action');
		require_code('ocf_posts_action');
		require_code('ocf_polls_action');
		require_code('ocf_members_action');
		require_code('ocf_groups_action');
		require_code('ocf_general_action');
		require_code('ocf_forums_action');
		require_code('ocf_topics_action');
		require_code('ocf_moderation_action2');
		require_code('ocf_posts_action2');
		require_code('ocf_polls_action2');
		require_code('ocf_members_action2');
		require_code('ocf_groups_action2');
		require_code('ocf_general_action2');
		require_code('ocf_forums_action2');
		require_code('ocf_topics_action2');

		// Decide what we're doing
		$type=get_param('type','misc');

		if ($type=='misc') return $this->choose_importer();
		if ($type=='session') return $this->choose_session();
		if ($type=='session2') return $this->choose_session2();
		if ($type=='hook') return $this->choose_actions();
		if ($type=='import') return $this->do_import();
		/*if ($type=='advanced_hook') return $this->advanced_choose_actions();
		if ($type=='advanced_import') return $this->advanced_do_import();*/
	
		return new ocp_tempcode();
	}

	/**
	 * The UI to choose an importer.
	 *
	 * @return tempcode		The UI
	 */
	function choose_importer()
	{
		$title=get_page_title('IMPORT');
	
		$hooks=new ocp_tempcode();
		$_hooks=find_all_hooks('modules','admin_import');
		require_code('form_templates');
		foreach (array_keys($_hooks) as $hook)
		{
			require_code('hooks/modules/admin_import/'.filter_naughty_harsh($hook));
			if (class_exists('Hook_'.filter_naughty_harsh($hook)))
			{
				$object=object_factory('Hook_'.filter_naughty_harsh($hook),true);
				if (is_null($object)) continue;
				$info=$object->info();
				$hooks->attach(form_input_list_entry($hook,false,$info['product']));
			}
		}
		if ($hooks->is_empty()) warn_exit(do_lang_tempcode('NO_CATEGORIES'));
		$fields=form_input_list(do_lang_tempcode('IMPORTER'),do_lang_tempcode('DESCRIPTION_IMPORTER'),'importer',$hooks,NULL,true);

		$post_url=build_url(array('page'=>'_SELF','type'=>'session'),'_SELF');
	
		breadcrumb_set_self(do_lang_tempcode('IMPORT'));

		return do_template('FORM_SCREEN',array('_GUID'=>'02416e5e9d6cb64248adeb9d2e6f2402','GET'=>true,'HIDDEN'=>'','SKIP_VALIDATION'=>true,'SUBMIT_NAME'=>do_lang_tempcode('PROCEED'),'TITLE'=>$title,'FIELDS'=>$fields,'URL'=>$post_url,'TEXT'=>''));
	}

	/**
	 * The UI to choose an import session.
	 *
	 * @return tempcode		The UI
	 */
	function choose_session()
	{
		$title=get_page_title('IMPORT');

		/* Codes to detect redirect hooks for import */
		$importer=filter_naughty(get_param('importer'));
		require_code('hooks/modules/admin_import/'.filter_naughty_harsh($importer));
		$object=object_factory('Hook_'.filter_naughty_harsh($importer));
		$info=$object->info();

		if(array_key_exists('hook_type',$info))
		{
			$redirect_url=build_url(array('page'=>$info['import_module'],'type'=>$info['import_method_name']),get_module_zone($info['import_module']));
			return redirect_screen($title,$redirect_url,do_lang_tempcode('REDIRECTED_TO_MODULES'));
		}
		/* END */
		
		$sessions=new ocp_tempcode();
		$_sessions=$GLOBALS['SITE_DB']->query_select('import_session',array('*'));
		require_code('form_templates');
		foreach ($_sessions as $session)
		{
			if ($session['imp_session']==get_session_id()) $text=do_lang_tempcode('IMPORT_SESSION_CURRENT',escape_html($session['imp_db_name']));
			else $text=do_lang_tempcode('IMPORT_SESSION_EXISTING_REMAP',escape_html($session['imp_db_name']));
			$sessions->attach(form_input_list_entry(strval($session['imp_session']),false,$text));
		}
		$text=do_lang_tempcode('IMPORT_SESSION_NEW_DELETE');
		$sessions->attach(form_input_list_entry(strval(-1),false,$text));
		$fields=form_input_list(do_lang_tempcode('IMPORT_SESSION'),do_lang_tempcode('DESCRIPTION_IMPORT_SESSION'),'session',$sessions,NULL,true);

		$post_url=build_url(array('page'=>'_SELF','type'=>'session2','importer'=>get_param('importer')),'_SELF');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('IMPORT'))));
		breadcrumb_set_self(do_lang_tempcode('IMPORT_SESSION'));

		return do_template('FORM_SCREEN',array('_GUID'=>'f474980f7263f2def2ff75e7ee40be33','SKIP_VALIDATION'=>true,'HIDDEN'=>form_input_hidden('importer',get_param('importer')),'SUBMIT_NAME'=>do_lang_tempcode('CHOOSE'),'TITLE'=>$title,'FIELDS'=>$fields,'URL'=>$post_url,'TEXT'=>''));
	}

	/**
	 * The UI to choose session details.
	 *
	 * @return tempcode		The UI
	 */
	function choose_session2()
	{
		$title=get_page_title('IMPORT');

		/* Three cases:
			  1) We are continuing (therefore do nothing)
			  2) We are resuming a prior session, after our session changed (therefore remap old session-data to current session)
			  3) We are starting afresh (therefore delete all previous import sessions)
		*/
		$session=either_param_integer('session',get_session_id());
		if ($session==-1)
		{
			// Delete all others
			$GLOBALS['SITE_DB']->query('DELETE FROM '.get_table_prefix().'import_session');
			$GLOBALS['SITE_DB']->query('DELETE FROM '.get_table_prefix().'import_parts_done');
			$GLOBALS['SITE_DB']->query('DELETE FROM '.get_table_prefix().'import_id_remap');

			$session=get_session_id();
		} elseif ($session!=get_session_id())
		{
			// Remap given to current
			$GLOBALS['SITE_DB']->query_update('import_session',array('imp_session'=>get_session_id()),array('imp_session'=>$session),'',1);
			$GLOBALS['SITE_DB']->query_update('import_parts_done',array('imp_session'=>get_session_id()),array('imp_session'=>$session));
			$GLOBALS['SITE_DB']->query_update('import_id_remap',array('id_session'=>get_session_id()),array('id_session'=>$session));
		}

		// Get details from the session row
		$importer=filter_naughty(get_param('importer'));
		require_code('hooks/modules/admin_import/'.filter_naughty_harsh($importer));
		$object=object_factory('Hook_'.filter_naughty_harsh($importer));
		$info=$object->info();

		$session_row=$GLOBALS['SITE_DB']->query_select('import_session',array('*'),array('imp_session'=>get_session_id()),'',1);
		if (array_key_exists(0,$session_row))
		{
			$old_base_dir=$session_row[0]['imp_old_base_dir'];
			$db_name=$session_row[0]['imp_db_name'];
			$db_user=$session_row[0]['imp_db_user'];
			$db_table_prefix=$session_row[0]['imp_db_table_prefix'];
			$refresh_time=$session_row[0]['imp_refresh_time'];
		} else
		{
			$old_base_dir=get_file_base().'/old';
			$db_name=get_db_site();
			$db_user=get_db_site_user();
			$db_table_prefix=array_key_exists('prefix',$info)?$info['prefix']:$GLOBALS['SITE_DB']->get_table_prefix();
			$refresh_time=15;
		}

		// Build the form
		$fields=new ocp_tempcode();
		require_code('form_templates');
		if (!method_exists($object,'probe_db_access'))
		{
			$fields->attach(form_input_line(do_lang_tempcode('DATABASE_NAME'),do_lang_tempcode('_FROM_IMPORTING_SYSTEM'),'db_name',$db_name,true));
			$fields->attach(form_input_line(do_lang_tempcode('DATABASE_USERNAME'),do_lang_tempcode('_FROM_IMPORTING_SYSTEM'),'db_user',$db_user,true));
			$fields->attach(form_input_password(do_lang_tempcode('DATABASE_PASSWORD'),do_lang_tempcode('_FROM_IMPORTING_SYSTEM'),'db_password',false)); // Not required as there may be a blank password
			$fields->attach(form_input_line(do_lang_tempcode('TABLE_PREFIX'),do_lang_tempcode('_FROM_IMPORTING_SYSTEM'),'db_table_prefix',$db_table_prefix,true));
		}
		$fields->attach(form_input_line(do_lang_tempcode('FILE_BASE'),do_lang_tempcode('FROM_IMPORTING_SYSTEM'),'old_base_dir',$old_base_dir,true));
		if (intval(ini_get('safe_mode'))==0)
		{
			$fields->attach(form_input_integer(do_lang_tempcode('REFRESH_TIME'),do_lang_tempcode('DESCRIPTION_REFRESH_TIME'),'refresh_time',$refresh_time,true));
		}
		if (method_exists($object,'get_extra_fields'))
		{
			$fields->attach($object->get_extra_fields());
		}

		$url=build_url(array('page'=>'_SELF','type'=>'hook','session'=>$session,'importer'=>$importer),'_SELF');
		$message=array_key_exists('message',$info)?$info['message']:'';

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('IMPORT')),array('_SELF:_SELF:session',do_lang_tempcode('IMPORT_SESSION'))));

		return do_template('FORM_SCREEN',array('_GUID'=>'15f2c855acf0d365a2e6329bec692dc8','TEXT'=>$message,'TITLE'=>$title,'FIELDS'=>$fields,'URL'=>$url,'HIDDEN'=>'','SUBMIT_NAME'=>do_lang_tempcode('PROCEED')));
	}

	/**
	 * The UI to choose what to import.
	 *
	 * @param  mixed			Output to show from last action (blank: none)
	 * @return tempcode		The UI
	 */
	function choose_actions($extra='')
	{
		$title=get_page_title('IMPORT');

		$session=either_param_integer('session',get_session_id());
		$importer=filter_naughty(get_param('importer'));

		require_code('hooks/modules/admin_import/'.filter_naughty_harsh($importer));
		$object=object_factory('Hook_'.filter_naughty_harsh($importer));

		// Test import source is good
		if (method_exists($object,'probe_db_access'))
		{
			list($db_name,$db_user,$db_password,$db_table_prefix)=$object->probe_db_access(either_param('old_base_dir'));
		} else
		{
			$db_name=either_param('db_name');
			$db_user=either_param('db_user');
			$db_password=either_param('db_password');
			$db_table_prefix=either_param('db_table_prefix');
		}
		if (($db_name==get_db_site()) && ($importer=='ocp_merge') && ($db_table_prefix==$GLOBALS['SITE_DB']->get_table_prefix()))
			warn_exit(do_lang_tempcode('IMPORT_SELF_NO'));
		$import_source=is_null($db_name)?NULL:new database_driver($db_name,get_db_site_host(),$db_user,$db_password,$db_table_prefix);
		unset($import_source);

		$lang_array=array();
		$hooks=find_all_hooks('modules','admin_import_types');
		foreach (array_keys($hooks) as $hook)
		{
			require_code('hooks/modules/admin_import_types/'.filter_naughty_harsh($hook));
			$_hook=object_factory('Hook_admin_import_types_'.filter_naughty_harsh($hook));
			$lang_array+=$_hook->run();
		}

		$info=$object->info();

		$session_row=$GLOBALS['SITE_DB']->query_select('import_session',array('*'),array('imp_session'=>get_session_id()),'',1);
		if (array_key_exists(0,$session_row))
		{
			$old_base_dir=$session_row[0]['imp_old_base_dir'];
			$db_name=$session_row[0]['imp_db_name'];
			$db_user=$session_row[0]['imp_db_user'];
			$db_table_prefix=$session_row[0]['imp_db_table_prefix'];
			$refresh_time=$session_row[0]['imp_refresh_time'];
		} else
		{
			$old_base_dir=get_file_base().'/old';
			$db_name=get_db_site();
			$db_user=get_db_site_user();
			$db_table_prefix=array_key_exists('prefix',$info)?$info['prefix']:$GLOBALS['SITE_DB']->get_table_prefix();
			$refresh_time=15;
		}

		$_import_list=$info['import'];
		$_import_list_2=array();
		foreach ($_import_list as $import)
		{
			if (is_null($import)) continue;
			if (!array_key_exists($import,$lang_array)) // Shouldn't happen, but a failsafe
			{
				$lang_array[$import]=$import;
			}
			if (is_null($lang_array[$import])) continue;

			$text=do_lang((strtolower($lang_array[$import])!=$lang_array[$import])?$lang_array[$import]:strtoupper($lang_array[$import]));
			$_import_list_2[$import]=$text;
		}
		if ((array_key_exists('ocf_members',$_import_list_2)) && (get_forum_type()==$importer) && ($db_name==get_db_forums()) && ($db_table_prefix==$GLOBALS['FORUM_DB']->get_table_prefix()))
		{
			$_import_list_2['ocf_switch']=do_lang_tempcode('SWITCH_TO_OCF');
		}
		$import_list=new ocp_tempcode();
	//	asort($_import_list_2); Let's preserve order here
		$just=get_param('just',NULL);
		$first=true;
		$skip_hidden=array();
		$parts_done=collapse_2d_complexity('imp_id','imp_session',$GLOBALS['SITE_DB']->query_select('import_parts_done',array('imp_id','imp_session'),array('imp_session'=>get_session_id())));
		foreach ($_import_list_2 as $import=>$text)
		{
			if (array_key_exists($import,$parts_done))
			{
				$import_list->attach(do_template('IMPORT_ACTION_LINE',array('CHECKED'=>false,'DISABLED'=>true,'NAME'=>'import_'.$import,'TEXT'=>$text,'ADVANCED_URL'=>$info['supports_advanced_import']?build_url(array('page'=>'_SELF','type'=>'advanced_hook','session'=>$session,'content_type'=>$import,'importer'=>$importer),'_SELF'):new ocp_tempcode())));
			} else
			{
				$checked=(is_null($just)) && ($first);
				$import_list->attach(do_template('IMPORT_ACTION_LINE',array('_GUID'=>'f2215115f920200a0a1ba6bc776ad945','CHECKED'=>$checked,'NAME'=>'import_'.$import,'TEXT'=>$text,'ADVANCED_URL'=>$info['supports_advanced_import']?build_url(array('page'=>'_SELF','type'=>'advanced_hook','session'=>$session,'content_type'=>$import,'importer'=>$importer),'_SELF'):new ocp_tempcode())));
			}
			if ($just==$import)
			{
				$first=true;
				$just=NULL;
			} else $first=false;
			
			$skip_hidden[]='import_'.$import;
		}

		$message=array_key_exists('message',$info)?$info['message']:'';

		if (count($parts_done)==count($_import_list_2))
		{
			inform_exit(do_lang_tempcode(($message==='')?'_IMPORT_ALL_FINISHED':'IMPORT_ALL_FINISHED',$message));
		}

		$url=build_url(array('page'=>'_SELF','type'=>'import','session'=>$session,'importer'=>$importer),'_SELF');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('IMPORT')),array('_SELF:_SELF:session:importer='.$importer,do_lang_tempcode('IMPORT_SESSION'))));

		$hidden=new ocp_tempcode();
		$hidden->attach(build_keep_post_fields($skip_hidden));
		$hidden->attach(build_keep_form_fields('',true));

		return do_template('IMPORT_ACTION_SCREEN',array('_GUID'=>'a3a69637e541923ad76e9e7e6ec7e1af','EXTRA'=>$extra,'MESSAGE'=>$message,'TITLE'=>$title,'FIELDS'=>'','HIDDEN'=>$hidden,'IMPORTER'=>$importer,'IMPORT_LIST'=>$import_list,'URL'=>$url));
	}

	/* *
	 * The UI to choose options for an advanced import.
	 *
	 * @return tempcode		The UI
	 */
	/*function advanced_choose_actions()
	{
		$title=get_page_title('IMPORT');

		$session=either_param_integer('session',get_session_id());
		$importer=filter_naughty(get_param('importer'));
		$content_type=filter_naughty(either_param('content_type'));

		// Get the data from the content type and importer hooks
		require_code('hooks/modules/admin_import_types/'.filter_naughty_harsh($content_type));
		$content_type_object=object_factory('Hook_admin_import_types_'.filter_naughty_harsh($content_type));
		$lang=$content_type_object->run();

		require_code('hooks/modules/admin_import/'.filter_naughty_harsh($importer));
		$importer_object=object_factory('Hook_'.filter_naughty_harsh($importer));
		$info=$importer_object->info();

		// Build up the advanced import form
		$fields=new ocp_tempcode();
		require_code('form_templates');

		// Selector for the content to import
		$javascript='standardAlternateFields(\'import_all\',\'import_items\',NULL,false);';
		$fields->attach(form_input_tick(do_lang_tempcode('IMPORT_ALL'),do_lang_tempcode('DESCRIPTION_IMPORT_ALL'),'import_all',true));
		$fields->attach($importer_object->get_import_items_selector($content_type)); // Returns a form field called import_items

		// Options for what to do with the imported content (if it's hierarchical)
		if (in_array($content_type,$info['hierarchical']))
		{
			$radio_entries=new ocp_tempcode();
			$radio_entries->attach(form_input_radio_entry('import_position','specific_position',true,do_lang_tempcode('IMPORT_TO_SPECIFIC_POSITION')));
			$radio_entries->attach(form_input_radio_entry('import_position','match_or_specific_position',false,do_lang_tempcode('IMPORT_MATCH_OR_SPECIFIC_POSITION')));
			$radio_entries->attach(form_input_radio_entry('import_position','match_or_skip',false,do_lang_tempcode('IMPORT_MATCH_OR_SKIP')));
			$radio_entries->attach(form_input_radio_entry('import_position','match_or_warn',false,do_lang_tempcode('IMPORT_MATCH_OR_WARN')));
			$fields->attach(form_input_radio(do_lang_tempcode('IMPORT_POSITION'),do_lang_tempcode('DESCRIPTION_IMPORT_POSITION'),$radio_entries,true));
		}

		// Options for replacing/overwriting content
		$radio_entries=new ocp_tempcode();
		$radio_entries->attach(form_input_radio_entry('import_replace','replace',true,do_lang_tempcode('IMPORT_REPLACE_OVERWRITE')));
		$radio_entries->attach(form_input_radio_entry('import_replace','skip',false,do_lang_tempcode('IMPORT_REPLACE_SKIP')));
		$fields->attach(form_input_radio(do_lang_tempcode('IMPORT_REPLACE'),do_lang_tempcode('DESCRIPTION_IMPORT_REPLACE'),$radio_entries,true));

		$url=build_url(array('page'=>'_SELF','type'=>'advanced_import','session'=>$session),'_SELF');
		$message=array_key_exists('message',$info)?$info['message']:'';

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('IMPORT')),array('_SELF:_SELF:session:importer='.$importer,do_lang_tempcode('IMPORT_SESSION'))));

		return do_template('FORM_SCREEN',array('_GUID'=>'07848c5a99cc6fe38650b4904091af79','TEXT'=>$message,'TITLE'=>$title,'FIELDS'=>$fields,'URL'=>$url,'HIDDEN'=>build_keep_post_fields(),'SUBMIT_NAME'=>do_lang_tempcode('IMPORT')));
	}*/

	/* *
	 * The actualiser to do an advanced import.
	 *
	 * @return tempcode		The UI
	 */
	/*function advanced_do_import()
	{

	}*/

	/**
	 * The actualiser to do an import.
	 *
	 * @return tempcode		The UI
	 */
	function do_import()
	{
		$refresh_url=get_self_url(true,false,array('type'=>'import'),true);
		$refresh_time=either_param_integer('refresh_time',15); // Shouldn't default, but reported on some systems to do so
		if (function_exists('set_time_limit')) @set_time_limit($refresh_time);
		header('Content-type: text/html');
		global $I_REFRESH_URL;
		$I_REFRESH_URL=$refresh_url;

		require_code('database_action');

		$title=get_page_title('IMPORT');

		$importer=get_param('importer');
		require_code('hooks/modules/admin_import/'.filter_naughty_harsh($importer));
		$object=object_factory('Hook_'.filter_naughty_harsh($importer));

		// Get data
		$old_base_dir=either_param('old_base_dir');
		if ((method_exists($object,'verify_base_path')) && (!$object->verify_base_path($old_base_dir)))
			warn_exit(do_lang_tempcode('BAD_IMPORT_PATH',escape_html($old_base_dir)));
		if (method_exists($object,'probe_db_access'))
		{
			list($db_name,$db_user,$db_password,$db_table_prefix)=$object->probe_db_access(either_param('old_base_dir'));
		} else
		{
			$db_name=either_param('db_name');
			$db_user=either_param('db_user');
			$db_password=either_param('db_password');
			$db_table_prefix=either_param('db_table_prefix');
		}
		if (($db_name==get_db_site()) && ($importer=='ocp_merge') && ($db_table_prefix==$GLOBALS['SITE_DB']->get_table_prefix()))
			warn_exit(do_lang_tempcode('IMPORT_SELF_NO'));

		$import_source=is_null($db_name)?NULL:new database_driver($db_name,get_db_site_host(),$db_user,$db_password,$db_table_prefix);

		// Some preliminary tests
		$happy=get_param_integer('happy',0);
		if ((method_exists($object,'pre_import_tests')) && ($happy==0))
		{
			$ui=$object->pre_import_tests($import_source,$db_table_prefix,$old_base_dir);
			if (!is_null($ui))
			{
				return $ui;
			}
		}

		// Save data
		$GLOBALS['SITE_DB']->query_delete('import_session',array('imp_session'=>get_session_id()),'',1);
		$GLOBALS['SITE_DB']->query_insert('import_session',array(
			'imp_hook'=>'',
			'imp_old_base_dir'=>$old_base_dir,
			'imp_db_name'=>is_null($db_name)?'':$db_name,
			'imp_db_user'=>is_null($db_user)?'':$db_user,
			'imp_db_table_prefix'=>is_null($db_table_prefix)?'':$db_table_prefix,
			'imp_refresh_time'=>$refresh_time,
			'imp_session'=>get_session_id()
		));
	
		$info=$object->info();
		$_import_list=$info['import'];
		$out=new ocp_tempcode();
		$parts_done=collapse_2d_complexity('imp_id','imp_session',$GLOBALS['SITE_DB']->query_select('import_parts_done',array('imp_id','imp_session'),array('imp_session'=>get_session_id())));
		$import_last='-1';
		if (get_forum_type()!='ocf')
		{
			require_code('forum/ocf');
			$GLOBALS['OCF_DRIVER']=new forum_driver_ocf();
			$GLOBALS['OCF_DRIVER']->connection=$GLOBALS['SITE_DB'];
			$GLOBALS['OCF_DRIVER']->MEMBER_ROWS_CACHED=array();
		}
		$_import_list[]='ocf_switch';
		$all_skipped=true;

		foreach ($_import_list as $import)
		{
			$import_this=either_param_integer('import_'.$import,0);
			if ($import_this==1)
			{
				$dependency=NULL;
				if ((array_key_exists('dependencies',$info)) && (array_key_exists($import,$info['dependencies'])))
				{
					foreach ($info['dependencies'][$import] as $_dependency)
					{
						if (!array_key_exists($_dependency,$parts_done))
						{
							$dependency=$_dependency;
						}
					}
				}
				if (is_null($dependency))
				{
					if ($import=='ocf_switch')
					{
						$out->attach($this->ocf_switch());
					} else
					{
						$function_name='import_'.$import;
						ocf_over_local();
						$func_output=call_user_func_array(array($object,$function_name),array($import_source,$db_table_prefix,$old_base_dir));
						if (!is_null($func_output)) $out->attach($func_output);
						ocf_over_msn();
					}
					$parts_done[$import]=get_session_id();

					$import_last=$import;
					$all_skipped=false;

					$GLOBALS['SITE_DB']->query_delete('import_parts_done',array('imp_id'=>$import,'imp_session'=>get_session_id()),'',1);
					$GLOBALS['SITE_DB']->query_insert('import_parts_done',array('imp_id'=>$import,'imp_session'=>get_session_id()));
				} else
				{
					$out->attach(do_template('IMPORT_MESSAGE',array('_GUID'=>'b2a853f5fb93beada51a3eb8fbd1575f','MESSAGE'=>do_lang_tempcode('IMPORT_OF_SKIPPED',escape_html($import),escape_html($dependency)))));
				}
			}
		}
		if (!$all_skipped)
		{
			$lang_code='SUCCESS';
			if (count($GLOBALS['ATTACHED_MESSAGES_RAW'])!=0) $lang_code='SOME_ERRORS_OCCURED';
			$out->attach(do_template('IMPORT_MESSAGE',array('_GUID'=>'4c4860d021814ffd1df6e21e712c7b44','MESSAGE'=>do_lang_tempcode($lang_code))));
		}

		log_it('IMPORT');
		// Quick and simple decacheing. No need to be smart about this.
		delete_value('ocf_member_count');
		delete_value('ocf_topic_count');
		delete_value('ocf_post_count');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('IMPORT')),array('_SELF:_SELF:session',do_lang_tempcode('IMPORT_SESSION')),array('_SELF:_SELF:hook:importer='.$importer.':session='.get_param('session'),do_lang_tempcode('IMPORT'))));
		breadcrumb_set_self(do_lang_tempcode('START'));

		$back_url=build_url(array('page'=>'_SELF','type'=>'hook','importer'=>get_param('importer'),'just'=>$import_last),'_SELF');
		$_GET['just']=$import_last;
		return $this->choose_actions($out);
	}


	/**
	 * Special import-esque function to aid switching to OCF after importing forum previously served by a forum driver.
	 *
	 * @return tempcode	Information about progress
	 */
	function ocf_switch()
	{
		$out=new ocp_tempcode();

		$todos=array('USER'=>array('member',db_get_first_id(),NULL),'GROUP'=>array('group',NULL,'group_id'));
		foreach ($todos as $db_abstraction=>$definition)
		{
			list($import_code,$default_id,$field_name_also)=$definition;

			$count=0;

			$extra=is_null($field_name_also)?'':(' OR '.db_string_equal_to('m_name',$field_name_also));
			$fields=$GLOBALS['SITE_DB']->query('SELECT m_table,m_name FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'db_meta WHERE (NOT (m_table LIKE \''.db_encode_like('f_%').'\')) AND ('.db_string_equal_to('m_type',$db_abstraction).' OR '.db_string_equal_to('m_type','*'.$db_abstraction).' OR '.db_string_equal_to('m_type','?'.$db_abstraction).$extra.')');
			foreach ($fields as $field)
			{
				if ($field['m_table']=='stats') continue; // Lots of data and it's not important

				//echo '(working) '.$field['m_table'].'/'.$field['m_name'].'<br />';

				$values=$GLOBALS['SITE_DB']->query_select($field['m_table'],array('*'));
				foreach ($values as $value)
				{
					$current=$value[$field['m_name']];
					$remapped=import_id_remap_get($import_code,$current,true);
					if (is_null($remapped)) $remapped=$default_id;

					if (!is_null($remapped))
					{
						$value2=$value;
						$value2[$field['m_name']]=-$remapped;
						$c=$GLOBALS['SITE_DB']->query_update($field['m_table'],$value2,$value,'',NULL,NULL,true,true);
						if (is_null($c)) // Something went wrong apparently- but we still need to clean up
						{
							$GLOBALS['SITE_DB']->query_delete($field['m_table'],$value);
						} else $count+=$c;
					} else
					{
						$GLOBALS['SITE_DB']->query_delete($field['m_table'],$value);
					}
				}
				$GLOBALS['SITE_DB']->query('UPDATE '.$GLOBALS['SITE_DB']->get_table_prefix().$field['m_table'].' SET '.$field['m_name'].'=-'.$field['m_name'].' WHERE '.$field['m_name'].'<0');
			}
			
			$out->attach(paragraph(do_lang_tempcode('OCF_CONVERTED_'.$db_abstraction,($count==0)?'?':strval($count))));
		}

		// info.php
		global $FILE_BASE;
		$info_file=((file_exists('use_comp_name'))?(array_key_exists('COMPUTERNAME',$_ENV)?$_ENV['COMPUTERNAME']:$_SERVER['SERVER_NAME']):'info').'.php';
		$info=fopen($FILE_BASE.'/'.$info_file,'wt');
		fwrite($info,"<"."?php\n");
		global $SITE_INFO;
		$SITE_INFO['forum_type']='ocf';
		$SITE_INFO['ocf_table_prefix']=$SITE_INFO['table_prefix'];
		$SITE_INFO['db_forums']=$SITE_INFO['db_site'];
		$SITE_INFO['db_forums_host']=array_key_exists('db_site_host',$SITE_INFO)?$SITE_INFO['db_site_host']:'localhost';
		$SITE_INFO['db_forums_user']=$SITE_INFO['db_site_user'];
		$SITE_INFO['db_forums_password']=$SITE_INFO['db_site_password'];
		$SITE_INFO['board_prefix']=get_base_url();
		foreach ($SITE_INFO as $key=>$val)
		{
			$_val=str_replace('\\','\\\\',$val);
			fwrite($info,'$SITE_INFO[\''.$key.'\']=\''.$_val."';\n");
		}
		fwrite($info,"?".">\n");
		fclose($info);
		fix_permissions($FILE_BASE.'/'.$info_file);
		sync_file($FILE_BASE.'/'.$info_file);
		$out->attach(paragraph(do_lang_tempcode('OCF_CONVERTED_INFO')));

		$LANG=get_site_default_lang();
		$trans5=insert_lang(do_lang('FORUM'),1,NULL,false,NULL,$LANG);
		$trans7=insert_lang(do_lang('PERSONAL_ZONE'),1,NULL,false,NULL,$LANG);
		$GLOBALS['SITE_DB']->query_insert('zones',array('zone_name'=>'forum','zone_title'=>insert_lang(do_lang('SECTION_FORUMS'),1),'zone_default_page'=>'forumview','zone_header_text'=>$trans5,'zone_theme'=>'-1','zone_wide'=>NULL,'zone_require_session'=>0,'zone_displayed_in_menu'=>1));
		$GLOBALS['SITE_DB']->query_insert('zones',array('zone_name'=>'personalzone','zone_title'=>insert_lang(do_lang('PERSONAL_ZONE'),1),'zone_default_page'=>'myhome','zone_header_text'=>$trans7,'zone_theme'=>'-1','zone_wide'=>0,'zone_require_session'=>1,'zone_displayed_in_menu'=>1));
		require_code('menus2');
		add_menu_item_simple('zone_menu',NULL,'SECTION_FORUMS','forum'.':forumview',0,1);
		add_menu_item_simple('zone_menu',NULL,'PERSONAL_ZONE','personalzone'.':myhome',0,1);

		return $out;
	}

}


