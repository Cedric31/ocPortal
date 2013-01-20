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
 * @package		core_database_drivers
 */

require_code('database/shared/mysql');

/**
 * Database Driver.
 * @package		core_database_drivers
 */
class Database_Static_mysql extends Database_super_mysql
{
	var $cache_db=array();
	var $last_select_db=NULL;
	var $reconnected_once=false;

	/**
	 * Get a database connection. This function shouldn't be used by you, as a connection to the database is established automatically.
	 *
	 * @param  boolean		Whether to create a persistent connection
	 * @param  string			The database name
	 * @param  string			The database host (the server)
	 * @param  string			The database connection username
	 * @param  string			The database connection password
	 * @param  boolean		Whether to on error echo an error and return with a NULL, rather than giving a critical error
	 * @return ?array			A database connection (note for mySQL, it's actually a pair, containing the database name too: because we need to select the name before each query on the connection) (NULL: failed)
	 */
	function db_get_connection($persistent,$db_name,$db_host,$db_user,$db_password,$fail_ok=false)
	{
		// Potential cacheing
		$x=serialize(array($db_name,$db_host));
		if (array_key_exists($x,$this->cache_db))
		{
			if ($this->last_select_db!=$db_name)
			{
				mysql_select_db($db_name,$x);
				$this->last_select_db=$db_name;
			}

			return array($x,$db_name);
		}

		if (!function_exists('mysql_connect'))
		{
			$error='The mySQL PHP extension not installed (anymore?). You need to contact the system administrator of this server, or use a different mySQL database driver (drivers can be chosen by editing _config.php).';
			if ($fail_ok)
			{
				echo $error.chr(10);
				return NULL;
			}
			critical_error('PASSON',$error);
		}

		if ((defined('HIPHOP_PHP')) && ($db_host=='localhost')) $db_host='127.0.0.1';
		$db=$persistent?@mysql_pconnect($db_host,$db_user,$db_password):@mysql_connect($db_host,$db_user,$db_password);
		if ($db===false)
		{
			$error='Could not connect to database-server ('.mysql_error().', '.(@strval($php_errormsg)).')';
			if ($fail_ok)
			{
				echo $error.chr(10);
				return NULL;
			}
			critical_error('PASSON',$error); //warn_exit(do_lang_tempcode('CONNECT_DB_ERROR'));
		}
		if (!mysql_select_db($db_name,$db))
		{
			if ($db_user=='root')
			{
				@mysql_query('CREATE DATABASE IF NOT EXISTS '.$db_name,$db);
			}

			if (!mysql_select_db($db_name,$db))
			{
				$error='Could not connect to database ('.mysql_error().')';
				if ($fail_ok)
				{
					echo $error.chr(10);
					return NULL;
				}
				critical_error('PASSON',$error); //warn_exit(do_lang_tempcode('CONNECT_ERROR'));
			}
		}
		$this->last_select_db=$db_name;

		global $SITE_INFO;
		if (!array_key_exists('database_charset',$SITE_INFO)) $SITE_INFO['database_charset']=(strtolower(get_charset())=='utf-8')?'utf8':'latin1';
		if (function_exists('mysql_set_charset'))
		{
			mysql_set_charset($SITE_INFO['database_charset'],$db);
		} else
		{
			@mysql_query('SET NAMES "'.addslashes($SITE_INFO['database_charset']).'"',$db);
		}
		@mysql_query('SET WAIT_TIMEOUT=28800',$db);
		@mysql_query('SET SQL_BIG_SELECTS=1',$db);
		if ((get_forum_type()=='ocf') && ($GLOBALS['IN_MINIKERNEL_VERSION']==0)) @mysql_query('SET sql_mode=\'STRICT_ALL_TABLES\'',$db); else @mysql_query('SET sql_mode=\'MYSQL40\'',$db);
		// NB: Can add ,ONLY_FULL_GROUP_BY for testing on what other DBs will do, but can_arbitrary_groupby() would need to be made to return false

		return array($db,$db_name);
	}

	/**
	 * Find whether full-text-search is present
	 *
	 * @param  array			A DB connection
	 * @return boolean		Whether it is
	 */
	function db_has_full_text($db)
	{
		if ($this->using_innodb()) return false;

		return true;
	}

	/**
	 * Find whether subquery support is present
	 *
	 * @param  array			A DB connection
	 * @return boolean		Whether it is
	 */
	function db_has_subqueries($db)
	{
		return true;
	}

	/**
	 * Find whether collate support is present
	 *
	 * @param  array			A DB connection
	 * @return boolean		Whether it is
	 */
	function db_has_collate_settings($db)
	{
		return true;
	}

	/**
	 * Find whether full-text-boolean-search is present
	 *
	 * @return boolean		Whether it is
	 */
	function db_has_full_text_boolean()
	{
		return true;
	}

	/**
	 * Escape a string so it may be inserted into a query. If SQL statements are being built up and passed using db_query then it is essential that this is used for security reasons. Otherwise, the abstraction layer deals with the situation.
	 *
	 * @param  string			The string
	 * @return string			The escaped string
	 */
	function db_escape_string($string)
	{
		static $mres=NULL;
		if ($mres===NULL) $mres=function_exists('mysql_real_escape_string');
		if (($mres) && (isset($GLOBALS['SITE_DB']->connection_read[0])) && ($GLOBALS['SITE_DB']->connection_read[0]!==false))
			return mysql_real_escape_string($string,$GLOBALS['SITE_DB']->connection_read[0]);
		if (!function_exists('mysql_escape_string')) return addslashes($string);
		return @mysql_escape_string($string);
	}

	/**
	 * This function is a very basic query executor. It shouldn't usually be used by you, as there are abstracted versions available.
	 *
	 * @param  string			The complete SQL query
	 * @param  array			A DB connection
	 * @param  ?integer		The maximum number of rows to affect (NULL: no limit)
	 * @param  ?integer		The start row to affect (NULL: no specification)
	 * @param  boolean		Whether to output an error on failure
	 * @param  boolean		Whether to get the autoincrement ID created for an insert query
	 * @return ?mixed			The results (NULL: no results), or the insert ID
	 */
	function db_query($query,$db_parts,$max=NULL,$start=NULL,$fail_ok=false,$get_insert_id=false)
	{
		list($db,$db_name)=$db_parts;

		if (isset($query[500000])) // Let's hope we can fail on this, because it's a huge query. We can only allow it if mySQL can.
		{
			$test_result=$this->db_query('SHOW VARIABLES LIKE \'max_allowed_packet\'',$db_parts,NULL,NULL,true);

			if (!is_array($test_result)) return NULL;
			if (intval($test_result[0]['Value'])<intval(strlen($query)*1.2))
			{
				/*@mysql_query('SET session max_allowed_packet='.strval(intval(strlen($query)*1.3)),$db); Does not work well, as MySQL server has gone away error will likely just happen instead */

				if ($get_insert_id) fatal_exit(do_lang_tempcode('QUERY_FAILED_TOO_BIG',escape_html($query)));
				return NULL;
			}
		}

		if ($this->last_select_db!=$db_name)
		{
			mysql_select_db($db_name,$db);
			$this->last_select_db=$db_name;
		}

		if (($max!==NULL) && ($start!==NULL)) $query.=' LIMIT '.strval($start).','.strval($max);
		elseif ($max!==NULL) $query.=' LIMIT '.strval($max);
		elseif ($start!==NULL) $query.=' LIMIT '.strval($start).',30000000';

		$results=@mysql_query($query,$db);
		if (($results===false) && ((!$fail_ok) || (strpos(mysql_error($db),'is marked as crashed and should be repaired')!==false)))
		{
			$err=mysql_error($db);

			if ((function_exists('mysql_ping')) && ($err=='MySQL server has gone away') && (!$this->reconnected_once))
			{
				$this->reconnected_once=true;
				mysql_ping($db);
				$ret=$this->db_query($query,$db_parts,$max,$start,$fail_ok,$get_insert_id);
				$this->reconnected_once=false;
				return $ret;
			}

			if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($err);
			if ((!running_script('upgrader')) && (get_page_name()!='admin_import') && (strpos($err,'Duplicate entry')===false))
			{
				$matches=array();
				if (preg_match('#/(\w+)\' is marked as crashed and should be repaired#U',$err,$matches)!=0)
				{
					$this->db_query('REPAIR TABLE '.$matches[1],$db_parts);
				}

				if (!function_exists('do_lang') || is_null(do_lang('QUERY_FAILED',NULL,NULL,NULL,NULL,false))) fatal_exit(htmlentities('Query failed: '.$query.' : '.$err));
				fatal_exit(do_lang_tempcode('QUERY_FAILED',escape_html($query),($err)));
			} else
			{
				echo htmlentities('Database query failed: '.$query.' [').($err).htmlentities(']'.'<br />'.chr(10));
				return NULL;
			}
		}

		$sub=substr(ltrim($query),0,7);
		if (($results!==true) && (($sub=='SELECT ') || ($sub=='select ') || (strtoupper(substr(ltrim($query),0,8))=='EXPLAIN ') || (strtoupper(substr(ltrim($query),0,9))=='DESCRIBE ') || (strtoupper(substr(ltrim($query),0,5))=='SHOW ')) && ($results!==false))
		{
			return $this->db_get_query_rows($results);
		}

		if ($get_insert_id)
		{
			if (strtoupper(substr($query,0,7))=='UPDATE ') return mysql_affected_rows($db);
			$ins=mysql_insert_id($db);
			if ($ins===0)
			{
				$table=substr($query,12,strpos($query,' ',12)-12);
				$rows=$this->db_query('SELECT MAX(id) AS x FROM '.$table,$db_parts,1,0,false,false);
				return $rows[0]['x'];
			}
			return $ins;
		}

		return NULL;
	}

	/**
	 * Get the rows returned from a SELECT query.
	 *
	 * @param  resource		The query result pointer
	 * @return array			A list of row maps
	 */
	function db_get_query_rows($results)
	{
		$row=mysql_fetch_row($results); // cannot use mysql_fetch_assoc because no dupe results are returned, which knocks off the offsets used by mysql_field_type
		if ($row===false) // Quick get away
		{
			mysql_free_result($results);
			return array();
		}

		$num_fields=mysql_num_fields($results);
		$names=array();
		$types=array();
		for ($x=0;$x<$num_fields;$x++)
		{
			$names[$x]=mysql_field_name($results,$x);
			$types[$x]=mysql_field_type($results,$x);
		}

		$out=array();
		$newrow=array();
		do
		{
			$j=0;
			foreach ($row as $v)
			{
				$name=$names[$j];
				$type=$types[$j];

				switch ($type)
				{
					case 'int':
						if (($v===NULL) || ($v==='')) // Roadsend returns empty string instead of NULL
						{
							$newrow[$name]=NULL;
						} else
						{
							$_v=intval($v);
							$newrow[$name]=$_v;
						}
						break;

					case 'real':
						$newrow[$name]=is_string($v)?floatval($v):$v;
						break;

					case 'unknown':
						if (is_string($v))
						{
							if ((strlen($v)==1) && ($v[0]==chr(0)))
							{
								$newrow[$name]=($v===NULL)?NULL:ord($v); // 0/1 char for BIT field
							} else
							{
								$newrow[$name]=intval($v);
							}

							break;
						}

					default:
						$newrow[$name]=$v;
				}

				++$j;
			}

			$out[]=$newrow;
		}
		while (false!==($row=mysql_fetch_row($results)));

		mysql_free_result($results);
		return $out;
	}

}


