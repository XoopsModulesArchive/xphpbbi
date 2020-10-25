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
 *                          functions_validate.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: functions_validate.php,v 1.8 2004/12/03 23:51:42 blackdeath_csmc Exp $
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
 * @param $username
 * @return array
 */

//
// Check to see if the username has been taken, or if it is disallowed.
// Also checks if it includes the " character, which we don't allow in usernames.
// Used for registering, changing names, and posting anonymously with a username
//
function validate_username($username)
{
    global $db, $lang, $userdata;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    // Remove doubled up spaces

    $username = preg_replace('#\s+#', ' ', $username);

    // Limit username length

    $username = mb_substr(str_replace("\'", "'", $username), 0, 25);

    $username = str_replace("'", "''", $username);

    $sql = 'SELECT uname
		FROM ' . USERS_TABLE . "
		WHERE LOWER(uname) = '" . mb_strtolower($username) . "'";

    if ($result = $db->sql_query($sql)) {
        if ($row = $db->sql_fetchrow($result)) {
            if ((is_object($xoopsUser) && $row['uname'] != $userdata['uname']) || !is_object($xoopsUser)) {
                $db->sql_freeresult($result);

                return ['error' => true, 'error_msg' => $lang['Username_taken']];
            }
        }
    }

    $db->sql_freeresult($result);

    $sql = 'SELECT name AS group_name
		FROM ' . GROUPS_TABLE . "
		WHERE LOWER(name) = '" . mb_strtolower($username) . "'";

    if ($result = $db->sql_query($sql)) {
        if ($row = $db->sql_fetchrow($result)) {
            $db->sql_freeresult($result);

            return ['error' => true, 'error_msg' => $lang['Username_taken']];
        }
    }

    $db->sql_freeresult($result);

    $sql = 'SELECT disallow_username
		FROM ' . DISALLOW_TABLE;

    if ($result = $db->sql_query($sql)) {
        if ($row = $db->sql_fetchrow($result)) {
            do {
                if (preg_match("#\b(" . str_replace("\*", '.*?', phpbb_preg_quote($row['disallow_username'], '#')) . ")\b#i", $username)) {
                    $db->sql_freeresult($result);

                    return ['error' => true, 'error_msg' => $lang['Username_disallowed']];
                }
            } while (false !== ($row = $db->sql_fetchrow($result)));
        }
    }

    $db->sql_freeresult($result);

    $sql = 'SELECT word
		FROM  ' . WORDS_TABLE;

    if ($result = $db->sql_query($sql)) {
        if ($row = $db->sql_fetchrow($result)) {
            do {
                if (preg_match("#\b(" . str_replace("\*", '.*?', phpbb_preg_quote($row['word'], '#')) . ")\b#i", $username)) {
                    $db->sql_freeresult($result);

                    return ['error' => true, 'error_msg' => $lang['Username_disallowed']];
                }
            } while (false !== ($row = $db->sql_fetchrow($result)));
        }
    }

    $db->sql_freeresult($result);

    // Don't allow " and ALT-255 in username.

    if (mb_strstr($username, '"') || mb_strstr($username, '&quot;') || mb_strstr($username, chr(160))) {
        return ['error' => true, 'error_msg' => $lang['Username_invalid']];
    }

    return ['error' => false, 'error_msg' => ''];
}

//
// Check to see if email address is banned
// or already present in the DB
//
function validate_email($email)
{
    global $db, $lang;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    if ('' != $email) {
        if (preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/is', $email)) {
            $sql = 'SELECT ban_email
				FROM ' . BANLIST_TABLE;

            if ($result = $db->sql_query($sql)) {
                if ($row = $db->sql_fetchrow($result)) {
                    do {
                        $match_email = str_replace('*', '.*?', $row['ban_email']);

                        if (preg_match('/^' . $match_email . '$/is', $email)) {
                            $db->sql_freeresult($result);

                            return ['error' => true, 'error_msg' => $lang['Email_banned']];
                        }
                    } while (false !== ($row = $db->sql_fetchrow($result)));
                }
            }

            $db->sql_freeresult($result);

            $sql = 'SELECT email
				FROM ' . USERS_TABLE . "
				WHERE email = '" . str_replace("\'", "''", $email) . "'";

            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, "Couldn't obtain user email information.", '', __LINE__, __FILE__, $sql);
            }

            if ($row = $db->sql_fetchrow($result)) {
                return ['error' => true, 'error_msg' => $lang['Email_taken']];
            }

            $db->sql_freeresult($result);

            return ['error' => false, 'error_msg' => ''];
        }
    }

    return ['error' => true, 'error_msg' => $lang['Email_invalid']];
}

//
// Does supplementary validation of optional profile fields. This expects common stuff like trim() and strip_tags()
// to have already been run. Params are passed by-ref, so we can set them to the empty string if they fail.
//
function validate_optional_fields(&$icq, &$aim, &$msnm, &$yim, &$website, &$location, &$occupation, &$interests, &$sig)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $check_var_length = ['aim', 'msnm', 'yim', 'location', 'occupation', 'interests', 'sig'];

    for ($i = 0, $iMax = count($check_var_length); $i < $iMax; $i++) {
        if (mb_strlen($$check_var_length[$i]) < 2) {
            $$check_var_length[$i] = '';
        }
    }

    // ICQ number has to be only numbers.

    if (!preg_match('/^[0-9]+$/', $icq)) {
        $icq = '';
    }

    // website has to start with http://, followed by something with length at least 3 that

    // contains at least one dot.

    if ('' != $website) {
        if (!preg_match('#^http[s]?:\/\/#i', $website)) {
            $website = 'http://' . $website;
        }

        if (!preg_match('#^http[s]?\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $website)) {
            $website = '';
        }
    }
}
