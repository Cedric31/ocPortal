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
 * @package		core
 */

/**
 * Erase a seo entry... as these shouldn't be left hanging around once content is deleted.
 *
 * @param  ID_TEXT		The type of resource (e.g. download)
 * @param  ID_TEXT		The ID of the resource
 */
function seo_meta_erase_storage($type,$id)
{
	$rows=$GLOBALS['SITE_DB']->query_select('seo_meta',array('meta_keywords','meta_description'),array('meta_for_type'=>$type,'meta_for_id'=>$id),'',1);
	if (!array_key_exists(0,$rows)) return;
	delete_lang($rows[0]['meta_keywords']);
	delete_lang($rows[0]['meta_description']);
	$GLOBALS['SITE_DB']->query_delete('seo_meta',array('meta_for_type'=>$type,'meta_for_id'=>$id),'',1);

	if (function_exists('persistant_cache_delete')) persistant_cache_delete(array('seo',$type,$id));
}

/**
 * Get template fields to insert into a form page, for manipulation of seo fields.
 *
 * @param  ID_TEXT		The type of resource (e.g. download)
 * @param  ?ID_TEXT		The ID of the resource (NULL: adding)
 * @return tempcode		Form page tempcode fragment
 */
function seo_get_fields($type,$id=NULL)
{
	require_code('form_templates');
	if (is_null($id))
	{
		list($keywords,$description)=array('','');
	} else
	{
		list($keywords,$description)=seo_meta_get_for($type,$id);
	}

	$fields=new ocp_tempcode();
	$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('SECTION_HIDDEN'=>$keywords=='' && $description=='','TITLE'=>do_lang_tempcode('SEO'),'HELP'=>(get_option('show_docs')==='0')?NULL:protect_from_escaping(symbol_tempcode('URLISE_LANG',array(do_lang('TUTORIAL_ON_THIS'),brand_base_url().'/docs'.strval(ocp_version()).'/pg/tut_seo','tut_seo','1'))))));
	$fields->attach(form_input_line_multi(do_lang_tempcode('KEYWORDS'),do_lang_tempcode('DESCRIPTION_META_KEYWORDS'),'meta_keywords[]',array_map('trim',explode(',',preg_replace('#,+#',',',$keywords))),0));
	$fields->attach(form_input_line(do_lang_tempcode('META_DESCRIPTION'),do_lang_tempcode('DESCRIPTION_META_DESCRIPTION'),'meta_description',$description,false));
	return $fields;
}

/**
 * Explictly sets the meta information for the specified resource.
 *
 * @param  ID_TEXT		The type of resource (e.g. download)
 * @param  ID_TEXT		The ID of the resource
 * @param  SHORT_TEXT	The keywords to use
 * @param  SHORT_TEXT	The description to use
 */
function seo_meta_set_for_explicit($type,$id,$keywords,$description)
{
	if ($description==STRING_MAGIC_NULL) return;
	if ($keywords==STRING_MAGIC_NULL) return;

	$description=str_replace(chr(10),' ',$description);

	$rows=$GLOBALS['SITE_DB']->query_select('seo_meta',array('meta_keywords','meta_description'),array('meta_for_type'=>$type,'meta_for_id'=>$id),'',1);
	if (array_key_exists(0,$rows))
	{
		lang_remap($rows[0]['meta_keywords'],$keywords);
		lang_remap($rows[0]['meta_description'],$description);
	} else
	{
		$GLOBALS['SITE_DB']->query_insert('seo_meta',array('meta_for_type'=>$type,'meta_for_id'=>$id,'meta_keywords'=>insert_lang($keywords,2),'meta_description'=>insert_lang($description,2)));
	}

	if (function_exists('decache')) decache('side_tag_cloud');

	if (function_exists('persistant_cache_delete')) persistant_cache_delete(array('seo',$type,$id));
}

/**
 * Sets the meta information for the specified resource, by auto-summarisation from the given parameters.
 *
 * @param  ID_TEXT		The type of resource (e.g. download)
 * @param  ID_TEXT		The ID of the resource
 * @param  array			Array of content strings to summarise from
 * @param  SHORT_TEXT	The description to use
 * @return SHORT_TEXT	Keyword string generated (it's also saved in the DB, so usually you won't want to collect this)
 */
function seo_meta_set_for_implicit($type,$id,$keyword_sources,$description)
{
	if ((!is_null(post_param('meta_keywords',NULL))) && ((post_param('meta_keywords')!='') || (post_param('meta_description')!='')))
	{
		seo_meta_set_for_explicit($type,$id,post_param('meta_keywords'),post_param('meta_description'));
		return '';
	}

	if (get_value('no_auto_meta')==='1') return '';

	if (get_option('automatic_meta_extraction')=='0') return '';

	// These characters are considered to be word-characters
	require_code('textfiles');
	$word_chars=explode(chr(10),read_text_file('word_characters',''));
	$strip_chars=array('\''); // These present problems so will be entirely stripped
	foreach ($word_chars as $i=>$word_char)
	{
		$word_chars[$i]=trim($word_char);
	}
	$common_words=explode(chr(10),read_text_file('too_common_words',''));
	foreach ($common_words as $i=>$common_word)
	{
		$common_words[$i]=trim($common_word);
	}

	$keywords=array(); // This will be filled

	foreach ($keyword_sources as $source) // Look in all our sources
	{
		$source=strip_comcode($source);
		foreach ($strip_chars as $strip_char)
		{
			$source=strtolower(str_replace($strip_char,'',$source));
		}
		
		$source=preg_replace('#\-+#',' ',$source);

		$i=0;
		$len=strlen($source);
		$from=0;
		$in_word=false;
		while ($i<$len)
		{
			$at=$source[$i];
			$word_char=in_array($at,$word_chars);

			if ($in_word)
			{
				// Exiting word
				if (!$word_char)
				{
					if (($i-$from)>=3)
					{
						$this_word=substr($source,$from,$i-$from);
						if (!in_array($this_word,$common_words))
						{
							if (!array_key_exists($this_word,$keywords)) $keywords[$this_word]=0;
							$keywords[$this_word]++;
						}
					}
					$in_word=false;
				}
			} else
			{
				// Entering word
				if ($word_char)
				{
					$from=$i;
					$in_word=true;
				}
			}
			$i++;
		}

		// Finalise
		if (($in_word) && (($i-$from)>=3))
		{
			$this_word=substr($source,$from,$i-$from);
			if (!in_array($this_word,$common_words))
			{
				if (!array_key_exists($this_word,$keywords)) $keywords[$this_word]=0;
				$keywords[$this_word]++;
			}
		}
	}

	arsort($keywords);

	$imp='';
	foreach (array_keys($keywords) as $i=>$keyword)
	{
		if ($imp!='') $imp.=',';
		$imp.=$keyword;
		if ($i==15) break;
	}

	require_code('xhtml');
	$description=strip_comcode($description);
	$description=preg_replace('#\s+---+\s+#',' ',$description);
	seo_meta_set_for_explicit($type,$id,$imp,(strlen($description)>1000)?(substr($description,0,1000).'...'):$description);

	if (function_exists('decache')) decache('side_tag_cloud');
	
	return $imp;
}


