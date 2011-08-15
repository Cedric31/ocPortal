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
 * @package		syndication_blocks
 */

class Block_bottom_rss
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
		$info['parameters']=array('param','max_entries');
		return $info;
	}
	
	/**
	 * Standard modular cache function.
	 *
	 * @return ?array	Map of cache details (cache_on and ttl) (NULL: module is disabled).
	 */
	function cacheing_environment()
	{
		$info=array();
		$info['cache_on']=array('block_bottom_rss__cache_on');
		$info['ttl']=intval(get_option('rss_update_time'));
		return $info;
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		$url=array_key_exists('param',$map)?$map['param']:'http://ocportal.com/backend.php?type=rss&mode=news&filter=16,17,18,19,20'; // http://channel9.msdn.com/Feeds/RSS/

		require_code('rss');
		$rss=new rss($url);
		if (!is_null($rss->error)) return paragraph($rss->error,'gfgrtyhyyfhd');
	
		global $NEWS_CATS;
		$NEWS_CATS=$GLOBALS['SITE_DB']->query_select('news_categories',array('*'),array('nc_owner'=>NULL));
		$NEWS_CATS=list_to_map('id',$NEWS_CATS);
	
		$_postdetailss=array();

		// Now for the actual stream contents
		$max=array_key_exists('max_entries',$map)?intval($map['max_entries']):10;
		$content=new ocp_tempcode();
		foreach ($rss->gleamed_items as $i=>$item)
		{
			if ($i>=$max) break;
	
			if (array_key_exists('full_url',$item)) $full_url=$item['full_url'];
			elseif (array_key_exists('guid',$item)) $full_url=$item['guid'];
			elseif (array_key_exists('comment_url',$item)) $full_url=$item['comment_url'];
			else $full_url='';

			$_title=$item['title'];
			$date=array_key_exists('clean_add_date',$item)?get_timezoned_date($item['clean_add_date']):array_key_exists('add_date',$item)?$item['add_date']:'';

			$_postdetailss[]=array('DATE'=>$date,'FULL_URL'=>$full_url,'NEWS_TITLE'=>$_title);
		}

		return do_template('BLOCK_BOTTOM_NEWS',array('_GUID'=>'0fc123199c4d4b7af5a26706271b1f4f','POSTS'=>$_postdetailss));
	}

}

/**
 * Find the cache signature for the block.
 *
 * @param  array	The block parameters.
 * @return array	The cache signature.
 */
function block_bottom_rss__cache_on($map)
{
	return array(array_key_exists('param',$map)?$map['param']:'',array_key_exists('max_entries',$map)?intval($map['max_entries']):10);
}


