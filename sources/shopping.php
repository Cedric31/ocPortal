<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license	http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package	shopping
 */

/**
 * Get product details array, according to the hook specified in the 'hook' GET parameter
 *
 * @return array	Product details
*/
function get_product_details()
{
	$products	=	array();

	$_hook		=	get_param('hook');
	
	require_code('hooks/systems/ecommerce/'.filter_naughty_harsh($_hook));

	$object=object_factory('Hook_'.filter_naughty_harsh($_hook));

	$products	=	$object->get_product_details();	
	
	return $products;
}

/**
 * Function to add new item to cart.
 *
 * @param  array	Product details	
*/
function add_to_cart($product_det)
{
	$_hook		=	get_param('hook');
	
	require_code('hooks/systems/ecommerce/'.filter_naughty_harsh($_hook));

	$object		=	object_factory('Hook_'.filter_naughty_harsh($_hook));

	$object->add_order($product_det);	
}

/**
 * Update cart
 *
 * @param  array	Product details
*/
function update_cart($product_det)
{
	if (!is_array($product_det) || count($product_det)==0) return;

	foreach($product_det as $product)
	{
		if($product['Quantity']>0)
		{	
			$GLOBALS['SITE_DB']->query_update('shopping_cart',array('quantity'	=>	($product['Quantity'])),
				array('product_id'=>$product['product_id'],'session_id'=>get_session_id(),'is_deleted'=>0)
			);
		}
		else
		{
			$GLOBALS['SITE_DB']->query_delete('shopping_cart',
				array('product_id'=>$product['product_id'],'session_id'=>get_session_id(),'is_deleted'=>0)
			);
		}
	}

	//Update tax opt out status to the current order
	$tax_opted_out	=	post_param_integer('tax_opted_out',0);	

	$order_id		=	get_current_order_id();

	if(get_option('allow_opting_out_of_tax')=='1')
	{
		$GLOBALS['SITE_DB']->query_update('shopping_order',array('tax_opted_out'=>$tax_opted_out),array('id'=>$order_id),'',1);
	}	
}

/**
 * Remove from cart.
 *
 * @param  array	Products to remove 
*/
function remove_from_cart($product_to_remove)
{	
	if (!is_array($product_to_remove) || count($product_to_remove)==0) return;

	foreach($product_to_remove as $product_id)
	{
		$GLOBALS['SITE_DB']->query_update('shopping_cart',array('is_deleted'=>1),
				array('product_id'=>$product_id,'session_id'=>get_session_id())
		);
	}
}

/**
 * Log cart actions
 *
 * @param  ID_TEXT	The data
*/
function log_cart_actions($action)
{
	$id	=	$GLOBALS['SITE_DB']->query_value_null_ok('shopping_logging','id',array('e_member_id'=>get_member(),'session_id'=>get_session_id()));

	if(is_null($id))
	{
		$GLOBALS['SITE_DB']->query_insert('shopping_logging',array(
						'e_member_id'	=>	get_member(),
						'session_id'	=>	get_session_id(),
						'ip'		=>	get_ip_address(),
						'last_action'	=>	$action,
						'date_and_time'	=>	time()
						)
					);
	}
	else
	{
		$GLOBALS['SITE_DB']->query_update('shopping_logging',array(
						'last_action'	=>	$action,				
						'date_and_time'	=>	time()
						)
					);
	}
}

/**
 *	Delete incomplete orders of current session of logged in user.
 * 
*/
function delete_incomplete_orders()
{
	$GLOBALS['SITE_DB']->query("DELETE t1,t2 FROM ".get_table_prefix()."shopping_order t1, ".get_table_prefix()."shopping_order_details t2 WHERE t1.id=t2.order_id AND t1.order_status='ORDER_STATUS_awaiting_payment' AND t1.c_member=".strval(get_member())." AND session_id=".strval(get_session_id()));
}

/**
 * Show cart image
 *
 * @return tempcode 
*/
function show_cart_image()
{
	$cart_url	=	build_url(array('page'=>'shopping','type'=>'misc'),get_module_zone('shopping'));

	$item_count	=	$GLOBALS['SITE_DB']->query_value_null_ok('shopping_cart','count(id)',array('session_id'=>get_session_id(),'is_deleted'=>0));

	if($item_count>0)
		$title	=	do_lang_tempcode('CART_ITEMS',strval($item_count));
	else
		$title	=	do_lang_tempcode('CART_EMPTY');

	return do_template('CART_LOGO',array('URL'=>$cart_url,'ITEMS'=>strval($item_count),'TITLE'=>$title));
}

/**
 * Tell the staff the shopping order was placed
 *
 * @param  AUTO_LINK		Order Id
*/
function purchase_done_staff_mail($order_id)
{
	$member_id=$GLOBALS['SITE_DB']->query_value('shopping_order','c_member_id',array('id'=>$order_id));
	$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id);
	$subject=do_lang('ORDER_PLACED_MAIL_SUBJECT',get_site_name(),strval($order_id),get_site_default_lang());
	$message=do_lang('ORDER_PLACED_MAIL_MESSAGE',comcode_escape(get_site_name()),comcode_escape($username),array(strval($order_id)),get_site_default_lang());
	require_code('notifications');
	dispatch_notification('new_order',NULL,$subject,$message);
}

/**
 * Find products in cart
 *
 * @return array	Product details in cart
*/
function find_products_in_cart()
{
	$cart	=	$GLOBALS['SITE_DB']->query_select('shopping_cart',array('*'),array('ordered_by'=>get_member(),'session_id'=>get_session_id(),'is_deleted'=>0));

	if(!array_key_exists(0,$cart)) return array();

	return $cart;
}

/**
 * Stock maintain warning mail
 *
 * @param  SHORT_TEXT	product name
 * @param  AUTO_LINK		Product id
*/
function stock_maintain_warn_mail($product_name,$product_id)
{
	$product_info_url	=	build_url(array('page'=>'catalogues','type'=>'entry','id'=>$product_id),get_module_zone('catalogues'));

	$subject=do_lang('STOCK_LEVEL_MAIL_SUBJECT',get_site_name(),$product_name,NULL,get_site_default_lang());
	$message=do_lang('STOCK_MAINTENANCE_WARN_MAIL',comcode_escape(get_site_name()),comcode_escape($product_name),array($product_info_url->evaluate()),get_site_default_lang());

	require_code('notifications');
	dispatch_notification('low_stock',NULL,$subject,$message,NULL,NULL,A_FROM_SYSTEM_PRIVILEGED);
}	

/**
 * Stock reduction
 *
 * @param  AUTO_LINK		The ID
*/
function update_stock($order_id)
{
	$row	=	$GLOBALS['SITE_DB']->query_select('shopping_order_details',array('*'),array('order_id'=>$order_id),'',1);
	
	foreach($row as $ordered_items)
	{ 
		$hook	=	$ordered_items['p_type'];	

		require_code('hooks/systems/ecommerce/'.filter_naughty_harsh($hook));

		$object=object_factory('Hook_'.$hook);

		$object->update_stock($ordered_items['p_id'],$ordered_items['p_quantity']);	
	}
}

/**
* Payment step.
* 
* @return tempcode	The result of execution.
*/
function payment_form()
{
	require_code('ecommerce');

	$title			=	get_page_title('PAYMENT_HEADING');

	$cart_items		=	array();

	$cart_items		=	find_products_in_cart();
	
	$purchase_id	=	NULL;

	$tax_opt_out	=	get_order_tax_opt_out_status();

	delete_incomplete_orders();

	if(count($cart_items)>0)
	{
		if(is_null($GLOBALS['SITE_DB']->query_value_null_ok('shopping_order','id')))
		{
			$insert		=	array(
							'id'			=>	hexdec('1701D'),
							'c_member'		=>	get_member(),
							'session_id'		=>	get_session_id(),
							'add_date'		=>	time(),
							'tot_price'		=>	0,
							'order_status'		=>	'ORDER_STATUS_awaiting_payment',
							'notes'			=>	'',
							'purchase_through'	=>	'cart',
							'transaction_id' => '',
							'tax_opted_out'=>$tax_opt_out,
						);	
		}
		else
		{
			$insert		=	array(
							'c_member'		=>	get_member(),
							'session_id'		=>	get_session_id(),
							'add_date'		=>	time(),
							'tot_price'		=>	0,
							'order_status'		=>	'ORDER_STATUS_awaiting_payment',
							'notes'			=>	'',
							'purchase_through'	=>	'cart',
							'transaction_id' => '',
							'tax_opted_out'=>$tax_opt_out,
						);
		}

		$order_id	=	$GLOBALS['SITE_DB']->query_insert('shopping_order',$insert,true);
	} else
	{
		$order_id = NULL;
	}

	$total_price		=	0;	
	
	foreach($cart_items as $item)
	{	
		$product	=	$item['product_id'];

		$hook		=	$item['product_type'];

		require_code('hooks/systems/ecommerce/'.filter_naughty_harsh($hook));
		
		$object		=	object_factory('Hook_'.filter_naughty_harsh($hook));
	
		$temp		=	$object->get_products(false,$product);

		if ($temp[$product][0]==PRODUCT_SUBSCRIPTION) continue;	//Subscription type skipped.

		$price		=	$temp[$product][1];

		$item_name	=	$temp[$product][4];				
	
		if (method_exists($object,'set_needed_fields'))
			$purchase_id	=	$object->set_needed_fields($product);
		else
			$purchase_id	=	strval(get_member());

		$length			=	NULL;

		$length_units	=	'';

		if(method_exists($object,'calculate_product_price'))
			$price	=	$object->calculate_product_price($item['price'],$item['price_pre_tax'],$item['product_weight']);
		else
			$price	=	$item['price'];

		if (method_exists($object,'calculate_tax') && ($tax_opt_out==0))
			$tax	=	round($object->calculate_tax($item['price'],$item['price_pre_tax']),2);
		else
			$tax	=	0.0;

		$GLOBALS['SITE_DB']->query_insert('shopping_order_details',array(
				'p_id'				=>		$item['product_id'],
				'p_name'				=>		$item['product_name'],
				'p_code'				=>		$item['product_code'],
				'p_type'				=>		$item['product_type'],
				'p_quantity'		=>		$item['quantity'],
				'p_price'			=>		$price,
				'included_tax'		=>		$tax,
				'order_id'			=>		$order_id,
				'dispatch_status' => 	'',
			),true);
		
		$total_price	+=	$price*$item['quantity'];
	}

	$GLOBALS['SITE_DB']->query_update('shopping_order',array('tot_price'=>$total_price),array('id'=>$order_id),'',1);
	
	
	if (!perform_local_payment()) // Pass through to the gateway's HTTP server
	{
		$result	=	make_cart_payment_button($order_id,get_option('currency'));

	} else // Handle the transaction internally
	{
		if (((ocp_srv('HTTPS')=='') || (ocp_srv('HTTPS')=='off')) && (!ecommerce_test_mode()))
		{
			warn_exit(do_lang_tempcode('NO_SSL_SETUP'));
		}

		$fields			=	is_null($order_id)?new ocp_tempcode():get_transaction_form_fields(NULL,$order_id,$item_name,float_to_raw_string($price),NULL,'');

		/*$via				=	get_option('payment_gateway');
		require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));
		$object	=	object_factory('Hook_'.$via);
		$ipn_url	=	$object->get_ipn_url();*/
		$finish_url=build_url(array('page'=>'purchase','type'=>'finish'),get_module_zone('purchase'));

		$result	=	do_template('PURCHASE_WIZARD_STAGE_TRANSACT',array('FIELDS'=>$fields));

		require_javascript('javascript_validation');

		return do_template('PURCHASE_WIZARD_SCREEN',array('TITLE'=>$title,'CONTENT'=>$result,'URL'=>$finish_url));
	}

	return $result;
}

/**
* Find current order tax opt out status
*
* @return  BINARY		Tax opt out status of current order
*/
function get_order_tax_opt_out_status()
{
	if(get_param('page','')=='purchase') return 0;	//Purchase module creates separate orders for every product, so optout status depending only on current value of checkbox.

	$row	=	$GLOBALS['SITE_DB']->query_select('shopping_order',array('tax_opted_out'),array('c_member'=>get_member(),'session_id'=>get_session_id()),'ORDER BY add_date DESC',1);

	if(!array_key_exists(0,$row)) 
		return 0;
	else
		return $row[0]['tax_opted_out'];
}

/**
* Find current order id
*
* @return  AUTO_LINK		Order id
*/
function get_current_order_id()
{	
	$row	=	$GLOBALS['SITE_DB']->query_select('shopping_order',array('id'),array('c_member'=>get_member(),'session_id'=>get_session_id()),'ORDER BY add_date DESC',1);

	if(!array_key_exists(0,$row)) 
		return 0;
	else
		return $row[0]['id'];
}

/**
* Return list entry of common order statuses of orders
*
* @return  tempcode		Order status list entries
*/
function get_order_status_list()
{
	$status_list=new ocp_tempcode();
	$status		=	array(
								'ORDER_STATUS_awaiting_payment'	=>	do_lang_tempcode('ORDER_STATUS_awaiting_payment'),	
								'ORDER_STATUS_payment_received'	=>	do_lang_tempcode('ORDER_STATUS_payment_received'),
								'ORDER_STATUS_dispatched'			=>	do_lang_tempcode('ORDER_STATUS_dispatched'),
								'ORDER_STATUS_onhold'				=>	do_lang_tempcode('ORDER_STATUS_onhold'),
								'ORDER_STATUS_cancelled'			=>	do_lang_tempcode('ORDER_STATUS_cancelled'),
								'ORDER_STATUS_returned'				=>	do_lang_tempcode('ORDER_STATUS_returned'),
						);

	$status_list->attach(form_input_list_entry('all',false,do_lang_tempcode('NA')));
	
	foreach ($status as $key=>$values)
	{
		$status_list->attach(form_input_list_entry($key,false,$values));
	}
	return $status_list;
}

/**
* Return a string of order products to export as csv
*
* @param  AUTO_LINK	Order Id
* @return LONG_TEXT	Products names and quantity
*/
function get_ordered_product_list_string($order_id)
{
	$product_det	=	array();

	$row	=	$GLOBALS['SITE_DB']->query_select('shopping_order_details',array('*'),array('order_id'=>$order_id));
	
	foreach($row as $key=>$product)
	{
		$product_det[]	=	$product['p_name']." x ".integer_format($product['p_quantity'])." @ ".do_lang('UNIT_PRICE')."=".float_format($product['p_price']);
	}
	
	return implode(chr(10),$product_det);
}
