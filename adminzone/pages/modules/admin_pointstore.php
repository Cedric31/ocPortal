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
 * @package		pointstore
 */

/**
 * Module page class.
 */
class Module_admin_pointstore
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL; 
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=false;
		return $info;
	}
	
	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('misc'=>'POINTSTORE_MANAGE_SALES','p'=>'POINTSTORE_MANAGE_INVENTORY');
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		$GLOBALS['HELPER_PANEL_PIC']='pagepics/pointstore';
		$GLOBALS['HELPER_PANEL_TUTORIAL']='tut_points';

		require_lang('pointstore');
		require_lang('menus');
		require_code('form_templates');
		require_css('points');
	
		$type=get_param('type','misc');
	
		if ($type=='misc') return $this->pointstore_log_interface();
		if ($type=='_logs') return $this->delete_log_entry();
		if ($type=='p') return $this->interface_set_prices();
		if ($type=='_p') return $this->set_prices();
	
		return new ocp_tempcode();
	}
	
	/**
	 * The UI to view point-store logs.
	 *
	 * @return tempcode		The UI
	 */
	function pointstore_log_interface()
	{
		$also_url=build_url(array('page'=>'_SELF','type'=>'p'),'_SELF');
		attach_message(do_lang_tempcode('ALSO_SEE_SETUP',escape_html($also_url->evaluate())),'inform');

		$title=get_page_title('POINTSTORE_MANAGE_SALES');
	
		$rows=$GLOBALS['SITE_DB']->query_select('sales',array('*'),NULL,'ORDER BY date_and_time DESC');
		$out=new ocp_tempcode();
		require_code('templates_results_table');
		require_code('templates_table_table');
		$do_other_details=false;
		foreach ($rows as $row)
		{
			if ($row['details2']!='') $do_other_details=true;
		}
		foreach ($rows as $row)
		{
			$member=$GLOBALS['FORUM_DRIVER']->get_username($row['memberid']);
			if (is_null($member)) $member=do_lang('UNKNOWN');
			switch ($row['purchasetype'])
			{
				case 'banner':
					require_lang('banners');
					$type=do_lang('ADD_BANNER');
					break;
				case 'pop3':
					$type=do_lang('POP3');
					break;
				case 'forwarding':
					$type=do_lang('FORWARDING');
					break;
				default:
					$type=do_lang($row['purchasetype'],NULL,NULL,NULL,NULL,false);
					if (is_null($type)) $type=$row['purchasetype'];
					break;
			}
			$details_1=$row['details'];
			$details_2=$row['details2'];
			$date=get_timezoned_date($row['date_and_time']);
	
			$url=build_url(array('page'=>'_SELF','type'=>'_logs','date_and_time'=>$row['date_and_time'],'memberid'=>$row['memberid']),'_SELF');
			$actions=do_template('TABLE_TABLE_ACTION_DELETE_ENTRY',array('_GUID'=>'12e3ea365f1a1ed2e7800293f3203283','NAME'=>$member,'URL'=>$url));
	
			if ($do_other_details)
			{
				$out->attach(table_table_row(array($member,$type,$details_1,$details_2,$date,$actions)));
			} else
			{
				$out->attach(table_table_row(array($member,$type,$details_1,$date,$actions)));
			}
		}
		if ($out->is_empty())
		{
			return inform_screen($title,do_lang_tempcode('NO_ENTRIES'));
		}
	
		if ($do_other_details)
		{
			$header_row=table_table_header_row(array(do_lang_tempcode('USERNAME'),do_lang_tempcode('PURCHASE'),do_lang_tempcode('DETAILS'),do_lang_tempcode('OTHER_DETAILS'),do_lang_tempcode('DATE_TIME'),do_lang_tempcode('ACTIONS')));
		} else
		{
			$header_row=table_table_header_row(array(do_lang_tempcode('USERNAME'),do_lang_tempcode('PURCHASE'),do_lang_tempcode('DETAILS'),do_lang_tempcode('DATE_TIME'),do_lang_tempcode('ACTIONS')));
		}

		$content=do_template('TABLE_TABLE',array('_GUID'=>'d87800ff26e9e5b8f7593fae971faa73','HEADER_ROW'=>$header_row,'ROWS'=>$out));

		return do_template('POINTSTORE_LOG_SCREEN',array('_GUID'=>'014cf9436ece951edb55f2f7b0efb597','TITLE'=>$title,'CONTENT'=>$content));
	}
	
	/**
	 * The actualiser to delete a purchase.
	 *
	 * @return tempcode		The UI
	 */
	function delete_log_entry()
	{
		$title=get_page_title('POINTSTORE_MANAGE_SALES');
	
		$this->_delete_log_entry(get_param_integer('date_and_time'),get_param_integer('memberid'));
	
		// Show it worked / Refresh
		$url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * Delete a point-store purchase.
	 *
	 * @param  integer		The time of the purchase
	 * @param  MEMBER			The member that made the purchase
	 */
	function _delete_log_entry($date_and_time,$memberid)
	{
		$GLOBALS['SITE_DB']->query_delete('sales',array('date_and_time'=>$date_and_time,'memberid'=>$memberid),'',1);
	}
	
	/**
	 * The UI to set point-store prices.
	 *
	 * @return tempcode		The UI
	 */
	function interface_set_prices()
	{
		$also_url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		attach_message(do_lang_tempcode('ALSO_SEE_USAGE',escape_html($also_url->evaluate())),'inform');

		$title=get_page_title('POINTSTORE_MANAGE_INVENTORY');

		$field_groups=new ocp_tempcode();
		$add_forms=new ocp_tempcode();

		// Load up configuration from hooks
		$_hooks=find_all_hooks('modules','pointstore');
		foreach (array_keys($_hooks) as $hook)
		{
			require_code('hooks/modules/pointstore/'.filter_naughty_harsh($hook));
			$object=object_factory('Hook_pointstore_'.filter_naughty_harsh($hook),true);
			if (is_null($object)) continue;
			if (method_exists($object,'config'))
			{
				$fg=$object->config();
				if (!is_null($fg))
				{
					foreach ($fg[0] as $__fg)
					{
						$_fg=do_template('FORM_GROUP',array('_GUID'=>'58a0948313f0e8e69c06ee01fb7ee48a','FIELDS'=>$__fg[0],'HIDDEN'=>$__fg[1]));
						$field_groups->attach(do_template('POINTSTORE_PRICES_FORM_WRAP',array('_GUID'=>'938143162b418de982cdb6ce8d8a92ee','TITLE'=>$__fg[2],'FORM'=>$_fg)));
					}
					if (!$fg[2]->is_empty())
					{
						$submit_name=do_lang_tempcode('ADD');
						$post_url=build_url(array('page'=>'_SELF','type'=>'_p'),'_SELF');
						$fg[2]=do_template('FORM',array('SECONDARY_FORM'=>true,'TABINDEX'=>strval(get_form_field_tabindex(NULL)),'HIDDEN'=>'','TEXT'=>'','FIELDS'=>$fg[2],'SUBMIT_BUTTON_CLASS'=>'proceed_button_left','SUBMIT_NAME'=>$submit_name,'URL'=>$post_url));
						$add_forms->attach(do_template('POINTSTORE_PRICES_FORM_WRAP',array('_GUID'=>'3956550ebff14bbb923b57c8341b0862','TITLE'=>$fg[1],'FORM'=>$fg[2])));
					}
				}
			}
		}

		$submit_name=do_lang_tempcode('SAVE_ALL');
		$post_url=build_url(array('page'=>'_SELF','type'=>'_p'),'_SELF');
		$edit_form=$field_groups->is_empty()?new ocp_tempcode():do_template('FORM_GROUPED',array('_GUID'=>'bf025026dcfc86cfd0a8ef3728bbf6d8','TEXT'=>'','FIELD_GROUPS'=>$field_groups,'SUBMIT_NAME'=>$submit_name,'SUBMIT_BUTTON_CLASS'=>'proceed_button_left_2','URL'=>$post_url));

		list($warning_details,$ping_url)=handle_conflict_resolution();

		return do_template('POINTSTORE_PRICE_SCREEN',array('_GUID'=>'278c8244c7f1743370198dfc437b7bbf','PING_URL'=>$ping_url,'WARNING_DETAILS'=>$warning_details,'TITLE'=>$title,'EDIT_FORM'=>$edit_form,'ADD_FORMS'=>$add_forms));
	}

	/**
	 * The actualiser to set point-store prices.
	 *
	 * @return tempcode		The UI
	 */
	function set_prices()
	{
		$title=get_page_title('POINTSTORE_MANAGE_INVENTORY');
	
		// Save configuration for hooks
		$_hooks=find_all_hooks('modules','pointstore');
		foreach (array_keys($_hooks) as $hook)
		{
			require_code('hooks/modules/pointstore/'.filter_naughty_harsh($hook));
			$object=object_factory('Hook_pointstore_'.filter_naughty_harsh($hook),true);
			if (is_null($object)) continue;
			if (method_exists($object,'save_config'))
			{
				$object->save_config();
			}
		}

		log_it('POINTSTORE_CHANGED_PRICES');

		// Show it worked / Refresh
		$url=build_url(array('page'=>'_SELF','type'=>'p'),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

}


