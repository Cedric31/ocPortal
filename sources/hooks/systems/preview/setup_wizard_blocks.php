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
 * @package		setupwizard
 */

class Hook_Preview_setup_wizard_blocks
{

	/**
	 * Find whether this preview hook applies.
	 *
	 * @return array			Triplet: Whether it applies, the attachment ID type, whether the forum DB is used [optional]
	 */
	function applies()
	{
		$applies=(get_param('page','')=='admin_setupwizard') && (get_param('type')=='step6');
		return array($applies,NULL,false);
	}

	/**
	 * Standard modular run function for preview hooks.
	 *
	 * @return array			A pair: The preview, the updated post Comcode
	 */
	function run()
	{
		require_code('setupwizard');
		
		$collapse_zones=post_param_integer('collapse_user_zones',0)==1;
		
		$installprofile=post_param('installprofile','');
		if ($installprofile!='')
		{
			require_code('hooks/modules/admin_setupwizard_installprofiles/'.$installprofile);
			$object=object_factory('Hook_admin_setupwizard_installprofiles_'.$installprofile);
			$installprofileblocks=$object->default_blocks();
			$block_options=$object->block_options();
		} else
		{
			$installprofileblocks=array();
			$block_options=array();
		}

		$page_structure=_get_zone_pages($installprofileblocks,$block_options,$collapse_zones,$installprofile);
		
		$zone_structure=array_pop($page_structure);

		$preview=do_template('SETUPWIZARD_BLOCK_PREVIEW',array('LEFT'=>$zone_structure['left'],'RIGHT'=>$zone_structure['right'],'START'=>$zone_structure['start']));

		return array($preview,NULL);
	}

}
