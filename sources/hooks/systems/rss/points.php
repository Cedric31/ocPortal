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
 * @package		points
 */

class Hook_rss_points
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
		if (!addon_installed('points')) return NULL;
		
		if (!has_actual_page_access(get_member(),'points')) return NULL;

		$filters=ocfilter_to_sqlfragment($_filters,'gift_to','f_members',NULL,'gift_to','id',true,true,$GLOBALS['FORUM_DB']); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)

		require_lang('points');

		$content=new ocp_tempcode();
		$rows=$GLOBALS['SITE_DB']->query('SELECT * FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'gifts WHERE '.$filters.' AND date_and_time>'.strval((integer)$cutoff).' ORDER BY date_and_time DESC',$max);
		foreach ($rows as $row)
		{
			$id=strval($row['id']);

			$author='';
			if ($row['anonymous']==0)
			{
				$from=$GLOBALS['FORUM_DRIVER']->get_username($row['gift_from']);
				if (is_null($from)) $from='';
			}

			$news_date=date($date_string,$row['date_and_time']);
			$edit_date='';

			$to=$GLOBALS['FORUM_DRIVER']->get_username($row['gift_to']);
			if (is_null($to)) $to=do_lang('UNKNOWN');
			$news_title=xmlentities(do_lang('POINTS_RSS_LINE',$to,integer_format($row['amount'])));
			$summary=get_translated_text($row['reason']);
			$news='';

			$category='';
			$category_raw='';

			$view_url=build_url(array('page'=>'points','type'=>'member','id'=>$row['gift_to']),get_module_zone('points'),NULL,false,false,true);

			$if_comments=new ocp_tempcode();

			$content->attach(do_template($prefix.'ENTRY',array('VIEW_URL'=>$view_url,'SUMMARY'=>$summary,'EDIT_DATE'=>$edit_date,'IF_COMMENTS'=>$if_comments,'TITLE'=>$news_title,'CATEGORY_RAW'=>$category_raw,'CATEGORY'=>$category,'AUTHOR'=>$author,'ID'=>$id,'NEWS'=>$news,'DATE'=>$news_date)));
		}

		require_lang('points');
		return array($content,do_lang('POINTS'));
	}

}


