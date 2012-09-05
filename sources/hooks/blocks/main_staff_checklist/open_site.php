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
 * @package		core_adminzone_frontpage
 */

class Hook_checklist_open_site
{

	/**
	 * Standard modular run function.
	 *
	 * @return array		An array of tuples: The task row to show, the number of seconds until it is due (or NULL if not on a timer), the number of things to sort out (or NULL if not on a queue), The name of the config option that controls the schedule (or NULL if no option).
	 */
	function run()
	{
		$url=build_url(array('page'=>'admin_config','type'=>'category','id'=>'SITE'),'adminzone',NULL,false,false,false,'group_CLOSED_SITE');
		$task=urlise_lang(do_lang('NAG_OPEN_WEBSITE'),$url);

		$status=(get_option('site_closed')=='1')?0:1;

		$_status=($status==0)?do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM_STATUS_0'):do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM_STATUS_1');
		$tpl=do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM',array('_GUID'=>'83cfd2a7553a4820f2930484bfa85e47','URL'=>'','STATUS'=>$_status,'TASK'=>$task));
		return array(array($tpl,($status==0)?-1:0,1,NULL));
	}

}

