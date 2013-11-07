<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		chat
 */

require_code('crud_module');

/**
 * Module page class.
 */
class Module_admin_chat extends standard_crud_module
{
	var $lang_type='CHATROOM';
	var $select_name='NAME';
	var $author='Philip Withnall';
	var $archive_entry_point='_SEARCH:chat';
	var $archive_label='CHAT_LOBBY';
	var $view_entry_point='_SEARCH:chat:type=room:id=_ID';
	var $permission_module='chat';
	var $menu_label='SECTION_CHAT';

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @param  boolean	Whether to check permissions.
	 * @param  ?MEMBER	The member to check permissions as (NULL: current user).
	 * @param  boolean	Whether to allow cross links to other modules (identifiable via a full-pagelink rather than a screen-name).
	 * @return ?array		A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (NULL: disabled).
	 */
	function get_entry_points($check_perms=true,$member_id=NULL,$support_crosslinks=true)
	{
		return array(
			'misc'=>array('MANAGE_CHATROOMS','menu/social/chat'),
			'delete_all'=>array('DELETE_ALL_ROOMS','menu/_generic_admin/delete'),
		)+parent::get_entry_points();
	}

	var $title;

	/**
	 * Standard modular pre-run function, so we know meta-data for <head> before we start streaming output.
	 *
	 * @param  boolean		Whether this is running at the top level, prior to having sub-objects called.
	 * @param  ?ID_TEXT		The screen type to consider for meta-data purposes (NULL: read from environment).
	 * @return ?tempcode		Tempcode indicating some kind of exceptional output (NULL: none).
	 */
	function pre_run($top_level=true,$type=NULL)
	{
		$type=get_param('type','misc');

		require_lang('chat');

		set_helper_panel_tutorial('tut_chat');

		if ($type=='misc')
		{
			$also_url=build_url(array('page'=>'cms_chat'),get_module_zone('cms_chat'));
			attach_message(do_lang_tempcode('menus:ALSO_SEE_CMS',escape_html($also_url->evaluate())),'inform');
		}

		if ($type=='delete_all' || $type=='_delete_all')
		{
			$this->title=get_screen_title('DELETE_ALL_ROOMS');
		}

		return parent::pre_run($top_level);
	}

	/**
	 * Standard crud_module run_start.
	 *
	 * @param  ID_TEXT		The type of module execution
	 * @return tempcode		The output of the run
	 */
	function run_start($type)
	{
		$this->extra_donext_entries=array(
			array('menu/_generic_admin/delete',array('_SELF',array('type'=>'delete_all'),'_SELF'),do_lang('DELETE_ALL_ROOMS')),
		);

		require_code('chat');
		require_code('chat2');
		require_css('chat');

		if ($type=='misc') return $this->misc();
		if ($type=='delete_all') return $this->delete_all();
		if ($type=='_delete_all') return $this->_delete_all();
		return new ocp_tempcode();
	}

	/**
	 * The do-next manager for before content management.
	 *
	 * @return tempcode		The UI
	 */
	function misc()
	{
		$this->add_one_label=do_lang_tempcode('ADD_CHATROOM');
		$this->edit_this_label=do_lang_tempcode('EDIT_THIS_CHATROOM');
		$this->edit_one_label=do_lang_tempcode('EDIT_CHATROOM');

		require_code('templates_donext');
		return do_next_manager(get_screen_title('MANAGE_CHATROOMS'),comcode_lang_string('DOC_CHAT'),
			array(
				array('menu/_generic_admin/add_one',array('_SELF',array('type'=>'ad'),'_SELF'),do_lang('ADD_CHATROOM')),
				array('menu/_generic_admin/edit_one',array('_SELF',array('type'=>'ed'),'_SELF'),do_lang('EDIT_CHATROOM')),
				array('menu/_generic_admin/delete',array('_SELF',array('type'=>'delete_all'),'_SELF'),do_lang('DELETE_ALL_ROOMS')),
			),
			do_lang('MANAGE_CHATROOMS')
		);
	}

	/**
	 * Get tempcode for a adding/editing form.
	 *
	 * @return array			A pair: The input fields, Hidden fields
	 */
	function get_form_fields()
	{
		list($fields,$hidden)=get_chatroom_fields();

		// Permissions
		$fields->attach($this->get_permission_fields(NULL,NULL,true));

		return array($fields,$hidden);
	}

	/**
	 * Standard crud_module list function.
	 *
	 * @return tempcode		The selection list
	 */
	function create_selection_list_entries()
	{
		require_code('chat_lobby');

		$rows=$GLOBALS['SITE_DB']->query_select('chat_rooms',array('*'),array('is_im'=>0),'ORDER BY room_name DESC',500);
		if (count($rows)==500)
		{
			warn_exit(do_lang_tempcode('TOO_MANY_TO_CHOOSE_FROM'));
		}
		$fields=new ocp_tempcode();
		foreach ($rows as $row)
		{
			if (!handle_chatroom_pruning($row))
			{
				$fields->attach(form_input_list_entry(strval($row['id']),false,$row['room_name']));
			}
		}

		return $fields;
	}

	/**
	 * Standard crud_module edit form filler.
	 *
	 * @param  ID_TEXT		The entry being edited
	 * @return array			A pair: The input fields, Hidden fields
	 */
	function fill_in_edit_form($id)
	{
		$rows=$GLOBALS['SITE_DB']->query_select('chat_rooms',array('*'),array('id'=>intval($id)),'',1);
		if (!array_key_exists(0,$rows))
		{
			warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		}
		$row=$rows[0];

		$allow2=$row['allow_list'];
		$allow2_groups=$row['allow_list_groups'];
		$disallow2=$row['disallow_list'];
		$disallow2_groups=$row['disallow_list_groups'];
		$username=$GLOBALS['FORUM_DRIVER']->get_username($row['room_owner']);
		if (is_null($username)) $username='';//do_lang('UNKNOWN');

		list($fields,$hidden)=get_chatroom_fields(intval($id),false,$row['room_name'],get_translated_text($row['c_welcome']),$username,$allow2,$allow2_groups,$disallow2,$disallow2_groups);

		// Permissions
		$fields->attach($this->get_permission_fields($id));

		$delete_fields=new ocp_tempcode();
		$logs_url=build_url(array('page'=>'chat','type'=>'download_logs','id'=>$id),get_module_zone('chat'));
		$delete_fields->attach(form_input_tick(do_lang_tempcode('DELETE'),do_lang_tempcode('DESCRIPTION_DELETE_CHAT_ROOM',escape_html($logs_url->evaluate())),'delete',false));

		return array($fields,$hidden,$delete_fields,NULL,true);
	}

	/**
	 * Standard crud_module add actualiser.
	 *
	 * @return ID_TEXT		The entry added
	 */
	function add_actualisation()
	{
		list($allow2,$allow2_groups,$disallow2,$disallow2_groups)=read_in_chat_perm_fields();

		$meta_data=actual_meta_data_get_fields('chat',NULL);

		$id=add_chatroom(post_param('c_welcome'),post_param('room_name'),$GLOBALS['FORUM_DRIVER']->get_member_from_username(post_param('room_owner')),$allow2,$allow2_groups,$disallow2,$disallow2_groups,post_param('room_lang',user_lang()));

		$this->set_permissions($id);

		if (addon_installed('content_reviews'))
			content_review_set('chat',strval($id));

		return strval($id);
	}

	/**
	 * Standard crud_module edit actualiser.
	 *
	 * @param  ID_TEXT		The entry being edited
	 */
	function edit_actualisation($id)
	{
		$_room_owner=post_param('room_owner',STRING_MAGIC_NULL);
		$room_owner=($_room_owner==STRING_MAGIC_NULL)?INTEGER_MAGIC_NULL:$GLOBALS['FORUM_DRIVER']->get_member_from_username($_room_owner);
		if ($_room_owner!=STRING_MAGIC_NULL)
		{
			list($allow2,$allow2_groups,$disallow2,$disallow2_groups)=read_in_chat_perm_fields();
		} else
		{
			$allow2=STRING_MAGIC_NULL;
			$allow2_groups=STRING_MAGIC_NULL;
			$disallow2=STRING_MAGIC_NULL;
			$disallow2_groups=STRING_MAGIC_NULL;
		}

		$meta_data=actual_meta_data_get_fields('chat',$id);

		edit_chatroom(intval($id),post_param('c_welcome',STRING_MAGIC_NULL),post_param('room_name'),$room_owner,$allow2,$allow2_groups,$disallow2,$disallow2_groups,post_param('room_lang',STRING_MAGIC_NULL));

		$this->set_permissions($id);

		if (addon_installed('content_reviews'))
			content_review_set('chat',$id);
	}

	/**
	 * Standard crud_module delete actualiser.
	 *
	 * @param  ID_TEXT		The entry being deleted
	 */
	function delete_actualisation($id)
	{
		delete_chatroom(intval($id));
	}

	/**
	 * The UI to delete all chat rooms.
	 *
	 * @return tempcode		The UI
	 */
	function delete_all()
	{
		$fields=new ocp_tempcode();
		require_code('form_templates');
		$fields->attach(form_input_tick(do_lang_tempcode('PROCEED'),do_lang_tempcode('Q_SURE'),'continue_delete',false));
		$posting_name=do_lang_tempcode('PROCEED');
		$posting_url=build_url(array('page'=>'_SELF','type'=>'_delete_all'),'_SELF');
		$text=paragraph(do_lang_tempcode('CONFIRM_DELETE_ALL_ROOMS'));
		return do_template('FORM_SCREEN',array('_GUID'=>'fdf02f5b3a3b9ce6d1abaccf0970ed73','SKIP_VALIDATION'=>true,'HIDDEN'=>'','TITLE'=>$this->title,'FIELDS'=>$fields,'SUBMIT_NAME'=>$posting_name,'URL'=>$posting_url,'TEXT'=>$text));
	}

	/**
	 * The actualiser to delete all chat rooms.
	 *
	 * @return tempcode		The UI
	 */
	function _delete_all()
	{
		$delete=post_param_integer('continue_delete',0);
		if ($delete!=1)
		{
			$url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
			return redirect_screen($this->title,$url,do_lang_tempcode('CANCELLED'));
		} else
		{
			delete_all_chatrooms();

			return $this->do_next_manager($this->title,do_lang_tempcode('SUCCESS'),NULL);
		}
	}
}


