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
 * @package		iotds
 */

class Hook_addon_registry_iotds
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
		return 'Choose and display Images Of The Day.';
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
			'sources/hooks/systems/notifications/iotd_chosen.php',
			'sources/hooks/systems/config_default/iotd_update_time.php',
			'sources/hooks/systems/config_default/points_ADD_IOTD.php',
			'sources/hooks/systems/config_default/points_CHOOSE_IOTD.php',
			'sources/hooks/systems/awards/iotd.php',
			'sources/hooks/systems/content_meta_aware/iotd.php',
			'sources/hooks/systems/addon_registry/iotds.php',
			'sources/hooks/modules/admin_setupwizard/iotds.php',
			'sources/hooks/modules/admin_import_types/iotds.php',
			'IOTD.tpl',
			'IOTD_ARCHIVE_SCREEN_IOTD.tpl',
			'IOTD_ENTRY_SCREEN.tpl',
			'BLOCK_MAIN_IOTD.tpl',
			'uploads/iotds/index.html',
			'uploads/iotds_thumbs/index.html',
			'iotds.css',
			'themes/default/images/bigicons/iotds.png',
			'themes/default/images/pagepics/iotds.png',
			'cms/pages/modules/cms_iotds.php',
			'lang/EN/iotds.ini',
			'site/pages/modules/iotds.php',
			'sources/blocks/main_iotd.php',
			'sources/hooks/blocks/main_staff_checklist/iotds.php',
			'sources/hooks/modules/search/iotds.php',
			'sources/hooks/systems/do_next_menus/iotds.php',
			'sources/hooks/systems/rss/iotds.php',
			'sources/hooks/systems/trackback/iotds.php',
			'sources/iotds.php',
			'sources/hooks/systems/preview/iotd.php',
			'IOTD_ADMIN_CHOOSE_SCREEN.tpl',
			'IOTD_ADMIN_CHOOSE_SCREEN_IOTD.tpl',
			'uploads/iotds/.htaccess',
			'uploads/iotds_thumbs/.htaccess'
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
			'IOTD_ADMIN_CHOOSE_SCREEN.tpl'=>'administrative__iotd_admin_choose_screen',
			'IOTD_ADMIN_CHOOSE_SCREEN_IOTD.tpl'=>'administrative__iotd_admin_choose_screen',
			'IOTD.tpl'=>'block_main_iotd',
			'BLOCK_MAIN_IOTD.tpl'=>'block_main_iotd',
			'IOTD_ARCHIVE_SCREEN_IOTD.tpl'=>'iotd_view_screen_iotd',
			'IOTD_ENTRY_SCREEN.tpl'=>'iotd_view_screen'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__iotd_admin_choose_screen()
	{
		$current_iotd=do_lorem_template('IOTD_ADMIN_CHOOSE_SCREEN_IOTD', array(
			'IS_CURRENT'=>placeholder_number(),
			'SUBMITTER_URL'=>placeholder_url(),
			'THUMB_URL'=>placeholder_image_url(),
			'FULL_URL'=>placeholder_url(),
			'ID'=>placeholder_id(),
			'EDIT_URL'=>placeholder_url(),
			'DELETE_URL'=>placeholder_url(),
			'CHOOSE_URL'=>placeholder_url(),
			'CAPTION'=>lorem_phrase(),
			'USER'=>lorem_word(),
			'USERNAME_LINK'=>lorem_word()
		));
		$unused_iotd=$current_iotd;
		$used_iotd=$current_iotd;

		return array(
			lorem_globalise(do_lorem_template('IOTD_ADMIN_CHOOSE_SCREEN', array(
				'SHOWING_OLD'=>lorem_phrase(),
				'TITLE'=>lorem_title(),
				'USED_URL'=>placeholder_url(),
				'CURRENT_IOTD'=>$current_iotd,
				'UNUSED_IOTD'=>$unused_iotd,
				'USED_IOTD'=>$used_iotd
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
	function tpl_preview__block_main_iotd()
	{
		$content=do_lorem_template('IOTD', array(
			'ID'=>placeholder_id(),
			'SUBMITTER'=>placeholder_number(),
			'IMAGE_URL'=>placeholder_image_url(),
			'VIEW_URL'=>placeholder_url(),
			'CAPTION'=>lorem_word(),
			'IMAGE'=>placeholder_image()
		));

		return array(
			lorem_globalise(do_lorem_template('BLOCK_MAIN_IOTD', array(
				'CONTENT'=>$content,
				'FULL_URL'=>placeholder_url(),
				'SUBMIT_URL'=>placeholder_url(),
				'ARCHIVE_URL'=>placeholder_url(),
				'ID'=>placeholder_id()
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
	function tpl_preview__iotd_view_screen_iotd()
	{
		//Wrap 'IOTD_ARCHIVE_SCREEN_IOTD' with 'PAGINATION_SCREEN'

		$content=new ocp_tempcode();
		$content->attach(do_lorem_template('IOTD_ARCHIVE_SCREEN_IOTD', array(
			'SUBMITTER'=>placeholder_id(),
			'ID'=>placeholder_id(),
			'VIEWS'=>placeholder_number(),
			'THUMB'=>placeholder_image(),
			'DATE'=>placeholder_time(),
			'DATE_RAW'=>placeholder_date_raw(),
			'URL'=>placeholder_url(),
			'CAPTION'=>lorem_phrase()
		)));

		return array(
			lorem_globalise(do_lorem_template('PAGINATION_SCREEN', array(
				'TITLE'=>lorem_title(),
				'CONTENT'=>$content,
				'PAGINATION'=>placeholder_pagination()
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
	function tpl_preview__iotd_view_screen()
	{
		require_lang('ocf');
		require_lang('captcha');

		require_lang('trackbacks');
		$trackbacks=new ocp_tempcode();
		foreach (placeholder_array(1) as $k=>$v)
		{
			$trackbacks->attach(do_lorem_template('TRACKBACK', array(
				'ID'=>placeholder_id(),
				'TIME_RAW'=>placeholder_date_raw(),
				'TIME'=>placeholder_date(),
				'URL'=>placeholder_url(),
				'TITLE'=>lorem_phrase(),
				'EXCERPT'=>lorem_paragraph(),
				'NAME'=>lorem_phrase()
			)));
		}
		$trackback_details=do_lorem_template('TRACKBACK_WRAPPER', array(
			'TRACKBACKS'=>$trackbacks,
			'TRACKBACK_PAGE'=>placeholder_id(),
			'TRACKBACK_ID'=>placeholder_id(),
			'TRACKBACK_TITLE'=>lorem_phrase()
		));

		$rating_details=new ocp_tempcode();

		$review_titles=array();
		$review_titles[]=array(
			'REVIEW_TITLE'=>lorem_word(),
			'REVIEW_RATING'=>make_string_tempcode(float_format(10.0))
		);

		$comments='';

		$form=do_lorem_template('COMMENTS_POSTING_FORM', array(
			'JOIN_BITS'=>lorem_phrase_html(),
			'FIRST_POST_URL'=>placeholder_url(),
			'FIRST_POST'=>lorem_paragraph_html(),
			'TYPE'=>'downloads',
			'ID'=>placeholder_id(),
			'REVIEW_RATING_CRITERIA'=>$review_titles,
			'USE_CAPTCHA'=>true,
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

		$comment_details=do_lorem_template('COMMENTS_WRAPPER', array(
			'TYPE'=>lorem_phrase(),
			'ID'=>placeholder_id(),
			'REVIEW_RATING_CRITERIA'=>$review_titles,
			'AUTHORISED_FORUM_URL'=>placeholder_url(),
			'FORM'=>$form,
			'COMMENTS'=>$comments
		));

		return array(
			lorem_globalise(do_lorem_template('IOTD_ENTRY_SCREEN', array(
				'TITLE'=>lorem_title(),
				'SUBMITTER'=>placeholder_id(),
				'I_TITLE'=>lorem_phrase(),
				'CAPTION'=>lorem_phrase(),
				'DATE_RAW'=>placeholder_date_raw(),
				'ADD_DATE_RAW'=>placeholder_date_raw(),
				'EDIT_DATE_RAW'=>placeholder_date_raw(),
				'DATE'=>placeholder_time(),
				'ADD_DATE'=>placeholder_time(),
				'EDIT_DATE'=>placeholder_time(),
				'VIEWS'=>placeholder_number(),
				'TRACKBACK_DETAILS'=>$trackback_details,
				'RATING_DETAILS'=>$rating_details,
				'COMMENT_DETAILS'=>$comment_details,
				'EDIT_URL'=>placeholder_url(),
				'URL'=>placeholder_image_url()
			)), NULL, '', true)
		);
	}
}
