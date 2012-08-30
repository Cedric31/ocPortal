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
 * @package		polls
 */

/**
 * Module page class.
 */
class Module_polls
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
		$info['version']=5;
		$info['update_require_upgrade']=1;
		$info['locked']=false;
		return $info;
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		$GLOBALS['SITE_DB']->drop_table_if_exists('poll');
		$GLOBALS['SITE_DB']->drop_table_if_exists('poll_votes');

		delete_privilege('choose_poll');
		$GLOBALS['SITE_DB']->query_delete('trackbacks',array('trackback_for_type'=>'polls'));

		delete_config_option('points_ADD_POLL');
		delete_config_option('points_CHOOSE_POLL');
		delete_config_option('poll_update_time');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		if (is_null($upgrade_from))
		{
			$GLOBALS['SITE_DB']->create_table('poll',array(
				'id'=>'*AUTO',
				'question'=>'SHORT_TRANS',	// Comcode
				'option1'=>'SHORT_TRANS',	// Comcode
				'option2'=>'SHORT_TRANS',	// Comcode
				'option3'=>'?SHORT_TRANS',	// Comcode
				'option4'=>'?SHORT_TRANS',	// Comcode
				'option5'=>'?SHORT_TRANS',	// Comcode
				'option6'=>'SHORT_TRANS',	// Comcode
				'option7'=>'SHORT_TRANS',	// Comcode
				'option8'=>'?SHORT_TRANS',	// Comcode
				'option9'=>'?SHORT_TRANS',	// Comcode
				'option10'=>'?SHORT_TRANS',	// Comcode
				'votes1'=>'INTEGER',
				'votes2'=>'INTEGER',
				'votes3'=>'INTEGER',
				'votes4'=>'INTEGER',
				'votes5'=>'INTEGER',
				'votes6'=>'INTEGER',
				'votes7'=>'INTEGER',
				'votes8'=>'INTEGER',
				'votes9'=>'INTEGER',
				'votes10'=>'INTEGER',
				'allow_rating'=>'BINARY',
				'allow_comments'=>'SHORT_INTEGER',
				'allow_trackbacks'=>'BINARY',
				'notes'=>'LONG_TEXT',
				'num_options'=>'SHORT_INTEGER',
				'is_current'=>'BINARY',
				'date_and_time'=>'?TIME',
				'submitter'=>'USER',
				'add_time'=>'INTEGER',
				'poll_views'=>'INTEGER',
				'edit_date'=>'?TIME'
			));

			$GLOBALS['SITE_DB']->create_index('poll','poll_views',array('poll_views'));
			$GLOBALS['SITE_DB']->create_index('poll','get_current',array('is_current'));
			$GLOBALS['SITE_DB']->create_index('poll','ps',array('submitter'));
			$GLOBALS['SITE_DB']->create_index('poll','padd_time',array('add_time'));
			$GLOBALS['SITE_DB']->create_index('poll','date_and_time',array('date_and_time'));

			add_privilege('POLLS','choose_poll',false);

			add_config_option('ADD_POLL','points_ADD_POLL','integer','return addon_installed(\'points\')?\'150\':NULL;','POINTS','COUNT_POINTS_GIVEN');
			add_config_option('CHOOSE_POLL','points_CHOOSE_POLL','integer','return addon_installed(\'points\')?\'35\':NULL;','POINTS','COUNT_POINTS_GIVEN');
			add_config_option('POLL_REGULARITY','poll_update_time','integer','return \'168\';','ADMIN','CHECK_LIST');
			$GLOBALS['SITE_DB']->create_index('poll','ftjoin_pq',array('question'));
			$GLOBALS['SITE_DB']->create_index('poll','ftjoin_po1',array('option1'));
			$GLOBALS['SITE_DB']->create_index('poll','ftjoin_po2',array('option2'));
			$GLOBALS['SITE_DB']->create_index('poll','ftjoin_po3',array('option3'));
			$GLOBALS['SITE_DB']->create_index('poll','ftjoin_po4',array('option4'));
			$GLOBALS['SITE_DB']->create_index('poll','ftjoin_po5',array('option5'));
		}

		if ((is_null($upgrade_from)) || ($upgrade_from<5))
		{
			$GLOBALS['SITE_DB']->create_table('poll_votes',array(
				'id'=>'*AUTO',
				'v_poll_id'=>'AUTO_LINK',
				'v_voter_id'=>'?USER',
				'v_voter_ip'=>'IP',
				'v_vote_for'=>'?SHORT_INTEGER',
			));

			$GLOBALS['SITE_DB']->create_index('poll_votes','v_voter_id',array('v_voter_id'));
			$GLOBALS['SITE_DB']->create_index('poll_votes','v_voter_ip',array('v_voter_ip'));
			$GLOBALS['SITE_DB']->create_index('poll_votes','v_vote_for',array('v_vote_for'));
		}

		if ((!is_null($upgrade_from)) && ($upgrade_from<5))
		{
			$polls=$GLOBALS['SITE_DB']->query_select('poll',array('id','ip'));
			foreach ($polls as $poll)
			{
				$voters=explode('-',$poll['ip']);
				foreach ($voters as $voter)
				{
					$GLOBALS['SITE_DB']->query_insert('poll_votes',array(
						'v_poll_id'=>$poll['id'],
						'v_voter_id'=>is_numeric($voter)?intval($voter):NULL,
						'v_voter_ip'=>is_numeric($voter)?'':$voter,
						'v_vote_for'=>NULL,
					));
				}
			}
			$GLOBALS['SITE_DB']->delete_table_field('poll','ip');
		}
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('misc'=>'POLLS');
	}

	/**
	 * Standard modular page-link finder function (does not return the main entry-points that are not inside the tree).
	 *
	 * @param  ?integer  The number of tree levels to computer (NULL: no limit)
	 * @param  boolean	Whether to not return stuff that does not support permissions (unless it is underneath something that does).
	 * @param  ?string	Position to start at in the tree. Does not need to be respected. (NULL: from root)
	 * @param  boolean	Whether to avoid returning categories.
	 * @return ?array	 	A tuple: 1) full tree structure [made up of (pagelink, permission-module, permissions-id, title, children, ?entry point for the children, ?children permission module, ?whether there are children) OR a list of maps from a get_* function] 2) permissions-page 3) optional base entry-point for the tree 4) optional permission-module 5) optional permissions-id (NULL: disabled).
	 */
	function get_page_links($max_depth=NULL,$require_permission_support=false,$start_at=NULL,$dont_care_about_categories=false)
	{
		$permission_page='cms_polls';

		return array(array(),$permission_page);
	}

	/**
	 * Standard modular new-style deep page-link finder function (does not return the main entry-points).
	 *
	 * @param  string  	Callback function to send discovered page-links to.
	 * @param  MEMBER		The member we are finding stuff for (we only find what the member can view).
	 * @param  integer	Code for how deep we are tunnelling down, in terms of whether we are getting entries as well as categories.
	 * @param  string		Stub used to create page-links. This is passed in because we don't want to assume a zone or page name within this function.
	 */
	function get_sitemap_pagelinks($callback,$member_id,$depth,$pagelink_stub)
	{
		// Entries
		if ($depth>=DEPTH__ENTRIES)
		{
			$rows=$GLOBALS['SITE_DB']->query('SELECT c.question,c.id,t.text_original AS title,add_time,edit_date AS edit_time FROM '.get_table_prefix().'poll c LEFT JOIN '.get_table_prefix().'translate t ON '.db_string_equal_to('language',user_lang()).' AND t.id=c.question WHERE votes1+votes2+votes3+votes4+votes5+votes6+votes7+votes8+votes9+votes10<>0');

			foreach ($rows as $row)
			{
				if (is_null($row['title'])) $row['title']=get_translated_text($row['question']);

				$pagelink=$pagelink_stub.'view:'.strval($row['id']);
				call_user_func_array($callback,array($pagelink,$pagelink_stub.'misc',$row['add_time'],$row['edit_time'],0.2,$row['title'])); // Callback
			}
		}
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		set_feed_url(find_script('backend').'?mode=polls&filter=');

		require_code('feedback');
		require_code('polls');
		require_lang('polls');
		require_css('polls');

		// What are we doing?
		$type=get_param('type','misc');

		if ($type=='view') return $this->view();
		if ($type=='misc') return $this->view_polls();

		return new ocp_tempcode();
	}

	/**
	 * The UI to view a list of polls.
	 *
	 * @return tempcode		The UI
	 */
	function view_polls()
	{
		$title=get_screen_title('POLL_ARCHIVE');

		$start=get_param_integer('polls_start',0);
		$max=get_param_integer('polls_max',20);

		$total_polls=$GLOBALS['SITE_DB']->query_select_value('poll','COUNT(*)');
		if ($total_polls<500)
		{
			$rows=$GLOBALS['SITE_DB']->query('SELECT id,date_and_time FROM '.get_table_prefix().'poll WHERE votes1+votes2+votes3+votes4+votes5+votes6+votes7+votes8+votes9+votes10<>0 ORDER BY date_and_time DESC',$max,$start);
			$max_rows=$GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*) FROM '.get_table_prefix().'poll WHERE votes1+votes2+votes3+votes4+votes5+votes6+votes7+votes8+votes9+votes10<>0');
		} else
		{
			$rows=$GLOBALS['SITE_DB']->query('SELECT id,date_and_time FROM '.get_table_prefix().'poll WHERE date_and_time IS NOT NULL ORDER BY date_and_time DESC',$max,$start);
			$max_rows=$GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*) FROM '.get_table_prefix().'poll WHERE date_and_time IS NOT NULL');
		}
		$content=new ocp_tempcode();
		foreach ($rows as $myrow)
		{
			$poll=do_block('main_poll',array('param'=>strval($myrow['id'])));
			$content->attach($poll);
		}
		if ($content->is_empty()) inform_exit(do_lang_tempcode('NO_ENTRIES'));

		require_code('templates_pagination');
		$pagination=pagination(do_lang_tempcode('POLLS'),NULL,$start,'polls_start',$max,'polls_max',$max_rows);

		return do_template('PAGINATION_SCREEN',array('_GUID'=>'bed3e31c98b35fea52a991e381e6cfaa','TITLE'=>$title,'CONTENT'=>$content,'PAGINATION'=>$pagination));
	}

	/**
	 * The UI to view a poll.
	 *
	 * @return tempcode		The UI
	 */
	function view()
	{
		$title=get_screen_title('POLL');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('POLL_ARCHIVE'))));

		$id=get_param_integer('id');
		$_GET['poll_id']=strval($id);

		$rows=$GLOBALS['SITE_DB']->query_select('poll',array('*'),array('id'=>$id),'',1);
		if (!array_key_exists(0,$rows))
		{
			return warn_screen($title,do_lang_tempcode('MISSING_RESOURCE'));
		}
		$myrow=$rows[0];

		// Views
		if (get_db_type()!='xml')
		{
			$myrow['poll_views']++;
			$GLOBALS['SITE_DB']->query_update('poll',array('poll_views'=>$myrow['poll_views']),array('id'=>$id),'',1,NULL,false,true);
		}

		$date_raw=is_null($myrow['date_and_time'])?'':strval($myrow['date_and_time']);
		$add_date_raw=strval($myrow['add_time']);
		$edit_date_raw=is_null($myrow['edit_date'])?'':strval($myrow['edit_date']);
		$date=get_timezoned_date($myrow['date_and_time']);
		$add_date=get_timezoned_date($myrow['add_time']);
		$edit_date=get_timezoned_date($myrow['edit_date']);

		$_title=get_translated_text($myrow['question']);

		list($rating_details,$comment_details,$trackback_details)=embed_feedback_systems(
			get_page_name(),
			strval($id),
			$myrow['allow_rating'],
			$myrow['allow_comments'],
			$myrow['allow_trackbacks'],
			is_null($myrow['date_and_time'])?0:1,
			$myrow['submitter'],
			build_url(array('page'=>'_SELF','type'=>'view','id'=>$id),'_SELF',NULL,false,false,true),
			$_title,
			get_value('comment_forum__polls')
		);

		if ((has_actual_page_access(NULL,'cms_polls',NULL,NULL)) && (has_edit_permission('high',get_member(),$myrow['submitter'],'cms_polls')))
		{
			$edit_url=build_url(array('page'=>'cms_polls','type'=>'_ed','id'=>$id),get_module_zone('cms_polls'));
		} else $edit_url=new ocp_tempcode();

		$poll_details=do_block('main_poll');

		set_extra_request_metadata(array(
			'created'=>date('Y-m-d',$myrow['add_time']),
			'creator'=>$GLOBALS['FORUM_DRIVER']->get_username($myrow['submitter']),
			'publisher'=>'', // blank means same as creator
			'modified'=>is_null($myrow['edit_date'])?'':date('Y-m-d',$myrow['edit_date']),
			'type'=>'Poll',
			'title'=>$_title,
			'identifier'=>'_SEARCH:polls:view:'.strval($id),
			'description'=>'',
			'image'=>find_theme_image('bigicons/polls'),
		));

		return do_template('POLL_SCREEN',array('_GUID'=>'1463a42354c3ad154e2c6bb0c96be3b9','TITLE'=>$title,'SUBMITTER'=>strval($myrow['submitter']),'ID'=>strval($id),'DATE_RAW'=>$date_raw,'ADD_DATE_RAW'=>$add_date_raw,'EDIT_DATE_RAW'=>$edit_date_raw,'DATE'=>$date,'ADD_DATE'=>$add_date,'EDIT_DATE'=>$edit_date,'VIEWS'=>integer_format($myrow['poll_views']),'TRACKBACK_DETAILS'=>$trackback_details,'RATING_DETAILS'=>$rating_details,'COMMENT_DETAILS'=>$comment_details,'EDIT_URL'=>$edit_url,'POLL_DETAILS'=>$poll_details));
	}

}


