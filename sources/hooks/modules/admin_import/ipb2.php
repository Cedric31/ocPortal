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
 * @package		import
 */

require_code('hooks/modules/admin_import/shared/ipb');

class Hook_ipb2 extends Hook_ipb_base
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['supports_advanced_import']=false;
		$info['product']='Invision Board 2.0.x';
		$info['prefix']='ibf_';
		$info['import']=array(
								'ocf_groups',
								'ocf_members',
								'ocf_member_files',
								'custom_comcode',
								'ocf_custom_profile_fields',
								'ocf_categories',
								'ocf_forums',
								'ocf_topics',
								'ocf_posts',
								'ocf_post_files',
								'ocf_polls_and_votes',
								'ocf_multi_moderations',
								'notifications',
								'ocf_personal_topics',
								'ocf_warnings',
								'wordfilter',
								'config',
								'calendar',
							);
		$info['dependencies']=array( // This dependency tree is overdefined, but I wanted to make it clear what depends on what, rather than having a simplified version
								'ocf_members'=>array('ocf_groups'),
								'ocf_member_files'=>array('ocf_members'),
								'ocf_forums'=>array('ocf_categories','ocf_members','ocf_groups'),
								'ocf_topics'=>array('ocf_forums','ocf_members'),
								'ocf_polls_and_votes'=>array('ocf_topics','ocf_members'),
								'ocf_posts'=>array('custom_comcode','ocf_topics','ocf_members'),
								'ocf_post_files'=>array('ocf_posts'),
								'ocf_multi_moderations'=>array('ocf_forums'),
								'notifications'=>array('ocf_topics','ocf_members'),
								'ocf_personal_topics'=>array('custom_comcode','ocf_members'),
								'ocf_warnings'=>array('ocf_members'),
								'calendar'=>array('ocf_members'),
							);
		$_cleanup_url=build_url(array('page'=>'admin_cleanup'),get_module_zone('admin_cleanup'));
		$cleanup_url=$_cleanup_url->evaluate();
		$info['message']=(get_param('type','misc')!='import' && get_param('type','misc')!='hook')?new ocp_tempcode():do_lang_tempcode('FORUM_CACHE_CLEAR',escape_html($cleanup_url));
	
		return $info;
	}

	/**
	 * Standard import function.
	 *
	 * @param  object			The DB connection to import from
	 * @param  string			The table prefix the target prefix is using
	 * @param  PATH			The base directory we are importing from
	 */
	function import_custom_comcode($db,$table_prefix,$old_base_dir)
	{
		$rows=$db->query('SELECT * FROM '.$table_prefix.'custom_bbcode');
		foreach ($rows as $row)
		{
			if (import_check_if_imported('custom_comcode',strval($row['bbcode_id']))) continue;
	
			global $VALID_COMCODE_TAGS;
			$test=$GLOBALS['SITE_DB']->query_value_null_ok('custom_comcode','tag_tag',array('tag_tag'=>$row['bbcode_tag']));
			if ((array_key_exists($row['bbcode_tag'],$VALID_COMCODE_TAGS)) || (!is_null($test)))
			{
				import_id_remap_put('custom_comcode',strval($row['bbcode_id']),1);
				continue;
			}

			$GLOBALS['SITE_DB']->query_insert('custom_comcode',array(
				'tag_tag'=>$row['bbcode_tag'],
				'tag_title'=>insert_lang($row['bbcode_title'],3),
				'tag_description'=>insert_lang($row['bbcode_desc'],3),
				'tag_replace'=>$row['bbcode_replace'],
				'tag_example'=>$row['bbcode_example'],
				'tag_parameters'=>'',
				'tag_enabled'=>1,
				'tag_dangerous_tag'=>0,
				'tag_block_tag'=>0,
				'tag_textual_tag'=>1
			));
	
			import_id_remap_put('custom_comcode',strval($row['bbcode_id']),1);
		}
	}
	
	/**
	 * Standard import function.
	 *
	 * @param  object			The DB connection to import from
	 * @param  string			The table prefix the target prefix is using
	 * @param  PATH			The base directory we are importing from
	 */
	function import_ocf_categories($db,$table_prefix,$old_base_dir)
	{
		$rows=$db->query('SELECT * FROM '.$table_prefix.'forums WHERE parent_id=-1 ORDER BY id');
		foreach ($rows as $row)
		{
			if (import_check_if_imported('category',strval($row['id']))) continue;
	
			if ($row['id']==-1) continue;
	
			$title=@html_entity_decode($row['name'],ENT_QUOTES,get_charset());
	
			$test=$GLOBALS['FORUM_DB']->query_value_null_ok('f_categories','id',array('c_title'=>$title));
			if (!is_null($test))
			{
				import_id_remap_put('category',strval($row['id']),$test);
				continue;
			}

			$description=strip_tags(@html_entity_decode($row['description'],ENT_QUOTES,get_charset()));
			$expanded_by_default=1;

			$id_new=ocf_make_category($title,$description,$expanded_by_default);

			import_id_remap_put('category',strval($row['id']),$id_new);
		}
	}
	
	/**
	 * Standard import function.
	 *
	 * @param  object			The DB connection to import from
	 * @param  string			The table prefix the target prefix is using
	 * @param  PATH			The base directory we are importing from
	 */
	function import_ocf_forums($db,$table_prefix,$old_base_dir)
	{
		require_code('ocf_forums_action2');

		$remap_id=array();
		$rows=$db->query('SELECT * FROM '.$table_prefix.'forums WHERE parent_id<>-1 ORDER BY id');
		foreach ($rows as $row_number=>$row)
		{
			$remapped=import_id_remap_get('forum',strval($row['id']),true);
			if (!is_null($remapped))
			{
				$remap_id[$row['id']]=$remapped;
				$rows[$row_number]['parent_id']=NULL;
				continue;
			}
	
			if ($row['id']==-1) continue;
	
			$name=@html_entity_decode($row['name'],ENT_QUOTES,get_charset());
			$description=strip_tags(@html_entity_decode($row['description'],ENT_QUOTES,get_charset()));
	
			// To determine whether parent_id specifies category or parent, we must check status of what it is pointing at
			$parent_test=$db->query('SELECT use_ibc,parent_id FROM '.$table_prefix.'forums WHERE id='.strval((integer)$row['parent_id']));
			if ($parent_test[0]['parent_id']!=-1) // Pointing to parent
			{
				$parent_forum=import_id_remap_get('forum',strval($row['parent_id']),true);
				if (!is_null($parent_forum)) $rows[$row_number]['parent_id']=NULL; // Mark it as good (we do not need to fix this parenting)
				$category_id=db_get_first_id();
			} else // Pointing to category
			{
				$category_id=import_id_remap_get('category',strval($row['parent_id']));
				$parent_forum=db_get_first_id();
				$rows[$row_number]['parent_id']=NULL; // Mark it as good (we do not need to fix this parenting)
			}
	
			$position=$row['position'];
			$post_count_increment=$row['inc_postcount'];
	
			$permissions=unserialize(stripslashes($row['permission_array']));
			$_all_groups=array_unique(explode(',',$permissions['start_perms'].','.$permissions['reply_perms'].','.$permissions['read_perms']));
			$level2_groups=explode(',',$permissions['read_perms']);
			$level3_groups=explode(',',$permissions['reply_perms']);
			$level4_groups=explode(',',$permissions['start_perms']);
			$access_mapping=array();
			foreach ($_all_groups as $old_group)
			{
				$new_group=import_id_remap_get('group',$old_group,true);
				if (is_null($new_group)) continue;
	
				if (in_array($old_group,$level4_groups)) $access_mapping[$new_group]=4;
				elseif (in_array($old_group,$level3_groups)) $access_mapping[$new_group]=3;
				elseif (in_array($old_group,$level2_groups)) $access_mapping[$new_group]=2;
				else $access_mapping[$new_group]=0;
			}
	
			$id_new=ocf_make_forum($name,$description,$category_id,$access_mapping,$parent_forum,$position,$post_count_increment);
	
			$remap_id[$row['id']]=$id_new;
			import_id_remap_put('forum',strval($row['id']),$id_new);
		}

		// Now we must fix parenting
		foreach ($rows as $row)
		{
			if (!is_null($row['parent_id']))
			{
				$parent_id=$remap_id[$row['parent_id']];
				$GLOBALS['FORUM_DB']->query_update('f_forums',array('f_parent_forum'=>$parent_id),array('id'=>$remap_id[$row['id']]),'',1);
			}
		}
	}
	
	/**
	 * Standard import function.
	 *
	 * @param  object			The DB connection to import from
	 * @param  string			The table prefix the target prefix is using
	 * @param  PATH			The base directory we are importing from
	 */
	function import_config($db,$table_prefix,$file_base)
	{
		$config_remapping=array(
			'board_offline'=>'site_closed',
			'offline_msg'=>'closed',
			'au_cutoff'=>'users_online_time',
			'email_out'=>'smtp_from_address',
			'email_in'=>'staff_address',
			'smtp_host'=>'smtp_sockets_host',
			'smtp_port'=>'smtp_sockets_port',
			'smtp_user'=>'smtp_sockets_username',
			'smtp_pass'=>'smtp_sockets_password',
			'home_name'=>'site_name',
			'reg_auth_type'=>'require_new_member_validation',
			'etfilter_shout'=>'prevent_shouting',
	/*		'show_max_msg_list'=>'forum_posts_per_page'  */
		);
	
		$rows=$db->query('SELECT * FROM '.$table_prefix.'conf_settings');
		$INFO=array();
		foreach ($rows as $row)
		{
			if ($row['conf_value']=='') $row['conf_value']=$row['conf_default'];
			if (array_key_exists($row['conf_key'],$config_remapping))
			{
				set_option($config_remapping[$row['conf_key']],$row['conf_value']);
			}
			$INFO[$row['conf_key']]=$row['conf_value'];
		}

		set_option('session_expiry_time',strval(60*intval($INFO['session_expiration'])));
		set_option('gzip_output',strval(1-intval($INFO['disable_gzip'])));
		set_option('smtp_sockets_use',($INFO['mail_method']=='smtp')?'1':'0');
		set_option('session_expiry_time',strval(60*intval($INFO['session_expiration'])));
		set_value('timezone',$INFO['time_offset']);
	
		// Now some usergroup options
		$groups=$GLOBALS['OCF_DRIVER']->get_usergroup_list();
		list($width,$height)=explode('x',$INFO['avatar_dims']);
		$GLOBALS['SITE_DB']->query_delete('group_page_access',array('page_name'=>'search','zone_name'=>get_module_zone('search')));
		$GLOBALS['SITE_DB']->query_delete('group_page_access',array('page_name'=>'join','zone_name'=>get_module_zone('join')));
		$super_admin_groups=$GLOBALS['OCF_DRIVER']->_get_super_admin_groups();
		foreach (array_keys($groups) as $id)
		{
			if (in_array($id, $super_admin_groups)) continue;
	
			if ($INFO['allow_search']=='0')
				$GLOBALS['SITE_DB']->query_insert('group_page_access',array('page_name'=>'search','zone_name'=>get_module_zone('search'),'group_id'=>$id));
			if ($INFO['no_reg']=='1')
				$GLOBALS['SITE_DB']->query_insert('group_page_access',array('page_name'=>'join','zone_name'=>get_module_zone('join'),'group_id'=>$id));
	
			$GLOBALS['FORUM_DB']->query_update('f_groups',array('g_flood_control_submit_secs'=>intval($INFO['flood_control']),'g_max_avatar_width'=>$width,'g_max_avatar_height'=>$height,'g_max_sig_length_comcode'=>$INFO['max_sig_length'],'g_max_post_length_comcode'=>$INFO['max_post_length']),array('id'=>$id),'',1);
		}
	}
	
	/**
	 * Standard import function.
	 *
	 * @param  object			The DB connection to import from
	 * @param  string			The table prefix the target prefix is using
	 * @param  PATH			The base directory we are importing from
	 */
	function import_ocf_personal_topics($db,$table_prefix,$old_base_dir)
	{
		$rows=$db->query('SELECT * FROM '.$table_prefix.'message_topics m LEFT JOIN '.$table_prefix.'message_text t ON m.mt_msg_id=t.msg_id WHERE mt_vid_folder<>\'sent\' ORDER BY mt_date');
	
		// Group them up into what will become topics
		$groups=array();
		foreach ($rows as $row)
		{
			if ($row['mt_from_id']>$row['mt_to_id'])
			{
				$a=$row['mt_to_id'];
				$b=$row['mt_from_id'];
			} else
			{
				$a=$row['mt_from_id'];
				$b=$row['mt_to_id'];
			}
			$title=str_replace('Re: ','',$row['mt_title']);
			$title=str_replace('RE: ','',$title);
			$title=str_replace('Re:','',$title);
			$title=str_replace('RE:','',$title);
			$groups[strval($a).':'.strval($b).':'.@html_entity_decode($title,ENT_QUOTES,get_charset())][]=$row;
		}
	
		// Import topics
		foreach ($groups as $group)
		{
			$row=$group[0];
	
			if (import_check_if_imported('pt',strval($row['mt_msg_id']))) continue;
	
			// Create topic
			$from_id=import_id_remap_get('member',strval($row['mt_from_id']),true);
			if (is_null($from_id)) $from_id=$GLOBALS['OCF_DRIVER']->get_guest_id();
			$to_id=import_id_remap_get('member',strval($row['mt_to_id']),true);
			if (is_null($to_id)) $to_id=$GLOBALS['OCF_DRIVER']->get_guest_id();
			$topic_id=ocf_make_topic(NULL,'','',1,1,0,0,0,$from_id,$to_id,false);
	
			$first_post=true;
			foreach ($group as $_postdetails)
			{
				if ($first_post)
				{
					$title=@html_entity_decode($row['mt_title'],ENT_QUOTES,get_charset());
				} else 
				{
					$title='';
					if (get_param_integer('keep_import_test',0)==1) continue;
				}

				$post=str_replace('$','[html]$[/html]',$this->clean_ipb_post($_postdetails['msg_post']));
				$validated=1;
				$from_id=import_id_remap_get('member',strval($_postdetails['mt_from_id']),true);
				if (is_null($from_id)) $from_id=$GLOBALS['OCF_DRIVER']->get_guest_id();
				$poster_name_if_guest=$GLOBALS['OCF_DRIVER']->get_member_row_field($from_id,'m_username');
				$ip_address=$GLOBALS['OCF_DRIVER']->get_member_row_field($from_id,'m_ip_address');
				$time=$_postdetails['mt_date'];
				$poster=$from_id;
				$last_edit_time=NULL;
				$last_edit_by=NULL;
	
				ocf_make_post($topic_id,$title,$post,0,$first_post,$validated,0,$poster_name_if_guest,$ip_address,$time,$poster,NULL,$last_edit_time,$last_edit_by,false,false,NULL,false);
				$first_post=false;
			}

			import_id_remap_put('pt',strval($row['mt_msg_id']),$topic_id);
		}
	}

}


