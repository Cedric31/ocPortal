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
 * @package		search
 */

class Block_main_search
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
		$info['parameters']=array('title','input_fields','limit_to','search_under','zone','sort','author','days','direction','only_titles','only_search_meta','boolean_search','conjunctive_operator','extra');
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
		$info['cache_on']='array(array_key_exists(\'title\',$map)?$map[\'title\']:NULL,array_key_exists(\'input_fields\',$map)?$map[\'input_fields\']:\'\',array_key_exists(\'extra\',$map)?$map[\'extra\']:\'\',array_key_exists(\'sort\',$map)?$map[\'sort\']:\'relevance\',array_key_exists(\'author\',$map)?$map[\'author\']:\'\',array_key_exists(\'days\',$map)?intval($map[\'days\']):-1,array_key_exists(\'direction\',$map)?$map[\'direction\']:\'DESC\',(array_key_exists(\'only_titles\',$map)?$map[\'only_titles\']:\'\')==\'1\',(array_key_exists(\'only_search_meta\',$map)?$map[\'only_search_meta\']:\'0\')==\'1\',(array_key_exists(\'boolean_search\',$map)?$map[\'boolean_search\']:\'0\')==\'1\',array_key_exists(\'conjunctive_operator\',$map)?$map[\'conjunctive_operator\']:\'AND\',array_key_exists(\'limit_to\',$map)?$map[\'limit_to\']:\'\',array_key_exists(\'search_under\',$map)?$map[\'search_under\']:\'\',array_key_exists(\'zone\',$map)?$map[\'zone\']:get_module_zone(\'search\'))';
		$info['ttl']=60*2;
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
		require_lang('search');
		require_css('search');
		require_javascript('javascript_ajax_people_lists');

		$zone=array_key_exists('zone',$map)?$map['zone']:get_module_zone('search');

		$title=array_key_exists('title',$map)?$map['title']:NULL;
		if ($title===NULL) $title=do_lang('SEARCH');

		$sort=array_key_exists('sort',$map)?$map['sort']:'relevance';
		$author=array_key_exists('author',$map)?$map['author']:'';
		$days=array_key_exists('days',$map)?intval($map['days']):-1;
		$direction=array_key_exists('direction',$map)?$map['direction']:'DESC';
		$only_titles=(array_key_exists('only_titles',$map)?$map['only_titles']:'')=='1';
		$only_search_meta=(array_key_exists('only_search_meta',$map)?$map['only_search_meta']:'0')=='1';
		$boolean_search=(array_key_exists('boolean_search',$map)?$map['boolean_search']:'0')=='1';
		$conjunctive_operator=array_key_exists('conjunctive_operator',$map)?$map['conjunctive_operator']:'AND';
		$_extra=array_key_exists('extra',$map)?$map['extra']:'';

		$map2=array('page'=>'search','type'=>'results');
		if (array_key_exists('search_under',$map)) $map2['search_under']=$map['search_under'];
		$url=build_url($map2,$zone,NULL,false,true);

		$extra=array();
		foreach (explode(',',$_extra) as $_bits)
		{
			$bits=explode('=',$_bits,2);
			if (count($bits)==2) $extra[$bits[0]]=$bits[1];
		}

		$input_fields=array('content'=>do_lang('SEARCH_TITLE'));
		if (array_key_exists('input_fields',$map))
		{
			$input_fields=array();
			foreach (explode(',',$map['input_fields']) as $_bits)
			{
				$bits=explode('=',$_bits,2);
				if (count($bits)==2) $input_fields[$bits[0]]=$bits[1];
			}
		}

		$limit_to=array('all_defaults');
		if (array_key_exists('limit_to',$map))
		{
			$limit_to=array();
			foreach (explode(',',$map['limit_to']) as $key)
			{
				$limit_to[]='search_'.$key;
			}
			if (count($limit_to)==1) $extra['id']=$key;
		}

		unset($map['input_fields']);
		unset($map['extra']);
		unset($map['zone']);
		$full_link=build_url(array('page'=>'search','type'=>'misc')+$map+$extra,$zone);

		if ((!array_key_exists('content',$input_fields)) && (count($input_fields)!=1)) $extra['content']='';

		return do_template('BLOCK_MAIN_SEARCH',array('_GUID'=>'4d239be8e7d846106c951d21d7979f78','TITLE'=>$title,'INPUT_FIELDS'=>$input_fields,'EXTRA'=>$extra,'SORT'=>$sort,'AUTHOR'=>$author,'DAYS'=>strval($days),'DIRECTION'=>$direction,'ONLY_TITLES'=>$only_titles?'1':'0','ONLY_SEARCH_META'=>$only_search_meta?'1':'0','BOOLEAN_SEARCH'=>$boolean_search?'1':'0','CONJUNCTIVE_OPERATOR'=>$conjunctive_operator,'LIMIT_TO'=>$limit_to,'URL'=>$url,'FULL_LINK'=>$full_link));
	}

}


