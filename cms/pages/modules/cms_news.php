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
 * @package		news
 */

require_code('crud_module');

/**
 * Module page class.
 */
class Module_cms_news extends standard_crud_module
{
	var $lang_type='NEWS';
	var $select_name='TITLE';
	var $code_require='news';
	var $permissions_require='high';
	var $permissions_cat_require='news';
	var $permissions_cat_name='main_news_category';
	var $user_facing=true;
	var $seo_type='news';
	var $award_type='news';
	var $possibly_some_kind_of_upload=true;
	var $upload='image';
	var $menu_label='NEWS';
	var $table='news';
	var $orderer='title';
	var $title_is_multi_lang=true;

	var $donext_type=NULL;

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array_merge(array('misc'=>'MANAGE_NEWS'),parent::get_entry_points());
	}

	/**
	 * Standard modular privilege-override finder function.
	 *
	 * @return array	A map of privileges that are overridable; privilege to 0 or 1. 0 means "not category overridable". 1 means "category overridable".
	 */
	function get_privilege_overrides()
	{
		require_lang('news');
		return array('mass_import'=>0,'have_personal_category'=>0,'submit_cat_highrange_content'=>array(0,'ADD_NEWS_CATEGORY'),'edit_own_cat_highrange_content'=>array(0,'EDIT_OWN_NEWS_CATEGORY'),'edit_cat_highrange_content'=>array(0,'EDIT_NEWS_CATEGORY'),'delete_own_cat_highrange_content'=>array(0,'DELETE_OWN_NEWS_CATEGORY'),'delete_cat_highrange_content'=>array(0,'DELETE_NEWS_CATEGORY'),'submit_highrange_content'=>array(1,'ADD_NEWS'),'bypass_validation_highrange_content'=>array(1,'BYPASS_NEWS_VALIDATION'),'edit_own_highrange_content'=>array(1,'EDIT_OWN_NEWS'),'edit_highrange_content'=>array(1,'EDIT_NEWS'),'delete_own_highrange_content'=>array(1,'DELETE_OWN_NEWS'),'delete_highrange_content'=>array(1,'DELETE_NEWS'));
	}

	/**
	 * Standard crud_module run_start.
	 *
	 * @param  ID_TEXT		The type of module execution
	 * @return tempcode		The output of the run
	 */
	function run_start($type)
	{
		set_helper_panel_pic('pagepics/news');
		set_helper_panel_tutorial('tut_news');

		$this->cat_crud_module=class_exists('Mx_cms_news_cat')?new Mx_cms_news_cat():new Module_cms_news_cat();
		$this->posting_form_title=do_lang_tempcode('NEWS_ARTICLE');

		require_code('news2');
		require_css('news');

		// Decide what to do
		if ($type=='misc') return $this->misc();

		if ($type=='import') return $this->import_news();

		if ($type=='_import_news') return $this->_import_news();

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
		return do_next_manager(get_screen_title('MANAGE_NEWS'),comcode_lang_string('DOC_NEWS'),
					array_merge(array(
						/*	 type							  page	 params													 zone	  */
						has_privilege(get_member(),'submit_cat_highrange_content','cms_news')?array('add_one_category',array('_SELF',array('type'=>'ac'),'_SELF'),do_lang('ADD_NEWS_CATEGORY')):NULL,
						has_privilege(get_member(),'edit_own_cat_highrange_content','cms_news')?array('edit_one_category',array('_SELF',array('type'=>'ec'),'_SELF'),do_lang('EDIT_NEWS_CATEGORY')):NULL,
						has_privilege(get_member(),'submit_highrange_content','cms_news')?array('add_one',array('_SELF',array('type'=>'ad'),'_SELF'),do_lang('ADD_NEWS')):NULL,
						has_privilege(get_member(),'edit_own_highrange_content','cms_news')?array('edit_one',array('_SELF',array('type'=>'ed'),'_SELF'),do_lang('EDIT_NEWS')):NULL,
						has_privilege(get_member(),'mass_import','cms_news')?array('import',array('_SELF',array('type'=>'import'),'_SELF'),do_lang('IMPORT_NEWS')):NULL,
					),manage_custom_fields_donext_link('news')),
					do_lang('MANAGE_NEWS')
		);
	}

	/**
	 * Standard crud_module table function.
	 *
	 * @param  array			Details to go to build_url for link to the next screen.
	 * @return array			A quartet: The choose table, Whether re-ordering is supported from this screen, Search URL, Archive URL.
	 */
	function nice_get_choose_table($url_map)
	{
		require_code('templates_results_table');

		$current_ordering=get_param('sort','date_and_time DESC');
		if (strpos($current_ordering,' ')===false) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
		list($sortable,$sort_order)=explode(' ',$current_ordering,2);
		$sortables=array(
			'title'=>do_lang_tempcode('TITLE'),
			'news_category'=>do_lang_tempcode('MAIN_CATEGORY'),
			'date_and_time'=>do_lang_tempcode('ADDED'),
			'news_views'=>do_lang_tempcode('COUNT_VIEWS'),
			'submitter'=>do_lang_tempcode('OWNER'),
		);
		if (addon_installed('unvalidated'))
			$sortables['validated']=do_lang_tempcode('VALIDATED');
		if (((strtoupper($sort_order)!='ASC') && (strtoupper($sort_order)!='DESC')) || (!array_key_exists($sortable,$sortables)))
			log_hack_attack_and_exit('ORDERBY_HACK');
		inform_non_canonical_parameter('sort');

		$fh=array(do_lang_tempcode('TITLE'),do_lang_tempcode('MAIN_CATEGORY'));
		$fh[]=do_lang_tempcode('ADDED');
		$fh[]=do_lang_tempcode('COUNT_VIEWS');
		if (addon_installed('unvalidated'))
			$fh[]=do_lang_tempcode('VALIDATED');
		$fh[]=do_lang_tempcode('OWNER');
		$fh[]=do_lang_tempcode('ACTIONS');
		$header_row=results_field_title($fh,$sortables,'sort',$sortable.' '.$sort_order);

		$fields=new ocp_tempcode();

		require_code('form_templates');
		$only_owned=has_privilege(get_member(),'edit_highrange_content','cms_news')?NULL:get_member();
		list($rows,$max_rows)=$this->get_entry_rows(false,$current_ordering,is_null($only_owned)?NULL:array('submitter'=>$only_owned));
		$news_cat_titles=array();
		foreach ($rows as $row)
		{
			$edit_link=build_url($url_map+array('id'=>$row['id']),'_SELF');

			$fr=array();
			$fr[]=protect_from_escaping(hyperlink(build_url(array('page'=>'news','type'=>'view','id'=>$row['id']),get_module_zone('news')),get_translated_text($row['title'])));
			if (array_key_exists($row['news_category'],$news_cat_titles))
			{
				$nc_title=$news_cat_titles[$row['news_category']];
			} else
			{
				$nc_title=$GLOBALS['SITE_DB']->query_select_value_if_there('news_categories','nc_title',array('id'=>$row['news_category']));
				$news_cat_titles[$row['news_category']]=$nc_title;
			}
			if (!is_null($nc_title))
			{
				$fr[]=protect_from_escaping(hyperlink(build_url(array('page'=>'news','type'=>'misc','filter'=>$row['news_category']),get_module_zone('news')),get_translated_text($nc_title),false,true));
			} else
			{
				$fr[]=do_lang('UNKNOWN');
			}
			$fr[]=get_timezoned_date($row['date_and_time']);
			$fr[]=integer_format($row['news_views']);
			if (addon_installed('unvalidated'))
				$fr[]=($row['validated']==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');
			$username=protect_from_escaping($GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($row['submitter']));
			$fr[]=$username;
			$fr[]=protect_from_escaping(hyperlink($edit_link,do_lang_tempcode('EDIT'),false,true,'#'.strval($row['id'])));

			$fields->attach(results_entry($fr,true));
		}

		$search_url=build_url(array('page'=>'search','id'=>'news'),get_module_zone('search'));
		$archive_url=build_url(array('page'=>'news'),get_module_zone('news'));

		return array(results_table(do_lang($this->menu_label),get_param_integer('start',0),'start',get_param_integer('max',20),'max',$max_rows,$header_row,$fields,$sortables,$sortable,$sort_order),false,$search_url,$archive_url);
	}

	/**
	 * Standard crud_module list function.
	 *
	 * @return tempcode		The selection list
	 */
	function nice_get_entries()
	{
		$only_owned=has_privilege(get_member(),'edit_highrange_content','cms_news')?NULL:get_member();
		return nice_get_news(NULL,$only_owned,false);
	}

	/**
	 * Get tempcode for a news adding/editing form.
	 *
	 * @param  ?AUTO_LINK		The news ID (NULL: new)
	 * @param  ?AUTO_LINK		The primary category for the news (NULL: personal)
	 * @param  ?array				A list of categories the news is in (NULL: not known)
	 * @param  SHORT_TEXT		The news title
	 * @param  LONG_TEXT			The news summary
	 * @param  SHORT_TEXT		The name of the author
	 * @param  BINARY				Whether the news is validated
 	 * @param  ?BINARY			Whether rating is allowed (NULL: decide statistically, based on existing choices)
 	 * @param  ?SHORT_INTEGER	Whether comments are allowed (0=no, 1=yes, 2=review style) (NULL: decide statistically, based on existing choices)
 	 * @param  ?BINARY			Whether trackbacks are allowed (NULL: decide statistically, based on existing choices)
	 * @param  BINARY				Whether to show the "send trackback" field
	 * @param  LONG_TEXT			Notes for the video
	 * @param  URLPATH			URL to the image for the news entry (blank: use cat image)
	 * @param  ?array				Scheduled go-live time (NULL: N/A)
	 * @return array				A tuple of lots of info (fields, hidden fields, trailing fields, tabindex for posting form)
	 */
	function get_form_fields($id=NULL,$main_news_category=NULL,$news_category=NULL,$title='',$news='',$author='',$validated=1,$allow_rating=NULL,$allow_comments=NULL,$allow_trackbacks=NULL,$send_trackbacks=1,$notes='',$image='',$scheduled=NULL)
	{
		list($allow_rating,$allow_comments,$allow_trackbacks)=$this->choose_feedback_fields_statistically($allow_rating,$allow_comments,$allow_trackbacks);

		require_lang('menus');
		set_helper_panel_text(comcode_lang_string('DOC_WRITING'));
		set_helper_panel_pic('');

		if (is_null($main_news_category))
		{
			inform_non_canonical_parameter('cat');

			$param_cat=get_param('cat','');
			if ($param_cat=='')
			{
				$news_category=array();
				$main_news_category=NULL;
			} elseif (strpos($param_cat,',')===false)
			{
				$news_category=array();
				$main_news_category=intval($param_cat);
			} else
			{
				require_code('ocfiltering');
				$news_category=ocfilter_to_idlist_using_db($param_cat,'id','news_categories','news_categories',NULL,'id','id');
				$main_news_category=NULL;
			}

			$author=$GLOBALS['FORUM_DRIVER']->get_username(get_member());
		}

		$cats1=nice_get_news_categories($main_news_category,false,true,is_integer($main_news_category),NULL,true);
		$cats2=nice_get_news_categories(is_null($news_category)?array():$news_category,false,true,is_integer($main_news_category),NULL,true);

		$fields=new ocp_tempcode();
		$fields2=new ocp_tempcode();
		require_code('form_templates');
		$fields->attach(form_input_line_comcode(do_lang_tempcode('TITLE'),do_lang_tempcode('DESCRIPTION_TITLE'),'title',$title,true));
		$fields->attach(form_input_list(do_lang_tempcode('MAIN_CATEGORY'),do_lang_tempcode('DESCRIPTION_MAIN_CATEGORY'),'main_news_category',$cats1));
		if (addon_installed('authors'))
		{
			$fields->attach(form_input_author(do_lang_tempcode('SOURCE'),do_lang_tempcode('DESCRIPTION_SOURCE'),'author',$author,true));
		}

		require_code('feedback2');

		$posting_form_tabindex=get_form_field_tabindex(NULL);

		if ($validated==0)
		{
			$validated=get_param_integer('validated',0);
			if (($validated==1) && (addon_installed('unvalidated'))) attach_message(do_lang_tempcode('WILL_BE_VALIDATED_WHEN_SAVING'));
		}
		if (has_some_cat_privilege(get_member(),'bypass_validation_'.$this->permissions_require.'range_content',NULL,$this->permissions_cat_require))
			if (addon_installed('unvalidated'))
				$fields2->attach(form_input_tick(do_lang_tempcode('VALIDATED'),do_lang_tempcode('DESCRIPTION_VALIDATED'),'validated',$validated==1));

		$fields2->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('_GUID'=>'90e0f1f4557eb78d58b9a13c3e1e65dc','SECTION_HIDDEN'=>$news=='' && $image=='' && (is_null($scheduled)) && (is_null($news_category) || $news_category==array()),'TITLE'=>do_lang_tempcode('ADVANCED'))));
		$fields2->attach(form_input_text_comcode(do_lang_tempcode('NEWS_SUMMARY'),do_lang_tempcode('DESCRIPTION_NEWS_SUMMARY'),'news',$news,false));
		$fields2->attach(form_input_multi_list(do_lang_tempcode('SECONDARY_CATEGORIES'),do_lang_tempcode('DESCRIPTION_SECONDARY_CATEGORIES'),'news_category',$cats2));
		$hidden=new ocp_tempcode();
		//handle_max_file_size($hidden,'image'); Attachments will add this
		$fields2->attach(form_input_upload(do_lang_tempcode('IMAGE'),do_lang_tempcode('DESCRIPTION_NEWS_IMAGE_OVERRIDE'),'file',false,$image,NULL,true,str_replace(' ','',get_option('valid_images'))));
		if ((addon_installed('calendar')) && (has_privilege(get_member(),'scheduled_publication_times')))
			$fields2->attach(form_input_date__scheduler(do_lang_tempcode('PUBLICATION_TIME'),do_lang_tempcode('DESCRIPTION_PUBLICATION_TIME'),'schedule',true,true,true,$scheduled,intval(date('Y'))-1970+2,1970));

		$fields2->attach(feedback_fields($allow_rating==1,$allow_comments==1,$allow_trackbacks==1,$send_trackbacks==1,$notes,$allow_comments==2));

		require_code('activities');
		$fields2->attach(get_syndication_option_fields());

		$fields2->attach(meta_data_get_fields('news',is_null($id)?NULL:strval($id)));

		if (addon_installed('content_reviews'))
			$fields2->attach(content_review_get_fields('news',is_null($id)?NULL:strval($id)));

		return array($fields,$hidden,NULL,NULL,NULL,NULL,make_string_tempcode($fields2->evaluate())/*XHTMLXHTML*/,$posting_form_tabindex);
	}

	/**
	 * Standard crud_module submitter getter.
	 *
	 * @param  ID_TEXT		The entry for which the submitter is sought
	 * @return array			The submitter, and the time of submission (null submission time implies no known submission time)
	 */
	function get_submitter($id)
	{
		$rows=$GLOBALS['SITE_DB']->query_select('news',array('submitter','date_and_time'),array('id'=>intval($id)),'',1);
		if (!array_key_exists(0,$rows)) return array(NULL,NULL);
		return array($rows[0]['submitter'],$rows[0]['date_and_time']);
	}

	/**
	 * Standard crud_module cat getter.
	 *
	 * @param  AUTO_LINK		The entry for which the cat is sought
	 * @return string			The cat
	 */
	function get_cat($id)
	{
		$temp=$GLOBALS['SITE_DB']->query_select_value_if_there('news','news_category',array('id'=>$id));
		if (is_null($temp)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		return strval($temp);
	}

	/**
	 * Standard crud_module edit form filler.
	 *
	 * @param  ID_TEXT		The entry being edited
	 * @return array			A tuple of lots of info
	 */
	function fill_in_edit_form($_id)
	{
		$id=intval($_id);

		require_lang('menus');
		require_lang('zones');

		$rows=$GLOBALS['SITE_DB']->query_select('news',array('*'),array('id'=>intval($id)),'',1);
		if (!array_key_exists(0,$rows))
		{
			warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		}
		$myrow=$rows[0];

		$cat=$myrow['news_category'];

		$categories=array();
		$category_query=$GLOBALS['SITE_DB']->query_select('news_category_entries',array('news_entry_category'),array('news_entry'=>$id));

		foreach ($category_query as $value) $categories[]=$value['news_entry_category'];

		$scheduled=mixed();

		if (addon_installed('calendar'))
		{
			$schedule_code=':$GLOBALS[\'SITE_DB\']->query_update(\'news\',array(\'date_and_time\'=>$GLOBALS[\'_EVENT_TIMESTAMP\'],\'validated\'=>1),array(\'id\'=>'.strval($id).'),\'\',1);';
			$past_event=$GLOBALS['SITE_DB']->query_select('calendar_events e LEFT JOIN '.$GLOBALS['SITE_DB']->get_table_prefix().'translate t ON e.e_content=t.id',array('e_start_day','e_start_month','e_start_year','e_start_hour','e_start_minute'),array('text_original'=>$schedule_code),'',1);
			$scheduled=array_key_exists(0,$past_event)?array($past_event[0]['e_start_minute'],$past_event[0]['e_start_hour'],$past_event[0]['e_start_month'],$past_event[0]['e_start_day'],$past_event[0]['e_start_year']):NULL;
			if ((!is_null($scheduled)) && ($scheduled<time())) $scheduled=NULL;
		} else
		{
			$scheduled=NULL;
		}

		$ret=$this->get_form_fields($id,$cat,$categories,get_translated_text($myrow['title']),get_translated_text($myrow['news']),$myrow['author'],$myrow['validated'],$myrow['allow_rating'],$myrow['allow_comments'],$myrow['allow_trackbacks'],0,$myrow['notes'],$myrow['news_image'],$scheduled);

		$ret[2]=new ocp_tempcode();
		$ret[3]='';
		$ret[4]=false;
		$ret[5]=get_translated_text($myrow['news_article']);
		$ret[7]=get_translated_tempcode($myrow['news_article']);
		return $ret;
	}

	/**
	 * Standard crud_module add actualiser.
	 *
	 * @return ID_TEXT		The ID of the entry added
	 */
	function add_actualisation()
	{
		$author=post_param('author',$GLOBALS['FORUM_DRIVER']->get_username(get_member()));
		$news=post_param('news');
		$title=post_param('title');
		$validated=post_param_integer('validated',0);
		$news_article=post_param('post');
		if (post_param('main_news_category')!='personal') $main_news_category=post_param_integer('main_news_category');
		else $main_news_category=NULL;

		$news_category=array();
		if (array_key_exists('news_category',$_POST))
		{
			foreach ($_POST['news_category'] as $val)
			{
				$news_category[]=($val=='personal')?NULL:intval($val);
			}
		}

		$allow_rating=post_param_integer('allow_rating',0);
		$allow_comments=post_param_integer('allow_comments',0);
		$allow_trackbacks=post_param_integer('allow_trackbacks',0);
		require_code('feedback2');
		send_trackbacks(post_param('send_trackbacks',''),$title,$news);
		$notes=post_param('notes','');

		$urls=get_url('','file','uploads/grepimages',0,OCP_UPLOAD_IMAGE);
		$url=$urls[0];

		$schedule=get_input_date('schedule');
		if ((addon_installed('calendar')) && (has_privilege(get_member(),'scheduled_publication_times')) && (!is_null($schedule)) && ($schedule>time()))
		{
			$validated=0;
		} else $schedule=NULL;

		if (!is_null($main_news_category))
		{
			$owner=$GLOBALS['SITE_DB']->query_select_value('news_categories','nc_owner',array('id'=>intval($main_news_category)));
			if ((!is_null($owner)) && ($owner!=get_member())) check_privilege('can_submit_to_others_categories',array('news',$main_news_category));
		}

		$meta_data=actual_meta_data_get_fields('news',NULL);

		$id=add_news($title,$news,$author,$validated,$allow_rating,$allow_comments,$allow_trackbacks,$notes,$news_article,$main_news_category,$news_category,$meta_data['add_time'],$meta_data['submitter'],$meta_data['views'],NULL,NULL,$url);

		$main_news_category=$GLOBALS['SITE_DB']->query_select_value('news','news_category',array('id'=>$id));
		$this->donext_type=$main_news_category;

		if (($validated==1) || (!addon_installed('unvalidated')))
		{
			$is_blog=!is_null($GLOBALS['SITE_DB']->query_select_value('news_categories','nc_owner',array('id'=>$main_news_category)));

			if (has_actual_page_access(get_modal_user(),'news')) // NB: no category permission check, as syndication choice was explicit, and news categorisation is a bit more complex
			{
				require_code('activities');
				syndicate_described_activity($is_blog?'news:ACTIVITY_ADD_NEWS_BLOG':'news:ACTIVITY_ADD_NEWS',$title,'','','_SEARCH:news:view:'.strval($id),'','','news',1,NULL,true);
			}
		}

		if (!is_null($schedule))
		{
			require_code('calendar');
			$schedule_code=':$GLOBALS[\'SITE_DB\']->query_update(\'news\',array(\'date_and_time\'=>$GLOBALS[\'_EVENT_TIMESTAMP\'],\'validated\'=>1),array(\'id\'=>'.strval($id).'),\'\',1);';
			$start_year=post_param_integer('schedule_year');
			$start_month=post_param_integer('schedule_month');
			$start_day=post_param_integer('schedule_day');
			$start_hour=post_param_integer('schedule_hour');
			$start_minute=post_param_integer('schedule_minute');
			require_code('calendar2');
			$event_id=add_calendar_event(db_get_first_id(),'',NULL,0,do_lang('PUBLISH_NEWS',$title),$schedule_code,3,0,$start_year,$start_month,$start_day,'day_of_month',$start_hour,$start_minute);
			regenerate_event_reminder_jobs($event_id,true);
		}

		if (addon_installed('content_reviews'))
			content_review_set('news',strval($id));

		return strval($id);
	}

	/**
	 * Standard crud_module edit actualiser.
	 *
	 * @param  ID_TEXT		The entry being edited
	 */
	function edit_actualisation($_id)
	{
		$id=intval($_id);

		$validated=post_param_integer('validated',fractional_edit()?INTEGER_MAGIC_NULL:0);

		$news_article=post_param('post',STRING_MAGIC_NULL);
		if (post_param('main_news_category')!='personal') $main_news_category=post_param_integer('main_news_category',INTEGER_MAGIC_NULL);
		else warn_exit(do_lang_tempcode('INTERNAL_ERROR'));

		$news_category=mixed();
		$news_category=array();
		if (array_key_exists('news_category',$_POST))
		{
			foreach ($_POST['news_category'] as $val)
			{
				$news_category[]=intval($val);
			}
		}
		if (fractional_edit()) $news_category=NULL;

		$allow_rating=post_param_integer('allow_rating',fractional_edit()?INTEGER_MAGIC_NULL:0);
		$allow_comments=post_param_integer('allow_comments',fractional_edit()?INTEGER_MAGIC_NULL:0);
		$allow_trackbacks=post_param_integer('allow_trackbacks',fractional_edit()?INTEGER_MAGIC_NULL:0);
		$notes=post_param('notes',STRING_MAGIC_NULL);

		$this->donext_type=$main_news_category;

		if (!fractional_edit())
		{
			$urls=get_url('','file','uploads/grepimages',0,OCP_UPLOAD_IMAGE);
			$url=$urls[0];
			if (($url=='') && (post_param_integer('file_unlink',0)!=1)) $url=NULL;
		} else
		{
			$url=STRING_MAGIC_NULL;
		}

		$owner=$GLOBALS['SITE_DB']->query_select_value_if_there('news_categories','nc_owner',array('id'=>$main_news_category)); // if_there in case somehow category setting corrupted
		if ((!is_null($owner)) && ($owner!=get_member())) check_privilege('can_submit_to_others_categories',array('news',$main_news_category));

		$schedule=get_input_date('schedule');

		if ((addon_installed('calendar')) && (!fractional_edit()) && (has_privilege(get_member(),'scheduled_publication_times')))
		{
			require_code('calendar2');
			$schedule_code=':$GLOBALS[\'SITE_DB\']->query_update(\'news\',array(\'date_and_time\'=>$GLOBALS[\'_EVENT_TIMESTAMP\'],\'validated\'=>1),array(\'id\'=>'.strval($id).'),\'\',1);';
			$past_event=$GLOBALS['SITE_DB']->query_select_value_if_there('calendar_events e LEFT JOIN '.$GLOBALS['SITE_DB']->get_table_prefix().'translate t ON e.e_content=t.id','e.id',array('text_original'=>$schedule_code));
			require_code('calendar');
			if (!is_null($past_event))
			{
				delete_calendar_event($past_event);
			}

			if ((!is_null($schedule)) && ($schedule>time()))
			{
				$validated=0;

				$start_year=post_param_integer('schedule_year');
				$start_month=post_param_integer('schedule_month');
				$start_day=post_param_integer('schedule_day');
				$start_hour=post_param_integer('schedule_hour');
				$start_minute=post_param_integer('schedule_minute');
				$event_id=add_calendar_event(db_get_first_id(),'none',NULL,0,do_lang('PUBLISH_NEWS',0,post_param('title')),$schedule_code,3,0,$start_year,$start_month,$start_day,'day_of_month',$start_hour,$start_minute);
				regenerate_event_reminder_jobs($event_id,true);
			}
		}

		$title=post_param('title',STRING_MAGIC_NULL);

		if (($validated==1) && ($GLOBALS['SITE_DB']->query_select_value('news','validated',array('id'=>intval($id)))==0)) // Just became validated, syndicate as just added
		{
			$is_blog=!is_null($GLOBALS['SITE_DB']->query_select_value('news_categories','nc_owner',array('id'=>$main_news_category)));

			$submitter=$GLOBALS['SITE_DB']->query_select_value('news','submitter',array('id'=>$id));
			$activity_title=($is_blog?'news:ACTIVITY_ADD_NEWS_BLOG':'news:ACTIVITY_ADD_NEWS');
			$activity_title_validate=($is_blog?'news:ACTIVITY_VALIDATE_NEWS_BLOG':'news:ACTIVITY_VALIDATE_NEWS');

			if (has_actual_page_access(get_modal_user(),'news'))
			{
				require_code('activities');
				syndicate_described_activity(($submitter!=get_member())?$activity_title_validate:$activity_title,$title,'','','_SEARCH:news:view:'.strval($id),'','','news',1,$submitter,true);
			}
		}

		$meta_data=actual_meta_data_get_fields('news',strval($id));

		edit_news($id,$title,post_param('news',STRING_MAGIC_NULL),post_param('author',STRING_MAGIC_NULL),$validated,$allow_rating,$allow_comments,$allow_trackbacks,$notes,$news_article,$main_news_category,$news_category,post_param('meta_keywords',STRING_MAGIC_NULL),post_param('meta_description',STRING_MAGIC_NULL),$url,$meta_data['add_time'],$meta_data['edit_time'],$meta_data['views'],$meta_data['submitter'],true);

		if (addon_installed('content_reviews'))
			content_review_set('news',strval($id));
	}

	/**
	 * Standard crud_module delete actualiser.
	 *
	 * @param  ID_TEXT		The entry being deleted
	 */
	function delete_actualisation($_id)
	{
		$id=intval($_id);

		delete_news($id);
	}

	/**
	 * The do-next manager for after download content management (events only).
	 *
	 * @param  tempcode		The title (output of get_screen_title)
	 * @param  tempcode		Some description to show, saying what happened
	 * @param  ?AUTO_LINK	The ID of whatever was just handled (NULL: N/A)
	 * @return tempcode		The UI
	 */
	function do_next_manager($title,$description,$id)
	{
		return $this->cat_crud_module->_do_next_manager($title,$description,is_null($id)?NULL:intval($id),$this->donext_type);
	}

	/**
	 * The UI to import news
	 *
	 * @return tempcode		The UI
	 */
	function import_news()
	{
		check_privilege('mass_import');

		$lang=post_param('lang',user_lang());

		$title=get_screen_title('IMPORT_NEWS');

		$post_url=build_url(array('page'=>'_SELF','type'=>'_import_news','old_type'=>get_param('type','')),'_SELF');
		$submit_name=do_lang_tempcode('IMPORT_NEWS');

		require_code('form_templates');

		require_code('news2');
		$fields=import_rss_fields(false);

		$hidden=new ocp_tempcode();
		$hidden->attach(form_input_hidden('lang',$lang));
		handle_max_file_size($hidden);

		return do_template('FORM_SCREEN',array('_GUID'=>'4ac8c667fa38c1e6338eedcb138e7fd4','TITLE'=>$title,'TEXT'=>do_lang_tempcode('IMPORT_NEWS_TEXT'),'HIDDEN'=>$hidden,'FIELDS'=>$fields,'SUBMIT_NAME'=>$submit_name,'URL'=>$post_url));
	}

	/**
	 * The actualiser to import news
	 *
	 * @return tempcode		The UI
	 */
	function _import_news()
	{
		check_privilege('mass_import');

		$title=get_screen_title('IMPORT_NEWS');

		require_code('news2');

		import_rss();

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('MANAGE_NEWS')),array('_SELF:_SELF:import',do_lang_tempcode('IMPORT_NEWS'))));
		breadcrumb_set_self(do_lang_tempcode('DONE'));

		return inform_screen($title,do_lang_tempcode('IMPORT_NEWS_DONE'));
	}
}

/**
 * Module page class.
 */
class Module_cms_news_cat extends standard_crud_module
{
	var $lang_type='NEWS_CATEGORY';
	var $select_name='TITLE';
	var $permissions_require='cat_high';
	var $permission_module='news';
	var $menu_label='NEWS';
	var $table='news_categories';
	var $orderer='nc_title';
	var $title_is_multi_lang=true;

	/**
	 * Standard crud_module table function.
	 *
	 * @param  array			Details to go to build_url for link to the next screen.
	 * @return array			A pair: The choose table, Whether re-ordering is supported from this screen.
	 */
	function nice_get_choose_table($url_map)
	{
		require_code('templates_results_table');

		$current_ordering=get_param('sort','nc_title ASC',true);
		list($sortable,$sort_order)=array(substr($current_ordering,0,strrpos($current_ordering,' ')),substr($current_ordering,strrpos($current_ordering,' ')+1));
		$sortables=array(
			'nc_title'=>do_lang_tempcode('TITLE'),
		);
		if (db_has_subqueries($GLOBALS['SITE_DB']->connection_read))
		{
			$sortables['((SELECT COUNT(*) FROM '.get_table_prefix().'news WHERE news_category=r.id) + (SELECT COUNT(*) FROM '.get_table_prefix().'news_category_entries WHERE news_entry_category=r.id))']=do_lang_tempcode('COUNT_TOTAL');
		}
		if (((strtoupper($sort_order)!='ASC') && (strtoupper($sort_order)!='DESC')) || (!array_key_exists($sortable,$sortables)))
			log_hack_attack_and_exit('ORDERBY_HACK');
		inform_non_canonical_parameter('sort');

		$header_row=results_field_title(array(
			do_lang_tempcode('TITLE'),
			do_lang_tempcode('COUNT_TOTAL'),
			do_lang_tempcode('ACTIONS'),
		),$sortables,'sort',$sortable.' '.$sort_order);

		$fields=new ocp_tempcode();

		require_code('form_templates');
		list($rows,$max_rows)=$this->get_entry_rows(false,$current_ordering);
		foreach ($rows as $row)
		{
			$edit_link=build_url($url_map+array('id'=>$row['id']),'_SELF');

			$total=$GLOBALS['SITE_DB']->query_select_value('news','COUNT(*)',array('news_category'=>$row['id']));
			$total+=$GLOBALS['SITE_DB']->query_select_value('news_category_entries','COUNT(*)',array('news_entry_category'=>$row['id']));

			$fields->attach(results_entry(array(protect_from_escaping(hyperlink(build_url(array('page'=>'news','type'=>'archive','filter'=>$row['id']),get_module_zone('news')),get_translated_text($row['nc_title']))),integer_format($total),protect_from_escaping(hyperlink($edit_link,do_lang_tempcode('EDIT'),false,true,'#'.strval($row['id']))))),true);
		}

		return array(results_table(do_lang($this->menu_label),get_param_integer('start',0),'start',get_param_integer('max',20),'max',$max_rows,$header_row,$fields,$sortables,$sortable,$sort_order),false);
	}

	/**
	 * Standard crud_module list function.
	 *
	 * @return tempcode		The selection list
	 */
	function nice_get_entries()
	{
		return nice_get_news_categories(NULL,false,false,true);
	}

	/**
	 * Get tempcode for a news category adding/editing form.
	 *
	 * @param  ?AUTO_LINK	The news category ID (NULL: new)
	 * @param  SHORT_TEXT	The title of the news category
	 * @param  SHORT_TEXT	The news category image
	 * @param  LONG_TEXT		Notes relating to the news category
	 * @param  ?MEMBER		The owner of the news category (NULL: public)
	 * @param  ?AUTO_LINK	The ID of this news category (NULL: we haven't added it yet)
	 * @return array			A pair: The input fields, Hidden fields
	 */
	function get_form_fields($id=NULL,$title='',$img='',$notes='',$owner=NULL,$category_id=NULL)
	{
		$fields=new ocp_tempcode();
		$hidden=new ocp_tempcode();

		require_code('form_templates');
		$fields->attach(form_input_line_comcode(do_lang_tempcode('TITLE'),do_lang_tempcode('DESCRIPTION_TITLE'),'title',$title,true));

		require_code('themes2');
		$ids=get_all_image_ids_type('newscats');

		if (get_base_url()==get_forum_base_url())
		{
			$set_name='rep_image';
			$required=true;
			$set_title=do_lang_tempcode('IMAGE');
			$field_set=(count($ids)==0)?new ocp_tempcode():alternate_fields_set__start($set_name);

			$field_set->attach(form_input_upload(do_lang_tempcode('UPLOAD'),do_lang_tempcode('DESCRIPTION_UPLOAD'),'file',$required,NULL,NULL,true,str_replace(' ','',get_option('valid_images'))));

			$image_chooser_field=form_input_theme_image(do_lang_tempcode('STOCK'),'','theme_img_code',$ids,NULL,$img,NULL,false);
			$field_set->attach($image_chooser_field);

			$fields->attach(alternate_fields_set__end($set_name,$set_title,'',$field_set,$required));

			handle_max_file_size($hidden,'image');
		} else
		{
			if (count($ids)==0) warn_exit(do_lang_tempcode('NO_SELECTABLE_THEME_IMAGES_MSN','newscats'));

			$image_chooser_field=form_input_theme_image(do_lang_tempcode('STOCK'),'','theme_img_code',$ids,NULL,$img,NULL,true);
			$fields->attach($image_chooser_field);
		}

		if (get_option('enable_staff_notes')=='1')
		{
			$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('_GUID'=>'b88f1b286b05e18991ad51538812f7b2','SECTION_HIDDEN'=>$notes=='','TITLE'=>do_lang_tempcode('ADVANCED'))));
			$fields->attach(form_input_text(do_lang_tempcode('NOTES'),do_lang_tempcode('DESCRIPTION_NOTES'),'notes',$notes,false));
		}

		$fields->attach(meta_data_get_fields('news_category',is_null($id)?NULL:strval($id)),true);

		if (addon_installed('content_reviews'))
			$fields->attach(content_review_get_fields('news_category',is_null($id)?NULL:strval($id)));

		$fields->attach($this->get_permission_fields(is_null($category_id)?'':strval($category_id),NULL,($title=='')));

		return array($fields,$hidden);
	}

	/**
	 * Standard crud_module edit form filler.
	 *
	 * @param  ID_TEXT		The entry being edited
	 * @return array			A pair: The input fields, Hidden fields
	 */
	function fill_in_edit_form($_id)
	{
		$id=intval($_id);

		$rows=$GLOBALS['SITE_DB']->query_select('news_categories',array('*'),array('id'=>$id),'',1);
		if (!array_key_exists(0,$rows))
		{
			warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		}
		$myrow=$rows[0];

		return $this->get_form_fields($id,get_translated_text($myrow['nc_title']),$myrow['nc_img'],$myrow['notes'],$myrow['nc_owner'],$myrow['id']);
	}

	/**
	 * Standard crud_module add actualiser.
	 *
	 * @return ID_TEXT		The entry added
	 */
	function add_actualisation()
	{
		require_code('themes2');

		$title=post_param('title');
		$img=get_theme_img_code('newscats',true);
		$notes=post_param('notes','');

		$meta_data=actual_meta_data_get_fields('news_category',NULL);

		$id=add_news_category($title,$img,$notes);

		$this->set_permissions($id);

		if (addon_installed('content_reviews'))
			content_review_set('news_category',strval($id));

		return strval($id);
	}

	/**
	 * Standard crud_module edit actualiser.
	 *
	 * @param  ID_TEXT		The entry being edited
	 */
	function edit_actualisation($id)
	{
		require_code('themes2');

		$title=post_param('title');
		$img=get_theme_img_code('newscats',true);
		if (fractional_edit()) $img=STRING_MAGIC_NULL;
		$notes=post_param('notes',STRING_MAGIC_NULL);

		$meta_data=actual_meta_data_get_fields('news_category',$id);

		if (is_null($meta_data['submitter'])) // We need to interpret this - if we didn't have specification permission, we need to copy through existing setting, as a NULL would imply a de-set
		{
			$meta_data['submitter']=$GLOBALS['SITE_DB']->query_select_value_if_there('news_categories','nc_owner',array('id'=>intval($id)));
		}

		edit_news_category(intval($id),$title,$img,$notes,$meta_data['submitter']);

		$this->set_permissions(intval($id));

		if (addon_installed('content_reviews'))
			content_review_set('news_category',$id);
	}

	/**
	 * Standard crud_module submitter getter.
	 *
	 * @param  ID_TEXT		The entry for which the submitter is sought
	 * @return ?MEMBER		The submitter (NULL: none)
	 */
	function get_submitter($id)
	{
		return $GLOBALS['SITE_DB']->query_select_value_if_there('news_categories','nc_owner',array('id'=>intval($id)));
	}

	/**
	 * Standard crud_module delete actualiser.
	 *
	 * @param  ID_TEXT		The entry being deleted
	 */
	function delete_actualisation($id)
	{
		delete_news_category(intval($id));
	}

	/**
	 * The do-next manager for after download content management (event types only).
	 *
	 * @param  tempcode		The title (output of get_screen_title)
	 * @param  tempcode		Some description to show, saying what happened
	 * @param  ?AUTO_LINK	The ID of whatever was just handled (NULL: N/A)
	 * @return tempcode		The UI
	 */
	function do_next_manager($title,$description,$id)
	{
		return $this->_do_next_manager($title,$description,NULL,is_null($id)?NULL:intval($id));
	}

	/**
	 * The do-next manager for after news content management.
	 *
	 * @param  tempcode		The title (output of get_screen_title)
	 * @param  tempcode		Some description to show, saying what happened
	 * @param  ?AUTO_LINK	The ID of whatever was just handled (NULL: N/A)
	 * @param  ?AUTO_LINK	The category ID we were working in (NULL: deleted)
	 * @return tempcode		The UI
	 */
	function _do_next_manager($title,$description,$id=NULL,$cat=NULL)
	{
		breadcrumb_set_self(do_lang_tempcode('DONE'));

		require_code('templates_donext');

		if ((is_null($id)) && (is_null($cat)))
		{
			return do_next_manager($title,$description,
						NULL,
						NULL,
						/*		TYPED-ORDERED LIST OF 'LINKS'		*/
						/*	 page	 params				  zone	  */
						array('_SELF',array('type'=>'ad'),'_SELF'),							// Add one
						NULL,							 // Edit this
						has_privilege(get_member(),'edit_own_highrange_content','cms_news')?array('_SELF',array('type'=>'ed'),'_SELF'):NULL,											// Edit one
						NULL,							// View this
						array('news',array('type'=>'misc'),get_module_zone('news')),									 // View archive
						NULL,	  // Add to category
						has_privilege(get_member(),'submit_cat_highrange_content','cms_news')?array('_SELF',array('type'=>'ac'),'_SELF'):NULL,					  // Add one category
						has_privilege(get_member(),'edit_own_cat_highrange_content','cms_news')?array('_SELF',array('type'=>'ec'),'_SELF'):NULL,					  // Edit one category
						NULL,			 // Edit this category
						NULL																						 // View this category
			);
		}

		return do_next_manager($title,$description,
					NULL,
					NULL,
					/*		TYPED-ORDERED LIST OF 'LINKS'		*/
					/*	 page	 params				  zone	  */
					array('_SELF',array('type'=>'ad','cat'=>$cat),'_SELF'),							// Add one
					(is_null($id) || (!has_privilege(get_member(),'edit_own_highrange_content','cms_news',array('news',$cat))))?NULL:array('_SELF',array('type'=>'_ed','id'=>$id),'_SELF'),							 // Edit this
					has_privilege(get_member(),'edit_own_highrange_content','cms_news')?array('_SELF',array('type'=>'ed'),'_SELF'):NULL,											// Edit one
					is_null($id)?NULL:array('news',array('type'=>'view','id'=>$id),get_module_zone('news')),							// View this
					array('news',array('type'=>'misc'),get_module_zone('news')),									 // View archive
					(!is_null($id))?NULL:array('_SELF',array('type'=>'ad','cat'=>$cat),'_SELF'),	  // Add to category
					has_privilege(get_member(),'submit_cat_highrange_content','cms_news')?array('_SELF',array('type'=>'ac'),'_SELF'):NULL,					  // Add one category
					has_privilege(get_member(),'edit_own_cat_highrange_content','cms_news')?array('_SELF',array('type'=>'ec'),'_SELF'):NULL,					  // Edit one category
					is_null($cat)?NULL:has_privilege(get_member(),'edit_own_cat_highrange_content','cms_news')?array('_SELF',array('type'=>'_ec','id'=>$cat),'_SELF'):NULL,			 // Edit this category
					NULL																						 // View this category
		);
	}

}


