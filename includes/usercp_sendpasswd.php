<?php

/***************************************************************************
 *    Title: xphpBBi - phpBB Complete Integration project
 *    Description: Project to turn the phpBB forums into a fully functioning
 *        Xoops2 Module. Based on the phpBB 2.0.9 port from BBpixel.com -
 *        (PBBoard v1.22).
 *    Credits: Xoops2 Module Development Team - Koudanshi - phpBB Group
 *    Thanks: To Koudanshi for a great start in his port, and phpBB Group for
 *        such a great forum software.
 *    License: GNU/GPL version 2 or later.
 ****************************************************************************/

/***************************************************************************
 *                           usercp_sendpasswd.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: usercp_sendpasswd.php,v 1.8 2004/12/03 23:51:43 blackdeath_csmc Exp $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

if (!defined('IN_PHPBB')) {
    die('Hacking attempt');

    exit;
}

if (isset($_POST['submit'])) {
    $username = (!empty($_POST['username'])) ? trim(strip_tags($_POST['username'])) : '';

    $email = (!empty($_POST['email'])) ? trim(strip_tags(htmlspecialchars($_POST['email'], ENT_QUOTES | ENT_HTML5))) : '';

    $sql = 'SELECT uid, uname, email, level, user_lang
		FROM ' . USERS_TABLE . "
		WHERE email = '" . str_replace("\'", "''", $email) . "'
			AND uname = '" . str_replace("\'", "''", $username) . "'";

    if ($result = $db->sql_query($sql)) {
        if ($row = $db->sql_fetchrow($result)) {
            if (!$row['level']) {
                message_die(GENERAL_MESSAGE, $lang['No_send_account_inactive']);
            }

            $username = $row['uname'];

            $user_id = $row['uid'];

            $user_actkey = gen_rand_string(true);

            $key_len = 54 - mb_strlen($server_url);

            $key_len = ($str_len > 6) ? $key_len : 6;

            $user_actkey = mb_substr($user_actkey, 0, $key_len);

            $user_password = gen_rand_string(false);

            $sql = 'UPDATE ' . USERS_TABLE . "
				SET user_newpasswd = '" . md5($user_password) . "', actkey = '$user_actkey'
				WHERE uid = " . $row['uid'];

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not update new password information', '', __LINE__, __FILE__, $sql);
            }

            include $phpbb_root_path . 'includes/emailer.php';

            $emailer = new emailer($board_config['smtp_delivery']);

            $emailer->from($board_config['board_email']);

            $emailer->replyto($board_config['board_email']);

            $emailer->use_template('user_activate_passwd', $row['user_lang']);

            $emailer->email_address($row['email']);

            $emailer->set_subject($lang['New_password_activation']);

            $emailer->assign_vars(
                [
                    'SITENAME' => $board_config['sitename'],
                    'USERNAME' => $username,
                    'PASSWORD' => $user_password,
                    'EMAIL_SIG' => (!empty($board_config['board_email_sig'])) ? str_replace('<br>', "\n", "-- \n" . $board_config['board_email_sig']) : '',

                    'U_ACTIVATE' => $server_url . '?mode=activate&' . POST_USERS_URL . '=' . $user_id . '&act_key=' . $user_actkey,
                ]
            );

            $emailer->send();

            $emailer->reset();

            $template->assign_vars(
                [
                    'META' => '<meta http-equiv="refresh" content="15;url=' . append_sid('index.php') . '">',
                ]
            );

            $message = $lang['Password_updated'] . '<br><br>' . sprintf($lang['Click_return_index'], '<a href="' . append_sid('index.php') . '">', '</a>');

            message_die(GENERAL_MESSAGE, $message);
        } else {
            message_die(GENERAL_MESSAGE, $lang['No_email_match']);
        }
    } else {
        message_die(GENERAL_ERROR, 'Could not obtain user information for sendpassword', '', __LINE__, __FILE__, $sql);
    }
} else {
    $username = '';

    $email = '';
}

//
// Output basic page
//
include $phpbb_root_path . 'includes/page_header.php';

$template->set_filenames(
    [
        'body' => 'profile_send_pass.tpl',
    ]
);
phpbbi_make_jumpbox('viewforum.php');

$template->assign_vars(
    [
        'USERNAME' => $username,
        'EMAIL' => $email,

        'L_SEND_PASSWORD' => $lang['Send_password'],
        'L_ITEMS_REQUIRED' => $lang['Items_required'],
        'L_EMAIL_ADDRESS' => $lang['Email_address'],
        'L_SUBMIT' => $lang['Submit'],
        'L_RESET' => $lang['Reset'],

        'S_HIDDEN_FIELDS' => '',
        'S_PROFILE_ACTION' => append_sid('profile.php?mode=sendpassword'),
    ]
);

$template->pparse('body');

include $phpbb_root_path . 'includes/page_tail.php';
