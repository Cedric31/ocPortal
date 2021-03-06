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
 * @package		core
 */

/**
 * Used to turn plain-text links into real links.
 *
 * @param  array			The matches
 * @return string			The replacement
 */
function extract_plain_links($matches)
{
	return '<a href="'.@html_entity_decode($matches[0],ENT_QUOTES,get_charset()).'">'.$matches[0].'</a>';
}

class rss
{
	// Used during parsing
	var $type,$namespace_stack,$version,$tag_stack,$attribute_stack,$text_so_far;

	var $gleamed_feed,$gleamed_items;

	var $feed_url;

	var $error;

	/**
	 * Constructs the RSS reader: downloads the URL and parses it. Check $error after constructing.
	 *
	 * @param  URLPATH		The URL to the RSS we will be reading
	 * @param  boolean		Whether the 'url' is actually a filesystem path
	 */
	function rss($url,$is_filesystem_path=false)
	{
		require_lang('rss');

		$this->namespace_stack=array();
		$this->tag_stack=array();
		$this->attribute_stack=array();

		$this->gleamed_feed=array();
		$this->gleamed_items=array();

		$this->feed_url=$url;

		$this->error=NULL;

		if (!function_exists('xml_parser_create'))
		{
			$this->error=do_lang_tempcode('XML_NEEDED');
			return;
		}

		if (!$is_filesystem_path && url_is_local($url)) $url=get_custom_base_url().'/'.$url;

		//echo $url;exit();

		if($is_filesystem_path)
		{
			$GLOBALS['HTTP_CHARSET']='';
			$data=@file_get_contents($url);
		}
		else
		{
			// Run the parser
//			for ($i=0;$i<10;$i++)		Don't retry now that we have the DO_NOT_CACHE_THIS flagged in main_rss.php for errors (i.e. because we don't cache errors we can afford to fail more naturally)
			{
				$GLOBALS['HTTP_CHARSET']='';
				$data=http_download_file($url,NULL,false);
//				if (!is_null($data)) break;
			}
		}

		if (is_null($data))
		{
			$this->error=do_lang('RSS_XML_MISSING',$url).' ['.$GLOBALS['HTTP_MESSAGE'].']';
		}
		else
		{
			// Try and detect feed charset
			$exp='#<\?xml\s+version\s*=\s*["\'][\d\.]+["\']\s*(encoding\s*=\s*["\']([^"\'<>]+)["\'])?\s*(standalone\s*=\s*["\']([^"\'<>]+)["\'])?\s*\?'.'>#';
			$matches=array();
			if ((preg_match($exp,$data,$matches)!=0) && (array_key_exists(2,$matches)))
			{
				$GLOBALS['HTTP_CHARSET']=$matches[2];
				if (strtolower($GLOBALS['HTTP_CHARSET'])=='windows-1252') $GLOBALS['HTTP_CHARSET']='ISO-8859-1';
			}
			// Weed out if isn't supported
			if ((is_null($GLOBALS['HTTP_CHARSET'])) || (!in_array(strtoupper($GLOBALS['HTTP_CHARSET']),array('ISO-8859-1','US-ASCII','UTF-8'))))
				$GLOBALS['HTTP_CHARSET']='UTF-8';

			// Our internal charset
			$parser_charset=get_charset();
			if (!in_array(strtoupper($parser_charset),array('ISO-8859-1','US-ASCII','UTF-8')))
				$parser_charset='UTF-8';

			// Create and setup our parser
			$xml_parser=function_exists('xml_parser_create_ns')?@xml_parser_create_ns($GLOBALS['HTTP_CHARSET']):@xml_parser_create($GLOBALS['HTTP_CHARSET']);
			if ($xml_parser===false)
			{
				$this->error=do_lang_tempcode('XML_PARSING_NOT_SUPPORTED');
				return; // PHP5 default build on windows comes with this function disabled, so we need to be able to escape on error
			}
			xml_set_object($xml_parser,$this);
			@xml_parser_set_option($xml_parser,XML_OPTION_TARGET_ENCODING,$parser_charset);
			xml_set_element_handler($xml_parser,'startElement','endElement');
			xml_set_character_data_handler($xml_parser,'startText');
			//xml_set_external_entity_ref_handler($xml_parser,'extEntity');
			if (function_exists('xml_set_start_namespace_decl_handler'))
			{
				xml_set_start_namespace_decl_handler($xml_parser,'startNamespace');
			}
			if (function_exists('xml_set_end_namespace_decl_handler'))
			{
				xml_set_end_namespace_decl_handler($xml_parser,'endNameSpace');
			}

			//$data=convert_to_internal_encoding($data);		xml_parser does it for us, and we can't disable it- so run with it instead of our own. Shame as it's inferior.

			if (strpos($data,'<!ENTITY')===false)
			{
				$extra_data="<"."?xml version=\"1.0\" encoding=\"".$GLOBALS['HTTP_CHARSET']."\" ?".">
<!DOCTYPE atom [
<!ENTITY nbsp \" \" >
]>
";

				$data=preg_replace($exp,$extra_data,trim($data));
				if (($extra_data!='') && (strpos($data,$extra_data)===false)) $data=$extra_data.$data;
				if ((strtoupper($GLOBALS['HTTP_CHARSET'])=='ISO-8859-1') || (strtoupper($GLOBALS['HTTP_CHARSET'])=='UTF-8')) // Hack to fix bad use of entities (we can't encode them all above)
				{
					$table=array_flip(get_html_translation_table(HTML_ENTITIES));
					if (strtoupper($GLOBALS['HTTP_CHARSET'])=='UTF-8')
					{
						foreach ($table as $x=>$y)
							$table[$x]=utf8_encode($y);
					}
					unset($table['&amp;']);
					unset($table['&gt;']);
					unset($table['&lt;']);
					$data=strtr($data,$table);
				}
				$convert_bad_entities=true;
			} else
			{
				$convert_bad_entities=false;
				if (strpos($data,"<"."?xml")===false) $data="<"."?xml version=\"1.0\" encoding=\"".$GLOBALS['HTTP_CHARSET']."\" ?".">".$data;
				$data=preg_replace($exp,"<"."?xml version=\"1.0\" encoding=\"".$GLOBALS['HTTP_CHARSET']."\" ?".">",$data); // Strip out internal encoding (we already detected and sanitised it)
			}

			$data=unixify_line_format($data,$GLOBALS['HTTP_CHARSET']); // Fixes Windows characters

			if ($convert_bad_entities)
			{
				if (strtoupper(get_charset())=='ISO-8859-1') // Hack to fix bad use of entities (we can't encode them all above)
				{
					$table=array_flip(get_html_translation_table(HTML_ENTITIES));
					unset($table['&amp;']);
					unset($table['&gt;']);
					unset($table['&lt;']);
					$data=strtr($data,$table);
				}
			}
			if (xml_parse($xml_parser,$data,true)==0)
			{
				$this->error=do_lang('RSS_XML_ERROR',xml_error_string(xml_get_error_code($xml_parser)),strval(xml_get_current_line_number($xml_parser)));
			}
			@xml_parser_free($xml_parser);

			$new_items=array();
			foreach ($this->gleamed_items as $i)
			{
				if ((!isset($i['bogus'])) || (!$i['bogus']))
					$new_items[]=$i;
			}

			$this->gleamed_items=$new_items;
		}
	}

	/* *
	 * Standard PHP XML parser function.
	 *
	 * @param  object			A reference to the XML parser calling the handler.
	 * @param  string			A space-separated list of the names of the entities that are open for the parse of this entity (including the name of the referenced entity).
	 * @param  string			The base for resolving the system identifier (system_id) of the external entity. Currently this parameter will always be set to an empty string.
	 * @param  string			The system identifier as specified in the entity declaration.
	 * @param  string			The public identifier as specified in the entity declaration, or an empty string if none was specified; the whitespace in the public identifier will have been normalized as required by the XML spec.
	 * @param  integer		?
	 */
	/*function extEntity($parser,$open_entity_names,$base,$system_id,$public_id)
	{
		$_open_entity_names=explode(',',$open_entity_names);
		return 1; // Kludge to skip dodgy entities
	}*/

	/**
	 * Standard PHP XML parser function.
	 *
	 * @param  object			The parser object (same as 'this')
	 * @param  string			N/A
	 * @param  ?URLPATH		The URI of the name space we are entering (NULL: not given)
	 */
	function startNameSpace($parser,$prefix,$uri=NULL)
	{
		unset($parser);
		unset($prefix);
		if ((($uri=='http://purl.org/atom/ns#') || ($uri=='http://www.w3.org/2005/Atom')) && ($this->type!='RSS'))
		{
			array_push($this->namespace_stack,'ATOM');
			$this->type='ATOM';
		}
		else array_push($this->namespace_stack,$uri);
	}

	/**
	 * Standard PHP XML parser function.
	 *
	 * @param  object			The parser object (same as 'this')
	 */
	function endNameSpace($parser)
	{
		unset($parser);

		array_pop($this->namespace_stack);
	}

	/**
	 * Standard PHP XML parser function.
	 *
	 * @param  object			The parser object (same as 'this')
	 * @param  string			The name of the element found
	 * @param  array			Array of attributes of the element
	 */
	function startElement($parser,$name,$attributes)
	{
		unset($parser);

		if ((strpos($name,'HTTP://PURL.ORG/RSS/1.0/:')!==false))
		{
			$this->type='RSS';

			$name=str_replace('HTTP://PURL.ORG/RSS/1.0/:','',$name);

			// Unfortunately we can't find the version using PHP XML functions
		}

		if (($name=='FEED') && (!function_exists('xml_set_start_namespace_decl_handler')))
		{
			$this->type='ATOM';
		}

		if ($name=='RSS')
		{
			$this->type='RSS';
			/*		$version=explode('.',$attributes['VERSION']);
			if ($version[0]=='0') && ($version[1]=='90') $this->version=0.9; // rdf
			elseif ($version[0]=='0') && ($version[1][0]=='9') $this->version=0.91;
			elseif ($version[0]=='1') $this->version=1; // rdf
			elseif ($version[0]=='2') $this->version=2;
			else fatal_exit(do_lang('RSS_UNKNOWN_VERSION',$version));*/
			$this->version=$attributes['VERSION'];
		}

		if ((($this->type=='RSS') && ($name=='ITEM')) || (($this->type=='ATOM') && (($name=='HTTP://PURL.ORG/ATOM/NS#:ENTRY') || ($name=='HTTP://WWW.W3.ORG/2005/ATOM:ENTRY'))))
		{
			$this->gleamed_items[]=array();
			if (($this->type=='RSS') && ($name=='ITEM'))
			{
				if (array_key_exists('ABOUT',$attributes)) // rdf namespace, but we don't realistically need to check this
				{
					$this->gleamed_items[]=array('full_url'=>$attributes['ABOUT']);
				}
			}
		}

		array_push($this->tag_stack,$name);
		array_push($this->attribute_stack,$attributes);

		$this->text_so_far='';
	}

	/**
	 * Standard PHP XML parser function.
	 *
	 * @param  object			The parser object (same as 'this')
	 */
	function endElement($parser)
	{
		$this->trueStartText($parser,$this->text_so_far);

		array_pop($this->tag_stack);
		array_pop($this->attribute_stack);
	}

	/**
	 * Standard PHP XML parser function.
	 *
	 * @param  object			The parser object (same as 'this')
	 * @param  string			The text
	 */
	function startText($parser,$data)
	{
		unset($parser);

		$this->text_so_far.=$data;
	}

	/**
	 * Parse the complete text of the inside of the tag.
	 *
	 * @param  object			The parser object (same as 'this')
	 * @param  string			The text
	 */
	function trueStartText($parser,$data)
	{
		unset($parser);

		$prelast_tag=array_peek($this->tag_stack,2);
		$last_tag=array_peek($this->tag_stack);
		$attributes=array_peek($this->attribute_stack);

		switch ($this->type)
		{
			case 'RSS':
				switch ($prelast_tag)
				{
					case 'CHANNEL':
						switch ($last_tag)
						{
							// dc namespace
							case 'HTTP://PURL.ORG/DC/ELEMENTS/1.1/:PUBLISHER':
								$this->gleamed_feed['author']=$data;
								break;
							case 'HTTP://PURL.ORG/DC/ELEMENTS/1.1/:CREATOR':
								$this->gleamed_feed['author_email']=$data;
								break;
							case 'HTTP://PURL.ORG/DC/ELEMENTS/1.1/:RIGHTS':
								$this->gleamed_feed['copyright']=$data;
								break;

							case 'TITLE':
								$this->gleamed_feed['title']=$data;
								break;
							case 'LINK':
								$this->gleamed_feed['url']=$data;
								break;
							case 'DESCRIPTION':
								if (($this->version=='0.90') || ($this->version=='0.91') ||
									(($this->version=='0.94') && ($attributes['type']!='text/html')))
									$data=str_replace("\n",'<br />',escape_html($data));
								$this->gleamed_feed['description']=$data;
								break;
							case 'COPYRIGHT':
								if (strpos($data,'(C)')!==false) // Not HTML -> convert
								{
									$data2=preg_replace_callback('(http://[^, \)]+[^\. ])','extract_plain_links',escape_html($data));
									$data2=str_replace('(C)','&copy;',$data2);
								} else $data2=$data;
								$this->gleamed_feed['copyright']=$data2;
								break;
							case 'MANAGINGEDITOR':
								$bracket=strpos($data,'(');
								if ($bracket!==false)
								{
									$bracket2=strrpos(substr($data,$bracket),')')+$bracket;
									if ($bracket2===false) $bracket2=$bracket;
									$this->gleamed_feed['author']=substr($data,$bracket+1,$bracket2-$bracket-1);
									$this->gleamed_feed['author_email']=substr($data,0,$bracket);
								} else $this->gleamed_feed['author_email']=$data;
								break;
							case 'CLOUD':
								$cloud=array();
								$cloud['domain']=$attributes['DOMAIN'];
								$cloud['port']=$attributes['PORT'];
								$cloud['path']=$attributes['PATH'];
								$cloud['registerProcedure']=$attributes['REGISTERPROCEDURE'];
								$cloud['protocol']=$attributes['PROTOCOL'];

								$this->gleamed_feed['cloud']=$cloud;
								break;
						}
						break;
					case 'ITEM':
						$current_item=&$this->gleamed_items[count($this->gleamed_items)-1];
						switch ($last_tag)
						{
							// dc namespace
							case 'HTTP://PURL.ORG/DC/ELEMENTS/1.1/:CREATOR':
								$current_item['author']=$data;
								break;
							case 'HTTP://PURL.ORG/DC/ELEMENTS/1.1/:SUBJECT':
								$current_item['category']=$data;
								break;
							case 'HTTP://PURL.ORG/DC/ELEMENTS/1.1/:DATE':
								$a=cleanup_date($data);
								$current_item['add_date']=$a[0];
								if (array_key_exists(1,$a)) $current_item['clean_add_date']=$a[1];
								break;
							case 'HTTP://PURL.ORG/RSS/1.0/MODULES/CONTENT/:ENCODED':
								$current_item['news']=$data;
								if ((preg_match('#[<>]#',$current_item['news'])==0) && (preg_match('#[<>]#',html_entity_decode($current_item['news'],ENT_QUOTES))!=0)) // Double escaped HTML
									$current_item['news']=@html_entity_decode($current_item['news'],ENT_QUOTES);
								elseif ((preg_match('#&(?!amp;)#',$current_item['news'])==0) && (preg_match('#&#',html_entity_decode($current_item['news'],ENT_QUOTES))!=0)) // Double escaped HTML
									$current_item['news']=@html_entity_decode($current_item['news'],ENT_QUOTES);
								if (preg_match('#^http://ocportal.com/#',$this->feed_url)==0)
								{
									require_code('xhtml');
									$current_item['news']=xhtmlise_html($current_item['news']);
								}
								break;	
							// slash namespace
							case 'HTTP://PURL.ORG/RSS/1.0/modules/slash:SECTION':
								$current_item['category']=$data;
								break;

							case 'TITLE':
								$current_item['title']=$data;
								if ((preg_match('#[<>]#',$current_item['title'])==0) && (preg_match('#[<>]#',html_entity_decode($current_item['title'],ENT_QUOTES))!=0)) // Double escaped HTML
									$current_item['title']=@html_entity_decode($current_item['title'],ENT_QUOTES);
								elseif ((preg_match('#&(?!amp;)#',$current_item['title'])==0) && (preg_match('#&#',html_entity_decode($current_item['title'],ENT_QUOTES))!=0)) // Double escaped HTML
									$current_item['title']=@html_entity_decode($current_item['title'],ENT_QUOTES);
								if (preg_match('#^http://ocportal.com/#',$this->feed_url)==0)
								{
									require_code('xhtml');
									$current_item['title']=xhtmlise_html($current_item['title']);
								}
								break;
							case 'DESCRIPTION'://echo "here";exit();
								$current_item['news']=$data;
								if ((preg_match('#[<>]#',$current_item['news'])==0) && (preg_match('#[<>]#',html_entity_decode($current_item['news'],ENT_QUOTES))!=0)) // Double escaped HTML
									$current_item['news']=@html_entity_decode($current_item['news'],ENT_QUOTES);
								elseif ((preg_match('#&(?!amp;)#',$current_item['news'])==0) && (preg_match('#&#',html_entity_decode($current_item['news'],ENT_QUOTES))!=0)) // Double escaped HTML
									$current_item['news']=@html_entity_decode($current_item['news'],ENT_QUOTES);
								elseif (strpos($current_item['news'],'>')===false)
									$current_item['news']=nl2br(escape_html($current_item['news']));
								if (preg_match('#^http://ocportal.com/#',$this->feed_url)==0)
								{
									require_code('xhtml');
									$current_item['news']=xhtmlise_html($current_item['news']);
								}
								break;
							case 'PUBDATE':
								$a=cleanup_date($data);
								$current_item['add_date']=$a[0];
								if (array_key_exists(1,$a)) $current_item['clean_add_date']=$a[1];
								break;
							case 'LINK':
								$current_item['full_url']=$data;
								break;
							case 'AUTHOR':
								$bracket=strpos($data,'(');
								if ($bracket!==false)
								{
									$bracket2=strpos($data,')',$bracket);
									$current_item['author']=substr($data,$bracket+1,$bracket2-$bracket-1);
									$current_item['author_email']=rtrim(substr($data,0,$bracket));
								} else $current_item['author_email']=$data;
								if ($current_item['author_email']==get_option('staff_address')) unset($current_item['author_email']);
								break;
							case 'CATEGORY':
								$current_item['category']=$data;
								break;
							case 'SOURCE':
								$current_item['author']	=	$data;
								break;
							case 'COMMENTS':
								$current_item['comment_url']=$data;
								break;
							case 'GUID':
								if ((!array_key_exists('ISPERMALINK',$attributes)) || ($attributes['ISPERMALINK']=='true'))
									$current_item['guid']=$data;
								break;
						}
						break;
				}
				break;

			case 'ATOM':
	//			if (array_peek($this->namespace_stack)=='ATOM')
				{
					if (array_key_exists('TYPE',$attributes)) $type=str_replace('text/','',$attributes['TYPE']); else $type='plain';
					if (array_key_exists('MODE',$attributes)) $mode=$attributes['MODE']; else $mode='xml';
					if ($mode=='BASE64') $data=base64_decode($data);
					if (function_exists('xml_set_start_namespace_decl_handler'))
					{
						$prefix='HTTP://PURL.ORG/ATOM/NS#:';
					} else
					{
						$prefix='';
					}
					if (!is_null($prelast_tag)) $prelast_tag=str_replace('HTTP://WWW.W3.ORG/2005/ATOM:',$prefix,$prelast_tag);
					$last_tag=str_replace('HTTP://WWW.W3.ORG/2005/ATOM:',$prefix,$last_tag);
					switch ($prelast_tag)
					{
						case $prefix.'AUTHOR':
							$preprelast_tag=array_peek($this->tag_stack,3);
							switch ($preprelast_tag)
							{
								case $prefix.'FEED':
									switch ($last_tag)
									{
										case $prefix.'NAME':
											$this->gleamed_feed['author']=$data;
											break;
										case $prefix.'URL':
											$this->gleamed_feed['author_url']=$data;
											break;
										case $prefix.'EMAIL':
											$this->gleamed_feed['author_email']=$data;
											break;
									}
									break;
								case $prefix.'ENTRY':
									$current_item=&$this->gleamed_items[count($this->gleamed_items)-1];
									switch ($last_tag)
									{
										case $prefix.'NAME':
											$current_item['author']=$data;
											break;
										case $prefix.'URL':
											$current_item['author_url']=$data;
											break;
										case $prefix.'EMAIL':
											$current_item['author_email']=$data;
											break;
									}
									break;
							}
						case $prefix.'FEED':
							switch ($last_tag)
							{
								case $prefix.'TITLE':
									$this->gleamed_feed['title']=$data;
									break;
								case $prefix.'LINK':
									$rel=array_key_exists('REL',$attributes)?$attributes['REL']:'alternate';
									if ($rel=='alternate')
									{
										$this->gleamed_feed['url']=array_key_exists('HREF',$attributes)?$attributes['HREF']:$data;
									}
									break;
								case $prefix.'UPDATED':
									$a=cleanup_date($data);
									$current_item['edit_date']=$a[0];
									if (array_key_exists(1,$a)) $current_item['clean_edit_date']=$a[1];
									break;
								case $prefix.'MODIFIED':
									$a=cleanup_date($data);
									$current_item['edit_date']=$a[0];
									if (array_key_exists(1,$a)) $current_item['clean_edit_date']=$a[1];
									break;
								case $prefix.'RIGHTS':
									$this->gleamed_feed['copyright']=$data;
									break;
								case $prefix.'COPYRIGHT':
									$this->gleamed_feed['copyright']=$data;
									break;
								case $prefix.'SUBTITLE':
									$this->gleamed_feed['description']=$data;
									break;
								case $prefix.'TAGLINE':
									$this->gleamed_feed['description']=$data;
									break;
							}
							break;
						case $prefix.'ENTRY':
							$current_item=&$this->gleamed_items[count($this->gleamed_items)-1];
							switch ($last_tag)
							{
								case $prefix.'TITLE':
									$current_item['title']=$data;
									break;
								case $prefix.'LINK':
									if ((!array_key_exists('REL',$attributes)) || ($attributes['REL']=='alternate'))
									{
										$current_item['full_url']=array_key_exists('HREF',$attributes)?$attributes['HREF']:$data;
									}
									break;
								case $prefix.'MODIFIED':
								case $prefix.'UPDATED':
									$a=cleanup_date($data);
									$current_item['edit_date']=$a[0];
									if (array_key_exists(1,$a)) $current_item['clean_edit_date']=$a[1];
									break;
								case $prefix.'PUBLISHED':
								case $prefix.'ISSUED':
									$a=cleanup_date($data);
									$current_item['add_date']=$a[0];
									if (array_key_exists(1,$a)) $current_item['clean_add_date']=$a[1];
									break;
								case $prefix.'ID':
									$current_item['guid']=$data;
									break;
								case $prefix.'SUMMARY':
									if ($type!='html') $data=str_replace("\n",'<br />',$data);
									$current_item['news']=$data;
									if (preg_match('#^http://ocportal.com/#',$this->feed_url)==0)
									{
										require_code('xhtml');
										$current_item['news']=xhtmlise_html($current_item['news']);
									}
									break;
								case $prefix.'CONTENT':
									if ($type!='html') $data=str_replace("\n",'<br />',$data);
									$current_item['news_article']=$data;
									if (preg_match('#^http://ocportal.com/#',$this->feed_url)==0)
									{
										require_code('xhtml');
										$current_item['news_article']=xhtmlise_html($current_item['news_article']);
									}
									break;
								case $prefix.'CATEGORY':
									if (($data=='') && (array_key_exists('TERM',$attributes))) $data=$attributes['TERM'];
									if (($data!='') && (strpos($data,'#')===false))
									{
										if (array_key_exists('category',$current_item))
										{
											if (!array_key_exists('extra_categories',$current_item)) $current_item['extra_categories']=array();
											$current_item['extra_categories'][]=$data;
										} else
										{
											$current_item['category']=$data;
										}
									}
									if ((array_key_exists('TERM',$attributes)) && (strpos($attributes['TERM'],'post')===false) && (strpos($attributes['TERM'],'://')!==false))
									{
										$current_item['bogus']=true;
									}
									break;
								case 'HTTP://SEARCH.YAHOO.COM/MRSS/:THUMBNAIL':
									if (array_key_exists('URL',$attributes))
									{
										$current_item['rep_image']=$attributes['URL'];
									}
									break;
							}
							break;
					}
				}
				break;
		}
	}

}

/**
 * Convert an ISO date into a timestamp.
 *
 * @param  string			The ISO date
 * @return array			If only one element, it contains the timestamp. Otherwise it is a pair: (string format, timestamp)
 */
function cleanup_date($date)
{
	$remap_month=array('Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,'Jul'=>7,'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12);
	$matches=array();
	if (preg_match('#(\d*) ('.implode('|',array_keys($remap_month)).') (\d\d\d\d) (\d*):(\d\d):(\d\d) (GMT|UTC)?([+-]?\w*)#',$date,$matches)!=0)
	{
		$hour=intval($matches[4]);
		$minute=intval($matches[5]);
		$second=intval($matches[6]);
		$month=$remap_month[$matches[2]];
		$day=intval($matches[1]);
		if ($day==0) $day=1;
		$year=intval($matches[3]);
		$timestamp=gmmktime($hour,$minute,$second,$month,$day,$year);
		$their_dif=0; // Assume GMT
		if (is_numeric($matches[8]))
		{
			$their_dif=intval($matches[8]);
			if (abs($their_dif)>30)
			{
				$their_dif=intval(floor(floatval($their_dif)/100.0))+($their_dif%100)/60;
			}
		}

		$timestamp-=$their_dif*60*60;
		return array(get_timezoned_date($timestamp),$timestamp);
	}
	if (preg_match('#(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)Z#',$date,$matches)!=0)
	{
		$hour=intval($matches[4]);
		$minute=intval($matches[5]);
		$second=intval($matches[6]);
		$month=intval($matches[2]);
		$day=intval($matches[3]);
		$year=intval($matches[1]);

		$timestamp=gmmktime($hour,$minute,$second,$month,$day,$year);
		return array(get_timezoned_date($timestamp),$timestamp);
	}
	if (preg_match('#(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)([\+\-]\d\d):(\d\d)#',$date,$matches)!=0)
	{
		$hour=intval($matches[4]);
		$minute=intval($matches[5]);
		$second=intval($matches[6]);
		$month=intval($matches[2]);
		$day=intval($matches[3]);
		$year=intval($matches[1]);

		$timestamp=gmmktime($hour,$minute,$second,$month,$day,$year);
		$timestamp+=intval($matches[7])*60*60+intval($matches[8])*60;
		return array(get_timezoned_date($timestamp),$timestamp);
	}
	if (preg_match('#(\d+?) (\D\D\D) (\d\d\d\d) (\d\d):(\d\d):(\d\d) ([\+\-]\d\d)(\d\d)#',$date,$matches)!=0)
	{
		$hour=intval($matches[4]);
		$minute=intval($matches[5]);
		$second=intval($matches[6]);
		$month=intval($matches[2]);
		$month_remap=array('Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,'Jul'=>7,'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12);
		$month=$month_remap[$month];
		$day=intval($matches[1]);
		$year=intval($matches[3]);

		$timestamp=gmmktime($hour,$minute,$second,$month,$day,$year);
		$timestamp-=intval($matches[7])*60*60+intval($matches[8])*60;
		return array(get_timezoned_date($timestamp),$timestamp);
	}
	return array($date);
}


