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

class Hook_addon_registry_ecommerce
{

	/**
	 * Get a list of file permissions to set
	 *
	 * @return array			File permissions to set
	 */
	function get_chmod_array()
	{
		return array();
	}

	/**
	 * Get the version of ocPortal this addon is for
	 *
	 * @return float			Version number
	 */
	function get_version()
	{
		return ocp_version_number();
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'eCommerce infrastructure, with support for digital purchase and usergroup subscriptions. Accounting functionality.';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array(),
		);
	}

	/**
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(

			'sources/hooks/systems/config_default/callback_password.php',
			'sources/hooks/systems/config_default/currency.php',
			'sources/hooks/systems/config_default/ecommerce_test_mode.php',
			'sources/hooks/systems/config_default/ipn.php',
			'sources/hooks/systems/config_default/ipn_digest.php',
			'sources/hooks/systems/config_default/ipn_password.php',
			'sources/hooks/systems/config_default/ipn_test.php',
			'sources/hooks/systems/config_default/payment_gateway.php',
			'sources/hooks/systems/config_default/pd_address.php',
			'sources/hooks/systems/config_default/pd_email.php',
			'sources/hooks/systems/config_default/pd_number.php',
			'sources/hooks/systems/config_default/use_local_payment.php',
			'sources/hooks/systems/config_default/vpn_password.php',
			'sources/hooks/systems/config_default/vpn_username.php',
			'sources/hooks/systems/realtime_rain/ecommerce.php',
			'themes/default/images/EN/pageitem/pay.png',
			'sources/hooks/systems/addon_registry/ecommerce.php',
			'sources/hooks/modules/admin_import_types/ecommerce.php',
			'ECOM_CASH_FLOW_SCREEN.tpl',
			'ECOM_INVOICES_SCREEN.tpl',
			'ECOM_OUTSTANDING_INVOICES_SCREEN.tpl',
			'ECOM_SUBSCRIPTIONS_SCREEN.tpl',
			'PURCHASE_WIZARD_SCREEN.tpl',
			'PURCHASE_WIZARD_STAGE_CHOOSE.tpl',
			'PURCHASE_WIZARD_STAGE_DETAILS.tpl',
			'PURCHASE_WIZARD_STAGE_FINISH.tpl',
			'PURCHASE_WIZARD_STAGE_GUEST.tpl',
			'PURCHASE_WIZARD_STAGE_LICENCE.tpl',
			'PURCHASE_WIZARD_STAGE_MESSAGE.tpl',
			'PURCHASE_WIZARD_STAGE_PAY.tpl',
			'PURCHASE_WIZARD_STAGE_SUBSCRIBE.tpl',
			'PURCHASE_WIZARD_STAGE_TRANSACT.tpl',
			'ECOM_BUTTON_VIA_PAYPAL.tpl',
			'ECOM_BUTTON_VIA_SECPAY.tpl',
			'ECOM_BUTTON_VIA_WORLDPAY.tpl',
			'ECOM_CANCEL_BUTTON_VIA_PAYPAL.tpl',
			'ECOM_CANCEL_BUTTON_VIA_SECPAY.tpl',
			'ECOM_CANCEL_BUTTON_VIA_WORLDPAY.tpl',
			'ECOM_LOGOS_WORLDPAY.tpl',
			'ECOM_SUBSCRIPTION_BUTTON_VIA_PAYPAL.tpl',
			'ECOM_SUBSCRIPTION_BUTTON_VIA_SECPAY.tpl',
			'ECOM_SUBSCRIPTION_BUTTON_VIA_WORLDPAY.tpl',
			'ECOM_TRANSACTION_LOGS_MANUAL_TRIGGER.tpl',
			'ECOM_TRANSACTION_LOGS_SCREEN.tpl',
			'adminzone/pages/modules/admin_ecommerce.php',
			'adminzone/pages/modules/admin_invoices.php',
			'ecommerce.css',
			'themes/default/images/bigicons/ecommerce.png',
			'themes/default/images/pagepics/ecommerce.png',
			'data/ecommerce.php',
			'lang/EN/ecommerce.ini',
			'sources/hooks/systems/notifications/paid_subscription_started.php',
			'sources/hooks/systems/notifications/paid_subscription_ended.php',
			'sources/hooks/systems/notifications/payment_received.php',
			'sources/hooks/systems/notifications/invoice.php',
			'sources/hooks/systems/notifications/subscription_cancelled_staff.php',
			'sources/hooks/systems/notifications/service_cancelled_staff.php',
			'sources/hooks/systems/notifications/service_paid_for_staff.php',
			'sources/ecommerce.php',
			'sources/hooks/modules/members/ecommerce.php',
			'sources/hooks/systems/do_next_menus/ecommerce.php',
			'sources/hooks/systems/ecommerce/.htaccess',
			'sources/hooks/systems/ecommerce/index.html',
			'sources/hooks/systems/ecommerce/interest.php',
			'sources/hooks/systems/ecommerce/other.php',
			'sources/hooks/systems/ecommerce/tax.php',
			'sources/hooks/systems/ecommerce/usergroup.php',
			'sources/hooks/systems/ecommerce/wage.php',
			'sources/hooks/systems/ecommerce/work.php',
			'sources/hooks/systems/ecommerce_via/.htaccess',
			'sources/hooks/systems/ecommerce_via/index.html',
			'sources/hooks/systems/ecommerce_via/paypal.php',
			'sources/hooks/systems/ecommerce_via/secpay.php',
			'sources/hooks/systems/ecommerce_via/worldpay.php',
			'sources/hooks/systems/ocf_cpf_filter/ecommerce.php',
			'site/pages/modules/purchase.php',
			'site/pages/modules/subscriptions.php',
			'themes/default/images/bigicons/invoices.png',
			'themes/default/images/pagepics/invoices.png',
			'site/pages/modules/invoices.php',
			'themes/default/images/bigicons/cash_flow.png',
			'themes/default/images/bigicons/profit_loss.png',
			'themes/default/images/bigicons/transactions.png',
			'themes/default/images/pagepics/cash_flow.png',
			'themes/default/images/pagepics/transactions.png',
			'themes/default/images/pagepics/profit_loss.png',
			'sources/currency.php',
		);
	}


	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array			The mapping
	*/
	function tpl_previews()
	{
		return array(
				'ECOM_OUTSTANDING_INVOICES_SCREEN.tpl'=>'administrative__ecom_outstanding_invoices_screen',
				'ECOM_TRANSACTION_LOGS_MANUAL_TRIGGER.tpl'=>'ecom_subscriptions_screen',
				'ECOM_TRANSACTION_LOGS_SCREEN.tpl'=>'administrative__ecom_transaction_logs_screen',
				'ECOM_CASH_FLOW_SCREEN.tpl'=>'administrative__ecom_cash_flow_screen',
				'PURCHASE_WIZARD_STAGE_TRANSACT.tpl'=>'purchase_wizard_stage_transact',
				'PURCHASE_WIZARD_SCREEN.tpl'=>'purchase_wizard_screen',
				'ECOM_LOGOS_WORLDPAY.tpl'=>'ecom_logos_worldpay',
				'ECOM_BUTTON_VIA_WORLDPAY.tpl'=>'ecom_button_via_worldpay',
				'ECOM_SUBSCRIPTION_BUTTON_VIA_WORLDPAY.tpl'=>'ecom_subscription_button_via_worldpay',
				'ECOM_CANCEL_BUTTON_VIA_WORLDPAY.tpl'=>'ecom_cancel_button_via_worldpay',
				'ECOM_BUTTON_VIA_PAYPAL.tpl'=>'ecom_button_via_paypal',
				'ECOM_SUBSCRIPTION_BUTTON_VIA_PAYPAL.tpl'=>'ecom_subscription_button_via_paypal',
				'ECOM_CANCEL_BUTTON_VIA_PAYPAL.tpl'=>'ecom_cancel_button_via_paypal',
				'ECOM_BUTTON_VIA_SECPAY.tpl'=>'ecom_button_via_secpay',
				'ECOM_SUBSCRIPTION_BUTTON_VIA_SECPAY.tpl'=>'ecom_subscription_button_via_secpay',
				'ECOM_CANCEL_BUTTON_VIA_SECPAY.tpl'=>'ecom_cancel_button_via_secpay',
				'PURCHASE_WIZARD_STAGE_GUEST.tpl'=>'purchase_wizard_stage_guest',
				'PURCHASE_WIZARD_STAGE_CHOOSE.tpl'=>'purchase_wizard_stage_choose',
				'PURCHASE_WIZARD_STAGE_MESSAGE.tpl'=>'purchase_wizard_stage_message',
				'PURCHASE_WIZARD_STAGE_LICENCE.tpl'=>'purchase_wizard_stage_licence',
				'PURCHASE_WIZARD_STAGE_DETAILS.tpl'=>'purchase_wizard_stage_details',
				'PURCHASE_WIZARD_STAGE_FINISH.tpl'=>'purchase_wizard_stage_finish',
				'ECOM_INVOICES_SCREEN.tpl'=>'ecom_invoices_screen',
				'ECOM_SUBSCRIPTIONS_SCREEN.tpl'=>'ecom_subscriptions_screen',
				'PURCHASE_WIZARD_STAGE_SUBSCRIBE.tpl'=>'purchase_wizard_stage_subscribe',
				'PURCHASE_WIZARD_STAGE_PAY.tpl'=>'purchase_wizard_stage_pay',
				);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__ecom_outstanding_invoices_screen()
	{
		$invoices=array();
		foreach(placeholder_array() as $invoice)
		{
			$invoices[]=array('INVOICE_TITLE'=>lorem_word(),'PROFILE_URL'=>placeholder_url(),'USERNAME'=>lorem_word_2(),'ID'=>placeholder_id(),'STATE'=>lorem_phrase(),'AMOUNT'=>placeholder_number(),'TIME'=>placeholder_time(),'NOTE'=>lorem_phrase(),'TYPE_CODE'=>lorem_phrase());
		}

		return array(
			lorem_globalise(
				do_lorem_template('ECOM_OUTSTANDING_INVOICES_SCREEN',array(
					'TITLE'=>lorem_title(),
					'FROM'=>lorem_phrase(),
					'INVOICES'=>$invoices
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__ecom_transaction_logs_screen()
	{
		$results_browser = placeholder_result_browser();

		//results_table starts
		//results_entry starts
		$cells = new ocp_tempcode();
		foreach (placeholder_array(11) as $k=>$v)
		{
			$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>$v)));
		}
		$fields_title = $cells;

		$order_entries = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells = new ocp_tempcode();
			foreach (placeholder_array(11) as $k=>$v)
			{
				$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>lorem_word()),NULL,false,'RESULTS_TABLE_FIELD'));
			}
			$order_entries->attach(do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false,'RESULTS_TABLE_ENTRY'));
		}
		//results_entry ends

		$selectors = new ocp_tempcode();
		foreach (placeholder_array(11) as $k=>$v)
		{
			$selectors->attach(do_lorem_template('RESULTS_BROWSER_SORTER',array('SELECTED'=>'','NAME'=>$v,'VALUE'=>$v)));
		}
		$sort = do_lorem_template('RESULTS_BROWSER_SORT',array('HIDDEN'=>'','SORT'=>lorem_word(),'RAND'=>placeholder_random(),'URL'=>placeholder_url(),'SELECTORS'=>$selectors));

		$results_table = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$order_entries,'MESSAGE'=>new ocp_tempcode(),'SORT'=>$sort,'BROWSER'=>$results_browser,'WIDTHS'=>array()),NULL,false,'RESULTS_TABLE');
		//results_table ends

		return array(
			lorem_globalise(
				do_lorem_template('ECOM_TRANSACTION_LOGS_SCREEN',array(
					'TITLE'=>lorem_title(),
					'PRODUCTS'=>lorem_phrase(),
					'URL'=>placeholder_url(),
					'RESULTS_TABLE'=>$results_table,
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__ecom_cash_flow_screen()
	{
		$types = array();
		foreach(placeholder_array() as $v)
		{
			$types[]=array('TYPE'=>lorem_word(),'AMOUNT'=>placeholder_number(),'SPECIAL'=>placeholder_number());
		}
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_CASH_FLOW_SCREEN',array(
					'TITLE'=>lorem_title(),
					'TYPES'=>$types,
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_transact()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_TRANSACT',array(
					'FIELDS'=>placeholder_fields(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_screen()
	{
		$fields	=	do_lorem_template('PURCHASE_WIZARD_STAGE_TRANSACT',array(
							'FIELDS'=>placeholder_fields(),
							)
						);

		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_SCREEN',array(
					'GET'=>false,
					'TITLE'=>lorem_title(),
					'CONTENT'=>$fields,
					'URL'=>placeholder_url(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_logos_worldpay()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_LOGOS_WORLDPAY',array(
					'INST_ID'=>placeholder_id(),
					'PD_ADDRESS'=>lorem_phrase(),
					'PD_EMAIL'=>lorem_word(),
					'PD_NUMBER'=>placeholder_number(),
					'PAYMENT_CANCEL_DTR'=>lorem_word(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_button_via_worldpay()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_BUTTON_VIA_WORLDPAY',array(
					'PRODUCT'=>lorem_phrase(),
					'ITEM_NAME'=>lorem_word(),
					'DIGEST'=>lorem_phrase(),
					'TEST_MODE'=>lorem_phrase(),
					'PURCHASE_ID'=>placeholder_id(),
					'AMOUNT'=>placeholder_number(),
					'CURRENCY'=>lorem_phrase(),
					'USERNAME'=>lorem_word(),
					'IPN_URL'=>placeholder_url(),
					'EMAIL_ADDRESS'=>lorem_word(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_subscription_button_via_worldpay()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_SUBSCRIPTION_BUTTON_VIA_WORLDPAY',array(
					'PRODUCT'=>lorem_phrase(),
					'DIGEST'=>lorem_phrase(),
					'TEST'=>lorem_phrase(),
					'LENGTH'=>lorem_phrase(),
					'LENGTH_UNITS_2'=>lorem_phrase(),
					'ITEM_NAME'=>lorem_word(),
					'PURCHASE_ID'=>placeholder_id(),
					'AMOUNT'=>placeholder_number(),
					'FIRST_REPEAT'=>lorem_phrase(),
					'CURRENCY'=>lorem_phrase(),
					'USERNAME'=>lorem_word(),
					'IPN_URL'=>placeholder_url(),
					'TEST_MODE'=>true,
					'EMAIL_ADDRESS'=>lorem_word(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_cancel_button_via_worldpay()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_CANCEL_BUTTON_VIA_WORLDPAY',array(
					'CANCEL_URL'=>placeholder_url(),
					'PURCHASE_ID'=>placeholder_id(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_button_via_paypal()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_BUTTON_VIA_PAYPAL',array(
					'PRODUCT'=>lorem_phrase(),
					'ITEM_NAME'=>lorem_word(),
					'PURCHASE_ID'=>placeholder_id(),
					'AMOUNT'=>placeholder_number(),
					'CURRENCY'=>lorem_phrase(),
					'PAYMENT_ADDRESS'=>lorem_word(),
					'IPN_URL'=>placeholder_url(),
					'MEMBER_ADDRESS'=>placeholder_array(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_subscription_button_via_paypal()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_SUBSCRIPTION_BUTTON_VIA_PAYPAL',array(
					'PRODUCT'=>lorem_phrase(),
					'ITEM_NAME'=>lorem_word_html(),
					'LENGTH'=>lorem_phrase(),
					'LENGTH_UNITS'=>lorem_phrase(),
					'PURCHASE_ID'=>placeholder_id(),
					'AMOUNT'=>placeholder_number(),
					'CURRENCY'=>lorem_phrase(),
					'PAYMENT_ADDRESS'=>lorem_word(),
					'IPN_URL'=>placeholder_url(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_cancel_button_via_paypal()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_CANCEL_BUTTON_VIA_PAYPAL',array(
					'PURCHASE_ID'=>placeholder_id(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_button_via_secpay()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_BUTTON_VIA_SECPAY',array(
					'PRODUCT'=>lorem_phrase(),
					'DIGEST'=>lorem_phrase(),
					'TEST'=>lorem_phrase(),
					'TRANS_ID'=>placeholder_id(),
					'ITEM_NAME'=>lorem_word_html(),
					'PURCHASE_ID'=>placeholder_id(),
					'AMOUNT'=>placeholder_number(),
					'CURRENCY'=>lorem_phrase(),
					'USERNAME'=>lorem_word(),
					'IPN_URL'=>placeholder_url(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_subscription_button_via_secpay()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_SUBSCRIPTION_BUTTON_VIA_SECPAY',array(
					'PRODUCT'=>lorem_phrase(),
					'DIGEST'=>lorem_phrase(),
					'TEST'=>lorem_phrase(),
					'TRANS_ID'=>placeholder_id(),
					'FIRST_REPEAT'=>lorem_phrase(),
					'LENGTH'=>lorem_phrase(),
					'LENGTH_UNITS_2'=>lorem_phrase(),
					'ITEM_NAME'=>lorem_word_html(),
					'PURCHASE_ID'=>placeholder_id(),
					'AMOUNT'=>placeholder_number(),
					'CURRENCY'=>lorem_phrase(),
					'USERNAME'=>lorem_word(),
					'IPN_URL'=>placeholder_url(),
					'PRICE'=>placeholder_number(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_cancel_button_via_secpay()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ECOM_CANCEL_BUTTON_VIA_SECPAY',array(
					'CANCEL_URL'=>placeholder_url(),
					'PURCHASE_ID'=>placeholder_id(),
						)
			),NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_guest()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_GUEST',array(
					'TITLE'=>lorem_title(),
					'TEXT'=>lorem_sentence_html(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_choose()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_CHOOSE',array(
					'FIELDS'=>placeholder_fields(),
					'TITLE'=>lorem_title(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_message()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_MESSAGE',array(
					'TITLE'=>lorem_title(),
					'TEXT'=>lorem_sentence_html(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_licence()
	{
		require_lang('installer');
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_LICENCE',array(
					'TITLE'=>lorem_title(),
					'URL'=>placeholder_url(),
					'LICENCE'=>lorem_phrase(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_details()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_DETAILS',array(
					'TEXT'=>lorem_sentence_html(),
					'FIELDS'=>placeholder_fields()
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_finish()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_FINISH',array(
					'TITLE'=>lorem_title(),
					'MESSAGE'=>lorem_phrase(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_invoices_screen()
	{
		$invoices = array();
		foreach (placeholder_array() as $k=>$v)
		{
			$invoices[] = array('TRANSACTION_BUTTON'=>placeholder_button(),'INVOICE_TITLE'=>lorem_phrase(),'ID'=>placeholder_id(),'AMOUNT'=>placeholder_number(),'TIME'=>placeholder_date(),'STATE'=>lorem_word(),'DELIVERABLE'=>lorem_word(),'PAYABLE'=>lorem_word(),'NOTE'=>lorem_phrase(),'TYPE_CODE'=>lorem_word());
		}

		return array(
			lorem_globalise(
				do_lorem_template('ECOM_INVOICES_SCREEN',array(
					'TITLE'=>lorem_title(),
					'CURRENCY'=>lorem_phrase(),
					'INVOICES'=>$invoices
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ecom_subscriptions_screen()
	{
		$button = do_lorem_template('ECOM_TRANSACTION_LOGS_MANUAL_TRIGGER',array(
					'STATUS'=>lorem_phrase(),
					'TRIGGER_URL'=>placeholder_url(),
						));
		$subscriptions = array();
		foreach (placeholder_array() as $k=>$v)
		{
			$subscriptions[] = array('SUBSCRIPTION_TITLE'=>lorem_phrase(),'ID'=>placeholder_id(),'PER'=>lorem_word(),'AMOUNT'=>placeholder_number(),'TIME'=>placeholder_date(),'STATE'=>lorem_word(),'TYPE_CODE'=>lorem_word(),'CANCEL_BUTTON'=>$button);
		}

		return array(
			lorem_globalise(
				do_lorem_template('ECOM_SUBSCRIPTIONS_SCREEN',array(
					'TITLE'=>lorem_title(),
					'SUBSCRIPTIONS'=>$subscriptions
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_subscribe()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_SUBSCRIBE',array(
					'LOGOS'=>placeholder_image(),
					'TRANSACTION_BUTTON'=>placeholder_button(),
					'CURRENCY'=>placeholder_number(),
					'ITEM_NAME'=>lorem_word(),
					'TITLE'=>lorem_phrase(),
					'LENGTH'=>"3",
					'LENGTH_UNITS'=>"$",
					'PURCHASE_ID'=>placeholder_id(),
					'PRICE'=>"123.45"
					)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__purchase_wizard_stage_pay()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PURCHASE_WIZARD_STAGE_PAY',array(
					'LOGOS'=>placeholder_image(),
					'TRANSACTION_BUTTON'=>placeholder_button(),
					'CURRENCY'=>placeholder_number(),
					'ITEM_NAME'=>lorem_word(),
					'TITLE'=>lorem_phrase(),
					'LENGTH'=>"3",
					'LENGTH_UNITS'=>"$",
					'PURCHASE_ID'=>placeholder_id(),
					'PRICE'=>"123.45"
					)
			),NULL,'',true),
		);
	}
}