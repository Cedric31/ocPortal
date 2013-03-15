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
 * @package		core_database_drivers
 */

/*EXTRA FUNCTIONS: oci.+*/

/*

For: php_oci8.dll

*/

/**
 * Standard code module initialisation function.
 */
function init__database__oracle()
{
	global $CACHE_DB;
	$CACHE_DB=array();
}

/**
 * Database Driver.
 * @package		core_database_drivers
 */
class Database_Static_oracle
{

	/**
	 * Get the default user for making db connections (used by the installer as a default).
	 *
	 * @return string			The default user for db connections
	 */
	function db_default_user()
	{
		return 'system';
	}

	/**
	 * Get the default password for making db connections (used by the installer as a default).
	 *
	 * @return string			The default password for db connections
	 */
	function db_default_password()
	{
		return '';
	}

	/**
	 * Create a table index.
	 *
	 * @param  ID_TEXT		The name of the table to create the index on
	 * @param  ID_TEXT		The index name (not really important at all)
	 * @param  string			Part of the SQL query: a comma-separated list of fields to use on the index
	 * @param  array			The DB connection to make on
	 */
	function db_create_index($table_name,$index_name,$_fields,$db)
	{
		if ($index_name[0]=='#')
		{
			$index_name=substr($index_name,1);
			$fields=explode(',',$_fields);
			foreach ($fields as $field)
			{
				$this->db_query('CREATE INDEX index'.$index_name.' ON '.$table_name.'('.$field.') INDEXTYPE IS CTXSYS.CONTEXT PARAMETERS(\'lexer theme_lexer\')',$db);
				$this->db_query('EXEC DBMS_STATS.GATHER_TABLE_STATS(USER,\''.$table_name.'\',cascade=>TRUE)',$db);
			}
			return;
		}
		$this->db_query('CREATE INDEX index'.$index_name.'_'.strval(mt_rand(0,10000)).' ON '.$table_name.'('.$_fields.')',$db);
	}

	/**
	 * Change the primary key of a table.
	 *
	 * @param  ID_TEXT		The name of the table to create the index on
	 * @param  array			A list of fields to put in the new key
	 * @param  array			The DB connection to make on
	 */
	function db_change_primary_key($table_name,$new_key,$db)
	{
		$this->db_query('ALTER TABLE '.$table_name.' DROP PRIMARY KEY',$db);
		$this->db_query('ALTER TABLE '.$table_name.' ADD PRIMARY KEY ('.implode(',',$new_key).')',$db);
	}

	/**
	 * Assemble part of a WHERE clause for doing full-text search
	 *
	 * @param  string			Our match string (assumes "?" has been stripped already)
	 * @param  boolean		Whether to do a boolean full text search
	 * @return string			Part of a WHERE clause for doing full-text search
	 */
	function db_full_text_assemble($content,$boolean)
	{
		unset($boolean);

		$content=str_replace('"','',$content);
		return 'CONTAINS ((?),\''.$this->db_escape_string($content).'\')';
	}

	/**
	 * Get the ID of the first row in an auto-increment table (used whenever we need to reference the first).
	 *
	 * @return integer			First ID used
	 */
	function db_get_first_id()
	{
		return 1;
	}

	/**
	 * Get a map of ocPortal field types, to actual mySQL types.
	 *
	 * @return array			The map
	 */
	function db_get_type_remap()
	{
		$type_remap=array(
							'AUTO'=>'integer',
							'AUTO_LINK'=>'integer',
							'INTEGER'=>'integer',
							'UINTEGER'=>'bigint',
							'SHORT_INTEGER'=>'smallint',
							'REAL'=>'real',
							'BINARY'=>'smallint',
							'USER'=>'integer',
							'GROUP'=>'integer',
							'TIME'=>'integer',
							'LONG_TRANS'=>'integer',
							'SHORT_TRANS'=>'integer',
							'SHORT_TEXT'=>'text',
							'LONG_TEXT'=>'CLOB',
							'ID_TEXT'=>'varchar(80)',
							'MINIID_TEXT'=>'varchar(40)',
							'IP'=>'varchar(40)',
							'LANGUAGE_NAME'=>'varchar(5)',
							'URLPATH'=>'varchar(255)',
							'MD5'=>'varchar(33)'
		);
		return $type_remap;
	}

	/**
	 * Create a new table.
	 *
	 * @param  ID_TEXT		The table name
	 * @param  array			A map of field names to ocPortal field types (with *#? encodings)
	 * @param  array			The DB connection to make on
	 */
	function db_create_table($table_name,$fields,$db)
	{
		$type_remap=$this->db_get_type_remap();

		/*if (multi_lang()==0)
		{
			$type_remap['LONG_TRANS']=$type_remap['LONG_TEXT'];
			$type_remap['SHORT_TRANS']=$type_remap['SHORT_TEXT'];
		}*/

		$_fields='';
		$keys='';
		$trigger=false;
		foreach ($fields as $name=>$type)
		{
			if ($type[0]=='*') // Is a key
			{
				$type=substr($type,1);
				if ($keys!='') $keys.=', ';
				$keys.=$name;
			}

			if ($type[0]=='?') // Is perhaps null
			{
				$type=substr($type,1);
				$perhaps_null='NULL';
			} else $perhaps_null='NOT NULL';

			if ($type=='AUTO')
			{
				$trigger=true;
			}

			$type=$type_remap[$type];

			$_fields.="	  $name $type $perhaps_null,\n";
		}

		$this->db_query('CREATE TABLE '.$table_name.' (
		  '.$_fields.'
		  PRIMARY KEY ('.$keys.')
		)',$db,NULL,NULL);

		if ($trigger)
		{
			$query1="
		CREATE SEQUENCE gen_$table_name
	";
			$query2="
		CREATE OR REPLACE TRIGGER gen_$table_name BEFORE INSERT ON $table_name
		FOR EACH ROW
		BEGIN
			SELECT gen_$table_name.nextval
			into :new.id
			from dual;
		END;
	";

				$parsed1=ociparse($db,$query1);
				$parsed2=ociparse($db,$query2);
				@ociexecute($parsed1);
				ociexecute($parsed2);
		}
	}

	/**
	 * Encode an SQL statement fragment for a conditional to see if two strings are equal.
	 *
	 * @param  ID_TEXT		The attribute
	 * @param  string			The comparison
	 * @return string			The SQL
	 */
	function db_string_equal_to($attribute,$compare)
	{
		return $attribute." LIKE '".$this->db_escape_string($compare)."'";
	}

	/**
	 * Encode an SQL statement fragment for a conditional to see if two strings are not equal.
	 *
	 * @param  ID_TEXT		The attribute
	 * @param  string			The comparison
	 * @return string			The SQL
	 */
	function db_string_not_equal_to($attribute,$compare)
	{
		return $attribute."<>'".$this->db_escape_string($compare)."'";
	}

	/**
	 * This function is internal to the database system, allowing SQL statements to be build up appropriately. Some databases require IS NULL to be used to check for blank strings.
	 *
	 * @return boolean			Whether a blank string IS NULL
	 */
	function db_empty_is_null()
	{
		return true;
	}

	/**
	 * Delete a table.
	 *
	 * @param  ID_TEXT		The table name
	 * @param  array			The DB connection to delete on
	 */
	function db_drop_if_exists($table,$db)
	{
		$this->db_query('DROP TABLE '.$table,$db,NULL,NULL,true);
	}

	/**
	 * Determine whether the database is a flat file database, and thus not have a meaningful connect username and password.
	 *
	 * @return boolean			Whether the database is a flat file database
	 */
	function db_is_flat_file_simple()
	{
		return false;
	}

	/**
	 * Encode a LIKE string comparision fragement for the database system. The pattern is a mixture of characters and ? and % wilcard symbols.
	 *
	 * @param  string			The pattern
	 * @return string			The encoded pattern
	 */
	function db_encode_like($pattern)
	{
		return $this->db_escape_string($pattern);
	}

	/**
	 * Close the database connections. We don't really need to close them (will close at exit), just disassociate so we can refresh them.
	 */
	function db_close_connections()
	{
		global $CACHE_DB;
		foreach ($CACHE_DB as $db)
		{
			foreach ($db as $_db)
			{
				ocicommit($_db);
			}
		}
	}

	/**
	 * Get a database connection. This function shouldn't be used by you, as a connection to the database is established automatically.
	 *
	 * @param  boolean		Whether to create a persistent connection
	 * @param  string			The database name
	 * @param  string			The database host (the server)
	 * @param  string			The database connection username
	 * @param  string			The database connection password
	 * @param  boolean		Whether to on error echo an error and return with a NULL, rather than giving a critical error
	 * @return ?array			A database connection (NULL: failed)
	 */
	function db_get_connection($persistent,$db_name,$db_host,$db_user,$db_password,$fail_ok=false)
	{
		if ($db_host!='localhost') fatal_exit(do_lang_tempcode('ONLY_LOCAL_HOST_FOR_TYPE'));

		// Potential cacheing
		global $CACHE_DB;
		if (isset($CACHE_DB[$db_name][$db_host]))
		{
			return $CACHE_DB[$db_name][$db_host];
		}

		if (!function_exists('ocilogon'))
		{
			$error='The oracle PHP extension not installed (anymore?). You need to contact the system administrator of this server.';
			if ($fail_ok)
			{
				echo $error;
				return NULL;
			}
			critical_error('PASSON',$error);
		}

		$db=$persistent?@ociplogon($db_user,$db_password,$db_name):@ocilogon($db_user,$db_password,$db_name);
		if ($db===false)
		{
			$error='Could not connect to database-server ('.ocierror().')';
			if ($fail_ok)
			{
				echo $error;
				return NULL;
			}
			critical_error('PASSON',$error); //warn_exit(do_lang_tempcode('CONNECT_DB_ERROR'));
		}

		if (!$db) fatal_exit(do_lang('CONNECT_DB_ERROR'));
		$CACHE_DB[$db_name][$db_host]=$db;
		return $db;
	}

	/**
	 * Find whether full-text-search is present
	 *
	 * @param  array			A DB connection
	 * @return boolean		Whether it is
	 */
	function db_has_full_text($db)
	{
		return false;
	}

	/**
	 * Find whether full-text-boolean-search is present
	 *
	 * @return boolean		Whether it is
	 */
	function db_has_full_text_boolean()
	{
		return false;
	}

	/**
	 * Escape a string so it may be inserted into a query. If SQL statements are being built up and passed using db_query then it is essential that this is used for security reasons. Otherwise, the abstraction layer deals with the situation.
	 *
	 * @param  string			The string
	 * @return string			The escaped string
	 */
	function db_escape_string($string)
	{
		$string=str_replace("'","''",$string);
		return str_replace('&','\&',$string);
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
	function db_query($query,$db,$max=NULL,$start=NULL,$fail_ok=false,$get_insert_id=false)
	{
		if ((!is_null($start)) && (!is_null($max)) && (strtoupper(substr($query,0,7))=='SELECT ') || (strtoupper(substr($query,0,8))=='(SELECT '))
		{
			$old_query=$query;

			if (is_null($start)) $start=0;

			$pos=strpos($old_query,'FROM ');
			$pos2=strpos($old_query,' ',$pos+5);
			$pos3=strpos($old_query,'WHERE ',$pos2);
			if ($pos3===false) // No where
			{
				$pos4=strpos($old_query,' ORDER BY');
				if ($pos4===false) $pos4=strlen($old_query);
				$query=substr($old_query,0,$pos4).' WHERE rownum>='.strval(intval($start));
				if (!is_null($max)) $query.=' AND rownum<'.strval(intval($start+$max));
				$query.=substr($old_query,$pos4);
			} else
			{
				$pos4=strpos($old_query,' ORDER BY');
				if ($pos4===false) $pos4=strlen($old_query);
				$query=substr($old_query,0,$pos3).'WHERE ('.substr($old_query,$pos3+6,$pos4-$pos3-6).') AND rownum>='.strval(intval($start));
				if (!is_null($max)) $query.=' AND rownum<'.strval(intval($start+$max));
				$query.=substr($old_query,$pos4);
			}
		}

		$stmt=ociparse($db,$query,0);
		$results=@ociexecute($stmt);
		if ((($results===false) || ((strtoupper(substr($query,0,7))=='SELECT ') || (strtoupper(substr($query,0,8))=='(SELECT ') && ($results===true))) && (!$fail_ok))
		{
			$err=ocierror($db);
			if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($err);
			if ((!running_script('upgrader')) && (get_page_name()!='admin_import'))
			{
				if (!function_exists('do_lang') || is_null(do_lang('QUERY_FAILED',NULL,NULL,NULL,NULL,false))) fatal_exit(htmlentities('Query failed: '.$query.' : '.$err));

				fatal_exit(do_lang_tempcode('QUERY_FAILED',escape_html($query),($err)));
			} else
			{
				echo htmlentities('Database query failed: '.$query.' [').($err).htmlentities(']'.'<br />'.chr(10));
				return NULL;
			}
		}

		if (($results!==true) && (strtoupper(substr($query,0,7))=='SELECT ') || (strtoupper(substr($query,0,8))=='(SELECT ') && ($results!==false))
		{
			return $this->db_get_query_rows($stmt);
		}

		if ($get_insert_id)
		{
			if (strtoupper(substr($query,0,7))=='UPDATE ') return NULL;

			$pos=strpos($query,'(');
			$table_name=substr($query,12,$pos-13);

			$stmt=ociparse($db,'SELECT gen_'.$table_name.'.CURRVAL AS v FROM dual');
			ociexecute($stmt);
			$ar2=ocifetch($stmt);
			return $ar2[0];
		}

		return NULL;
	}

	/**
	 * Get the rows returned from a SELECT query.
	 *
	 * @param  resource		The query result pointer
	 * @param  ?integer		Whether to start reading from (NULL: irrelevant for this forum driver)
	 * @return array			A list of row maps
	 */
	function db_get_query_rows($stmt,$start=NULL)
	{
		$out=array();
		$i=0;

		$num_fields=ocinumcols($stmt);
		$types=array();
		$names=array();
		for ($x=1;$x<=$num_fields;$x++)
		{
			$types[$x]=ocicolumntype($stmt,$x);
			$names[$x]=strtolower(ocicolumnname($stmt,$x));
		}
		while (ocifetch($stmt))
		{
			if ((is_null($start)) || ($i>=$start))
			{
				$newrow=array();

				for ($j=1;$j<=$num_fields;$j++)
				{
					$v=ociresult($stmt,$j);
					if (is_object($v)) $v=$v->load(); // For CLOB's
					if ($v===false)
					{
						fatal_exit(do_lang_tempcode('QUERY_FAILED',ocierror($stmt)));
					}

					$name=$names[$j];
					$type=$types[$j];

					if ($type=='NUMBER')
					{
						if (!is_null($v)) $newrow[$name]=intval($v); else $newrow[$name]=NULL;
					} else
					{
						if ($v==' ') $v='';
						$newrow[$name]=$v;
					}
				}

				$out[]=$newrow;
			}

			$i++;
		}

		return $out;
	}

}


