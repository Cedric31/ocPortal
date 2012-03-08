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
 * @package		core_ocf
 */

/**
 * Standard code module initialisation function.
 */
function init__ocf_posts_action()
{
	global $ALL_FORUM_POST_COUNT_INFO;
	$ALL_FORUM_POST_COUNT_INFO=NULL;
}

/**
 * Check a post would be valid.
 *
 * @param  LONG_TEXT		The post.
 * @param  ?AUTO_LINK	The ID of the topic the post would be in (NULL: don't check with regard to any particular topic).
 * @param  ?MEMBER		The poster (NULL: current member).
 * @return ?array			Row of the existing post if a double post (single row map-element in a list of rows) (NULL: not a double post).
 */
function ocf_check_post($post,$topic_id=NULL,$poster=NULL)
{
	if (is_null($poster)) $poster=get_member();

	require_code('comcode_check');
	check_comcode($post,NULL,false,NULL,true);

	if (strlen($post)==0)
	{
		warn_exit(do_lang_tempcode('POST_TOO_SHORT'));
	}
	require_code('ocf_groups');
	if (strlen($post)>ocf_get_member_best_group_property($poster,'max_post_length_comcode'))
	{
		warn_exit(make_string_tempcode(escape_html(do_lang('_POST_TOO_LONG'))));
	}

	if (!is_null($topic_id))
	{
		if (running_script('stress_test_loader')) return NULL;

		// Check this isn't the same as the last post here
		$last_posts=$GLOBALS['FORUM_DB']->query_select('f_posts',array('p_post','p_poster','p_ip_address'),array('p_topic_id'=>$topic_id),'ORDER BY p_time DESC,id DESC',1);
		if (array_key_exists(0,$last_posts))
		{
			if (($last_posts[0]['p_poster']==$GLOBALS['OCF_DRIVER']->get_guest_id()) && (get_ip_address()!=$last_posts[0]['p_ip_address']))
				$last_posts[0]['p_poster']=-1;
			if (($last_posts[0]['p_poster']==$poster) && (get_translated_text($last_posts[0]['p_post'],$GLOBALS['FORUM_DB'])==$post))
				warn_exit(do_lang_tempcode('DOUBLE_POST_PREVENTED'));
		}

		return $last_posts;
	}
}

/**
 * Add a post.
 *
 * @param  AUTO_LINK		The ID of the topic to add the post to.
 * @param  SHORT_TEXT	The title of the post (may be blank).
 * @param  LONG_TEXT		The post.
 * @param  BINARY			Whether to skip showing the posters signature in the post.
 * @param  boolean		Whether the post is the first in the topic.
 * @param  ?BINARY		Whether the post is validated (NULL: unknown, find whether it needs to be marked unvalidated initially).
 * @param  BINARY			Whether the post is marked emphasised.
 * @param  ?string		The name of the person making the post (NULL: username of current member).
 * @param  ?IP				The IP address the post is to be made under (NULL: IP of current user).
 * @param  ?TIME			The time of the post (NULL: now).
 * @param  ?MEMBER		The poster (NULL: current member).
 * @param  ?MEMBER		The member that this post is intended solely for (NULL: public).
 * @param  ?TIME			The last edit time of the post (NULL: never edited).
 * @param  ?MEMBER		The member that was last to edit the post (NULL: never edited).
 * @param  boolean		Whether to check permissions for whether the post may be made as it is given.
 * @param  boolean		Whether to update the caches after making the post.
 * @param  ?AUTO_LINK	The forum the post will be in (NULL: find out from the DB).
 * @param  boolean		Whether to allow attachments in this post.
 * @param  ?string		The title of the topic (NULL: find from the DB).
 * @param  BINARY			Whether the topic is a sunk topic.
 * @param  ?AUTO_LINK 	Force an ID (NULL: don't force an ID)
 * @param  boolean		Whether to make the post anonymous
 * @param  boolean		Whether to skip post checks
 * @param  boolean		Whether this is for a new Private Topic
 * @param  boolean		Whether to explicitly insert the Comcode with admin privileges
 * @param  ?AUTO_LINK	Parent post ID (NULL: none-threaded/root-of-thread)
 * @return AUTO_LINK		The ID of the new post.
 */
function ocf_make_post($topic_id,$title,$post,$skip_sig=0,$is_starter=false,$validated=NULL,$is_emphasised=0,$poster_name_if_guest=NULL,$ip_address=NULL,$time=NULL,$poster=NULL,$intended_solely_for=NULL,$last_edit_time=NULL,$last_edit_by=NULL,$check_permissions=true,$update_cacheing=true,$forum_id=NULL,$support_attachments=true,$topic_title='',$sunk=0,$id=NULL,$anonymous=false,$skip_post_checks=false,$is_pt=false,$insert_comcode_as_admin=false,$parent_id=NULL)
{
	if (is_null($poster)) $poster=get_member();

	if ($check_permissions)
	{
		if (strlen($title)>120)
		{
			warn_exit(do_lang_tempcode('TITLE_TOO_LONG'));
		}

		if (get_option('prevent_shouting')=='1')
		{
			if (strtoupper($title)==$title) $title=ucwords($title);
		}

		if ((is_null($intended_solely_for)) && (!$skip_post_checks))
		{
			ocf_check_post($post,$topic_id,$poster);
		}
	}

	if (is_null($ip_address)) $ip_address=get_ip_address();
	if (is_null($time))
	{
		$time=time();
		$send_notification=true;
	} else
	{
		$send_notification=false;
	}
	if (is_null($poster_name_if_guest))
	{
		if (($poster==$GLOBALS['OCF_DRIVER']->get_guest_id()) || ($anonymous))
		{
			$poster_name_if_guest=do_lang('GUEST');
		}
		else
		{
			$poster_name_if_guest=$GLOBALS['OCF_DRIVER']->get_username($poster);
			if (is_null($poster_name_if_guest)) $poster_name_if_guest=do_lang('UNKNOWN');
		}
	}

	if ((is_null($forum_id)) || (($topic_title=='') && (!$is_starter)))
	{
		$info=$GLOBALS['FORUM_DB']->query_select('f_topics',array('t_is_open','t_pt_from','t_pt_to','t_forum_id','t_cache_last_member_id','t_cache_first_title'),array('id'=>$topic_id),'',1);
		if (!array_key_exists(0,$info))
		{
			warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		}
		$forum_id=$info[0]['t_forum_id'];
		$topic_title=$info[0]['t_cache_first_title'];
		if ($topic_title=='') $topic_title=$title;

		if ($check_permissions)
		{
			if (((($info[0]['t_pt_from']!=get_member()) && ($info[0]['t_pt_to']!=get_member()) && (!ocf_has_special_pt_access($topic_id))) && (!has_specific_permission(get_member(),'view_other_pt')) && (is_null($forum_id))))
				access_denied('I_ERROR');
		}
	}
	if (is_null($forum_id))
	{
		if (($check_permissions) && ($poster==$GLOBALS['OCF_DRIVER']->get_guest_id()))
			access_denied('I_ERROR');
		$validated=1; // Personal posts always validated
	} else
	{
		if ($check_permissions)
		{
			if (($info[0]['t_is_open']==0) && (!ocf_may_moderate_forum($forum_id))) access_denied('I_ERROR');

			$last_member_id=$info[0]['t_cache_last_member_id'];
			if (!ocf_may_post_in_topic($forum_id,$topic_id,$last_member_id))
				access_denied('I_ERROR');
		}
	}

	if ((is_null($validated)) || (($validated==1) && ($check_permissions)))
	{
		if ((!is_null($forum_id)) && (!has_specific_permission(get_member(),'bypass_validation_lowrange_content','topics',array('forums',$forum_id)))) $validated=0; else $validated=1;
	}
	
	if (!$support_attachments)
	{
		$lang_id=insert_lang_comcode($post,4,$GLOBALS['FORUM_DB'],$insert_comcode_as_admin);
	} else
	{
		$lang_id=0;
	}

	if (!addon_installed('unvalidated')) $validated=1;
	$map=array(
		'p_title'=>$title,
		'p_post'=>$lang_id,
		'p_ip_address'=>$ip_address,
		'p_time'=>$time,
		'p_poster'=>$anonymous?db_get_first_id():$poster,
		'p_poster_name_if_guest'=>$poster_name_if_guest,
		'p_validated'=>$validated,
		'p_topic_id'=>$topic_id,
		'p_is_emphasised'=>$is_emphasised,
		'p_cache_forum_id'=>$forum_id,
		'p_last_edit_time'=>$last_edit_time,
		'p_last_edit_by'=>$last_edit_by,
		'p_intended_solely_for'=>$intended_solely_for,
		'p_skip_sig'=>$skip_sig,
		'p_parent_id'=>$parent_id
	);
	if (!is_null($id)) $map['id']=$id;
	$post_id=$GLOBALS['FORUM_DB']->query_insert('f_posts',$map,true);

	if ($support_attachments)
	{
		require_code('attachments2');
		$lang_id=insert_lang_comcode_attachments(4,$post,'ocf_post',strval($post_id),$GLOBALS['FORUM_DB']);
		$GLOBALS['FORUM_DB']->query_update('f_posts',array('p_post'=>$lang_id),array('id'=>$post_id),'',1);
	}

	if ($check_permissions) // Not automated, so we'll have to be doing run-time progressing too
	{
		// Is the user gonna automatically enable notifications for this?
		$auto_monitor_contrib_content=$GLOBALS['OCF_DRIVER']->get_member_row_field($poster,'m_auto_monitor_contrib_content');
		if ($auto_monitor_contrib_content==1)
		{
			require_code('notifications');
			enable_notifications('ocf_topic',strval($topic_id),$poster);
		}
	}

	if (($validated==0) || ($check_permissions))
	{
		$_url=build_url(array('page'=>'topicview','type'=>'findpost','id'=>$post_id),'forum',NULL,false,false,true,'post_'.strval($post_id));
		$url=$_url->evaluate();
	}
	if ($validated==0)
	{
		if ($check_permissions)
		{
			// send_validation_mail is used for other content - but forum is special
			$subject=do_lang('POST_REQUIRING_VALIDATION_MAIL_SUBJECT',$topic_title,NULL,NULL,get_site_default_lang());
			$post_text=get_translated_text($lang_id,$GLOBALS['FORUM_DB'],get_site_default_lang());
			$mail=do_lang('POST_REQUIRING_VALIDATION_MAIL',comcode_escape($url),comcode_escape($poster_name_if_guest),$post_text);
			require_code('notifications');
			dispatch_notification('needs_validation',NULL/*'ocf_forum:'.strval($forum_id)*/,$subject,$mail);
		}
	} else
	{
		if ($check_permissions) // Not automated, so we'll have to be doing run-time progressing too
		{
			if ($send_notification)
			{
				$post_comcode=get_translated_text($lang_id,$GLOBALS['FORUM_DB']);

				require_code('ocf_posts_action2');
				ocf_send_topic_notification($url,$topic_id,$forum_id,$anonymous?db_get_first_id():$poster,$is_starter,$post_comcode,$topic_title,$intended_solely_for,$is_pt);

				// Send a notification for the inline PP
				if (!is_null($intended_solely_for))
				{
					require_code('notifications');
					$msubject=do_lang('NEW_PERSONAL_POST_SUBJECT',$topic_title,NULL,NULL,get_lang($intended_solely_for));
					$mmessage=do_lang('NEW_PERSONAL_POST_MESSAGE',comcode_escape($GLOBALS['FORUM_DRIVER']->get_username($anonymous?db_get_first_id():$poster)),comcode_escape($topic_title),array(comcode_escape($url),$post_comcode),get_lang($intended_solely_for));
					dispatch_notification('ocf_new_pt',NULL,$msubject,$mmessage,array($intended_solely_for),$anonymous?db_get_first_id():$poster);
				}
			}
		}
	}

	if ($update_cacheing)
	{
		if (function_exists('get_member'))
		{
			if (function_exists('ocf_ping_topic_read'))
				ocf_ping_topic_read($topic_id);
	
			if (is_null($forum_id))
			{
				$with=$info[0]['t_pt_from'];
				if ($with==get_member()) $with=$info[0]['t_pt_to'];
	
				decache('side_ocf_personal_topics',array($with));
				decache('_new_pp',array($with));
			}

   		if (get_option('show_post_validation')=='1') decache('main_staff_checklist');
		}

		if (is_null($intended_solely_for))
		{
			if (($validated==1)/* || ($is_starter)*/)
			{
				require_code('ocf_posts_action2');
				ocf_force_update_topic_cacheing($topic_id,1,true,$is_starter,$post_id,$time,$title,$lang_id,$poster_name_if_guest,$poster);
			}
			if ($validated==1)
			{
				if (!is_null($forum_id))
				{
					/*if ($sunk==1)		Don't like this
					{
						$GLOBALS['FORUM_DB']->query('UPDATE '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_forums SET f_cache_num_topics=(f_cache_num_topics+'.(($is_starter)?'1':'0').'),f_cache_num_posts=(f_cache_num_posts+1) WHERE id='.strval((integer)$topic_id));
					} else*/
					{
						require_code('ocf_posts_action2');

						// Find if the topic is validated. This can be approximate, if we don't get 1 then ocf_force_update_forum_cacheing will do a search, making the code very slightly slower
						if ((!$check_permissions) || (is_null($forum_id)))
						{
							$topic_validated=1;
						} else
						{
							if ($is_starter)
							{
								$topic_validated=has_specific_permission($poster,'bypass_validation_midrange_content','topics',array('forums',$forum_id))?1:0;
							} else
							{
								$topic_validated=$GLOBALS['FORUM_DB']->query_value('f_topics','t_validated',array('id'=>$topic_id));
							}
						}

						ocf_force_update_forum_cacheing($forum_id,($is_starter)?1:0,1,($topic_validated==0)?NULL:$topic_id,($topic_validated==0)?NULL:$topic_title,($topic_validated==0)?NULL:$time,($topic_validated==0)?NULL:$poster_name_if_guest,($topic_validated==0)?NULL:$poster,($topic_validated==0)?NULL:$forum_id);
					}
				}
			}
		}

		// Update post count
		if (!is_null($forum_id))
		{
			$post_counts=is_null($forum_id)?1:$GLOBALS['FORUM_DB']->query_value_null_ok('f_forums','f_post_count_increment',array('id'=>$forum_id));
			if (($post_counts===1) && (!$anonymous)) ocf_force_update_member_post_count($poster,1);

			if ($check_permissions) ocf_decache_ocp_blocks($forum_id,NULL,$intended_solely_for); // i.e. we don't run this if in installer
		}
		if ($poster!=$GLOBALS['OCF_DRIVER']->get_guest_id())
		{
			require_code('ocf_posts_action2');
			ocf_member_handle_promotion($poster);
		}
	}

	return $post_id;
}

/**
 * Force a members post count to be recalculated.
 *
 * @param  MEMBER		The member.
 * @param  ?integer	The amount to add to the post count (NULL: fully recalculate the post count).
 */
function ocf_force_update_member_post_count($member_id,$member_post_count_dif=NULL)
{
	if ($GLOBALS['OCF_DRIVER']->get_guest_id()==$member_id) return;
	if (get_db_type()=='xml') return;
	
	if (is_null($member_post_count_dif))
	{
		// This is gonna take a while!!
		global $ALL_FORUM_POST_COUNT_INFO;
		if (is_null($ALL_FORUM_POST_COUNT_INFO))
		{
			$ALL_FORUM_POST_COUNT_INFO=collapse_2d_complexity('id','f_post_count_increment',$GLOBALS['FORUM_DB']->query('SELECT id,f_post_count_increment FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_forums WHERE f_cache_num_posts>0'));
		}
		$member_post_count=0;
		foreach ($ALL_FORUM_POST_COUNT_INFO as $forum_id=>$post_count_increment)
		{
			if ($post_count_increment==1)
			{
				$member_post_count+=$GLOBALS['FORUM_DB']->query_value('f_posts','COUNT(*)',array('p_poster'=>$member_id,'p_cache_forum_id'=>$forum_id));
			}
		}
		$member_post_count+=$GLOBALS['FORUM_DB']->query_value('f_posts','COUNT(*)',array('p_poster'=>$member_id,'p_cache_forum_id'=>NULL));
		$GLOBALS['FORUM_DB']->query('UPDATE '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members SET m_cache_num_posts='.strval((integer)$member_post_count).' WHERE id='.strval((integer)$member_id));
	}
	else
	{
		$GLOBALS['FORUM_DB']->query('UPDATE '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members SET m_cache_num_posts=(m_cache_num_posts+'.strval((integer)$member_post_count_dif).') WHERE id='.strval((integer)$member_id));
	}
}

/**
 * Decache cached OCF elements depending on a certain forum, and optionally a certain member.
 *
 * @param  AUTO_LINK The ID of the forum.
 * @param  ?string	The name of the forum (NULL: find it from the DB).
 * @param  ?MEMBER	The member (NULL: do no member decacheing).
 */
function ocf_decache_ocp_blocks($updated_forum_id,$forum_name=NULL,$member=NULL)
{
	if (is_null($forum_name)) $forum_name=$GLOBALS['FORUM_DB']->query_value('f_forums','f_name',array('id'=>$updated_forum_id));
	decache('main_forum_news');
	decache('main_forum_topics');
	decache('side_forum_news');
	decache('bottom_news',array($forum_name));
	if (!is_null($member))
	{
		decache('side_ocf_personal_topics',array($member));
		decache('_new_pp',array($member));
	}
}

