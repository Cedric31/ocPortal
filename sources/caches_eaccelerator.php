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
 * @package		core
 */

class eacceleratorcache
{
	/**
	 * (Plug-in replacement for memcache API) Get data from the persistant cache.
	 *
	 * @param  mixed			Key
	 * @param  ?TIME			Minimum timestamp that entries from the cache may hold (NULL: don't care)
	 * @return ?mixed			The data (NULL: not found / NULL entry)
	 */
	function get($key,$min_cache_date=NULL)
	{
		if (function_exists('eaccelerator_get'))
		{
			$data=eaccelerator_get($key);
		} elseif (function_exists('mmcache_get'))
		{
			$data=mmcache_get($key);
		}
		if (is_null($data)) return NULL;
		if ((!is_null($min_cache_date)) && ($data[0]<$min_cache_date)) return NULL;
		return unserialize($data[1]);
	}

	/**
	 * (Plug-in replacement for memcache API) Put data into the persistant cache.
	 *
	 * @param  mixed			Key
	 * @param  mixed			The data
	 * @param  integer		Various flags (parameter not used)
	 * @param  integer		The expiration time in seconds.
	 */
	function set($key,$data,$flags,$expire_secs)
	{
		unset($flags);

		// Update list of e-objects
		global $ECACHE_OBJECTS;
		if (!array_key_exists($key,$ECACHE_OBJECTS))
		{
			$ECACHE_OBJECTS[$key]=1;
			if (function_exists('eaccelerator_put'))
			{
				eaccelerator_put(get_file_base().'ECACHE_OBJECTS',$ECACHE_OBJECTS,0);
			} elseif (function_exists('mmcache_put'))
			{
				mmcache_put(get_file_base().'ECACHE_OBJECTS',$ECACHE_OBJECTS,0);
			}
		}

		if (function_exists('eaccelerator_put'))
		{
			eaccelerator_put($key,array(time(),serialize($data)),$expire_secs);
		} elseif (function_exists('mmcache_put'))
		{
			mmcache_put($key,array(time(),serialize($data)),$expire_secs);
		}
	}

	/**
	 * (Plug-in replacement for memcache API) Delete data from the persistant cache.
	 *
	 * @param  mixed			Key name
	 */
	function delete($key)
	{
		// Update list of e-objects
		global $ECACHE_OBJECTS;
		unset($ECACHE_OBJECTS[$key]);

		if (function_exists('eaccelerator_put'))
		{
			eaccelerator_put(get_file_base().'ECACHE_OBJECTS',$ECACHE_OBJECTS,0);
		} elseif (function_exists('mmcache_put'))
		{
			mmcache_put(get_file_base().'ECACHE_OBJECTS',$ECACHE_OBJECTS,0);
		}

		if (function_exists('eaccelerator_rm'))
		{
			eaccelerator_rm($key);
		} elseif (function_exists('mmcache_rm'))
		{
			mmcache_rm($key);
		}
	}

	/**
	 * (Plug-in replacement for memcache API) Remove all data from the persistant cache.
	 */
	function flush()
	{
		global $ECACHE_OBJECTS;
		$ECACHE_OBJECTS=array();
		if (function_exists('eaccelerator_rm'))
		{
			foreach (array_keys($ECACHE_OBJECTS) as $obkey)
			{
				eaccelerator_rm($obkey);
			}
		} elseif (function_exists('mmcache_rm'))
		{
			foreach (array_keys($ECACHE_OBJECTS) as $obkey)
			{
				mmcache_rm($obkey);
			}
		}
		if (function_exists('eaccelerator_put'))
		{
			eaccelerator_put(get_file_base().'ECACHE_OBJECTS',$ECACHE_OBJECTS,0);
		} elseif (function_exists('mmcache_put'))
		{
			mmcache_put(get_file_base().'ECACHE_OBJECTS',$ECACHE_OBJECTS,0);
		}
	}
}
