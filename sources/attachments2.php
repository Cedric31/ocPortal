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
 * @package		core_rich_media
 */

/**
 * Get an array containing new comcode, and tempcode. The function wraps the normal comcode_to_tempcode function. The function will do attachment management, including deleting of attachments that have become unused due to editing of some comcode and removing of the reference.
 *
 * @param  LONG_TEXT		The unparsed comcode that references the attachments
 * @param  ID_TEXT		The type the attachment will be used for (e.g. download)
 * @param  ID_TEXT		The ID the attachment will be used for
 * @param  boolean		Whether we are only previewing the attachments (i.e. don't store them!)
 * @param  ?object		The database connection to use (NULL: standard site connection)
 * @param  ?boolean		Whether to insert it as an admin (any comcode parsing will be carried out with admin privileges) (NULL: autodetect)
 * @param  ?MEMBER		The member to use for ownership permissions (NULL: current member)
 * @return array			A map containing 'comcode' (after substitution for tying down the new attachments) and 'tempcode'
 */
function do_comcode_attachments($original_comcode,$type,$id,$previewing_only=false,$connection=NULL,$insert_as_admin=NULL,$for_member=NULL)
{
	require_lang('comcode');

	global $COMCODE_ATTACHMENTS;
	unset($COMCODE_ATTACHMENTS[$id]); // In case we have some kind of conflict

	if (is_null($connection)) $connection=$GLOBALS['SITE_DB'];

	if ($for_member!==NULL)
	{
		$member=$for_member;
		if (is_null($insert_as_admin)) $insert_as_admin=false;
	} else
	{
		if (function_exists('get_member'))
		{
			$member=get_member();
			if (is_null($insert_as_admin)) $insert_as_admin=false;
		} else
		{
			$member=0;
			if (is_null($insert_as_admin)) $insert_as_admin=true;
		}
	}

	$comcode_text=(substr($original_comcode,0,8)!='<comcode');

	// Handle data URLs for attachment embedding
	if (function_exists('imagecreatefromstring'))
	{
		$matches=array();
		$matches2=array();
		$num_matches=preg_match_all('#<img[^<>]*src="data:image/\w+;base64,([^"]*)"[^<>]*>#',$original_comcode,$matches);
		$num_matches2=preg_match_all('#\[img[^\[\]]*\]data:image/\w+;base64,([^"]*)\[/img\]#',$original_comcode,$matches2);
		for ($i=0;$i<$num_matches2;$i++)
		{
			$matches[0][$num_matches]=$matches2[0][$i];
			$matches[1][$num_matches]=$matches2[1][$i];
			$num_matches++;
		}
		for ($i=0;$i<$num_matches;$i++)
		{
			if (strpos($original_comcode,$matches[0][$i])!==false) // Check still here (if we have same image in multiple places, may have already been attachment-ified)
			{
				$data=@base64_decode($matches[1][$i]);
				if (($data!==false) && (function_exists('imagepng')))
				{
					$image=@imagecreatefromstring($data);
					if ($image!==false)
					{
						do
						{
							$new_filename=uniqid('',true).'.png';
							$new_path=get_custom_file_base().'/uploads/attachments/'.$new_filename;
						}
						while (file_exists($new_path));
						imagepng($image,$new_path);
	
						$attachment_id=$GLOBALS['SITE_DB']->query_insert('attachments',array(
							'a_member_id'=>get_member(),
							'a_file_size'=>strlen($data),
							'a_url'=>'uploads/attachments/'.$new_filename,
							'a_thumb_url'=>'',
							'a_original_filename'=>basename($new_filename),
							'a_num_downloads'=>0,
							'a_last_downloaded_time'=>time(),
							'a_description'=>'',
							'a_add_time'=>time()),true);
						$GLOBALS['SITE_DB']->query_insert('attachment_refs',array('r_referer_type'=>$type,'r_referer_id'=>$id,'a_id'=>$attachment_id));
	
						$original_comcode=str_replace($matches[0][$i],'[attachment type="inline" thumb="0"]'.strval($attachment_id).'[/attachment]',$original_comcode);
					}
				}
			}
		}
	}

	global $ATTACHMENTS_ALREADY_REFERENCED;
	$old_already=$ATTACHMENTS_ALREADY_REFERENCED;
	$ATTACHMENTS_ALREADY_REFERENCED=array();
	$before=$connection->query_select('attachment_refs',array('a_id','id'),array('r_referer_type'=>$type,'r_referer_id'=>$id));
	foreach ($before as $ref)
	{
		$ATTACHMENTS_ALREADY_REFERENCED[$ref['a_id']]=1;
	}

	$has_one=false;
	$may_have_one=false;
	foreach($_POST as $key=>$value)
	{
		if (preg_match('#^hidFileID\_#i',$key)!=0)
		{
			require_code('uploads');
			$may_have_one=is_swf_upload();
		}
	}
	if ($may_have_one)
	{
		require_code('uploads');
		is_swf_upload(true);

		require_code('comcode_from_html');
		$original_comcode=preg_replace_callback('#<input [^>]*class="ocp_keep_ui_controlled" [^>]*title="([^"]*)" [^>]*type="text" [^>]*value="[^"]*"[^>]*/?'.'>#siU','debuttonise',$original_comcode);
	}
	$myfile=mixed();
	foreach ($_FILES as $key=>$file)
	{
		$matches=array();
		if ((($may_have_one) && (is_swf_upload()) || (is_uploaded_file($file['tmp_name']))) && (preg_match('#file(\d+)#',$key,$matches)!=0))
		{
			$has_one=true;

			$atype=post_param('attachmenttype'.$matches[1],'');
			$is_extract=(preg_match('#\[attachment [^\]]*type="\w+_extract"[^\]]*\]new_'.$matches[1].'\[/#',$original_comcode)!=0) || (preg_match('#<attachment [^>]*type="\w+_extract"[^>]*>new_'.$matches[1].'</#',$original_comcode)!=0);

			if ((substr($atype,-8)=='_extract') || ($is_extract))
			{
				require_code('uploads');
				require_code('files');
				require_code('files2');
				$thumb=(preg_match('#\[(attachment|attachment_safe) [^\]]*thumb="1"[^\]]*\]new_'.$matches[1].'\[/#',$original_comcode)!=0) || (preg_match('#<(attachment|attachment_safe) [^>]*thumb="1"[^>]*>new_'.$matches[1].'</#',$original_comcode)!=0);

				$arcext=get_file_extension($_FILES[$key]['name']);
				if (($arcext=='tar') || ($arcext=='zip'))
				{
					if ($arcext=='tar')
					{
						require_code('tar');
						$myfile=tar_open($file['tmp_name'],'rb');
						$dir=tar_get_directory($myfile,true);
					} elseif ($arcext=='zip')
					{
						if ((!function_exists('zip_open')) && (get_option('unzip_cmd')=='')) warn_exit(do_lang_tempcode('ZIP_NOT_ENABLED'));
						if (!function_exists('zip_open'))
						{
							require_code('m_zip');
							$mzip=true;
						} else $mzip=false;

						$myfile=zip_open($file['tmp_name']);
						if (is_integer($myfile))
						{
							require_code('failure');
							warn_exit(zip_error($myfile,$mzip));
						}
						$dir=array();
						while (($zip_entry=zip_read($myfile))!==false)
						{
							$dir[]=array(
								'zip_entry'=>$zip_entry,
								'path'=>zip_entry_name($zip_entry),
								'size'=>zip_entry_filesize($zip_entry),
							);
						}
					}
					if (count($dir)>100)
					{
						require_code('site');
						attach_message(do_lang_tempcode('TOO_MANY_FILES_TO_EXTRACT'),'warn');
					} else
					{
						foreach ($dir as $entry)
						{
							if (substr($entry['path'],-1)=='/') continue; // Ignore folders

							$_file=preg_replace('#\..*\.#','.',basename($entry['path']));

							if (!check_extension($_file,false,NULL,true)) continue;
							if (should_ignore_file($entry['path'],IGNORE_ACCESS_CONTROLLERS | IGNORE_HIDDEN_FILES)) continue;

							$place=get_custom_file_base().'/uploads/attachments/'.$_file;
							$i=2;
							// Hunt with sensible names until we don't get a conflict
							while (file_exists($place))
							{
								$_file=strval($i).basename($entry['path']);
								$place=get_custom_file_base().'/uploads/attachments/'.$_file;
								$i++;
							}

							$i=2;
							$_file_thumb=basename($entry['path']);
							$place_thumb=get_custom_file_base().'/uploads/attachments_thumbs/'.$_file_thumb;
							// Hunt with sensible names until we don't get a conflict
							while (file_exists($place_thumb))
							{
								$_file_thumb=strval($i).basename($entry['path']);
								$place_thumb=get_custom_file_base().'/uploads/attachments_thumbs/'.$_file_thumb;
								$i++;
							}

							if ($arcext=='tar')
							{
								$file_details=tar_get_file($myfile,$entry['path'],false,$place);
							} elseif ($arcext=='zip')
							{
								zip_entry_open($myfile,$entry['zip_entry']);
								$file_details=array(
									'size'=>$entry['size'],
								);

								$out_file=@fopen($place,'wb') OR intelligent_write_error($place);
								$more=mixed();
								do
								{
									$more=zip_entry_read($entry['zip_entry']);
									if ($more!==false)
									{
										if (fwrite($out_file,$more)<strlen($more)) warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));
									}
								}
								while (($more!==false) && ($more!=''));
								fclose($out_file);

								zip_entry_close($entry['zip_entry']);
							}

							$description=do_lang('EXTRACTED_FILE');
							if (strpos($entry['path'],'/')!==false)
							{
								$description=do_lang('EXTRACTED_FILE_PATH',dirname($entry['path']));
							}

							// Thumbnail
							$thumb_url='';
							require_code('images');
							if (is_image($_file))
							{
								$gd=((get_option('is_on_gd')=='1') && (function_exists('imagetypes')));
								if ($gd)
								{
									require_code('images');
									if (!is_saveable_image($_file)) $ext='.png'; else $ext='.'.get_file_extension($_file);
									$thumb_url='uploads/attachments_thumbs/'.$_file_thumb;
									convert_image(get_custom_base_url().'/uploads/attachments/'.$_file,$place_thumb,-1,-1,intval(get_option('thumb_width')),true,NULL,false,true);

									if ($connection->connection_write!=$GLOBALS['SITE_DB']->connection_write) $thumb_url=get_custom_base_url().'/'.$thumb_url;
								} else $thumb_url='uploads/attachments/'.$_file;
							}

							$url='uploads/attachments/'.$_file;
							if (addon_installed('galleries'))
							{
								require_code('images');
								if ((is_video($url)) && ($connection->connection_read==$GLOBALS['SITE_DB']->connection_read))
								{
									require_code('transcoding');
									$url=transcode_video($url,'attachments','a_url','a_original_filename',NULL,NULL);
								}
							}

							$attachment_id=$connection->query_insert('attachments',array(
								'a_member_id'=>get_member(),
								'a_file_size'=>$file_details['size'],
								'a_url'=>$url,
								'a_thumb_url'=>$thumb_url,
								'a_original_filename'=>basename($entry['path']),
								'a_num_downloads'=>0,
								'a_last_downloaded_time'=>time(),
								'a_description'=>$description,
								'a_add_time'=>time()),true);
							$connection->query_insert('attachment_refs',array('r_referer_type'=>$type,'r_referer_id'=>$id,'a_id'=>$attachment_id));

							if ($comcode_text)
							{
								$original_comcode.=chr(10).chr(10).'[attachment type="'.comcode_escape(str_replace('_extract','',$atype)).'" description="'.comcode_escape($description).'" thumb="'.($thumb?'1':'0').'"]'.strval($attachment_id).'[/attachment]';
							} else
							{
								require_code('comcode_xml');
								//$original_comcode.=chr(10).chr(10).'<attachment type="'.comcode_escape(str_replace('_extract','',$atype)).'" thumb="'.($thumb?'1':'0').'"><attachmentDescription>'.comcode_text__to__comcode_xml($description).'</attachmentDescription>'.strval($attachment_id).'</attachment>';			Would go in bad spot
							}
						}
					}
					if ($arcext=='tar')
					{
						tar_close($myfile);
					} elseif ($arcext=='zip')
					{
						zip_close($myfile);
					}
				}
			} else
			{
				if ((strpos($original_comcode,']new_'.$matches[1].'[/attachment]')===false) && (strpos($original_comcode,'>new_'.$matches[1].'</attachment>')===false) && (strpos($original_comcode,']new_'.$matches[1].'[/attachment_safe]')===false) && (strpos($original_comcode,'>new_'.$matches[1].'</attachment_safe>')===false))
				{
					if ((preg_match('#\]\d+\[/attachment\]#',$original_comcode)==0) && (preg_match('#>\d+</attachment>#',$original_comcode)==0)) // Attachment could have already been put through (e.g. during a preview). If we have actual ID's referenced, it's almost certainly the case.
					{
						if ($comcode_text)
						{
							$original_comcode.=chr(10).chr(10).'[attachment]new_'.$matches[1].'[/attachment]';
						} else
						{
							//$original_comcode.=chr(10).chr(10).'<attachment>new_'.$matches[1].'</attachment>';		Would go in bad spot
						}
					}
				}
			}
		}
	}

	global $LAX_COMCODE;
	$temp=$LAX_COMCODE;
	if ($has_one) $LAX_COMCODE=true; // We don't want a simple syntax error to cause us to lose our attachments
	$tempcode=comcode_to_tempcode($original_comcode,$member,$insert_as_admin,60,$id,$connection,false,false,false,false,false,NULL,$for_member);
	$LAX_COMCODE=$temp;
	$ATTACHMENTS_ALREADY_REFERENCED=$old_already;

	/*if ((array_key_exists($id,$COMCODE_ATTACHMENTS)) && (array_key_exists(0,$COMCODE_ATTACHMENTS[$id])))
	{
		$original_comcode=$COMCODE_ATTACHMENTS[$id][0]['comcode'];
	}*/

	$new_comcode=$original_comcode;

	if (array_key_exists($id,$COMCODE_ATTACHMENTS))
	{
		$ids_present=array();
		for ($i=0;$i<count($COMCODE_ATTACHMENTS[$id]);$i++)
		{
			$attachment=$COMCODE_ATTACHMENTS[$id][$i];

			// If it's a new one, we need to change the comcode to reference the ID we made for it
			if ($attachment['type']=='new')
			{
				$marker=$attachment['marker'];
//				echo $marker.'!'.$new_comcode;
				$a_id=$attachment['id'];

				$old_length=strlen($new_comcode);

				// Search backwards from $marker
				$tag_end_start=$marker-strlen('[/'.$attachment['tag_type'].']'); // </attachment> would be correct if it is Comcode-XML, but they have the same length, so it's irrelevant
				$tag_start_end=$tag_end_start;
				while (($tag_start_end>1) && ((!isset($new_comcode[$tag_start_end-1])) || (($new_comcode[$tag_start_end-1]!=']') && ($new_comcode[$tag_start_end-1]!='>')))) $tag_start_end--;
				$param_keep=substr($new_comcode,0,$tag_start_end-1);
				$end_keep=substr($new_comcode,$tag_end_start);
				if ($comcode_text)
				{
					$new_comcode=$param_keep;
					if (strpos(substr($param_keep,strrpos($param_keep,'[')),' type=')===false) $new_comcode.=' type="'.comcode_escape($attachment['attachmenttype']).'"';
					if (strpos(substr($param_keep,strrpos($param_keep,'[')),' description=')===false) $new_comcode.=' description="'.comcode_escape($attachment['description']).'"';
					$new_comcode.=']'.strval($a_id).$end_keep;
				} else
				{
					require_code('comcode_xml');
					$new_comcode=$param_keep;
					if (strpos(substr($param_keep,strrpos($param_keep,'<')),' type=')===false) $new_comcode.=' type="'.comcode_escape($attachment['attachmenttype']);
					$new_comcode.='">';
					if (strpos(substr($param_keep,strrpos($param_keep,'<')),' description=')===false)
					{
						require_code('comcode_xml');
						$new_comcode.='<attachmentDescription>'.comcode_text__to__comcode_xml($attachment['description'],true).'</attachmentDescription>';
					}
					$new_comcode.=strval($a_id).$end_keep;
				}
//				echo $new_comcode.'<br />!<br />';

				// Update other attachment markers
				$dif=strlen($new_comcode)-$old_length;
				for ($j=$i+1;$j<count($COMCODE_ATTACHMENTS[$id]);$j++)
				{
//					echo $COMCODE_ATTACHMENTS[$id][$i]['marker'].'!';
					$COMCODE_ATTACHMENTS[$id][$j]['marker']+=$dif;
				}

				if (!is_null($type))
					$connection->query_insert('attachment_refs',array('r_referer_type'=>$type,'r_referer_id'=>$id,'a_id'=>$a_id));
			} else
			{
				// (Re-)Reference it
				$connection->query_delete('attachment_refs',array('r_referer_type'=>$type,'r_referer_id'=>$id,'a_id'=>$attachment['id']),'',1);
				$connection->query_insert('attachment_refs',array('r_referer_type'=>$type,'r_referer_id'=>$id,'a_id'=>$attachment['id']));
			}

			$ids_present[]=$attachment['id'];
		}

		if (!$previewing_only)
		{
			// Clear any de-referenced attachments
			foreach ($before as $ref)
			{
				if ((!in_array($ref['a_id'],$ids_present)) && (strpos($new_comcode,'attachment.php?id=')===false) && (!multi_lang()))
				{
					// Delete reference (as it's not actually in the new comcode!)
					$connection->query_delete('attachment_refs',array('id'=>$ref['id']),'',1);

					// Was that the last reference to this attachment? (if so -- delete attachment)
					$test=$connection->query_value_null_ok('attachment_refs','id',array('a_id'=>$ref['a_id']));
					if (is_null($test))
					{
						require_code('attachments3');
						_delete_attachment($ref['a_id'],$connection);
					}
				}
			}
		}
	}

	return array(
		'comcode'=>$new_comcode,
		'tempcode'=>$tempcode
	);
}

/**
 * Check that not too many attachments have been uploaded for the member submitting.
 */
function _check_attachment_count()
{
	if ((get_forum_type()=='ocf') && (function_exists('get_member')))
	{
		require_code('ocf_groups');
		require_lang('ocf');
		require_lang('comcode');
		$max_attachments_per_post=ocf_get_member_best_group_property(get_member(),'max_attachments_per_post');

		$may_have_one=false;
		foreach($_POST as $key=>$value)
		{
			if (preg_match('#^hidFileID\_#i',$key)!=0)
			{
				require_code('uploads');
				$may_have_one=is_swf_upload();
			}
		}
		if ($may_have_one)
		{
			require_code('uploads');
			is_swf_upload(true);
		}
		foreach (array_keys($_FILES) as $name)
		{
			if ((substr($name,0,4)=='file') && (is_numeric(substr($name,4)) && ($_FILES[$name]['tmp_name']!='')))
			{
				$max_attachments_per_post--;
			}
		}

		if ($max_attachments_per_post<0) warn_exit(do_lang_tempcode('TOO_MANY_ATTACHMENTS'));
	}
}

/**
 * Insert some comcode content that may contain attachments, and return the language id.
 *
 * @param  integer		The level of importance this language string holds
 * @set    1 2 3 4
 * @param  LONG_TEXT		The comcode content
 * @param  ID_TEXT		The arbitrary type that the attached is for (e.g. download)
 * @param  ID_TEXT		The id in the set of the arbitrary types that the attached is for
 * @param  ?object		The database connection to use (NULL: standard site connection)
 * @param  boolean		Whether to insert it as an admin (any comcode parsing will be carried out with admin privileges)
 * @param  ?MEMBER		The member to use for ownership permissions (NULL: current member)
 * @return integer		The language id
 */
function insert_lang_comcode_attachments($level,$text,$type,$id,$connection=NULL,$insert_as_admin=false,$for_member=NULL)
{
	if (is_null($connection)) $connection=$GLOBALS['SITE_DB'];

	require_lang('comcode');

	_check_attachment_count();

	$_info=do_comcode_attachments($text,$type,$id,false,$connection,$insert_as_admin,$for_member);
	$text2=$_info['tempcode']->to_assembly();
	$member=(function_exists('get_member'))?get_member():$GLOBALS['FORUM_DRIVER']->get_guest_id();

	$lang_id=NULL;
	if (user_lang()=='Gibb') // Debug code to help us spot language layer bugs. We expect &keep_lang=EN to show EnglishEnglish content, but otherwise no EnglishEnglish content.
	{
		$lang_id=$connection->query_insert('translate',array('source_user'=>$member,'broken'=>0,'importance_level'=>$level,'text_original'=>'EnglishEnglishWarningWrongLanguageWantGibberishLang','text_parsed'=>'','language'=>'EN'),true);
	}
	if (is_null($lang_id))
	{
		$lang_id=$connection->query_insert('translate',array('source_user'=>$member,'broken'=>0,'importance_level'=>$level,'text_original'=>$_info['comcode'],'text_parsed'=>$text2,'language'=>user_lang()),true);
	} else
	{
		$connection->query_insert('translate',array('id'=>$lang_id,'source_user'=>$member,'broken'=>0,'importance_level'=>$level,'text_original'=>$_info['comcode'],'text_parsed'=>$text2,'language'=>user_lang()));
	}

	final_attachments_from_preview($id,$connection);

	return $lang_id;
}

/**
 * Finalise attachments which were created during a preview, so that they have the proper reference IDs.
 *
 * @param  ID_TEXT		The ID in the set of the arbitrary types that the attached is for
 * @param  ?object		The database connection to use (NULL: standard site connection)
 */
function final_attachments_from_preview($id,$connection=NULL)
{
	if (is_null($connection)) $connection=$GLOBALS['SITE_DB'];

	// Clean up the any attachments added at the preview stage
	$posting_ref_id=post_param_integer('posting_ref_id',NULL);
	if ($posting_ref_id<0) fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
	if (!is_null($posting_ref_id))
	{
		$connection->query_delete('attachment_refs',array('r_referer_type'=>'null','r_referer_id'=>strval(-$posting_ref_id)),'',1);
		$connection->query_delete('attachment_refs',array('r_referer_id'=>strval(-$posting_ref_id))); // Can trash this, was made during preview but we made a new one in do_comcode_attachments (recalled by insert_lang_comcode_attachments)
	}
}

