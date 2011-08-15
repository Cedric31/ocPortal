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
 * @package		banners
 */

class Hook_sw_banners
{

	/**
	 * Standard modular run function for features in the setup wizard.
	 *
	 * @param  array		Default values for the fields, from the install-profile.
	 * @return tempcode	An input field.
	 */
	function get_fields($field_defaults)
	{
		if (!addon_installed('banners')) return new ocp_tempcode();

		require_lang('banners');
		$fields=new ocp_tempcode();
		$test=$GLOBALS['SITE_DB']->query_value_null_ok('banners','name',array('name'=>'hosting'));
		if (!is_null($test)) $fields->attach(form_input_tick(do_lang_tempcode('HAVE_DEFAULT_BANNERS_HOSTING'),do_lang_tempcode('DESCRIPTION_HAVE_DEFAULT_BANNERS_HOSTING'),'have_default_banners_hosting',array_key_exists('have_default_banners_hosting',$field_defaults)?($field_defaults['have_default_banners_hosting']=='1'):false));
		$test=$GLOBALS['SITE_DB']->query_value_null_ok('banners','name',array('name'=>'donate'));
		if (!is_null($test)) $fields->attach(form_input_tick(do_lang_tempcode('HAVE_DEFAULT_BANNERS_DONATION'),do_lang_tempcode('DESCRIPTION_HAVE_DEFAULT_BANNERS_DONATION'),'have_default_banners_donation',array_key_exists('have_default_banners_donation',$field_defaults)?($field_defaults['have_default_banners_donation']=='1'):false));
		$test=$GLOBALS['SITE_DB']->query_value_null_ok('banners','name',array('name'=>'advertise_here'));
		if (!is_null($test)) $fields->attach(form_input_tick(do_lang_tempcode('HAVE_DEFAULT_BANNERS_ADVERTISING'),do_lang_tempcode('DESCRIPTION_HAVE_DEFAULT_BANNERS_ADVERTISING'),'have_default_banners_advertising',array_key_exists('have_default_banners_advertising',$field_defaults)?($field_defaults['have_default_banners_advertising']=='1'):false));
		return $fields;
	}

	/**
	 * Standard modular run function for setting features from the setup wizard.
	 */
	function set_fields()
	{
		if (!addon_installed('banners')) return;
		
		if (post_param_integer('have_default_banners_hosting',0)==0)
		{
			$test=$GLOBALS['SITE_DB']->query_value_null_ok('banners','name',array('name'=>'hosting'));
			if (!is_null($test))
			{
				require_code('banners2');
				delete_banner('hosting');
			}
		}
		if (post_param_integer('have_default_banners_donation',0)==0)
		{
			$test=$GLOBALS['SITE_DB']->query_value_null_ok('banners','name',array('name'=>'donate'));
			if (!is_null($test))
			{
				require_code('banners2');
				delete_banner('donate');
			}
		}
		if (post_param_integer('have_default_banners_advertising',0)==0)
		{
			$test=$GLOBALS['SITE_DB']->query_value_null_ok('banners','name',array('name'=>'advertise_here'));
			if (!is_null($test))
			{
				require_code('banners2');
				delete_banner('advertise_here');
			}
		}
		$test=$GLOBALS['SITE_DB']->query_value('banners','COUNT(*)');
		if ($test==0) set_option('is_on_banners','0');
	}
}


