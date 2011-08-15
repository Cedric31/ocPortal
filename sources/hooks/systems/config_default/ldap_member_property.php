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
 * @package		ldap
 */

class Hook_config_default_ldap_member_property
{

	/**
	 * Gets the details relating to the config option.
	 *
	 * @return ?array		The details (NULL: disabled)
	 */
	function get_details()
	{
		return array(
			'human_name'=>'LDAP_MEMBER_PROPERTY',
			'the_type'=>'line',
			'the_page'=>'SECTION_FORUMS',
			'section'=>'LDAP',
			'explanation'=>'CONFIG_OPTION_ldap_member_property',
			'shared_hosting_restricted'=>'0',
			'c_data'=>'',

			'addon'=>'ldap',
		);
	}

	/**
	 * Gets the default value for the config option.
	 *
	 * @return ?string		The default value (NULL: option is disabled)
	 */
	function get_default()
	{
		return (get_option('ldap_is_windows')=='0')?'uid':'sAMAccountName';
	}

}


