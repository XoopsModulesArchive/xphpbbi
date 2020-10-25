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
 *                                login.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: login.php,v 1.8 2004/12/03 23:51:42 blackdeath_csmc Exp $
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
 ***************************************************************************/

//
// Allow people to reach login page if
// board is shut down
//
define('IN_LOGIN', true);

define('IN_PHPBB', true);
$phpbb_root_path = './';
include $phpbb_root_path . 'common.php';

//
// Set page ID for session management
//
$userdata = session_pagestart($user_ip, PAGE_LOGIN);
init_userprefs($userdata);
//
// End session management
//

if (isset($_POST['login']) || isset($_GET['login']) || isset($_POST['logout']) || isset($_GET['logout'])) {
    if ((isset($_POST['login']) || isset($_GET['login'])) && !is_object($xoopsUser)) {
        $username = isset($_POST['username']) ? trim(htmlspecialchars($_POST['username'], ENT_QUOTES | ENT_HTML5)) : '';
        $username = substr(str_replace("\\'", "'", $username), 0, 25);
        $username = str_replace("'", "\\'", $username);
        $password = $_POST['password'] ?? '';

        $sql = 'SELECT uid, uname, pass, level, user_level
			FROM ' . USERS_TABLE . "
			WHERE uname = '" . str_replace("\\'", "''", $username) . "'";
        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Error in obtaining userdata', '', __LINE__, __FILE__, $sql);
        }

        if ($row = $db->sql_fetchrow($result)) {
            if ($row['user_level'] != ADMIN && $board_config['board_disable']) {
                redirect(append_sid('index.php', true));
            } else {
                if (md5($password) == $row['pass'] && $row['level']) {
                    //<<<---------------------------------------------
                    //-+- Redirect to XOOPS login system -- Koudanshi
                    //<<<---------------------------------------------
                    $autologin = (isset($_POST['autologin'])) ? 'On' : '';
                    @header('location: ' . XOOPS_URL . '/user.php?op=login&AutoLogin=' . $autologin . '&uname=' . $username . '&pass=' . md5($password) . '');
                    />>>---------------------------------------------
                } else {
                    $redirect = (!empty($_POST['redirect'])) ? str_replace('&amp;', '&', htmlspecialchars($_POST['redirect'], ENT_QUOTES | ENT_HTML5)) : 'index.php';
                    $redirect = str_replace('?', '&', $redirect);

                    $template->assign_vars(
                        [
                            'META' => "<meta http-equiv=\"refresh\" content=\"3;url=login.php?redirect=$redirect\">",
                        ]
                    );

                    $message = $lang['Error_login'] . '<br><br>' . sprintf($lang['Click_return_login'], "<a href=\"login.php?redirect=$redirect\">", '</a>') . '<br><br>' . sprintf($lang['Click_return_index'], '<a href="' . append_sid('index.php') . '">', '</a>');

                    message_die(GENERAL_MESSAGE, $message);
                }
            }
        } else {
            $redirect = (!empty($_POST['redirect'])) ? str_replace('&amp;', '&', htmlspecialchars($_POST['redirect'], ENT_QUOTES | ENT_HTML5)) : 'index.php';
            $redirect = str_replace('?', '&', $redirect);

            $template->assign_vars(
                [
                    'META' => "<meta http-equiv=\"refresh\" content=\"3;url=login.php?redirect=$redirect\">",
                ]
            );

            $message = $lang['Error_login'] . '<br><br>' . sprintf($lang['Click_return_login'], "<a href=\"login.php?redirect=$redirect\">", '</a>') . '<br><br>' . sprintf($lang['Click_return_index'], '<a href="' . append_sid('index.php') . '">', '</a>');

            message_die(GENERAL_MESSAGE, $message);
        }
    } elseif ((isset($_GET['logout']) || isset($_POST['logout'])) && is_object($xoopsUser)) {
        //<<<---------------------------------------------
        //-+- Redirect to XOOPS logout system -- Koudanshi
        //<<<---------------------------------------------
        @header('location: ' . XOOPS_URL . '/user.php?op=logout');
        />>>---------------------------------------------
    } else {
        $url = (!empty($_POST['redirect'])) ? str_replace('&amp;', '&', htmlspecialchars($_POST['redirect'], ENT_QUOTES | ENT_HTML5)) : 'index.php';
        redirect(append_sid($url, true));
    }
} else {
    //
    // Do a full login page dohickey if
    // user not already logged in
    //
    if (!is_object($xoopsUser)) {
        $page_title = $lang['Login'];
        include $phpbb_root_path . 'includes/page_header.php';

        $template->set_filenames(
            [
                'body' => 'login_body.tpl',
            ]
        );

        if (isset($_POST['redirect']) || isset($_GET['redirect'])) {
            $forward_to = $HTTP_SERVER_VARS['QUERY_STRING'];

            if (preg_match("/^redirect=([a-z0-9\.#\/\?&=\+\-_]+)/si", $forward_to, $forward_matches)) {
                $forward_to    = (!empty($forward_matches[3])) ? $forward_matches[3] : $forward_matches[1];
                $forward_match = explode('&', $forward_to);

                if (count($forward_match) > 1) {
                    $forward_page = '';

                    for ($i = 1, $iMax = count($forward_match); $i < $iMax; $i++) {
                        if (!preg_match('sid=', $forward_match[$i])) {
                            if ($forward_page != '') {
                                $forward_page .= '&';
                            }
                            $forward_page .= $forward_match[$i];
                        }
                    }
                    $forward_page = $forward_match[0] . '?' . $forward_page;
                } else {
                    $forward_page = $forward_match[0];
                }
            }
        } else {
            $forward_page = '';
        }

        $username = ($userdata['uid'] != ANONYMOUS) ? $userdata['uname'] : '';

        $s_hidden_fields = '<input type="hidden" name="redirect" value="' . $forward_page . '">';

        phpbbi_make_jumpbox('viewforum.php', $forum_id);
        $template->assign_vars(
            [
                'USERNAME' => $username,

                'L_ENTER_PASSWORD' => $lang['Enter_password'],
                'L_SEND_PASSWORD'  => $lang['Forgotten_password'],

                'U_SEND_PASSWORD' => append_sid('profile.php?mode=sendpassword'),

                'S_HIDDEN_FIELDS' => $s_hidden_fields,
            ]
        );

        $template->pparse('body');

        include $phpbb_root_path . 'includes/page_tail.php';
    } else {
        redirect(append_sid('index.php', true));
    }
}


