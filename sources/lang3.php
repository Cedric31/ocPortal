<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

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
 * UI to choose a language.
 *
 * @param  tempcode			Title for the form
 * @param  boolean			Whether to give a tip about edit order
 * @param  boolean			Whether to add an 'all' entry to the list
 * @return mixed				The UI (tempcode) or the language to use (string/LANGUAGE_NAME)
 */
function _choose_language($title,$tip=false,$allow_all_selection=false)
{
	if (!multi_lang()) return user_lang();

	$lang=either_param('lang',/*get_param('keep_lang',NULL)*/NULL);
	if (!is_null($lang)) return filter_naughty($lang);

	if (!$tip)
	{
		$text=do_lang_tempcode('CHOOSE_LANG_DESCRIP');
	} else
	{
		global $LANGS_MAP_CACHE;
		if ($LANGS_MAP_CACHE===NULL)
		{
			require_code('files');
			$map_a=get_file_base().'/lang/langs.ini';
			$map_b=get_custom_file_base().'/lang_custom/langs.ini';
			if (!is_file($map_b)) $map_b=$map_a;
			$LANGS_MAP_CACHE=better_parse_ini_file($map_b);
		}

		$lang_name=get_site_default_lang();
		if (array_key_exists($lang_name,$LANGS_MAP_CACHE)) $lang_name=$LANGS_MAP_CACHE[$lang_name];

		$text=do_lang_tempcode('CHOOSE_LANG_DESCRIP_ADD_TO_MAIN_LANG_FIRST',escape_html($lang_name));
	}

	$langs=new ocp_tempcode();
	if ($allow_all_selection)
	{
		$langs->attach(form_input_list_entry('',false,do_lang_tempcode('_ALL')));
	}
	$langs->attach(nice_get_langs());
	require_code('form_templates');
	$fields=form_input_list(do_lang_tempcode('LANGUAGE'),do_lang_tempcode('DESCRIPTION_LANGUAGE'),'lang',$langs,NULL,true);

	$hidden=build_keep_post_fields();
	$url=get_self_url();

	return do_template('FORM_SCREEN',array('_GUID'=>'1a2823d450237aa299c095bf9c689a2a','SKIP_VALIDATION'=>true,'HIDDEN'=>$hidden,'SUBMIT_NAME'=>do_lang_tempcode('PROCEED'),'TITLE'=>$title,'FIELDS'=>$fields,'URL'=>$url,'TEXT'=>$text));
}

/**
 * Get an array of all the installed languages that can be found in root/lang/ and root/lang_custom/
 *
 * @param  boolean			Whether to even find empty languages
 * @return array				The installed languages (map, lang=>type)
 */
function _find_all_langs($even_empty_langs=false)
{
	require_code('files');

	// NB: This code is heavily optimised

	$_langs=array();
	if (!in_safe_mode())
	{
		$_dir=@opendir(get_custom_file_base().'/lang_custom/');
		if ($_dir!==false)
		{
			while (false!==($file=readdir($_dir)))
			{
				if ((!isset($file[5])) && ($file[0]!='.') && (($file=='EN') || (!should_ignore_file('lang_custom/'.$file,IGNORE_ACCESS_CONTROLLERS))))
				{
					if (is_dir(get_custom_file_base().'/lang_custom/'.$file))
					{
						if (($even_empty_langs) || (/*optimisation*/is_file(get_custom_file_base().'/lang_custom/'.$file.'/global.ini')))
						{
							$_langs[$file]='lang_custom';
						} else
						{
							$_dir2=opendir(get_custom_file_base().'/lang_custom/'.$file);
							while (false!==($file2=readdir($_dir2)))
							{
								if ((substr($file2,-4)=='.ini') || (substr($file2,-3)=='.po'))
								{
									$_langs[$file]='lang_custom';
									break;
								}
							}
						}
					}
				}
			}
			closedir($_dir);
		}
		if (get_custom_file_base()!=get_file_base())
		{
			$_dir=opendir(get_file_base().'/lang_custom/');
			while (false!==($file=readdir($_dir)))
			{
				if ((!isset($file[5])) && ($file[0]!='.') && (($file=='EN') || (!should_ignore_file('lang_custom/'.$file,IGNORE_ACCESS_CONTROLLERS))))
				{
					if ($even_empty_langs)
					{
						$_langs[$file]='lang_custom';
					} else
					{
						$_dir2=opendir(get_file_base().'/lang_custom/'.$file);
						while (false!==($file2=readdir($_dir2)))
						{
							if ((substr($file2,-4)=='.ini') || (substr($file2,-3)=='.po'))
							{
								$_langs[$file]='lang_custom';
								break;
							}
						}
					}
				}
			}
			closedir($_dir);
		}
	}	
	$_dir=opendir(get_file_base().'/lang/');
	while (false!==($file=readdir($_dir)))
	{
		if ((!isset($_langs[$file])) && ($file[0]!='.') && (!isset($file[5])) && (($file=='EN') || (!should_ignore_file('lang/'.$file,IGNORE_ACCESS_CONTROLLERS))))
		{
			if (is_dir(get_file_base().'/lang/'.$file)) $_langs[$file]='lang';
		}
	}
	closedir($_dir);

	return $_langs;
}

/**
 * Get the title for a language.
 *
 * @param  LANGUAGE_NAME	The language to have selected by default
 * @return string				The language title
 */
function get_language_title($lang)
{
	global $LANGS_MAP_CACHE;

	if ($LANGS_MAP_CACHE===NULL)
	{
		require_code('files');
		$map_a=get_file_base().'/lang/langs.ini';
		$map_b=get_custom_file_base().'/lang_custom/langs.ini';
		if (!is_file($map_b)) $map_b=$map_a;
		$LANGS_MAP_CACHE=better_parse_ini_file($map_b);
	}

	return array_key_exists($lang,$LANGS_MAP_CACHE)?$LANGS_MAP_CACHE[$lang]:$lang;
}

/**
 * Get a nice formatted XHTML listed language selector.
 *
 * @param  ?LANGUAGE_NAME	The language to have selected by default (NULL: uses the current language)
 * @param  boolean			Whether to show languages that have no language details currently defined for them
 * @return tempcode			The language selector
 */
function _nice_get_langs($select_lang=NULL,$show_unset=false)
{
	$langs=new ocp_tempcode();
	$_langs=find_all_langs();

	if (is_null($select_lang)) $select_lang=user_lang();

	foreach (array_keys($_langs) as $lang)
	{
		$langs->attach(form_input_list_entry($lang,($lang==$select_lang),get_language_title($lang)));
	}

	if ($show_unset)
	{
		global $LANGS_MAP_CACHE;
		asort($LANGS_MAP_CACHE);
		foreach ($LANGS_MAP_CACHE as $lang=>$full)
		{
			if (!array_key_exists($lang,$_langs))
			{
				$_full=make_string_tempcode($full);
				$_full->attach(do_lang_tempcode('_UNSET'));
				$langs->attach(form_input_list_entry($lang,false,protect_from_escaping($_full)));
			}
		}
	}

	return $langs;
}

/**
 * Insert a language entry into the translation table, and returns the ID.
 *
 * @param  string				The text
 * @param  integer			The level of importance this language string holds
 * @set    1 2 3 4
 * @param  ?object			The database connection to use (NULL: standard site connection)
 * @param  boolean			Whether it is to be parsed as Comcode
 * @param  ?integer			The ID to use for the language entry (NULL: work out next available)
 * @param  ?LANGUAGE_NAME	The language (NULL: uses the current language)
 * @param  boolean			Whether to insert it as an admin (any Comcode parsing will be carried out with admin privileges)
 * @param  ?string			The special identifier for this lang code on the page it will be displayed on; this is used to provide an explicit binding between languaged elements and greater templated areas (NULL: none)
 * @param  ?string			Assembled Tempcode portion (NULL: work it out)
 * @param  integer			Comcode parser wrap position
 * @param  boolean			Whether to generate a fatal error if there is invalid Comcode
 * @param  boolean			Whether we are saving as a 'volatile' file extension (used in the XML DB driver, to mark things as being non-syndicated to subversion)
 * @return integer			The ID of the newly added language entry
 */
function _insert_lang($text,$level,$connection=NULL,$comcode=false,$id=NULL,$lang=NULL,$insert_as_admin=false,$pass_id=NULL,$text2=NULL,$wrap_pos=60,$preparse_mode=true,$save_as_volatile=false)
{
	if (is_null($connection)) $connection=$GLOBALS['SITE_DB'];

	if (get_mass_import_mode()) $comcode=false; // For speed, and to avoid instantly showing Comcode errors from sloppy bbcode

	if (is_null($lang)) $lang=user_lang();
	$_text2=NULL;

	if (running_script('stress_test_loader'))
	{
		$comcode=false;
	}

	if ($comcode)
	{
		if (is_null($text2))
		{
			if ((function_exists('get_member')) && (!$insert_as_admin))
			{
				$member=get_member();
			} else
			{
				$member=is_object($GLOBALS['FORUM_DRIVER'])?$GLOBALS['FORUM_DRIVER']->get_guest_id():0;
				$insert_as_admin=true;
			}
			require_code('comcode');
			$_text2=comcode_to_tempcode($text,$member,$insert_as_admin,$wrap_pos,$pass_id,$connection,false,$preparse_mode);
			$text2=$_text2->to_assembly();
		}
	} else $text2='';

	$source_member=(function_exists('get_member'))?get_member():$GLOBALS['FORUM_DRIVER']->get_guest_id();

	if ((is_null($id)) && (multi_lang())) // Needed as MySQL auto-increment works separately for each combo of other key values (i.e. language in this case). We can't let a language string ID get assigned to something entirely different in another language. This MySQL behaviour is not well documented, it may work differently on different versions.
	{
		$connection->query('LOCK TABLES '.get_table_prefix().'translate',NULL,NULL,true);
		$lock=true;
		$id=$connection->query_select_value('translate','MAX(id)');
		$id=is_null($id)?NULL:($id+1);
	} else
	{
		$lock=false;
	}

	if ($lang=='Gibb') // Debug code to help us spot language layer bugs. We expect &keep_lang=EN to show EnglishEnglish content, but otherwise no EnglishEnglish content.
	{
		if (is_null($id))
		{
			$id=$connection->query_insert('translate',array('source_user'=>$source_member,'broken'=>0,'importance_level'=>$level,'text_original'=>'EnglishEnglishWarningWrongLanguageWantGibberishLang','text_parsed'=>'','language'=>'EN'),true,false,$save_as_volatile);
		} else
		{
			$connection->query_insert('translate',array('id'=>$id,'source_user'=>$source_member,'broken'=>0,'importance_level'=>$level,'text_original'=>'EnglishEnglishWarningWrongLanguageWantGibberishLang','text_parsed'=>'','language'=>'EN'),false,false,$save_as_volatile);
		}
	}
	if ((is_null($id)) || ($id===0)) //==0 because unless MySQL NO_AUTO_VALUE_ON_ZERO is on, 0 insertion is same as NULL is same as "use autoincrement"
	{
		$id=$connection->query_insert('translate',array('source_user'=>$source_member,'broken'=>0,'importance_level'=>$level,'text_original'=>$text,'text_parsed'=>$text2,'language'=>$lang),true,false,$save_as_volatile);
	} else
	{
		$connection->query_insert('translate',array('id'=>$id,'source_user'=>$source_member,'broken'=>0,'importance_level'=>$level,'text_original'=>$text,'text_parsed'=>$text2,'language'=>$lang),false,false,$save_as_volatile);
	}

	if ($lock)
	{
		$connection->query('UNLOCK TABLES',NULL,NULL,true);
	}

	if (count($connection->text_lookup_cache)<5000)
	{
		if (!is_null($_text2))
		{
			$connection->text_lookup_cache[$id]=$_text2;
		} else
		{
			$connection->text_lookup_original_cache[$id]=$text;
		}
	}

	return $id;
}

/**
 * Remap the specified language ID, and return the ID again - the ID isn't changed.
 *
 * @param  integer		The language entries id
 * @param  string			The text to remap to
 * @param  ?object		The database connection to use (NULL: standard site connection)
 * @param  boolean		Whether it is to be parsed as Comcode
 * @param  ?string		The special identifier for this lang code on the page it will be displayed on; this is used to provide an explicit binding between languaged elements and greater templated areas (NULL: none)
 * @param  ?MEMBER		The member performing the change (NULL: current member)
 * @param  boolean		Whether to generate Comcode as arbitrary admin
 * @param  boolean		Whether to backup the language string before changing it
 * @return integer		The language entries id
 */
function _lang_remap($id,$text,$connection=NULL,$comcode=false,$pass_id=NULL,$source_member=NULL,$as_admin=false,$backup_string=false)
{
	if ($id==0) return insert_lang($text,3,$connection,$comcode,NULL,NULL,$as_admin,$pass_id);

	if ($text===STRING_MAGIC_NULL) return $id;

	if (is_null($connection)) $connection=$GLOBALS['SITE_DB'];

	$lang=user_lang();

	$test=$connection->query_select_value_if_there('translate','text_original',array('id'=>$id,'language'=>$lang));

	// Mark old as out-of-date
	if ($test!==$text)
		$GLOBALS['SITE_DB']->query_update('translate',array('broken'=>1),array('id'=>$id));

	if ($backup_string)
	{
		$current=$connection->query_select('translate',array('*'),array('id'=>$id,'language'=>$lang),'',1);
		if (!array_key_exists(0,$current))
		{
			$current=$connection->query_select('translate',array('*'),array('id'=>$id),'',1);
		}

		$connection->query_insert('translate_history',array(
			'lang_id'=>$id,
			'language'=>$current[0]['language'],
			'text_original'=>$current[0]['text_original'],
			'broken'=>$current[0]['broken'],
			'action_member'=>get_member(),
			'action_time'=>time()
		));
	}

	if ($comcode)
	{
		$_text2=comcode_to_tempcode($text,$source_member,$as_admin,60,$pass_id,$connection);
		$connection->text_lookup_cache[$id]=$_text2;
		$text2=$_text2->to_assembly();
	} else $text2='';
	if (is_null($source_member)) $source_member=(function_exists('get_member'))?get_member():$GLOBALS['FORUM_DRIVER']->get_guest_id(); // This updates the Comcode reference to match the current user, which may not be the owner of the content this is for. This is for a reason - we need to parse with the security token of the current user, not the original content submitter.

	$remap=array('broken'=>0,'text_original'=>$text,'text_parsed'=>$text2);
	if ((function_exists('ocp_admirecookie')) && ((ocp_admirecookie('use_wysiwyg','1')=='0') && (get_value('edit_with_my_comcode_perms')==='1')) || (!has_privilege($source_member,'allow_html')) || (!has_privilege($source_member,'use_very_dangerous_comcode')))
		$remap['source_user']=$source_member;
	if (!is_null($test)) // Good, we save into our own language, as we have a translation for the lang entry setup properly
	{
		$connection->query_update('translate',$remap,array('id'=>$id,'language'=>$lang),'',1);
	} else // Darn, we'll have to save over whatever we did load from
	{
		$connection->query_update('translate',$remap,array('id'=>$id),'',1);
	}

	$connection->text_lookup_original_cache[$id]=$text;

	// $id doesn't change, but lets allow some functional embedding
	return $id;
}

/**
 * get_translated_tempcode was asked for a lang entry that had not been parsed into Tempcode yet.
 *
 * @param  integer			The ID
 * @param  ?object			The database connection to use (NULL: standard site connection)
 * @param  ?LANGUAGE_NAME	The language (NULL: uses the current language)
 * @param  boolean			Whether to force it to the specified language
 * @param  boolean			Whether to force as_admin, even if the lang string isn't stored against an admin (designed for Comcode page cacheing)
 * @return ?tempcode			The parsed Comcode (NULL: the text couldn't be looked up)
 */
function parse_translated_text($entry,$connection,$lang,$force,$as_admin)
{
	global $SEARCH__CONTENT_BITS,$LAX_COMCODE;

	$nql_backup=$GLOBALS['NO_QUERY_LIMIT'];
	$GLOBALS['NO_QUERY_LIMIT']=true;

	$result=$connection->query_select('translate',array('text_original','source_user'),array('id'=>$entry,'language'=>$lang),'',1);
	$result=array_key_exists(0,$result)?$result[0]:NULL;

	if (is_null($result))
	{
		if ($force)
		{
			$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;
			return NULL;
		}

		$result=$connection->query_select_value_if_there('translate','text_parsed',array('id'=>$entry,'language'=>get_site_default_lang()));
		if (is_null($result)) $result=$connection->query_select_value_if_there('translate','text_parsed',array('id'=>$entry));

		if ((!is_null($result)) && ($result!=''))
		{
			$connection->text_lookup_cache[$entry]=new ocp_tempcode();
			if (!$connection->text_lookup_cache[$entry]->from_assembly($result,true))
				$result=NULL;
		}

		if ((is_null($result)) || ($result==''))
		{
			load_user_stuff();
			require_code('comcode'); // might not have been loaded for a quick-boot
			require_code('permissions');

			$result=$connection->query_select('translate',array('text_original','source_user'),array('id'=>$entry,'language'=>get_site_default_lang()),'',1);
			if (!array_key_exists(0,$result))
			{
				$result=$connection->query_select('translate',array('text_original','source_user'),array('id'=>$entry),'',1);
			}
			$result=array_key_exists(0,$result)?$result[0]:NULL;

			$temp=$LAX_COMCODE;
			$LAX_COMCODE=true;
			lang_remap_comcode($entry,is_null($result)?'':$result['text_original'],$connection,NULL,$result['source_user'],$as_admin);
			if (!is_null($SEARCH__CONTENT_BITS))
			{
				$ret=comcode_to_tempcode($result['text_original'],$result['source_user'],$as_admin,60,NULL,$connection,false,false,false,false,false,$SEARCH__CONTENT_BITS);
				$LAX_COMCODE=$temp;
				$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;
				return $ret;
			}
			$LAX_COMCODE=$temp;
			$ret=get_translated_tempcode($entry,$connection,$lang);
			$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;
			return $ret;
		}

		$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;
		return $connection->text_lookup_cache[$entry];
	} else
	{
		load_user_stuff();
		require_code('comcode'); // might not have been loaded for a quick-boot
		require_code('permissions');

		$temp=$LAX_COMCODE;
		$LAX_COMCODE=true;
		global $SHOW_EDIT_LINKS,$KEEP_MARKERS;
		if ((!is_null($SEARCH__CONTENT_BITS)) || ($SHOW_EDIT_LINKS) || ($KEEP_MARKERS))
		{
			$ret=comcode_to_tempcode($result['text_original'],$result['source_user'],$as_admin,60,NULL,$connection,false,false,false,false,false,$SEARCH__CONTENT_BITS);
			$LAX_COMCODE=$temp;
			$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;
			return $ret;
		}
		lang_remap_comcode($entry,$result['text_original'],$connection,NULL,$result['source_user'],$as_admin);
		$LAX_COMCODE=$temp;
		$ret=get_translated_tempcode($entry,$connection,$lang);
		$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;
		return $ret;
	}
}

/**
 * Convert a language string that is Comcode to tempcode, with potential cacheing in the db.
 *
 * @param  ID_TEXT		The language string ID
 * @return tempcode		The parsed Comcode
 */
function _comcode_lang_string($lang_code)
{
	global $COMCODE_LANG_STRING_CACHE;
	if (array_key_exists($lang_code,$COMCODE_LANG_STRING_CACHE)) return $COMCODE_LANG_STRING_CACHE[$lang_code];

	if ((substr($lang_code,0,4)=='DOC_') && (is_wide()==1)) return new ocp_tempcode(); // Not needed if wide, and we might be going wide to reduce chance of errors occuring

	$comcode_page=$GLOBALS['SITE_DB']->query_select('cached_comcode_pages p LEFT JOIN '.$GLOBALS['SITE_DB']->get_table_prefix().'translate t ON t.id=string_index AND '.db_string_equal_to('t.language',user_lang()),array('string_index','text_parsed'),array('the_page'=>$lang_code,'the_zone'=>'!'),'',1);
	if ((array_key_exists(0,$comcode_page)) && (!is_browser_decacheing()))
	{
		if ((!is_null($comcode_page[0]['text_parsed'])) && ($comcode_page[0]['text_parsed']!=''))
		{
			$parsed=new ocp_tempcode();
			if (!$parsed->from_assembly($comcode_page[0]['text_parsed'],true))
			{
				$ret=get_translated_tempcode($comcode_page[0]['string_index']);
				unset($GLOBALS['RECORDED_LANG_STRINGS_CONTENT'][$comcode_page[0]['string_index']]);
			}
		} else
		{
			$ret=get_translated_tempcode($comcode_page[0]['string_index'],NULL,NULL,true);
			if (is_null($ret)) // Not existent in our language, we'll need to lookup and insert, and get again
			{
				$looked_up=do_lang($lang_code,NULL,NULL,NULL,NULL,false);
				$GLOBALS['SITE_DB']->query_insert('translate',array('id'=>$comcode_page[0]['string_index'],'source_user'=>get_member(),'broken'=>0,'importance_level'=>1,'text_original'=>$looked_up,'text_parsed'=>'','language'=>user_lang()),true,false,true);
				$ret=get_translated_tempcode($comcode_page[0]['string_index']);
			}
			unset($GLOBALS['RECORDED_LANG_STRINGS_CONTENT'][$comcode_page[0]['string_index']]);
			return $ret;
		}
		$COMCODE_LANG_STRING_CACHE[$lang_code]=$parsed;
		return $parsed;
	} elseif (array_key_exists(0,$comcode_page))
	{
		$GLOBALS['SITE_DB']->query_delete('cached_comcode_pages',array('the_page'=>$lang_code,'the_zone'=>'!'));
		delete_lang($comcode_page[0]['string_index']);
	}

	$nql_backup=$GLOBALS['NO_QUERY_LIMIT'];
	$GLOBALS['NO_QUERY_LIMIT']=true;
	$looked_up=do_lang($lang_code,NULL,NULL,NULL,NULL,false);
	if (is_null($looked_up)) return make_string_tempcode(escape_html('{!'.$lang_code.'}'));
	$index=insert_lang_comcode($looked_up,4,NULL,true,NULL,60,false,true);
	$GLOBALS['SITE_DB']->query_insert('cached_comcode_pages',array('the_zone'=>'!','the_page'=>$lang_code,'string_index'=>$index,'the_theme'=>$GLOBALS['FORUM_DRIVER']->get_theme(),'cc_page_title'=>NULL),false,true); // Race conditions
	$parsed=get_translated_tempcode($index);
	$COMCODE_LANG_STRING_CACHE[$lang_code]=$parsed;

	$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;

	return $parsed;
}
