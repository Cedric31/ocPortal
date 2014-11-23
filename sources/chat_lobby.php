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
 * @package    chat
 */

/**
 * Enter the current member to the chat lobby / Mark them available.
 */
function enter_chat_lobby()
{
    if (is_guest()) {
        return;
    }

    require_css('chat');
    require_lang('chat');
    require_code('chat');
    require_javascript('ajax');
    require_javascript('chat');
    require_javascript('sound');

    if ((!array_key_exists(get_member(), get_chatters_in_room(null))) && (!is_invisible())) {
        $GLOBALS['SITE_DB']->query_insert('chat_active', array('member_id' => get_member(), 'date_and_time' => time(), 'room_id' => null));

        $GLOBALS['SITE_DB']->query_insert('chat_events', array(
            'e_type_code' => 'BECOME_ACTIVE',
            'e_member_id' => get_member(),
            'e_room_id' => null,
            'e_date_and_time' => time()
        ));
    }
}

/**
 * Show IM contacts, with online/offline status and clickability to initiate IM sessions.
 *
 * @param  ?MEMBER                      The member ID (null: current user).
 * @param  boolean                      Whether to show a simpler, more compact, UI.
 * @param  ?integer                     Maximum to show (null: default).
 * @return tempcode                     The contact UI.
 */
function show_im_contacts($member_id = null, $simpler = false, $max = null)
{
    require_code('chat');
    require_lang('chat');
    require_code('users2');

    if (is_null($max)) {
        $max = intval(get_option('max_chat_lobby_friends'));
    }

    if (is_null($member_id)) {
        $member_id = get_member();
    }

    $can_im = has_privilege(get_member(), 'start_im');

    $online_url = $GLOBALS['FORUM_DRIVER']->users_online_url();
    $friends_offline = array();
    $friends_online = array();
    $friend_rows = $GLOBALS['SITE_DB']->query_select('chat_friends', array('member_liked'), array('member_likes' => $member_id), 'ORDER BY date_and_time', 300);
    $friend_active = get_chatters_in_room(null);
    $users_online_time_seconds = CHAT_ACTIVITY_PRUNE;
    foreach ($friend_rows as $friend) {
        if ((array_key_exists($friend['member_liked'], $friend_active)) && (!member_blocked(get_member(), $friend['member_liked']))) {
            $online_text = do_lang_tempcode('ACTIVE');
            $online = true;
        } else {
            require_code('users2');
            $online = member_is_online($friend['member_liked']);
            $online_text = $online ? do_lang_tempcode('ONLINE') : do_lang_tempcode('OFFLINE');
        }
        $username = array_key_exists($friend['member_liked'], $friend_active) ? $friend_active[$friend['member_liked']] : $GLOBALS['FORUM_DRIVER']->get_username($friend['member_liked']);
        if (!is_null($username)) {
            $member_profile_url = $GLOBALS['FORUM_DRIVER']->member_profile_url($friend['member_liked'], true, true);

            $friend = array(
                /*'DATE_AND_TIME_RAW'=>strval($friend['date_and_time']),
                    'DATE_AND_TIME'=>get_timezoned_date($friend['date_and_time'],false),*/
                'MEMBER_PROFILE_URL' => $member_profile_url,
                'MEMBER_ID' => strval($friend['member_liked']),
                'USERNAME' => $username,
                'ONLINE_TEXT' => $online_text,
                'ONLINE' => $online,
            );

            if ($online) {
                $friends_online[] = $friend;
            } else {
                $friends_offline[] = $friend;
            }
        }
    }

    if (count($friends_online) + count($friends_offline) > $max) {
        $friends = $friends_online;
    } else {
        $friends = array_merge($friends_offline, $friends_online);
    }

    return do_template('CHAT_FRIENDS', array('_GUID' => '57397daa0c000ea589e3a7a5fd323110', 'FRIENDS' => $friends,
        'FRIENDS_ONLINE' => $friends_online,
        'FRIENDS_OFFLINE' => $friends_offline,
        'CAN_IM' => $can_im,
        'ONLINE_URL' => $online_url,
        'SIMPLER' => $simpler,
    ));
}

/**
 * Prune timed-out private chatrooms.
 *
 * @param  array                        The row of the chat room to possibly prune
 * @return boolean                      Whether the room was pruned
 */
function handle_chatroom_pruning($row)
{
    $deletion_time = intval(get_option('chat_private_room_deletion_time'));
    if ($deletion_time == 0) {
        return false;
    }
    if (($row['allow_list'] != '') || (!is_null($row['room_owner']))) {
        // As this is a private chatroom, we need to delete it if it has been idle for too long ;-)
        $message = $GLOBALS['SITE_DB']->query_select('chat_messages', array('date_and_time'), array('room_id' => $row['id']), 'ORDER BY date_and_time DESC', 1);
        if ((isset($message[0])) && (($message[0]['date_and_time'] + ($deletion_time * 60)) <= time())) {
            // Delete the room and its messages
            $GLOBALS['SITE_DB']->query_delete('chat_rooms', array('id' => $row['id']), '', 1);
            require_code('chat2');
            delete_chat_messages(array('room_id' => $row['id']));
            return true;
        }
    }
    return false;
}
