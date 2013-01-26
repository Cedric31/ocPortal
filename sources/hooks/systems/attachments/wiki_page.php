<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		wiki
 */

class Hook_attachments_wiki_page
{

	/**
	 * Standard modular run function for attachment hooks. They see if permission to an attachment of an ID relating to this content is present for the current member.
	 *
	 * @param  ID_TEXT		The ID
	 * @param  object			The database connection to check on
	 * @return boolean		Whether there is permission
	 */
	function run($id,$connection)
	{
		if ($connection->connection_write!=$GLOBALS['SITE_DB']->connection_write) return false;

		return (has_category_access(get_member(),'wiki_page',strval($id)));
	}

}

