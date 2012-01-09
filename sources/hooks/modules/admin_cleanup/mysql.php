<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/
/*EXTRA FUNCTIONS: mysql\_.+*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_cleanup_tools
 */

class Hook_mysql
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		if (get_db_type()!='mysql') return NULL;
	
		$info=array();
		$info['title']=do_lang_tempcode('MYSQL_OPTIMISE');
		$info['description']=do_lang_tempcode('DESCRIPTION_MYSQL_OPTIMISE');
		$info['type']='optimise';

		return $info;
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	Results
	 */
	function run()
	{
		$out=new ocp_tempcode();
	
		$tables=$GLOBALS['SITE_DB']->query_select('db_meta',array('DISTINCT m_table'));
		if (count($GLOBALS['SITE_DB']->connection_write)>4) // Okay, we can't be lazy anymore
		{
			$GLOBALS['SITE_DB']->connection_write=call_user_func_array(array($GLOBALS['SITE_DB']->static_ob,'db_get_connection'),$GLOBALS['SITE_DB']->connection_write);
			_general_db_init();
		}
		list($db,$db_name)=$GLOBALS['SITE_DB']->connection_write;
		mysql_select_db($db_name,$db);
	
		foreach ($tables as $table)
		{
			if ($table['m_table']=='sessions') continue; // HEAP, so can't be repaired
			
			$table=get_table_prefix().$table['m_table'];
	
			// Check/Repair
			$result=mysql_query('CHECK TABLE '.$table.' FAST',$db);
			echo mysql_error($db);
			mysql_data_seek($result,mysql_num_rows($result)-1);
			$status_row=mysql_fetch_assoc($result);
			if ($status_row['Msg_type']!='status')
			{
				$out->attach(paragraph(do_lang_tempcode('TABLE_ERROR',escape_html($table),escape_html($status_row['Msg_type']),array(escape_html($status_row['Msg_text']))),'dfsdgdsgfgd'));
				$result2=mysql_query('REPAIR TABLE '.$table,$db);
				mysql_data_seek($result2,mysql_num_rows($result2)-1);
				$status_row_2=mysql_fetch_assoc($result2);
				$out->attach(paragraph(do_lang_tempcode('TABLE_FIXED',escape_html($table),escape_html($status_row_2['Msg_type']),array(escape_html($status_row_2['Msg_text']))),'dfsdfgdst4'));
			}
	
			// Optimise
			mysql_unbuffered_query('OPTIMIZE TABLE '.$table,$db);
		}
	
		return $out;
	}

}


