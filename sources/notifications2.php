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
 * @package		core_notifications
 */

/**
 * Get a map of notification types available to our member.
 *
 * @param  ?MEMBER		Member this is for (NULL: just check globally)
 * @return array			Map of notification types (integer code to language string code)
 */
function _get_available_notification_types($member_id_of=NULL)
{
	$__notification_types=array(
		A_INSTANT_EMAIL=>'INSTANT_EMAIL',
		A_INSTANT_PT=>'INSTANT_PT',
		A_INSTANT_SMS=>'INSTANT_SMS',
		A_DAILY_EMAIL_DIGEST=>'DAILY_EMAIL_DIGEST',
		A_WEEKLY_EMAIL_DIGEST=>'WEEKLY_EMAIL_DIGEST',
		A_MONTHLY_EMAIL_DIGEST=>'MONTHLY_EMAIL_DIGEST',
	);
	$_notification_types=array();
	foreach ($__notification_types as $possible=>$ntype)
	{
		if (_notification_setting_available($possible,$member_id_of))
		{
			$_notification_types[$possible]=$ntype;
		}
	}
	return $_notification_types;
}

/**
 * Put out a user interface for managing notifications overall.
 *
 * @param  MEMBER			Member this is for
 * @return tempcode		UI
 */
function notifications_ui($member_id_of)
{
	require_css('notifications');
	require_code('notifications');
	require_lang('notifications');
	require_javascript('javascript_notifications');
	require_all_lang();

	if (is_guest($member_id_of)) access_denied('NOT_AS_GUEST');

	// UI fields
	$fields=new ocp_tempcode();

	$_notification_types=_get_available_notification_types($member_id_of);
	if (count($_notification_types)==0) return new ocp_tempcode();

	$statistical_notification_type=_find_member_statistical_notification_type($member_id_of);

	$lockdown=collapse_2d_complexity('l_notification_code','l_setting',$GLOBALS['SITE_DB']->query_select('notification_lockdown',array('*')));

	$cnt_post=count($_POST);

	$notification_sections=array();
	$hooks=find_all_hooks('systems','notifications');
	foreach (array_keys($hooks) as $hook)
	{
		if (array_key_exists($hook,$lockdown)) continue;

		if ((substr($hook,0,4)=='ocf_') && (get_forum_type()!='ocf')) continue;
		require_code('hooks/systems/notifications/'.$hook);
		$ob=object_factory('Hook_Notification_'.$hook);
		$_notification_codes=$ob->list_handled_codes();
		foreach ($_notification_codes as $notification_code=>$notification_details)
		{
			if (array_key_exists($notification_code,$lockdown)) continue;

			if ($ob->member_could_potentially_enable($notification_code,$member_id_of))
			{
				$current_setting=notifications_setting($notification_code,NULL,$member_id_of);
				if ($current_setting==A__STATISTICAL) $current_setting=$statistical_notification_type;
				$allowed_setting=$ob->allowed_settings($notification_code);

				$notification_types=array();
				foreach ($_notification_types as $possible=>$ntype)
				{
					$available=(($possible & $allowed_setting) != 0);
					if ($cnt_post!=0)
					{
						$checked=post_param_integer('notification_'.$notification_code.'_'.$ntype,0);
					} else
					{
						$checked=(($possible & $current_setting) != 0)?1:0;
					}
					$notification_types[]=array(
						'NTYPE'=>$ntype,
						'LABEL'=>do_lang_tempcode('ENABLE_NOTIFICATIONS_'.$ntype),
						'CHECKED'=>($checked==1),
						'RAW'=>strval($possible),
						'AVAILABLE'=>$available,
						'SCOPE'=>$notification_code,
					);
				}

				if (!isset($notification_sections[$notification_details[0]]))
				{
					$notification_sections[$notification_details[0]]=array(
						'NOTIFICATION_SECTION'=>$notification_details[0],
						'NOTIFICATION_CODES'=>array(),
					);
				}
				$notification_sections[$notification_details[0]]['NOTIFICATION_CODES'][]=array(
					'NOTIFICATION_CODE'=>$notification_code,
					'NOTIFICATION_LABEL'=>$notification_details[1],
					'NOTIFICATION_TYPES'=>$notification_types,
					'SUPPORTS_CATEGORIES'=>$ob->supports_categories($notification_code),
				);
			}
		}
	}
	if (count($notification_sections)==0) return new ocp_tempcode();

	// Sort labels
	global $M_SORT_KEY;
	$M_SORT_KEY='NOTIFICATION_LABEL';
	ksort($notification_sections);
	foreach (array_keys($notification_sections) as $i)
	{
		usort($notification_sections[$i]['NOTIFICATION_CODES'],'multi_sort');
	}

	// Save
	if (count($_POST)!=0)
	{
		foreach ($notification_sections as $notification_section)
		{
			foreach ($notification_section['NOTIFICATION_CODES'] as $notification_code)
			{
				$new_setting=A_NA;
				foreach ($notification_code['NOTIFICATION_TYPES'] as $notification_type)
				{
					$ntype=$notification_type['NTYPE'];
					if (post_param_integer('notification_'.$notification_code['NOTIFICATION_CODE'].'_'.$ntype,0)==1)
					{
						$new_setting=$new_setting | intval($notification_type['RAW']);
					}
				}
				enable_notifications($notification_code['NOTIFICATION_CODE'],NULL,$member_id_of,$new_setting);
			}
		}
	}

	// Main UI...

	$notification_types_titles=array();
	foreach ($_notification_types as $possible=>$ntype)
	{
		$notification_types_titles[]=array(
			'NTYPE'=>$ntype,
			'LABEL'=>do_lang_tempcode('ENABLE_NOTIFICATIONS_'.$ntype),
			'RAW'=>strval($possible),
		);
	}

	$css_path=get_custom_file_base().'/themes/'.$GLOBALS['FORUM_DRIVER']->get_theme().'/templates_cached/'.user_lang().'/global.css';
	$color='FF00FF';
	if (file_exists($css_path))
	{
		$tmp_file=file_get_contents($css_path);
		$matches=array();
		if (preg_match('#(\s|\})th[\s,][^\}]*(\s|\{)background-color:\s*\#([\dA-Fa-f]*);color:\s*\#([\dA-Fa-f]*);#sU',$tmp_file,$matches)!=0)
		{
			$color=$matches[3].'&fgcolor='.$matches[4];
		}
	}

	$auto_monitor_contrib_content=mixed();
	if (get_forum_type()=='ocf')
	{
		$auto_monitor_contrib_content=strval($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_auto_monitor_contrib_content'));
	}

	return do_template('NOTIFICATIONS_MANAGE',array(
		'_GUID'=>'838165ca739c45c2dcf994bed6fefe3e',
		'COLOR'=>$color,
		'AUTO_NOTIFICATION_CONTRIB_CONTENT'=>$auto_monitor_contrib_content,
		'NOTIFICATION_TYPES_TITLES'=>$notification_types_titles,
		'NOTIFICATION_SECTIONS'=>$notification_sections,
		'MEMBER_ID'=>strval($member_id_of),
	));
}

/**
 * Put out a user interface for managing notifications for a notification-category supporting content type. Also toggle notifications if an ID is passed.
 *
 * @param  ID_TEXT		The notification code to work with
 * @param  ?tempcode		Special message to output if we have toggled to enable (NULL: use standard)
 * @param  ?tempcode		Special message to output if we have toggled to disable (NULL: use standard)
 * @return tempcode		UI
 */
function notifications_ui_advanced($notification_code,$enable_message=NULL,$disable_message=NULL)
{
	require_css('notifications');
	require_code('notifications');
	require_lang('notifications');
	require_javascript('javascript_notifications');
	require_javascript('javascript_notifications');
	require_all_lang();

	$test=$GLOBALS['SITE_DB']->query_value_null_ok('notification_lockdown','l_setting',array(
		'l_notification_code'=>substr($notification_code,0,80),
	));
	if (!is_null($test)) warn_exit(do_lang_tempcode('NOTIFICATION_CODE_LOCKED_DOWN'));

	$ob=_get_notification_ob_for_code($notification_code);
	$info_details=$ob->list_handled_codes();

	$title=get_screen_title('NOTIFICATION_MANAGEMENT_FOR',true,array(escape_html($info_details[$notification_code][1])));

	if (is_guest()) access_denied('NOT_AS_GUEST');

	$db=(substr($notification_code,0,4)=='ocf_')?$GLOBALS['FORUM_DB']:$GLOBALS['SITE_DB'];

	if (is_null($enable_message)) $enable_message=do_lang_tempcode('NOW_ENABLED_NOTIFICATIONS');
	if (is_null($disable_message)) $disable_message=do_lang_tempcode('NOW_DISABLED_NOTIFICATIONS');

	$_notification_types=_get_available_notification_types(get_member());

	$notification_category=get_param('id',NULL);
	if (is_null($notification_category))
	{
		if (count($_POST)!=0) // If we've just saved
		{
			enable_notifications($notification_code,NULL,NULL,A_NA); // Make it clear we've overridden the general value by doing this

			foreach (array_keys($_POST) as $key)
			{
				$matches=array();
				if (preg_match('#^notification\_'.preg_quote($notification_code).'\_category\_(.*)#',$key,$matches)!=0)
				{
					$notification_category=$matches[1];

					$new_setting=A_NA;
					foreach ($_notification_types as $possible=>$ntype)
					{
						if (post_param_integer('notification_'.$notification_category.'_'.$ntype,0)==1)
						{
							$new_setting=$new_setting | $possible;
						}
					}

					enable_notifications($notification_code,$notification_category,NULL,$new_setting);
				}
			}

			attach_message(do_lang_tempcode('SUCCESS'),'inform');

			// Redirect them back
			$redirect=get_param('redirect',NULL);
			if (!is_null($redirect))
			{
				return redirect_screen($title,$redirect,do_lang_tempcode('SUCCESS'));
			}
		}
	} else
	{
		if (notifications_enabled($notification_code,$notification_category))
		{
			attach_message($disable_message,'inform');
		} else
		{
			attach_message($enable_message,'inform');
		}
	}

	$done_get_change=false;
	$tree=_notifications_build_category_tree($_notification_types,$notification_code,$ob,NULL,0,NULL,$done_get_change);
	$notification_category_being_changed=get_param('id',NULL);
	if ($notification_category_being_changed!==null && !$done_get_change) {
		// The tree has been pruned due to over-sizeness issue (too much content to list), so we have to set a notification here rather than during render.
		enable_notifications($notification_code,$notification_category_being_changed);

		// Re-render too
		$tree=_notifications_build_category_tree($_notification_types,$notification_code,$ob,NULL,0,NULL,$done_get_change);
	}

	$notification_types_titles=array();
	foreach ($_notification_types as $possible=>$ntype)
	{
		$notification_types_titles[]=array(
			'NTYPE'=>$ntype,
			'LABEL'=>do_lang_tempcode('ENABLE_NOTIFICATIONS_'.$ntype),
			'RAW'=>strval($possible),
		);
	}

	$css_path=get_custom_file_base().'/themes/'.$GLOBALS['FORUM_DRIVER']->get_theme().'/templates_cached/'.user_lang().'/global.css';
	$color='FF00FF';
	if (file_exists($css_path))
	{
		$tmp_file=file_get_contents($css_path);
		$matches=array();
		if (preg_match('#(\s|\})th[\s,][^\}]*(\s|\{)background-color:\s*\#([\dA-Fa-f]*);color:\s*\#([\dA-Fa-f]*);#sU',$tmp_file,$matches)!=0)
		{
			$color=$matches[3].'&fgcolor='.$matches[4];
		}
	}

	return do_template('NOTIFICATIONS_MANAGE_ADVANCED_SCREEN',array(
		'TITLE'=>$title,
		'COLOR'=>$color,
		'ACTION_URL'=>get_self_url(false,false,array('id'=>NULL)),
		'NOTIFICATION_TYPES_TITLES'=>$notification_types_titles,
		'TREE'=>$tree,
		'NOTIFICATION_CODE'=>$notification_code,
	));
}

/**
 * Build a tree UI for all categories available.
 *
 * @param  array			Notification types
 * @param  ID_TEXT		The notification code to work with
 * @param  object			Notificiation hook object
 * @param  ?ID_TEXT		Category we're looking under (NULL: root)
 * @param  integer		Recursion depth
 * @param  ?boolean		Value to change setting to (NULL: do not change)
 * @param  boolean		Whether we have made a change to the settings
 * @return tempcode		UI
 */
function _notifications_build_category_tree($_notification_types,$notification_code,$ob,$id,$depth,$force_change_children_to,&$done_get_change)
{
	$_notification_categories=$ob->create_category_tree($notification_code,$id);

	$statistical_notification_type=_find_member_statistical_notification_type(get_member());

	$notification_categories=array();
	foreach ($_notification_categories as $c)
	{
		$notification_category=(is_integer($c['id'])?strval($c['id']):$c['id']);

		$current_setting=notifications_setting($notification_code,$notification_category);
		if ($current_setting==A__STATISTICAL) $current_setting=_find_member_statistical_notification_type(get_member());

		$notification_category_being_changed=get_param('id',NULL);
		if (($notification_category_being_changed===$notification_category) || ($force_change_children_to!==NULL))
		{
			if (!$done_get_change)
			{
				if (($force_change_children_to===false/*If recursively disabling*/) || (($force_change_children_to===NULL) && ($current_setting!=A_NA)/*If explicitly toggling this one to disabled*/))
				{
					enable_notifications($notification_code,$notification_category,NULL,A_NA);
					$force_change_children_to_children=false;
				} else
				{
					enable_notifications($notification_code,$notification_category);
					$force_change_children_to_children=true;
				}
				$done_get_change=true;
			} else
			{
				$force_change_children_to_children=false;
			}
		} else
		{
			$force_change_children_to_children=$force_change_children_to;
		}

		$current_setting=notifications_setting($notification_code,$notification_category);
		if ($current_setting==A__STATISTICAL) $current_setting=_find_member_statistical_notification_type(get_member());

		$notification_types=array();
		foreach ($_notification_types as $possible=>$ntype)
		{
			$current_setting=notifications_setting($notification_code,$notification_category);
			if ($current_setting==A__STATISTICAL) $current_setting=$statistical_notification_type;
			$allowed_setting=$ob->allowed_settings($notification_code);

			$available=(($possible & $allowed_setting) != 0);

			if (count($_POST)!=0)
			{
				$checked=post_param_integer('notification_'.$notification_category.'_'.$ntype,0);
			} else
			{
				$checked=(($possible & $current_setting) != 0)?1:0;
			}

			$notification_types[]=array(
				'NTYPE'=>$ntype,
				'LABEL'=>do_lang_tempcode('ENABLE_NOTIFICATIONS_'.$ntype),
				'CHECKED'=>($checked==1),
				'RAW'=>strval($possible),
				'AVAILABLE'=>$available,
				'SCOPE'=>$notification_category,
			);
		}

		if ((!array_key_exists('num_children',$c)) && (array_key_exists('child_count',$c))) $c['num_children']=$c['child_count'];
		if ((!array_key_exists('num_children',$c)) && (array_key_exists('children',$c))) $c['num_children']=count($c['children']);
		$children=new ocp_tempcode();
		if ((array_key_exists('num_children',$c)) && ($c['num_children']!=0))
		{
			$children=_notifications_build_category_tree($_notification_types,$notification_code,$ob,$notification_category,$depth+1,$force_change_children_to_children,$done_get_change);
		}

		$notification_categories[]=array(
			'NUM_CHILDREN'=>strval(array_key_exists('num_children',$c)?$c['num_children']:0),
			'DEPTH'=>strval($depth),
			'NOTIFICATION_CATEGORY'=>$notification_category,
			'NOTIFICATION_TYPES'=>$notification_types,
			'CATEGORY_TITLE'=>$c['title'],
			'CHECKED'=>notifications_enabled($notification_code,$notification_category),
			'CHILDREN'=>$children,
		);
	}

	$tree=do_template('NOTIFICATIONS_TREE',array(
		'NOTIFICATION_CODE'=>$notification_code,
		'NOTIFICATION_CATEGORIES'=>$notification_categories,
	));

	return $tree;
}

/**
 * Copy notification settings from a parent category to a child category.
 *
 * @param  ID_TEXT		Parent category type
 * @param  ID_TEXT		Parent category ID
 * @param  ID_TEXT		Child category ID
 */
function copy_notifications_to_new_child($notification_code,$id,$child_id)
{
	// Copy notifications over to new children
	$_start=0;
	do
	{
		$notifications_to=$GLOBALS['SITE_DB']->query_select('notifications_enabled',array('l_member_id','l_setting'),array('l_notification_code'=>substr($notification_code,0,80),'l_code_category'=>$id),'',100,$_start);

		foreach ($notifications_to as $notification_to)
		{
			$GLOBALS['SITE_DB']->query_insert('notifications_enabled',array(
				'l_member_id'=>$notification_to['l_member_id'],
				'l_notification_code'=>substr($notification_code,0,80),
				'l_code_category'=>$child_id,
				'l_setting'=>$notification_to['l_setting'],
			));
		}

		$_start+=100;
	}
	while (count($notifications_to)!=0);
}
