<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		stats_block
 */

class Hook_stats_forum
{

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		if (get_forum_type()!='none')
		{
			$bits=new ocp_tempcode();
			if (get_option('forum_show_stats_count_members',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'a2dbcdec813d5a5edbb416bf087b4a97','KEY'=>do_lang_tempcode('COUNT_MEMBERS'),'VALUE'=>integer_format($GLOBALS['FORUM_DRIVER']->get_members()))));
			if (get_forum_type()=='ocf')
			{
				if (get_option('forum_show_stats_count_members_new_today',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'fd2e149f6921836e3c2ea1039644e2e7','KEY'=>do_lang_tempcode('MEMBERS_NEW_TODAY'),'VALUE'=>integer_format($GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE m_join_time>'.strval(time()-60*60*24))))));
				if (get_option('forum_show_stats_count_members_new_this_week',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'10128b288dec4a578517de75cc9e404d','KEY'=>do_lang_tempcode('MEMBERS_NEW_THIS_WEEK'),'VALUE'=>integer_format($GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE m_join_time>'.strval(time()-60*60*24*7))))));
				if (get_option('forum_show_stats_count_members_new_this_month',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'b2dbcdec813d5a5edbb416bf087b4a97','KEY'=>do_lang_tempcode('MEMBERS_NEW_THIS_MONTH'),'VALUE'=>integer_format($GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE m_join_time>'.strval(time()-60*60*24*31))))));
				if (get_option('forum_show_stats_count_members_active_today',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'cc9760b2ed9e985e96b53c91c511e84e','KEY'=>do_lang_tempcode('MEMBERS_ACTIVE_TODAY'),'VALUE'=>integer_format($GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE m_last_visit_time>'.strval(time()-60*60*24))))));
				if (get_option('forum_show_stats_count_members_active_this_week',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'dc9760b2ed9e985e96b53c91c511e84e','KEY'=>do_lang_tempcode('MEMBERS_ACTIVE_THIS_WEEK'),'VALUE'=>integer_format($GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE m_last_visit_time>'.strval(time()-60*60*24*7))))));
				if (get_option('forum_show_stats_count_members_active_this_month',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'ec9760b2ed9e985e96b53c91c511e84e','KEY'=>do_lang_tempcode('MEMBERS_ACTIVE_THIS_MONTH'),'VALUE'=>integer_format($GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE m_last_visit_time>'.strval(time()-60*60*24*31))))));
			}
			if (!has_no_forum())
			{
				if (get_option('forum_show_stats_count_topics',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'2e0fe7ccbb15052743c94aab6a3654bc','KEY'=>do_lang_tempcode('COUNT_TOPICS'),'VALUE'=>integer_format($GLOBALS['FORUM_DRIVER']->get_topics()))));
				if (get_option('forum_show_stats_count_posts',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'de7e97b855cfbc4d60d069ca3f652b17','KEY'=>do_lang_tempcode('COUNT_POSTS'),'VALUE'=>integer_format($GLOBALS['FORUM_DRIVER']->get_num_forum_posts()))));
				if (get_option('forum_show_stats_count_posts_today',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'8649eee4a70ce0383c5534da43e2b58c','KEY'=>do_lang_tempcode('COUNT_POSTSTODAY'),'VALUE'=>integer_format($GLOBALS['FORUM_DRIVER']->get_num_new_forum_posts()))));
			}
			if ($bits->is_empty()) return new ocp_tempcode();
			$forums=do_template('BLOCK_SIDE_STATS_SECTION',array('_GUID'=>'52cd616760efe17adcec4b97e1305301','SECTION'=>do_lang_tempcode('FORUM_SLASH_COMMUNITY'),'CONTENT'=>$bits));
		} else $forums=new ocp_tempcode();

		return $forums;
	}

}


