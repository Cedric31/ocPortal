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

/*
Orders are compound-products. They link together multiple eCommerce items into a single purchasable set with a fixed price.
*/

/**
 * Handling ecommerce orders and dispatch
 *
 * @param  AUTO_LINK	The purchase ID.
 * @param  array		Details relating to the product.
 * @param  ID_TEXT	The product.
 */
function handle_product_orders($purchase_id,$details,$product)
{
	require_code('shopping');

	$GLOBALS['SITE_DB']->query_update('shopping_order_details',array('dispatch_status'=>$details['ORDER_STATUS']),array('order_id'=>$purchase_id));

	$GLOBALS['SITE_DB']->query_update('shopping_order',array('order_status'=>$details['ORDER_STATUS'],'transaction_id'=>$details['txn_id']),array('id'=>$purchase_id));

	purchase_done_staff_mail($purchase_id);

	if($details['ORDER_STATUS']=='ORDER_STATUS_dispatched')
	{	
		update_stock($purchase_id);	//Update stock after dispatch
	}
}

class Hook_cart_orders
{
	/**
	 * Get the products handled by this eCommerce hook.
    *
	 * IMPORTANT NOTE TO PROGRAMMERS: This function may depend only on the database, and not on get_member() or any GET/POST values.
    *  Such dependencies will break IPN, which works via a Guest and no dependable environment variables. It would also break manual transactions from the Admin Zone.
	 *
	 * @param  boolean	Whether to make sure the language for item_name is the site default language (crucial for when we read/go to third-party sales systems and use the item_name as a key).
	 * @param  ?ID_TEXT	Product being searched for (NULL: none).
	 * @param  boolean 	Whether $search refers to the product name rather than the product_id.
	 * @return array		A map of product name to list of product details.
	 */
	function get_products($site_lang=false,$search=NULL,$search_titles_not_ids=false)
	{	
		if (is_null($search)) return array(); // Too many to list potentially

		$products		=	array();

		require_lang('shopping');	

		if (function_exists('set_time_limit')) @set_time_limit(0);

		$where=array('order_status'=>'ORDER_STATUS_awaiting_payment');
		if (!is_null($search))
		{
			if (!$search_titles_not_ids)
			{
				$l=do_lang('CART_ORDER','',NULL,NULL,$site_lang?get_site_default_lang():user_lang());
				if (substr($search,0,strlen($l))!=$l) return array();
				$where['id']=intval(substr($search,strlen($l)));
			}
		}

		$start=0;
		do
		{
			$orders		=	$GLOBALS['SITE_DB']->query_select('shopping_order',array('id','tot_price'),$where,'',500);

			foreach($orders as $order)
			{			
				$products[do_lang('CART_ORDER',strval($order['id']),NULL,NULL,$site_lang?get_site_default_lang():user_lang())]	=	array(PRODUCT_ORDERS,$order['tot_price'],'handle_product_orders',array(),do_lang('CART_ORDER',strval($order['id']),NULL,NULL,$site_lang?get_site_default_lang():user_lang()));
			}
			
			$start+=500;
		}
		while (count($orders)==500);

		return $products;
	}

	/**
	 * Function to return dispatch type of product. (this hook represents a cart order, so find all of it's sub products's dispatch type and decide cart order product's dispatch type - automatic or manual
	 *	 
	 * @param  SHORT_TEXT	Item ID
	 * @return SHORT_TEXT 	Dispatch type
	*/
	function get_product_dispatch_type($order_id)
	{		
		$row		=	$GLOBALS['SITE_DB']->query_select('shopping_order_details',array('*'),array('order_id'=>$order_id));

		foreach($row as $item)
		{
			if(is_null($item['p_type']))	continue;

			require_code('hooks/systems/ecommerce/'.filter_naughty_harsh($item['p_type']));
			
			$object		=	object_factory('Hook_'.filter_naughty_harsh($item['p_type']));

			//if any of the product's dispatch type is manual, return type as 'manual'
			if($object->get_product_dispatch_type()=='manual')
				return 'manual';
		}

		//if none of product items have manual dispatch, return order dispatch as automatic.
		return 'automatic';	
	}

	/**
	 * Function to return order id from formated of order id
	 *
	 * @param  SHORT_TEXT	item id
	 * @return SHORT_TEXT 	dispatch type
	*/
	function set_needed_fields($item_name)
	{
		return str_replace('#','',$item_name);
	}
}


