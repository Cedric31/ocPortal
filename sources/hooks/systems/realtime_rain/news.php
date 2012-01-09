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

class Hook_realtime_rain_news
{

	/**
	 * Standard modular run function for realtime-rain hooks.
	 *
	 * @param  TIME			Start of time range.
	 * @param  TIME			End of time range.
	 * @return array			A list of template parameter sets for rendering a 'drop'.
	 */
	function run($from,$to)
	{
		$drops=array();

		if (has_actual_page_access(get_member(),'news'))
		{
			$rows=$GLOBALS['SITE_DB']->query('SELECT title,n.id,nc_img,submitter AS member_id,date_and_time AS timestamp,news_category FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'news n LEFT JOIN '.$GLOBALS['SITE_DB']->get_table_prefix().'news_categories c ON c.id=n.news_category WHERE date_and_time BETWEEN '.strval($from).' AND '.strval($to));

			foreach ($rows as $row)
			{
				if (!has_category_access(get_member(),'news',$row['news_category'])) continue;

				$timestamp=$row['timestamp'];
				$member_id=$row['member_id'];

				$image=$GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id);
				$image=$row['nc_img'];
				if (url_is_local($image)) $image=get_custom_base_url().'/'.$image;

				$ticker_text=strip_comcode(get_translated_text($row['title']));

				$drops[]=rain_get_special_icons(NULL,$timestamp,NULL,$ticker_text)+array(
					'TYPE'=>'news',
					'FROM_MEMBER_ID'=>strval($member_id),
					'TO_MEMBER_ID'=>NULL,
					'TITLE'=>rain_truncate_for_title(get_translated_text($row['title'])),
					'IMAGE'=>$image,
					'TIMESTAMP'=>strval($timestamp),
					'RELATIVE_TIMESTAMP'=>strval($timestamp-$from),
					'TICKER_TEXT'=>$ticker_text,
					'URL'=>build_url(array('page'=>'news','type'=>'view','id'=>$row['id']),'_SEARCH'),
					'IS_POSITIVE'=>false,
					'IS_NEGATIVE'=>false,

					// These are for showing connections between drops. They are not discriminated, it's just three slots to give an ID code that may be seen as a commonality with other drops.
					'FROM_ID'=>'member_'.strval($member_id),
					'TO_ID'=>NULL,
					'GROUP_ID'=>'news_'.strval($row['id']),
				);
			}
		}
		
		return $drops;
	}

}
