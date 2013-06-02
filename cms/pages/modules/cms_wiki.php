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
 * @package		wiki
 */

/**
 * Module page class.
 */
class Module_cms_wiki
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
		$info['version']=4;
		$info['locked']=false;
		$info['update_require_upgrade']=1;
		return $info;
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('add_page'=>'WIKI_ADD_PAGE');
	}

	/**
	 * Standard modular privilege-override finder function.
	 *
	 * @return array	A map of privileges that are overridable; privilege to 0 or 1. 0 means "not category overridable". 1 means "category overridable".
	 */
	function get_privilege_overrides()
	{
		require_lang('wiki');
		return array('edit_cat_lowrange_content'=>array(1,'WIKI_EDIT_PAGE'),'delete_cat_lowrange_content'=>array(1,'WIKI_DELETE_PAGE'),'submit_lowrange_content'=>array(1,'WIKI_MAKE_POST'),'bypass_validation_lowrange_content'=>array(1,'BYPASS_WIKI_VALIDATION'),'edit_own_lowrange_content'=>array(1,'WIKI_EDIT_OWN_POST'),'edit_lowrange_content'=>array(1,'WIKI_EDIT_POST'),'delete_own_lowrange_content'=>array(1,'WIKI_DELETE_OWN_POST'),'delete_lowrange_content'=>array(1,'WIKI_DELETE_POST'),'wiki_manage_tree'=>1);
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		$type=get_param('type','misc');

		set_helper_panel_pic('pagepics/wiki');
		set_helper_panel_tutorial('tut_wiki');

		require_code('wiki');
		require_lang('wiki');
		require_css('wiki');

		// Decide what to do
		if ($type=='misc') return $this->misc();
		if ($type=='choose_page_to_edit') return $this->choose_page_to_edit();
		if ($type=='add_page') return $this->add_page();
		if ($type=='_add_page') return $this->_add_page();
		if ($type=='edit_page') return $this->edit_page();
		if ($type=='_edit_page') return $this->_edit_page();
		if ($type=='edit_tree') return $this->edit_tree();
		if ($type=='_edit_tree') return $this->_edit_tree();

		return new ocp_tempcode();
	}

	/**
	 * The do-next manager for before content management.
	 *
	 * @return tempcode		The UI
	 */
	function misc()
	{
		require_code('templates_donext');
		require_code('fields');
		return do_next_manager(get_screen_title('MANAGE_WIKI'),comcode_lang_string('DOC_WIKI'),
					array_merge(array(
						/*	 type							  page	 params													 zone	  */
						array('add_one',array('_SELF',array('type'=>'add_page'),'_SELF'),do_lang('WIKI_ADD_PAGE')),
						array('edit_one',array('_SELF',array('type'=>'choose_page_to_edit'),'_SELF'),do_lang('WIKI_EDIT_PAGE')),
					),manage_custom_fields_donext_link('wiki_post'),manage_custom_fields_donext_link('wiki_page')),
					do_lang('MANAGE_WIKI')
		);
	}

	/**
	 * Get the fields for adding/editing a Wiki+ page.
	 *
	 * @param  ?AUTO_LINK	The page ID (NULL: new)
	 * @param  SHORT_TEXT	The page title
	 * @param  LONG_TEXT		Hidden notes pertaining to the page
	 * @param  BINARY			Whether to hide the posts on the page by default
	 * @param  AUTO_LINK		The ID of the page (-1 implies we're adding)
	 * @return array			The fields, the extra fields, the hidden fields.
	 */
	function get_page_fields($id=NULL,$title='',$notes='',$hide_posts=0,$page_id=-1)
	{
		$fields=new ocp_tempcode();
		$fields2=new ocp_tempcode();
		$hidden=new ocp_tempcode();

		require_code('form_templates');
		$fields->attach(form_input_line(do_lang_tempcode('SCREEN_TITLE'),do_lang_tempcode('SCREEN_TITLE_DESC'),'title',$title,true));
		$fields2->attach(form_input_tick(do_lang_tempcode('HIDE_POSTS'),do_lang_tempcode('DESCRIPTION_HIDE_POSTS'),'hide_posts',$hide_posts==1));

		require_lang('notifications');
		$notify=($page_id==-1) || ($GLOBALS['SITE_DB']->query_select_value_if_there('wiki_changes','MAX(date_and_time)',array('the_page'=>$page_id))<time()-60*10);
		$radios=form_input_radio_entry('send_notification','0',!$notify,do_lang_tempcode('NO'));
		$radios->attach(form_input_radio_entry('send_notification','1',$notify,do_lang_tempcode('YES')));
		$fields2->attach(form_input_radio(do_lang_tempcode('SEND_NOTIFICATION'),do_lang_tempcode('DESCRIPTION_SEND_NOTIFICATION'),'send_notification',$radios));

		$fields2->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('_GUID'=>'5ae885a9f92415498340c41edfb47501','SECTION_HIDDEN'=>$notes=='','TITLE'=>do_lang_tempcode('ADVANCED'))));
		if (get_value('disable_staff_notes')!=='1')
			$fields2->attach(form_input_text(do_lang_tempcode('NOTES'),do_lang_tempcode('DESCRIPTION_NOTES'),'notes',$notes,false));

		require_code('fields');
		if (has_tied_catalogue('wiki_page'))
		{
			append_form_custom_fields('wiki_page',($page_id==-1)?NULL:strval($page_id),$fields,$hidden);
		}

		require_code('content2');
		$fields2->attach(meta_data_get_fields('wiki_page',is_null($id)?NULL:strval($id)));

		if (addon_installed('content_reviews'))
		{
			require_code('content_reviews');
			$fields2->attach(content_review_get_fields('wiki_page',is_null($id)?NULL:strval($id)));
		}

		require_code('permissions2');
		$fields2->attach(get_category_permissions_for_environment('wiki_page',strval($page_id),'cms_wiki',NULL,($page_id==-1)));

		return array($fields,$fields2,$hidden);
	}

	/**
	 * The UI for adding a Wiki+ page.
	 *
	 * @return tempcode	The UI.
	 */
	function add_page()
	{
		$title=get_screen_title('WIKI_ADD_PAGE');

		check_submit_permission('cat_low');

		$_title=get_param('id','',true);

		$add_url=build_url(array('page'=>'_SELF','type'=>'_add_page','redirect'=>get_param('redirect',NULL)),'_SELF');

		list($fields,$fields2,$hidden)=$this->get_page_fields(NULL,$_title);

		// Awards?
		if (addon_installed('awards'))
		{
			require_code('awards');
			$fields2->attach(get_award_fields('wiki_page'));
		}

		$posting_form=get_posting_form(do_lang('WIKI_ADD_PAGE'),'',$add_url,$hidden,$fields,NULL,'',$fields2);

		return do_template('POSTING_SCREEN',array('_GUID'=>'ea72f10d85ed06b618866f21da515180','POSTING_FORM'=>$posting_form,'HIDDEN'=>'','TITLE'=>$title,'TEXT'=>paragraph(do_lang_tempcode('WIKI_EDIT_PAGE_TEXT'))));
	}

	/**
	 * The actualiser for adding a Wiki+ page.
	 *
	 * @return tempcode	The UI.
	 */
	function _add_page()
	{
		$title=get_screen_title('WIKI_ADD_PAGE');

		check_submit_permission('cat_low');

		require_code('content2');
		$meta_data=actual_meta_data_get_fields('wiki_page',NULL);

		$id=wiki_add_page(post_param('title'),post_param('post'),post_param('notes',''),post_param_integer('hide_posts',0),$meta_data['submitter'],$meta_data['add_time'],$meta_data['views']);
		require_code('permissions2');
		set_category_permissions_from_environment('wiki_page',strval($id),'cms_wiki');

		require_code('fields');
		if (has_tied_catalogue('wiki_page'))
		{
			save_form_custom_fields('wiki_page',strval($id));
		}

		if (addon_installed('awards'))
		{
			require_code('awards');
			handle_award_setting('wiki_page',strval($id));
		}

		if (addon_installed('content_reviews'))
		{
			require_code('content_reviews');
			content_review_set('wiki_page',strval($id));
		}

		require_code('autosave');
		clear_ocp_autosave();

		// Show it worked / Refresh
		$url=get_param('redirect',NULL);
		if (is_null($url))
		{
			$_url=build_url(array('page'=>'wiki','type'=>'misc','id'=>($id==db_get_first_id())?NULL:$id),get_module_zone('wiki'));
			$url=$_url->evaluate();
		}
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The UI for choosing a Wiki+ page to edit (not normally used).
	 *
	 * @return tempcode	The UI.
	 */
	function choose_page_to_edit()
	{
		$title=get_screen_title('WIKI_EDIT_PAGE');

		$list=wiki_show_tree();
		require_code('form_templates');
		$fields=form_input_list(do_lang_tempcode('_WIKI_PAGE'),'','id',$list,NULL,true);

		$post_url=build_url(array('page'=>'_SELF','type'=>'edit_page'),'_SELF',NULL,false,true);
		$submit_name=do_lang_tempcode('CHOOSE');

		breadcrumb_set_self(do_lang_tempcode('CHOOSE'));

		set_helper_panel_text(comcode_lang_string('DOC_WIKI'));

		$search_url=build_url(array('page'=>'search','id'=>'wiki_pages'),get_module_zone('search'));
		$archive_url=build_url(array('page'=>'wiki'),get_module_zone('wiki'));
		$text=paragraph(do_lang_tempcode('CHOOSE_EDIT_LIST_EXTRA',escape_html($search_url->evaluate()),escape_html($archive_url->evaluate())));

		return do_template('FORM_SCREEN',array('_GUID'=>'e64757db1c77d752d813638f8a80581d','GET'=>true,'SKIP_VALIDATION'=>true,'TITLE'=>$title,'HIDDEN'=>'','SUBMIT_NAME'=>$submit_name,'TEXT'=>$text,'FIELDS'=>$fields,'URL'=>$post_url));
	}

	/**
	 * The UI for editing a Wiki+ page.
	 *
	 * @return tempcode	The UI.
	 */
	function edit_page()
	{
		$title=get_screen_title('WIKI_EDIT_PAGE');

		$__id=get_param('id','',true);
		if (($__id=='') || (strpos($__id,'/')!==false))
		{
			$_id=get_param_wiki_chain('id');
			$id=intval($_id[0]);
		} else $id=intval($__id);

		check_edit_permission('cat_low',NULL,array('wiki_page',$id));

		if (!has_category_access(get_member(),'wiki_page',strval($id))) access_denied('CATEGORY_ACCESS');

		$pages=$GLOBALS['SITE_DB']->query_select('wiki_pages',array('*'),array('id'=>$id),'',1);
		if (!array_key_exists(0,$pages)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		$page=$pages[0];

		$page_title=get_translated_text($page['title']);
		$description=get_translated_text($page['description']);
		$_description=get_translated_tempcode($page['description']);

		$redir_url=get_param('redirect',NULL);
		if (is_null($redir_url))
		{
			$_redir_url=build_url(array('page'=>'wiki','type'=>'misc','id'=>(get_param('id',false,true)==strval(db_get_first_id()))?NULL:get_param('id',false,true)),get_module_zone('wiki'));
			$redir_url=$_redir_url->evaluate();
		}
		$edit_url=build_url(array('page'=>'_SELF','redirect'=>$redir_url,'id'=>get_param('id',false,true),'type'=>'_edit_page'),'_SELF');

		list($fields,$fields2,$hidden)=$this->get_page_fields($id,$page_title,$page['notes'],$page['hide_posts'],$id);
		require_code('seo2');
		$fields2->attach(seo_get_fields('wiki_page',strval($id)));

		if (addon_installed('awards'))
		{
			// Awards?
			require_code('awards');
			$fields2->attach(get_award_fields('wiki_page',strval($id)));
		}

		if (has_delete_permission('cat_low',get_member(),NULL,NULL,array('wiki_page',$id)) && ($id!=db_get_first_id()))
		{
			$fields2->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('_GUID'=>'189cb80853d73ea1f63d5b0463ef7a37','TITLE'=>do_lang_tempcode('ACTIONS'))));
			$fields2->attach(form_input_tick(do_lang_tempcode('DELETE'),do_lang_tempcode('DESCRIPTION_DELETE'),'delete',false));
		}

		$restore_from=get_param_integer('restore_from',-1);
		if ($restore_from!=-1)
		{
			$description=$GLOBALS['SITE_DB']->query_select_value('translate_history','text_original',array('id'=>$restore_from,'lang_id'=>$page['description'])); // Double selection to stop hacking
			$_description=NULL;
		}

		$posting_form=get_posting_form(do_lang('SAVE'),$description,$edit_url,new ocp_tempcode(),$fields,do_lang_tempcode('PAGE_TEXT'),'',$fields2,$_description,NULL,NULL,false);

		// Revision history
		require_code('files');
		$revision_history=new ocp_tempcode();
		$revisions=$GLOBALS['SITE_DB']->query_select('translate_history',array('*'),array('lang_id'=>$page['description']),'ORDER BY action_time DESC');
		$last_description=$description;
		foreach ($revisions as $revision)
		{
			$time=$revision['action_time'];
			$date=get_timezoned_date($time);
			$editor=$GLOBALS['FORUM_DRIVER']->get_username($revision['action_member']);
			$restore_url=build_url(array('page'=>'_SELF','type'=>'edit_page','id'=>get_param('id',false,true),'restore_from'=>$revision['id']),'_SELF');
			$size=strlen($revision['text_original']);
			require_code('diff');
			if (function_exists('diff_simple_2'))
			{
				$rendered_diff=diff_simple_2($revision['text_original'],$last_description);
				$last_description=$revision['text_original'];
				$revision_history->attach(do_template('REVISION_HISTORY_LINE',array('_GUID'=>'a46de8a930ecfb814695a50b1c4931ac','RENDERED_DIFF'=>$rendered_diff,'EDITOR'=>$editor,'DATE'=>$date,'DATE_RAW'=>strval($time),'RESTORE_URL'=>$restore_url,'URL'=>'','SIZE'=>clean_file_size($size))));
			}
		}
		if ((!$revision_history->is_empty()) && ($restore_from==-1))
			$revision_history=do_template('REVISION_HISTORY_WRAP',array('_GUID'=>'1fc38d9d7ec57af110759352446e533d','CONTENT'=>$revision_history));
		elseif (!$revision_history->is_empty()) $revision_history=do_template('REVISION_RESTORE');

		list($warning_details,$ping_url)=handle_conflict_resolution();

		$breadcrumbs=wiki_breadcrumbs(get_param('id',false,true),NULL,true,true);
		breadcrumb_add_segment($breadcrumbs,protect_from_escaping('<span>'.do_lang('WIKI_EDIT_PAGE').'</span>'));
		breadcrumb_set_parents(array(array('_SELF:_SELF:edit_page',do_lang_tempcode('CHOOSE'))));

		return do_template('POSTING_SCREEN',array(
			'_GUID'=>'de53b8902ab1431e0d2d676f7d5471d3',
			'PING_URL'=>$ping_url,
			'WARNING_DETAILS'=>$warning_details,
			'REVISION_HISTORY'=>$revision_history,
			'POSTING_FORM'=>$posting_form,
			'HIDDEN'=>$hidden,
			'TITLE'=>$title,
			'TEXT'=>paragraph(do_lang_tempcode('WIKI_EDIT_PAGE_TEXT')),
		));
	}

	/**
	 * The actualiser for editing a Wiki+ page.
	 *
	 * @return tempcode	The UI.
	 */
	function _edit_page()
	{
		$_id=get_param_wiki_chain('id');
		$id=intval($_id[0]);

		if (!has_category_access(get_member(),'wiki_page',strval($id))) access_denied('CATEGORY_ACCESS');

		if (post_param_integer('delete',0)==1)
		{
			$title=get_screen_title('WIKI_DELETE_PAGE');

			check_delete_permission('cat_low',NULL,array('wiki_page',$id));

			wiki_delete_page($id);

			require_code('fields');
			if (has_tied_catalogue('wiki_page'))
			{
				delete_form_custom_fields('wiki_page',strval($id));
			}

			require_code('autosave');
			clear_ocp_autosave();

			$_url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
			$url=$_url->evaluate();
		} else
		{
			$title=get_screen_title('WIKI_EDIT_PAGE');

			check_edit_permission('cat_low',NULL,array('wiki_page',$id));

			require_code('content2');
			$meta_data=actual_meta_data_get_fields('wiki_page',strval($id));

			require_code('permissions2');
			set_category_permissions_from_environment('wiki_page',strval($id),'cms_wiki');
			wiki_edit_page($id,post_param('title'),post_param('post'),post_param('notes',''),post_param_integer('hide_posts',0),post_param('meta_keywords',''),post_param('meta_description',''),$meta_data['submitter'],$meta_data['add_time'],$meta_data['views']);

			require_code('fields');
			if (has_tied_catalogue('wiki_page'))
			{
				save_form_custom_fields('wiki_page',strval($id));
			}

			if (addon_installed('content_reviews'))
			{
				require_code('content_reviews');
				content_review_set('wiki_page',strval($id));
			}

			require_code('autosave');
			clear_ocp_autosave();

			if (addon_installed('awards'))
			{
				require_code('awards');
				handle_award_setting('wiki_page',strval($id));
			}

			$url=get_param('redirect');
		}

		// Show it worked / Refresh
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The UI for managing the Wiki+ children of a page.
	 *
	 * @return tempcode	The UI.
	 */
	function edit_tree()
	{
		list($id,$chain)=get_param_wiki_chain('id');

		$pages=$GLOBALS['SITE_DB']->query_select('wiki_pages',array('*'),array('id'=>$id),'',1);
		if (!array_key_exists(0,$pages)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		$page=$pages[0];

		$page_title=get_translated_text($page['title']);
		$title=get_screen_title('_WIKI_EDIT_TREE',true,array(escape_html($page_title)));

		check_privilege('wiki_manage_tree',array('wiki_page',$id));

		if (!has_category_access(get_member(),'wiki_page',strval($id))) access_denied('CATEGORY_ACCESS');

		$children_entries=$GLOBALS['SITE_DB']->query_select('wiki_children',array('child_id','title'),array('parent_id'=>$id),'ORDER BY the_order');
		$children='';
		foreach ($children_entries as $entry)
		{
			$child_id=$entry['child_id'];
			$child_title=$entry['title'];
			$children.=strval($child_id).'!'.$child_title."\n";
		}

		$redir_url=get_param('redirect',NULL);
		if (is_null($redir_url))
		{
			$_redir_url=build_url(array('page'=>'wiki','type'=>'misc','id'=>(get_param('id',false,true)==strval(db_get_first_id()))?NULL:get_param('id',false,true)),get_module_zone('wiki'));
			$redir_url=$_redir_url->evaluate();
		}
		$post_url=build_url(array('page'=>'_SELF','id'=>get_param('id',false,true),'redirect'=>$redir_url,'type'=>'_edit_tree'),'_SELF');

		$wiki_tree=wiki_show_tree($id,NULL,'',true,false,true);

		require_code('form_templates');
		list($warning_details,$ping_url)=handle_conflict_resolution();

		$breadcrumbs=wiki_breadcrumbs($chain,NULL,true,true);
		breadcrumb_add_segment($breadcrumbs,protect_from_escaping('<span>'.do_lang('WIKI_EDIT_TREE').'</span>'));

		$fields=new ocp_tempcode();
		require_code('form_templates');
		$fields->attach(form_input_text(do_lang_tempcode('CHILD_PAGES'),new ocp_tempcode(),'children',$children,false,NULL,true));
		$form=do_template('FORM',array('_GUID'=>'b908438ccfc9be6166cf7c5c81d5de8b','FIELDS'=>$fields,'URL'=>$post_url,'HIDDEN'=>'','TEXT'=>'','SUBMIT_NAME'=>do_lang_tempcode('SAVE')));

		return do_template('WIKI_MANAGE_TREE_SCREEN',array('_GUID'=>'83da3f20799b66b8846eafa4251a5d01','PAGE_TITLE'=>$page_title,'PING_URL'=>$ping_url,'WARNING_DETAILS'=>$warning_details,'BREADCRUMBS'=>$breadcrumbs,'TITLE'=>$title,'FORM'=>$form,'WIKI_TREE'=>$wiki_tree));
	}

	/**
	 * The actualiser for managing the Wiki+ children of a page.
	 *
	 * @return tempcode	The UI.
	 */
	function _edit_tree()
	{
		$_title=get_screen_title('WIKI_EDIT_TREE');

		$_id=get_param_wiki_chain('id');
		$id=$_id[0];

		if (!has_category_access(get_member(),'wiki_page',strval($id))) access_denied('CATEGORY_ACCESS');

		$childlinks=post_param('children');

		$member=get_member();
		check_privilege('wiki_manage_tree',array('wiki_page',$id));

		$hide_posts=$GLOBALS['SITE_DB']->query_select_value('wiki_pages','hide_posts',array('id'=>$id));

		if ((substr($childlinks,-1,1)!="\n") && (strlen($childlinks)>0)) $childlinks.="\n";
		$no_children=substr_count($childlinks,"\n");
		if ($no_children>300) warn_exit(do_lang_tempcode('TOO_MANY_WIKI_CHILDREN'));
		$start=0;
		$GLOBALS['SITE_DB']->query_delete('wiki_children',array('parent_id'=>$id));
		require_code('seo2');
		for ($i=0;$i<$no_children;$i++)
		{
			$length=strpos($childlinks,chr(10),$start)-$start;
			$newlink=str_replace(chr(10),'',str_replace(chr(13),'',substr($childlinks,$start,$length)));
			if ($newlink!='')
			{
				// Find ID and title
				$q_pos=strpos($newlink,'!');
				$child_id_on_start=(($q_pos!==false) && ($q_pos>0) && (is_numeric(substr($newlink,0,$q_pos))));

				if ($child_id_on_start) // Existing
				{
					$title=substr($newlink,$q_pos+1);
					$child_id=intval(substr($newlink,0,$q_pos));
					if ($child_id==$id) continue;
					$title_id=$GLOBALS['SITE_DB']->query_select_value_if_there('wiki_pages','title',array('id'=>$child_id));
					if (is_null($title_id)) continue;
					if ($title=='')
					{
						$title=get_translated_text($title_id);
					} else
					{
						if (get_translated_text($title_id)!=$title)
						{
							require_code('urls2');
							suggest_new_idmoniker_for('wiki','misc',strval($child_id),'',$title);
							$GLOBALS['SITE_DB']->query_update('wiki_pages',array('title'=>lang_remap($title_id,$title)),array('id'=>$child_id),'',1);
						}
					}
				}
				else // New
				{
					$title=$newlink;
					$child_id=wiki_add_page($title,'','',$hide_posts);
					$admin_groups=$GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
					$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(false,true);
					foreach (array_keys($groups) as $group_id)
					{
						if (in_array($group_id,$admin_groups)) continue;

						$GLOBALS['SITE_DB']->query_insert('group_category_access',array('module_the_name'=>'wiki_page','category_name'=>strval($child_id),'group_id'=>$group_id));
					}

					require_code('notifications2');
					copy_notifications_to_new_child('wiki',strval($id),strval($child_id));
				}

				$GLOBALS['SITE_DB']->query_delete('wiki_children',array('parent_id'=>$id,'child_id'=>$child_id),'',1); // Just in case it was repeated
				$GLOBALS['SITE_DB']->query_insert('wiki_children',array('parent_id'=>$id,'child_id'=>$child_id,'the_order'=>$i,'title'=>$title));

				require_code('notifications2');
				copy_notifications_to_new_child('wiki',strval($id),strval($child_id));
			}
			$start=$start+$length+1;
		}

		$GLOBALS['SITE_DB']->query_insert('wiki_changes',array('the_action'=>'WIKI_EDIT_TREE','the_page'=>$id,'date_and_time'=>time(),'ip'=>get_ip_address(),'member_id'=>$member));

		// Show it worked / Refresh
		$url=get_param('redirect');
		return redirect_screen($_title,$url,do_lang_tempcode('SUCCESS'));
	}

}


