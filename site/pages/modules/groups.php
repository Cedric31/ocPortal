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
 * @package		core_ocf
 */

/**
 * Module page class.
 */
class Module_groups
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
	 * @param  boolean	Whether to check permissions.
	 * @param  ?MEMBER	The member to check permissions as (NULL: current user).
	 * @param  boolean	Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
	 * @param  boolean	Whether to avoid any entry-point (or even return NULL to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "misc" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
	 * @return ?array		A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (NULL: disabled).
	 */
	function get_entry_points($check_perms=true,$member_id=NULL,$support_crosslinks=true,$be_deferential=false)
	{
		if (get_forum_type()!='ocf') return NULL;

		return array(
			'misc'=>array('USERGROUPS','menu/social/groups'),
		);
	}

	var $title;
	var $id;
	var $group;
	var $group_name;
	var $club;

	/**
	 * Standard modular pre-run function, so we know meta-data for <head> before we start streaming output.
	 *
	 * @return ?tempcode		Tempcode indicating some kind of exceptional output (NULL: none).
	 */
	function pre_run()
	{
		$type=get_param('type','misc');

		require_lang('ocf');

		if ($type=='misc')
		{
			$this->title=get_screen_title('USERGROUPS');
		}

		if ($type=='view')
		{
			$id=get_param_integer('id');

			if ($id==db_get_first_id()) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));

			$map=has_privilege(get_member(),'see_hidden_groups')?array('id'=>$id):array('id'=>$id,'g_hidden'=>0);
			$groups=$GLOBALS['FORUM_DB']->query_select('f_groups',array('*'),$map,'',1);
			if (!array_key_exists(0,$groups)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
			$group=$groups[0];

			$group_name=get_translated_text($group['g_name'],$GLOBALS['FORUM_DB']);
			$club=($group['g_is_private_club']==1);

			breadcrumb_set_self(make_string_tempcode($group_name));
			breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('USERGROUPS'))));

			set_extra_request_metadata(array(
				'created'=>'',
				'creator'=>is_null($group['g_group_leader'])?'':$GLOBALS['FORUM_DRIVER']->get_username($group['g_group_leader']),
				'publisher'=>'', // blank means same as creator
				'modified'=>'',
				'type'=>'Usergroup',
				'title'=>$group_name,
				'identifier'=>'_SEARCH:groups:view:'.strval($id),
				'description'=>'',
				'image'=>find_theme_image('icons/48x48/menu/social/groups'),
			));

			$this->title=get_screen_title($club?'CLUB':'USERGROUP',true,array(make_fractionable_editable('group',$id,$group_name)));

			$this->id=$id;
			$this->group=$group;
			$this->group_name=$group_name;
			$this->club=$club;
		}

		if ($type=='resign')
		{
			$this->title=get_screen_title('RESIGN_FROM_GROUP');
		}

		if ($type=='remove_from')
		{
			$this->title=get_screen_title('REMOVE_MEMBER_FROM_GROUP');
		}

		if ($type=='apply')
		{
			$id=post_param_integer('id',NULL);
			if (is_null($id))
			{
				$_id=get_param('id');
				if (is_numeric($_id))
				{
					$id=intval($_id);
				} else // Collaboration zone has a text link like this
				{
					$id=$GLOBALS['FORUM_DB']->query_select_value_if_there('f_groups g LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'translate t ON t.id=g.g_name','g.id',array('text_original'=>$_id));
					if (is_null($id)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
				}
				if ($id==db_get_first_id()) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));

				$group_name=ocf_get_group_name($id);

				breadcrumb_set_self(do_lang_tempcode('DONE'));
				breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('USERGROUPS')),array('_SELF:_SELF:view:'.strval($id),do_lang_tempcode('USERGROUP',escape_html($group_name)))));
			} else
			{
				$group_name=ocf_get_group_name($id);
			}

			$this->title=get_screen_title('_APPLY_TO_GROUP',true,array(escape_html($group_name)));

			$this->id=$id;
			$this->group_name=$group_name;
		}

		if ($type=='accept')
		{
			$this->title=get_screen_title('ACCEPT_INTO_GROUP');
		}

		if ($type=='add_to')
		{
			$this->title=get_screen_title('ADD_MEMBER_TO_GROUP');
		}

		if ($type=='decline')
		{
			$this->title=get_screen_title('DECLINE_FROM_GROUP');
		}

		return NULL;
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		if (get_forum_type()!='ocf') warn_exit(do_lang_tempcode('NO_OCF')); else ocf_require_all_forum_stuff();
		require_css('ocf');
		require_code('ocf_groups_action');
		require_code('ocf_groups_action2');
		require_code('ocf_groups2');

		$type=get_param('type','misc');

		if ($type=='misc') return $this->directory();
		if ($type=='view') return $this->usergroup();
		if ($type=='resign') return $this->resign();
		if ($type=='remove_from') return $this->remove_from();
		if ($type=='apply') return $this->apply();
		if ($type=='accept') return $this->accept();
		if ($type=='add_to') return $this->add_to();
		if ($type=='decline') return $this->decline();

		return new ocp_tempcode();
	}

	/**
	 * The UI to show the usergroup directory.
	 *
	 * @return tempcode		The UI
	 */
	function directory()
	{
		$staff_groups=array_merge($GLOBALS['FORUM_DRIVER']->get_super_admin_groups(),$GLOBALS['FORUM_DRIVER']->get_moderator_groups());

		$sql='SELECT * FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_groups g WHERE ';
		if (!has_privilege(get_member(),'see_hidden_groups'))
			$sql.='g_hidden=0 AND ';
		$sql.='(g_promotion_target IS NOT NULL';
		if (db_has_subqueries($GLOBALS['FORUM_DB']))
			$sql.=' OR EXISTS(SELECT id FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_groups h WHERE h.g_promotion_target=g.id)';
		foreach ($staff_groups as $g_id)
		{
			$sql.=' OR g.id='.strval($g_id);
		}
		$sql.=')';
		$sql.=' ORDER BY g_order,id';
		$groups=$GLOBALS['FORUM_DB']->query($sql);

		foreach ($groups as $g_id=>$row)
		{
			$groups[$g_id]['text_original']=get_translated_text($row['g_name'],$GLOBALS['FORUM_DB']);
		}

		// Categorise
		$_staff=array();
		$_ranks=array();
		$_others=array();
		foreach ($groups as $group)
		{
			if ($group['id']==1) continue; // Don't show guest usergroup

			if (in_array($group['id'],$staff_groups))
			{
				$_staff[$group['id']]=$group;
			} else
			{
				if (!is_null($group['g_promotion_target']))
				{
					// Are we at the start of a usergroup?
					$found=false;
					foreach ($groups as $group2)
					{
						if ($group2['g_promotion_target']==$group['id'])
						{
							$found=true;
							break;
						}
					}
					if (!$found)
					{
						$_ranks[$group['id']]=array($group['id']=>$group);
						$next=$group['g_promotion_target'];
						while (!is_null($next))
						{
							$found=false;
							foreach ($groups as $group2)
							{
								if ($group2['id']==$next)
								{
									$next=$group2['g_promotion_target'];
									$_ranks[$group['id']][$group2['id']]=$group2;
									if (array_key_exists($next,$_ranks[$group['id']])) break; // uhoh- loop
									$found=true;
									break;
								}
							}
							if (!$found) break; // uhoh- either loop, or unfound usergroup
						}
					}
				}
			}
		}

		// Generate usergroup result browsers
		require_code('templates_results_table');
		$sortables=array();
		list($sortable,$sort_order)=array('foo','ASC');

		//-Staff
		$start=get_param_integer('staff_start',0);
		$max=get_param_integer('staff_max',intval(get_option('important_groups_per_page')));
		$max_rows=count($_staff);
		$fields_title=results_field_title(array(do_lang_tempcode('NAME'),do_lang_tempcode('COUNT_MEMBERS')),$sortables);
		$staff=new ocp_tempcode();
		$i=0;
		foreach ($_staff as $row)
		{
			if ($i<$start)
			{
				$i++;
				continue;
			}
			if ($i>$start+$max) break;
			$group_name=$row['text_original'];
			$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$row['id']),'_SELF');
			$num_members=integer_format(ocf_get_group_members_raw_count($row['id'],true));
			$staff->attach(results_entry(array(hyperlink($url,make_fractionable_editable('group',$row['id'],$group_name)),escape_html($num_members))));
			$i++;
		}
		$staff=results_table(do_lang_tempcode('STAFF'),$start,'staff_start',$max,'staff_max',$max_rows,$fields_title,$staff,$sortables,$sortable,$sort_order,'staff_sort',NULL,array('200'));

		//-Ranks
		$ranks=array();
		foreach ($_ranks as $g_id=>$_rank)
		{
			$start=get_param_integer('rank_start_'.strval($g_id),0);
			$max=get_param_integer('rank_max_'.strval($g_id),intval(get_option('important_groups_per_page')));
			$max_rows=count($_rank);
			$fields_title=results_field_title(array(do_lang_tempcode('NAME'),do_lang_tempcode('COUNT_MEMBERS'),do_lang_tempcode('PROMOTION_THRESHOLD')),$sortables);
			$rank=new ocp_tempcode();
			$i=0;
			foreach ($_rank as $row)
			{
				if ($i<$start)
				{
					$i++;
					continue;
				}
				if ($i>$start+$max) break;
				$group_name=$row['text_original'];
				$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$row['id']),'_SELF');
				$num_members=integer_format(ocf_get_group_members_raw_count($row['id'],true));
				$_p_t=$row['g_promotion_threshold'];
				$p_t=new ocp_tempcode();
				if ((!is_null($_p_t)) && (array_key_exists($row['g_promotion_target'],$_rank)))
				{
					$p_t=do_lang_tempcode('PROMOTION_TO',escape_html(integer_format($_p_t)),escape_html($_rank[$row['g_promotion_target']]['text_original']));
				}
				$rank->attach(results_entry(array(hyperlink($url,make_fractionable_editable('group',$row['id'],$group_name)),escape_html($num_members),$p_t)));
			}
			$rank=results_table(do_lang_tempcode('RANK_SETS'),$start,'rank_start_'.strval($g_id),$max,'rank_max_'.strval($g_id),$max_rows,$fields_title,$rank,$sortables,$sortable,$sort_order,'rank_sort_'.strval($g_id),NULL,array('200'));
			$ranks[]=$rank;
		}

		//-Others
		$start=get_param_integer('others_start',0);
		$max=get_param_integer('others_max',intval(get_option('normal_groups_per_page')));
		$sql='SELECT * FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_groups g WHERE ';
		if (!has_privilege(get_member(),'see_hidden_groups'))
			$sql.='g_hidden=0 AND ';
		$sql.='(g_promotion_target IS NULL';
		if (db_has_subqueries($GLOBALS['FORUM_DB']))
			$sql.=' AND NOT EXISTS(SELECT id FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_groups h WHERE h.g_promotion_target=g.id)';
		foreach ($staff_groups as $g_id)
		{
			$sql.=' AND g.id<>'.strval($g_id);
		}
		$sql.=' AND g.id<>'.strval(db_get_first_id());
		$sql.=')';
		$sql.=' ORDER BY g_order,id';
		$_others=$GLOBALS['FORUM_DB']->query($sql,$max,$start);
		$max_rows=$GLOBALS['FORUM_DB']->query_value_if_there(str_replace('SELECT * ','SELECT COUNT(*) ',$sql));
		$fields_title=results_field_title(array(do_lang_tempcode('NAME'),do_lang_tempcode('COUNT_MEMBERS')),$sortables);
		$others=new ocp_tempcode();
		foreach ($_others as $row)
		{
			$row['text_original']=get_translated_text($row['g_name'],$GLOBALS['FORUM_DB']);

			$group_name=$row['text_original'];
			$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$row['id']),'_SELF');
			$num_members=integer_format(ocf_get_group_members_raw_count($row['id'],true));
			$others->attach(results_entry(array(hyperlink($url,make_fractionable_editable('group',$row['id'],$group_name)),escape_html($num_members))));
		}
		if (!$others->is_empty())
			$others=results_table(do_lang_tempcode('OTHER_USERGROUPS'),$start,'others_start',$max,'others_max',$max_rows,$fields_title,$others,$sortables,$sortable,$sort_order,'others_sort',NULL,array('200'));

		$tpl=do_template('OCF_GROUP_DIRECTORY_SCREEN',array('_GUID'=>'39aebd8fcb618c2ae45e867d0c96a4cf','TITLE'=>$this->title,'STAFF'=>$staff,'OTHERS'=>$others,'RANKS'=>$ranks));

		require_code('templates_internalise_screen');
		return internalise_own_screen($tpl);
	}

	/**
	 * The UI to show a usergroup.
	 *
	 * @return tempcode		The UI
	 */
	function usergroup()
	{
		$id=$this->id;
		$group=$this->group;
		$group_name=$this->group_name;
		$club=$this->club;

		// Leadership
		if ((!is_null($group['g_group_leader'])) && (!is_null($GLOBALS['FORUM_DRIVER']->get_username($group['g_group_leader']))))
		{
			$leader_name=$GLOBALS['FORUM_DRIVER']->get_username($group['g_group_leader'],true);
			if (is_null($leader_name)) $leader_name=do_lang('UNKNOWN');
			$leader_url=build_url(array('page'=>'members','type'=>'view','id'=>$group['g_group_leader']),get_module_zone('members'));
			$leader_link=hyperlink($leader_url,$leader_name,false,true);
			$leader=paragraph(do_lang_tempcode('GROUP_LED_BY',$leader_link),'gfgdfggdf');
		} else $leader=new ocp_tempcode();

		// Promotion
		if ((addon_installed('points')) && (!is_null($group['g_promotion_threshold'])) && (!is_null($group['g_promotion_target'])))
		{
			$promote_link=ocf_get_group_link($group['g_promotion_target']);
			$promotion_info=do_lang_tempcode('OCF_PROMOTION_INFO',integer_format($group['g_promotion_threshold']),$promote_link->evaluate());
		} else $promotion_info=new ocp_tempcode();

		// To add
		if (ocf_may_control_group($id,get_member()))
		{
			$add_url=build_url(array('page'=>'_SELF','type'=>'add_to','id'=>$id),'_SELF');
		} else $add_url=new ocp_tempcode();

		// To apply
		$my_groups=$GLOBALS['FORUM_DRIVER']->get_members_groups(get_member());
		if (is_guest())
		{
			$apply_url=new ocp_tempcode();
			$apply_text=new ocp_tempcode();
		} else
		{
			if (!in_array($id,$my_groups))
			{
				$apply_url=build_url(array('page'=>'_SELF','type'=>'apply','id'=>$id),'_SELF');
				$apply_text=do_lang_tempcode('APPLY_TO_GROUP');
			} elseif (ocf_get_member_primary_group(get_member())!=$id)
			{
				$apply_url=build_url(array('page'=>'_SELF','type'=>'resign','id'=>$id),'_SELF');
				$apply_text=do_lang_tempcode('RESIGN_FROM_GROUP');
			} else
			{
				$apply_url=new ocp_tempcode();
				$apply_text=new ocp_tempcode();
			}
		}

		require_code('templates_results_table');
		$sortables=array();
		list($sortable,$sort_order)=explode(' ',get_param('p_sort','date_and_time DESC'));

		// Primary members
		$start=get_param_integer('p_start',0);
		$max=get_param_integer('p_max',intval(get_option('primary_members_per_page')));
		$_primary_members=ocf_get_group_members_raw($id,true,true,false,false,$max,$start);
		if (count($_primary_members)>0)
		{
			$max_rows=ocf_get_group_members_raw_count($id,true,true,false,false);
			$primary_members=new ocp_tempcode();
			foreach ($_primary_members as $i=>$primary_member)
			{
				$url=$GLOBALS['FORUM_DRIVER']->member_profile_url($primary_member['gm_member_id'],false,true);
				$temp=do_template('OCF_VIEW_GROUP_MEMBER',array('_GUID'=>'b96b674ac713e9790ecb78c15af1baab','ID'=>strval($primary_member['gm_member_id']),'NAME'=>$primary_member['m_username'],'URL'=>$url));
				$primary_members->attach(results_entry(array($temp)));
			}
			$fields_title=results_field_title(array(do_lang_tempcode('PRIMARY_MEMBERS')),$sortables,'p_sort',$sortable.' '.$sort_order);
			$primary_members=results_table(do_lang_tempcode('PRIMARY_MEMBERS'),$start,'p_start',$max,'p_max',$max_rows,$fields_title,$primary_members,$sortables,$sortable,$sort_order,'p_sort',NULL,NULL,NULL,6);
		} else $primary_members=new ocp_tempcode();

		$edit_url=new ocp_tempcode();

		// Secondary members
		$s_start=get_param_integer('s_start',0);
		$s_max=get_param_integer('s_max',intval(get_option('secondary_members_per_page')));
		$_secondary_members=ocf_get_group_members_raw($id,false,true,true,ocf_may_control_group($id,get_member()),$s_max,$s_start);
		$secondary_members=new ocp_tempcode();
		$prospective_members=new ocp_tempcode();
		$s_max_rows=ocf_get_group_members_raw_count($id,false,false,true,ocf_may_control_group($id,get_member()));
		$d_max_rows=ocf_get_group_members_raw_count($id,false,true,true,ocf_may_control_group($id,get_member()));
		foreach ($_secondary_members as $secondary_member)
		{
			$m_username=$GLOBALS['FORUM_DRIVER']->get_member_row_field($secondary_member['gm_member_id'],'m_username');
			if (is_null($m_username)) continue;
			if ($secondary_member['gm_validated']==1)
			{
				$url=$GLOBALS['FORUM_DRIVER']->member_profile_url($secondary_member['gm_member_id'],false,true);
				$remove_url=build_url(array('page'=>'_SELF','type'=>'remove_from','id'=>$id,'member_id'=>$secondary_member['gm_member_id']),'_SELF');
				$may_control=(ocf_may_control_group($id,get_member()) && (!$secondary_member['implicit']));
				$temp=do_template('OCF_VIEW_GROUP_MEMBER'.($may_control?'_SECONDARY':''),array('ID'=>strval($secondary_member['gm_member_id']),'REMOVE_URL'=>$remove_url,'NAME'=>$m_username,'URL'=>$url));
				$secondary_members->attach(results_entry(array($temp)));
			} elseif (!$add_url->is_empty())
			{
				$url=$GLOBALS['FORUM_DRIVER']->member_profile_url($secondary_member['gm_member_id'],false,true);
				$accept_url=build_url(array('page'=>'_SELF','type'=>'accept','id'=>$id,'member_id'=>$secondary_member['gm_member_id']),'_SELF');
				$decline_url=build_url(array('page'=>'_SELF','type'=>'decline','id'=>$id,'member_id'=>$secondary_member['gm_member_id']),'_SELF');
				$temp=do_template('OCF_VIEW_GROUP_MEMBER_PROSPECTIVE',array('_GUID'=>'16e93cf50a14e3b6a3bdf31525fd5e7f','ID'=>strval($secondary_member['gm_member_id']),'ACCEPT_URL'=>$accept_url,'DECLINE_URL'=>$decline_url,'NAME'=>$m_username,'URL'=>$url));
				$prospective_members->attach(results_entry(array($temp)));
			}
		}
		if (!$secondary_members->is_empty())
		{
			$fields_title=results_field_title(array(do_lang_tempcode('SECONDARY_MEMBERS')),$sortables,'p_sort',$sortable.' '.$sort_order);
			$secondary_members=results_table(do_lang_tempcode('SECONDARY_MEMBERS'),$s_start,'s_start',$s_max,'s_max',$s_max_rows,$fields_title,$secondary_members,$sortables,$sortable,$sort_order,'s_sort',NULL,NULL,NULL,6);
		}
		if (!$prospective_members->is_empty())
		{
			$fields_title=results_field_title(array(do_lang_tempcode('PROSPECTIVE_MEMBERS')),$sortables,'p_sort',$sortable.' '.$sort_order);
			$prospective_members=results_table(do_lang_tempcode('PROSPECTIVE_MEMBERS'),$s_start,'s_start',$s_max,'s_max',$d_max_rows,$fields_title,$prospective_members,$sortables,$sortable,$sort_order,'d_sort',NULL,NULL,NULL,6);
		}
		elseif (has_actual_page_access(get_member(),'cms_ocf_groups',get_module_zone('cms_ocf_groups')))
		{
			$is_super_admin=$group['g_is_super_admin'];
			if ((!has_privilege(get_member(),'control_usergroups')) || ($is_super_admin==1))
			{
				$leader_tmp=$group['g_group_leader'];
				if ($leader_tmp==get_member())
				{
					$edit_url=build_url(array('page'=>'cms_ocf_groups','type'=>'_ed','id'=>$id),get_module_zone('cms_ocf_groups'));
				}
			} else
			{
				$edit_url=build_url(array('page'=>'cms_ocf_groups','type'=>'_ed','id'=>$id),get_module_zone('cms_ocf_groups'));
			}
		}

		if (has_actual_page_access(get_member(),'admin_ocf_groups',get_module_zone('admin_ocf_groups')))
		{
			$edit_url=build_url(array('page'=>'admin_ocf_groups','type'=>'_ed','id'=>$id),get_module_zone('admin_ocf_groups'));
		}

		$club_forum=NULL;
		if ($group['g_is_private_club']==1)
		{
			$club_forum=$GLOBALS['FORUM_DB']->query_select_value_if_there('f_forums f LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'translate t ON t.id=f.f_description','f.id',array('text_original'=>do_lang('FORUM_FOR_CLUB',$group_name)));
		}

		require_javascript('javascript_ajax');
		require_javascript('javascript_ajax_people_lists');

		$forum_id=NULL;
		if ($club)
		{
			$forum_id=$GLOBALS['FORUM_DB']->query_select_value_if_there('f_forums','id',array('f_name'=>$group_name,'f_forum_grouping_id'=>intval(get_option('club_forum_parent_forum_grouping')),'f_parent_forum'=>intval(get_option('club_forum_parent_forum'))));
		}

		$tpl=do_template('OCF_VIEW_GROUP_SCREEN',array(
			'_GUID'=>'fc6cac5c73f92ab4410b492d58976dbe',
			'GROUP_NAME'=>$group_name,
			'ID'=>strval($id),
			'FORUM'=>is_null($forum_id)?'':strval($forum_id),
			'CLUB'=>$club,
			'EDIT_URL'=>$edit_url,
			'TITLE'=>$this->title,
			'LEADER'=>$leader,
			'PROMOTION_INFO'=>$promotion_info,
			'ADD_URL'=>$add_url,
			'APPLY_URL'=>$apply_url,
			'APPLY_TEXT'=>$apply_text,
			'PRIMARY_MEMBERS'=>$primary_members,
			'SECONDARY_MEMBERS'=>$secondary_members,
			'PROSPECTIVE_MEMBERS'=>$prospective_members,
		));

		require_code('templates_internalise_screen');
		return internalise_own_screen($tpl);
	}

	/**
	 * The actualiser to add a member to a usergroup.
	 *
	 * @param  boolean		Whether to skip checking permission for usergroup control
	 * @param  ?string		Username to add (NULL: read from environment)
	 * @return tempcode		The UI
	 */
	function add_to($special_permission=false,$username=NULL)
	{
		$_id=get_param('id');
		if (is_numeric($_id))
		{
			$id=intval($_id);
		} else // Collaboration zone has a text link like this
		{
			$id=$GLOBALS['FORUM_DB']->query_select_value_if_there('f_groups g LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'translate t ON t.id=g.g_name','g.id',array('text_original'=>$_id));
			if (is_null($id)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		}

		if ($id==db_get_first_id()) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));

		if (is_null($username)) $username=trim(post_param('username'));

		if ($username=='') warn_exit(do_lang_tempcode('IMPROPERLY_FILLED_IN'));

		if ((!$special_permission) && (!ocf_may_control_group($id,get_member())))
			access_denied('I_ERROR');

		$member_id=$GLOBALS['FORUM_DRIVER']->get_member_from_username($username);
		if (is_null($member_id)) warn_exit(do_lang_tempcode('_MEMBER_NO_EXIST',escape_html($username)));

		$test=$GLOBALS['FORUM_DRIVER']->get_members_groups($member_id);
		if (in_array($id,$test))
		{
			warn_exit(do_lang_tempcode('ALREADY_IN_GROUP'));
		}

		ocf_add_member_to_group($member_id,$id);

		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$id),'_SELF');
		return redirect_screen($this->title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The actualiser to remove a member from a usergroup.
	 *
	 * @return tempcode		The UI
	 */
	function remove_from()
	{
		$member_id=get_param_integer('member_id');
		$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id,true);
		if (is_null($username)) $username=do_lang('UNKNOWN');

		$id=post_param_integer('id',NULL);
		if (is_null($id))
		{
			$id=get_param_integer('id');

			$post_url=build_url(array('page'=>'_SELF','type'=>get_param('type')),'_SELF',NULL,true);
			$hidden=form_input_hidden('id',strval($id));

			return do_template('CONFIRM_SCREEN',array('_GUID'=>'f98ab98f130646f6fd33fbf85ae3f972','TITLE'=>$this->title,'TEXT'=>do_lang_tempcode('Q_SURE_REMOVE_FROM_GROUP',escape_html($username)),'URL'=>$post_url,'HIDDEN'=>$hidden));
		}

		if (!ocf_may_control_group($id,get_member()))
			access_denied('I_ERROR');

		ocf_member_leave_group($id,$member_id);

		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$id),'_SELF');
		return redirect_screen($this->title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The actualiser to apply to join a usergroup.
	 *
	 * @return tempcode		The UI
	 */
	function apply()
	{
		$group_name=$this->group_name;

		$id=post_param_integer('id',NULL);
		if (is_null($id))
		{
			$id=$this->id;

			$_leader=ocf_get_group_property($id,'group_leader');
			$free_access=(ocf_get_group_property($id,'open_membership')==1);

			$post_url=build_url(array('page'=>'_SELF','type'=>get_param('type')),'_SELF',NULL,true);
			$hidden=form_input_hidden('id',strval($id));

			if ($free_access)
			{
				$text=do_lang_tempcode('ABOUT_TO_APPLY_FREE_ACCESS',escape_html($group_name));
			} else
			{
				if ((is_null($_leader)) || (is_null($GLOBALS['FORUM_DRIVER']->get_username($_leader))))
				{
					$text=do_lang_tempcode('ABOUT_TO_APPLY_STAFF',escape_html($group_name),escape_html(get_site_name()));
				} else
				{
					$leader_username=$GLOBALS['FORUM_DRIVER']->get_username($_leader,true);
					if (is_null($leader_username)) $leader_username=do_lang('UNKNOWN');
					$leader_url=$GLOBALS['FORUM_DRIVER']->member_profile_url($_leader,false,true);
					$text=do_lang_tempcode('ABOUT_TO_APPLY_LEADER',escape_html($group_name),escape_html($leader_username),escape_html($leader_url));
				}
			}

			return do_template('CONFIRM_SCREEN',array('_GUID'=>'ceafde00ade4492c65ed2e6e2309a0e7','TITLE'=>$this->title,'TEXT'=>$text,'URL'=>$post_url,'HIDDEN'=>$hidden));
		}
		if ($id==db_get_first_id()) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));

		$free_access=(ocf_get_group_property($id,'open_membership')==1);

		if (is_guest()) access_denied('I_ERROR');

		require_code('ocf_groups');
		if (ocf_get_group_property($id,'open_membership')==1)
		{
			return $this->add_to(true,$GLOBALS['FORUM_DRIVER']->get_username(get_member()));
		}

		ocf_member_ask_join_group($id,get_member());

		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$id),'_SELF');
		return redirect_screen($this->title,$url,do_lang_tempcode('AWAITING_GROUP_LEADER'));
	}

	/**
	 * The actualiser to accept a member into a usergroup.
	 *
	 * @return tempcode		The UI
	 */
	function accept()
	{
		$id=post_param_integer('id',NULL);
		if (is_null($id))
		{
			$id=get_param_integer('id');

			$post_url=build_url(array('page'=>'_SELF','type'=>get_param('type')),'_SELF',NULL,true);
			$hidden=form_input_hidden('id',strval($id));

			return do_template('CONFIRM_SCREEN',array('_GUID'=>'ebc562534bceb3161a21307633bc229e','TITLE'=>$this->title,'TEXT'=>do_lang_tempcode('Q_SURE'),'URL'=>$post_url,'HIDDEN'=>$hidden));
		}

		if (!ocf_may_control_group($id,get_member()))
			access_denied('I_ERROR');

		ocf_member_validate_into_group($id,get_param_integer('member_id'));

		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$id),'_SELF');
		return redirect_screen($this->title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The actualiser to decline a members joining of a usergroup.
	 *
	 * @return tempcode		The UI
	 */
	function decline()
	{
		$id=post_param_integer('id',NULL);
		if (is_null($id))
		{
			$id=get_param_integer('id');

			require_code('form_templates');

			$text=paragraph(do_lang_tempcode('OPTIONAL_REASON'));
			$submit_name=do_lang_tempcode('DECLINE_FROM_GROUP');
			$post_url=build_url(array('page'=>'_SELF','type'=>get_param('type')),'_SELF',NULL,true);
			$fields=new ocp_tempcode();
			$hidden=form_input_hidden('id',strval($id));
			$fields->attach(form_input_line(do_lang_tempcode('REASON'),'','reason','',false));

			return do_template('FORM_SCREEN',array('_GUID'=>'ebec84204dee305a8db1a57e5a95c774','SKIP_VALIDATION'=>true,'HIDDEN'=>$hidden,'TITLE'=>$this->title,'TEXT'=>$text,'URL'=>$post_url,'FIELDS'=>$fields,'SUBMIT_ICON'=>'buttons__no','SUBMIT_NAME'=>$submit_name));
		}

		if (!ocf_may_control_group($id,get_member()))
			access_denied('I_ERROR');

		$member_id=get_param_integer('member_id');

		ocf_member_validate_into_group($id,$member_id,true,post_param('reason'));

		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$id),'_SELF');
		return redirect_screen($this->title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The actualiser to resign from a usergroup.
	 *
	 * @return tempcode		The UI
	 */
	function resign()
	{
		$id=post_param_integer('id',NULL);
		if (is_null($id))
		{
			$id=get_param_integer('id');

			$post_url=build_url(array('page'=>'_SELF','type'=>get_param('type')),'_SELF',NULL,true);
			$hidden=form_input_hidden('id',strval($id));

			return do_template('CONFIRM_SCREEN',array('_GUID'=>'d9524899fbc243247a9d253cf93c8aa2','TITLE'=>$this->title,'TEXT'=>do_lang_tempcode('Q_SURE'),'URL'=>$post_url,'HIDDEN'=>$hidden));
		}

		ocf_member_leave_group($id,get_member());

		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$id),'_SELF');
		return redirect_screen($this->title,$url,do_lang_tempcode('SUCCESS'));
	}
}


