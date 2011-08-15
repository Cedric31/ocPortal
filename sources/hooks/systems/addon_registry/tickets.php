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
 * @package		tickets
 */

class Hook_addon_registry_tickets
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
		return 'A support ticket system.';
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
			'conflicts_with'=>array(),
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

			'sources/hooks/systems/config_default/ticket_forum_name.php',
			'sources/hooks/systems/config_default/ticket_member_forums.php',
			'sources/hooks/systems/config_default/ticket_text.php',
			'sources/hooks/systems/config_default/ticket_type_forums.php',
			'sources/hooks/systems/addon_registry/tickets.php',
			'sources/hooks/modules/admin_import_types/tickets.php',
			'SUPPORT_TICKET_TYPE_SCREEN.tpl',
			'SUPPORT_TICKET_SCREEN.tpl',
			'SUPPORT_TICKETS_SCREEN.tpl',
			'SUPPORT_TICKET_LINK.tpl',
			'SUPPORT_TICKETS_SEARCH_SCREEN.tpl',
			'adminzone/pages/modules/admin_tickets.php',
			'tickets.css',
			'themes/default/images/bigicons/tickets.png',
			'themes/default/images/pagepics/tickets.png',
			'lang/EN/tickets.ini',
			'site/pages/modules/tickets.php',
			'sources/hooks/systems/change_detection/tickets.php',
			'sources/hooks/systems/do_next_menus/tickets.php',
			'sources/hooks/systems/module_permissions/tickets.php',
			'sources/hooks/systems/rss/tickets.php',
			'sources/hooks/systems/cron/ticket_type_lead_times.php',
			'sources/tickets.php',
			'sources/tickets2.php',
			'sources/hooks/systems/preview/ticket.php',
			'themes/default/images/EN/page/staff_only_reply.png',
			'sources/hooks/blocks/main_staff_checklist/tickets.php',
		);
	}


	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array                 The mapping
	*/
	function tpl_previews()
	{
		return array(
			'SUPPORT_TICKET_LINK.tpl'=>'support_tickets_screen',
			'SUPPORT_TICKETS_SCREEN.tpl'=>'support_tickets_screen',
			'SUPPORT_TICKET_SCREEN.tpl'=>'support_ticket_screen',
			'SUPPORT_TICKETS_SEARCH_SCREEN.tpl'=>'support_tickets_search_screen',
			'SUPPORT_TICKET_TYPE_SCREEN.tpl'=>'support_ticket_type_screen',
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__support_tickets_screen()
	{
		$links = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$links->attach(do_lorem_template('SUPPORT_TICKET_LINK',array('NUM_POSTS'=>placeholder_number(),'CLOSED'=>lorem_phrase(),'URL'=>placeholder_url(),'TITLE'=>lorem_phrase(),'DATE'=>placeholder_date(),'DATE_RAW'=>placeholder_date_raw(),'PROFILE_LINK'=>placeholder_url(),'LAST_POSTER'=>lorem_phrase(),'UNCLOSED'=>lorem_word())));
		}

		return array(
			lorem_globalise(
				do_lorem_template('SUPPORT_TICKETS_SCREEN',array(
					'TITLE'=>lorem_title(),
					'MESSAGE'=>lorem_phrase(),
					'LINKS'=>$links,
					'TICKET_TYPE'=>lorem_word(),
					'NAME'=>lorem_word_2(),
					'SELECTED'=>true,
					'ADD_TICKET_URL'=>placeholder_url(),
					'TYPES'=>placeholder_array(),
					'LEAD_TIME'=>placeholder_number()
				)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__support_ticket_screen()
	{
		require_javascript('javascript_ajax');
		require_lang('ocf');

		$other_tickets = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$other_tickets->attach(do_lorem_template('SUPPORT_TICKET_LINK',array('NUM_POSTS'=>placeholder_number(),'CLOSED'=>lorem_phrase(),'URL'=>placeholder_url(),'TITLE'=>lorem_phrase(),'DATE'=>placeholder_date(),'DATE_RAW'=>placeholder_date_raw(),'PROFILE_LINK'=>placeholder_url(),'LAST_POSTER'=>lorem_phrase(),'UNCLOSED'=>lorem_word())));
		}

		$comments = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$tpl_post = do_lorem_template('POST',array('POSTER_ID'=>placeholder_id(),'EDIT_URL'=>placeholder_url(),'HIGHLIGHT'=>lorem_word(),'TITLE'=>lorem_phrase(),'TIME'=>placeholder_date(),'TIME_RAW'=>placeholder_date_raw(),'POSTER_LINK'=>placeholder_url(),'POSTER_NAME'=>lorem_phrase(),'POST'=>lorem_sentence()));
			$comments->attach($tpl_post);
		}

		$comment_box = do_lorem_template('COMMENTS',array('JOIN_BITS'=>lorem_phrase_html(),'FIRST_POST_URL'=>placeholder_url(),'FIRST_POST'=>lorem_paragraph_html(),'USE_CAPTCHA'=>false,'ATTACHMENTS'=>lorem_phrase(),'ATTACH_SIZE_FIELD'=>lorem_phrase(),'POST_WARNING'=>'','COMMENT_TEXT'=>'','GET_EMAIL'=>lorem_word(),'EMAIL_OPTIONAL'=>lorem_word(),'GET_TITLE'=>true,'EM'=>placeholder_emoticon_chooser(),'DISPLAY'=>'block','COMMENT_URL'=>'','SUBMIT_NAME'=>lorem_phrase(),'TITLE'=>lorem_phrase(),'MAKE_POST'=>true,'CREATE_TICKET_MAKE_POST'=>true));

		return array(
			lorem_globalise(
				do_lorem_template('SUPPORT_TICKET_SCREEN',array(
					'TOGGLE_TICKET_CLOSED_URL'=>placeholder_url(),
					'CLOSED'=>lorem_phrase(),
					'OTHER_TICKETS'=>$other_tickets,
					'USERNAME'=>lorem_word(),
					'PING_URL'=>placeholder_url(),
					'WARNING_DETAILS'=>'',
					'NEW'=>lorem_phrase(),
					'TICKET_PAGE_TEXT'=>lorem_sentence_html(),
					'TYPES'=>placeholder_array(),
					'STAFF_ONLY'=>placeholder_fields(),
					'POSTER'=>lorem_phrase(),
					'TITLE'=>lorem_title(),
					'COMMENTS'=>$comments,
					'COMMENT_BOX'=>$comment_box,
					'STAFF_DETAILS'=>placeholder_url(),
					'URL'=>placeholder_url(),
				)
			),NULL,'',true)
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__support_tickets_search_screen()
	{
		return array(
			lorem_globalise(
				do_lorem_template('SUPPORT_TICKETS_SEARCH_SCREEN',array(
					'TITLE'=>lorem_title(),
					'URL'=>placeholder_url(),
					'POST_FIELDS'=>lorem_phrase(),
					'RESULTS'=>lorem_phrase(),
				)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__support_ticket_type_screen()
	{
		return array(
			lorem_globalise(
				do_lorem_template('SUPPORT_TICKET_TYPE_SCREEN',array(
					'TITLE'=>lorem_title(),
					'TPL'=>placeholder_form(),
					'ADD_FORM'=>placeholder_form(),
				)
			),NULL,'',true),
		);
	}
}