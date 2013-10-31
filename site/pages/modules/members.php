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
class Module_members
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
		$ret=array('misc'=>'MEMBERS');
		if (!is_guest()) $ret['view']='MY_PROFILE';
		return $ret;
	}

	var $title;
	var $username;
	var $member_id_of;

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
			inform_non_canonical_parameter('md_sort');

			$this->title=get_screen_title('MEMBERS');
		}

		if ($type=='view')
		{
			$username=get_param('id',strval(get_member()));
			if ($username=='') $username=strval(get_member());
			if (is_numeric($username))
			{
				$member_id_of=get_param_integer('id',get_member());
				if (is_guest($member_id_of))
					access_denied('NOT_AS_GUEST');
				$username=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_username');
				if ((is_null($username)) || (is_guest($member_id_of))) warn_exit(do_lang_tempcode('MEMBER_NO_EXIST'));
			} else
			{
				$member_id_of=$GLOBALS['FORUM_DRIVER']->get_member_from_username($username);
				if (is_null($member_id_of)) warn_exit(do_lang_tempcode('_MEMBER_NO_EXIST',escape_html($username)));
			}

			$join_time=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_join_time');

			$privacy_ok=true;
			if (addon_installed('content_privacy'))
			{
				require_code('content_privacy');
				$privacy_ok=has_privacy_access('_photo',strval($member_id_of),get_member());
			}

			$photo_url=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_photo_url');
			if (($photo_url!='') && (addon_installed('ocf_member_photos')) && (has_privilege(get_member(),'view_member_photos')) && ($privacy_ok))
			{
				require_code('images');
				$photo_thumb_url=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_photo_thumb_url');
				$photo_thumb_url=ensure_thumbnail($photo_url,$photo_thumb_url,(strpos($photo_url,'uploads/photos')!==false)?'photos':'ocf_photos','f_members',$member_id_of,'m_photo_thumb_url');
				if (url_is_local($photo_url))
				{
					$photo_url=get_complex_base_url($photo_url).'/'.$photo_url;
				}
				if (url_is_local($photo_thumb_url))
				{
					$photo_thumb_url=get_complex_base_url($photo_thumb_url).'/'.$photo_thumb_url;
				}
			} else
			{
				$photo_url='';
				$photo_thumb_url='';
			}

			$avatar_url=$GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id_of);

			set_extra_request_metadata(array(
				'created'=>date('Y-m-d',$join_time),
				'creator'=>$username,
				'publisher'=>'', // blank means same as creator
				'modified'=>'',
				'type'=>'Profile',
				'title'=>'',
				'identifier'=>'_SEARCH:members:view:'.strval($member_id_of),
				'description'=>'',
				'image'=>(($avatar_url=='') && (has_privilege(get_member(),'view_member_photos')))?$photo_url:$avatar_url,
			));

			breadcrumb_set_parents(array(array('_SELF:_SELF:misc'.propagate_ocselect_pagelink(),do_lang_tempcode('MEMBERS'))));

			if ((get_value('no_awards_in_titles')!=='1') && (addon_installed('awards')))
			{
				require_code('awards');
				$awards=find_awards_for('member',strval($member_id_of));
			} else $awards=array();

			//$this->title=get_screen_title('MEMBER_PROFILE',true,array(make_fractionable_editable('member',$member_id_of,$username)),NULL,$awards);
			$displayname=$GLOBALS['FORUM_DRIVER']->get_username($member_id_of,true);
			$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id_of);
			$this->title=get_screen_title('MEMBER_PROFILE',true,array(escape_html($displayname),escape_html($username)),NULL,$awards);

			$this->member_id_of=$member_id_of;
			$this->username=$username;
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

		$type=get_param('type','misc');

		if ($type=='misc') return $this->directory();
		if ($type=='view') return $this->profile();

		return new ocp_tempcode();
	}

	/**
	 * The UI to show the member directory.
	 *
	 * @return tempcode		The UI
	 */
	function directory()
	{
		require_javascript('javascript_ajax');
		require_javascript('javascript_ajax_people_lists');

		$get_url=get_self_url(true);
		$hidden=build_keep_form_fields('_SELF',true,array('filter'));

		$start=get_param_integer('md_start',0);
		$max=get_param_integer('md_max',intval(get_option('members_per_page')));
		$sortables=array(
			'm_username'=>do_lang_tempcode('USERNAME'),
			'm_cache_num_posts'=>do_lang_tempcode('COUNT_POSTS'),
			'm_join_time'=>do_lang_tempcode('JOIN_DATE'),
			'm_last_visit_time'=>do_lang_tempcode('LAST_VISIT_TIME'),
		);
		$default_sort_order=get_option('md_default_sort_order');
		$test=explode(' ',get_param('md_sort',$default_sort_order),2);
		if (count($test)==1) $test[]='ASC';
		list($sortable,$sort_order)=$test;
		if (((strtoupper($sort_order)!='ASC') && (strtoupper($sort_order)!='DESC')) || (!array_key_exists($sortable,$sortables)))
			log_hack_attack_and_exit('ORDERBY_HACK');

		$group_filter=get_param('group_filter','');

		$_usergroups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(true,false,false,($group_filter=='')?NULL:array(intval($group_filter)));
		$usergroups=array();
		require_code('ocf_groups2');
		foreach ($_usergroups as $group_id=>$group)
		{
			$num=ocf_get_group_members_raw_count($group_id,true);
			$usergroups[$group_id]=array('USERGROUP'=>$group,'NUM'=>strval($num));
		}

		// ocSelect
		$ocselect=either_param('active_filter','');
		if ($ocselect!='')
		{
			require_code('ocselect');
			$content_type='member';
			list($ocselect_extra_select,$ocselect_extra_join,$ocselect_extra_where)=ocselect_to_sql($GLOBALS['SITE_DB'],parse_ocselect($ocselect),$content_type,'');
			$extra_select_sql=implode('',$ocselect_extra_select);
			$extra_join_sql=implode('',$ocselect_extra_join);
		} else
		{
			$extra_select_sql='';
			$extra_join_sql='';
			$ocselect_extra_where='';
		}

		$where_clause='id<>'.strval(db_get_first_id()).$ocselect_extra_where;
		if ((!has_privilege(get_member(),'see_unvalidated')) && (addon_installed('unvalidated'))) $where_clause.=' AND m_validated=1';

		if ($group_filter!='')
		{
			if (is_numeric($group_filter))
				$this->title=get_screen_title('USERGROUP',true,array($usergroups[intval($group_filter)]['USERGROUP']));

			require_code('ocfiltering');
			$filter=ocfilter_to_sqlfragment($group_filter,'m_primary_group','f_groups',NULL,'m_primary_group','id');
			$where_clause.=' AND '.$filter;
		}
		$search=get_param('filter','');
		if ($search!='')
		{
			$where_clause.=' AND (m_username LIKE \''.db_encode_like(str_replace('*','%',$search)).'\'';
			if (has_privilege(get_member(),'member_maintenance'))
				$where_clause.=' OR m_email_address LIKE \''.db_encode_like(str_replace('*','%',$search)).'\'';
			$where_clause.=')';
		}
		$query='FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members r'.$extra_join_sql.' WHERE '.$where_clause;

		$max_rows=$GLOBALS['FORUM_DB']->query_value_if_there('SELECT COUNT(DISTINCT r.id) '.$query);

		if (can_arbitrary_groupby()) $query.=' GROUP BY r.id';
		if ($sortable=='m_join_time' || $sortable=='m_last_visit_time')
		{
			$query.=' ORDER by '.$sortable.' '.$sort_order.','.'id '.$sort_order; // Also order by ID, in case lots joined at the same time
		} else
		{
			$query.=' ORDER BY '.$sortable.' '.$sort_order;
		}
		$rows=$GLOBALS['FORUM_DB']->query('SELECT r.*'.$extra_select_sql.' '.$query,$max,$start);
		$rows=remove_duplicate_rows($rows,'id');

		$members=new ocp_tempcode();
		$member_boxes=array();
		require_code('templates_results_table');
		$_fields_title=array(do_lang_tempcode('USERNAME'),do_lang_tempcode('PRIMARY_GROUP'),do_lang_tempcode('COUNT_POSTS'));
		if (get_option('use_lastondate')=='1')
			$_fields_title[]=do_lang_tempcode('LAST_VISIT_TIME');
		if (get_option('use_joindate')=='1')
			$_fields_title[]=do_lang_tempcode('JOIN_DATE');
		$fields_title=results_field_title($_fields_title,$sortables,'md_sort',$sortable.' '.$sort_order);
		require_code('ocf_members2');
		foreach ($rows as $row)
		{
			$link=$GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($row['id'],true,$row['m_username'],false);
			$url=$GLOBALS['FORUM_DRIVER']->member_profile_url($row['id'],true);
			if ($row['m_validated']==0) $link->attach(do_lang_tempcode('MEMBER_IS_UNVALIDATED'));
			if ($row['m_validated_email_confirm_code']!='') $link->attach(do_lang_tempcode('MEMBER_IS_UNCONFIRMED'));
			$member_primary_group=ocf_get_member_primary_group($row['id']);
			$primary_group=ocf_get_group_link($member_primary_group);

			$_entry=array($link,$primary_group,integer_format($row['m_cache_num_posts']));
			if (get_option('use_joindate')=='1')
				$_entry[]=escape_html(get_timezoned_date($row['m_join_time']));
			if (get_option('use_lastondate')=='1')
				$_entry[]=escape_html(get_timezoned_date($row['m_last_visit_time']));
			$members->attach(results_entry($_entry));

			$box=render_member_box($row['id'],true,NULL,NULL,true,NULL,false);
			$member_boxes[]=$box;
		}
		$results_table=(count($rows)==0)?new ocp_tempcode():results_table(do_lang_tempcode('MEMBERS'),$start,'md_start',$max,'md_max',$max_rows,$fields_title,$members,$sortables,$sortable,$sort_order,'md_sort');

		$other_ids=array();
		$_max_rows_to_preload=get_value('max_rows_to_preload');
		$max_rows_to_preload=is_null($_max_rows_to_preload)?500:intval($_max_rows_to_preload);
		if (($max_rows<$max_rows_to_preload) && ($max_rows>count($rows)))
		{
			$query='FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members r'.$extra_join_sql.' WHERE '.$where_clause;
			$or_list='';
			foreach ($rows as $row)
			{
				$or_list=' AND r.id<>'.strval($row['id']);
			}
			$rows=$GLOBALS['FORUM_DB']->query('SELECT r.id'.$extra_select_sql.' '.$query.$or_list);
			foreach ($rows as $row)
			{
				$other_ids[]=strval($row['id']);
			}
		}

		require_code('templates_pagination');
		$pagination=pagination(do_lang_tempcode('MEMBERS'),$start,'md_start',$max,'md_max',$max_rows,true);

		$symbols=NULL;
		if (get_option('allow_alpha_search')=='1')
		{
			$alpha_query=$GLOBALS['FORUM_DB']->query('SELECT m_username FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE id<>'.strval(db_get_first_id()).' ORDER BY m_username ASC');
			$symbols=array(array('START'=>'0','SYMBOL'=>do_lang('ALL')),array('START'=>'0','SYMBOL'=>'#'));
			foreach (array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z') as $s)
			{
				foreach ($alpha_query as $i=>$q)
				{
					if (strtolower(substr($q['m_username'],0,1))==$s)
					{
						break;
					}
				}
				if (substr(strtolower($q['m_username']),0,1)!=$s) $i=intval($symbols[count($symbols)-1]['START']);
				$symbols[]=array('START'=>strval(intval($max*floor(floatval($i)/floatval($max)))),'SYMBOL'=>$s);
			}
		}

		$tpl=do_template('OCF_MEMBER_DIRECTORY_SCREEN',array(
			'_GUID'=>'096767e9aaabce9cb3e6591b7bcf95b8',
			'MAX'=>strval($max),
			'PAGINATION'=>$pagination,
			'MEMBER_BOXES'=>$member_boxes,
			'OTHER_IDS'=>$other_ids,
			'USERGROUPS'=>$usergroups,
			'HIDDEN'=>$hidden,
			'SYMBOLS'=>$symbols,
			'SEARCH'=>$search,
			'GET_URL'=>$get_url,
			'TITLE'=>$this->title,
			'RESULTS_TABLE'=>$results_table,
		));

		require_code('templates_internalise_screen');
		return internalise_own_screen($tpl);
	}

	/**
	 * The UI to show a member's profile.
	 *
	 * @return tempcode		The UI
	 */
	function profile()
	{
		disable_php_memory_limit();

		require_code('ocf_profiles');
		return render_profile_tabset($this->title,$this->member_id_of,get_member(),$this->username);
	}

}

