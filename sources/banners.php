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
 * @package		banners
 */

/**
 * Standard code module initialisation function.
 */
function init__banners()
{
	define('BANNER_PERMANENT',0);
	define('BANNER_CAMPAIGN',1);
	define('BANNER_DEFAULT',2);
}

/**
 * Show a banner according to GET parameter specification.
 *
 * @param  boolean		Whether to return a result rather than outputting
 * @param  ?string		Whether we are displaying or click-processing (NULL: get from URL param)
 * @set    "click" ""
 * @param  ?string		Specific banner to display (NULL: get from URL param) (blank: randomise)
 * @param  ?string		Banner type to display (NULL: get from URL param)
 * @param  ?string		The banner advertisor who is actively displaying the banner (calling up this function) and hence is rewarded (NULL: get from URL param) (blank: our own site)
 * @return ?tempcode		Result (NULL: we weren't asked to return the result)
 */
function banners_script($ret=false,$type=NULL,$dest=NULL,$b_type=NULL,$source=NULL)
{
	require_code('images');
	require_lang('banners');

	// If this is being called for a click through
	if (is_null($type)) $type=get_param('type','');

	if ($type=='click')
	{
		// Input parameters
		if (is_null($source)) $source=get_param('source','');
		if (is_null($dest)) $dest=get_param('dest','');

		// Has the banner been clicked before?
		$test=$GLOBALS['SITE_DB']->query_value('banner_clicks','MAX(c_date_and_time)',array('c_ip_address'=>get_ip_address(),'c_banner_id'=>$dest));
		$unique=(is_null($test)) || ($test<time()-60*60*24);

		// Find the information about the dest
		$rows=$GLOBALS['SITE_DB']->query_select('banners',array('site_url','hits_to','campaign_remaining'),array('name'=>$dest));
		if (!array_key_exists(0,$rows)) fatal_exit(do_lang_tempcode('MISSING_RESOURCE'));
		$myrow=$rows[0];
		$url=$myrow['site_url'];
		$page_link=url_to_pagelink($url);
		if ($page_link!='')
		{
			$keep=symbol_tempcode('KEEP',array((strpos($url,'?')===false)?'1':'0'));
			$url.=$keep->evaluate();
		}

		if ($unique)
		{
			if (get_db_type()!='xml')
			{
				if (!$GLOBALS['SITE_DB']->table_is_locked('banners'))
					$GLOBALS['SITE_DB']->query('UPDATE '.get_table_prefix().'banners SET hits_to=(hits_to+1) WHERE '.db_string_equal_to('name',$dest),1);
			}
			$campaignremaining=$myrow['campaign_remaining'];
			if (!is_null($campaignremaining))
			{
				if (get_db_type()!='xml')
				{
					if (!$GLOBALS['SITE_DB']->table_is_locked('banners'))
						$GLOBALS['SITE_DB']->query('UPDATE '.get_table_prefix().'banners SET campaign_remaining=(campaign_remaining-1) WHERE '.db_string_equal_to('name',$dest),1);
				}
			}
		}

		// Find the information about the source
		if (($source!='') && ($unique))
		{
			$rows=$GLOBALS['SITE_DB']->query_select('banners',array('hits_from','campaign_remaining'),array('name'=>$source));
			if (!array_key_exists(0,$rows)) fatal_exit(do_lang_tempcode('BANNER_MISSING_SOURCE'));
			$myrow=$rows[0];
			if (get_db_type()!='xml')
				$GLOBALS['SITE_DB']->query('UPDATE '.get_table_prefix().'banners SET hits_from=(hits_from+1) WHERE '.db_string_equal_to('name',$source),1);
			$campaignremaining=$myrow['campaign_remaining'];
			if (!is_null($campaignremaining))
			{
				if (get_db_type()!='xml')
				{
					if (!$GLOBALS['SITE_DB']->table_is_locked('banners'))
						$GLOBALS['SITE_DB']->query('UPDATE '.get_table_prefix().'banners SET campaign_remaining=(campaign_remaining+1) WHERE '.db_string_equal_to('name',$source),1);
				}
			}
		}

		// Log the click
		load_user_stuff();
		$GLOBALS['SITE_DB']->query_insert('banner_clicks',array(
			'c_date_and_time'=>time(),
			'c_member_id'=>get_member(),
			'c_ip_address'=>get_ip_address(),
			'c_source'=>$source,
			'c_banner_id'=>$dest
		));

		if ((strpos($url,chr(10))!==false) || (strpos($url,chr(13))!==false))
			log_hack_attack_and_exit('HEADER_SPLIT_HACK');
		header('Location: '.$url);
	}

	// Being called to display a banner
	else
	{
		if (is_null($dest)) $dest=get_param('dest','');
		if (is_null($b_type)) $b_type=get_param('b_type','');

		// A community banner then...
		// ==========================

		// Input parameters (clicks-in from source site)
		if (is_null($source)) $source=get_param('source','');

		// To allow overriding to specify a specific banner
		if ($dest!='')
		{
			$myquery='SELECT * FROM '.get_table_prefix().'banners WHERE '.db_string_equal_to('name',$dest);
		} else
		{
			$myquery='SELECT * FROM '.get_table_prefix().'banners WHERE ((the_type<>'.strval(BANNER_CAMPAIGN).') OR (campaign_remaining>0)) AND ((expiry_date IS NULL) OR (expiry_date>'.strval(time()).')) AND '.db_string_not_equal_to('name',$source).' AND validated=1 AND '.db_string_equal_to('b_type',$b_type);
		}

		// Run Query
		$rows=$GLOBALS['SITE_DB']->query($myquery,500/*reasonable limit - old ones should be turned off*/,NULL,true,false,array('caption'=>'SHORT_TRANS__COMCODE'));
		if (is_null($rows)) $rows=array(); // Error, but tolerate it as it could be on each page load

		// Filter out what we don't have permission for
		if (get_option('use_banner_permissions',true)==='1')
		{
			load_user_stuff();
			require_code('permissions');
			$groups=_get_where_clause_groups(get_member());
			if (!is_null($groups))
			{
				$perhaps=collapse_1d_complexity('category_name',$GLOBALS['SITE_DB']->query('SELECT DISTINCT category_name FROM '.get_table_prefix().'group_category_access WHERE '.db_string_equal_to('module_the_name','banners').' AND ('.$groups.')'));
				$new_rows=array();
				foreach ($rows as $row)
				{
					if (in_array($row['name'],$perhaps)) $new_rows[]=$row;
				}
				$rows=$new_rows;
			}
		}

		// Are we allowed to show default banners?
		$show_defaults=true;
		foreach ($rows as $counter=>$myrow)
		{
			if ($myrow['the_type']==BANNER_CAMPAIGN) $show_defaults=false;
		}

		// Remove ones already shown on this page-view
		static $shown_already=array();
		if (!running_script('banner'))
		{
			if (count($shown_already)<count($rows))
			{
				foreach ($rows as $counter=>$myrow)
				{
					if (array_key_exists($myrow['name'],$shown_already))
					{
						unset($rows[$counter]);
					}
				}
			}
		}

		// Count the total of all importance_modulus entries
		$tally=0;
		$counter=0;
		$bound=array();
		shuffle($rows); // Should not be needed, but mt_rand seems to not be very good when running from the website. Also does a re-index, which we require.
		while (array_key_exists($counter,$rows))
		{
			$myrow=$rows[$counter];

			if (($myrow['the_type']==2) && (!$show_defaults)) $myrow['importance_modulus']=0;
			$tally+=$myrow['importance_modulus'];
			$bound[$counter]=$tally;
			$counter++;
		}
		if ($tally==0)
		{
			load_user_stuff();
			require_code('permissions');
			if ((has_actual_page_access(NULL,'cms_banners')) && (has_submit_permission('mid',get_member(),get_ip_address(),'cms_banners')))
			{
				$add_banner_url=build_url(array('page'=>'cms_banners','type'=>'ad'),get_module_zone('cms_banners'));
			} else $add_banner_url=new ocp_tempcode();
			$content=do_template('BANNERS_NONE',array('_GUID'=>'b786ec327365d1ef38134ce401db9dd2','ADD_BANNER_URL'=>$add_banner_url));
			if ($ret) return $content;
			$echo=do_template('BASIC_HTML_WRAP',array('_GUID'=>'00c8549b88dac8a1291450eb5b681d80','TARGET'=>'_top','TITLE'=>do_lang_tempcode('BANNER'),'CONTENT'=>$content));
			$echo->evaluate_echo();
			return NULL;
		}

		// Choose which banner to show from the results
		$rand=mt_rand(0,$tally);
		for ($i=0;$i<$counter;$i++)
		{
			if ($rand<=$bound[$i]) break;
		}

		$name=$rows[$i]['name'];
		$shown_already[$name]=1;

		// Update the counts (ones done per-view)
		if (get_db_type()!='xml')
		{
			if (!$GLOBALS['SITE_DB']->table_is_locked('banners'))
				$GLOBALS['SITE_DB']->query('UPDATE '.get_table_prefix().'banners SET views_to=(views_to+1) WHERE '.db_string_equal_to('name',$name),1,NULL,false,true);
		}
		if ($source!='')
		{
			if (get_db_type()!='xml')
			{
				if (!$GLOBALS['SITE_DB']->table_is_locked('banners'))
					$GLOBALS['SITE_DB']->query('UPDATE '.get_table_prefix().'banners SET views_from=(views_from+1) WHERE '.db_string_equal_to('name',$name),1,NULL,false,true);
			}
		}

		// Display!
		$img=$rows[$i]['img_url'];
		$caption=get_translated_tempcode('banners',$rows[$i],'caption');
		$content=show_banner($name,$rows[$i]['b_title_text'],$caption,array_key_exists('b_direct_code',$rows[$i])?$rows[$i]['b_direct_code']:'',$img,$source,$rows[$i]['site_url'],$rows[$i]['b_type'],$rows[$i]['submitter']);
		if ($ret) return $content;
		$echo=do_template('BASIC_HTML_WRAP',array('_GUID'=>'d23424ded86c850f4ae0006241407ff9','TITLE'=>do_lang_tempcode('BANNER'),'CONTENT'=>$content));
		$echo->evaluate_echo();
	}

	return NULL;
}

/**
 * Get a nice, formatted XHTML list to select a banner type
 *
 * @param  ?ID_TEXT		The currently selected licence (NULL: none selected)
 * @return tempcode		The list of categories
 */
function nice_get_banner_types($it=NULL)
{
	$list=new ocp_tempcode();
	$rows=$GLOBALS['SITE_DB']->query_select('banner_types',array('id','t_image_width','t_image_height','t_is_textual'),NULL,'ORDER BY id');
	foreach ($rows as $row)
	{
		$caption=($row['id']=='')?do_lang('GENERAL'):$row['id'];

		if ($row['t_is_textual']==1)
		{
			$type_line=do_lang_tempcode('BANNER_TYPE_LINE_TEXTUAL',$caption);
		} else
		{
			$type_line=do_lang_tempcode('BANNER_TYPE_LINE',$caption,strval($row['t_image_width']),strval($row['t_image_height']));
		}

		$list->attach(form_input_list_entry($row['id'],$it===$row['id'],$type_line));
	}
	return $list;
}

/**
 * Get the tempcode for the display of the defined banner.
 *
 * @param  ID_TEXT		The name of the banner
 * @param  SHORT_TEXT	The title text of the banner (displayed for a text banner only)
 * @param  tempcode		The caption of the banner
 * @param  LONG_TEXT		The full HTML/PHP for the banner
 * @param  URLPATH		The URL to the banner image
 * @param  ID_TEXT		The name of the banner for the site that will get the return-hit
 * @param  URLPATH		The URL to the banner's target
 * @param  ID_TEXT		The banner type
 * @param  MEMBER			The submitting user
 * @return tempcode		The rendered banner
 */
function show_banner($name,$title_text,$caption,$direct_code,$img_url,$source,$url,$b_type,$submitter)
{
	// If this is an image, we <img> it, else we <iframe> it
	require_code('images');
	if ($img_url!='') // Flash/Image/Iframe
	{
		if (substr($img_url,-4)=='.swf') // Flash
		{
			if (url_is_local($img_url)) $img_url=get_custom_base_url().'/'.$img_url;
			$_banner_type_row=$GLOBALS['SITE_DB']->query_select('banner_types',array('t_image_width','t_image_height'),array('id'=>$b_type),'',1);
			if (array_key_exists(0,$_banner_type_row))
			{
				$banner_type_row=$_banner_type_row[0];
			} else
			{
				$banner_type_row=array('t_image_width'=>468,'t_image_height'=>60);
			}
			$content=do_template('BANNER_FLASH',array('_GUID'=>'25525a3722715e79a83af4cec53fe072','B_TYPE'=>$b_type,'WIDTH'=>strval($banner_type_row['t_image_width']),'HEIGHT'=>strval($banner_type_row['t_image_height']),'SOURCE'=>$source,'DEST'=>$name,'CAPTION'=>$caption,'IMG'=>$img_url));
		}
		elseif (($url!='') || (is_image($img_url))) // Image; Can't rely on image check, because often they have script-generated URLs
		{
			if (url_is_local($img_url)) $img_url=get_custom_base_url().'/'.$img_url;
			$_banner_type_row=$GLOBALS['SITE_DB']->query_select('banner_types',array('t_image_width','t_image_height'),array('id'=>$b_type),'',1);
			if (array_key_exists(0,$_banner_type_row))
			{
				$banner_type_row=$_banner_type_row[0];
			} else
			{
				$banner_type_row=array('t_image_width'=>468,'t_image_height'=>60);
			}
			$content=do_template('BANNER_IMAGE',array('_GUID'=>'6aaf45b7bb7349393024c24458549e9e','URL'=>$url,'B_TYPE'=>$b_type,'WIDTH'=>strval($banner_type_row['t_image_width']),'HEIGHT'=>strval($banner_type_row['t_image_height']),'SOURCE'=>$source,'DEST'=>$name,'CAPTION'=>$caption,'IMG'=>$img_url));
		} else // Iframe
		{
			if (url_is_local($img_url)) $img_url=get_custom_base_url().'/'.$img_url;
			$_banner_type_row=$GLOBALS['SITE_DB']->query_select('banner_types',array('t_image_width','t_image_height'),array('id'=>$b_type),'',1);
			if (array_key_exists(0,$_banner_type_row))
			{
				$banner_type_row=$_banner_type_row[0];
			} else
			{
				$banner_type_row=array('t_image_width'=>468,'t_image_height'=>60);
			}
			$content=do_template('BANNER_IFRAME',array('_GUID'=>'deeef9834bc308b5d07e025ab9c04c0e','B_TYPE'=>$b_type,'IMG'=>$img_url,'WIDTH'=>strval($banner_type_row['t_image_width']),'HEIGHT'=>strval($banner_type_row['t_image_height'])));
		}
	} else // Text/HTML/PHP
	{
		if ($direct_code=='') // Text
		{
			if ($url=='')
			{
				$filtered_url='';
			} else
			{
				$filtered_url=(strpos($url,'://')!==false)?substr($url,strpos($url,'://')+3):$url;
				if (strpos($filtered_url,'/')!==false) $filtered_url=substr($filtered_url,0,strpos($filtered_url,'/'));
			}
			$content=do_template('BANNER_TEXT',array('_GUID'=>'18ff8f7b14f5ca30cc19a2ad11ecdd62','B_TYPE'=>$b_type,'TITLE_TEXT'=>$title_text,'CAPTION'=>$caption,'SOURCE'=>$source,'DEST'=>$name,'URL'=>$url,'FILTERED_URL'=>$filtered_url));
		} else // HTML/PHP
		{
			load_user_stuff();
			require_code('permissions');
			if (has_specific_permission($submitter,'use_html_banner'))
			{
				if (get_file_base()==get_custom_file_base()) // Only allow PHP code if not a shared install
				{
					$matches=array();
					$num_matches=preg_match_all('#\<\?(.*)\?\>#U',$direct_code,$matches);
					for ($i=0;$i<$num_matches;$i++)
					{
						if (has_specific_permission($submitter,'use_php_banner'))
						{
							$php_code=$matches[1][$i];
							if (substr($php_code,0,3)=='php') $php_code=substr($php_code,3);
							ob_start();
							$evaled=eval($php_code);
							if (!is_string($evaled)) $evaled='';
							$evaled.=ob_get_contents();
							ob_end_clean();
						} else
						{
							$evaled=do_lang('BANNER_PHP_NOT_RUN');
						}
						$direct_code=str_replace($matches[0][$i],$evaled,$direct_code);
					}
				}
				$content=make_string_tempcode($direct_code);
			} else
			{
				$content=do_lang_tempcode('BANNER_HTML_NOT_RUN');
			}
		}
	}

	return $content;
}

/**
 * Get a list of banners.
 *
 * @param  ?AUTO_LINK	The ID of the banner selected by default (NULL: no specific default)
 * @param  ?MEMBER		Only show banners owned by the member (NULL: no such restriction)
 * @return tempcode		The list
 */
function nice_get_banners($it=NULL,$only_owned=NULL)
{
	$where=is_null($only_owned)?NULL:array('submitter'=>$only_owned);
	$rows=$GLOBALS['SITE_DB']->query_select('banners',array('name'),$where,'ORDER BY name',150);
	if (count($rows)==300)
	{
		$rows=$GLOBALS['SITE_DB']->query_select('banners',array('name'),$where,'ORDER BY add_date DESC',150);
	}
	$out=new ocp_tempcode();
	foreach ($rows as $myrow)
	{
		$selected=($myrow['name']==$it);
		$out->attach(form_input_list_entry($myrow['name'],$selected));
	}

	return $out;
}


