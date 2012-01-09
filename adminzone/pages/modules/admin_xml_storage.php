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
 * @package		import
 */

/**
 * Module page class.
 */
class Module_admin_xml_storage
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
		return array('misc'=>'XML_DATA_MANAGEMENT');
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		$type=get_param('type','misc');
		
		require_code('xml_storage');
		require_lang('xml_storage');
		require_lang('import');
		
		$GLOBALS['HELPER_PANEL_PIC']='pagepics/xml';
		$GLOBALS['HELPER_PANEL_TEXT']=comcode_lang_string('DOC_XML_DATA_MANAGEMENT');

		switch ($type)
		{
			case 'misc':
				return $this->ui();

			case '_import':
				return $this->_import();

			case '_export':
				return $this->_export();
		}
		
		return new ocp_tempcode();
	}

	/**
	 * Interface to import/export.
	 *
	 * @return tempcode	The interface.
	 */
	function ui()
	{
		$title=get_page_title('XML_DATA_MANAGEMENT');

		require_code('form_templates');

		$import_url=build_url(array('page'=>'_SELF','type'=>'_import'),'_SELF');
		$import_fields=new ocp_tempcode();
		$import_fields->attach(form_input_huge(do_lang_tempcode('XML_DATA'),'','xml','',true));
		$import_form=do_template('FORM',array('TABINDEX'=>strval(get_form_field_tabindex()),'URL'=>$import_url,'HIDDEN'=>'','TEXT'=>do_lang_tempcode('XML_IMPORT_TEXT'),'FIELDS'=>$import_fields,'SUBMIT_NAME'=>do_lang_tempcode('IMPORT')));

		$all_tables=find_all_xml_tables();
		$export_url=build_url(array('page'=>'_SELF','type'=>'_export'),'_SELF');
		$export_fields=new ocp_tempcode();
		$nice_tables=new ocp_tempcode();
		foreach ($all_tables as $table)
		{
			$nice_tables->attach(form_input_list_entry($table));
		}
		$export_fields->attach(form_input_multi_list(do_lang_tempcode('TABLES'),do_lang_tempcode('DESCRIPTION_TABLES'),'tables',$nice_tables,NULL,15));
		$export_fields->attach(form_input_tick(do_lang_tempcode('EXPORT_WITH_COMCODE_XML'),do_lang_tempcode('DESCRIPTION_EXPORT_WITH_COMCODE_XML'),'comcode_xml',false));
		$export_form=do_template('FORM',array('TABINDEX'=>strval(get_form_field_tabindex()),'URL'=>$export_url,'HIDDEN'=>'','TEXT'=>do_lang_tempcode('XML_EXPORT_TEXT'),'FIELDS'=>$export_fields,'SUBMIT_NAME'=>do_lang_tempcode('EXPORT')));

		return do_template('XML_STORAGE_SCREEN',array('TITLE'=>$title,'IMPORT_FORM'=>$import_form,'EXPORT_FORM'=>$export_form));
	}

	/**
	 * Actualiser to do an import.
	 *
	 * @return tempcode	The results.
	 */
	function _import()
	{
		$title=get_page_title('IMPORT');

		$xml=post_param('xml');

		$ops=import_from_xml($xml);

		$ops_nice=array();
		foreach ($ops as $op)
		{
			$ops_nice[]=array('OP'=>$op[0],'PARAM_A'=>$op[1],'PARAM_B'=>array_key_exists(2,$op)?$op[2]:'');
		}

		// Clear some cacheing
		require_code('view_modes');
		require_code('zones2');
		require_code('zones3');
		erase_comcode_page_cache();
		require_code('view_modes');
		erase_tempcode_cache();
		persistant_cache_empty();

		breadcrumb_set_self(do_lang_tempcode('_RESULTS'));
		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('XML_DATA_MANAGEMENT'))));

		return do_template('XML_STORAGE_IMPORT_RESULTS_SCREEN',array('TITLE'=>$title,'OPS'=>$ops_nice));
	}

	/**
	 * Actualiser to do an export.
	 *
	 * @return tempcode	The results.
	 */
	function _export()
	{
		$title=get_page_title('EXPORT');

		if (!array_key_exists('tables',$_POST)) warn_exit(do_lang_tempcode('IMPROPERLY_FILLED_IN'));

		$xml=export_to_xml($_POST['tables'],post_param_integer('comcode_xml',0)==1);

		breadcrumb_set_self(do_lang_tempcode('_RESULTS'));
		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('XML_DATA_MANAGEMENT'))));

		return do_template('XML_STORAGE_EXPORT_RESULTS_SCREEN',array('TITLE'=>$title,'XML'=>$xml));
	}

}


