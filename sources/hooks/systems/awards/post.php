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

class Hook_awards_post
{

	/**
	 * Standard modular info function for award hooks. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
	 *
	 * @param  ?ID_TEXT	The zone to link through to (NULL: autodetect).
	 * @return ?array		Map of award content-type info (NULL: disabled).
	 */
	function info($zone=NULL)
	{
		if (get_forum_type()!='ocf') return NULL;
		require_lang('ocf');

		$info=array();
		$info['connection']=$GLOBALS['FORUM_DB'];
		$info['table']='f_posts';
		$info['date_field']='p_time';
		$info['id_field']='id';
		$info['add_url']='';
		$info['category_field']='p_cache_forum_id';
		$info['category_type']='forums';
		$info['parent_spec__table_name']='f_forums';
		$info['parent_spec__parent_name']='f_parent_forum';
		$info['parent_spec__field_name']='id';
		$info['parent_field_name']='p_cache_forum_id';
		$info['submitter_field']='p_poster';
		$info['id_is_string']=false;
		$info['title']=do_lang_tempcode('SPECIFIC_FORUM_POSTS');
		$info['validated_field']='p_validated';
		$info['category_is_string']=false;
		$info['archive_url']=build_url(array('page'=>'forumview'),(!is_null($zone))?$zone:get_module_zone('forumview'));
		$info['cms_page']='topics';
		$info['supports_custom_fields']=true;

		return $info;
	}

	/**
	 * Standard modular run function for award hooks. Renders a content box for an award/randomisation.
	 *
	 * @param  array		The database row for the content
	 * @param  ID_TEXT	The zone to display in
	 * @param  boolean	Whether to include context (i.e. say WHAT this is, not just show the actual content)
	 * @param  boolean	Whether to include breadcrumbs (if there are any)
	 * @param  ?ID_TEXT	Virtual root to use (NULL: none)
	 * @param  boolean	Whether to copy through any filter parameters in the URL, under the basis that they are associated with what this box is browsing
	 * @return tempcode	Results
	 */
	function run($row,$zone,$give_context=true,$include_breadcrumbs=true,$root=NULL,$attach_to_url_filter=false)
	{
		require_code('ocf_posts2');

		return render_post_box($row,false,$give_context,$include_breadcrumbs,is_null($root)?NULL:intval($root));
	}

}


