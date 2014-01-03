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
 * @package		ecommerce
 */

/**
 * Standard code module initialisation function.
 */
function init__ecommerce()
{
	if (!defined('PRODUCT_PURCHASE_WIZARD'))
	{
		define('PRODUCT_PURCHASE_WIZARD',0);
		define('PRODUCT_INVOICE',1);
		define('PRODUCT_SUBSCRIPTION',2);
		define('PRODUCT_OTHER',3);
		define('PRODUCT_CATALOGUE',4);
		define('PRODUCT_ORDERS',5);

		define('ECOMMERCE_PRODUCT_AVAILABLE',0);
		define('ECOMMERCE_PRODUCT_NO_GUESTS',1); // Only used if current user really is a Guest
		define('ECOMMERCE_PRODUCT_ALREADY_HAS',2);
		define('ECOMMERCE_PRODUCT_DISABLED',3);
		define('ECOMMERCE_PRODUCT_PROHIBITED',4);
		define('ECOMMERCE_PRODUCT_OUT_OF_STOCK',5);
		define('ECOMMERCE_PRODUCT_MISSING',6);
		define('ECOMMERCE_PRODUCT_INTERNAL_ERROR',7);
	}

	require_lang('ecommerce');
}

/**
 * Check whether the system is in test mode (normally, not).
 *
 * @return boolean	The answer.
 */
function ecommerce_test_mode()
{
	return get_option('ecommerce_test_mode')=='1';
}

/**
 * Get the symbol of the currency we're trading in.
 *
 * @return ID_TEXT	The currency.
 */
function ecommerce_get_currency_symbol()
{
	$currency=get_option('currency');
	switch ($currency)
	{
		case 'USD':
			$currency='$';
			break;
		case 'CAD':
			$currency='$';
			break;
		case 'EUR':
			$currency='&euro;';
			break;
		case 'GBP':
			$currency='&pound;';
			break;
		case 'JPY':
			$currency='&yen;';
			break;
		case 'AUD':
			$currency='$';
			break;
	}
	if ($GLOBALS['XSS_DETECT']) ocp_mark_as_escaped($currency);
	return $currency;
}

/**
 * Find a transaction fee from a transaction amount. Regular fees aren't taken into account.
 *
 * @param  ?ID_TEXT		The transaction ID (NULL: auto-generate)
 * @param  ID_TEXT		The purchase ID
 * @param  SHORT_TEXT	The item name
 * @param  SHORT_TEXT	The amount
 * @param  ?integer		The length (NULL: not a subscription)
 * @param  ID_TEXT		The length units
 * @return tempcode		The form fields
 */
function get_transaction_form_fields($trans_id,$purchase_id,$item_name,$amount,$length,$length_units)
{
	if (is_null($trans_id))
	{	
		$via=get_option('payment_gateway');
		require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));
		$object=object_factory('Hook_'.$via);
		if (!method_exists($object,'do_transaction')) warn_exit(do_lang_tempcode('LOCAL_PAYMENT_NOT_SUPPORTED',escape_html($via)));
		$trans_id=$object->generate_trans_id();
	}

	$GLOBALS['SITE_DB']->query_insert('trans_expecting',array(
		'id'=>$trans_id,
		'e_purchase_id'=>$purchase_id,
		'e_item_name'=>$item_name,
		'e_amount'=>$amount,
		'e_member_id'=>get_member(),
		'e_ip_address'=>get_ip_address(),
		'e_session_id'=>get_session_id(),
		'e_time'=>time(),
		'e_length'=>$length,
		'e_length_units'=>$length_units,
	));

	require_code('form_templates');
	$fields=new ocp_tempcode();
	$fields->attach(form_input_hidden('trans_id',$trans_id));
	$fields->attach(form_input_line(do_lang_tempcode('CARDHOLDER_NAME'),do_lang_tempcode('DESCRIPTION_CARDHOLDER_NAME'),'name',ecommerce_test_mode()?$GLOBALS['FORUM_DRIVER']->get_username(get_member()):get_ocp_cpf('payment_cardholder_name'),true));
	$fields->attach(form_input_list(do_lang_tempcode('CARD_TYPE'),'','card_type',$object->nice_get_card_types(ecommerce_test_mode()?'Visa':get_ocp_cpf('payment_type'))));
	$fields->attach(form_input_line(do_lang_tempcode('CARD_NUMBER'),do_lang_tempcode('DESCRIPTION_CARD_NUMBER'),'card_number',ecommerce_test_mode()?'4444333322221111':get_ocp_cpf('payment_card_number'),true));
	$fields->attach(form_input_line(do_lang_tempcode('CARD_START_DATE'),do_lang_tempcode('DESCRIPTION_CARD_START_DATE'),'start_date',ecommerce_test_mode()?date('m/y',utctime_to_usertime(time()-60*60*24*365)):get_ocp_cpf('payment_card_start_date'),true));
	$fields->attach(form_input_line(do_lang_tempcode('CARD_EXPIRY_DATE'),do_lang_tempcode('DESCRIPTION_CARD_EXPIRY_DATE'),'expiry_date',ecommerce_test_mode()?date('m/y',utctime_to_usertime(time()+60*60*24*365)):get_ocp_cpf('payment_card_expiry_date'),true));
	$fields->attach(form_input_integer(do_lang_tempcode('CARD_ISSUE_NUMBER'),do_lang_tempcode('DESCRIPTION_CARD_ISSUE_NUMBER'),'issue_number',intval(get_ocp_cpf('payment_card_issue_number')),false));
	$fields->attach(form_input_line(do_lang_tempcode('CARD_CV2'),do_lang_tempcode('DESCRIPTION_CARD_CV2'),'cv2',ecommerce_test_mode()?'123':get_ocp_cpf('payment_card_cv2'),true));

	//Shipping address fields
	$fields->attach(form_input_line(do_lang_tempcode('SPECIAL_CPF__ocp_firstname'),'','first_name',get_ocp_cpf('firstname'),true));
	$fields->attach(form_input_line(do_lang_tempcode('SPECIAL_CPF__ocp_lastname'),'','last_name',get_ocp_cpf('last_name'),true));
	$fields->attach(form_input_line(do_lang_tempcode('SPECIAL_CPF__ocp_building_name_or_number'),'','address1',get_ocp_cpf('building_name_or_number'),true));
	$fields->attach(form_input_line(do_lang_tempcode('SPECIAL_CPF__ocp_city'),'','city',get_ocp_cpf('city'),true));
	$fields->attach(form_input_line(do_lang_tempcode('SPECIAL_CPF__ocp_state'),'','zip',get_ocp_cpf('state'),true));
	$fields->attach(form_input_line(do_lang_tempcode('SPECIAL_CPF__ocp_post_code'),'','zip',get_ocp_cpf('post_code'),true));
	$fields->attach(form_input_line(do_lang_tempcode('SPECIAL_CPF__ocp_country'),'','country',get_ocp_cpf('country'),true));

	//Set purchase id as hidden form field to get back after transaction
	$fields->attach(form_input_hidden('customfld1',$purchase_id));

	return $fields;
}

/**
 * Find a transaction fee from a transaction amount. Regular fees aren't taken into account.
 *
 * @param  float		A transaction amount.
 * @param  ID_TEXT	The service the payment went via.
 * @return float		The fee
 */
function get_transaction_fee($amount,$via)
{
	if ($via=='') return 0.0;
	if ($via=='manual') return 0.0;

	if ((file_exists(get_file_base().'/sources/hooks/systems/ecommerce_via/'.$via)) || (file_exists(get_file_base().'/sources_custom/hooks/systems/ecommerce_via/'.$via)))
	{
		require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));
		$object=object_factory('Hook_'.$via);
		return $object->get_transaction_fee($amount);
	}

	return 0.0;
}

/**
 * Make a transaction (payment) button.
 *
 * @param  ID_TEXT		The product codename.
 * @param  SHORT_TEXT	The human-readable product title.
 * @param  ID_TEXT		The purchase ID.
 * @param  float			A transaction amount.
 * @param  ID_TEXT		The currency to use.
 * @param  ?ID_TEXT		The service the payment will go via via (NULL: autodetect).
 * @return tempcode		The button
 */
function make_transaction_button($product,$item_name,$purchase_id,$amount,$currency,$via=NULL)
{
	if (is_null($via)) $via=get_option('payment_gateway');
	require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));
	$object=object_factory('Hook_'.$via);
	return $object->make_transaction_button($product,$item_name,$purchase_id,$amount,$currency);
}

/**
 * Make a subscription (payment) button.
 *
 * @param  ID_TEXT		The product codename.
 * @param  SHORT_TEXT	The human-readable product title.
 * @param  ID_TEXT		The purchase ID.
 * @param  float			A transaction amount.
 * @param  integer		The subscription length in the units.
 * @param  ID_TEXT		The length units.
 * @set    d w m y
 * @param  ID_TEXT		The currency to use.
 * @param  ?ID_TEXT		The service the payment will go via via (NULL: autodetect).
 * @return tempcode		The button
 */
function make_subscription_button($product,$item_name,$purchase_id,$amount,$length,$length_units,$currency,$via=NULL)
{
	if (is_null($via)) $via=get_option('payment_gateway');
	require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));
	$object=object_factory('Hook_'.$via);
	return $object->make_subscription_button($product,$item_name,$purchase_id,$amount,$length,$length_units,$currency);
}

/**
 * Make a subscription cancellation button.
 *
 * @param  AUTO_LINK	The purchase ID.
 * @param  ID_TEXT	The service the payment will go via via.
 * @return ?tempcode	The button (NULL: no special cancellation -- just delete the subscription row to stop ocPortal regularly re-charging)
 */
function make_cancel_button($purchase_id,$via)
{
	if ($via=='') return NULL;
	if ($via=='manual') return NULL;
	require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));
	$object=object_factory('Hook_'.$via);
	if (!method_exists($object,'make_cancel_button')) return NULL;
	return $object->make_cancel_button($purchase_id);
}

/**
 * Send an invoice notification to a member.
 *
 * @param  MEMBER		The member to send to.
 * @param  AUTO_LINK	The invoice ID.
 */
function send_invoice_mail($member_id,$id)
{
	// Send out notification
	require_code('notifications');
	$_url=build_url(array('page'=>'invoices','type'=>'misc'),get_module_zone('invoices'),NULL,false,false,true);
	$url=$_url->evaluate();
	dispatch_notification('invoice',NULL,do_lang('INVOICE_SUBJECT',strval($id),NULL,NULL,get_lang($member_id)),do_lang('INVOICE_MESSAGE',$url,get_site_name(),NULL,get_lang($member_id)),array($member_id));
}

/**
 * Find all products, except ones from hooks that might have too many to list (so don't rely on this for important backend tasks).
 *
 * @param  boolean	Whether to make sure the language for item_name is the site default language (crucial for when we read/go to third-party sales systems and use the item_name as a key).
 * @return array		A list of maps of product details.
 */
function find_all_products($site_lang=false)
{
	$_hooks=find_all_hooks('systems','ecommerce');
	$products=array();
	foreach (array_keys($_hooks) as $hook)
	{
		require_code('hooks/systems/ecommerce/'.filter_naughty_harsh($hook));
		$object=object_factory('Hook_'.filter_naughty_harsh($hook),true);
		if (is_null($object)) continue;
		$_products=$object->get_products($site_lang);
		foreach ($_products as $product=>$details)
		{
			if (!array_key_exists(4,$details))
			{
				$details[4]=do_lang('CUSTOM_PRODUCT_'.$product,NULL,NULL,NULL,$site_lang?get_site_default_lang():NULL);
			}
			$details[]=$object;
			$products[$product]=$details;
		}
	}
	return $products;
}

/**
 * Find product.
 *
 * @param  ID_TEXT	The product name/product_id
 * @param  boolean	Whether to make sure the language for item_name is the site default language (crucial for when we read/go to third-party sales systems and use the item_name as a key).
 * @param  boolean 	Whether $search refers to the product name rather than the product_id
 * @return ?object	The product-class object (NULL: not found).
 */
function find_product($search,$site_lang=false,$search_titles_not_ids=false)
{
	$_hooks=find_all_hooks('systems','ecommerce');
	foreach (array_keys($_hooks) as $hook)
	{
		require_code('hooks/systems/ecommerce/'.filter_naughty_harsh($hook));
		$object=object_factory('Hook_'.filter_naughty_harsh($hook),true);
		if (is_null($object)) continue;

		$_products=$object->get_products($site_lang,$search,$search_titles_not_ids);

		$product=mixed();
		foreach ($_products as $product=>$product_row)
		{
			if (is_integer($product)) $product=strval($product);

			if ($search_titles_not_ids)
			{
				if (($product_row[4]==$search) || ('_'.$product==$search)) return $object;
			} else
			{
				if ($product==$search) return $object;
			}
		}
	}
	return NULL;
}

/**
 * Find product info row.
 *
 * @param  ID_TEXT	The product name/product_id
 * @param  boolean	Whether to make sure the language for item_name is the site default language (crucial for when we read/go to third-party sales systems and use the item_name as a key).
 * @param  boolean 	Whether $search refers to the product name rather than the product_id
 * @return array		A pair: The product-class map, and the formal product name (both will be NULL if not found).
 */
function find_product_row($search,$site_lang=false,$search_titles_not_ids=false)
{
	$_hooks=find_all_hooks('systems','ecommerce');
	foreach (array_keys($_hooks) as $hook)
	{
		require_code('hooks/systems/ecommerce/'.filter_naughty_harsh($hook));
		$object=object_factory('Hook_'.filter_naughty_harsh($hook),true);
		if (is_null($object)) continue;

		$_products=$object->get_products($site_lang,$search,$search_titles_not_ids);

		$product=mixed();
		foreach ($_products as $product=>$product_row)
		{
			if (is_integer($product)) $product=strval($product);

			if ($search_titles_not_ids)
			{
				if (($product_row[4]==$search) || ('_'.$product==$search))
				{
					return array($product_row,$product);
				}
			} else
			{
				if ($product==$search) return array($product_row,$product);
			}
		}
	}
	return array(NULL,NULL);
}

/**
 * Find whether local payment will be performed.
 *
 * @return boolean	Whether local payment will be performed.
 */
function perform_local_payment()
{
	$via=get_option('payment_gateway');
	require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));
	$object=object_factory('Hook_'.$via);
	return ((get_option('use_local_payment')=='1') && (method_exists($object,'do_transaction')));
}

/**
 * Send an IPN call to a remote host for debugging purposes.
 * Useful for making one ocP site (caller site) pretend to be PayPal, when talking to another (target site).
 * Make sure the target site has the caller site listed as the backdoor_ip in the base config, or the verification will happen and fail.
 *
 * @param   URL		URL to send IPN to
 * @param   string	Post parameters to send, in query string format
 * @return  string	Output
 */
function dev__ipn_debug($ipn_target,$ipn_message)
{
	require_code('ecommerce');
	$post_params=array();
	parse_str($ipn_message,$post_params);

	return http_download_file($ipn_target,NULL,false,false,'ocPortal-IPN-debug',$post_params)."\n".$GLOBALS['HTTP_MESSAGE'];
}

/**
 * Handle IPN's.
 *
 * @return ID_TEXT		The ID of the purchase-type (meaning depends on item_name)
 */
function handle_transaction_script()
{
	if ((file_exists(get_file_base().'/data_custom/ecommerce.log')) && (is_writable_wrap(get_file_base().'/data_custom/ecommerce.log')))
	{
		$myfile=fopen(get_file_base().'/data_custom/ecommerce.log','at');
		fwrite($myfile,serialize($_POST).chr(10));
		fclose($myfile);
	}

	$via=get_param('from',get_option('payment_gateway'));
	require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));
	$object=object_factory('Hook_'.$via);

	ob_start();
	$test=false;
	if (!$test)
	{
		list($purchase_id,$item_name,$payment_status,$reason_code,$pending_reason,$memo,$mc_gross,$mc_currency,$txn_id,$parent_txn_id)=$object->handle_transaction();
	} else
	{
		$purchase_id='15';
		$item_name=do_lang('CUSTOM_PRODUCT_OTHER');
		$payment_status='Completed';
		$reason_code='';
		$pending_reason='bar';
		$memo='foo';
		$mc_gross='0.01';
		$mc_currency=get_option('currency');
		$txn_id='0';
		$parent_txn_id='0';
	}

	handle_confirmed_transaction($purchase_id,$item_name,$payment_status,$reason_code,$pending_reason,$memo,$mc_gross,$mc_currency,$txn_id,$parent_txn_id,$via,post_param('period3',''));

	return $purchase_id;

	//my_exit(do_lang('SUCCESS'));
}

/**
 * Handle IPN's that have been confirmed as backed up by real money.
 *
 * @param  ID_TEXT		The ID of the purchase-type (meaning depends on item_name)
 * @param  SHORT_TEXT	The item being purchased (aka the product) (blank: subscription, so we need to look it up). One might wonder why we use $item_name instead of $product. This is because we pass human-readable-names (hopefully unique!!!) through payment gateways because they are visually shown to the user. (blank: it's a subscription, so look up via a key map across the subscriptions table)
 * @param  SHORT_TEXT	The status this transaction is telling of
 * @set    SModified SCancelled Completed Pending Failed
 * @param  SHORT_TEXT	The code that gives reason to the status
 * @param  SHORT_TEXT	The reason it is in pending status (if it is)
 * @param  SHORT_TEXT	A note attached to the transaction
 * @param  SHORT_TEXT	The amount of money
 * @param  SHORT_TEXT	The currency the amount is in
 * @param  SHORT_TEXT	The transaction ID
 * @param  SHORT_TEXT	The ID of the parent transaction
 * @param  ID_TEXT		The ID of a special source for the transaction
 * @param  string			The subscription period (blank: N/A)
 */
function handle_confirmed_transaction($purchase_id,$item_name,$payment_status,$reason_code,$pending_reason,$memo,$mc_gross,$mc_currency,$txn_id,$parent_txn_id,$source='',$period='')
{
	/*#####################################################################################*/
	//Temporary setting - force payment setting to "completed" for test mode transactions
	if (get_option('ecommerce_test_mode')=="1")
		$payment_status='Completed';
	/*#####################################################################################*/

	// Try and locate the product
	if (($item_name=='')/* && ($payment_status[0]=='S')*/) // Subscription
	{
		$product=$GLOBALS['SITE_DB']->query_value_null_ok('subscriptions','s_type_code',array('id'=>intval($purchase_id))); // Note that s_type_code is not numeric, it is a $product
		if (is_null($product)) my_exit(do_lang('NO_SUCH_SUBSCRIPTION',strval($purchase_id)),true);
		$item_name='_'.$product;

		// Check what we sold
		list($found,)=find_product_row($product,true,false);
		if (!is_null($found))
		{
			$item_name=$found[4];
		}

		$subscription=true;
	} else
	{
		// Check what we sold
		list($found,$product)=find_product_row($item_name,true,true);

		$subscription=false;
	}
	if (is_null($found)) my_exit(do_lang('PRODUCT_NO_SUCH').' - '.$item_name,true);

	// Check price
	if (($mc_gross!=$found[1]) && ($found[1]!='?'))
	{
		if ($payment_status=='SModified')
			$GLOBALS['SITE_DB']->query_update('subscriptions',array('s_state'=>'new'),array('id'=>intval($purchase_id)),'',1);
		if (($payment_status!='SCancelled') && (substr($txn_id,0,6)!='manual')) my_exit(do_lang('PURCHASE_WRONG_PRICE',$item_name),$subscription);
	}

	if ($period!='')
	{
		$length=array_key_exists('length',$found[3])?strval($found[3]['length']):'1';
		$length_units=array_key_exists('length_units',$found[3])?$found[3]['length_units']:'m';
		if (strtolower($period)!=strtolower($length.' '.$length_units)) my_exit(do_lang('IPN_SUB_PERIOD_WRONG'));
	}


	// Store
	$GLOBALS['SITE_DB']->query_insert('transactions',array('id'=>$txn_id,'t_memo'=>$memo,'purchase_id'=>$purchase_id,'status'=>$payment_status,'pending_reason'=>$pending_reason,'reason'=>$reason_code,'amount'=>$mc_gross,'t_currency'=>$mc_currency,'linked'=>$parent_txn_id,'t_time'=>time(),'item'=>$product,'t_via'=>$source));

	$found['txn_id']=$txn_id;

	// Check currency
	if ($mc_currency!=get_option('currency'))
	{
		if ($payment_status=='SModified')
			$GLOBALS['SITE_DB']->query_update('subscriptions',array('s_state'=>'new'),array('id'=>intval($purchase_id)),'',1);
		if (($payment_status!='SCancelled') && (substr($txn_id,0,6)!='manual')) my_exit(do_lang('PURCHASE_WRONG_CURRENCY'));
	}

	// Pending
	if (($payment_status=='Pending') && ($found[0]==PRODUCT_INVOICE)) // Invoices have special support for tracking the order status
	{
		$GLOBALS['SITE_DB']->query_update('invoices',array('i_state'=>'pending'),array('id'=>intval($purchase_id)),'',1);
	}
	elseif (($payment_status=='Pending') && ($found[0]==PRODUCT_SUBSCRIPTION)) // Subscriptions have special support for tracking the order status
	{
		$GLOBALS['SITE_DB']->query_update('subscriptions',array('s_state'=>'pending'),array('id'=>intval($purchase_id)),'',1);
		if ($found[2]!='') call_user_func_array($found[2],array($purchase_id,$found,$product,true)); // Run cancel code
	}
	elseif (($payment_status=='Pending') && ($item_name==do_lang('CART_ORDER',$purchase_id))) // Cart orders have special support for tracking the order status
	{
		$found['ORDER_STATUS']='ORDER_STATUS_awaiting_payment';

		if ($found[2]!='') call_user_func_array($found[2],array($purchase_id,$found,$product,true)); // Set order status
	}

	// Subscription: Cancelled
	elseif (($payment_status=='SCancelled') && ($found[0]==PRODUCT_SUBSCRIPTION))
	{
		$GLOBALS['SITE_DB']->query_update('subscriptions',array('s_auto_fund_source'=>$source,'s_auto_fund_key'=>$txn_id,'s_state'=>'cancelled'),array('id'=>intval($purchase_id)),'',1);
	}

	// Subscription: Made active
	elseif (($payment_status=='Completed') && ($found[0]==PRODUCT_SUBSCRIPTION))
	{
		$GLOBALS['SITE_DB']->query_update('subscriptions',array('s_auto_fund_source'=>$source,'s_auto_fund_key'=>$txn_id,'s_state'=>'active'),array('id'=>intval($purchase_id)),'',1);
	}

	// Check completed: if not, proceed no further
	elseif (($payment_status!='Completed') && ($payment_status!='SCancelled') && (get_option('ecommerce_test_mode')!='1'))
		my_exit(do_lang('TRANSACTION_NOT_COMPLETE',$product.':'.strval($purchase_id),$payment_status),true);

	// Invoice: Check price
	if ($found[0]==PRODUCT_INVOICE)
	{
		$price=$GLOBALS['SITE_DB']->query_value('invoices','i_amount',array('id'=>intval($purchase_id)));
		if ($price!=$mc_gross)
		{
			if (substr($txn_id,0,6)!='manual')
				my_exit(do_lang('PURCHASE_WRONG_PRICE',$item_name));
		}
	}

	/* At this point we know our order (or subscription cancellation) is good */

	// Dispatch
	if (($payment_status=='Completed') || ($payment_status=='SCancelled'))
	{
		//Find product hooks of this order to check dispatch type

		$object=find_product($product,true);

		if(is_object($object) && !method_exists($object,'get_product_dispatch_type'))	
		{	//If hook does not have dispatch method setting take dispatch method as automatic
			$found['ORDER_STATUS']='ORDER_STATUS_dispatched';	
		}
		elseif(is_object($object) && $object->get_product_dispatch_type($purchase_id)=='automatic')
		{	
			$found['ORDER_STATUS']='ORDER_STATUS_dispatched';
		}
		else
		{	
			$found['ORDER_STATUS']='ORDER_STATUS_payment_received';
		}
		if ($found[2]!='') call_user_func_array($found[2],array($purchase_id,$found,$product));

		// Send out notification to staff
		if ($found[0]==PRODUCT_SUBSCRIPTION)
		{
			require_code('notifications');
			$member_id=$GLOBALS['SITE_DB']->query_value_null_ok('subscriptions','s_member_id',array('id'=>intval($purchase_id)));
			if (!is_null($member_id))
			{
				$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id);
				if (is_null($username)) $username=do_lang('GUEST');
				if ($payment_status=='Completed')
				{
					$subject=do_lang('SERVICE_PAID_FOR',$item_name,$username,get_site_name(),get_site_default_lang());
					$body=do_lang('_SERVICE_PAID_FOR',$item_name,$username,get_site_name(),get_site_default_lang());
					dispatch_notification('service_paid_for_staff',NULL,$subject,$body);
				} else
				{
					$subject=do_lang('SERVICE_CANCELLED',$item_name,$username,get_site_name(),get_site_default_lang());
					$body=do_lang('_SERVICE_CANCELLED',$item_name,$username,get_site_name(),get_site_default_lang());
					dispatch_notification('service_cancelled_staff',NULL,$subject,$body);
				}
			}
		}
	}

	// Invoice handling
	if ($found[0]==PRODUCT_INVOICE)
	{
		$GLOBALS['SITE_DB']->query_update('invoices',array('i_state'=>'paid'),array('id'=>intval($purchase_id)),'',1);
	}

	// Subscription: Delete if cancelled
	if (($payment_status=='SCancelled') && ($found[0]==PRODUCT_SUBSCRIPTION))
	{
		$GLOBALS['SITE_DB']->query_delete('subscriptions',array('id'=>intval($purchase_id)),'',1);
	}
}

/**
 * Exit ocPortal and write to the error log file.
 *
 * @param  string		The message.
 * @param  boolean	Dont trigger an error
 */
function my_exit($error,$dont_trigger=false)
{
	echo $error."\n";
	if (!$dont_trigger) trigger_error($error,E_USER_NOTICE);
	exit();
}

/**
 * Add a usergroup subscription.
 *
 * @param  SHORT_TEXT	The title
 * @param  LONG_TEXT		The description
 * @param  SHORT_TEXT	The cost
 * @param  integer		The length
 * @param  SHORT_TEXT	The units for the length
 * @set    y m d w
 * @param  ?GROUP			The usergroup that purchasing gains membership to (NULL: super members)
 * @param  BINARY			Whether this is applied to primary usergroup membership
 * @param  BINARY			Whether this is currently enabled
 * @param  ?LONG_TEXT	The text of the e-mail to send out when a subscription is start (NULL: default)
 * @param  ?LONG_TEXT	The text of the e-mail to send out when a subscription is ended (NULL: default)
 * @param  ?LONG_TEXT	The text of the e-mail to send out when a subscription cannot be renewed because the subproduct is gone (NULL: default)
 * @return AUTO_LINK		The ID
 */
function add_usergroup_subscription($title,$description,$cost,$length,$length_units,$group_id,$uses_primary,$enabled,$mail_start,$mail_end,$mail_uhoh)
{
	$dbs_bak=$GLOBALS['NO_DB_SCOPE_CHECK'];
	$GLOBALS['NO_DB_SCOPE_CHECK']=true;

	$id=$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']->query_insert('f_usergroup_subs',array(
		's_title'=>insert_lang($title,2,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
		's_description'=>insert_lang($description,2,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
		's_cost'=>$cost,
		's_length'=>$length,
		's_length_units'=>$length_units,
		's_group_id'=>$group_id,
		's_uses_primary'=>$uses_primary,
		's_enabled'=>$enabled,
		's_mail_start'=>insert_lang($mail_start,2,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
		's_mail_end'=>insert_lang($mail_end,2,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
		's_mail_uhoh'=>insert_lang($mail_uhoh,2,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
	),true);

	log_it('ADD_USERGROUP_SUBSCRIPTION',strval($id),$title);

	$GLOBALS['NO_DB_SCOPE_CHECK']=$dbs_bak;

	return $id;
}

/**
 * Edit a usergroup subscription.
 *
 * @param  AUTO_LINK		The ID
 * @param  SHORT_TEXT	The title
 * @param  LONG_TEXT		The description
 * @param  SHORT_TEXT	The cost
 * @param  integer		The length
 * @param  SHORT_TEXT	The units for the length
 * @set    y m d w
 * @param  ?GROUP			The usergroup that purchasing gains membership to (NULL: super members)
 * @param  BINARY			Whether this is applied to primary usergroup membership
 * @param  BINARY			Whether this is currently enabled
 * @param  ?LONG_TEXT	The text of the e-mail to send out when a subscription is start (NULL: default)
 * @param  ?LONG_TEXT	The text of the e-mail to send out when a subscription is ended (NULL: default)
 * @param  ?LONG_TEXT	The text of the e-mail to send out when a subscription cannot be renewed because the subproduct is gone (NULL: default)
 */
function edit_usergroup_subscription($id,$title,$description,$cost,$length,$length_units,$group_id,$uses_primary,$enabled,$mail_start,$mail_end,$mail_uhoh)
{
	$dbs_bak=$GLOBALS['NO_DB_SCOPE_CHECK'];
	$GLOBALS['NO_DB_SCOPE_CHECK']=true;

	$rows=$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']->query_select('f_usergroup_subs',array('*'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$myrow=$rows[0];

	// If usergroup has changed, do a move
	if ($myrow['s_group_id']!=$group_id)
	{
		require_code('ocf_groups_action');
		require_code('ocf_groups_action2');
		$product='USERGROUP'.strval($id);
		$subscriptions=$GLOBALS['SITE_DB']->query_select('subscriptions',array('*'),array('s_type_code'=>$product));
		foreach ($subscriptions as $sub)
		{
			$member_id=$sub['s_member_id'];
			if ((get_value('unofficial_ecommerce')=='1') && (get_forum_type()!='ocf'))
			{
				$GLOBALS['FORUM_DB']->remove_member_from_group($member_id,$group_id);
				$GLOBALS['FORUM_DB']->add_member_to_group($member_id,$group_id);
			} else
			{
				$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']->query_delete('f_group_members',array('gm_group_id'=>$group_id,'gm_member_id'=>$member_id),'',1);
				ocf_add_member_to_group($member_id,$group_id);
			}
		}
	}

	$_title=$myrow['s_title'];
	$_description=$myrow['s_description'];
	$_mail_start=$myrow['s_mail_start'];
	$_mail_end=$myrow['s_mail_end'];
	$_mail_uhoh=$myrow['s_mail_uhoh'];

	$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']->query_update('f_usergroup_subs',array(
		's_title'=>lang_remap($_title,$title,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
		's_description'=>lang_remap($_description,$description,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
		's_cost'=>$cost,
		's_length'=>$length,
		's_length_units'=>$length_units,
		's_group_id'=>$group_id,
		's_uses_primary'=>$uses_primary,
		's_enabled'=>$enabled,
		's_mail_start'=>lang_remap($_mail_start,$mail_start,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
		's_mail_end'=>lang_remap($_mail_end,$mail_end,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
		's_mail_uhoh'=>lang_remap($_mail_uhoh,$mail_uhoh,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']),
	),array('id'=>$id),'',1);

	log_it('EDIT_USERGROUP_SUBSCRIPTION',strval($id),$title);

	$GLOBALS['NO_DB_SCOPE_CHECK']=$dbs_bak;
}

/**
 * Delete a usergroup subscription.
 *
 * @param  AUTO_LINK		The ID
 * @param  LONG_TEXT		The cancellation mail to send out (blank: none)
 */
function delete_usergroup_subscription($id,$uhoh_mail='')
{
	$dbs_bak=$GLOBALS['NO_DB_SCOPE_CHECK'];
	$GLOBALS['NO_DB_SCOPE_CHECK']=true;

	$rows=$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']->query_select('f_usergroup_subs',array('*'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$myrow=$rows[0];
	$new_group=$myrow['s_group_id'];

	// Remove benefits
	$product='USERGROUP'.strval($id);
	$subscriptions=$GLOBALS['SITE_DB']->query_select('subscriptions',array('*'),array('s_type_code'=>$product));
	$to_members=array();
	foreach ($subscriptions as $sub)
	{
		$member_id=$sub['s_member_id'];

		$test=in_array($new_group,$GLOBALS['FORUM_DRIVER']->get_members_groups($member_id));
		if ($test)
		{
			if (is_null($GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']->query_value_null_ok('f_group_member_timeouts','member_id',array('member_id'=>$member_id,'group_id'=>$new_group))))
			{
				// Remove them from the group

				if ((get_value('unofficial_ecommerce')=='1') && (get_forum_type()!='ocf'))
				{
					$GLOBALS['FORUM_DB']->remove_member_from_group($member_id,$new_group);
				} else
				{
					$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']->query_delete('f_group_members',array('gm_group_id'=>$new_group,'gm_member_id'=>$member_id),'',1);
				}
				$to_members[]=$member_id;
			}
		}
	}
	if ($uhoh_mail!='')
	{
		require_code('notifications');
		dispatch_notification('paid_subscription_ended',NULL,do_lang('PAID_SUBSCRIPTION_ENDED',NULL,NULL,NULL,get_site_default_lang()),$uhoh_mail,$to_members);
	}

	$_title=$myrow['s_title'];
	$_description=$myrow['s_description'];
	$title=get_translated_text($_title,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']);
	$_mail_start=$myrow['s_mail_start'];
	$_mail_end=$myrow['s_mail_end'];
	$_mail_uhoh=$myrow['s_mail_uhoh'];

	$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']->query_delete('f_usergroup_subs',array('id'=>$id),'',1);
	delete_lang($_title,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']);
	delete_lang($_description,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']);
	delete_lang($_mail_start,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']);
	delete_lang($_mail_end,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']);
	delete_lang($_mail_uhoh,$GLOBALS[(get_forum_type()=='ocf')?'FORUM_DB':'SITE_DB']);

	log_it('DELETE_USERGROUP_SUBSCRIPTION',strval($id),$title);

	$GLOBALS['NO_DB_SCOPE_CHECK']=$dbs_bak;
}

/**
 * Make a shopping cart payment button.
 *
 * @param  AUTO_LINK		Order ID
 * @param  ID_TEXT		The currency to use.
 * @return tempcode		The button
*/
function make_cart_payment_button($order_id,$currency)
{
	$_items=$GLOBALS['SITE_DB']->query_select('shopping_order_details',array('p_name','p_price','p_quantity'),array('order_id'=>$order_id));
	$items=array();
	foreach ($_items as $item)
	{
		$items[]=array(
			'PRODUCT_NAME'=>$item['p_name'],
			'PRICE'=>float_to_raw_string($item['p_price']),
			'QUANTITY'=>strval($item['p_quantity']),
		);
	}

	$via=get_option('payment_gateway');

	require_code('hooks/systems/ecommerce_via/'.filter_naughty_harsh($via));

	$object=object_factory('Hook_'.$via);

	if (!method_exists($object,'make_cart_transaction_button'))
	{
		$amount=$GLOBALS['SITE_DB']->query_value('shopping_order','tot_price',array('id'=>$order_id));
		return $object->make_transaction_button($order_id,do_lang('CART_ORDER',$order_id),$order_id,$amount,$currency);
	}

	return $object->make_cart_transaction_button($items,$currency,$order_id);
}
