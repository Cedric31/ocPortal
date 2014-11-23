<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    pointstore
 */

/**
 * Hook class.
 */
class Hook_pointstore_forwarding
{
    /**
     * Standard pointstore item initialisation function.
     */
    public function init()
    {
    }

    /**
     * Standard pointstore item "shop front" function.
     *
     * @return array                    The "shop fronts"
     */
    public function info()
    {
        return array();
    }

    /**
     * Standard pointstore item configuration save function.
     */
    public function save_config()
    {
        $forw = post_param_integer('forw', -1);
        if ($forw != -1) {
            $dforw = post_param('dforw');
            $GLOBALS['SITE_DB']->query_insert('prices', array('name' => 'forw_' . $dforw, 'price' => $forw));
            log_it('POINTSTORE_ADD_MAIL_FORWARDER', $dforw);
        }
        $this->_do_price_mail();
    }

    /**
     * Update an e-mail address from what was chosen in an interface; update or delete each price/cost/item
     */
    public function _do_price_mail()
    {
        $i = 0;
        while (array_key_exists('forw_' . strval($i), $_POST)) {
            $price = post_param_integer('forw_' . strval($i));
            $name = 'forw_' . post_param('dforw_' . strval($i));
            $name2 = 'forw_' . post_param('ndforw_' . strval($i));
            if (post_param_integer('delete_forw_' . strval($i), 0) == 1) {
                $GLOBALS['SITE_DB']->query_delete('prices', array('name' => $name), '', 1);
            } else {
                $GLOBALS['SITE_DB']->query_update('prices', array('price' => $price, 'name' => $name2), array('name' => $name), '', 1);
            }

            $i++;
        }
    }

    /**
     * Get fields for adding/editing one of these.
     *
     * @return tempcode                 The fields
     */
    public function get_fields()
    {
        $fields = new Tempcode();
        $fields->attach(form_input_line(do_lang_tempcode('MAIL_DOMAIN'), do_lang_tempcode('DESCRIPTION_MAIL_DOMAIN'), 'dforw', '', true));
        $fields->attach(form_input_integer(do_lang_tempcode('MAIL_COST'), do_lang_tempcode('_DESCRIPTION_MAIL_COST'), 'forw', null, true));
        return $fields;
    }

    /**
     * Standard pointstore item configuration function.
     *
     * @return ?array                   A tuple: list of [fields to shown, hidden fields], title for add form, add form (null: disabled)
     */
    public function config()
    {
        $rows = $GLOBALS['SITE_DB']->query('SELECT price,name FROM ' . get_table_prefix() . 'prices WHERE name LIKE \'' . db_encode_like('forw_%') . '\'');
        $out = array();
        foreach ($rows as $i => $row) {
            $fields = new Tempcode();
            $hidden = new Tempcode();
            $domain = substr($row['name'], strlen('forw_'));
            $hidden->attach(form_input_hidden('dforw_' . strval($i), $domain));
            $fields->attach(form_input_line(do_lang_tempcode('MAIL_DOMAIN'), do_lang_tempcode('DESCRIPTION_MAIL_DOMAIN'), 'ndforw_' . strval($i), substr($row['name'], 5), true));
            $fields->attach(form_input_integer(do_lang_tempcode('MAIL_COST'), do_lang_tempcode('DESCRIPTION_MAIL_COST', escape_html('forw'), escape_html($domain)), 'forw_' . strval($i), $row['price'], true));
            $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '34f5212a96f58fa1b0575a99ca0509e7', 'TITLE' => do_lang_tempcode('ACTIONS'))));
            $fields->attach(form_input_tick(do_lang_tempcode('DELETE'), do_lang_tempcode('DESCRIPTION_DELETE'), 'delete_forw_' . strval($i), false));
            $out[] = array($fields, $hidden, do_lang_tempcode('_EDIT_FORWARDING_DOMAIN', escape_html(substr($row['name'], 5))));
        }

        return array($out, do_lang_tempcode('ADD_NEW_FORWARDING_DOMAIN'), $this->get_fields(), do_lang_tempcode('FORWARDING_DESCRIPTION'));
    }

    /**
     * Standard stage of pointstore item purchase.
     *
     * @return tempcode                 The UI
     */
    public function newforwarding()
    {
        if (get_option('is_on_forw_buy') == '0') {
            return new Tempcode();
        }

        $title = get_screen_title('TITLE_NEWFORWARDING');

        $member_id = get_member();

        pointstore_handle_error_already_has('forwarding');

        // What addresses are there?
        $points_left = available_points($member_id); // the number of points this member has left
        $list = get_mail_domains('forw_', $points_left);
        if ($list->is_empty()) {
            return warn_screen($title, do_lang_tempcode('NO_FORWARDINGS'));
        }

        // Build up fields
        $fields = new Tempcode();
        require_code('form_templates');
        $fields->attach(form_input_line(do_lang_tempcode('ADDRESS_DESIRED_STUB'), '', 'email-prefix', '', true));
        $fields->attach(form_input_list(do_lang_tempcode('ADDRESS_DESIRED_DOMAIN'), '', 'esuffix', $list));
        $fields->attach(form_input_line(do_lang_tempcode('ADDRESS_CURRENT'), '', 'email', $GLOBALS['FORUM_DRIVER']->get_member_email_address($member_id), true));

        // Return template
        $newfor_url = build_url(array('page' => '_SELF', 'type' => '_newforwarding', 'id' => 'forwarding'), '_SELF');
        return do_template('FORM_SCREEN', array(
            '_GUID' => '1fcc6083db18c996fabb51d0ac10bc88',
            'HIDDEN' => '',
            'TITLE' => $title,
            'ACTION' => do_lang_tempcode('TITLE_NEWFORWARDING'),
            'TEXT' => paragraph(do_lang_tempcode('ADDRESSES_ABOUT')),
            'URL' => $newfor_url,
            'SUBMIT_ICON' => 'buttons__proceed',
            'SUBMIT_NAME' => do_lang_tempcode('PURCHASE'),
            'FIELDS' => $fields,
        ));
    }

    /**
     * Standard stage of pointstore item purchase.
     *
     * @return tempcode                 The UI
     */
    public function _newforwarding()
    {
        if (get_option('is_on_forw_buy') == '0') {
            return new Tempcode();
        }

        require_code('type_validation');

        $title = get_screen_title('TITLE_NEWFORWARDING');

        // Getting User Information
        $member_id = get_member();
        $points_left = available_points($member_id);

        // So we don't need to call these big ugly names, again...
        $_suffix = post_param('esuffix');
        $prefix = post_param('email-prefix');
        $email = post_param('email');

        // Which suffix have we chosen?
        $suffix = 'forw_' . $_suffix;

        $suffix_price = get_price($suffix);
        $points_after = $points_left - $suffix_price;

        pointstore_handle_error_already_has('forwarding');

        if (($points_after < 0) && (!has_privilege(get_member(), 'give_points_self'))) {
            return warn_screen($title, do_lang_tempcode('NOT_ENOUGH_POINTS', escape_html($suffix)));
        }

        // Does the prefix contain valid characters?
        require_code('type_validation');
        if (!is_valid_email_address($prefix . '@' . $_suffix)) {
            return warn_screen($title, do_lang_tempcode('INVALID_EMAIL_PREFIX'));
        }

        // Is the email for things to be forwarded to valid?
        if (!is_valid_email_address($email)) {
            return warn_screen($title, do_lang_tempcode('INVALID_EMAIL_ADDRESS'));
        }

        pointstore_handle_error_taken($prefix, $_suffix);

        // Return
        $proceed_url = build_url(array('page' => '_SELF', 'type' => '__newforwarding', 'id' => 'forwarding'), '_SELF');
        $keep = new Tempcode();
        $keep->attach(form_input_hidden('prefix', $prefix));
        $keep->attach(form_input_hidden('suffix', $_suffix));
        $keep->attach(form_input_hidden('email', $email));
        return do_template('POINTSTORE_CONFIRM_SCREEN', array(
            '_GUID' => '2209e63206edac410bf553b96eee4782',
            'MESSAGE' => paragraph($prefix . '@' . $_suffix),
            'TITLE' => $title,
            'ACTION' => do_lang_tempcode('TITLE_NEWFORWARDING'),
            'KEEP' => $keep,
            'COST' => integer_format($suffix_price),
            'POINTS_AFTER' => integer_format($points_after),
            'PROCEED_URL' => $proceed_url,
            'CANCEL_URL' => build_url(array('page' => '_SELF'), '_SELF'),
        ));
    }

    /**
     * Standard stage of pointstore item purchase.
     *
     * @return tempcode                 The UI
     */
    public function __newforwarding()
    {
        if (get_option('is_on_forw_buy') == '0') {
            return new Tempcode();
        }

        $title = get_screen_title('TITLE_NEWFORWARDING');

        $member_id = get_member();
        $points_left = available_points($member_id); // the number of points this member has left
        $time = time();

        // So we don't need to call these big ugly names, again...
        $prefix = post_param('prefix');
        $_suffix = post_param('suffix');
        $email = post_param('email');

        $suffix = 'forw_' . $_suffix;
        $suffix_price = get_price($suffix);

        pointstore_handle_error_already_has('forwarding');

        // If the price is more than we can afford...
        if (($suffix_price > $points_left) && (!has_privilege(get_member(), 'give_points_self'))) {
            return warn_screen($title, do_lang_tempcode('NOT_ENOUGH_POINTS', escape_html($_suffix)));
        }

        pointstore_handle_error_taken($prefix, $_suffix);

        // Add us to the database
        $sale_id = $GLOBALS['SITE_DB']->query_insert('sales', array('date_and_time' => $time, 'memberid' => get_member(), 'purchasetype' => 'forwarding', 'details' => $prefix, 'details2' => '@' . $_suffix), true);

        $forw_url = get_option('forw_url');

        // Mail off the order form
        $encoded_reason = do_lang('TITLE_NEWFORWARDING');
        $message_raw = do_template('POINTSTORE_FORWARDER_MAIL', array('_GUID' => 'a09dba8b440baa5cd48d462ebfafd15f', 'ENCODED_REASON' => $encoded_reason, 'EMAIL' => $email, 'PREFIX' => $prefix, 'SUFFIX' => $_suffix, 'FORW_URL' => $forw_url, 'SUFFIX_PRICE' => integer_format($suffix_price)), null, false, null, '.txt', 'text');

        require_code('notifications');
        dispatch_notification('pointstore_request_forwarding', 'forw_' . strval($sale_id), do_lang('MAIL_REQUEST_FORWARDING', null, null, null, get_site_default_lang()), $message_raw->evaluate(get_site_default_lang(), false), null, null, 3, true, false, null, null, '', '', '', '', null, true);

        $text = do_lang_tempcode('ORDER_FORWARDER_DONE', $email, escape_html($prefix . '@' . $_suffix));
        $url = build_url(array('page' => '_SELF', 'type' => 'misc'), '_SELF');
        return redirect_screen($title, $url, $text);
    }
}
