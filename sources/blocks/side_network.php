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
 * @package		msn
 */

class Block_side_network
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
		$info['parameters']=array();
		return $info;
	}

	/**
	 * Standard modular cache function.
	 *
	 * @return ?array	Map of cache details (cache_on and ttl) (NULL: module is disabled).
	 */
	function cacheing_environment()
	{
		$info=array();
		$info['cache_on']='';
		$info['ttl']=60;
		return $info;
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		delete_config_option('network_links');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		add_config_option('NETWORK_LINKS','network_links','line','return get_base_url().\'/netlink.php\';','SITE','ENVIRONMENT',1);
	}

	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		unset($map);

		$netlinks=get_option('network_links');
		if (strlen($netlinks)>0)
		{
			require_code('character_sets');

			$data=http_download_file($netlinks,NULL,false);
			if (is_null($data))
			{
				$if_network=do_lang_tempcode('HTTP_DOWNLOAD_NO_SERVER',escape_html($netlinks));
			} else
			{
				$if_network=make_string_tempcode(convert_to_internal_encoding($data));
			}
			return do_template('BLOCK_SIDE_NETWORK',array('_GUID'=>'5fe8867b9f69670ad61e6c78b956fab2','CONTENT'=>$if_network));
		}
		return new ocp_tempcode();
	}

}


