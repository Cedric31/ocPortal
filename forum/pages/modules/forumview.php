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

/**
 * Module page class.
 */
class Module_forumview
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
		return array('!'=>'ROOT_FORUM');
	}

	/**
	 * Standard modular page-link finder function (does not return the main entry-points that are not inside the tree).
	 *
	 * @param  ?integer  The number of tree levels to computer (NULL: no limit)
	 * @param  boolean	Whether to not return stuff that does not support permissions (unless it is underneath something that does).
	 * @param  ?string	Position to start at in the tree. Does not need to be respected. (NULL: from root)
	 * @param  boolean	Whether to avoid returning categories.
	 * @return ?array 	A tuple: 1) full tree structure [made up of (pagelink, permission-module, permissions-id, title, children, ?entry point for the children, ?children permission module, ?whether there are children) OR a list of maps from a get_* function] 2) permissions-page 3) optional base entry-point for the tree 4) optional permission-module 5) optional permissions-id (NULL: disabled).
	 */
	function get_page_links($max_depth=NULL,$require_permission_support=false,$start_at=NULL,$dont_care_about_categories=false)
	{
		unset($require_permission_support);

		$permission_page='topics';

		require_code('ocf_forums');
		require_code('ocf_forums2');
		$forum_id=NULL;
		if (!is_null($start_at))
		{
			$matches=array();
			if (preg_match('#[^:]*:forumview:type=misc:id=(.*)#',$start_at,$matches)!=0) $forum_id=intval($matches[1]);
		}

		$adjusted_max_depth=is_null($max_depth)?NULL:(is_null($forum_id)?($max_depth):$max_depth);
		if (get_forum_type()=='ocf')
		{
			$structure=$dont_care_about_categories?array():ocf_get_forum_tree_secure(NULL,$forum_id,false,NULL,'',NULL,NULL,false,$adjusted_max_depth,true);
		} else
		{
			$structure=array();
		}
		return array($structure,$permission_page,'_SELF:_SELF:type=misc:id=!','forums');
	}

	/**
	 * Standard modular new-style deep page-link finder function (does not return the main entry-points).
	 *
	 * @param  string  	Callback function to send discovered page-links to.
	 * @param  MEMBER		The member we are finding stuff for (we only find what the member can view).
	 * @param  integer	Code for how deep we are tunnelling down, in terms of whether we are getting entries as well as categories.
	 * @param  string		Stub used to create page-links. This is passed in because we don't want to assume a zone or page name within this function.
	 * @param  ?string	Where we're looking under (NULL: root of tree). We typically will NOT show a root node as there's often already an entry-point representing it.
	 * @param  integer	Our recursion depth (used to calculate importance of page-link, used for instance by Google sitemap). Deeper is typically less important.
	 * @param  ?array		Non-standard for API [extra parameter tacked on] (NULL: yet unknown). Contents of database table for performance.
	 * @param  ?array		Non-standard for API [extra parameter tacked on] (NULL: yet unknown). Contents of database table for performance.
	 */
	function get_sitemap_pagelinks($callback,$member_id,$depth,$pagelink_stub,$parent_pagelink=NULL,$recurse_level=0,$category_data=NULL,$entry_data=NULL)
	{
		// This is where we start
		if (is_null($parent_pagelink))
		{
			$parent_pagelink=$pagelink_stub.':misc'; // This is the entry-point we're under
			$parent_attributes=array('id'=>strval(db_get_first_id()));
		} else
		{
			list(,$parent_attributes,)=page_link_decode($parent_pagelink);
		}

		// We read in all data for efficiency
		if (is_null($category_data))
		{
			$category_data=$GLOBALS['FORUM_DB']->query_select('f_forums',array('id','f_name AS title','f_parent_forum AS parent_id','f_cache_last_time AS edit_date'),NULL,'',300);
			if (count($category_data)==300) // Ah, we need to limit things then
			{
				$category_data=$GLOBALS['FORUM_DB']->query_select('f_forums',array('id','f_name AS title','f_parent_forum AS parent_id','f_cache_last_time AS edit_date'),array('f_cache_num_posts'),'',300);
			}
		}

		// Subcategories
		foreach ($category_data as $row)
		{
			if ((!is_null($row['parent_id'])) && (strval($row['parent_id'])==$parent_attributes['id']))
			{
				$pagelink=$pagelink_stub.'id='.strval($row['id']);
				if (__CLASS__!='')
				{
					$this->get_sitemap_pagelinks($callback,$member_id,$depth,$pagelink_stub,$pagelink,$recurse_level+1,$category_data,$entry_data); // Recurse
				} else
				{
					call_user_func_array(__FUNCTION__,array($callback,$member_id,$depth,$pagelink_stub,$pagelink,$recurse_level+1,$category_data,$entry_data)); // Recurse
				}
				if (has_category_access($member_id,'forums',strval($row['id'])))
				{
					call_user_func_array($callback,array($pagelink,$parent_pagelink,NULL,$row['edit_date'],max(0.7-$recurse_level*0.1,0.3),$row['title'])); // Callback
				} else // Not accessible: we need to copy the node through, but we will flag it 'Unknown' and say it's not accessible.
				{
					call_user_func_array($callback,array($pagelink,$parent_pagelink,NULL,$row['edit_date'],max(0.7-$recurse_level*0.1,0.3),do_lang('UNKNOWN'),false)); // Callback
				}
			}
		}

		// Entries
		if (($depth>=DEPTH__ENTRIES) && (has_category_access($member_id,'forums',$parent_attributes['id'])))
		{
			$start=0;
			do
			{
				$entry_data=$GLOBALS['FORUM_DB']->query_select('f_topics',array('id','t_cache_first_title AS title','t_forum_id AS category_id','t_cache_first_time AS add_date','t_cache_last_time AS edit_date'),array('t_forum_id'=>intval($parent_attributes['id'])),'',500,$start);

				foreach ($entry_data as $row)
				{
					$pagelink='forum:topicview:misc:'.strval($row['id']);
					call_user_func_array($callback,array($pagelink,$parent_pagelink,$row['add_date'],$row['edit_date'],0.2,$row['title'])); // Callback
				}

				$start+=500;
			}
			while (array_key_exists(0,$entry_data));
		}
	}

	/**
	 * Convert a page link to a category ID and category permission module type.
	 *
	 * @param  string	The page link
	 * @return array	The pair
	 */
	function extract_page_link_permissions($page_link)
	{
		$matches=array();
		preg_match('#^([^:]*):([^:]*):type=misc:id=(.*)$#',$page_link,$matches);
		return array($matches[3],'forums');
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		delete_menu_item_simple('_SEARCH:forumview:type=misc');
		delete_menu_item_simple('_SEARCH:forumview:type=pt:id={$USER_OVERIDE}');
		delete_menu_item_simple('_SEARCH:forumview:type=pt');
		delete_menu_item_simple('_SEARCH:vforums:type=misc');
		delete_menu_item_simple('_SEARCH:vforums:type=unread');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		require_lang('ocf');
		add_menu_item_simple('forum_features',NULL,'ROOT_FORUM','_SEARCH:forumview:type=misc');
		add_menu_item_simple('forum_features',NULL,'PRIVATE_TOPICS','_SEARCH:forumview:type=pt');
		add_menu_item_simple('forum_features',NULL,'POSTS_SINCE_LAST_VISIT','_SEARCH:vforums:type=misc');
		add_menu_item_simple('forum_features',NULL,'TOPICS_UNREAD','_SEARCH:vforums:type=unread');
		add_menu_item_simple('forum_features',NULL,'RECENTLY_READ','_SEARCH:vforums:type=recently_read');
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		if (get_forum_type()!='ocf') warn_exit(do_lang_tempcode('NO_OCF')); else ocf_require_all_forum_stuff();
		require_code('ocf_forumview');

		global $NON_CANONICAL_PARAMS;
		foreach (array_keys($_GET) as $key)
			if (substr($key,0,3)=='kfs') $NON_CANONICAL_PARAMS[]=$key;
		$NON_CANONICAL_PARAMS[]='order';

		$type=get_param('type','misc');

		$current_filter_cat=get_param('category','');

		$max=get_param_integer('max',intval(get_option('forum_topics_per_page')));

		$root=get_param_integer('keep_forum_root',db_get_first_id());

		if ($type=='pt') // Not used anymore by default, but code still here
		{
			$id=NULL;
			$start=get_param_integer('start',get_param_integer('kfs',0));
			$of_member_id=get_param_integer('id',get_member());
		} else
		{
			$id=get_param_integer('id',db_get_first_id());
			$start=get_param_integer('start',get_param_integer('kfs'.strval($id),0));
			$of_member_id=NULL;
		}

		require_code('ocf_general');
		ocf_set_context_forum($id);

		$test=ocf_render_forumview($id,$current_filter_cat,$max,$start,$root,$of_member_id);
		if (is_array($test))
		{
			list($content,$ltitle,$breadcrumbs,$forum_name)=$test;
		} else
		{
			return $test;
		}

		if ($type!='pt')
		{
			global $SEO_TITLE;
			$SEO_TITLE=$forum_name;

			breadcrumb_add_segment($breadcrumbs);
		}

		if (addon_installed('awards'))
		{
			require_code('awards');
			$awards=is_null($id)?array():find_awards_for('forum',strval($id));
		} else $awards=array();

		$title=get_screen_title($ltitle,false,NULL,NULL,$awards);

		return do_template('OCF_FORUM_SCREEN',array('_GUID'=>'9e9fd9110effd8a92b7a839a4fea60c5','TITLE'=>$title,'CONTENT'=>$content));
	}

}


