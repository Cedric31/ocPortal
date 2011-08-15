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
 * @package		core_comcode_pages
 */

class Hook_whats_news_comcode_pages
{

	/**
	 * Standard modular run function for newsletter hooks.
	 *
	 * @return array				Tuple of result details: HTML list of all types that can be choosed, title for selection list
	 */
	function choose_categories()
	{
		require_code('zones3');
		return array(nice_get_zones(),do_lang('PAGES'));
	}

	/**
	 * Standard modular run function for newsletter hooks.
	 *
	 * @param  TIME				The time that the entries found must be newer than
	 * @param  LANGUAGE_NAME	The language the entries found must be in
	 * @param  string				Category filter to apply
	 * @return array				Tuple of result details
	 */
	function run($cutoff_time,$lang,$filter)
	{
		$new=new ocp_tempcode();
	
		require_code('ocfiltering');
		if ($filter=='') $filter=','; // Just welcome zone
		$or_list=ocfilter_to_sqlfragment($filter,'b.the_zone',NULL,NULL,NULL,NULL,false);

		$_rows=$GLOBALS['SITE_DB']->query('SELECT a.* FROM '.get_table_prefix().'cached_comcode_pages a LEFT JOIN '.get_table_prefix().'comcode_pages b ON a.the_page=b.the_page AND a.the_zone=b.the_zone WHERE p_add_date>'.strval($cutoff_time).' AND ('.$or_list.')',300);
		if (count($_rows)==300) return array();
		$rows=array();
		foreach ($_rows as $row)
			$rows[$row['the_zone'].':'.$row['the_page']]=$row;
		$_rows2=$GLOBALS['SITE_DB']->query_select('seo_meta',array('*'),array('meta_for_type'=>'comcode_page'));
		$rows2=array();
		foreach ($_rows2 as $row)
			$rows2[$row['meta_for_id']]=$row;
		$zones=explode(',',$filter);//find_all_zones();
		foreach ($zones as $zone)
		{
			if ($zone=='cms') continue;
			if ($zone=='adminzone') continue;
	
			$pages=find_all_pages($zone,'comcode_custom/'.get_site_default_lang(),'txt',false,$cutoff_time);
			foreach (array_keys($pages) as $page)
			{
				if (!is_string($page)) $page=strval($page); // PHP can be weird when things like '404' are put in arrays
				
				if (substr($page,0,6)=='panel_') continue;

				$id=$zone.':'.$page;
				$_url=build_url(array('page'=>$page),$zone,NULL,false,false,true);
				$url=$_url->evaluate();
				$name=ucfirst(str_replace('_',' ',$page));
				if (array_key_exists($id,$rows))
				{
					$_name=get_translated_text($rows[$id]['cc_page_title'],NULL,NULL,true);
					if (!is_null($_name)) $name=$_name;
				}
				$description='';
				$member_id=NULL;
				if (array_key_exists($id,$rows2))
				{
					$description=get_translated_text($rows2[$id]['meta_description']);
				}
				$new->attach(do_template('NEWSLETTER_NEW_RESOURCE_FCOMCODE',array('_GUID'=>'67f165847dacd54d2965686d561b57ee','MEMBER_ID'=>$member_id,'URL'=>$url,'NAME'=>$name,'DESCRIPTION'=>$description)));
			}
		}

		return array($new,do_lang('PAGES','','','',$lang));
	}

}


