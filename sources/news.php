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
 * @package		news
 */

/**
 * Show a news entry box.
 *
 * @param  array				The news row
 * @param  ID_TEXT			The zone our news module is in
 * @return tempcode			The box
 */
function render_news_box($row,$zone='_SEARCH')
{
	$url=build_url(array('page'=>'news','type'=>'view','id'=>$row['id']),$zone);
	$title=get_translated_tempcode($row['title']);
	$title_plain=get_translated_text($row['title']);

	global $NEWS_CATS;
	if (!isset($NEWS_CATS)) $NEWS_CATS=array();
	if (!array_key_exists($row['news_category'],$NEWS_CATS))
	{
		$_news_cats=$GLOBALS['SITE_DB']->query_select('news_categories',array('*'),array('id'=>$row['news_category']),'',1);
		if (array_key_exists(0,$_news_cats))
			$NEWS_CATS[$row['news_category']]=$_news_cats[0];
	}
	if ((!array_key_exists($row['news_category'],$NEWS_CATS)) || (!array_key_exists('nc_title',$NEWS_CATS[$row['news_category']])))
		$row['news_category']=db_get_first_id();
	$news_cat_row=$NEWS_CATS[$row['news_category']];
	$img=($news_cat_row['nc_img']=='')?'':find_theme_image($news_cat_row['nc_img']);
	if (is_null($img)) $img='';
	if ($row['news_image']!='')
	{
		$img=$row['news_image'];
		if (url_is_local($img)) $img=get_custom_base_url().'/'.$img;
	}
	$category=get_translated_text($news_cat_row['nc_title']);

	$news=get_translated_tempcode($row['news']);
	if ($news->is_empty())
	{
		$news=get_translated_tempcode($row['news_article']);
		$truncate=true;
	} else $truncate=false;
	$author_url=addon_installed('authors')?build_url(array('page'=>'authors','type'=>'misc','id'=>$row['author']),get_module_zone('authors')):new ocp_tempcode();
	$author=$row['author'];
	require_css('news');
	$seo_bits=seo_meta_get_for('news',strval($row['id']));
	$map=array('_GUID'=>'jd89f893jlkj9832gr3uyg2u','TAGS'=>get_loaded_tags('news',explode(',',$seo_bits[0])),'TRUNCATE'=>$truncate,'AUTHOR'=>$author,'BLOG'=>false,'AUTHOR_URL'=>$author_url,'CATEGORY'=>$category,'IMG'=>$img,'NEWS'=>$news,'ID'=>strval($row['id']),'SUBMITTER'=>strval($row['submitter']),'DATE'=>get_timezoned_date($row['date_and_time']),'DATE_RAW'=>strval($row['date_and_time']),'FULL_URL'=>$url,'NEWS_TITLE'=>$title,'NEWS_TITLE_PLAIN'=>$title_plain);
	if ((get_option('is_on_comments')=='1') && (!has_no_forum()) && ($row['allow_comments']>=1)) $map['COMMENT_COUNT']='1';

	$box=do_template('NEWS_BOX',$map);

	return do_template('SIMPLE_PREVIEW_BOX',array('_GUID'=>'47eeed9d10cb6ad1f1631056d3744ea2','SUMMARY'=>$box));
}

/**
 * Add a news category of the specified details.
 *
 * @param  SHORT_TEXT	The news category title
 * @param  ID_TEXT		The theme image ID of the picture to use for the news category
 * @param  LONG_TEXT		Notes for the news category
 * @param  ?MEMBER		The owner (NULL: public)
 * @param  ?AUTO_LINK	Force an ID (NULL: don't force an ID)
 * @return AUTO_LINK		The ID of our new news category
 */
function add_news_category($title,$img,$notes,$owner=NULL,$id=NULL)
{
	$map=array('nc_title'=>insert_lang($title,1),'nc_img'=>$img,'notes'=>$notes,'nc_owner'=>$owner);
	if (!is_null($id)) $map['id']=$id;
	$id=$GLOBALS['SITE_DB']->query_insert('news_categories',$map,true);

	log_it('ADD_NEWS_CATEGORY',strval($id),$title);

	decache('side_news_categories');

	return $id;
}

/**
 * Edit a news category.
 *
 * @param  AUTO_LINK			The news category to edit
 * @param  ?SHORT_TEXT		The title (NULL: keep as-is)
 * @param  ?SHORT_TEXT		The image (NULL: keep as-is)
 * @param  ?LONG_TEXT		The notes (NULL: keep as-is)
 * @param  ?MEMBER			The owner (NULL: public)
*/
function edit_news_category($id,$title,$img,$notes,$owner=NULL)
{
	$myrows=$GLOBALS['SITE_DB']->query_select('news_categories',array('nc_title','nc_img','notes'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$myrows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$myrow=$myrows[0];

	$old_title=get_translated_text($myrow['nc_title']);

	require_code('urls2');
	suggest_new_idmoniker_for('news','misc',strval($id),$title);

	// Sync meta keywords, if we have auto-sync for these
	if (get_value('disable_seo')==='1') // TODO: Update to get_option in v10
	{
		$sql='SELECT meta_keywords,text_original FROM '.get_table_prefix().'seo_meta m JOIN '.get_table_prefix().'translate t ON m.meta_keywords=t.id AND '.db_string_equal_to('language',user_lang()).' WHERE '.db_string_equal_to('meta_for_type','news').' AND (text_original LIKE \''.db_encode_like($old_title.',%').'\' OR text_original LIKE \''.db_encode_like('%,'.$old_title.',%').'\' OR text_original LIKE \''.db_encode_like('%,'.$old_title).'\')';
		$affected_news=$GLOBALS['SITE_DB']->query($sql);
		foreach ($affected_news as $af_row)
		{
			$new_meta=str_replace(',,',',',preg_replace('#(^|,)'.preg_quote($old_title).'($|,)#',','.$title.',',$af_row['text_original']));
			if (substr($new_meta,0,1)==',') $new_meta=substr($new_meta,1);
			if (substr($new_meta,-1)==',') $new_meta=substr($new_meta,0,strlen($new_meta)-1);
			lang_remap($af_row['meta_keywords'],$new_meta);
		}
	}

	log_it('EDIT_NEWS_CATEGORY',strval($id),$title);

	if (is_null($title)) $title=get_translated_text($myrow['nc_title']);
	if (is_null($img)) $img=$myrow['nc_img'];
	if (is_null($notes)) $notes=$myrow['notes'];

	$GLOBALS['SITE_DB']->query_update('news_categories',array('nc_title'=>lang_remap($myrow['nc_title'],$title),'nc_img'=>$img,'notes'=>$notes,'nc_owner'=>$owner),array('id'=>$id),'',1);

	require_code('themes2');
	tidy_theme_img_code($img,$myrow['nc_img'],'news_categories','nc_img');

	decache('main_news');
	decache('side_news');
	decache('side_news_archive');
	decache('bottom_news');
	decache('side_news_categories');
}

/**
 * Delete a news category.
 *
 * @param  AUTO_LINK		The news category to delete
 */
function delete_news_category($id)
{
	$rows=$GLOBALS['SITE_DB']->query_select('news_categories',array('nc_title','nc_img'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$myrow=$rows[0];

	$min=$GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT c.id FROM '.get_table_prefix().'news_categories c JOIN '.get_table_prefix().'translate t ON t.id=c.nc_title WHERE c.id<>'.strval($id).' AND '.db_string_equal_to('text_original',do_lang('news:NC_general')));
	if (is_null($min))
		$min=$GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT MIN(id) FROM '.get_table_prefix().'news_categories WHERE id<>'.strval((integer)$id));
	if (is_null($min))
	{
		warn_exit(do_lang_tempcode('YOU_MUST_KEEP_ONE_NEWS_CAT'));
	}

	$old_title=get_translated_text($myrow['nc_title']);

	if (addon_installed('catalogues'))
	{
		update_catalogue_content_ref('news_category',strval($id),'');
	}

	log_it('DELETE_NEWS_CATEGORY',strval($id),$old_title);

	delete_lang($myrow['nc_title']);

	// Sync meta keywords, if we have auto-sync for these
	if (get_value('disable_seo')==='1') // TODO: Update to get_option in v10
	{
		$sql='SELECT meta_keywords,text_original FROM '.get_table_prefix().'seo_meta m JOIN '.get_table_prefix().'translate t ON m.meta_keywords=t.id AND '.db_string_equal_to('language',user_lang()).' WHERE '.db_string_equal_to('meta_for_type','news').' AND (text_original LIKE \''.db_encode_like($old_title.',%').'\' OR text_original LIKE \''.db_encode_like('%,'.$old_title.',%').'\' OR text_original LIKE \''.db_encode_like('%,'.$old_title).'\')';
		$affected_news=$GLOBALS['SITE_DB']->query($sql);
		foreach ($affected_news as $af_row)
		{
			$new_meta=str_replace(',,',',',preg_replace('#(^|,)'.preg_quote($old_title).'($|,)#','',$af_row['text_original']));
			if (substr($new_meta,0,1)==',') $new_meta=substr($new_meta,1);
			if (substr($new_meta,-1)==',') $new_meta=substr($new_meta,0,strlen($new_meta)-1);
			lang_remap($af_row['meta_keywords'],$new_meta);
		}
	}

	$GLOBALS['SITE_DB']->query_update('news',array('news_category'=>$min),array('news_category'=>$id));
	$GLOBALS['SITE_DB']->query_delete('news_categories',array('id'=>$id),'',1);
	$GLOBALS['SITE_DB']->query_delete('news_category_entries',array('news_entry_category'=>$id));

	$GLOBALS['SITE_DB']->query_delete('group_category_access',array('module_the_name'=>'news','category_name'=>strval($id)));
	$GLOBALS['SITE_DB']->query_delete('gsp',array('module_the_name'=>'news','category_name'=>strval($id)));

	require_code('themes2');
	tidy_theme_img_code(NULL,$myrow['nc_img'],'news_categories','nc_img');

	decache('side_news_categories');
}

/**
 * Adds a news entry to the database, and send out the news to any RSS cloud listeners.
 *
 * @param  SHORT_TEXT		The news title
 * @param  LONG_TEXT			The news summary (or if not an article, the full news)
 * @param  ?ID_TEXT			The news author (possibly, a link to an existing author in the system, but does not need to be) (NULL: current username)
 * @param  BINARY				Whether the news has been validated
 * @param  BINARY				Whether the news may be rated
 * @param  SHORT_INTEGER	Whether comments are allowed (0=no, 1=yes, 2=review style)
 * @param  BINARY				Whether the news may have trackbacks
 * @param  LONG_TEXT			Notes for the news
 * @param  LONG_TEXT			The news entry (blank means no entry)
 * @param  ?AUTO_LINK		The primary news category (NULL: personal)
 * @param  ?array				The IDs of the news categories that this is in (NULL: none)
 * @param  ?TIME				The time of submission (NULL: now)
 * @param  ?MEMBER			The news submitter (NULL: current member)
 * @param  integer			The number of views the article has had
 * @param  ?TIME				The edit date (NULL: never)
 * @param  ?AUTO_LINK		Force an ID (NULL: don't force an ID)
 * @param  URLPATH			URL to the image for the news entry (blank: use cat image)
 * @return AUTO_LINK			The ID of the news just added
 */
function add_news($title,$news,$author=NULL,$validated=1,$allow_rating=1,$allow_comments=1,$allow_trackbacks=1,$notes='',$news_article='',$main_news_category=NULL,$news_categories=NULL,$time=NULL,$submitter=NULL,$views=0,$edit_date=NULL,$id=NULL,$image='')
{
	if (is_null($author)) $author=$GLOBALS['FORUM_DRIVER']->get_username(get_member());
	if (is_null($news_categories)) $news_categories=array();
	if (is_null($time)) $time=time();
	if (is_null($submitter)) $submitter=get_member();
	$already_created_personal_category=false;

	require_code('comcode_check');
	check_comcode($news_article,NULL,false,NULL,true);

	if (is_null($main_news_category))
	{
		$main_news_category_id=$GLOBALS['SITE_DB']->query_value_null_ok('news_categories','id',array('nc_owner'=>$submitter));
		if (is_null($main_news_category_id))
		{
			if (!has_specific_permission(get_member(),'have_personal_category','cms_news')) fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));

			$p_nc_title=insert_lang(do_lang('MEMBER_CATEGORY',$GLOBALS['FORUM_DRIVER']->get_username($submitter)),2);

			$main_news_category_id=$GLOBALS['SITE_DB']->query_insert('news_categories',array('nc_title'=>$p_nc_title,'nc_img'=>'newscats/community','notes'=>'','nc_owner'=>$submitter),true);
			$already_created_personal_category=true;

			$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(false,true);

			foreach (array_keys($groups) as $group_id)
				$GLOBALS['SITE_DB']->query_insert('group_category_access',array('module_the_name'=>'news','category_name'=>strval($main_news_category_id),'group_id'=>$group_id));
		}
	} else
	{
		$main_news_category_id=$main_news_category;
	}

	if (!addon_installed('unvalidated')) $validated=1;
	$map=array('news_image'=>$image,'edit_date'=>$edit_date,'news_category'=>$main_news_category_id,'news_views'=>$views,'news_article'=>0,'allow_rating'=>$allow_rating,'allow_comments'=>$allow_comments,'allow_trackbacks'=>$allow_trackbacks,'notes'=>$notes,'submitter'=>$submitter,'validated'=>$validated,'date_and_time'=>$time,'title'=>insert_lang_comcode($title,1),'news'=>insert_lang_comcode($news,1),'author'=>$author);
	if (!is_null($id)) $map['id']=$id;
	$id=$GLOBALS['SITE_DB']->query_insert('news',$map,true);

	if (!is_null($news_categories))
	{
		foreach ($news_categories as $i=>$value)
		{
			if ((is_null($value)) && (!$already_created_personal_category))
			{
				$p_nc_title=insert_lang(do_lang('MEMBER_CATEGORY',$GLOBALS['FORUM_DRIVER']->get_username($submitter)),2);
				$news_category_id=$GLOBALS['SITE_DB']->query_insert('news_categories',array('nc_title'=>$p_nc_title,'nc_img'=>'newscats/community','notes'=>'','nc_owner'=>$submitter),true);

				$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(false,true);

				foreach (array_keys($groups) as $group_id)
					$GLOBALS['SITE_DB']->query_insert('group_category_access',array('module_the_name'=>'news','category_name'=>strval($news_category_id),'group_id'=>$group_id));
			}
			else $news_category_id=$value;

			if (is_null($news_category_id)) continue; // Double selected

			$GLOBALS['SITE_DB']->query_insert('news_category_entries',array('news_entry'=>$id,'news_entry_category'=>$news_category_id));

			$news_categories[$i]=$news_category_id;
		}
	}

	require_code('attachments2');
	$map=array('news_article'=>insert_lang_comcode_attachments(2,$news_article,'news',strval($id)));
	$GLOBALS['SITE_DB']->query_update('news',$map,array('id'=>$id),'',1);

	log_it('ADD_NEWS',strval($id),$title);

	if (function_exists('xmlrpc_encode'))
	{
		if (function_exists('set_time_limit')) @set_time_limit(0);

		// Send out on RSS cloud
		if (!$GLOBALS['SITE_DB']->table_is_locked('news_rss_cloud'))
			$GLOBALS['SITE_DB']->query('DELETE FROM '.get_table_prefix().'news_rss_cloud WHERE register_time<'.strval(time()-25*60*60));
		$start=0;
		do
		{
			$listeners=$GLOBALS['SITE_DB']->query_select('news_rss_cloud',array('*'),NULL,'',100,$start);
			foreach ($listeners as $listener)
			{
				$data=$listener['watching_channel'];
				if ($listener['rem_protocol']=='xml-rpc')
				{
					$request=xmlrpc_encode_request($listener['rem_procedure'],$data);
					$length=strlen($request);
					$_length=strval($length);
$packet=<<<END
POST /{$listener['rem_path']} HTTP/1.0
Host: {$listener['rem_ip']}
Content-Type: text/xml
Content-length: {$_length}

{$request}
END;
				}
				$errno=0;
				$errstr='';
				$mysock=@fsockopen($listener['rem_ip'],$listener['rem_port'],$errno,$errstr,6.0);
				if ($mysock!==false)
				{
					@fwrite($mysock,$packet);
					@fclose($mysock);
				}
				$start+=100;
			}
		}
		while (array_key_exists(0,$listeners));
	}

	require_code('seo2');
	$meta_description=($news=='')?$news_article:$news;
	if (get_value('disable_seo')==='1') // TODO: Update to get_option in v10
	{
		$meta_keywords='';
		foreach (array_unique(array_merge(is_null($news_categories)?array():$news_categories,array($main_news_category_id))) as $news_category_id)
		{
			if ($meta_keywords!='') $meta_keywords.=',';
			$meta_keywords.=get_translated_text($GLOBALS['SITE_DB']->query_value('news_categories','nc_title',array('id'=>$news_category_id)));
		}
		seo_meta_set_for_explicit('news',strval($id),$meta_keywords,$meta_description);
	} else
	{
		seo_meta_set_for_implicit('news',strval($id),array($title,$meta_description/*,$news_article*/),$meta_description); // News article could be used, but it's probably better to go for the summary only to avoid crap
	}

	if ($validated==1)
	{
		decache('main_news');
		decache('side_news');
		decache('side_news_archive');
		decache('bottom_news');
		decache('side_news_categories');

		dispatch_news_notification($id,$title,$main_news_category_id);
	}

	if ((!get_mass_import_mode()) && ($validated==1) && (get_option('site_closed')=='0') && (ocp_srv('HTTP_HOST')!='127.0.0.1') && (ocp_srv('HTTP_HOST')!='localhost') && (has_category_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'news',strval($main_news_category_id))))
	{
		register_shutdown_function('send_rss_ping');
		require_code('news_sitemap');
		register_shutdown_function('build_news_sitemap');
	}

	return $id;
}

/**
 * Send out a ping to configured services.
 *
 * @param  boolean			Whether to show errors
 * @return string				HTTP result output
 */
function send_rss_ping($show_errors=false)
{
	$url=find_script('backend').'?type=rss&mode=news';

	$out='';

	$_ping_url=str_replace('{url}',urlencode(get_base_url()),str_replace('{rss}',urlencode($url),str_replace('{title}',urlencode(get_site_name()),get_option('ping_url'))));
	$ping_urls=explode(chr(10),$_ping_url);
	foreach ($ping_urls as $ping_url)
	{
		$ping_url=trim($ping_url);
		if ($ping_url!='')
		{
			$out.=http_download_file($ping_url,NULL,$show_errors);
		}
	}

	require_code('sitemap');
	$out.=ping_sitemap($url);

	return $out;
}

/**
 * Edit a news entry.
 *
 * @param  AUTO_LINK			The ID of the news to edit
 * @param  SHORT_TEXT		The news title
 * @param  LONG_TEXT			The news summary (or if not an article, the full news)
 * @param  ID_TEXT			The news author (possibly, a link to an existing author in the system, but does not need to be)
 * @param  BINARY				Whether the news has been validated
 * @param  BINARY				Whether the news may be rated
 * @param  SHORT_INTEGER	Whether comments are allowed (0=no, 1=yes, 2=review style)
 * @param  BINARY				Whether the news may have trackbacks
 * @param  LONG_TEXT			Notes for the news
 * @param  LONG_TEXT			The news entry (blank means no entry)
 * @param  AUTO_LINK			The primary news category (NULL: personal)
 * @param  ?array				The IDs of the news categories that this is in (NULL: do not change)
 * @param  SHORT_TEXT		Meta keywords
 * @param  LONG_TEXT			Meta description
 * @param  ?URLPATH			URL to the image for the news entry (blank: use cat image) (NULL: don't delete existing)
 * @param  ?TIME				Recorded add time (NULL: leave alone)
 */
function edit_news($id,$title,$news,$author,$validated,$allow_rating,$allow_comments,$allow_trackbacks,$notes,$news_article,$main_news_category,$news_categories,$meta_keywords,$meta_description,$image,$time=NULL)
{
	$rows=$GLOBALS['SITE_DB']->query_select('news',array('title','news','news_article','submitter','news_category'),array('id'=>$id),'',1);
	$_title=$rows[0]['title'];
	$_news=$rows[0]['news'];
	$_news_article=$rows[0]['news_article'];

	require_code('urls2');

	suggest_new_idmoniker_for('news','view',strval($id),$title);

	require_code('attachments2');
	require_code('attachments3');

	if (!addon_installed('unvalidated')) $validated=1;

	require_code('submit');
	$just_validated=(!content_validated('news',strval($id))) && ($validated==1);
	if ($just_validated)
	{
		send_content_validated_notification('news',strval($id));
	}

	$map=array('news_category'=>$main_news_category,'news_article'=>update_lang_comcode_attachments($_news_article,$news_article,'news',strval($id),NULL,false,$rows[0]['submitter']),'edit_date'=>time(),'allow_rating'=>$allow_rating,'allow_comments'=>$allow_comments,'allow_trackbacks'=>$allow_trackbacks,'notes'=>$notes,'validated'=>$validated,'title'=>lang_remap_comcode($_title,$title),'news'=>lang_remap_comcode($_news,$news),'author'=>$author);

	if (!is_null($time)) $map['date_and_time']=$time;

	if (!is_null($image))
	{
		$map['news_image']=$image;
		require_code('files2');
		delete_upload('uploads/grepimages','news','news_image','id',$id,$image);
	}

	/*$news_categories=$news_categories[0];
	foreach ($news_categories as $key=>$value)
	{
		if($key>0) $news_categories.=','.$value;
	}*/

	if (!is_null($news_categories))
	{
		$GLOBALS['SITE_DB']->query_delete('news_category_entries',array('news_entry'=>$id));

		foreach ($news_categories as $value)
		{
			$GLOBALS['SITE_DB']->query_insert('news_category_entries',array('news_entry'=>$id,'news_entry_category'=>$value));
		}
	}

	log_it('EDIT_NEWS',strval($id),$title);

	$GLOBALS['SITE_DB']->query_update('news',$map,array('id'=>$id),'',1);

	$self_url=build_url(array('page'=>'news','type'=>'view','id'=>$id),get_module_zone('news'),NULL,false,false,true);

	if ($just_validated)
	{
		dispatch_news_notification($id,$title,$main_news_category);
	}

	require_code('seo2');
	if (get_value('disable_seo')==='1') // TODO: Update to get_option in v10
	{
		$meta_description=($news=='')?$news_article:$news;
		$meta_keywords='';
		foreach (array_unique(array_merge(is_null($news_categories)?array():$news_categories,array($main_news_category))) as $news_category_id)
		{
			if ($meta_keywords!='') $meta_keywords.=',';
			$meta_keywords.=get_translated_text($GLOBALS['SITE_DB']->query_value('news_categories','nc_title',array('id'=>$news_category_id)));
		}
	}
	seo_meta_set_for_explicit('news',strval($id),$meta_keywords,$meta_description);

	decache('main_news');
	decache('side_news');
	decache('side_news_archive');
	decache('bottom_news');
	decache('side_news_categories');

	if (($validated==1) && (has_category_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'news',strval($main_news_category))))
	{
		register_shutdown_function('send_rss_ping');
	}

	require_code('feedback');
	update_spacer_post(
		$allow_comments!=0,
		'news',
		strval($id),
		$self_url,
		$title,
		process_overridden_comment_forum('news',strval($id),strval($main_news_category),strval($rows[0]['news_category']))
	);
}

/**
 * Send out a notification of some new news.
 *
 * @param  AUTO_LINK		The ID of the news
 * @param  SHORT_TEXT	The title
 * @param  AUTO_LINK		The main news category
 */
function dispatch_news_notification($id,$title,$main_news_category)
{
	$self_url=build_url(array('page'=>'news','type'=>'view','id'=>$id),get_module_zone('news'),NULL,false,false,true);

	$is_blog=!is_null($GLOBALS['SITE_DB']->query_value('news_categories','nc_owner',array('id'=>$main_news_category)));

	require_code('notifications');
	require_lang('news');
	if ($is_blog)
	{
		$subject=do_lang('BLOG_NOTIFICATION_MAIL_SUBJECT',get_site_name(),$title);
		$mail=do_lang('BLOG_NOTIFICATION_MAIL',comcode_escape(get_site_name()),comcode_escape($title),array($self_url->evaluate()));
		dispatch_notification('news_entry',strval($main_news_category),$subject,$mail);
	} else
	{
		$subject=do_lang('NEWS_NOTIFICATION_MAIL_SUBJECT',get_site_name(),$title);
		$mail=do_lang('NEWS_NOTIFICATION_MAIL',comcode_escape(get_site_name()),comcode_escape($title),array($self_url->evaluate()));
		dispatch_notification('news_entry',strval($main_news_category),$subject,$mail);
	}
}

/**
 * Delete a news entry.
 *
 * @param  AUTO_LINK		The ID of the news to edit
 */
function delete_news($id)
{
	$rows=$GLOBALS['SITE_DB']->query_select('news',array('title','news','news_article'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$title=$rows[0]['title'];
	$news=$rows[0]['news'];
	$news_article=$rows[0]['news_article'];

	if (addon_installed('catalogues'))
	{
		update_catalogue_content_ref('news',strval($id),'');
	}

	$_title=get_translated_text($title);
	log_it('DELETE_NEWS',strval($id),$_title);

	require_code('files2');
	delete_upload('uploads/grepimages','news','news_image','id',$id);

	$GLOBALS['SITE_DB']->query_delete('news',array('id'=>$id),'',1);
	$GLOBALS['SITE_DB']->query_delete('news_category_entries',array('news_entry'=>$id));

	$GLOBALS['SITE_DB']->query_delete('rating',array('rating_for_type'=>'news','rating_for_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('trackbacks',array('trackback_for_type'=>'news','trackback_for_id'=>$id));
	require_code('notifications');
	delete_all_notifications_on('comment_posted','news_'.strval($id));

	delete_lang($title);
	delete_lang($news);
	require_code('attachments2');
	require_code('attachments3');
	if (!is_null($news_article)) delete_lang_comcode_attachments($news_article,'news',strval($id));

	require_code('seo2');
	seo_meta_erase_storage('news',strval($id));

	decache('main_news');
	decache('side_news');
	decache('side_news_archive');
	decache('bottom_news');
	decache('side_news_categories');
}

/**
 * Get a nice formatted XHTML list of news categories.
 *
 * @param  ?mixed			The selected news category. Array or AUTO_LINK (NULL: personal)
 * @param  boolean		Whether to add all personal categories into the list (for things like the adminzone, where all categories must be shown, regardless of permissions)
 * @param  boolean		Whether to only show for what may be added to by the current member
 * @param  boolean		Whether to limit to only existing cats (otherwise we dynamically add unstarted blogs)
 * @param  ?boolean		Whether to limit to only show blog categories (NULL: don't care, true: blogs only, false: no blogs)
 * @param  boolean		Whether to prefer to choose a non-blog category as the default
 * @return tempcode		The tempcode for the news category select list
 */
function nice_get_news_categories($it=NULL,$show_all_personal_categories=false,$addable_filter=false,$only_existing=false,$only_blogs=NULL,$prefer_not_blog_selected=false)
{
	if (!is_array($it)) $it=array($it);

	if ($only_blogs===true)
	{
		$where='WHERE nc_owner IS NOT NULL';
	}
	elseif ($only_blogs===false)
	{
		$where='WHERE nc_owner IS NULL';
	} else
	{
		$where='WHERE 1=1';
	}
	$count=$GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.get_table_prefix().'news_categories '.$where.' ORDER BY id');
	if ($count>500) // Uh oh, loads, need to limit things more
	{
		$where.=' AND (nc_owner IS NULL OR nc_owner='.strval(get_member()).')';
	}
	$_cats=$GLOBALS['SITE_DB']->query('SELECT * FROM '.get_table_prefix().'news_categories '.$where.' ORDER BY id');

	foreach ($_cats as $i=>$cat)
	{
		$_cats[$i]['nice_title']=get_translated_text($cat['nc_title']);
	}
	global $M_SORT_KEY;
	$M_SORT_KEY='nice_title';
	usort($_cats,'multi_sort');

	// Sort so blogs go after news
	$title_ordered_cats=$_cats;
	$_cats=array();
	foreach ($title_ordered_cats as $cat)
		if (is_null($cat['nc_owner'])) $_cats[]=$cat;
	foreach ($title_ordered_cats as $cat)
		if (!is_null($cat['nc_owner'])) $_cats[]=$cat;

	$categories=new ocp_tempcode();
	$add_cat=true;

	foreach ($_cats as $cat)
	{
		if ($cat['nc_owner']==get_member()) $add_cat=false;

		if (!has_category_access(get_member(),'news',strval($cat['id']))) continue;
		if (($addable_filter) && (!has_submit_permission('high',get_member(),get_ip_address(),'cms_news',array('news',$cat['id'])))) continue;

		if (is_null($cat['nc_owner']))
		{
			$li=form_input_list_entry(strval($cat['id']),($it!=array(NULL)) && in_array($cat['id'],$it),$cat['nice_title'].' (#'.strval($cat['id']).')');
			$categories->attach($li);
		} else
		{
			if ((((!is_null($cat['nc_owner'])) && (has_specific_permission(get_member(),'can_submit_to_others_categories'))) || (($cat['nc_owner']==get_member()) && (!is_guest()))) || ($show_all_personal_categories))
				$categories->attach(form_input_list_entry(strval($cat['id']),(($cat['nc_owner']==get_member()) && ((!$prefer_not_blog_selected) && (in_array(NULL,$it)))) || (in_array($cat['id'],$it)),$cat['nice_title'].' (#'.strval($cat['id']).')'));
		}
	}

	if ((!$only_existing) && (has_specific_permission(get_member(),'have_personal_category','cms_news')) && ($add_cat) && (!is_guest()))
	{
		$categories->attach(form_input_list_entry('personal',(!$prefer_not_blog_selected) && in_array(NULL,$it),do_lang_tempcode('MEMBER_CATEGORY',do_lang_tempcode('_NEW',escape_html($GLOBALS['FORUM_DRIVER']->get_username(get_member()))))));
	}

	return $categories;
}

/**
 * Get a nice formatted XHTML list of news.
 *
 * @param  ?AUTO_LINK	The selected news entry (NULL: none)
 * @param  ?MEMBER		Limit news to those submitted by this member (NULL: show all)
 * @param  boolean		Whether to only show for what may be edited by the current member
 * @param  boolean		Whether to only show blog posts
 * @return tempcode		The list
 */
function nice_get_news($it,$only_owned=NULL,$editable_filter=false,$only_in_blog=false)
{
	$where=is_null($only_owned)?'1':'submitter='.strval($only_owned);
	if ($only_in_blog)
	{
		$rows=$GLOBALS['SITE_DB']->query('SELECT n.* FROM '.get_table_prefix().'news n JOIN '.get_table_prefix().'news_categories c ON c.id=n.news_category AND '.$where.' AND nc_owner IS NOT NULL ORDER BY date_and_time DESC',300/*reasonable limit*/);
	} else
	{
		$rows=$GLOBALS['SITE_DB']->query('SELECT * FROM '.get_table_prefix().'news WHERE '.$where.' ORDER BY date_and_time DESC',300/*reasonable limit*/);
	}

	if (count($rows)==300) attach_message(do_lang_tempcode('TOO_MUCH_CHOOSE__RECENT_ONLY',escape_html(integer_format(300))),'warn');

	$out=new ocp_tempcode();
	foreach ($rows as $myrow)
	{
		if (!has_category_access(get_member(),'news',strval($myrow['news_category']))) continue;
		if (($editable_filter) && (!has_edit_permission('high',get_member(),$myrow['submitter'],'cms_news',array('news',$myrow['news_category'])))) continue;

		$selected=($myrow['id']==$it);

		$out->attach(form_input_list_entry(strval($myrow['id']),$selected,get_translated_text($myrow['title'])));
	}

	return $out;
}

