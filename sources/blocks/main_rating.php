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
 * @package		core_feedback_features
 */

class Block_main_rating
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
		$info['parameters']=array('param','page','extra_param_from');
		return $info;
	}
	
	/**
	 * Standard modular cache function.
	 *
	 * @return ?array	Map of cache details (cache_on and ttl) (NULL: module is disabled).
	 */
	/*
	function cacheing_environment() // We can't cache this block, because it needs to execute in order to allow commenting
	{
		$info['cache_on']='array(has_specific_permission(get_member(),\'rate\'),array_key_exists(\'extra_param_from\',$map)?$map[\'extra_param_from\']:\'\',array_key_exists(\'param\',$map)?$map[\'param\']:\'main\',array_key_exists(\'page\',$map)?$map[\'page\']:get_page_name())';
		$info['ttl']=60*5;
		return $info;
	}*/
	
	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		if (!array_key_exists('param',$map)) $map['param']='main';
		if (!array_key_exists('page',$map)) $map['page']=get_page_name();
	
		if (array_key_exists('extra_param_from',$map))
		{
			$extra='_'.$map['extra_param_from'];
		} else $extra='';
	
		require_code('feedback');
	
		$self_url=get_self_url();
		$self_title=$map['page'];
		$id=$map['page'].'_'.$map['param'].$extra;
		$test_changed=post_param('rating_'.$id,'');
		if ($test_changed!='')
		{
			decache('main_rating');
		}
		do_rating(true,'block_main_rating',$id,$self_url,$self_title);
	
		return get_rating_details($self_url,$self_title,'block_main_rating',$id,true);
	}

}


