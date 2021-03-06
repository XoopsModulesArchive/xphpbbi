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
 *                             (admin) index.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: index.php,v 1.9 2004/12/03 23:51:41 blackdeath_csmc Exp $
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

//
// Load default header
//
$no_page_header = true;
$phpbb_root_path = './../';
require __DIR__ . '/pagestart.php';

// ---------------
// Begin functions
//
function inarray($needle, $haystack)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    for ($i = 0, $iMax = count($haystack); $i < $iMax; $i++) {
        if ($haystack[$i] == $needle) {
            return true;
        }
    }

    return false;
}

//
// End functions
// -------------

//
// Generate relevant output
//
if (isset($_GET['pane']) && 'left' == $_GET['pane']) {
    $dir = @opendir('.');

    $setmodules = 1;

    while ($file = @readdir($dir)) {
        if (preg_match("/^admin_.*?\.php$/", $file)) {
            include $file;
        }
    }

    @closedir($dir);

    unset($setmodules);

    require __DIR__ . '/page_header_admin.php';

    $template->set_filenames(
        [
            'body' => 'admin/index_navigate.tpl',
        ]
    );

    $template->assign_vars(
        [
            'U_FORUM_INDEX' => append_sid('../index.php'),
            'U_ADMIN_INDEX' => append_sid('index.php?pane=right'),

            'L_FORUM_INDEX' => $lang['Main_index'],
            'L_ADMIN_INDEX' => $lang['Admin_Index'],
            'L_PREVIEW_FORUM' => $lang['Preview_forum'],
        ]
    );

    ksort($module);

    while (list($cat, $action_array) = each($module)) {
        $cat = (!empty($lang[$cat])) ? $lang[$cat] : str_replace("_", ' ', $cat);

        $template->assign_block_vars(
            'catrow',
            [
                'ADMIN_CATEGORY' => $cat,
            ]
        );

        ksort($action_array);

        $row_count = 0;

        while (list($action, $file) = each($action_array)) {
            $row_color = (!($row_count % 2)) ? $theme['td_color1'] : $theme['td_color2'];

            $row_class = (!($row_count % 2)) ? $theme['td_class1'] : $theme['td_class2'];

            $action = (!empty($lang[$action])) ? $lang[$action] : str_replace("_", ' ', $action);

            $template->assign_block_vars(
                'catrow.modulerow',
                [
                    'ROW_COLOR' => '#' . $row_color,
                    'ROW_CLASS' => $row_class,

                    'ADMIN_MODULE' => $action,
                    'U_ADMIN_MODULE' => append_sid($file),
                ]
            );

            $row_count++;
        }
    }

    $template->pparse('body');

    require __DIR__ . '/page_footer_admin.php';
} elseif (isset($_GET['pane']) && 'right' == $_GET['pane']) {
    require __DIR__ . '/page_header_admin.php';

    $template->set_filenames(
        [
            'body' => 'admin/index_body.tpl',
        ]
    );

    $template->assign_vars(
        [
            'L_WELCOME' => $lang['Welcome_phpBB'],
            'L_ADMIN_INTRO' => $lang['Admin_intro'],
            'L_FORUM_STATS' => $lang['Forum_stats'],
            'L_WHO_IS_ONLINE' => $lang['Who_is_Online'],
            'L_USERNAME' => $lang['Username'],
            'L_LOCATION' => $lang['Location'],
            'L_LAST_UPDATE' => $lang['Last_updated'],
            'L_IP_ADDRESS' => $lang['IP_Address'],
            'L_STATISTIC' => $lang['Statistic'],
            'L_VALUE' => $lang['Value'],
            'L_NUMBER_POSTS' => $lang['Number_posts'],
            'L_POSTS_PER_DAY' => $lang['Posts_per_day'],
            'L_NUMBER_TOPICS' => $lang['Number_topics'],
            'L_TOPICS_PER_DAY' => $lang['Topics_per_day'],
            'L_NUMBER_USERS' => $lang['Number_users'],
            'L_USERS_PER_DAY' => $lang['Users_per_day'],
            'L_BOARD_STARTED' => $lang['Board_started'],
            'L_AVATAR_DIR_SIZE' => $lang['Avatar_dir_size'],
            'L_DB_SIZE' => $lang['Database_size'],
            'L_FORUM_LOCATION' => $lang['Forum_Location'],
            'L_STARTED' => $lang['Login'],
            'L_GZIP_COMPRESSION' => $lang['Gzip_compression'],
        ]
    );

    //

    // Get forum statistics

    //

    $total_posts = get_db_stat('postcount');

    $total_users = get_db_stat('usercount');

    $total_topics = get_db_stat('topiccount');

    $start_date = create_date($board_config['default_dateformat'], $board_config['board_startdate'], $board_config['board_timezone']);

    $boarddays = (time() - $board_config['board_startdate']) / 86400;

    $posts_per_day = sprintf('%.2f', $total_posts / $boarddays);

    $topics_per_day = sprintf('%.2f', $total_topics / $boarddays);

    $users_per_day = sprintf('%.2f', $total_users / $boarddays);

    $avatar_dir_size = 0;

    if ($avatar_dir = @opendir($phpbb_root_path . $board_config['avatar_path'])) {
        while ($file = @readdir($avatar_dir)) {
            if ('.' != $file && '..' != $file) {
                $avatar_dir_size += @filesize($phpbb_root_path . $board_config['avatar_path'] . '/' . $file);
            }
        }

        @closedir($avatar_dir);

        //

        // This bit of code translates the avatar directory size into human readable format

        // Borrowed the code from the PHP.net annoted manual, origanally written by:

        // Jesse (jesse@jess.on.ca)

        //

        if ($avatar_dir_size >= 1048576) {
            $avatar_dir_size = round($avatar_dir_size / 1048576 * 100) / 100 . ' MB';
        } elseif ($avatar_dir_size >= 1024) {
            $avatar_dir_size = round($avatar_dir_size / 1024 * 100) / 100 . ' KB';
        } else {
            $avatar_dir_size .= ' Bytes';
        }
    } else {
        // Couldn't open Avatar dir.

        $avatar_dir_size = $lang['Not_available'];
    }

    if ($posts_per_day > $total_posts) {
        $posts_per_day = $total_posts;
    }

    if ($topics_per_day > $total_topics) {
        $topics_per_day = $total_topics;
    }

    if ($users_per_day > $total_users) {
        $users_per_day = $total_users;
    }

    //

    // DB size ... MySQL only

    //

    // This code is heavily influenced by a similar routine

    // in phpMyAdmin 2.2.0

    //

    if (0 === strpos(SQL_LAYER, "mysql")) {
        $sql = 'SELECT VERSION() AS mysql_version';

        if ($result = $db->sql_query($sql)) {
            $row = $db->sql_fetchrow($result);

            $version = $row['mysql_version'];

            if (preg_match("/^(3\.23|4\.)/", $version)) {
                $db_name = (preg_match("/^(3\.23\.[6-9])|(3\.23\.[1-9][1-9])|(4\.)/", $version)) ? "`$dbname`" : $dbname;

                $sql = 'SHOW TABLE STATUS
					FROM ' . $db_name;

                if ($result = $db->sql_query($sql)) {
                    $tabledata_ary = $db->sql_fetchrowset($result);

                    $dbsize = 0;

                    for ($i = 0, $iMax = count($tabledata_ary); $i < $iMax; $i++) {
                        if ('MRG_MyISAM' != $tabledata_ary[$i]['Type']) {
                            if ('' != $table_prefix) {
                                if (mb_strstr($tabledata_ary[$i]['Name'], $table_prefix)) {
                                    $dbsize += $tabledata_ary[$i]['Data_length'] + $tabledata_ary[$i]['Index_length'];
                                }
                            } else {
                                $dbsize += $tabledata_ary[$i]['Data_length'] + $tabledata_ary[$i]['Index_length'];
                            }
                        }
                    }
                } // Else we couldn't get the table status.
            } else {
                $dbsize = $lang['Not_available'];
            }
        } else {
            $dbsize = $lang['Not_available'];
        }
    } elseif (0 === strpos(SQL_LAYER, "mssql")) {
        $sql = 'SELECT ((SUM(size) * 8.0) * 1024.0) AS dbsize
			FROM sysfiles';

        if ($result = $db->sql_query($sql)) {
            $dbsize = ($row = $db->sql_fetchrow($result)) ? (int)$row['dbsize'] : $lang['Not_available'];
        } else {
            $dbsize = $lang['Not_available'];
        }
    } else {
        $dbsize = $lang['Not_available'];
    }

    if (is_int($dbsize)) {
        if ($dbsize >= 1048576) {
            $dbsize = sprintf('%.2f MB', ($dbsize / 1048576));
        } elseif ($dbsize >= 1024) {
            $dbsize = sprintf('%.2f KB', ($dbsize / 1024));
        } else {
            $dbsize = sprintf('%.2f Bytes', $dbsize);
        }
    }

    $template->assign_vars(
        [
            'NUMBER_OF_POSTS' => $total_posts,
            'NUMBER_OF_TOPICS' => $total_topics,
            'NUMBER_OF_USERS' => $total_users,
            'START_DATE' => $start_date,
            'POSTS_PER_DAY' => $posts_per_day,
            'TOPICS_PER_DAY' => $topics_per_day,
            'USERS_PER_DAY' => $users_per_day,
            'AVATAR_DIR_SIZE' => $avatar_dir_size,
            'DB_SIZE' => $dbsize,
            'GZIP_COMPRESSION' => ($board_config['gzip_compress']) ? $lang['ON'] : $lang['OFF'],
        ]
    );

    //

    // End forum statistics

    //

    //

    // Get users online information.

    //

    $sql = 'SELECT u.uid, u.uname, ue.user_session_time, ue.user_session_page, se.session_logged_in, s.sess_ip, se.session_start
		FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . ' s
		LEFT JOIN ' . USERS_TABLE_EXT . ' ue ON u.uid=ue.uid
		LEFT JOIN ' . SESSIONS_TABLE_EXT . ' se ON s.sess_id=se.sess_id
		WHERE se.session_logged_in = ' . true . '
			AND u.uid = se.session_user_id
			AND u.uid <> ' . ANONYMOUS . '
			AND s.sess_updated >= ' . (time() - 300) . '
		ORDER BY ue.user_session_time DESC';

    if (!$result = $db->sql_query($sql)) {
        message_die(GENERAL_ERROR, "Couldn't obtain regd user/online information.", '', __LINE__, __FILE__, $sql);
    }

    $onlinerow_reg = $db->sql_fetchrowset($result);

    $sql = 'SELECT se.session_page, se.session_logged_in, s.sess_updated, s.sess_ip, se.session_start
		FROM ' . SESSIONS_TABLE . ' s
		LEFT JOIN ' . SESSIONS_TABLE_EXT . ' se ON s.sess_id=se.sess_id
		WHERE se.session_logged_in = 0
			AND s.sess_updated >= ' . (time() - 300) . '
		ORDER BY s.sess_updated DESC';

    if (!$result = $db->sql_query($sql)) {
        message_die(GENERAL_ERROR, "Couldn't obtain guest user/online information.", '', __LINE__, __FILE__, $sql);
    }

    $onlinerow_guest = $db->sql_fetchrowset($result);

    $sql = 'SELECT forum_name, forum_id
		FROM ' . FORUMS_TABLE;

    if ($forums_result = $db->sql_query($sql)) {
        while (false !== ($forumsrow = $db->sql_fetchrow($forums_result))) {
            $forum_data[$forumsrow['forum_id']] = $forumsrow['forum_name'];
        }
    } else {
        message_die(GENERAL_ERROR, "Couldn't obtain user/online forums information.", '', __LINE__, __FILE__, $sql);
    }

    $reg_userid_ary = [];

    if (count($onlinerow_reg)) {
        $registered_users = 0;

        for ($i = 0, $iMax = count($onlinerow_reg); $i < $iMax; $i++) {
            if (!inarray($onlinerow_reg[$i]['uid'], $reg_userid_ary)) {
                $reg_userid_ary[] = $onlinerow_reg[$i]['uid'];

                $username = $onlinerow_reg[$i]['uname'];

                if ($onlinerow_reg[$i]['user_allow_viewonline'] || ADMIN == $userdata['user_level']) {
                    $registered_users++;

                    $hidden = false;
                } else {
                    $hidden_users++;

                    $hidden = true;
                }

                if ($onlinerow_reg[$i]['user_session_page'] < 1) {
                    switch ($onlinerow_reg[$i]['user_session_page']) {
                        case PAGE_INDEX:
                            $location = $lang['Forum_index'];
                            $location_url = 'index.php?pane=right';
                            break;
                        case PAGE_POSTING:
                            $location = $lang['Posting_message'];
                            $location_url = 'index.php?pane=right';
                            break;
                        case PAGE_LOGIN:
                            $location = $lang['Logging_on'];
                            $location_url = 'index.php?pane=right';
                            break;
                        case PAGE_SEARCH:
                            $location = $lang['Searching_forums'];
                            $location_url = 'index.php?pane=right';
                            break;
                        case PAGE_PROFILE:
                            $location = $lang['Viewing_profile'];
                            $location_url = 'index.php?pane=right';
                            break;
                        case PAGE_VIEWONLINE:
                            $location = $lang['Viewing_online'];
                            $location_url = 'index.php?pane=right';
                            break;
                        case PAGE_VIEWMEMBERS:
                            $location = $lang['Viewing_member_list'];
                            $location_url = 'index.php?pane=right';
                            break;
                        case PAGE_PRIVMSGS:
                            $location = $lang['Viewing_priv_msgs'];
                            $location_url = 'index.php?pane=right';
                            break;
                        case PAGE_FAQ:
                            $location = $lang['Viewing_FAQ'];
                            $location_url = 'index.php?pane=right';
                            break;
                        default:
                            $location = $lang['Forum_index'];
                            $location_url = 'index.php?pane=right';
                    }
                } else {
                    $location_url = append_sid('admin_forums.php?mode=editforum&amp;' . POST_FORUM_URL . '=' . $onlinerow_reg[$i]['user_session_page']);

                    $location = $forum_data[$onlinerow_reg[$i]['user_session_page']];
                }

                $row_color = ($registered_users % 2) ? $theme['td_color1'] : $theme['td_color2'];

                $row_class = ($registered_users % 2) ? $theme['td_class1'] : $theme['td_class2'];

                $reg_ip = decode_ip($onlinerow_reg[$i]['sess_ip']);

                $template->assign_block_vars(
                    'reg_user_row',
                    [
                        'ROW_COLOR' => '#' . $row_color,
                        'ROW_CLASS' => $row_class,
                        'USERNAME' => $username,
                        'STARTED' => create_date($board_config['default_dateformat'], $onlinerow_reg[$i]['session_start'], $board_config['board_timezone']),
                        'LASTUPDATE' => create_date($board_config['default_dateformat'], $onlinerow_reg[$i]['user_session_time'], $board_config['board_timezone']),
                        'FORUM_LOCATION' => $location,
                        'IP_ADDRESS' => $reg_ip,

                        'U_WHOIS_IP' => "http://network-tools.com/default.asp?host=$reg_ip",
                        'U_USER_PROFILE' => append_sid('admin_users.php?mode=edit&amp;' . POST_USERS_URL . '=' . $onlinerow_reg[$i]['uid']),
                        'U_FORUM_LOCATION' => append_sid($location_url),
                    ]
                );
            }
        }
    } else {
        $template->assign_vars(
            [
                'L_NO_REGISTERED_USERS_BROWSING' => $lang['No_users_browsing'],
            ]
        );
    }

    //

    // Guest users

    //

    if (count($onlinerow_guest)) {
        $guest_users = 0;

        for ($i = 0, $iMax = count($onlinerow_guest); $i < $iMax; $i++) {
            $guest_userip_ary[] = $onlinerow_guest[$i]['sess_ip'];

            $guest_users++;

            if ($onlinerow_guest[$i]['session_page'] < 1) {
                switch ($onlinerow_guest[$i]['session_page']) {
                    case PAGE_INDEX:
                        $location = $lang['Forum_index'];
                        $location_url = 'index.php?pane=right';
                        break;
                    case PAGE_POSTING:
                        $location = $lang['Posting_message'];
                        $location_url = 'index.php?pane=right';
                        break;
                    case PAGE_LOGIN:
                        $location = $lang['Logging_on'];
                        $location_url = 'index.php?pane=right';
                        break;
                    case PAGE_SEARCH:
                        $location = $lang['Searching_forums'];
                        $location_url = 'index.php?pane=right';
                        break;
                    case PAGE_PROFILE:
                        $location = $lang['Viewing_profile'];
                        $location_url = 'index.php?pane=right';
                        break;
                    case PAGE_VIEWONLINE:
                        $location = $lang['Viewing_online'];
                        $location_url = 'index.php?pane=right';
                        break;
                    case PAGE_VIEWMEMBERS:
                        $location = $lang['Viewing_member_list'];
                        $location_url = 'index.php?pane=right';
                        break;
                    case PAGE_PRIVMSGS:
                        $location = $lang['Viewing_priv_msgs'];
                        $location_url = 'index.php?pane=right';
                        break;
                    case PAGE_FAQ:
                        $location = $lang['Viewing_FAQ'];
                        $location_url = 'index.php?pane=right';
                        break;
                    default:
                        $location = $lang['Forum_index'];
                        $location_url = 'index.php?pane=right';
                }
            } else {
                $location_url = append_sid('admin_forums.php?mode=editforum&amp;' . POST_FORUM_URL . '=' . $onlinerow_guest[$i]['session_page']);

                $location = $forum_data[$onlinerow_guest[$i]['session_page']];
            }

            $row_color = ($guest_users % 2) ? $theme['td_color1'] : $theme['td_color2'];

            $row_class = ($guest_users % 2) ? $theme['td_class1'] : $theme['td_class2'];

            $guest_ip = decode_ip($onlinerow_guest[$i]['sess_ip']);

            $template->assign_block_vars(
                'guest_user_row',
                [
                    'ROW_COLOR' => '#' . $row_color,
                    'ROW_CLASS' => $row_class,
                    'USERNAME' => $lang['Guest'],
                    'STARTED' => create_date($board_config['default_dateformat'], $onlinerow_guest[$i]['session_start'], $board_config['board_timezone']),
                    'LASTUPDATE' => create_date($board_config['default_dateformat'], $onlinerow_guest[$i]['sess_updated'], $board_config['board_timezone']),
                    'FORUM_LOCATION' => $location,
                    'IP_ADDRESS' => $guest_ip,

                    'U_WHOIS_IP' => "http://network-tools.com/default.asp?host=$guest_ip",
                    'U_FORUM_LOCATION' => append_sid($location_url),
                ]
            );
        }
    } else {
        $template->assign_vars(
            [
                'L_NO_GUESTS_BROWSING' => $lang['No_users_browsing'],
            ]
        );
    }

    $template->pparse('body');

    require __DIR__ . '/page_footer_admin.php';
} else {
    //

    // Generate frameset

    //

    $template->set_filenames(
        [
            'body' => 'admin/index_frameset.tpl',
        ]
    );

    $template->assign_vars(
        [
            'S_FRAME_NAV' => append_sid('index.php?pane=left'),
            'S_FRAME_MAIN' => append_sid('index.php?pane=right'),
        ]
    );

    header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    $template->pparse('body');

    $db->sql_close();

    exit;
}
