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
 *                             usercp_email.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: usercp_email.php,v 1.8 2004/12/03 23:51:43 blackdeath_csmc Exp $
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

// Is send through board enabled? No, return to index
if (!$board_config['board_email_form']) {
    redirect(append_sid('index.php', true));
}

if (!empty($_GET[POST_USERS_URL]) || !empty($_POST[POST_USERS_URL])) {
    $user_id = (!empty($_GET[POST_USERS_URL])) ? (int)$_GET[POST_USERS_URL] : (int)$_POST[POST_USERS_URL];
} else {
    message_die(GENERAL_MESSAGE, $lang['No_user_specified']);
}

if (!is_object($xoopsUser)) {
    redirect(append_sid('login.php?redirect=profile.php&mode=email&' . POST_USERS_URL . "=$user_id", true));
}

$sql = 'SELECT uname, email, user_viewemail, user_lang
	FROM ' . USERS_TABLE . "
	WHERE uid = $user_id";
if ($result = $db->sql_query($sql)) {
    $row = $db->sql_fetchrow($result);

    $username = $row['uname'];

    $user_email = $row['email'];

    $user_lang = $row['user_lang'];

    if ($row['user_viewemail'] || ADMIN == $userdata['user_level']) {
        if (time() - $userdata['user_emailtime'] < $board_config['flood_interval']) {
            message_die(GENERAL_MESSAGE, $lang['Flood_email_limit']);
        }

        if (isset($_POST['submit'])) {
            $error = false;

            if (!empty($_POST['subject'])) {
                $subject = trim(stripslashes($_POST['subject']));
            } else {
                $error = true;

                $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $lang['Empty_subject_email'] : $lang['Empty_subject_email'];
            }

            if (!empty($_POST['message'])) {
                $message = trim(stripslashes($_POST['message']));
            } else {
                $error = true;

                $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $lang['Empty_message_email'] : $lang['Empty_message_email'];
            }

            if (!$error) {
                $sql = 'UPDATE ' . USERS_TABLE . '
					SET user_emailtime = ' . time() . '
					WHERE uid = ' . $userdata['uid'];

                if ($result = $db->sql_query($sql)) {
                    include $phpbb_root_path . 'includes/emailer.php';

                    $emailer = new emailer($board_config['smtp_delivery']);

                    $emailer->from($userdata['email']);

                    $emailer->replyto($userdata['email']);

                    $email_headers = 'X-AntiAbuse: Board servername - ' . $server_name . "\n";

                    $email_headers .= 'X-AntiAbuse: User_id - ' . $userdata['uid'] . "\n";

                    $email_headers .= 'X-AntiAbuse: Username - ' . $userdata['uname'] . "\n";

                    $email_headers .= 'X-AntiAbuse: User IP - ' . decode_ip($user_ip) . "\n";

                    $emailer->use_template('profile_send_email', $user_lang);

                    $emailer->email_address($user_email);

                    $emailer->set_subject($subject);

                    $emailer->extra_headers($email_headers);

                    $emailer->assign_vars(
                        [
                            'SITENAME' => $board_config['sitename'],
                            'BOARD_EMAIL' => $board_config['board_email'],
                            'FROM_USERNAME' => $userdata['uname'],
                            'TO_USERNAME' => $username,
                            'MESSAGE' => $message,
                        ]
                    );

                    $emailer->send();

                    $emailer->reset();

                    if (!empty($_POST['cc_email'])) {
                        $emailer->from($userdata['email']);

                        $emailer->replyto($userdata['email']);

                        $emailer->use_template('profile_send_email');

                        $emailer->email_address($userdata['email']);

                        $emailer->set_subject($subject);

                        $emailer->assign_vars(
                            [
                                'SITENAME' => $board_config['sitename'],
                                'BOARD_EMAIL' => $board_config['board_email'],
                                'FROM_USERNAME' => $userdata['uname'],
                                'TO_USERNAME' => $username,
                                'MESSAGE' => $message,
                            ]
                        );

                        $emailer->send();

                        $emailer->reset();
                    }

                    $template->assign_vars(
                        [
                            'META' => '<meta http-equiv="refresh" content="5;url=' . append_sid('index.php') . '">',
                        ]
                    );

                    $message = $lang['Email_sent'] . '<br><br>' . sprintf($lang['Click_return_index'], '<a href="' . append_sid('index.php') . '">', '</a>');

                    message_die(GENERAL_MESSAGE, $message);
                } else {
                    message_die(GENERAL_ERROR, 'Could not update last email time', '', __LINE__, __FILE__, $sql);
                }
            }
        }

        include $phpbb_root_path . 'includes/page_header.php';

        $template->set_filenames(
            [
                'body' => 'profile_send_email.tpl',
            ]
        );

        phpbbi_make_jumpbox('viewforum.php');

        if ($error) {
            $template->set_filenames(
                [
                    'reg_header' => 'error_body.tpl',
                ]
            );

            $template->assign_vars(
                [
                    'ERROR_MESSAGE' => $error_msg,
                ]
            );

            $template->assign_var_from_handle('ERROR_BOX', 'reg_header');
        }

        $template->assign_vars(
            [
                'USERNAME' => $username,

                'S_HIDDEN_FIELDS' => '',
                'S_POST_ACTION' => append_sid('profile.php?mode=email&amp;' . POST_USERS_URL . "=$user_id"),

                'L_SEND_EMAIL_MSG' => $lang['Send_email_msg'],
                'L_RECIPIENT' => $lang['Recipient'],
                'L_SUBJECT' => $lang['Subject'],
                'L_MESSAGE_BODY' => $lang['Message_body'],
                'L_MESSAGE_BODY_DESC' => $lang['Email_message_desc'],
                'L_EMPTY_SUBJECT_EMAIL' => $lang['Empty_subject_email'],
                'L_EMPTY_MESSAGE_EMAIL' => $lang['Empty_message_email'],
                'L_OPTIONS' => $lang['Options'],
                'L_CC_EMAIL' => $lang['CC_email'],
                'L_SPELLCHECK' => $lang['Spellcheck'],
                'L_SEND_EMAIL' => $lang['Send_email'],
            ]
        );

        $template->pparse('body');

        include $phpbb_root_path . 'includes/page_tail.php';
    } else {
        message_die(GENERAL_MESSAGE, $lang['User_prevent_email']);
    }
} else {
    message_die(GENERAL_MESSAGE, $lang['User_not_exist']);
}
