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
 * @package		welcome_emails
 */

require_code('aed_module');

/**
 * Module page class.
 */
class Module_admin_ocf_welcome_emails extends standard_aed_module
{
	var $lang_type='WELCOME_EMAIL';
	var $select_name='SUBJECT';
	var $select_name_description='DESCRIPTION_WELCOME_EMAIL_SUBJECT';
	var $menu_label='WELCOME_EMAILS';
	var $orderer='w_name';

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
		$info['version']=3;
		$info['locked']=true;
		$info['update_require_upgrade']=1;
		return $info;
	}
	
	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		$GLOBALS['NO_DB_SCOPE_CHECK']=true;
		$GLOBALS['SITE_DB']->drop_if_exists('f_welcome_emails');
		$GLOBALS['NO_DB_SCOPE_CHECK']=false;
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		$GLOBALS['NO_DB_SCOPE_CHECK']=true;

		if ((!is_null($upgrade_from)) && ($upgrade_from<3))
		{
			$GLOBALS['SITE_DB']->add_table_field('f_welcome_emails','w_newsletter','BINARY',0);
			$GLOBALS['SITE_DB']->add_table_field('f_welcome_emails','w_name','SHORT_TEXT');
			$welcome_mails=$GLOBALS['SITE_DB']->query_select('f_welcome_emails',array('id','w_subject'));
			foreach ($welcome_mails as $row)
			{
				$GLOBALS['SITE_DB']->query_update('f_welcome_emails',array('w_name'=>$row['w_subject']),array('id'=>$row['id']),'',1);
			}
		}

		if (is_null($upgrade_from))
		{
			$GLOBALS['SITE_DB']->create_table('f_welcome_emails',array(
				'id'=>'*AUTO',
				'w_name'=>'SHORT_TEXT',
				'w_subject'=>'SHORT_TRANS',
				'w_text'=>'LONG_TRANS',
				'w_send_time'=>'INTEGER',
				'w_newsletter'=>'BINARY'
			));
		}

		$GLOBALS['NO_DB_SCOPE_CHECK']=false;
	}
	
	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array_merge(array('misc'=>'WELCOME_EMAILS'),parent::get_entry_points());
	}
	
	/**
	 * Standard aed_module run_start.
	 *
	 * @param  ID_TEXT		The type of module execution
	 * @return tempcode		The output of the run
	 */
	function run_start($type)
	{
		$GLOBALS['NO_DB_SCOPE_CHECK']=true;

		breadcrumb_set_parents(array(array('_SEARCH:admin_ocf_join:menu',do_lang_tempcode('MEMBERS'))));

		require_lang('ocf_welcome_emails');

		$GLOBALS['HELPER_PANEL_PIC']='pagepics/welcome_emails';
		$GLOBALS['HELPER_PANEL_TUTORIAL']='tut_members';
		$GLOBALS['HELPER_PANEL_TEXT']=comcode_lang_string('DOC_WELCOME_EMAIL_PREVIEW');

		require_code('ocf_general_action');
		require_code('ocf_general_action2');
		require_lang('ocf_welcome_emails');

		ocf_require_all_forum_stuff();

		$this->add_one_label=do_lang_tempcode('ADD_WELCOME_EMAIL');
		$this->edit_this_label=do_lang_tempcode('EDIT_THIS_WELCOME_EMAIL');
		$this->edit_one_label=do_lang_tempcode('EDIT_WELCOME_EMAIL');

		if ($type=='misc') return $this->misc();
		return new ocp_tempcode();
	}

	/**
	 * The do-next manager for before content management.
	 *
	 * @return tempcode		The UI
	 */
	function misc()
	{
		if (!cron_installed()) attach_message(do_lang_tempcode('CRON_NEEDED_TO_WORK',escape_html(brand_base_url().'/docs'.strval(ocp_version()).'/pg/tut_configuration')),'warn');
		
		require_code('templates_donext');
		return do_next_manager(get_page_title('WELCOME_EMAILS'),comcode_lang_string('DOC_WELCOME_EMAILS'),
					array(
						/*	 type							  page	 params													 zone	  */
						array('add_one',array('_SELF',array('type'=>'ad'),'_SELF'),do_lang('ADD_WELCOME_EMAIL')),
						array('edit_one',array('_SELF',array('type'=>'ed'),'_SELF'),do_lang('EDIT_WELCOME_EMAIL')),
					),
					do_lang('WELCOME_EMAILS')
		);
	}

	/**
	 * Get tempcode for adding/editing form.
	 *
	 * @param  SHORT_TEXT	A name for the Welcome E-mail
	 * @param  SHORT_TEXT	The subject of the Welcome E-mail
	 * @param  LONG_TEXT		The message body of the Welcome E-mail
	 * @param  integer		The number of hours before sending the e-mail
	 * @param  ?AUTO_LINK	What newsletter to send out to instead of members (NULL: none)
	 * @return tempcode		The input fields
	 */
	function get_form_fields($name='',$subject='',$text='',$send_time=0,$newsletter=0)
	{
		$fields=new ocp_tempcode();
		$fields->attach(form_input_line(do_lang_tempcode('NAME'),do_lang_tempcode('DESCRIPTION_NAME_REFERENCE'),'name',$name,true));
		$fields->attach(form_input_line(do_lang_tempcode('SUBJECT'),do_lang_tempcode('DESCRIPTION_WELCOME_EMAIL_SUBJECT'),'subject',$subject,true));
		$fields->attach(form_input_huge_comcode(do_lang_tempcode('TEXT'),do_lang_tempcode('DESCRIPTION_WELCOME_EMAIL_TEXT'),'text',$text,true));
		$fields->attach(form_input_integer(do_lang_tempcode('SEND_TIME'),do_lang_tempcode('DESCRIPTION_SEND_TIME'),'send_time',$send_time,true));
		if (addon_installed('newsletter'))
		{
			require_lang('newsletter');
			if (get_value('welcome_nw_choice')==='1')
			{
				$newsletters=new ocp_tempcode();
				$rows=$GLOBALS['SITE_DB']->query_select('newsletters',array('id','title'));
				if (get_forum_type()=='ocf')
				{
					$newsletters->attach(form_input_list_entry('',is_null($newsletter),do_lang_tempcode('NEWSLETTER_OCF')));
				}
				foreach ($rows as $_newsletter)
					$newsletters->attach(form_input_list_entry(strval($_newsletter['id']),$_newsletter['id']===$newsletter,get_translated_text($_newsletter['title'])));
				if (!$newsletters->is_empty())
					$fields->attach(form_input_list(do_lang_tempcode('NEWSLETTER'),'','newsletter',$newsletters,NULL,false,false));
			} else
			{
				if (get_forum_type()=='ocf') // If you are not using OCF, it is IMPLIED you will have to be sending to newsletter people, hence no choice
				{
					require_lang('newsletter');
					$fields->attach(form_input_tick(do_lang_tempcode('NEWSLETTER'),do_lang_tempcode('DESCRIPTION_NEWSLETTER_INSTEAD'),'newsletter',$newsletter==1));
				}
			}
		}

		return $fields;
	}

	/**
	 * Standard aed_module table function.
	 *
	 * @param  array			Details to go to build_url for link to the next screen.
	 * @return array			A pair: The choose table, Whether re-ordering is supported from this screen.
	 */
	function nice_get_choose_table($url_map)
	{
		$table=new ocp_tempcode();
		
		require_code('templates_results_table');
		
		$current_ordering=get_param('sort','w_name ASC');
		list($sortable,$sort_order)=explode(' ',$current_ordering,2);
		$sortables=array(
			'w_name'=>do_lang_tempcode('NAME'),
			'w_subject'=>do_lang_tempcode('SUBJECT'),
			'w_send_time'=>do_lang_tempcode('SEND_TIME'),
		);
		if (((strtoupper($sort_order)!='ASC') && (strtoupper($sort_order)!='DESC')) || (!array_key_exists($sortable,$sortables)))
			log_hack_attack_and_exit('ORDERBY_HACK');
		global $NON_CANONICAL_PARAMS;
		$NON_CANONICAL_PARAMS[]='sort';

		$header_row=results_field_title(array(
			do_lang_tempcode('NAME'),
			do_lang_tempcode('SUBJECT'),
			do_lang_tempcode('SEND_TIME'),
			do_lang_tempcode('ACTIONS'),
		),$sortables,'sort',$sortable.' '.$sort_order);

		$fields=new ocp_tempcode();

		require_code('form_templates');
		list($rows,$max_rows)=$this->get_entry_rows(false,$current_ordering);
		foreach ($rows as $row)
		{
			$edit_link=build_url($url_map+array('id'=>$row['id']),'_SELF');

			$fields->attach(results_entry(array($row['w_name'],get_translated_text($row['w_subject']),do_lang_tempcode('HOURS',escape_html(strval($row['w_send_time']))),protect_from_escaping(hyperlink($edit_link,do_lang_tempcode('EDIT'),false,true,'#'.strval($row['id']))))),true);
		}
		
		return array(results_table(do_lang($this->menu_label),get_param_integer('start',0),'start',get_param_integer('max',300),'max',$max_rows,$header_row,$fields,$sortables,$sortable,$sort_order),false);
	}

	/**
	 * Standard aed_module list function.
	 *
	 * @return tempcode		The selection list
	 */
	function nice_get_entries()
	{
		$_m=$GLOBALS['SITE_DB']->query_select('f_welcome_emails',array('*'));
		$entries=new ocp_tempcode();
		foreach ($_m as $m)
		{
			$entries->attach(form_input_list_entry(strval($m['id']),false,$m['w_name']));
		}

		return $entries;
	}

	/**
	 * Standard aed_module edit form filler.
	 *
	 * @param  ID_TEXT		The entry being edited
	 * @return tempcode		The edit form
	 */
	function fill_in_edit_form($id)
	{
		$m=$GLOBALS['SITE_DB']->query_select('f_welcome_emails',array('*'),array('id'=>intval($id)),'',1);
		if (!array_key_exists(0,$m)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		$r=$m[0];

		$fields=$this->get_form_fields($r['w_name'],get_translated_text($r['w_subject']),get_translated_text($r['w_text']),$r['w_send_time'],$r['w_newsletter']);

		return $fields;
	}

	/**
	 * Standard aed_module add actualiser.
	 *
	 * @return ID_TEXT		The entry added
	 */
	function add_actualisation()
	{
		$name=post_param('name');
		$subject=post_param('subject');
		$text=post_param('text');
		$send_time=post_param_integer('send_time');
		if (get_value('welcome_nw_choice')==='1')
		{
			$newsletter=post_param_integer('newsletter',NULL);
		} else
		{
			$newsletter=post_param_integer('newsletter',0);
		}
		$id=ocf_make_welcome_email($name,$subject,$text,$send_time,$newsletter);
		return strval($id);
	}

	/**
	 * Standard aed_module edit actualiser.
	 *
	 * @param  ID_TEXT		The entry being edited
	 */
	function edit_actualisation($id)
	{
		$name=post_param('name');
		$subject=post_param('subject');
		$text=post_param('text');
		$send_time=post_param_integer('send_time');
		if (get_value('welcome_nw_choice')==='1')
		{
			$newsletter=post_param_integer('newsletter',NULL);
		} else
		{
			$newsletter=post_param_integer('newsletter',0);
		}
		ocf_edit_welcome_email(intval($id),$name,$subject,$text,$send_time,$newsletter);
	}

	/**
	 * Standard aed_module delete actualiser.
	 *
	 * @param  ID_TEXT		The entry being deleted
	 */
	function delete_actualisation($id)
	{
		ocf_delete_welcome_email(intval($id));
	}
}


