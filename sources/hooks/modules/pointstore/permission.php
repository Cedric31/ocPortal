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
 * @package		pointstore
 */

class Hook_pointstore_permission
{

	/**
	 * Standard pointstore item initialisation function.
	 */
	function init()
	{
		$GLOBALS['SITE_DB']->query('DELETE FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'msp WHERE active_until<'.strval(time()));
		$GLOBALS['SITE_DB']->query('DELETE FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'member_category_access WHERE active_until<'.strval(time()));
		$GLOBALS['SITE_DB']->query('DELETE FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'member_page_access WHERE active_until<'.strval(time()));
		$GLOBALS['SITE_DB']->query('DELETE FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'member_zone_access WHERE active_until<'.strval(time()));
	}

	/**
	 * Get fields for adding/editing one of these.
	 *
	 * @param  string			What to place onto the end of the field name
	 * @param  SHORT_TEXT	Title
	 * @param  LONG_TEXT		Description
	 * @param  BINARY			Whether it is enabled
	 * @param  ?integer		The cost in points (NULL: not set)
	 * @param  integer		Number of hours for it to last for
	 * @param  ID_TEXT		Permission scope 'type'
	 * @param  ID_TEXT		Permission scope 'specific_permission'
	 * @param  ID_TEXT		Permission scope 'zone'
	 * @param  ID_TEXT		Permission scope 'page'
	 * @param  ID_TEXT		Permission scope 'module'
	 * @param  ID_TEXT		Permission scope 'category'
	 * @return tempcode		The fields
	 */
	function get_fields($name_suffix='',$title='',$description='',$enabled=1,$cost=NULL,$hours=24,$type='msp',$specific_permission='',$zone='',$page='',$module='',$category='')
	{
		require_lang('points');
		$fields=new ocp_tempcode();
		$fields->attach(form_input_line(do_lang_tempcode('TITLE'),do_lang_tempcode('DESCRIPTION_TITLE'),'permission_title'.$name_suffix,$title,true));
		$fields->attach(form_input_text(do_lang_tempcode('DESCRIPTION'),do_lang_tempcode('DESCRIPTION_DESCRIPTION'),'permission_description'.$name_suffix,$description,true));
		$fields->attach(form_input_integer(do_lang_tempcode('COST'),do_lang_tempcode('HOW_MUCH_THIS_COSTS'),'permission_cost'.$name_suffix,$cost,true));
		$fields->attach(form_input_integer(do_lang_tempcode('PERMISSION_HOURS'),do_lang_tempcode('DESCRIPTION_PERMISSION_HOURS'),'permission_hours'.$name_suffix,$hours,true));
		$types=new ocp_tempcode();
		$_types=array('msp','member_zone_access','member_page_access','member_category_access');
		foreach ($_types as $_type)
		{
			$types->attach(form_input_list_entry($_type,$type==$_type,do_lang_tempcode('PERM_TYPE_'.$_type)));
		}
		$fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_type'),do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_type'),'permission_type'.$name_suffix,$types));
		require_all_lang();
		$specific_permissions=new ocp_tempcode();
		$temp=form_input_list_entry('',false,do_lang_tempcode('NA_EM'));
		$specific_permissions->attach($temp);
		$_specific_permissions=$GLOBALS['SITE_DB']->query_select('sp_list',array('*'),NULL,'ORDER BY p_section,the_name');
		foreach ($_specific_permissions as $sp)
		{
			$pt_name=do_lang_tempcode('PT_'.$sp['the_name']);
			$_pt_name=do_lang('PT_'.$sp['the_name'],NULL,NULL,NULL,NULL,false);
			if (is_null($_pt_name)) continue;
			$temp=form_input_list_entry($sp['the_name'],$sp['the_name']==$specific_permission,$pt_name);
			$specific_permissions->attach($temp);
		}
		$fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_specific_permission'),do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_specific_permission'),'permission_specific_permission'.$name_suffix,$specific_permissions,NULL,false,false));
		$zones=new ocp_tempcode();
		$zones->attach(form_input_list_entry('',false,do_lang_tempcode('NA_EM')));
		require_code('zones2');
		require_code('zones3');
		$zones->attach(nice_get_zones($zone));
		$fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_zone'),do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_zone'),'permission_zone'.$name_suffix,$zones,NULL,false,false));
		$pages=new ocp_tempcode();
		$temp=form_input_list_entry('',false,do_lang_tempcode('NA_EM'));
		$pages->attach($temp);
		$_zones=find_all_zones();
		$_pages=array();
		foreach ($_zones as $_zone)
		{
			$_pages+=find_all_pages_wrap($_zone);
		}
		foreach (array_keys($_pages) as $_page)
		{
			if (is_integer($_page)) $_page=strval($_page); // PHP array combining weirdness
			$temp=form_input_list_entry($_page,$page==$_page);
			$pages->attach($temp);
		}
		$fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_page'),do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_page'),'permission_page'.$name_suffix,$pages,NULL,false,false));
		$modules=new ocp_tempcode();
		$temp=form_input_list_entry('',false,do_lang_tempcode('NA_EM'));
		$modules->attach($temp);
		$_modules=find_all_hooks('systems','module_permissions');
		foreach (array_keys($_modules) as $_module)
		{
			$temp=form_input_list_entry($_module,$_module==$module);
			$modules->attach($temp);
		}
		$fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_module'),do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_module'),'permission_module'.$name_suffix,$modules,NULL,false,false));
		$fields->attach(form_input_line(do_lang_tempcode('PERMISSION_SCOPE_category'),do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_category'),'permission_category'.$name_suffix,$category,false));
		$fields->attach(form_input_tick(do_lang_tempcode('ENABLED'),'','permission_enabled'.$name_suffix,$enabled==1));
		return $fields;
	}

	/**
	 * Standard pointstore item configuration function.
	 *
	 * @return ?array		A tuple: list of [fields to shown, hidden fields], title for add form, add form (NULL: disabled)
	 */
	function config()
	{
		$fields=new ocp_tempcode();
		$rows=$GLOBALS['SITE_DB']->query_select('pstore_permissions',array('*'),NULL,'ORDER BY id');
		$hidden=new ocp_tempcode();
		$out=array();
		foreach ($rows as $i=>$row)
		{
			$fields=new ocp_tempcode();
			$hidden=new ocp_tempcode();
			$fields->attach($this->get_fields('_'.strval($i),get_translated_text($row['p_title']),get_translated_text($row['p_description']),$row['p_enabled'],$row['p_cost'],$row['p_hours'],$row['p_type'],$row['p_specific_permission'],$row['p_zone'],$row['p_page'],$row['p_module'],$row['p_category']));
			$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('TITLE'=>do_lang_tempcode('ACTIONS'))));
			$fields->attach(form_input_tick(do_lang_tempcode('DELETE'),do_lang_tempcode('DESCRIPTION_DELETE'),'delete_permission_'.strval($i),false));
			$hidden->attach(form_input_hidden('permission_'.strval($i),strval($row['id'])));
			$out[]=array($fields,$hidden,do_lang_tempcode('EDIT_PERMISSION_PRODUCT'));
		}

		return array($out,do_lang_tempcode('ADD_NEW_PERMISSION_PRODUCT'),$this->get_fields());
	}

	/**
	 * Standard pointstore item configuration save function.
	 */
	function save_config()
	{
		$i=0;
		$rows=list_to_map('id',$GLOBALS['SITE_DB']->query_select('pstore_permissions',array('*')));
		while (array_key_exists('permission_'.strval($i),$_POST))
		{
			$id=post_param_integer('permission_'.strval($i));
			$title=post_param('permission_title_'.strval($i));
			$description=post_param('permission_description_'.strval($i));
			$enabled=post_param_integer('permission_enabled_'.strval($i),0);
			$cost=post_param_integer('permission_cost_'.strval($i));
			$delete=post_param_integer('delete_permission_'.strval($i),0);
			$hours=post_param_integer('permission_hours_'.strval($i));
			$type=post_param('permission_type_'.strval($i));
			$specific_permission=post_param('permission_specific_permission_'.strval($i));
			$zone=post_param('permission_zone_'.strval($i));
			$page=post_param('permission_page_'.strval($i));
			$module=post_param('permission_module_'.strval($i));
			$category=post_param('permission_category_'.strval($i));
			$_title=$rows[$id]['p_title'];
			$_description=$rows[$id]['p_description'];
			if ($delete==1)
			{
				delete_lang($_title);
				delete_lang($_description);
				$GLOBALS['SITE_DB']->query_delete('pstore_permissions',array('id'=>$id),'',1);
			} else
			{
				$GLOBALS['SITE_DB']->query_update('pstore_permissions',array(
					'p_title'=>lang_remap($_title,$title),
					'p_description'=>lang_remap($_description,$description),
					'p_enabled'=>$enabled,
					'p_cost'=>$cost,
					'p_hours'=>$hours,
					'p_type'=>$type,
					'p_specific_permission'=>$specific_permission,
					'p_zone'=>$zone,
					'p_page'=>$page,
					'p_module'=>$module,
					'p_category'=>$category,
				),array('id'=>$id),'',1);
			}
			$i++;
		}
		$title=post_param('permission_title',NULL);
		if (!is_null($title))
		{
			$description=post_param('permission_description');
			$enabled=post_param_integer('permission_enabled',0);
			$cost=post_param_integer('permission_cost');
			$hours=post_param_integer('permission_hours');
			$type=post_param('permission_type');
			$specific_permission=post_param('permission_specific_permission');
			$zone=post_param('permission_zone');
			$page=post_param('permission_page');
			$module=post_param('permission_module');
			$category=post_param('permission_category');

			$GLOBALS['SITE_DB']->query_insert('pstore_permissions',array(
				'p_title'=>insert_lang($title,2),
				'p_description'=>insert_lang($description,2),
				'p_enabled'=>$enabled,
				'p_cost'=>$cost,
				'p_hours'=>$hours,
				'p_type'=>$type,
				'p_specific_permission'=>$specific_permission,
				'p_zone'=>$zone,
				'p_page'=>$page,
				'p_module'=>$module,
				'p_category'=>$category,
			));
		}
	}

	/**
	 * Standard pointstore item initialisation function.
	 *
	 * @return array			The "shop fronts"
	 */
	function info()
	{
		if (file_exists(get_file_base().'/mysql_old')) return array();

		$class=str_replace('hook_pointstore_','',strtolower(get_class($this)));

		$items=array();
		$rows=$GLOBALS['SITE_DB']->query_select('pstore_permissions',array('*'),array('p_enabled'=>1));
		foreach ($rows as $row)
		{
			if ($this->bought($row)) continue;

			$next_url=build_url(array('page'=>'_SELF','type'=>'action','id'=>$class,'sub_id'=>$row['id']),'_SELF');
			$items[]=do_template('POINTSTORE_'.strtoupper($class),array('NEXT_URL'=>$next_url,'TITLE'=>get_translated_text($row['p_title']),'DESCRIPTION'=>get_translated_tempcode($row['p_description'])));
		}
		return $items;
	}

	/**
	 * Standard interface stage of pointstore item purchase.
	 *
	 * @return tempcode		The UI
	 */
	function action()
	{
		$class=str_replace('hook_pointstore_','',strtolower(get_class($this)));

		$id=get_param_integer('sub_id');
		$rows=$GLOBALS['SITE_DB']->query_select('pstore_permissions',array('p_title','p_cost'),array('id'=>$id,'p_enabled'=>1));
		if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));

		$p_title=get_translated_text($rows[0]['p_title']);
		$title=get_page_title('PURCHASE_SOME_PRODUCT',true,array($p_title));

		$cost=$rows[0]['p_cost'];
		$next_url=build_url(array('page'=>'_SELF','type'=>'action_done','id'=>$class,'sub_id'=>$id),'_SELF');
		$points_left=available_points(get_member());

		// Check points
		if (($points_left<$cost) && (!has_specific_permission(get_member(),'give_points_self')))
		{
			return warn_screen($title,do_lang_tempcode('_CANT_AFFORD',integer_format($cost),integer_format($points_left)));
		}

		return do_template('POINTSTORE_CUSTOM_ITEM_SCREEN',array('_GUID'=>'879bd8389dcd6b4b8e0ec610d76bcb35','TITLE'=>$title,'COST'=>integer_format($cost),'REMAINING'=>integer_format($points_left-$cost),'NEXT_URL'=>$next_url));
	}

	/**
	 * Standard actualisation stage of pointstore item purchase.
	 *
	 * @return tempcode		The UI
	 */
	function action_done()
	{
		$class=str_replace('hook_pointstore_','',strtolower(get_class($this)));

		post_param_integer('confirm'); // Make sure POSTed
		$id=get_param_integer('sub_id');
		$rows=$GLOBALS['SITE_DB']->query_select('pstore_permissions',array('*'),array('id'=>$id,'p_enabled'=>1));
		if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));

		$cost=$rows[0]['p_cost'];

		$p_title=get_translated_text($rows[0]['p_title']);
		$title=get_page_title('PURCHASE_SOME_PRODUCT',true,array($p_title));

		// Check points
		$points_left=available_points(get_member());
		if (($points_left<$cost) && (!has_specific_permission(get_member(),'give_points_self')))
		{
			return warn_screen($title,do_lang_tempcode('_CANT_AFFORD',integer_format($cost),integer_format($points_left)));
		}

		// Test to see if it's been bought
		if ($this->bought($rows[0]))
			warn_exit(do_lang_tempcode('_ALREADY_HAVE'));

		require_code('points2');
		charge_member(get_member(),$cost,$p_title);
		$GLOBALS['SITE_DB']->query_insert('sales',array('date_and_time'=>time(),'memberid'=>get_member(),'purchasetype'=>'PURCHASE_PERMISSION_PRODUCT','details'=>$p_title,'details2'=>strval($rows[0]['id'])));

		// Actuate
		$map=$this->get_map($rows[0]);
		$map['active_until']=time()+$rows[0]['p_hours']*60*60;
		$GLOBALS['SITE_DB']->query_insert(filter_naughty_harsh($rows[0]['p_type']),$map);

		// Show message
		$url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('ORDER_GENERAL_DONE'));
	}

	/**
	 * Get a database map for our permission row.
	 *
	 * @param  array			Map row of item
	 * @return array			Permission map row
	 */
	function get_map($row)
	{
		$map=array('member_id'=>get_member());
		switch ($row['p_type'])
		{
			case 'msp':
				$map['specific_permission']=$row['p_specific_permission'];
				$map['the_page']=$row['p_page'];
				$map['module_the_name']=$row['p_module'];
				$map['category_name']=$row['p_category'];
				$map['the_value']='1';
				break;
			case 'member_category_access':
				$map['module_the_name']=$row['p_module'];
				$map['category_name']=$row['p_category'];
				break;
			case 'member_page_access':
				$map['zone_name']=$row['p_zone'];
				$map['page_name']=$row['p_page'];
				break;
			case 'member_zone_access':
				$map['zone_name']=$row['p_zone'];
				break;
		}
		return $map;
	}

	/**
	 * Standard actualisation stage of pointstore item purchase.
	 *
	 * @param  array			Map row
	 * @return boolean		Whether the current member has bought it already
	 */
	function bought($row)
	{
		$map=$this->get_map($row);
		$test=$GLOBALS['SITE_DB']->query_value_null_ok(filter_naughty_harsh($row['p_type']),'member_id',$map);
		return (!is_null($test));
	}

}


