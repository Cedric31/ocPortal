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
 * @package		ocf_forum
 */

require_code('aed_module');

/**
 * Module page class.
 */
class Module_admin_ocf_forums extends standard_aed_module
{
	var $lang_type='FORUM';
	var $select_name='NAME';
	var $protect_first=1;
	var $archive_entry_point='_SEARCH:forumview';
	var $archive_label='SECTION_FORUMS';
	var $view_entry_point='_SEARCH:forumview:id=_ID';
	var $special_edit_frontend=true;
	var $permission_page='topics';
	var $permission_module='forums';
	var $award_type='forum';
	var $javascript='if (document.getElementById(\'delete\')) { var form=document.getElementById(\'delete\').form; var crf=function() { form.elements[\'target_forum\'].disabled=(!form.elements[\'delete\'].checked); form.elements[\'delete_topics\'].disabled=(!form.elements[\'delete\'].checked); }; crf(); form.elements[\'delete\'].onchange=crf; }';
	var $menu_label='SECTION_FORUMS';
	var $do_preview=NULL;

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array_merge(array('misc'=>'MANAGE_FORUMS'),parent::get_entry_points());
	}
	
	/**
	 * Standard aed_module run_start.
	 *
	 * @param  ID_TEXT		The type of module execution
	 * @return tempcode		The output of the run
	 */
	function run_start($type)
	{
		$GLOBALS['HELPER_PANEL_PIC']='pagepics/forums';
		$GLOBALS['HELPER_PANEL_TUTORIAL']='tut_forums';

		$this->add_one_label=do_lang_tempcode('ADD_FORUM');
		$this->edit_this_label=do_lang_tempcode('EDIT_THIS_FORUM');
		$this->edit_one_label=do_lang_tempcode('EDIT_FORUM');

		global $C_TITLE;
		$C_TITLE=NULL;

		if (get_forum_type()!='ocf') warn_exit(do_lang_tempcode('NO_OCF')); else ocf_require_all_forum_stuff();
		require_code('ocf_forums_action');
		require_code('ocf_forums_action2');
		require_code('ocf_forums2');
		require_css('ocf');
		
		load_up_all_module_category_permissions($GLOBALS['FORUM_DRIVER']->get_guest_id(),'forums');
		
		if ($type=='misc') return $this->misc();
		if ($type=='reorder') return $this->reorder();
		
		return new ocp_tempcode();
	}

	/**
	 * The do-next manager for before content management.
	 *
	 * @return tempcode		The UI
	 */
	function misc()
	{
		$menu_links=array(
						/*	 type							  page	 params													 zone	  */
						array('add_one_category',array('admin_ocf_categories',array('type'=>'ad'),get_module_zone('admin_ocf_categories')),do_lang('ADD_FORUM_CATEGORY')),
						array('edit_one_category',array('admin_ocf_categories',array('type'=>'ed'),get_module_zone('admin_ocf_categories')),do_lang('EDIT_FORUM_CATEGORY')),
						array('add_one',array('_SELF',array('type'=>'ad'),'_SELF'),do_lang('ADD_FORUM')),
						array('edit_one',array('_SELF',array('type'=>'ed'),'_SELF'),do_lang('EDIT_FORUM')),
					);

		if (addon_installed('ocf_post_templates'))
			$menu_links[]=array('posttemplates',array('admin_ocf_post_templates',array('type'=>'misc'),get_module_zone('admin_ocf_post_templates')),do_lang_tempcode('POST_TEMPLATES'),('DOC_POST_TEMPLATES'));
		if (addon_installed('ocf_multi_moderations'))
			$menu_links[]=array('multimods',array('admin_ocf_multimoderations',array('type'=>'misc'),get_module_zone('admin_ocf_multimoderations')),do_lang_tempcode('MULTI_MODERATIONS'),('DOC_MULTI_MODERATIONS'));
		
		require_code('templates_donext');
		require_code('fields');
		return do_next_manager(get_page_title('MANAGE_FORUMS'),comcode_to_tempcode(do_lang('DOC_FORUMS')."\n\n".do_lang('DOC_FORUM_CATEGORIES'),NULL,true),
					array_merge($menu_links,manage_custom_fields_donext_link('post'),manage_custom_fields_donext_link('topic'),manage_custom_fields_donext_link('forum')),
					do_lang('MANAGE_FORUMS')
		);
	}

	/**
	 * Get tempcode for a forum adding/editing form.
	 *
	 * @param  ?AUTO_LINK	The ID of the forum being edited (NULL: adding, not editing)
	 * @param  SHORT_TEXT	The name of the forum
	 * @param  LONG_TEXT		The description of the forum
	 * @param  ?AUTO_LINK	The ID of the category for the forum (NULL: first)
	 * @param  ?AUTO_LINK	The parent forum (NULL: root)
	 * @param  ?integer		The position (NULL: next)
	 * @param  BINARY			Whether post counts are incremented in this forum
	 * @param  BINARY			Whether subforums are ordered alphabetically (instead of manually)
	 * @param  LONG_TEXT		Introductory question posed to all newcomers to the forum
	 * @param  LONG_TEXT		Answer to the introductory question (or blank if it was just an 'ok')
	 * @param  SHORT_TEXT	Redirection code (blank implies a normal forum, not a redirector)
	 * @param  ID_TEXT		The order the topics are shown in, by default.
	 * @return array			A pair: The input fields, Hidden fields
	 */
	function get_form_fields($id=NULL,$name='',$description='',$category_id=NULL,$parent_forum=NULL,$position=NULL,$post_count_increment=1,$order_sub_alpha=0,$intro_question='',$intro_answer='',$redirection='',$order='last_post')
	{
		if (is_null($category_id))
		{
			$category_id=get_param_integer('category_id',db_get_first_id());

			global $NON_CANONICAL_PARAMS;
			$NON_CANONICAL_PARAMS[]='category_id';
		}

		if (is_null($parent_forum))
		{
			global $NON_CANONICAL_PARAMS;
			$NON_CANONICAL_PARAMS[]='parent_forum';

			$parent_forum=get_param_integer('parent_forum',NULL);
		}

		if (is_null($position))
		{
			$position=$GLOBALS['FORUM_DB']->query_value_null_ok('f_forums','MAX(f_position)')+1;
		}

		$fields=new ocp_tempcode();
		$hidden=new ocp_tempcode();

		$fields->attach(form_input_line(do_lang_tempcode('NAME'),do_lang_tempcode('DESCRIPTION_NAME'),'name',$name,true));
		$fields->attach(form_input_line_comcode(do_lang_tempcode('DESCRIPTION'),do_lang_tempcode('DESCRIPTION_DESCRIPTION'),'description',$description,false));
		$list=ocf_nice_get_categories(NULL,$category_id);
		$fields->attach(form_input_list(do_lang_tempcode('FORUM_GROUPING'),do_lang_tempcode('DESCRIPTION_FORUM_GROUPING'),'category_id',$list));
		if ((is_null($id)) || ((!is_null($id)) && ($id!=db_get_first_id())))
		{
			$fields->attach(form_input_tree_list(do_lang_tempcode('PARENT'),do_lang_tempcode('DESCRIPTION_PARENT_FORUM'),'parent_forum',NULL,'choose_forum',array(),true,is_null($parent_forum)?'':strval($parent_forum)));
		}
		if ($GLOBALS['FORUM_DB']->query_value('f_forums','COUNT(*)')>300)
		{
			$fields->attach(form_input_integer(do_lang_tempcode('ORDER'),do_lang_tempcode('DESCRIPTION_FORUM_ORDER'),'position',$position,true));
		} else
		{
			$hidden->attach(form_input_hidden('position',strval($position)));
		}

		$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('SECTION_HIDDEN'=>$post_count_increment==1 && $order_sub_alpha==0 && ($intro_question=='') && ($intro_answer=='') && ($redirection=='') && ($order=='last_post'),'TITLE'=>do_lang_tempcode('ADVANCED'))));
		$fields->attach(form_input_tick(do_lang_tempcode('POST_COUNT_INCREMENT'),do_lang_tempcode('DESCRIPTION_POST_COUNT_INCREMENT'),'post_count_increment',$post_count_increment==1));
		$fields->attach(form_input_tick(do_lang_tempcode('ORDER_SUB_ALPHA'),do_lang_tempcode('DESCRIPTION_ORDER_SUB_ALPHA'),'order_sub_alpha',$order_sub_alpha==1));
		$fields->attach(form_input_text_comcode(do_lang_tempcode('INTRO_QUESTION'),do_lang_tempcode('DESCRIPTION_INTRO_QUESTION'),'intro_question',$intro_question,false));
		$fields->attach(form_input_line(do_lang_tempcode('INTRO_ANSWER'),do_lang_tempcode('DESCRIPTION_INTRO_ANSWER'),'intro_answer',$intro_answer,false));
		$fields->attach(form_input_line(do_lang_tempcode('REDIRECTING'),do_lang_tempcode('DESCRIPTION_FORUM_REDIRECTION'),'redirection',$redirection,false));
		$list=new ocp_tempcode();
		$list->attach(form_input_list_entry('last_post',$order=='last_post',do_lang_tempcode('FORUM_ORDER_BY_LAST_POST')));
		$list->attach(form_input_list_entry('first_post',$order=='first_post',do_lang_tempcode('FORUM_ORDER_BY_FIRST_POST')));
		$list->attach(form_input_list_entry('title',$order=='title',do_lang_tempcode('FORUM_ORDER_BY_TITLE')));
		$fields->attach(form_input_list(do_lang_tempcode('TOPIC_ORDER'),do_lang_tempcode('DESCRIPTION_TOPIC_ORDER'),'order',$list));

		// Permissions
		$fields->attach($this->get_permission_fields(is_null($id)?NULL:strval($id),NULL,is_null($id)));

		return array($fields,$hidden);
	}

	/**
	 * Get a UI to choose a forum to edit.
	 *
	 * @param  AUTO_LINK		The ID of the forum we are generating the tree below (start recursion with db_get_first_id())
	 * @param  SHORT_TEXT	The name of the forum $id
	 * @param  array			A list of rows of all forums, or array() if the function is to get the list itself
	 * @param  integer		The relative position of this forum wrt the others on the same level/branch in the UI
	 * @param  integer		The number of forums in the parent category
	 * @param  ?BINARY		Whether to order own subcategories alphabetically (NULL: ask the DB)
	 * @param  ?BINARY		Whether to order subcategories alphabetically (NULL: ask the DB)
	 * @param  boolean		Whether we are dealing with a huge forum structure
	 * @return tempcode		The UI
	 */
	function get_forum_tree($id,$forum,&$all_forums,$position=0,$sub_num_in_parent_category=1,$order_sub_alpha=NULL,$parent_order_sub_alpha=NULL,$huge=false)
	{
		$categories=new ocp_tempcode();

		if ($huge)
		{
			$all_forums=$GLOBALS['FORUM_DB']->query_select('f_forums',array('id','f_name','f_position','f_category_id','f_order_sub_alpha','f_parent_forum'),array('f_parent_forum'=>$id),'ORDER BY f_parent_forum,f_position',300);
			if (count($all_forums)==300) return paragraph(do_lang_tempcode('TOO_MANY_TO_CHOOSE_FROM'));
		} else
		{
			if (count($all_forums)==0)
			{
				$all_forums=$GLOBALS['FORUM_DB']->query_select('f_forums',array('id','f_name','f_position','f_category_id','f_order_sub_alpha','f_parent_forum'),NULL,'ORDER BY f_parent_forum,f_position');
			}
		}

		if (is_null($order_sub_alpha))
		{
			$parent_order_sub_alpha=0;
			$order_sub_alpha=$GLOBALS['FORUM_DB']->query_value('f_forums','f_order_sub_alpha',array('id'=>$id));
		}

		global $C_TITLE;
		if (is_null($C_TITLE)) $C_TITLE=collapse_2d_complexity('id','c_title',$GLOBALS['FORUM_DB']->query_select('f_categories',array('id','c_title')));

		$_categories=array();
		foreach ($all_forums as $_forum)
		{
			if ($_forum['f_parent_forum']==$id) $_categories[$_forum['f_category_id']]=1;
		}
		$num_categories=count($_categories);

		$order=($order_sub_alpha==1)?'f_name':'f_position';
		$subforums=array();
		foreach ($all_forums as $_forum)
		{
			if ($_forum['f_parent_forum']==$id) $subforums[$_forum['id']]=$_forum;
		}
		if ($order=='f_name')
		{
			global $M_SORT_KEY;
			$M_SORT_KEY='f_name';
			uasort($subforums,'multi_sort');
		}
		$category_id=mixed();
		$position_in_cat=0;
		$category_position=0;
		$forums=NULL;
		$orderings='';
		while (count($subforums)!=0)
		{
			$i=NULL;
			if (!is_null($category_id))
			{
				foreach ($subforums as $j=>$subforum)
				{
					if ($subforum['f_category_id']==$category_id)
					{
						$i=$j;
						break;
					}
				}
			}

			if (is_null($i))
			{
				if (!is_null($forums))
				{
					$categories->attach(do_template('OCF_EDIT_FORUM_SCREEN_CATEGORY',array('_GUID'=>'889173769e237b917b7e06eda0fb4350','ORDERINGS'=>$orderings,'CATEGORY'=>$C_TITLE[$category_id],'SUBFORUMS'=>$forums)));
					$category_position++;
				}
				$forums=new ocp_tempcode();
				$i=0;
				foreach ($subforums as $j=>$subforum)
				{
					$i=$j;
					break;
				}
				$category_id=$subforums[$i]['f_category_id'];
				$position_in_cat=0;
				$sub_num_in_category=0;
				foreach ($subforums as $subforum)
				{
					if ($subforum['f_category_id']==$category_id) $sub_num_in_category++;
				}
			}

			$subforum=$subforums[$i];

			$orderings='';
			if (($order_sub_alpha==0) && (!$huge))
			{
				for ($_i=0;$_i<$num_categories;$_i++)
				{
					$orderings.='<option '.(($_i==$category_position)?'selected="selected"':'').'>'.strval($_i+1).'</option>';
				}
				$orderings='<label for="category_order_'.strval($id).'_'.strval($category_id).'">'.do_lang('ORDER').'<span class="accessibility_hidden"> ('.(array_key_exists($category_id,$C_TITLE)?escape_html($C_TITLE[$category_id]):'').')</span> <select id="category_order_'.strval($id).'_'.strval($category_id).'" name="category_order_'.strval($id).'_'.strval($category_id).'">'.$orderings.'</select></label>'; // XHTMLXHTML
			}

			$forums->attach($this->get_forum_tree($subforum['id'],$subforum['f_name'],$all_forums,$position_in_cat,$sub_num_in_category,$subforum['f_order_sub_alpha'],$order_sub_alpha,$huge));

			$position_in_cat++;
			unset($subforums[$i]);
		}
		if (!is_null($category_id))
			$categories->attach(do_template('OCF_EDIT_FORUM_SCREEN_CATEGORY',array('_GUID'=>'6cb30ec5189f75a9631b2bb430c89fd0','ORDERINGS'=>$orderings,'CATEGORY'=>$C_TITLE[$category_id],'SUBFORUMS'=>$forums)));

		$edit_url=build_url(array('page'=>'_SELF','type'=>'_ed','id'=>$id),'_SELF');
		$view_map=array('page'=>'forumview');
		if ($id!=db_get_first_id()) $view_map['id']=$id;
		$view_url=build_url($view_map,get_module_zone('forumview'));

		$class=(!has_category_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'forums',strval($id)))?'access_restricted_in_list':'';

		$orderings='';
		if ($parent_order_sub_alpha==0)
		{
			for ($i=0;$i<$sub_num_in_parent_category;$i++)
			{
				$orderings.='<option '.(($i==$position)?'selected="selected"':'').'>'.strval($i+1).'</option>';
			}
			$orderings='<label for="order_'.strval($id).'">'.do_lang('ORDER').'<span class="accessibility_hidden"> ('.escape_html($forum).')</span> <select id="order_'.strval($id).'" name="order_'.strval($id).'">'.$orderings.'</select></label>';
		}

		if ($GLOBALS['XSS_DETECT']) ocp_mark_as_escaped($orderings);

		return do_template('OCF_EDIT_FORUM_SCREEN_FORUM',array('_GUID'=>'35fdeb9848919b5c30b069eb5df603d5','ID'=>strval($id),'ORDERINGS'=>$orderings,'CATEGORIES'=>$categories,'CLASS'=>$class,'FORUM'=>$forum,'VIEW_URL'=>$view_url,'EDIT_URL'=>$edit_url));
	}

	/**
	 * The UI to choose a forum to edit (relies on get_forum_tree to do almost all the work).
	 *
	 * @return tempcode		The UI
	 */
	function ed()
	{
		$title=get_page_title('EDIT_FORUM');

		$huge=($GLOBALS['FORUM_DB']->query_value('f_forums','COUNT(*)')>300);

		$all_forums=array();
		$forums=$this->get_forum_tree(db_get_first_id(),$GLOBALS['FORUM_DB']->query_value('f_forums','f_name',array('id'=>db_get_first_id())),$all_forums,0,1,NULL,NULL,$huge);

		if ($huge)
		{
			$reorder_url=new ocp_tempcode();
		} else
		{
			$reorder_url=build_url(array('page'=>'_SELF','type'=>'reorder'),'_SELF');
		}

		return do_template('OCF_EDIT_FORUM_SCREEN',array('_GUID'=>'762810dcff9acfa51995984d2c008fef','REORDER_URL'=>$reorder_url,'TITLE'=>$title,'ROOT_FORUM'=>$forums));
	}

	/**
	 * The actualiser to reorder forums.
	 *
	 * @return tempcode		The UI
	 */
	function reorder()
	{
		$title=get_page_title('EDIT_FORUM');

		$all=$GLOBALS['FORUM_DB']->query_select('f_forums',array('id','f_parent_forum','f_category_id'));
		$ordering=array();
		foreach ($all as $forum)
		{
			$cat_order=post_param_integer('category_order_'.strval($forum['f_parent_forum']).'_'.strval($forum['f_category_id']),-1);
			$order=post_param_integer('order_'.strval($forum['id']),-1);
			if (($cat_order!=-1) && ($order!=-1)) // Should only be -1 if since deleted
			{
				if (!array_key_exists($forum['f_parent_forum'],$ordering))
					$ordering[$forum['f_parent_forum']]=array();
				if (!array_key_exists($cat_order,$ordering[$forum['f_parent_forum']]))
					$ordering[$forum['f_parent_forum']][$cat_order]=array();
				while (array_key_exists($order,$ordering[$forum['f_parent_forum']][$cat_order]))
					$order++;

				$ordering[$forum['f_parent_forum']][$cat_order][$order]=$forum['id'];
			}
		}

		foreach ($ordering as $_ordering)
		{
			ksort($_ordering);
			$order=0;
			foreach ($_ordering as $forums)
			{
				ksort($forums);
				foreach ($forums as $forum_id)
				{
					$GLOBALS['FORUM_DB']->query_update('f_forums',array('f_position'=>$order),array('id'=>$forum_id),'',1);
					$order++;
				}
			}
		}

		$url=build_url(array('page'=>'_SELF','type'=>'ed'),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * Standard aed_module delete possibility checker.
	 *
	 * @param  ID_TEXT		The entry being potentially deleted
	 * @return boolean		Whether it may be deleted
	 */
	function may_delete_this($_id)
	{
		$id=intval($_id);

		if ($id==db_get_first_id()) return false;

		$fname=$GLOBALS['FORUM_DB']->query_value('f_forums','f_name',array('id'=>$id));
		$all_configured_forums=$GLOBALS['SITE_DB']->query_select('config',array('*'),array('the_type'=>'forum'));
		foreach ($all_configured_forums as $f)
		{
			if ((get_option($f['the_name'])==$fname) || (get_option($f['the_name'])==$_id))
			{
				require_all_lang();
				$_edit_url=build_url(array('page'=>'admin_config','type'=>'category','id'=>$f['the_page']),get_module_zone('admin_config'));
				$edit_url=$_edit_url->evaluate();
				$edit_url.='#group_'.$f['section'];
				attach_message(do_lang_tempcode('CANNOT_DELETE_FORUM_OPTION',escape_html($edit_url),escape_html(do_lang_tempcode($f['human_name']))),'warn');
				return false;
			}
		}

		return true;
	}

	/**
	 * Standard aed_module edit form filler.
	 *
	 * @param  ID_TEXT		The entry being edited
	 * @return array			A tuple: fields, hidden-fields, delete-fields, N/A, N/A, N/A, action fields
	 */
	function fill_in_edit_form($id)
	{
		$m=$GLOBALS['FORUM_DB']->query_select('f_forums',array('*'),array('id'=>intval($id)),'',1);
		if (!array_key_exists(0,$m)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		$r=$m[0];

		$fields=$this->get_form_fields($r['id'],$r['f_name'],get_translated_text($r['f_description'],$GLOBALS['FORUM_DB']),$r['f_category_id'],$r['f_parent_forum'],$r['f_position'],$r['f_post_count_increment'],$r['f_order_sub_alpha'],get_translated_text($r['f_intro_question'],$GLOBALS['FORUM_DB']),$r['f_intro_answer'],$r['f_redirection'],$r['f_order']);

		$delete_fields=new ocp_tempcode();
		if (intval($id)!=db_get_first_id())
		{
			$delete_fields->attach(form_input_tree_list(do_lang_tempcode('TARGET'),do_lang_tempcode('DESCRIPTION_TOPIC_MOVE_TARGET'),'target_forum',NULL,'choose_forum',array(),true,$id));
			$delete_fields->attach(form_input_tick(do_lang_tempcode('DELETE_TOPICS'),do_lang_tempcode('DESCRIPTION_DELETE_TOPICS'),'delete_topics',false));
		}

		$action_fields=new ocp_tempcode();
		$action_fields->attach(form_input_tick(do_lang_tempcode('RESET_INTRO_ACCEPTANCE'),do_lang_tempcode('DESCRIPTION_RESET_INTRO_ACCEPTANCE'),'reset_intro_acceptance',false));

		return array($fields[0],$fields[1],$delete_fields,NULL,false,NULL,$action_fields);
	}

	/**
	 * Standard aed_module add actualiser.
	 *
	 * @return ID_TEXT		The entry added
	 */
	function add_actualisation()
	{
		require_code('ocf_forums_action2');

		$parent_forum=post_param_integer('parent_forum',-1);
		$name=post_param('name');
		$id=strval(ocf_make_forum($name,post_param('description'),post_param_integer('category_id'),NULL,$parent_forum,post_param_integer('position'),post_param_integer('post_count_increment',0),post_param_integer('order_sub_alpha',0),post_param('intro_question'),post_param('intro_answer'),post_param('redirection'),post_param('order')));

		// Warning if there is full access to this forum, but not to the parent
		$admin_groups=$GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
		$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(true,true);
		$full_access=true;
		foreach (array_keys($groups) as $gid)
		{
			if (!in_array($gid,$admin_groups))
			{
				if (post_param_integer('access_'.strval($gid),0)==0)
				{
					$full_access=false;
					break;
				}
			}
		}
		if ($full_access)
		{
			$parent_has_full_access=true;
			$access_rows=$GLOBALS['FORUM_DB']->query_select('group_category_access',array('group_id'),array('module_the_name'=>'forums','category_name'=>strval($parent_forum)));
			$access=array();
			foreach ($access_rows as $row)
			{
				$access[$row['group_id']]=1;
			}
			foreach (array_keys($groups) as $gid)
			{
				if (!in_array($gid,$admin_groups))
				{
					if (!array_key_exists($gid,$access))
					{
						$parent_has_full_access=false;
						break;
					}
				}
			}
			if (!$parent_has_full_access)
			{
				attach_message(do_lang_tempcode('ANOMALOUS_FORUM_ACCESS'),'warn');
			}
		}

		$this->set_permissions($id);
		
		if ((has_actual_page_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'forumview')) && (has_category_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'forums',$id)))
			syndicate_described_activity('ocf:ADD_FORUM',$name,'','','_SEARCH:news:view:'.$id,'','','ocf_forum');

		return $id;
	}

	/**
	 * Standard aed_module edit actualiser.
	 *
	 * @param  ID_TEXT		The entry being edited
	 */
	function edit_actualisation($id)
	{
		ocf_edit_forum(intval($id),post_param('name'),post_param('description',STRING_MAGIC_NULL),post_param_integer('category_id',INTEGER_MAGIC_NULL),post_param_integer('parent_forum',INTEGER_MAGIC_NULL),post_param_integer('position',INTEGER_MAGIC_NULL),post_param_integer('post_count_increment',fractional_edit()?INTEGER_MAGIC_NULL:0),post_param_integer('order_sub_alpha',fractional_edit()?INTEGER_MAGIC_NULL:0),post_param('intro_question',STRING_MAGIC_NULL),post_param('intro_answer',STRING_MAGIC_NULL),post_param('redirection',STRING_MAGIC_NULL),post_param('order',STRING_MAGIC_NULL),post_param_integer('reset_intro_acceptance',0)==1);

		if (!fractional_edit())
		{
			require_code('ocf_groups2');

			$old_access_mapping=collapse_1d_complexity('group_id',$GLOBALS['FORUM_DB']->query_select('group_category_access',array('group_id'),array('module_the_name'=>'forums','category_name'=>$id)));

			require_code('ocf_groups_action');
			require_code('ocf_groups_action2');

			$members_to_test=array();
			$lost_groups=array();
			foreach ($old_access_mapping as $group_id)
			{
				if (post_param_integer('access_'.strval($group_id),0)==0) // Lost access
					$lost_groups[]=$group_id;
			}
	
			$this->set_permissions($id);
		}
	}

	/**
	 * Standard aed_module delete actualiser.
	 *
	 * @param  ID_TEXT		The entry being deleted
	 */
	function delete_actualisation($id)
	{
		ocf_delete_forum(intval($id),post_param_integer('target_forum'),post_param_integer('delete_topics',0));
	}
}


