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
 * @package		core_notifications
 */

class Hook_addon_registry_core_notifications
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
		return 'Sends out action-triggered notifications to members listening to them.';
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

			'sources/hooks/systems/addon_registry/core_notifications.php',
			'sources/notifications.php',
			'sources/notifications2.php',
			'lang/EN/notifications.ini',
			'sources/hooks/systems/cron/notification_digests.php',
			'sources/hooks/systems/notifications/.htaccess',
			'sources/hooks/systems/notifications/index.html',
			'themes/default/images/EN/page/disable_notifications.png',
			'themes/default/images/EN/page/enable_notifications.png',
			'themes/default/images/EN/pageitem/disable_notifications.png',
			'themes/default/images/EN/pageitem/enable_notifications.png',
			'sources/hooks/systems/profiles_tabs_edit/notifications.php',
			'themes/default/css/notifications.css',
			'JAVASCRIPT_NOTIFICATIONS.tpl',
			'NOTIFICATIONS_MANAGE.tpl',
			'NOTIFICATIONS_MANAGE_SCREEN.tpl',
			'NOTIFICATIONS_MANAGE_ADVANCED_SCREEN.tpl',
			'NOTIFICATIONS_TREE.tpl',
			'NOTIFICATION_TYPES.tpl',
			'NOTIFICATION_BUTTONS.tpl',
			'site/pages/modules/notifications.php',
			'adminzone/pages/modules/admin_notifications.php',
			'sources/hooks/systems/do_next_menus/notifications.php',
			'themes/default/images/bigicons/notifications.png',
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
				'NOTIFICATIONS_MANAGE.tpl'=>'notifications_regular',
				'NOTIFICATIONS_MANAGE_SCREEN.tpl'=>'notifications_regular',
				'NOTIFICATIONS_MANAGE_ADVANCED_SCREEN.tpl'=>'notifications_advanced',
				'NOTIFICATIONS_TREE.tpl'=>'notifications_advanced',
				'NOTIFICATION_TYPES.tpl'=>'notifications_regular',
				);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__notifications_regular()
	{
		require_css('notifications');
		require_javascript('javascript_notifications');

		$notification_types=array();
		$notification_types[]=array(
			'NTYPE'=>placeholder_id(),
			'LABEL'=>lorem_phrase(),
			'CHECKED'=>true,
			'RAW'=>placeholder_number(),
			'AVAILABLE'=>true,
			'SCOPE'=>placeholder_id(),
		);
		$notification_types_titles=array();
		$notification_types_titles[]=array(
			'NTYPE'=>placeholder_id(),
			'LABEL'=>lorem_phrase(),
			'RAW'=>placeholder_number(),
		);
		$notification_sections=array();
		$notification_sections[lorem_phrase()]=array(
			'NOTIFICATION_SECTION'=>lorem_phrase(),
			'NOTIFICATION_CODES'=>array(
				array(
					'NOTIFICATION_CODE'=>placeholder_id(),
					'NOTIFICATION_LABEL'=>lorem_phrase(),
					'NOTIFICATION_TYPES'=>$notification_types,
					'SUPPORTS_CATEGORIES'=>true,
				),
			),
		);
		$interface=do_lorem_template('NOTIFICATIONS_MANAGE',array(
			'COLOR'=>'FFFFFF',
			'NOTIFICATION_TYPES_TITLES'=>$notification_types_titles,
			'NOTIFICATION_SECTIONS'=>$notification_sections,
		));
		$out=do_lorem_template('NOTIFICATIONS_MANAGE_SCREEN',array(
			'TITLE'=>lorem_title(),
			'INTERFACE'=>$interface,
			'ACTION_URL'=>get_self_url(),
		));
		
		return array(
			lorem_globalise(
				$out
			,NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__notifications_advanced()
	{
		require_css('notifications');
		require_javascript('javascript_notifications');

		$notification_types=array();
		$notification_types[]=array(
			'NTYPE'=>placeholder_id(),
			'LABEL'=>lorem_phrase(),
			'CHECKED'=>true,
			'RAW'=>placeholder_number(),
			'AVAILABLE'=>true,
			'SCOPE'=>placeholder_id(),
		);
		$notification_categories=array();
		$notification_categories[]=array(
			'NUM_CHILDREN'=>'0',
			'DEPTH'=>'0',
			'NOTIFICATION_CATEGORY'=>placeholder_id(),
			'NOTIFICATION_TYPES'=>$notification_types,
			'CATEGORY_TITLE'=>lorem_phrase(),
			'CHECKED'=>true,
			'CHILDREN'=>'',
		);
		$tree=do_lorem_template('NOTIFICATIONS_TREE',array(
			'NOTIFICATION_CODE'=>placeholder_id(),
			'NOTIFICATION_CATEGORIES'=>$notification_categories,
		));
		$notification_types_titles=array();
		$notification_types_titles[]=array(
			'NTYPE'=>placeholder_id(),
			'LABEL'=>lorem_phrase(),
			'RAW'=>placeholder_number(),
		);
		$out=do_lorem_template('NOTIFICATIONS_MANAGE_ADVANCED_SCREEN',array(
			'TITLE'=>lorem_title(),
			'COLOR'=>'FFFFFF',
			'ACTION_URL'=>placeholder_url(),
			'NOTIFICATION_TYPES_TITLES'=>$notification_types_titles,
			'TREE'=>$tree,
			'NOTIFICATION_CODE'=>placeholder_id(),
		));

		return array(
			lorem_globalise(
				$out
			,NULL,'',true),
		);
	}

}
