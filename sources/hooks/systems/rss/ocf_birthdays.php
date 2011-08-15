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
 * @package		core_ocf
 */

class Hook_rss_ocf_birthdays
{

	/**
	 * Standard modular run function for RSS hooks.
	 *
	 * @param  string			A list of categories we accept from
	 * @param  TIME			Cutoff time, before which we do not show results from
	 * @param  string			Prefix that represents the template set we use
	 * @set    RSS_ ATOM_
	 * @param  string			The standard format of date to use for the syndication type represented in the prefix
	 * @param  integer		The maximum number of entries to return, ordering by date
	 * @return ?array			A pair: The main syndication section, and a title (NULL: error)
	 */
	function run($_filters,$cutoff,$prefix,$date_string,$max)
	{
		if (get_forum_type()!='ocf') return NULL;

		if ($cutoff<time()) // Forward in time for birthdays, unlike most RSS
		{
			$cutoff+=(time()-$cutoff)*2;
		}

		require_lang('dates');

		$filters_1=ocfilter_to_sqlfragment($_filters,'d.gm_group_id','f_groups',NULL,'d.gm_group_id','id',true,true,$GLOBALS['FORUM_DB']); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)
		$filters_2=ocfilter_to_sqlfragment($_filters,'p.m_primary_group','f_groups',NULL,'p.m_primary_group','id',true,true,$GLOBALS['FORUM_DB']); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)
		$filters='('.$filters_1.' OR '.$filters_2.')';

		$join='';
		if ($filters!='(1=1 OR 1=1)')
			$join=' LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_group_members d ON (d.gm_member_id=p.id AND d.gm_validated=1)';
		$rows=$GLOBALS['FORUM_DB']->query('SELECT id,m_dob_day,m_dob_month,m_dob_year,m_username,m_reveal_age,m_join_time FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members p'.$join.' WHERE m_validated=1 AND '.$filters.' AND m_dob_day IS NOT NULL ORDER BY m_join_time DESC',$max*3/*for inbalances*/*intval(360.0/floatval(get_param_integer('days',30))));

		$done=0;

		$content=new ocp_tempcode();
		foreach ($rows as $row)
		{
			$year=intval(date('Y',time()));
			$next_birthday_time_a=mktime(0,0,0,$row['m_dob_month'],$row['m_dob_day'],$year);
			$next_birthday_time_b=mktime(0,0,0,$row['m_dob_month'],$row['m_dob_day'],$year+1);
			$next_birthday_time=($next_birthday_time_a<(time()-60*60*24))?$next_birthday_time_b:$next_birthday_time_a;
			$a=$next_birthday_time;
			if ($a<0) $a=-$a;
			$b=$cutoff;
			if ($b<0) $b=-$b;

			if ($a<$b)
			{
				$id=strval($row['id']);
				$author=$row['m_username'];

				$news_date=date($date_string,$next_birthday_time); // The "post" date is actually the birthday
				$edit_date=date($date_string,$row['m_join_time']); // The "edit" date is actually the join date

				if ($row['m_reveal_age']==1)
				{
					$news_title=xmlentities(do_lang('BIRTHDAY_OF_AGE',$author,strval($year-$row['m_dob_year'])));
				} else
				{
					$news_title=xmlentities(do_lang('BIRTHDAY_OF',$author));
				}
				$summary='';
				$news='';

				$category=do_lang('BIRTHDAY');
				$category_raw=do_lang('BIRTHDAY');

				$view_url=$GLOBALS['FORUM_DRIVER']->member_profile_link($row['id'],false,true);

				$if_comments=new ocp_tempcode();

				$content->attach(do_template($prefix.'ENTRY',array('VIEW_URL'=>$view_url,'SUMMARY'=>$summary,'EDIT_DATE'=>$edit_date,'IF_COMMENTS'=>$if_comments,'TITLE'=>$news_title,'CATEGORY_RAW'=>$category_raw,'CATEGORY'=>$category,'AUTHOR'=>$author,'ID'=>$id,'NEWS'=>$news,'DATE'=>$news_date)));
				
				$done++;
				if ($done==$max) break;
			}
		}

		require_lang('ocf');
		return array($content,do_lang('BIRTHDAYS'));
	}

}


