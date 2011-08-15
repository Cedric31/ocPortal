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
 * @package		news
 */

class Hook_search_news
{
	
	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		if (!module_installed('news')) return NULL;

		if (!has_actual_page_access(get_member(),'news')) return NULL;
		if ($GLOBALS['SITE_DB']->query_value('news','COUNT(*)')==0) return NULL;

		require_lang('news');
	
		$info=array();
		$info['lang']=do_lang_tempcode('NEWS');
		$info['default']=true;
		$info['category']='news_category';
		$info['integer_category']=true;

		return $info;
	}
	
	/**
	 * Get a list of entries for the content covered by this search hook. In hierarchical list selection format.
	 *
	 * @param  string			The default selected item
	 * @return tempcode		Tree structure
	 */
	function get_tree($_selected)
	{
		$selected=intval($_selected);

		require_code('news');

		$tree=nice_get_news_categories($selected);
		return $tree;
	}

	/**
	 * Standard modular run function for search results.
	 *
	 * @param  string			Search string
	 * @param  boolean		Whether to only do a META (tags) search
	 * @param  ID_TEXT		Order direction
	 * @param  integer		Start position in total results
	 * @param  integer		Maximum results to return in total
	 * @param  boolean		Whether only to search titles (as opposed to both titles and content)
	 * @param  string			Where clause that selects the content according to the main search string (SQL query fragment) (blank: full-text search)
	 * @param  SHORT_TEXT	Username/Author to match for
	 * @param  ?MEMBER		Member-ID to match for (NULL: unknown)
	 * @param  TIME			Cutoff date
	 * @param  string			The sort type (gets remapped to a field in this function)
	 * @set    title add_date
	 * @param  integer		Limit to this number of results
	 * @param  string			What kind of boolean search to do
	 * @set    or and
	 * @param  string			Where constraints known by the main search code (SQL query fragment)
	 * @param  string			Comma-separated list of categories to search under
	 * @param  boolean		Whether it is a boolean search
	 * @return array			List of maps (template, orderer)
	 */
	function run($content,$only_search_meta,$direction,$max,$start,$only_titles,$content_where,$author,$author_id,$cutoff,$sort,$limit_to,$boolean_operator,$where_clause,$search_under,$boolean_search)
	{
		unset($limit_to);

		$remapped_orderer='';
		switch ($sort)
		{
			case 'rating':
				$remapped_orderer='_rating:news:id';
				break;

			case 'title':
				$remapped_orderer='title';
				break;

			case 'add_date':
				$remapped_orderer='date_and_time';
				break;
		}

		require_code('news');
		require_lang('news');
		require_css('news');

		// Calculate our where clause (search)
		$sq=build_search_submitter_clauses('submitter',$author_id,$author,'author');
		if (is_null($sq)) return array(); else $where_clause.=$sq;
		if (!is_null($cutoff))
		{
			$where_clause.=' AND ';
			$where_clause.='date_and_time>'.strval((integer)$cutoff);
		}

		if (!has_specific_permission(get_member(),'see_unvalidated'))
		{
			$where_clause.=' AND ';
			$where_clause.='validated=1';
		}

		// Calculate and perform query
		$rows=get_search_rows('news','id',$content,$boolean_search,$boolean_operator,$only_search_meta,$direction,$max,$start,$only_titles,'news r',array('r.title','r.news','r.news_article'),$where_clause,$content_where,$remapped_orderer,'r.*',NULL,'news','news_category');

		$out=array();
		foreach ($rows as $i=>$row)
		{
			$out[$i]['data']=$row;
			unset($rows[$i]);
			if (($remapped_orderer!='') && (array_key_exists($remapped_orderer,$row))) $out[$i]['orderer']=$row[$remapped_orderer]; elseif (substr($remapped_orderer,0,7)=='_rating') $out[$i]['orderer']=$row['compound_rating'];
		}

		return $out;
	}

	/**
	 * Standard modular run function for rendering a search result.
	 *
	 * @param  array		The data row stored when we retrieved the result
	 * @return tempcode	The output
	 */
	function render($myrow)
	{
		global $NEWS_CATS;
		if (!isset($NEWS_CATS))
		{
			$NEWS_CATS=$GLOBALS['SITE_DB']->query_select('news_categories',array('*'),array('nc_owner'=>NULL));
			$NEWS_CATS=list_to_map('id',$NEWS_CATS);
		}

		$id=$myrow['id'];
		$date=get_timezoned_date($myrow['date_and_time']);
		$author_url=addon_installed('authors')?build_url(array('page'=>'authors','type'=>'misc','id'=>$myrow['author']),get_module_zone('authors')):new ocp_tempcode();
		$author=$myrow['author'];
		$news_title=get_translated_tempcode($myrow['title']);
		$news=get_translated_tempcode($myrow['news']);
		if ($news->is_empty())
		{
			$news=get_translated_tempcode($myrow['news_article']);
			$truncate=true;
		} else $truncate=false;
		$tmp=array('page'=>'news','type'=>'view','id'=>$id);
		$full_url=build_url($tmp,get_module_zone('news'));
		if (!array_key_exists($myrow['news_category'],$NEWS_CATS))
		{
			$_news_cats=$GLOBALS['SITE_DB']->query_select('news_categories',array('*'),array('id'=>$myrow['news_category']),'',1);
			if (array_key_exists(0,$_news_cats))
				$NEWS_CATS[$myrow['news_category']]=$_news_cats[0];
		}
		if ((!array_key_exists($myrow['news_category'],$NEWS_CATS)) || (!array_key_exists('nc_title',$NEWS_CATS[$myrow['news_category']])))
			$myrow['news_category']=db_get_first_id();
		$img=find_theme_image($NEWS_CATS[$myrow['news_category']]['nc_img']);
		if (is_null($img)) $img='';
		if ($myrow['news_image']!='')
		{
			$img=$myrow['news_image'];
			if (url_is_local($img)) $img=get_base_url().'/'.$img;
		}
		$category=get_translated_text($NEWS_CATS[$myrow['news_category']]['nc_title']);
		$seo_bits=seo_meta_get_for('news',strval($id));
		$map=array('ID'=>strval($id),'TAGS'=>get_loaded_tags('news',explode(',',$seo_bits[0])),'TRUNCATE'=>$truncate,'BLOG'=>false,'SUBMITTER'=>strval($myrow['submitter']),'CATEGORY'=>$category,'IMG'=>$img,'DATE'=>$date,'DATE_RAW'=>strval($myrow['date_and_time']),'NEWS_TITLE'=>$news_title,'AUTHOR'=>$author,'AUTHOR_URL'=>$author_url,'NEWS'=>$news,'FULL_URL'=>$full_url);

		$tpl=do_template('NEWS_PIECE_SUMMARY',$map);
		return put_in_standard_box($tpl,do_lang_tempcode('NEWS_ARTICLE'));
	}

}


