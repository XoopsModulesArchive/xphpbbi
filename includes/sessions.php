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
 *                                sessions.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: sessions.php,v 1.9 2004/12/03 23:51:42 blackdeath_csmc Exp $
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
 **************************************************************************
 * @param $user_ip
 * @param $thispage_id
 * @return false|mixed
 */

//
// Checks for a given user session, tidies session table and updates user
// sessions at each page refresh
//
function session_pagestart($user_ip, $thispage_id)
{
    global $db, $lang, $board_config, $sid_bb, $uid_bb, $uname_bb, $issess, $lastvisit_bb;

    global $HTTP_COOKIE_VARS, $_GET, $SID;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $cookiename = $board_config['cookie_name'];

    $cookiepath = $board_config['cookie_path'];

    $cookiedomain = $board_config['cookie_domain'];

    $cookiesecure = $board_config['cookie_secure'];

    $last_visit = 0;

    $current_time = time();

    unset($userdata);

    $sessiondata = [];

    $sessionmethod = SESSION_METHOD_GET;

    $session_id = session_id();

    if (!preg_match('/^[A-Za-z0-9]*$/', $session_id)) {
        $session_id = '';
    }

    //

    // Does a session exist?

    //

    if (!empty($session_id)) {
        session_begin($session_id, $user_ip, $thispage_id);

        //

        // session_id exists so go ahead and attempt to grab all

        // data in preparation

        //

        if (is_object($xoopsUser)) {
            $sql = 'SELECT u.*, s.*, ue.*, se.*
				FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . ' s
				LEFT JOIN ' . USERS_TABLE_EXT . ' ue ON u.uid=ue.uid
				LEFT JOIN ' . SESSIONS_TABLE_EXT . " se ON u.uid=se.session_user_id
				WHERE s.sess_id='$session_id' AND u.uid = " . $xoopsUser->getVar('uid');

            if (!($result = $db->sql_query($sql)) || empty($userdata['user_lastvisit'])) {
                $sql_2 = 'INSERT INTO ' . USERS_TABLE_EXT . ' (uid,user_lastvisit) VALUES (' . $xoopsUser->getVar('uid') . ',' . $xoopsUser->getVar('last_login') . ')';

                $db->sql_query($sql_2);
            }
        } else {
            $sql = 'SELECT s.*, ue.*, se.*
				FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE_EXT . ' ue
				LEFT JOIN ' . SESSIONS_TABLE_EXT . " se ON ue.uid=se.session_user_id
				WHERE s.sess_id='$session_id' AND ue.uid = 0";
        }

        $userdata = $db->sql_fetchrow($result);

        //var_dump($userdata);exit;

        if (empty($userdata['user_lastvisit'])) {
            $userdata['user_lastvisit'] = $userdata['last_login'];
        }

        if (empty($userdata['uid'])) {
            $userdata['uid'] = 0;
        }

        if (empty($userdata['uname'])) {
            $userdata['uname'] = 'Guest';
        }

        //

        // Initial ban check against user id, IP and email address

        //

        $sql2 = 'SELECT ban_ip, ban_userid, ban_email 
			FROM ' . BANLIST_TABLE . " WHERE ban_ip = '" . $user_ip . "' OR ban_userid = " . $userdata['uid'];

        $sql2 .= " OR ban_email LIKE '" . str_replace("\'", "''", $userdata['email']) . "' 
			OR ban_email LIKE '" . mb_substr(str_replace("\'", "''", $userdata['email']), mb_strpos(str_replace("\'", "''", $userdata['email']), '@')) . "'";

        if (!($result2 = $db->sql_query($sql2))) {
            message_die(CRITICAL_ERROR, 'Could not obtain ban information', '', __LINE__, __FILE__, $sql2);
        }

        if ($ban_info = $db->sql_fetchrow($result2)) {
            if ($ban_info['ban_ip'] || $ban_info['ban_userid'] || $ban_info['ban_email']) {
                message_die(CRITICAL_MESSAGE, 'You_been_banned');
            }
        }

        //

        // Did the session exist in the DB?

        //

        if (isset($userdata['uid'])) {
            //

            // Do not check IP assuming equivalence, if IPv4 we'll check only first 24

            // bits ... I've been told (by vHiker) this should alleviate problems with

            // load balanced et al proxies while retaining some reliance on IP security.

            //

            $ip_check_s = mb_substr($userdata['sess_ip'], 0, 14);

            $ip_check_u = mb_substr($user_ip, 0, 14);

            if ($ip_check_s == $ip_check_u) {
                $SID = (SESSION_METHOD_GET == $sessionmethod || defined('IN_ADMIN')) ? 'sid=' . $session_id : '';
            }
        }
    }

    return $userdata;
}

function session_begin($sess_id, $sess_ip, $sess_page)
{
    global $xoopsConfig, $xoopsDB, $xoopsUser;

    $sess_id = $xoopsDB->quoteString($sess_id);

    [$count] = $xoopsDB->fetchRow($xoopsDB->query('SELECT COUNT(*) FROM ' . SESSIONS_TABLE_EXT . ' WHERE sess_id=' . $sess_id));

    $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : ANONYMOUS;

    $issess = is_object($xoopsUser) ? 1 : 0;

    $current_time = time();

    $sess_exp = $xoopsDB->fetchArray($xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('phpbbi_config') . " WHERE config_name = 'session_length'"));

    $time_exp_bb = $sess_exp['config_value'] ? ($current_time - $sess_exp['config_value']) : ($current_time - 3600);

    $time_exp = $sess_exp['config_value'] > $xoopsConfig['session_expire'] * 60 ? ($current_time - $xoopsConfig['session_expire'] * 60) : ($current_time - $sess_exp['config_value']);

    if ($count > 0) {
        $sql = 'UPDATE ' . SESSIONS_TABLE_EXT . " SET session_user_id = '$uid', session_logged_in = '$issess', session_page = '$sess_page' WHERE sess_id = $sess_id";
    } else {
        $xoopsDB->queryF(
            'DELETE FROM ' . SESSIONS_TABLE_EXT . ' se 
						  LEFT JOIN ' . SESSIONS_TABLE . " s ON se.sess_id=s.sess_id
						  WHERE sess_updated < '" . $time_exp . "'"
        );

        $sql = 'INSERT INTO ' . SESSIONS_TABLE_EXT . " (sess_id, sess_ip, session_user_id, session_start, session_page, session_logged_in) VALUES ($sess_id, '$sess_ip', '$uid', '$current_time', '$sess_page', '$issess')";
    }

    if (!$xoopsDB->queryF($sql)) {
        message_die(CRITICAL_ERROR, 'Error updating sessions table', '', __LINE__, __FILE__, $sql);

        return false;
    }

    if (ANONYMOUS != $uid) {
        $sql = 'UPDATE ' . USERS_TABLE_EXT . "
			SET user_session_time = $current_time, user_session_page = $sess_page
			WHERE uid = $uid";

        if (!$xoopsDB->queryF($sql)) {
            message_die(CRITICAL_ERROR, 'Error updating sessions table', '', __LINE__, __FILE__, $sql);
        }
    }

    setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);

    setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);

    return true;
}

// append_sid function has moved to functions.php
