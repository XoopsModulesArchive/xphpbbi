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
 *                            admin_ug_auth.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: admin_ug_auth.php,v 1.9 2004/12/03 23:51:41 blackdeath_csmc Exp $
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

define('IN_PHPBB', 1);

if (!empty($setmodules)) {
    $filename = basename(__FILE__);

    $module['Users']['Permissions'] = $filename . '?mode=user';

    $module['Groups']['Permissions'] = $filename . '?mode=group';

    return;
}

//
// Load default header
//
$no_page_header = true;

$phpbb_root_path = './../';
require __DIR__ . '/pagestart.php';

$params = ['mode' => 'mode', 'user_id' => POST_USERS_URL, 'groupid' => POST_GROUPS_URL, 'adv' => 'adv'];

while (list($var, $param) = @each($params)) {
    if (!empty($_POST[$param]) || !empty($_GET[$param])) {
        $$var = (!empty($_POST[$param])) ? $_POST[$param] : $_GET[$param];
    } else {
        $$var = '';
    }
}

$user_id = (int)$user_id;
$groupid = (int)$groupid;
$adv = (int)$adv;
$mode = htmlspecialchars($mode, ENT_QUOTES | ENT_HTML5);

//
// Start program - define vars
//
$forum_auth_fields = ['auth_view', 'auth_read', 'auth_post', 'auth_reply', 'auth_edit', 'auth_delete', 'auth_sticky', 'auth_announce', 'auth_vote', 'auth_pollcreate'];

$auth_field_match = [
    'auth_view' => AUTH_VIEW,
    'auth_read' => AUTH_READ,
    'auth_post' => AUTH_POST,
    'auth_reply' => AUTH_REPLY,
    'auth_edit' => AUTH_EDIT,
    'auth_delete' => AUTH_DELETE,
    'auth_sticky' => AUTH_STICKY,
    'auth_announce' => AUTH_ANNOUNCE,
    'auth_vote' => AUTH_VOTE,
    'auth_pollcreate' => AUTH_POLLCREATE,
];

$field_names = [
    'auth_view' => $lang['View'],
    'auth_read' => $lang['Read'],
    'auth_post' => $lang['Post'],
    'auth_reply' => $lang['Reply'],
    'auth_edit' => $lang['Edit'],
    'auth_delete' => $lang['Delete'],
    'auth_sticky' => $lang['Sticky'],
    'auth_announce' => $lang['Announce'],
    'auth_vote' => $lang['Vote'],
    'auth_pollcreate' => $lang['Pollcreate'],
];

// ---------------
// Start Functions
//
function check_auth($type, $key, $u_access, $is_admin)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $auth_user = 0;

    if (count($u_access)) {
        for ($j = 0, $jMax = count($u_access); $j < $jMax; $j++) {
            $result = 0;

            switch ($type) {
                case AUTH_ACL:
                    $result = $u_access[$j][$key];

                    // no break
                case AUTH_MOD:
                    $result = $result || $u_access[$j]['auth_mod'];

                    // no break
                case AUTH_ADMIN:
                    $result = $result || $is_admin;
                    break;
            }

            $auth_user = $auth_user || $result;
        }
    } else {
        $auth_user = $is_admin;
    }

    return $auth_user;
}

//
// End Functions
// -------------

if (isset($_POST['submit']) && (('user' == $mode && $user_id) || ('group' == $mode && $groupid))) {
    $user_level = '';

    if ('user' == $mode) {
        //

        // Get groupid for this user_id

        //

        /* //original query
        $sql = "SELECT g.groupid, u.user_level
            FROM " . USER_GROUP_TABLE . " ug, " . USERS_TABLE . " u, " . GROUPS_TABLE . " g
            WHERE u.uid = $user_id
                AND ug.uid = u.uid
                AND g.groupid = ug.groupid
                AND g.group_single_user = " . TRUE;*/

        $sql = 'SELECT user_level
			FROM ' . USERS_TABLE_EXT . "
			WHERE uid = $user_id";

        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not select info from user/user_group table', '', __LINE__, __FILE__, $sql);
        }

        $row = $db->sql_fetchrow($result);

        //$groupid = $row['groupid'];

        $user_level = $row['user_level'];

        $db->sql_freeresult($result);
    }

    //

    // Carry out requests

    //

    if ('user' == $mode && 'admin' == $_POST['userlevel'] && ADMIN != $user_level) {
        //

        // Make user an admin (if already user)

        //

        if ($userdata['uid'] != $user_id) {
            $sql = 'UPDATE ' . USERS_TABLE_EXT . '
				SET user_level = ' . ADMIN . "
				WHERE uid = $user_id";

            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not update user level', '', __LINE__, __FILE__, $sql);
            }

            /*
            $sql = "DELETE FROM " . AUTH_ACCESS_TABLE . "
                WHERE groupid = $groupid
                    AND auth_mod = 0";
            if ( !($result = $db->sql_query($sql)) )
            {
                message_die(GENERAL_ERROR, "Couldn't delete auth access info", "", __LINE__, __FILE__, $sql);
            }

            //
            // Delete any entries in auth_access, they are not required if user is becoming an
            // admin
            //
            $sql = "UPDATE " . AUTH_ACCESS_TABLE . "
                SET auth_view = 0, auth_read = 0, auth_post = 0, auth_reply = 0, auth_edit = 0, auth_delete = 0, auth_sticky = 0, auth_announce = 0
                WHERE groupid = $groupid";
            if ( !($result = $db->sql_query($sql)) )
            {
                message_die(GENERAL_ERROR, "Couldn't update auth access", "", __LINE__, __FILE__, $sql);
            }*/
        }

        $message = $lang['Auth_updated'] . '<br><br>' . sprintf($lang['Click_return_userauth'], '<a href="' . append_sid("admin_ug_auth.php?mode=$mode") . '">', '</a>') . '<br><br>' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid('index.php?pane=right') . '">', '</a>');

        message_die(GENERAL_MESSAGE, $message);
    } else {
        if ('user' == $mode && 'user' == $_POST['userlevel'] && ADMIN == $user_level) {
            //

            // Make admin a user (if already admin) ... ignore if you're trying

            // to change yourself from an admin to user!

            //

            if ($userdata['uid'] != $user_id) {
                /*
                $sql = "UPDATE " . AUTH_ACCESS_TABLE . "
                    SET auth_view = 0, auth_read = 0, auth_post = 0, auth_reply = 0, auth_edit = 0, auth_delete = 0, auth_sticky = 0, auth_announce = 0
                    WHERE groupid = $groupid";
                if ( !($result = $db->sql_query($sql)) )
                {
                    message_die(GENERAL_ERROR, 'Could not update auth access', '', __LINE__, __FILE__, $sql);
                }
                */

                //

                // Update users level, reset to USER

                //

                $sql = 'UPDATE ' . USERS_TABLE_EXT . '
					SET user_level = ' . USER . "
					WHERE uid = $user_id";

                if (!($result = $db->sql_query($sql))) {
                    message_die(GENERAL_ERROR, 'Could not update user level', '', __LINE__, __FILE__, $sql);
                }
            }

            $message = $lang['Auth_updated'] . '<br><br>' . sprintf($lang['Click_return_userauth'], '<a href="' . append_sid("admin_ug_auth.php?mode=$mode") . '">', '</a>') . '<br><br>' . sprintf(
                $lang['Click_return_admin_index'],
                '<a href="' . append_sid('index.php?pane=right') . '">',
                '</a>'
            );
        } else {
            $change_mod_list = $_POST['moderator'] ?? false;

            if (empty($adv)) {
                $change_acl_list = $_POST['private'] ?? false;
            } else {
                $change_acl_list = [];

                for ($j = 0, $jMax = count($forum_auth_fields); $j < $jMax; $j++) {
                    $auth_field = $forum_auth_fields[$j];

                    while (list($forum_id, $value) = @each($_POST['private_' . $auth_field])) {
                        $change_acl_list[$forum_id][$auth_field] = $value;
                    }
                }
            }

            $sql = 'SELECT *
				FROM ' . FORUMS_TABLE . ' f
				ORDER BY forum_order';

            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, "Couldn't obtain forum information", '', __LINE__, __FILE__, $sql);
            }

            $forum_access = [];

            while (false !== ($row = $db->sql_fetchrow($result))) {
                $forum_access[] = $row;
            }

            $db->sql_freeresult($result);

            // need to examine this query, see what it's doing, since single_user isn't used anymore

            $sql = ('user' == $mode) ? 'SELECT aa.* FROM ' . AUTH_ACCESS_TABLE . ' aa, ' . USER_GROUP_TABLE . ' ug, ' . GROUPS_TABLE . " g WHERE ug.uid = $user_id AND g.groupid = ug.groupid AND aa.groupid = ug.groupid AND g.group_single_user = " . true : 'SELECT * FROM '
                                                                                                                                                                                                                                                               . AUTH_ACCESS_TABLE
                                                                                                                                                                                                                                                               . " WHERE groupid = $groupid";

            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, "Couldn't obtain user/group permissions", '', __LINE__, __FILE__, $sql);
            }

            $auth_access = [];

            while (false !== ($row = $db->sql_fetchrow($result))) {
                $auth_access[$row['forum_id']] = $row;
            }

            $db->sql_freeresult($result);

            $forum_auth_action = [];

            $update_acl_status = [];

            $update_mod_status = [];

            for ($i = 0, $iMax = count($forum_access); $i < $iMax; $i++) {
                $forum_id = $forum_access[$i]['forum_id'];

                if ((isset($auth_access[$forum_id]['auth_mod']) && $change_mod_list[$forum_id]['auth_mod'] != $auth_access[$forum_id]['auth_mod']) || (!isset($auth_access[$forum_id]['auth_mod']) && !empty($change_mod_list[$forum_id]['auth_mod']))) {
                    $update_mod_status[$forum_id] = $change_mod_list[$forum_id]['auth_mod'];

                    if (!$update_mod_status[$forum_id]) {
                        $forum_auth_action[$forum_id] = 'delete';
                    } elseif (!isset($auth_access[$forum_id]['auth_mod'])) {
                        $forum_auth_action[$forum_id] = 'insert';
                    } else {
                        $forum_auth_action[$forum_id] = 'update';
                    }
                }

                for ($j = 0, $jMax = count($forum_auth_fields); $j < $jMax; $j++) {
                    $auth_field = $forum_auth_fields[$j];

                    if (AUTH_ACL == $forum_access[$i][$auth_field] && isset($change_acl_list[$forum_id][$auth_field])) {
                        if ((empty($auth_access[$forum_id]['auth_mod']) && (isset($auth_access[$forum_id][$auth_field]) && $change_acl_list[$forum_id][$auth_field] != $auth_access[$forum_id][$auth_field])
                             || (!isset($auth_access[$forum_id][$auth_field])
                                 && !empty($change_acl_list[$forum_id][$auth_field])))
                            || !empty($update_mod_status[$forum_id])) {
                            $update_acl_status[$forum_id][$auth_field] = (!empty($update_mod_status[$forum_id])) ? 0 : $change_acl_list[$forum_id][$auth_field];

                            if (isset($auth_access[$forum_id][$auth_field]) && empty($update_acl_status[$forum_id][$auth_field]) && 'insert' != $forum_auth_action[$forum_id] && 'update' != $forum_auth_action[$forum_id]) {
                                $forum_auth_action[$forum_id] = 'delete';
                            } elseif (!isset($auth_access[$forum_id][$auth_field]) && !('delete' == $forum_auth_action[$forum_id] && empty($update_acl_status[$forum_id][$auth_field]))) {
                                $forum_auth_action[$forum_id] = 'insert';
                            } elseif (isset($auth_access[$forum_id][$auth_field]) && !empty($update_acl_status[$forum_id][$auth_field])) {
                                $forum_auth_action[$forum_id] = 'update';
                            }
                        } elseif ((empty($auth_access[$forum_id]['auth_mod']) && (isset($auth_access[$forum_id][$auth_field]) && $change_acl_list[$forum_id][$auth_field] == $auth_access[$forum_id][$auth_field])) && 'delete' == $forum_auth_action[$forum_id]) {
                            $forum_auth_action[$forum_id] = 'update';
                        }
                    }
                }
            }

            //

            // Checks complete, make updates to DB

            //

            $delete_sql = '';

            while (list($forum_id, $action) = @each($forum_auth_action)) {
                if ('delete' == $action) {
                    $delete_sql .= (('' != $delete_sql) ? ', ' : '') . $forum_id;
                } else {
                    if ('insert' == $action) {
                        $sql_field = '';

                        $sql_value = '';

                        while (list($auth_type, $value) = @each($update_acl_status[$forum_id])) {
                            $sql_field .= (('' != $sql_field) ? ', ' : '') . $auth_type;

                            $sql_value .= (('' != $sql_value) ? ', ' : '') . $value;
                        }

                        $sql_field .= (('' != $sql_field) ? ', ' : '') . 'auth_mod';

                        $sql_value .= (('' != $sql_value) ? ', ' : '') . ($update_mod_status[$forum_id] ?? 0);

                        $sql = 'INSERT INTO ' . AUTH_ACCESS_TABLE . " (forum_id, groupid, $sql_field)
							VALUES ($forum_id, $groupid, $sql_value)";
                    } else {
                        $sql_values = '';

                        while (list($auth_type, $value) = @each($update_acl_status[$forum_id])) {
                            $sql_values .= (('' != $sql_values) ? ', ' : '') . $auth_type . ' = ' . $value;
                        }

                        $sql_values .= (('' != $sql_values) ? ', ' : '') . 'auth_mod = ' . ($update_mod_status[$forum_id] ?? 0);

                        $sql = 'UPDATE ' . AUTH_ACCESS_TABLE . "
							SET $sql_values
							WHERE groupid = $groupid
								AND forum_id = $forum_id";
                    }

                    if (!($result = $db->sql_query($sql))) {
                        message_die(GENERAL_ERROR, "Couldn't update private forum permissions", '', __LINE__, __FILE__, $sql);
                    }
                }
            }

            if ('' != $delete_sql) {
                $sql = 'DELETE FROM ' . AUTH_ACCESS_TABLE . "
					WHERE groupid = $groupid
						AND forum_id IN ($delete_sql)";

                if (!($result = $db->sql_query($sql))) {
                    message_die(GENERAL_ERROR, "Couldn't delete permission entries", '', __LINE__, __FILE__, $sql);
                }
            }

            $l_auth_return = ('user' == $mode) ? $lang['Click_return_userauth'] : $lang['Click_return_groupauth'];

            $message = $lang['Auth_updated'] . '<br><br>' . sprintf($l_auth_return, '<a href="' . append_sid("admin_ug_auth.php?mode=$mode") . '">', '</a>') . '<br><br>' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid('index.php?pane=right') . '">', '</a>');
        }

        //

        // Update user level to mod for appropriate users

        //

        $sql = 'SELECT u.uid
			FROM ' . AUTH_ACCESS_TABLE . ' aa, ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
			LEFT JOIN ' . USERS_TABLE_EXT . ' ue ON u.uid=ue.uid
			WHERE ug.groupid = aa.groupid
				AND u.uid = ug.uid
				AND ue.user_level NOT IN (' . MOD . ', ' . ADMIN . ')
			GROUP BY u.uid
			HAVING SUM(aa.auth_mod) > 0';

        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, "Couldn't obtain user/group permissions", '', __LINE__, __FILE__, $sql);
        }

        $set_mod = '';

        while (false !== ($row = $db->sql_fetchrow($result))) {
            $set_mod .= (('' != $set_mod) ? ', ' : '') . $row['uid'];
        }

        $db->sql_freeresult($result);

        //

        // Update user level to user for appropriate users

        //

        $sql = 'SELECT u.uid
			FROM ( ( ' . USERS_TABLE . ' u
			LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON ug.uid = u.uid )
			LEFT JOIN ' . AUTH_ACCESS_TABLE . ' aa ON aa.groupid = ug.groupid )
			LEFT JOIN ' . USERS_TABLE_EXT . ' ue ON u.uid=ue.uid
			WHERE ue.user_level NOT IN (' . USER . ', ' . ADMIN . ')
			GROUP BY u.uid
			HAVING SUM(aa.auth_mod) = 0';

        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, "Couldn't obtain user/group permissions", '', __LINE__, __FILE__, $sql);
        }

        $unset_mod = '';

        while (false !== ($row = $db->sql_fetchrow($result))) {
            $unset_mod .= (('' != $unset_mod) ? ', ' : '') . $row['uid'];
        }

        $db->sql_freeresult($result);

        if ('' != $set_mod) {
            $sql = 'UPDATE ' . USERS_TABLE_EXT . '
				SET user_level = ' . MOD . "
				WHERE uid IN ($set_mod)";

            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, "Couldn't update user level", '', __LINE__, __FILE__, $sql);
            }
        }

        if ('' != $unset_mod) {
            $sql = 'UPDATE ' . USERS_TABLE_EXT . '
				SET user_level = ' . USER . "
				WHERE uid IN ($unset_mod)";

            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, "Couldn't update user level", '', __LINE__, __FILE__, $sql);
            }
        }

        message_die(GENERAL_MESSAGE, $message);
    }
} elseif (('user' == $mode && (isset($_POST['username']) || $user_id)) || ('group' == $mode && $groupid)) {
    if (isset($_POST['username'])) {
        $this_userdata = get_userdata($_POST['username']);

        if (!is_array($this_userdata)) {
            message_die(GENERAL_MESSAGE, $lang['No_such_user']);
        }

        $user_id = $this_userdata['uid'];
    }

    //

    // Front end

    //

    $sql = 'SELECT *
		FROM ' . FORUMS_TABLE . ' f
		ORDER BY forum_order';

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, "Couldn't obtain forum information", '', __LINE__, __FILE__, $sql);
    }

    $forum_access = [];

    while (false !== ($row = $db->sql_fetchrow($result))) {
        $forum_access[] = $row;
    }

    $db->sql_freeresult($result);

    if (empty($adv)) {
        for ($i = 0, $iMax = count($forum_access); $i < $iMax; $i++) {
            $forum_id = $forum_access[$i]['forum_id'];

            $forum_auth_level[$forum_id] = AUTH_ALL;

            for ($j = 0, $jMax = count($forum_auth_fields); $j < $jMax; $j++) {
                $forum_access[$i][$forum_auth_fields[$j]] . ' :: ';

                if (AUTH_ACL == $forum_access[$i][$forum_auth_fields[$j]]) {
                    $forum_auth_level[$forum_id] = AUTH_ACL;

                    $forum_auth_level_fields[$forum_id][] = $forum_auth_fields[$j];
                }
            }
        }
    }

    $sql = 'SELECT u.uid as user_id, u.uname as username, ue.user_level, g.groupid, g.name AS group_name 
			FROM ' . USERS_TABLE . ' u, ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug 
			LEFT JOIN ' . USERS_TABLE_EXT . ' ue ON u.uid=ue.uid
			WHERE ';

    $sql .= ('user' == $mode) ? "u.uid = $user_id AND ug.uid = u.uid AND g.groupid = ug.groupid" : "g.groupid = $groupid AND ug.groupid = g.groupid AND u.uid = ug.uid";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, "Couldn't obtain user/group information", '', __LINE__, __FILE__, $sql);
    }

    $ug_info = [];

    while (false !== ($row = $db->sql_fetchrow($result))) {
        $ug_info[] = $row;
    }

    $db->sql_freeresult($result);

    $sql = ('user' == $mode) ? 'SELECT aa.* FROM ' . AUTH_ACCESS_TABLE . ' aa, ' . USER_GROUP_TABLE . ' ug, ' . GROUPS_TABLE . " g WHERE ug.uid = $user_id AND g.groupid = ug.groupid AND aa.groupid = ug.groupid" : 'SELECT * FROM ' . AUTH_ACCESS_TABLE . " WHERE groupid = $groupid";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, "Couldn't obtain user/group permissions", '', __LINE__, __FILE__, $sql);
    }

    $auth_access = [];

    $auth_access_count = [];

    while (false !== ($row = $db->sql_fetchrow($result))) {
        $auth_access[$row['forum_id']][] = $row;

        $auth_access_count[$row['forum_id']]++;
    }

    $db->sql_freeresult($result);

    $is_admin = ('user' == $mode) ? ((ADMIN == $ug_info[0]['user_level'] && ANONYMOUS != $ug_info[0]['user_id']) ? 1 : 0) : 0;

    for ($i = 0, $iMax = count($forum_access); $i < $iMax; $i++) {
        $forum_id = $forum_access[$i]['forum_id'];

        unset($prev_acl_setting);

        for ($j = 0, $jMax = count($forum_auth_fields); $j < $jMax; $j++) {
            $key = $forum_auth_fields[$j];

            $value = $forum_access[$i][$key];

            switch ($value) {
                case AUTH_ALL:
                case AUTH_REG:
                    $auth_ug[$forum_id][$key] = 1;
                    break;
                case AUTH_ACL:
                    $auth_ug[$forum_id][$key] = (!empty($auth_access_count[$forum_id])) ? check_auth(AUTH_ACL, $key, $auth_access[$forum_id], $is_admin) : 0;
                    $auth_field_acl[$forum_id][$key] = $auth_ug[$forum_id][$key];

                    if (isset($prev_acl_setting)) {
                        if ($prev_acl_setting != $auth_ug[$forum_id][$key] && empty($adv)) {
                            $adv = 1;
                        }
                    }

                    $prev_acl_setting = $auth_ug[$forum_id][$key];

                    break;
                case AUTH_MOD:
                    $auth_ug[$forum_id][$key] = (!empty($auth_access_count[$forum_id])) ? check_auth(AUTH_MOD, $key, $auth_access[$forum_id], $is_admin) : 0;
                    break;
                case AUTH_ADMIN:
                    $auth_ug[$forum_id][$key] = $is_admin;
                    break;
                default:
                    $auth_ug[$forum_id][$key] = 0;
                    break;
            }
        }

        //

        // Is user a moderator?

        //

        $auth_ug[$forum_id]['auth_mod'] = (!empty($auth_access_count[$forum_id])) ? check_auth(AUTH_MOD, 'auth_mod', $auth_access[$forum_id], 0) : 0;
    }

    $i = 0;

    @reset($auth_ug);

    while (list($forum_id, $user_ary) = @each($auth_ug)) {
        if (empty($adv)) {
            if (AUTH_ACL == $forum_auth_level[$forum_id]) {
                $allowed = 1;

                for ($j = 0, $jMax = count($forum_auth_level_fields[$forum_id]); $j < $jMax; $j++) {
                    if (!$auth_ug[$forum_id][$forum_auth_level_fields[$forum_id][$j]]) {
                        $allowed = 0;
                    }
                }

                $optionlist_acl = '<select name="private[' . $forum_id . ']">';

                if ($is_admin || $user_ary['auth_mod']) {
                    $optionlist_acl .= '<option value="1">' . $lang['Allowed_Access'] . '</option>';
                } elseif ($allowed) {
                    $optionlist_acl .= '<option value="1" selected="selected">' . $lang['Allowed_Access'] . '</option><option value="0">' . $lang['Disallowed_Access'] . '</option>';
                } else {
                    $optionlist_acl .= '<option value="1">' . $lang['Allowed_Access'] . '</option><option value="0" selected="selected">' . $lang['Disallowed_Access'] . '</option>';
                }

                $optionlist_acl .= '</select>';
            } else {
                $optionlist_acl = '&nbsp;';
            }
        } else {
            for ($j = 0, $jMax = count($forum_access); $j < $jMax; $j++) {
                if ($forum_access[$j]['forum_id'] == $forum_id) {
                    for ($k = 0, $kMax = count($forum_auth_fields); $k < $kMax; $k++) {
                        $field_name = $forum_auth_fields[$k];

                        if (AUTH_ACL == $forum_access[$j][$field_name]) {
                            $optionlist_acl_adv[$forum_id][$k] = '<select name="private_' . $field_name . '[' . $forum_id . ']">';

                            if (isset($auth_field_acl[$forum_id][$field_name]) && !($is_admin || $user_ary['auth_mod'])) {
                                if (!$auth_field_acl[$forum_id][$field_name]) {
                                    $optionlist_acl_adv[$forum_id][$k] .= '<option value="1">' . $lang['ON'] . '</option><option value="0" selected="selected">' . $lang['OFF'] . '</option>';
                                } else {
                                    $optionlist_acl_adv[$forum_id][$k] .= '<option value="1" selected="selected">' . $lang['ON'] . '</option><option value="0">' . $lang['OFF'] . '</option>';
                                }
                            } else {
                                if ($is_admin || $user_ary['auth_mod']) {
                                    $optionlist_acl_adv[$forum_id][$k] .= '<option value="1">' . $lang['ON'] . '</option>';
                                } else {
                                    $optionlist_acl_adv[$forum_id][$k] .= '<option value="1">' . $lang['ON'] . '</option><option value="0" selected="selected">' . $lang['OFF'] . '</option>';
                                }
                            }

                            $optionlist_acl_adv[$forum_id][$k] .= '</select>';
                        }
                    }
                }
            }
        }

        $optionlist_mod = '<select name="moderator[' . $forum_id . ']">';

        $optionlist_mod .= ($user_ary['auth_mod']) ? '<option value="1" selected="selected">' . $lang['Is_Moderator'] . '</option><option value="0">' . $lang['Not_Moderator'] . '</option>' : '<option value="1">'
                                                                                                                                                                                               . $lang['Is_Moderator']
                                                                                                                                                                                               . '</option><option value="0" selected="selected">'
                                                                                                                                                                                               . $lang['Not_Moderator']
                                                                                                                                                                                               . '</option>';

        $optionlist_mod .= '</select>';

        $row_class = (!($i % 2)) ? 'row2' : 'row1';

        $row_color = (!($i % 2)) ? $theme['td_color1'] : $theme['td_color2'];

        $template->assign_block_vars(
            'forums',
            [
                'ROW_COLOR' => '#' . $row_color,
                'ROW_CLASS' => $row_class,
                'FORUM_NAME' => $forum_access[$i]['forum_name'],

                'U_FORUM_AUTH' => append_sid('admin_forumauth.php?f=' . $forum_access[$i]['forum_id']),

                'S_MOD_SELECT' => $optionlist_mod,
            ]
        );

        if (!$adv) {
            $template->assign_block_vars(
                'forums.aclvalues',
                [
                    'S_ACL_SELECT' => $optionlist_acl,
                ]
            );
        } else {
            for ($j = 0, $jMax = count($forum_auth_fields); $j < $jMax; $j++) {
                $template->assign_block_vars(
                    'forums.aclvalues',
                    [
                        'S_ACL_SELECT' => $optionlist_acl_adv[$forum_id][$j],
                    ]
                );
            }
        }

        $i++;
    }

    @reset($auth_user);

    if ('user' == $mode) {
        $t_username = $ug_info[0]['username'];

        $s_user_type = ($is_admin) ? '<select name="userlevel"><option value="admin" selected="selected">' . $lang['Auth_Admin'] . '</option><option value="user">' . $lang['Auth_User'] . '</option></select>' : '<select name="userlevel"><option value="admin">'
                                                                                                                                                                                                                  . $lang['Auth_Admin']
                                                                                                                                                                                                                  . '</option><option value="user" selected="selected">'
                                                                                                                                                                                                                  . $lang['Auth_User']
                                                                                                                                                                                                                  . '</option></select>';
    } else {
        $t_groupname = $ug_info[0]['group_name'];
    }

    $name = [];

    $id = [];

    for ($i = 0, $iMax = count($ug_info); $i < $iMax; $i++) {
        if (('user' == $mode) || 'group' == $mode) {
            $name[] = ('user' == $mode) ? $ug_info[$i]['group_name'] : $ug_info[$i]['username'];

            $id[] = ('user' == $mode) ? (int)$ug_info[$i]['groupid'] : (int)$ug_info[$i]['user_id'];
        }
    }

    if (count($name)) {
        $t_usergroup_list = '';

        for ($i = 0, $iMax = count($ug_info); $i < $iMax; $i++) {
            $ug = ('user' == $mode) ? 'group&amp;' . POST_GROUPS_URL : 'user&amp;' . POST_USERS_URL;

            $t_usergroup_list .= (('' != $t_usergroup_list) ? ', ' : '') . '<a href="' . append_sid("admin_ug_auth.php?mode=$ug=" . $id[$i]) . '">' . $name[$i] . '</a>';
        }
    } else {
        $t_usergroup_list = $lang['None'];
    }

    $s_column_span = 2; // Two columns always present

    if (!$adv) {
        $template->assign_block_vars(
            'acltype',
            [
                'L_UG_ACL_TYPE' => $lang['Simple_Permission'],
            ]
        );

        $s_column_span++;
    } else {
        for ($i = 0, $iMax = count($forum_auth_fields); $i < $iMax; $i++) {
            $cell_title = $field_names[$forum_auth_fields[$i]];

            $template->assign_block_vars(
                'acltype',
                [
                    'L_UG_ACL_TYPE' => $cell_title,
                ]
            );

            $s_column_span++;
        }
    }

    //

    // Dump in the page header ...

    //

    require __DIR__ . '/page_header_admin.php';

    $template->set_filenames(
        [
            'body' => 'admin/auth_ug_body.tpl',
        ]
    );

    $adv_switch = (empty($adv)) ? 1 : 0;

    $u_ug_switch = ('user' == $mode) ? POST_USERS_URL . '=' . $user_id : POST_GROUPS_URL . '=' . $groupid;

    $switch_mode = append_sid("admin_ug_auth.php?mode=$mode&amp;" . $u_ug_switch . "&amp;adv=$adv_switch");

    $switch_mode_text = (empty($adv)) ? $lang['Advanced_mode'] : $lang['Simple_mode'];

    $u_switch_mode = '<a href="' . $switch_mode . '">' . $switch_mode_text . '</a>';

    $s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '"><input type="hidden" name="adv" value="' . $adv . '">';

    $s_hidden_fields .= ('user' == $mode) ? '<input type="hidden" name="' . POST_USERS_URL . '" value="' . $user_id . '">' : '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $groupid . '">';

    if ('user' == $mode) {
        $template->assign_block_vars('switch_user_auth', []);

        $template->assign_vars(
            [
                'USERNAME' => $t_username,
                'USER_LEVEL' => $lang['User_Level'] . ' : ' . $s_user_type,
                'USER_GROUP_MEMBERSHIPS' => $lang['Group_memberships'] . ' : ' . $t_usergroup_list,
            ]
        );
    } else {
        $template->assign_block_vars('switch_group_auth', []);

        $template->assign_vars(
            [
                'USERNAME' => $t_groupname,
                'GROUP_MEMBERSHIP' => $lang['Usergroup_members'] . ' : ' . $t_usergroup_list,
            ]
        );
    }

    $template->assign_vars(
        [
            'L_USER_OR_GROUPNAME' => ('user' == $mode) ? $lang['Username'] : $lang['Group_name'],

            'L_AUTH_TITLE' => ('user' == $mode) ? $lang['Auth_Control_User'] : $lang['Auth_Control_Group'],
            'L_AUTH_EXPLAIN' => ('user' == $mode) ? $lang['User_auth_explain'] : $lang['Group_auth_explain'],
            'L_MODERATOR_STATUS' => $lang['Moderator_status'],
            'L_PERMISSIONS' => $lang['Permissions'],
            'L_SUBMIT' => $lang['Submit'],
            'L_RESET' => $lang['Reset'],
            'L_FORUM' => $lang['Forum'],

            'U_USER_OR_GROUP' => append_sid('admin_ug_auth.php'),
            'U_SWITCH_MODE' => $u_switch_mode,

            'S_COLUMN_SPAN' => $s_column_span,
            'S_AUTH_ACTION' => append_sid('admin_ug_auth.php'),
            'S_HIDDEN_FIELDS' => $s_hidden_fields,
        ]
    );
} else {
    //

    // Select a user/group

    //

    require __DIR__ . '/page_header_admin.php';

    $template->set_filenames(
        [
            'body' => ('user' == $mode) ? 'admin/user_select_body.tpl' : 'admin/auth_select_body.tpl',
        ]
    );

    if ('user' == $mode) {
        $template->assign_vars(
            [
                'L_FIND_USERNAME' => $lang['Find_username'],

                'U_SEARCH_USER' => append_sid('../search.php?mode=searchuser'),
            ]
        );
    } else {
        $sql = 'SELECT groupid, name AS group_name
			FROM ' . GROUPS_TABLE . '';

        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, "Couldn't get group list", '', __LINE__, __FILE__, $sql);
        }

        if ($row = $db->sql_fetchrow($result)) {
            $select_list = '<select name="' . POST_GROUPS_URL . '">';

            do {
                $select_list .= '<option value="' . $row['groupid'] . '">' . $row['group_name'] . '</option>';
            } while (false !== ($row = $db->sql_fetchrow($result)));

            $select_list .= '</select>';
        }

        $template->assign_vars(
            [
                'S_AUTH_SELECT' => $select_list,
            ]
        );
    }

    $s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '">';

    $l_type = ('user' == $mode) ? 'USER' : 'AUTH';

    $template->assign_vars(
        [
            'L_' . $l_type . '_TITLE' => ('user' == $mode) ? $lang['Auth_Control_User'] : $lang['Auth_Control_Group'],
            'L_' . $l_type . '_EXPLAIN' => ('user' == $mode) ? $lang['User_auth_explain'] : $lang['Group_auth_explain'],
            'L_' . $l_type . '_SELECT' => ('user' == $mode) ? $lang['Select_a_User'] : $lang['Select_a_Group'],
            'L_LOOK_UP' => ('user' == $mode) ? $lang['Look_up_User'] : $lang['Look_up_Group'],

            'S_HIDDEN_FIELDS' => $s_hidden_fields,
            'S_' . $l_type . '_ACTION' => append_sid('admin_ug_auth.php'),
        ]
    );
}

$template->pparse('body');

require __DIR__ . '/page_footer_admin.php';
