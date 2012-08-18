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

class Hook_addon_registry_core_feedback_features
{
	/**
	 * Get a list of file permissions to set
	 *
	 * @return array			File permissions to set
	 */
	function get_chmod_array()
	{
		return array();
	}

	/**
	 * Get the version of ocPortal this addon is for
	 *
	 * @return float			Version number
	 */
	function get_version()
	{
		return ocp_version_number();
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'Features for user interaction with content.';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array()
		);
	}

	/**
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(
			'sources/topics.php',
			'sources/hooks/systems/notifications/like.php',
			'sources/hooks/systems/notifications/comment_posted.php',
			'sources/hooks/systems/notifications/new_feedback.php',
			'themes/default/images/pagepics/trackbacks.png',
			'themes/default/images/bigicons/trackbacks.png',
			'TRACKBACK_DELETE_SCREEN.tpl',
			'sources/hooks/systems/do_next_menus/trackbacks.php',
			'lang/EN/trackbacks.ini',
			'sources/hooks/systems/trackback/.htaccess',
			'sources/hooks/systems/trackback/index.html',
			'trackback.php',
			'adminzone/pages/modules/admin_trackbacks.php',
			'sources/hooks/systems/addon_registry/core_feedback_features.php',
			'sources/hooks/systems/snippets/rating.php',
			'sources/hooks/systems/snippets/comments.php',
			'sources/hooks/systems/preview/comments.php',
			'themes/default/images/like.png',
			'themes/default/images/dislike.png',
			'sources/hooks/systems/rss/comments.php',
			'COMMENTS_POSTING_FORM.tpl',
			'COMMENTS_WRAPPER.tpl',
			'COMMENTS_DEFAULT_TEXT.tpl',
			'RATING_BOX.tpl',
			'RATING_INLINE_STATIC.tpl',
			'RATING_INLINE_DYNAMIC.tpl',
			'RATING_DISPLAY_SHARED.tpl',
			'RATING_FORM.tpl',
			'TRACKBACK.tpl',
			'TRACKBACK_WRAPPER.tpl',
			'TRACKBACK_XML.tpl',
			'TRACKBACK_XML_ERROR.tpl',
			'TRACKBACK_XML_LISTING.tpl',
			'TRACKBACK_XML_NO_ERROR.tpl',
			'TRACKBACK_XML_WRAPPER.tpl',
			'sources/feedback.php',
			'sources/feedback2.php',
			'pages/comcode/EN/feedback.txt',
			'sources/blocks/main_comments.php',
			'sources/blocks/main_trackback.php',
			'sources/blocks/main_rating.php',
			'COMMENT_AJAX_HANDLER.tpl',
			'data/post_comment.php'
		);
	}


	/**
	 * Get mapping between template names and the method of this class that can render a preview of them
	 *
	 * @return array			The mapping
	 */
	function tpl_previews()
	{
		return array(
			'COMMENTS_DEFAULT_TEXT.tpl'=>'comments_default_text',
			'TRACKBACK.tpl'=>'administrative__trackback_delete_screen',
			'TRACKBACK_DELETE_SCREEN.tpl'=>'administrative__trackback_delete_screen',
			'TRACKBACK_XML_NO_ERROR.tpl'=>'trackback_xml_wrapper',
			'TRACKBACK_XML_ERROR.tpl'=>'trackback_xml_error',
			'TRACKBACK_XML_WRAPPER.tpl'=>'trackback_xml_wrapper',
			'COMMENTS_POSTING_FORM.tpl'=>'comments',
			'RATING_BOX.tpl'=>'rating',
			'RATING_DISPLAY_SHARED.tpl'=>'rating_display_shared',
			'COMMENTS_WRAPPER.tpl'=>'comments_wrapper',
			'TRACKBACK_XML.tpl'=>'trackback_xml_wrapper',
			'TRACKBACK_WRAPPER.tpl'=>'trackback_wrapper',
			'TRACKBACK_XML_LISTING.tpl'=>'trackback_xml_listing',
			'RATING_FORM.tpl'=>'rating',
			'RATING_INLINE_STATIC.tpl'=>'rating_inline_static',
			'RATING_INLINE_DYNAMIC.tpl'=>'rating_inline_dynamic',
			'EMOTICON_CLICK_CODE.tpl'=>'comments',
			'COMMENT_AJAX_HANDLER.tpl'=>'comments',
			'POST.tpl'=>'comments_wrapper',
			'POST_CHILD_LOAD_LINK.tpl'=>'comments_wrapper'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__comments_default_text()
	{
		return array(
			lorem_globalise(do_lorem_template('COMMENTS_DEFAULT_TEXT', array()), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__trackback_delete_screen()
	{
		$trackbacks=new ocp_tempcode();
		foreach (placeholder_array() as $k=>$value)
		{
			$trackbacks->attach(do_lorem_template('TRACKBACK', array(
				'ID'=>strval($k),
				'TIME_RAW'=>placeholder_date_raw(),
				'TIME'=>placeholder_number(),
				'URL'=>placeholder_url(),
				'TITLE'=>lorem_word(),
				'EXCERPT'=>lorem_phrase(),
				'NAME'=>$value
			)));
		}

		return array(
			lorem_globalise(do_lorem_template('TRACKBACK_DELETE_SCREEN', array(
				'TITLE'=>lorem_title(),
				'TRACKBACKS'=>$trackbacks,
				'LOTS'=>lorem_phrase()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__trackback_xml_error()
	{
		return array(
			lorem_globalise(do_lorem_template('TRACKBACK_XML_ERROR', array(
				'TRACKBACK_ERROR'=>lorem_phrase()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__trackback_xml_wrapper()
	{
		$xml=do_lorem_template('TRACKBACK_XML', array(
			'TITLE'=>lorem_phrase(),
			'LINK'=>placeholder_url(),
			'EXCERPT'=>lorem_phrase()
		));
		$xml->attach(do_lorem_template('TRACKBACK_XML_NO_ERROR', array()));
		return array(
			lorem_globalise(do_lorem_template('TRACKBACK_XML_WRAPPER', array(
				'XML'=>$xml
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__comments()
	{
		require_lang('comcode');
		require_javascript('javascript_swfupload');
		require_javascript('javascript_posting');

		require_css('forms');

		$content=do_lorem_template('ATTACHMENT', array(
			'I'=>placeholder_number(),
			'POSTING_FIELD_NAME'=>''
		));

		$attachments=do_lorem_template('ATTACHMENTS', array(
			'ATTACHMENT_TEMPLATE'=>$content,
			'IMAGE_TYPES'=>placeholder_types(),
			'POSTING_FIELD_NAME'=>'',
			'ATTACHMENTS'=>$content,
			'MAX_ATTACHMENTS'=>placeholder_number(),
			'NUM_ATTACHMENTS'=>placeholder_number()
		));

		$ret=do_lorem_template('COMMENTS_POSTING_FORM', array(
			'JOIN_BITS'=>lorem_phrase_html(),
			'ATTACHMENTS'=>$attachments,
			'ATTACH_SIZE_FIELD'=>'',
			'POST_WARNING'=>lorem_phrase(),
			'COMMENT_TEXT'=>lorem_sentence_html(),
			'GET_EMAIL'=>lorem_word_html(),
			'EMAIL_OPTIONAL'=>lorem_word_html(),
			'GET_TITLE'=>true,
			'EM'=>placeholder_emoticon_chooser(),
			'DISPLAY'=>lorem_phrase(),
			'COMMENT_URL'=>placeholder_url(),
			'SUBMIT_NAME'=>lorem_word(),
			'TITLE'=>lorem_word(),
			'MAKE_POST'=>true,
			'CREATE_TICKET_MAKE_POST'=>true,
			'FIRST_POST'=>lorem_paragraph_html(),
			'FIRST_POST_URL'=>placeholder_url()
		));

		$ret->attach(do_lorem_template('COMMENT_AJAX_HANDLER', array(
			'OPTIONS'=>'',
			'HASH'=>''
		)));

		return array(
			lorem_globalise($ret, NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__comments_wrapper()
	{
		$review_titles=array();
		$review_titles[]=array(
			'REVIEW_TITLE'=>lorem_phrase(),
			'REVIEW_RATING'=>make_string_tempcode(float_format(10.0))
		);
		$comments=new ocp_tempcode();
		foreach (placeholder_array() as $i=>$comment)
		{
			$map=array(
				'INDIVIDUAL_REVIEW_RATINGS'=>array(),
				'HIGHLIGHT'=>($i==1),
				'TITLE'=>lorem_word(),
				'TIME_RAW'=>placeholder_number(),
				'TIME'=>placeholder_time(),
				'POSTER_ID'=>placeholder_id(),
				'POSTER_URL'=>placeholder_url(),
				'POSTER_NAME'=>lorem_word(),
				'POSTER'=>NULL,
				'POSTER_DETAILS'=>new ocp_tempcode(),
				'ID'=>placeholder_id() . strval($i),
				'POST'=>lorem_phrase(),
				'POST_COMCODE'=>lorem_phrase(),
				'CHILDREN'=>lorem_phrase(),
				'OTHER_IDS'=>array(
					placeholder_id()
				),
				'RATING'=>new ocp_tempcode(),
				'EMPHASIS'=>new ocp_tempcode(),
				'BUTTONS'=>new ocp_tempcode(),
				'LAST_EDITED_RAW'=>'',
				'LAST_EDITED'=>new ocp_tempcode(),
				'UNVALIDATED'=>new ocp_tempcode(),
				'TOPIC_ID'=>placeholder_id(),
				'IS_SPACER_POST'=>false,
				'NUM_TO_SHOW_LIMIT'=>placeholder_number()
			);
			$comments->attach(do_lorem_template('POST', $map));
			do_lorem_template('POST_CHILD_LOAD_LINK', $map); // INCLUDE'd in above, but test set needs to see it run direct
		}

		if (addon_installed('captcha'))
		{
			require_code('captcha');
			$use_captcha=use_captcha();
		}
		else
		{
			$use_captcha=false;
		}
		$form=do_lorem_template('COMMENTS_POSTING_FORM', array(
			'FIRST_POST_URL'=>'',
			'JOIN_BITS'=>lorem_phrase_html(),
			'FIRST_POST'=>lorem_paragraph_html(),
			'TYPE'=>'downloads',
			'ID'=>placeholder_id(),
			'REVIEW_RATING_CRITERIA'=>$review_titles,
			'USE_CAPTCHA'=>$use_captcha,
			'GET_EMAIL'=>false,
			'EMAIL_OPTIONAL'=>true,
			'GET_TITLE'=>true,
			'POST_WARNING'=>do_lang('POST_WARNING'),
			'COMMENT_TEXT'=>get_option('comment_text'),
			'EM'=>placeholder_emoticon_chooser(),
			'DISPLAY'=>'block',
			'COMMENT_URL'=>placeholder_url(),
			'TITLE'=>lorem_word(),
			'MAKE_POST'=>true,
			'CREATE_TICKET_MAKE_POST'=>true
		));

		$out=do_lorem_template('COMMENTS_WRAPPER', array(
			'TYPE'=>lorem_phrase(),
			'ID'=>placeholder_id(),
			'REVIEW_RATING_CRITERIA'=>$review_titles,
			'AUTHORISED_FORUM_URL'=>placeholder_url(),
			'FORM'=>$form,
			'COMMENTS'=>$comments
		));

		$out->attach(do_lorem_template('COMMENT_AJAX_HANDLER', array(
			'OPTIONS'=>lorem_phrase(),
			'HASH'=>lorem_phrase()
		)));

		return array(
			lorem_globalise($out, NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__trackback_wrapper()
	{
		$trackbacks=placeholder_array();

		$content=new ocp_tempcode();

		foreach ($trackbacks as $i=>$value)
		{
			$content->attach(do_lorem_template('TRACKBACK', array(
				'ID'=>placeholder_id().strval($i),
				'TIME_RAW'=>placeholder_time(),
				'TIME'=>placeholder_time(),
				'URL'=>placeholder_url(),
				'TITLE'=>lorem_word(),
				'EXCERPT'=>'',
				'NAME'=>lorem_word()
			)));
		}

		return array(
			lorem_globalise(do_lorem_template('TRACKBACK_WRAPPER', array(
				'TRACKBACKS'=>$content,
				'TRACKBACK_PAGE'=>lorem_word(),
				'TRACKBACK_ID'=>placeholder_id(),
				'TRACKBACK_TITLE'=>lorem_phrase()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__trackback_xml_listing()
	{
		$content=do_lorem_template('TRACKBACK_XML_LISTING', array(
			'ITEMS'=>lorem_phrase(),
			'LINK_PAGE'=>lorem_word(),
			'LINK_ID'=>placeholder_id()
		));

		return array(
			lorem_globalise($content, NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__rating()
	{
		$all_rating_criteria=array();
		$all_rating_criteria[]=array(
			'TITLE'=>lorem_word(),
			'RATING'=>make_string_tempcode("6"),
			'NUM_RATINGS'=>placeholder_number(),
			'TYPE'=>lorem_word()
		);
		$rating_form=do_lorem_template('RATING_FORM', array(
			'LIKES'=>true,
			'CONTENT_TYPE'=>'downloads',
			'ID'=>placeholder_id(),
			'URL'=>placeholder_url(),
			'ALL_RATING_CRITERIA'=>$all_rating_criteria,
			'HAS_RATINGS'=>true,
			'OVERALL_NUM_RATINGS'=>placeholder_number(),
			'SIMPLISTIC'=>true,
			'ERROR'=>''
		));

		return array(
			lorem_globalise(do_lorem_template('RATING_BOX', array(
				'OVERALL_NUM_RATINGS'=>placeholder_number(),
				'LIKES'=>true,
				'CONTENT_TYPE'=>'downloads',
				'ID'=>placeholder_id(),
				'HAS_RATINGS'=>true,
				'ALL_RATING_CRITERIA'=>$all_rating_criteria,
				'NUM_RATINGS'=>"10",
				'RATING_FORM'=>$rating_form,
				'ERROR'=>''
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__rating_inline_static()
	{
		$all_rating_criteria=array();
		foreach (placeholder_array() as $i=>$v)
		{
			$all_rating_criteria[]=array(
				'TITLE'=>lorem_word(),
				'RATING'=>'3',
				'OVERALL_NUM_RATINGS'=>placeholder_number(),
				'TYPE'=>lorem_word() . strval($i)
			);
		}
		$rating_form=do_lorem_template('RATING_FORM', array(
			'CONTENT_TYPE'=>lorem_word(),
			'ID'=>placeholder_id(),
			'URL'=>placeholder_url(),
			'LIKES'=>true,
			'ALL_RATING_CRITERIA'=>$all_rating_criteria,
			'HAS_RATINGS'=>true,
			'SIMPLISTIC'=>false,
			'ERROR'=>''

		));
		return array(
			lorem_globalise(do_lorem_template('RATING_INLINE_STATIC', array(
				'CONTENT_TYPE'=>lorem_word(),
				'ID'=>placeholder_id(),
				'ALL_RATING_CRITERIA'=>$all_rating_criteria,
				'HAS_RATINGS'=>true,
				'NUM_RATINGS'=>placeholder_number(),
				'OVERALL_NUM_RATINGS'=>placeholder_number(),
				'RATING_FORM'=>$rating_form,
				'ERROR'=>'',
				'LIKES'=>false
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__rating_inline_dynamic()
	{
		$all_rating_criteria=array();
		foreach (placeholder_array() as $i=>$v)
		{
			$all_rating_criteria[]=array(
				'TITLE'=>lorem_word(),
				'RATING'=>'3',
				'OVERALL_NUM_RATINGS'=>placeholder_number(),
				'TYPE'=>lorem_word() . strval($i)
			);
		}
		$rating_form=do_lorem_template('RATING_FORM', array(
			'CONTENT_TYPE'=>lorem_word(),
			'ID'=>placeholder_id(),
			'URL'=>placeholder_url(),
			'ALL_RATING_CRITERIA'=>$all_rating_criteria,
			'HAS_RATINGS'=>true,
			'SIMPLISTIC'=>false,
			'ERROR'=>'',
			'LIKES'=>true
		));
		return array(
			lorem_globalise(do_lorem_template('RATING_INLINE_DYNAMIC', array(
				'CONTENT_TYPE'=>lorem_word(),
				'ID'=>placeholder_id(),
				'ALL_RATING_CRITERIA'=>$all_rating_criteria,
				'HAS_RATINGS'=>true,
				'NUM_RATINGS'=>placeholder_number(),
				'OVERALL_NUM_RATINGS'=>placeholder_number(),
				'RATING_FORM'=>$rating_form,
				'ERROR'=>'',
				'LIKES'=>false
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__rating_display_shared()
	{
		$all_rating_criteria=array();
		foreach (placeholder_array() as $i=>$v)
		{
			$all_rating_criteria[]=array(
				'TITLE'=>lorem_word(),
				'RATING'=>'3',
				'OVERALL_NUM_RATINGS'=>placeholder_number(),
				'TYPE'=>lorem_word() . strval($i)
			);
		}
		$rating_form=do_lorem_template('RATING_FORM', array(
			'CONTENT_TYPE'=>lorem_word(),
			'ID'=>placeholder_id(),
			'URL'=>placeholder_url(),
			'ALL_RATING_CRITERIA'=>$all_rating_criteria,
			'HAS_RATINGS'=>true,
			'SIMPLISTIC'=>false,
			'ERROR'=>'',
			'LIKES'=>true
		));
		return array(
			lorem_globalise(do_lorem_template('RATING_DISPLAY_SHARED', array(
				'CONTENT_TYPE'=>lorem_word(),
				'RATING'=>'3',
				'ID'=>placeholder_id(),
				'ALL_RATING_CRITERIA'=>$all_rating_criteria,
				'HAS_RATINGS'=>true,
				'NUM_RATINGS'=>placeholder_number(),
				'OVERALL_NUM_RATINGS'=>placeholder_number(),
				'RATING_FORM'=>$rating_form,
				'ERROR'=>'',
				'LIKES'=>false
			)), NULL, '', true)
		);
	}
}
