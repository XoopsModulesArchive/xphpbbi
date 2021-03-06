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
 *                            usercp_register.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: usercp_register.php,v 1.9 2004/12/03 23:51:43 blackdeath_csmc Exp $
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

$unhtml_specialchars_match   = ['#&gt;#', '#&lt;#', '#&quot;#', '#&amp;#'];
$unhtml_specialchars_replace = ['>', '<', '"', '&'];

// ---------------------------------------
// Load agreement template since user has not yet
// agreed to registration conditions/coppa
//
function show_coppa()
{
    global $userdata, $template, $lang, $phpbb_root_path;
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $template->set_filenames(
        [
            'body' => 'agreement.tpl',
        ]
    );

    $template->assign_vars(
        [
            'REGISTRATION'   => $lang['Registration'],
            'AGREEMENT'      => $lang['Reg_agreement'],
            'AGREE_OVER_13'  => $lang['Agree_over_13'],
            'AGREE_UNDER_13' => $lang['Agree_under_13'],
            'DO_NOT_AGREE'   => $lang['Agree_not'],

            'U_AGREE_OVER13'  => append_sid('profile.php?mode=register&amp;agreed=true'),
            'U_AGREE_UNDER13' => append_sid('profile.php?mode=register&amp;agreed=true&amp;coppa=true'),
        ]
    );

    $template->pparse('body');
}

//
// ---------------------------------------

$error      = false;
$page_title = ($mode == 'editprofile') ? $lang['Edit_profile'] : $lang['Register'];

if ($mode == 'register' && !isset($_POST['agreed']) && !isset($_GET['agreed'])) {
    include $phpbb_root_path . 'includes/page_header.php';

    show_coppa();

    include $phpbb_root_path . 'includes/page_tail.php';
}

$coppa = (empty($_POST['coppa']) && empty($_GET['coppa'])) ? 0 : true;

//
// Check and initialize some variables if needed
//
if (isset($_POST['submit']) || isset($_POST['avatargallery']) || isset($_POST['submitavatar']) || isset($_POST['cancelavatar']) || $mode == 'register') {
    include $phpbb_root_path . 'includes/functions_validate.php';
    include $phpbb_root_path . 'includes/bbcode.php';
    include $phpbb_root_path . 'includes/functions_post.php';

    if ($mode == 'editprofile') {
        $user_id       = (int)$_POST['user_id'];
        $current_email = trim(htmlspecialchars($_POST['current_email'], ENT_QUOTES | ENT_HTML5));
    }

    $strip_var_list = ['username' => 'username', 'email' => 'email', 'icq' => 'icq', 'aim' => 'aim', 'msn' => 'msn', 'yim' => 'yim', 'website' => 'website', 'location' => 'location', 'occupation' => 'occupation', 'interests' => 'interests'];

    // Strip all tags from data ... may p**s some people off, bah, strip_tags is
    // doing the job but can still break HTML output ... have no choice, have
    // to use htmlspecialchars ... be prepared to be moaned at.
    while (list($var, $param) = @each($strip_var_list)) {
        if (!empty($_POST[$param])) {
            $$var = trim(htmlspecialchars($_POST[$param], ENT_QUOTES | ENT_HTML5));
        }
    }

    $trim_var_list = ['cur_password' => 'cur_password', 'new_password' => 'new_password', 'password_confirm' => 'password_confirm', 'signature' => 'signature'];

    while (list($var, $param) = @each($trim_var_list)) {
        if (!empty($_POST[$param])) {
            $$var = trim($_POST[$param]);
        }
    }

    $signature = str_replace('<br>', "\n", $signature);

    // Run some validation on the optional fields. These are pass-by-ref, so they'll be changed to
    // empty strings if they fail.
    validate_optional_fields($icq, $aim, $msn, $yim, $website, $location, $occupation, $interests, $signature);

    $viewemail       = (isset($_POST['viewemail'])) ? (($_POST['viewemail']) ? true : 0) : 0;
    $allowviewonline = (isset($_POST['hideonline'])) ? (($_POST['hideonline']) ? 0 : true) : true;
    $notifyreply     = (isset($_POST['notifyreply'])) ? (($_POST['notifyreply']) ? true : 0) : 0;
    $notifypm        = (isset($_POST['notifypm'])) ? (($_POST['notifypm']) ? true : 0) : true;
    $popup_pm        = (isset($_POST['popup_pm'])) ? (($_POST['popup_pm']) ? true : 0) : true;

    if ($mode == 'register') {
        $attachsig = (isset($_POST['attachsig'])) ? (($_POST['attachsig']) ? true : 0) : $board_config['allow_sig'];

        $allowhtml    = (isset($_POST['allowhtml'])) ? (($_POST['allowhtml']) ? true : 0) : $board_config['allow_html'];
        $allowbbcode  = (isset($_POST['allowbbcode'])) ? (($_POST['allowbbcode']) ? true : 0) : $board_config['allow_bbcode'];
        $allowsmilies = (isset($_POST['allowsmilies'])) ? (($_POST['allowsmilies']) ? true : 0) : $board_config['allow_smilies'];
    } else {
        $attachsig = (isset($_POST['attachsig'])) ? (($_POST['attachsig']) ? true : 0) : 0;

        $allowhtml    = (isset($_POST['allowhtml'])) ? (($_POST['allowhtml']) ? true : 0) : $userdata['user_allowhtml'];
        $allowbbcode  = (isset($_POST['allowbbcode'])) ? (($_POST['allowbbcode']) ? true : 0) : $userdata['user_allowbbcode'];
        $allowsmilies = (isset($_POST['allowsmilies'])) ? (($_POST['allowsmilies']) ? true : 0) : $userdata['user_allowsmile'];
    }

    $user_style = (isset($_POST['style'])) ? (int)$_POST['style'] : $board_config['default_style'];

    if (!empty($_POST['language'])) {
        if (preg_match('/^[a-z_]+$/i', $_POST['language'])) {
            $user_lang = htmlspecialchars($_POST['language'], ENT_QUOTES | ENT_HTML5);
        } else {
            $error     = true;
            $error_msg = $lang['Fields_empty'];
        }
    } else {
        $user_lang = $board_config['default_lang'];
    }

    $user_timezone = (isset($_POST['timezone'])) ? (float)$_POST['timezone'] : $board_config['board_timezone'];

    $sql = 'SELECT config_value
		FROM ' . CONFIG_TABLE . "
		WHERE config_name = 'default_dateformat'";
    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not select default dateformat', '', __LINE__, __FILE__, $sql);
    }
    $row                                = $db->sql_fetchrow($result);
    $board_config['default_dateformat'] = $row['config_value'];
    $user_dateformat                    = (!empty($_POST['dateformat'])) ? trim(htmlspecialchars($_POST['dateformat'], ENT_QUOTES | ENT_HTML5)) : $board_config['default_dateformat'];

    $user_avatar_local     = (isset($_POST['avatarselect']) && !empty($_POST['submitavatar']) && $board_config['allow_avatar_local']) ? htmlspecialchars($_POST['avatarselect'], ENT_QUOTES | ENT_HTML5) : ((isset($_POST['avatarlocal'])) ? htmlspecialchars(
        $_POST['avatarlocal'],
        ENT_QUOTES | ENT_HTML5
    ) : '');
    $user_avatar_remoteurl = (!empty($_POST['avatarremoteurl'])) ? trim(htmlspecialchars($_POST['avatarremoteurl'], ENT_QUOTES | ENT_HTML5)) : '';
    $user_avatar_upload    = (!empty($_POST['avatarurl'])) ? trim($_POST['avatarurl']) : (($HTTP_POST_FILES['avatar']['tmp_name'] != 'none') ? $HTTP_POST_FILES['avatar']['tmp_name'] : '');
    $user_avatar_name      = (!empty($HTTP_POST_FILES['avatar']['name'])) ? $HTTP_POST_FILES['avatar']['name'] : '';
    $user_avatar_size      = (!empty($HTTP_POST_FILES['avatar']['size'])) ? $HTTP_POST_FILES['avatar']['size'] : 0;
    $user_avatar_filetype  = (!empty($HTTP_POST_FILES['avatar']['type'])) ? $HTTP_POST_FILES['avatar']['type'] : '';

    $user_avatar      = (empty($user_avatar_loc) && $mode == 'editprofile') ? $userdata['user_avatar'] : '';
    $user_avatar_type = (empty($user_avatar_loc) && $mode == 'editprofile') ? $userdata['user_avatar_type'] : '';

    if ((isset($_POST['avatargallery']) || isset($_POST['submitavatar']) || isset($_POST['cancelavatar'])) && (!isset($_POST['submit']))) {
        $username         = stripslashes($username);
        $email            = stripslashes($email);
        $cur_password     = htmlspecialchars(stripslashes($cur_password), ENT_QUOTES | ENT_HTML5);
        $new_password     = htmlspecialchars(stripslashes($new_password), ENT_QUOTES | ENT_HTML5);
        $password_confirm = htmlspecialchars(stripslashes($password_confirm), ENT_QUOTES | ENT_HTML5);

        $icq = stripslashes($icq);
        $aim = stripslashes($aim);
        $msn = stripslashes($msn);
        $yim = stripslashes($yim);

        $website    = stripslashes($website);
        $location   = stripslashes($location);
        $occupation = stripslashes($occupation);
        $interests  = stripslashes($interests);
        $signature  = stripslashes($signature);

        $user_lang       = stripslashes($user_lang);
        $user_dateformat = stripslashes($user_dateformat);

        if (!isset($_POST['cancelavatar'])) {
            $user_avatar      = $user_avatar_local;
            $user_avatar_type = USER_AVATAR_GALLERY;
        }
    }
}

//
// Let's make sure the user isn't logged in while registering,
// and ensure that they were trying to register a second time
// (Prevents double registrations)
//
if ($mode == 'register' && (is_object($xoopsUser) || $username == $userdata['uname'])) {
    message_die(GENERAL_MESSAGE, $lang['Username_taken'], '', __LINE__, __FILE__);
}

//
// Did the user submit? In this case build a query to update the users profile in the DB
//
if (isset($_POST['submit'])) {
    include $phpbb_root_path . 'includes/usercp_avatar.php';

    $passwd_sql = '';
    if ($mode == 'editprofile') {
        if ($user_id != $userdata['uid']) {
            $error     = true;
            $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $lang['Wrong_Profile'];
        }
    } elseif ($mode == 'register') {
        if (empty($username) || empty($new_password) || empty($password_confirm) || empty($email)) {
            $error     = true;
            $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $lang['Fields_empty'];
        }
    }

    $passwd_sql = '';
    if (!empty($new_password) && !empty($password_confirm)) {
        if ($new_password != $password_confirm) {
            $error     = true;
            $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $lang['Password_mismatch'];
        } elseif (strlen($new_password) > 32) {
            $error     = true;
            $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $lang['Password_long'];
        } else {
            if ($mode == 'editprofile') {
                $sql = 'SELECT pass
					FROM ' . USERS_TABLE . "
					WHERE uid = $user_id";
                if (!($result = $db->sql_query($sql))) {
                    message_die(GENERAL_ERROR, 'Could not obtain user_password information', '', __LINE__, __FILE__, $sql);
                }

                $row = $db->sql_fetchrow($result);

                if ($row['pass'] != md5($cur_password)) {
                    $error     = true;
                    $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $lang['Current_password_mismatch'];
                }
            }

            if (!$error) {
                $new_password = md5($new_password);
                $passwd_sql   = "pass = '$new_password', ";
            }
        }
    } elseif ((empty($new_password) && !empty($password_confirm)) || (!empty($new_password) && empty($password_confirm))) {
        $error     = true;
        $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $lang['Password_mismatch'];
    }

    //
    // Do a ban check on this email address
    //
    if ($email != $userdata['email'] || $mode == 'register') {
        $result = validate_email($email);
        if ($result['error']) {
            $email = $userdata['email'];

            $error     = true;
            $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $result['error_msg'];
        }

        if ($mode == 'editprofile') {
            $sql = 'SELECT pass
				FROM ' . USERS_TABLE . "
				WHERE uid = $user_id";
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not obtain user_password information', '', __LINE__, __FILE__, $sql);
            }

            $row = $db->sql_fetchrow($result);

            if ($row['pass'] != md5($cur_password)) {
                $email = $userdata['email'];

                $error     = true;
                $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $lang['Current_password_mismatch'];
            }
        }
    }

    $username_sql = '';
    if ($board_config['allow_namechange'] || $mode == 'register') {
        if (empty($username)) {
            // Error is already triggered, since one field is empty.
            $error = true;
        } elseif ($username != $userdata['uname'] || $mode == 'register') {
            if (strtolower($username) != strtolower($userdata['uname'])) {
                $result = validate_username($username);
                if ($result['error']) {
                    $error     = true;
                    $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $result['error_msg'];
                }
            }

            if (!$error) {
                $username_sql = "username = '" . str_replace("\'", "''", $username) . "', ";
            }
        }
    }

    if ($signature != '') {
        if (strlen($signature) > $board_config['max_sig_chars']) {
            $error     = true;
            $error_msg .= ((isset($error_msg)) ? '<br>' : '') . $lang['Signature_too_long'];
        }

        if ($signature_bbcode_uid == '') {
            $signature_bbcode_uid = ($allowbbcode) ? make_bbcode_uid() : '';
        }
        $signature = prepare_message($signature, $allowhtml, $allowbbcode, $allowsmilies, $signature_bbcode_uid);
    }

    if ($website != '') {
        rawurlencode($website);
    }

    $avatar_sql = '';

    if (isset($_POST['avatardel']) && $mode == 'editprofile') {
        $avatar_sql = user_avatar_delete($userdata['user_avatar_type'], $userdata['user_avatar']);
    }

    if ((!empty($user_avatar_upload) || !empty($user_avatar_name)) && $board_config['allow_avatar_upload']) {
        if (!empty($user_avatar_upload)) {
            $avatar_mode = (!empty($user_avatar_name)) ? 'local' : 'remote';
            $avatar_sql  = user_avatar_upload($mode, $avatar_mode, $userdata['user_avatar'], $userdata['user_avatar_type'], $error, $error_msg, $user_avatar_upload, $user_avatar_name, $user_avatar_size, $user_avatar_filetype);
        } elseif (!empty($user_avatar_name)) {
            $l_avatar_size = sprintf($lang['Avatar_filesize'], round($board_config['avatar_filesize'] / 1024));

            $error     = true;
            $error_msg .= ((!empty($error_msg)) ? '<br>' : '') . $l_avatar_size;
        }
    } elseif ($user_avatar_remoteurl != '' && $board_config['allow_avatar_remote']) {
        if (@file_exists(@phpbb_realpath('./' . $board_config['avatar_path'] . '/' . $userdata['user_avatar']))) {
            @unlink(@phpbb_realpath('./' . $board_config['avatar_path'] . '/' . $userdata['user_avatar']));
        }
        $avatar_sql = user_avatar_url($mode, $error, $error_msg, $user_avatar_remoteurl);
    } elseif ($user_avatar_local != '' && $board_config['allow_avatar_local']) {
        if (@file_exists(@phpbb_realpath('./' . $board_config['avatar_path'] . '/' . $userdata['user_avatar']))) {
            @unlink(@phpbb_realpath('./' . $board_config['avatar_path'] . '/' . $userdata['user_avatar']));
        }
        $avatar_sql = user_avatar_gallery($mode, $error, $error_msg, $user_avatar_local);
    }

    if (!$error) {
        if ($avatar_sql == '') {
            $avatar_sql = ($mode == 'editprofile') ? '' : "'blank.gif', 3";
        }

        if ($mode == 'editprofile') {
            if ($email != $userdata['email'] && $board_config['require_activation'] != USER_ACTIVATION_NONE && $userdata['user_level'] != ADMIN) {
                $user_active = 0;

                $user_actkey = gen_rand_string(true);
                $key_len     = 54 - (strlen($server_url));
                $key_len     = ($key_len > 6) ? $key_len : 6;
                $user_actkey = substr($user_actkey, 0, $key_len);

                if (is_object($xoopsUser)) {
                    session_end($userdata['sess_id'], $userdata['uid']);
                }
            } else {
                $user_active = 1;
                $user_actkey = '';
            }

            $sql = 'UPDATE '
                   . USERS_TABLE
                   . '
				SET '
                   . $username_sql
                   . $passwd_sql
                   . "email = '"
                   . str_replace("\'", "''", $email)
                   . "', user_icq = '"
                   . str_replace("\'", "''", $icq)
                   . "', url = '"
                   . str_replace("\'", "''", $website)
                   . "', user_occ = '"
                   . str_replace("\'", "''", $occupation)
                   . "', user_from = '"
                   . str_replace(
                       "\'",
                       "''",
                       $location
                   )
                   . "', user_intrest = '"
                   . str_replace("\'", "''", $interests)
                   . "', user_sig = '"
                   . str_replace("\'", "''", $signature)
                   . "', user_viewemail = $viewemail, user_aim = '"
                   . str_replace("\'", "''", str_replace(' ', '+', $aim))
                   . "', user_yim = '"
                   . str_replace("\'", "''", $yim)
                   . "', user_msnm = '"
                   . str_replace("\'", "''", $msn)
                   . "', attachsig = $attachsig,  timezone_offset = $user_timezone, level = $user_active, actkey = '"
                   . str_replace("\'", "''", $user_actkey)
                   . "'"
                   . $avatar_sql
                   . "
				WHERE uid = $user_id";
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not update users table', '', __LINE__, __FILE__, $sql);
            }
            $sql = 'UPDATE '
                   . USERS_TABLE_EXT
                   . "
				SET user_sig_bbcode_uid = '$signature_bbcode_uid', user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_notify = $notifyreply, user_notify_pm = $notifypm, user_popup_pm = $popup_pm, user_dateformat = '"
                   . str_replace("\'", "''", $user_dateformat)
                   . "', user_lang = '"
                   . str_replace("\'", "''", $user_lang)
                   . "', user_style = $user_style"
                   . $avatar_sql
                   . "
				WHERE uid = $user_id";
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not update users table', '', __LINE__, __FILE__, $sql);
            }

            if (!$user_active) {
                //
                // The users account has been deactivated, send them an email with a new activation key
                //
                include $phpbb_root_path . 'includes/emailer.php';
                $emailer = new emailer($board_config['smtp_delivery']);

                $emailer->from($board_config['board_email']);
                $emailer->replyto($board_config['board_email']);

                $emailer->use_template('user_activate', stripslashes($user_lang));
                $emailer->email_address($email);
                $emailer->set_subject($lang['Reactivate']);

                $emailer->assign_vars(
                    [
                        'SITENAME'  => $board_config['sitename'],
                        'USERNAME'  => preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, substr(str_replace("\'", "'", $username), 0, 25)),
                        'EMAIL_SIG' => (!empty($board_config['board_email_sig'])) ? str_replace('<br>', "\n", "-- \n" . $board_config['board_email_sig']) : '',

                        'U_ACTIVATE' => $server_url . '?mode=activate&' . POST_USERS_URL . '=' . $user_id . '&act_key=' . $user_actkey,
                    ]
                );
                $emailer->send();
                $emailer->reset();

                $message = $lang['Profile_updated_inactive'] . '<br><br>' . sprintf($lang['Click_return_index'], '<a href="' . append_sid('index.php') . '">', '</a>');
            } else {
                $message = $lang['Profile_updated'] . '<br><br>' . sprintf($lang['Click_return_index'], '<a href="' . append_sid('index.php') . '">', '</a>');
            }

            $template->assign_vars(
                [
                    'META' => '<meta http-equiv="refresh" content="5;url=' . append_sid('index.php') . '">',
                ]
            );

            message_die(GENERAL_MESSAGE, $message);
        } else {
            $sql = 'SELECT MAX(uid) AS total
				FROM ' . USERS_TABLE;
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not obtain next user_id information', '', __LINE__, __FILE__, $sql);
            }

            if (!($row = $db->sql_fetchrow($result))) {
                message_die(GENERAL_ERROR, 'Could not obtain next user_id information', '', __LINE__, __FILE__, $sql);
            }
            $user_id = $row['total'] + 1;

            //
            // Get current date
            //
            $sql = 'INSERT INTO '
                   . USERS_TABLE
                   . "	(uid, uname, user_regdate, pass, email, user_icq, url, user_occ, user_from, user_intrest, user_sig, user_sig_bbcode_uid, user_avatar, user_avatar_type, user_viewemail, user_aim, user_yim, user_msnm, attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_viewonline, user_notify, user_notify_pm, user_popup_pm, timezone_offset, user_dateformat, user_lang, user_style, user_level, user_allow_pm, level, actkey)
				VALUES ($user_id, '"
                   . str_replace("\'", "''", $username)
                   . "', "
                   . time()
                   . ", '"
                   . str_replace("\'", "''", $new_password)
                   . "', '"
                   . str_replace("\'", "''", $email)
                   . "', '"
                   . str_replace("\'", "''", $icq)
                   . "', '"
                   . str_replace("\'", "''", $website)
                   . "', '"
                   . str_replace(
                       "\'",
                       "''",
                       $occupation
                   )
                   . "', '"
                   . str_replace("\'", "''", $location)
                   . "', '"
                   . str_replace("\'", "''", $interests)
                   . "', '"
                   . str_replace("\'", "''", $signature)
                   . "', '$signature_bbcode_uid', $avatar_sql, $viewemail, '"
                   . str_replace("\'", "''", str_replace(' ', '+', $aim))
                   . "', '"
                   . str_replace(
                       "\'",
                       "''",
                       $yim
                   )
                   . "', '"
                   . str_replace("\'", "''", $msn)
                   . "', $attachsig, $allowsmilies, $allowhtml, $allowbbcode, $allowviewonline, $notifyreply, $notifypm, $popup_pm, $user_timezone, '"
                   . str_replace("\'", "''", $user_dateformat)
                   . "', '"
                   . str_replace("\'", "''", $user_lang)
                   . "', $user_style, 0, 1, ";
            if ($board_config['require_activation'] == USER_ACTIVATION_SELF || $board_config['require_activation'] == USER_ACTIVATION_ADMIN || $coppa) {
                $user_actkey = gen_rand_string(true);
                $key_len     = 54 - (strlen($server_url));
                $key_len     = ($key_len > 6) ? $key_len : 6;
                $user_actkey = substr($user_actkey, 0, $key_len);
                $sql         .= "0, '" . str_replace("\'", "''", $user_actkey) . "')";
            } else {
                $sql .= "1, '')";
            }

            if (!($result = $db->sql_query($sql, BEGIN_TRANSACTION))) {
                message_die(GENERAL_ERROR, 'Could not insert data into users table', '', __LINE__, __FILE__, $sql);
            }

            $sql = 'INSERT INTO ' . GROUPS_TABLE . " (name, description)
				VALUES ('', 'Personal User')";
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not insert data into groups table', '', __LINE__, __FILE__, $sql);
            }

            $groupid = $db->sql_nextid();

            $sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (uid, groupid)
				VALUES ($user_id, $groupid)";
            if (!($result = $db->sql_query($sql, END_TRANSACTION))) {
                message_die(GENERAL_ERROR, 'Could not insert data into user_group table', '', __LINE__, __FILE__, $sql);
            }
            // ADD XOOPS groups_users_link
            $db->sql_query('INSERT INTO ' . XOOPS_DB_PREFIX . "_groups_users_link (groupid, uid) VALUES (2, $user_id)");
            //
            if ($coppa) {
                $message        = $lang['COPPA'];
                $email_template = 'coppa_welcome_inactive';
            } elseif ($board_config['require_activation'] == USER_ACTIVATION_SELF) {
                $message        = $lang['Account_inactive'];
                $email_template = 'user_welcome_inactive';
            } elseif ($board_config['require_activation'] == USER_ACTIVATION_ADMIN) {
                $message        = $lang['Account_inactive_admin'];
                $email_template = 'admin_welcome_inactive';
            } else {
                $message        = $lang['Account_added'];
                $email_template = 'user_welcome';
            }

            include $phpbb_root_path . 'includes/emailer.php';
            $emailer = new emailer($board_config['smtp_delivery']);

            $emailer->from($board_config['board_email']);
            $emailer->replyto($board_config['board_email']);

            $emailer->use_template($email_template, stripslashes($user_lang));
            $emailer->email_address($email);
            $emailer->set_subject(sprintf($lang['Welcome_subject'], $board_config['sitename']));

            if ($coppa) {
                $emailer->assign_vars(
                    [
                        'SITENAME'    => $board_config['sitename'],
                        'WELCOME_MSG' => sprintf($lang['Welcome_subject'], $board_config['sitename']),
                        'USERNAME'    => preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, substr(str_replace("\'", "'", $username), 0, 25)),
                        'PASSWORD'    => $password_confirm,
                        'EMAIL_SIG'   => str_replace('<br>', "\n", "-- \n" . $board_config['board_email_sig']),

                        'FAX_INFO'      => $board_config['coppa_fax'],
                        'MAIL_INFO'     => $board_config['coppa_mail'],
                        'EMAIL_ADDRESS' => $email,
                        'ICQ'           => $icq,
                        'AIM'           => $aim,
                        'YIM'           => $yim,
                        'MSN'           => $msn,
                        'WEB_SITE'      => $website,
                        'FROM'          => $location,
                        'OCC'           => $occupation,
                        'INTERESTS'     => $interests,
                        'SITENAME'      => $board_config['sitename'],
                    ]
                );
            } else {
                $emailer->assign_vars(
                    [
                        'SITENAME'    => $board_config['sitename'],
                        'WELCOME_MSG' => sprintf($lang['Welcome_subject'], $board_config['sitename']),
                        'USERNAME'    => preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, substr(str_replace("\'", "'", $username), 0, 25)),
                        'PASSWORD'    => $password_confirm,
                        'EMAIL_SIG'   => str_replace('<br>', "\n", "-- \n" . $board_config['board_email_sig']),

                        'U_ACTIVATE' => $server_url . '?mode=activate&' . POST_USERS_URL . '=' . $user_id . '&act_key=' . $user_actkey,
                    ]
                );
            }

            $emailer->send();
            $emailer->reset();

            if ($board_config['require_activation'] == USER_ACTIVATION_ADMIN) {
                $sql = 'SELECT email, user_lang
					FROM ' . USERS_TABLE . '
					WHERE user_level = ' . ADMIN;

                if (!($result = $db->sql_query($sql))) {
                    message_die(GENERAL_ERROR, 'Could not select Administrators', '', __LINE__, __FILE__, $sql);
                }

                while (false !== ($row = $db->sql_fetchrow($result))) {
                    $emailer->from($board_config['board_email']);
                    $emailer->replyto($board_config['board_email']);

                    $emailer->email_address(trim($row['email']));
                    $emailer->use_template('admin_activate', $row['user_lang']);
                    $emailer->set_subject($lang['New_account_subject']);

                    $emailer->assign_vars(
                        [
                            'USERNAME'  => preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, substr(str_replace("\'", "'", $username), 0, 25)),
                            'EMAIL_SIG' => str_replace('<br>', "\n", "-- \n" . $board_config['board_email_sig']),

                            'U_ACTIVATE' => $server_url . '?mode=activate&' . POST_USERS_URL . '=' . $user_id . '&act_key=' . $user_actkey,
                        ]
                    );
                    $emailer->send();
                    $emailer->reset();
                }
                $db->sql_freeresult($result);
            }

            $message .= '<br><br>' . sprintf($lang['Click_return_index'], '<a href="' . append_sid('index.php') . '">', '</a>');

            message_die(GENERAL_MESSAGE, $message);
        } // if mode == register
    }
} // End of submit

if ($error) {
    //
    // If an error occured we need to stripslashes on returned data
    //
    $username         = stripslashes($username);
    $email            = stripslashes($email);
    $new_password     = '';
    $password_confirm = '';

    $icq = stripslashes($icq);
    $aim = str_replace('+', ' ', stripslashes($aim));
    $msn = stripslashes($msn);
    $yim = stripslashes($yim);

    $website    = stripslashes($website);
    $location   = stripslashes($location);
    $occupation = stripslashes($occupation);
    $interests  = stripslashes($interests);
    $signature  = stripslashes($signature);
    $signature  = ($signature_bbcode_uid != '') ? preg_replace("/:(([a-z0-9]+:)?)$signature_bbcode_uid(=|\])/si", '\\3', $signature) : $signature;

    $user_lang       = stripslashes($user_lang);
    $user_dateformat = stripslashes($user_dateformat);
} elseif ($mode == 'editprofile' && !isset($_POST['avatargallery']) && !isset($_POST['submitavatar']) && !isset($_POST['cancelavatar'])) {
    $user_id          = $userdata['uid'];
    $username         = $userdata['uname'];
    $email            = $userdata['email'];
    $new_password     = '';
    $password_confirm = '';

    $icq = $userdata['user_icq'];
    $aim = str_replace('+', ' ', $userdata['user_aim']);
    $msn = $userdata['user_msnm'];
    $yim = $userdata['user_yim'];

    $website              = $userdata['url'];
    $location             = $userdata['user_from'];
    $occupation           = $userdata['user_occ'];
    $interests            = $userdata['user_intrest'];
    $signature_bbcode_uid = $userdata['user_sig_bbcode_uid'];
    $signature            = ($signature_bbcode_uid != '') ? preg_replace("/:(([a-z0-9]+:)?)$signature_bbcode_uid(=|\])/si", '\\3', $userdata['user_sig']) : $userdata['user_sig'];

    $viewemail       = $userdata['user_viewemail'];
    $notifypm        = $userdata['user_notify_pm'];
    $popup_pm        = $userdata['user_popup_pm'];
    $notifyreply     = $userdata['user_notify'];
    $attachsig       = $userdata['attachsig'];
    $allowhtml       = $userdata['user_allowhtml'];
    $allowbbcode     = $userdata['user_allowbbcode'];
    $allowsmilies    = $userdata['user_allowsmile'];
    $allowviewonline = $userdata['user_allow_viewonline'];

    $user_avatar      = ($userdata['user_allowavatar']) ? $userdata['user_avatar'] : '';
    $user_avatar_type = ($userdata['user_allowavatar']) ? $userdata['user_avatar_type'] : USER_AVATAR_NONE;

    $user_style      = $userdata['user_style'];
    $user_lang       = $userdata['user_lang'];
    $user_timezone   = $userdata['timezone_offset'];
    $user_dateformat = $userdata['user_dateformat'];
}

//
// Default pages
//
include $phpbb_root_path . 'includes/page_header.php';

phpbbi_make_jumpbox('viewforum.php');

if ($mode == 'editprofile') {
    if ($user_id != $userdata['uid']) {
        $error     = true;
        $error_msg = $lang['Wrong_Profile'];
    }
}

if (isset($_POST['avatargallery']) && !$error) {
    include $phpbb_root_path . 'includes/usercp_avatar.php';

    $avatar_category = (!empty($_POST['avatarcategory'])) ? htmlspecialchars($_POST['avatarcategory'], ENT_QUOTES | ENT_HTML5) : '';

    $template->set_filenames(
        [
            'body' => 'profile_avatar_gallery.tpl',
        ]
    );

    $allowviewonline = !$allowviewonline;

    display_avatar_gallery(
        $mode,
        $avatar_category,
        $user_id,
        $email,
        $current_email,
        $coppa,
        $username,
        $email,
        $new_password,
        $cur_password,
        $password_confirm,
        $icq,
        $aim,
        $msn,
        $yim,
        $website,
        $location,
        $occupation,
        $interests,
        $signature,
        $viewemail,
        $notifypm,
        $popup_pm,
        $notifyreply,
        $attachsig,
        $allowhtml,
        $allowbbcode,
        $allowsmilies,
        $allowviewonline,
        $user_style,
        $user_lang,
        $user_timezone,
        $user_dateformat,
        $userdata['sess_id']
    );
} else {
    include $phpbb_root_path . 'includes/functions_selects.php';

    if (!isset($coppa)) {
        $coppa = false;
    }

    if (!isset($user_template)) {
        $selected_template = $board_config['system_template'];
    }

    $avatar_img = '';
    if ($user_avatar_type) {
        switch ($user_avatar_type) {
            case USER_AVATAR_UPLOAD:
                $avatar_img = ($board_config['allow_avatar_upload']) ? '<img src="' . $board_config['avatar_path'] . '/' . $user_avatar . '" alt="">' : '';
                break;
            case USER_AVATAR_REMOTE:
                $avatar_img = ($board_config['allow_avatar_remote']) ? '<img src="' . $user_avatar . '" alt="">' : '';
                break;
            case USER_AVATAR_GALLERY:
                $avatar_img = ($board_config['allow_avatar_local']) ? '<img src="' . $board_config['avatar_gallery_path'] . '/' . $user_avatar . '" alt="">' : '';
                break;
        }
    }

    $s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="agreed" value="true"><input type="hidden" name="coppa" value="' . $coppa . '">';
    if ($mode == 'editprofile') {
        $s_hidden_fields .= '<input type="hidden" name="user_id" value="' . $userdata['uid'] . '">';
        //
        // Send the users current email address. If they change it, and account activation is turned on
        // the user account will be disabled and the user will have to reactivate their account.
        //
        $s_hidden_fields .= '<input type="hidden" name="current_email" value="' . $userdata['email'] . '">';
    }

    if (!empty($user_avatar_local)) {
        $s_hidden_fields .= '<input type="hidden" name="avatarlocal" value="' . $user_avatar_local . '">';
    }

    $html_status    = ($userdata['user_allowhtml'] && $board_config['allow_html']) ? $lang['HTML_is_ON'] : $lang['HTML_is_OFF'];
    $bbcode_status  = ($userdata['user_allowbbcode'] && $board_config['allow_bbcode']) ? $lang['BBCode_is_ON'] : $lang['BBCode_is_OFF'];
    $smilies_status = ($userdata['user_allowsmile'] && $board_config['allow_smilies']) ? $lang['Smilies_are_ON'] : $lang['Smilies_are_OFF'];

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

    $template->set_filenames(
        [
            'body' => 'profile_add_body.tpl',
        ]
    );

    if ($mode == 'editprofile') {
        $template->assign_block_vars('switch_edit_profile', []);
    }

    if (($mode == 'register') || ($board_config['allow_namechange'])) {
        $template->assign_block_vars('switch_namechange_allowed', []);
    } else {
        $template->assign_block_vars('switch_namechange_disallowed', []);
    }

    //
    // Let's do an overall check for settings/versions which would prevent
    // us from doing file uploads....
    //
    $ini_val      = (phpversion() >= '4.0.0') ? 'ini_get' : 'get_cfg_var';
    $form_enctype = (@$ini_val('file_uploads') == '0' || strtolower(@$ini_val('file_uploads') == 'off') || phpversion() == '4.0.4pl1' || !$board_config['allow_avatar_upload'] || (phpversion() < '4.0.3' && @$ini_val('open_basedir') != '')) ? '' : 'enctype="multipart/form-data"';

    $template->assign_vars(
        [
            'USERNAME'                 => $username,
            'CUR_PASSWORD'             => $cur_password,
            'NEW_PASSWORD'             => $new_password,
            'PASSWORD_CONFIRM'         => $password_confirm,
            'EMAIL'                    => $email,
            'YIM'                      => $yim,
            'ICQ'                      => $icq,
            'MSN'                      => $msn,
            'AIM'                      => $aim,
            'OCCUPATION'               => $occupation,
            'INTERESTS'                => $interests,
            'LOCATION'                 => $location,
            'WEBSITE'                  => $website,
            'SIGNATURE'                => str_replace('<br>', "\n", $signature),
            'VIEW_EMAIL_YES'           => ($viewemail) ? 'checked' : '',
            'VIEW_EMAIL_NO'            => (!$viewemail) ? 'checked' : '',
            'HIDE_USER_YES'            => (!$allowviewonline) ? 'checked' : '',
            'HIDE_USER_NO'             => ($allowviewonline) ? 'checked' : '',
            'NOTIFY_PM_YES'            => ($notifypm) ? 'checked' : '',
            'NOTIFY_PM_NO'             => (!$notifypm) ? 'checked' : '',
            'POPUP_PM_YES'             => ($popup_pm) ? 'checked' : '',
            'POPUP_PM_NO'              => (!$popup_pm) ? 'checked' : '',
            'ALWAYS_ADD_SIGNATURE_YES' => ($attachsig) ? 'checked' : '',
            'ALWAYS_ADD_SIGNATURE_NO'  => (!$attachsig) ? 'checked' : '',
            'NOTIFY_REPLY_YES'         => ($notifyreply) ? 'checked' : '',
            'NOTIFY_REPLY_NO'          => (!$notifyreply) ? 'checked' : '',
            'ALWAYS_ALLOW_BBCODE_YES'  => ($allowbbcode) ? 'checked' : '',
            'ALWAYS_ALLOW_BBCODE_NO'   => (!$allowbbcode) ? 'checked' : '',
            'ALWAYS_ALLOW_HTML_YES'    => ($allowhtml) ? 'checked' : '',
            'ALWAYS_ALLOW_HTML_NO'     => (!$allowhtml) ? 'checked' : '',
            'ALWAYS_ALLOW_SMILIES_YES' => ($allowsmilies) ? 'checked' : '',
            'ALWAYS_ALLOW_SMILIES_NO'  => (!$allowsmilies) ? 'checked' : '',
            'ALLOW_AVATAR'             => $board_config['allow_avatar_upload'],
            'AVATAR'                   => $avatar_img,
            'AVATAR_SIZE'              => $board_config['avatar_filesize'],
            'LANGUAGE_SELECT'          => language_select($user_lang, 'language'),
            'STYLE_SELECT'             => style_select($user_style, 'style'),
            'TIMEZONE_SELECT'          => tz_select($user_timezone, 'timezone'),
            'DATE_FORMAT'              => $user_dateformat,
            'HTML_STATUS'              => $html_status,
            'BBCODE_STATUS'            => sprintf($bbcode_status, '<a href="' . append_sid('faq.php?mode=bbcode') . '" target="_phpbbcode">', '</a>'),
            'SMILIES_STATUS'           => $smilies_status,

            'L_CURRENT_PASSWORD'            => $lang['Current_password'],
            'L_NEW_PASSWORD'                => ($mode == 'register') ? $lang['Password'] : $lang['New_password'],
            'L_CONFIRM_PASSWORD'            => $lang['Confirm_password'],
            'L_CONFIRM_PASSWORD_EXPLAIN'    => ($mode == 'editprofile') ? $lang['Confirm_password_explain'] : '',
            'L_PASSWORD_IF_CHANGED'         => ($mode == 'editprofile') ? $lang['password_if_changed'] : '',
            'L_PASSWORD_CONFIRM_IF_CHANGED' => ($mode == 'editprofile') ? $lang['password_confirm_if_changed'] : '',
            'L_SUBMIT'                      => $lang['Submit'],
            'L_RESET'                       => $lang['Reset'],
            'L_ICQ_NUMBER'                  => $lang['ICQ'],
            'L_MESSENGER'                   => $lang['MSNM'],
            'L_YAHOO'                       => $lang['YIM'],
            'L_WEBSITE'                     => $lang['Website'],
            'L_AIM'                         => $lang['AIM'],
            'L_LOCATION'                    => $lang['Location'],
            'L_OCCUPATION'                  => $lang['Occupation'],
            'L_BOARD_LANGUAGE'              => $lang['Board_lang'],
            'L_BOARD_STYLE'                 => $lang['Board_style'],
            'L_TIMEZONE'                    => $lang['Timezone'],
            'L_DATE_FORMAT'                 => $lang['Date_format'],
            'L_DATE_FORMAT_EXPLAIN'         => $lang['Date_format_explain'],
            'L_YES'                         => $lang['Yes'],
            'L_NO'                          => $lang['No'],
            'L_INTERESTS'                   => $lang['Interests'],
            'L_ALWAYS_ALLOW_SMILIES'        => $lang['Always_smile'],
            'L_ALWAYS_ALLOW_BBCODE'         => $lang['Always_bbcode'],
            'L_ALWAYS_ALLOW_HTML'           => $lang['Always_html'],
            'L_HIDE_USER'                   => $lang['Hide_user'],
            'L_ALWAYS_ADD_SIGNATURE'        => $lang['Always_add_sig'],

            'L_AVATAR_PANEL'               => $lang['Avatar_panel'],
            'L_AVATAR_EXPLAIN'             => sprintf($lang['Avatar_explain'], $board_config['avatar_max_width'], $board_config['avatar_max_height'], (round($board_config['avatar_filesize'] / 1024))),
            'L_UPLOAD_AVATAR_FILE'         => $lang['Upload_Avatar_file'],
            'L_UPLOAD_AVATAR_URL'          => $lang['Upload_Avatar_URL'],
            'L_UPLOAD_AVATAR_URL_EXPLAIN'  => $lang['Upload_Avatar_URL_explain'],
            'L_AVATAR_GALLERY'             => $lang['Select_from_gallery'],
            'L_SHOW_GALLERY'               => $lang['View_avatar_gallery'],
            'L_LINK_REMOTE_AVATAR'         => $lang['Link_remote_Avatar'],
            'L_LINK_REMOTE_AVATAR_EXPLAIN' => $lang['Link_remote_Avatar_explain'],
            'L_DELETE_AVATAR'              => $lang['Delete_Image'],
            'L_CURRENT_IMAGE'              => $lang['Current_Image'],

            'L_SIGNATURE'                => $lang['Signature'],
            'L_SIGNATURE_EXPLAIN'        => sprintf($lang['Signature_explain'], $board_config['max_sig_chars']),
            'L_NOTIFY_ON_REPLY'          => $lang['Always_notify'],
            'L_NOTIFY_ON_REPLY_EXPLAIN'  => $lang['Always_notify_explain'],
            'L_NOTIFY_ON_PRIVMSG'        => $lang['Notify_on_privmsg'],
            'L_POPUP_ON_PRIVMSG'         => $lang['Popup_on_privmsg'],
            'L_POPUP_ON_PRIVMSG_EXPLAIN' => $lang['Popup_on_privmsg_explain'],
            'L_PREFERENCES'              => $lang['Preferences'],
            'L_PUBLIC_VIEW_EMAIL'        => $lang['Public_view_email'],
            'L_ITEMS_REQUIRED'           => $lang['Items_required'],
            'L_REGISTRATION_INFO'        => $lang['Registration_info'],
            'L_PROFILE_INFO'             => $lang['Profile_info'],
            'L_PROFILE_INFO_NOTICE'      => $lang['Profile_info_warn'],
            'L_EMAIL_ADDRESS'            => $lang['Email_address'],

            'S_ALLOW_AVATAR_UPLOAD' => $board_config['allow_avatar_upload'],
            'S_ALLOW_AVATAR_LOCAL'  => $board_config['allow_avatar_local'],
            'S_ALLOW_AVATAR_REMOTE' => $board_config['allow_avatar_remote'],
            'S_HIDDEN_FIELDS'       => $s_hidden_fields,
            'S_FORM_ENCTYPE'        => $form_enctype,
            'S_PROFILE_ACTION'      => append_sid('profile.php'),
        ]
    );

    //
    // This is another cheat using the block_var capability
    // of the templates to 'fake' an IF...ELSE...ENDIF solution
    // it works well :)
    //
    if ($mode != 'register') {
        if ($userdata['user_allowavatar'] && ($board_config['allow_avatar_upload'] || $board_config['allow_avatar_local'] || $board_config['allow_avatar_remote'])) {
            $template->assign_block_vars('switch_avatar_block', []);

            if ($board_config['allow_avatar_upload'] && file_exists(@phpbb_realpath('./' . $board_config['avatar_path']))) {
                if ($form_enctype != '') {
                    $template->assign_block_vars('switch_avatar_block.switch_avatar_local_upload', []);
                }
                $template->assign_block_vars('switch_avatar_block.switch_avatar_remote_upload', []);
            }

            if ($board_config['allow_avatar_remote']) {
                $template->assign_block_vars('switch_avatar_block.switch_avatar_remote_link', []);
            }

            if ($board_config['allow_avatar_local'] && file_exists(@phpbb_realpath('./' . $board_config['avatar_gallery_path']))) {
                $template->assign_block_vars('switch_avatar_block.switch_avatar_local_gallery', []);
            }
        }
    }
}

$template->pparse('body');

include $phpbb_root_path . 'includes/page_tail.php';


