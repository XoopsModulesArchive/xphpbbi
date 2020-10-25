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
 *                            functions_admin.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: functions_admin.php,v 1.8 2004/12/03 23:51:42 blackdeath_csmc Exp $
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
 **************************************************************************
 * @param        $box_name
 * @param bool   $ignore_forum
 * @param string $select_forum
 * @return string
 */

//
// Simple version of jumpbox, just lists authed forums
//
function make_forum_select($box_name, $ignore_forum = false, $select_forum = '')
{
    global $db, $userdata;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $is_auth_ary = auth(AUTH_READ, AUTH_LIST_ALL, $userdata);

    $sql = 'SELECT forum_id, forum_name
		FROM ' . FORUMS_TABLE . ' 
		ORDER BY cat_id, forum_order';

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Couldn not obtain forums information', '', __LINE__, __FILE__, $sql);
    }

    $forum_list = '';

    while (false !== ($row = $db->sql_fetchrow($result))) {
        if ($is_auth_ary[$row['forum_id']]['auth_read'] && $ignore_forum != $row['forum_id']) {
            $selected = ($select_forum == $row['forum_id']) ? ' selected="selected"' : '';

            $forum_list .= '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $row['forum_name'] . '</option>';
        }
    }

    $forum_list = ('' == $forum_list) ? '<option value="-1">-- ! No Forums ! --</option>' : '<select name="' . $box_name . '">' . $forum_list . '</select>';

    return $forum_list;
}

//
// Synchronise functions for forums/topics
//
function phpbbi_sync($type, $id = false)
{
    global $db;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    switch ($type) {
        case 'all forums':
            $sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE;
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not get forum IDs', '', __LINE__, __FILE__, $sql);
            }

            while (false !== ($row = $db->sql_fetchrow($result))) {
                phpbbi_sync('forum', $row['forum_id']);
            }
            break;
        case 'all topics':
            $sql = 'SELECT topic_id
				FROM ' . TOPICS_TABLE;
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not get topic ID', '', __LINE__, __FILE__, $sql);
            }

            while (false !== ($row = $db->sql_fetchrow($result))) {
                phpbbi_sync('topic', $row['topic_id']);
            }
            break;
        case 'forum':
            $sql = 'SELECT MAX(post_id) AS last_post, COUNT(post_id) AS total 
				FROM ' . POSTS_TABLE . "  
				WHERE forum_id = $id";
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not get post ID', '', __LINE__, __FILE__, $sql);
            }

            if ($row = $db->sql_fetchrow($result)) {
                $last_post = ($row['last_post']) ?: 0;

                $total_posts = ($row['total']) ?: 0;
            } else {
                $last_post = 0;

                $total_posts = 0;
            }

            $sql = 'SELECT COUNT(topic_id) AS total
				FROM ' . TOPICS_TABLE . "
				WHERE forum_id = $id";
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not get topic count', '', __LINE__, __FILE__, $sql);
            }

            $total_topics = ($row = $db->sql_fetchrow($result)) ? (($row['total']) ?: 0) : 0;

            $sql = 'UPDATE ' . FORUMS_TABLE . "
				SET forum_last_post_id = $last_post, forum_posts = $total_posts, forum_topics = $total_topics
				WHERE forum_id = $id";
            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not update forum', '', __LINE__, __FILE__, $sql);
            }
            break;
        case 'topic':
            $sql = 'SELECT MAX(post_id) AS last_post, MIN(post_id) AS first_post, COUNT(post_id) AS total_posts
				FROM ' . POSTS_TABLE . "
				WHERE topic_id = $id";
            if (!($result = $db->sql_query($sql))) {
                message_die(GENERAL_ERROR, 'Could not get post ID', '', __LINE__, __FILE__, $sql);
            }

            if ($row = $db->sql_fetchrow($result)) {
                $sql = ($row['total_posts']) ? 'UPDATE ' . TOPICS_TABLE . ' SET topic_replies = ' . ($row['total_posts'] - 1) . ', topic_first_post_id = ' . $row['first_post'] . ', topic_last_post_id = ' . $row['last_post'] . " WHERE topic_id = $id" : 'DELETE FROM '
                                                                                                                                                                                                                                                            . TOPICS_TABLE
                                                                                                                                                                                                                                                            . " WHERE topic_id = $id";

                if (!$db->sql_query($sql)) {
                    message_die(GENERAL_ERROR, 'Could not update topic', '', __LINE__, __FILE__, $sql);
                }
            }
            break;
    }

    return true;
}
