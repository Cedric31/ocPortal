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
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_cron_ip_address_sharing
{
    /**
     * Run function for CRON hooks. Searches for tasks to perform.
     */
    public function run()
    {
        $limit = get_option('max_ip_addresses_per_subscriber');
        if ($limit == '') {
            return;
        }

        if (get_forum_type() != 'ocf') {
            return;
        }
        if (!addon_installed('stats')) {
            return;
        }
        if (is_ocf_satellite_site()) {
            return;
        }
        if (!db_has_subqueries($GLOBALS['SITE_DB']->connection_write)) {
            return;
        }

        $time = time();

        $days = 7;
        $last_time = intval(get_long_value('mail_log_last_run_time'));
        if ($last_time > $time + ($days * 24 * 60 * 60)) {
            set_long_value('mail_log_last_run_time', strval($time));

            $results = array();

            $table = 'f_usergroup_subs s JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_groups g ON g.id=s.s_group_id';
            $groups = collapse_1d_complexity('id', $GLOBALS['FORUM_DB']->query_select($table, array('g.id')));
            if (count($groups) > 0) {
                $group_or_list_1 = '';
                $group_or_list_2 = '';
                foreach ($groups as $group) {
                    if ($group_or_list_1 != '') {
                        $group_or_list_1 .= ' OR ';
                    }
                    $group_or_list_1 .= 'm_primary_group=' . strval($group);

                    if ($group_or_list_2 != '') {
                        $group_or_list_2 .= ' OR ';
                    }
                    $group_or_list_2 .= 'gm_group_id=' . strval($group);
                }

                $sql = 'SELECT DISTINCT id,m_username FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members m ';
                $sql .= 'LEFT JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_group_members g ON m.id=g.gm_member_id AND (' . $group_or_list_2 . ')';
                $sql .= 'WHERE ';
                $sql .= '(' . $group_or_list_1 . ' OR gm_validated=1) ';
                $sql .= 'AND (SELECT COUNT(DISTINCT ip) FROM ' . get_table_prefix() . 'stats s WHERE s.member_id=m.id AND date_and_time>' . strval($time - 60 * 60 * 24) . ')>' . strval(intval($limit));
                $members = $GLOBALS['FORUM_DB']->query($sql);

                foreach ($members as $member) {
                    $_ips = $GLOBALS['SITE_DB']->query_select('stats', array('ip', 'COUNT(*) AS cnt'), array('member_id' => $member['id']), ' AND date_and_time>' . strval($time - 60 * 60 * 24) . ' GROUP BY ip');
                    $ips = array();
                    foreach ($_ips as $ip) {
                        $ips[] = array(
                            $ip['ip'],
                            $ip['cnt'],
                            gethostbyaddr($ip['ip']),
                        );
                    }

                    $results[] = array(
                        $member['id'],
                        $member['m_username'],
                        array_intersect($GLOBALS['FORUM_DRIVER']->get_members_groups($member['id']), $groups),
                        $ips,
                    );
                }
            }

            if (count($results) > 0) {
                require_code('ocf_groups');

                $table = "{|\n";
                $table .= "! " . do_lang('USERNAME') . "\n";
                $table .= "! " . do_lang('GROUPS') . "\n";
                $table .= "! " . do_lang('IP_ADDRESSES') . "\n";
                foreach ($results as $result) {
                    $table .= "|-\n";
                    $table .= "| {{" . $result[1] . "}}\n";
                    $table .= "| ";
                    foreach ($result[2] as $i => $group_id) {
                        if ($i != 0) {
                            $table .= ', ';
                        }
                        $table .= ocf_get_group_name($group_id, false);
                    }
                    $table .= "\n";
                    $table .= "| ";
                    foreach ($result[3] as $i => $ip_address) {
                        if ($i != 0) {
                            $table .= "\n";
                        }
                        $table .= $ip_address[0] . '&times;' . strval($ip_address[1]) . ' (' . $ip_address[2] . ')';
                    }
                    $table .= "\n";
                }
                $table .= "|}";

                $subject = do_lang('MAIL_IP_ADDRESS_REPORT_SUBJECT', integer_format(intval($limit)));
                $message = do_lang('MAIL_IP_ADDRESS_REPORT_BODY', integer_format(intval($limit)), $table);

                require_code('notifications');
                dispatch_notification('ip_address_sharing', null, $subject, $message);
            }
        }
    }
}
