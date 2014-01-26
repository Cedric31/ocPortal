<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_adminzone_dashboard
 */

class Hook_cron_staff_checklist_notify
{
	/**
	 * Standard modular run function for CRON hooks. Searches for tasks to perform.
	 */
	function run()
	{
		require_lang('staff_checklist');

		$time=time();
		$last_time=intval(get_long_value('last_staff_checklist_notify'));
		if ($last_time>time()-24*60*60*7) return;
		set_long_value('last_staff_checklist_notify',strval($time));

		require_code('blocks/main_staff_checklist');

		// Find if anything needs doing
		$outstanding=0;
		$rows=$GLOBALS['SITE_DB']->query_select('customtasks',array('*'));
		foreach($rows as $r)
		{
			$task_done=((!is_null($r['taskisdone'])) && (($r['recurinterval']==0) || (($r['recurevery']!='mins') || (time()<$r['taskisdone']+60*$r['recurinterval'])) && (($r['recurevery']!='hours') || (time()<$r['taskisdone']+60*60*$r['recurinterval'])) && (($r['recurevery']!='days') || (time()<$r['taskisdone']+24*60*60*$r['recurinterval'])) && (($r['recurevery']!='months') || (time()<$r['taskisdone']+31*24*60*60*$r['recurinterval']))));
			if (!$task_done) $outstanding++;
		}
		$_hooks=find_all_hooks('blocks','main_staff_checklist');
		foreach (array_keys($_hooks) as $hook)
		{
			require_code('hooks/blocks/main_staff_checklist/'.filter_naughty_harsh($hook));
			$object=object_factory('Hook_checklist_'.filter_naughty_harsh($hook),true);
			if (is_null($object)) continue;
			$ret=$object->run();
			if ((!is_null($ret)) && (count($ret)!=0))
			{
				foreach ($ret as $r)
				{
					if (!is_null($r[2]))
					{
						if ($r[2]>0)
							$outstanding++; // A tally of undone stuff
					} elseif (!is_null($r[1]))
					{
						if ($r[1]<0) // Needed doing in the past
							$outstanding++;
					}
				}
			}
		}

		if ($outstanding>0)
		{
			require_lang('staff_checklist');

			require_code('notifications');
			$subject=do_lang('STAFF_CHECKLIST_MAIL_SUBJECT',integer_format($outstanding),get_site_name(),NULL,get_site_default_lang());
			$adminzone_url=build_url(array('page'=>''),'adminzone',NULL,false,false,true);
			$message=do_lang('STAFF_CHECKLIST_MAIL_BODY',integer_format($outstanding),get_site_name(),static_evaluate_tempcode($adminzone_url),get_site_default_lang());
			dispatch_notification('staff_checklist',NULL,$subject,$message,NULL,A_FROM_SYSTEM_PRIVILEGED);
		}
	}
}


