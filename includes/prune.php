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
 *                                 prune.php
 *                            -------------------
 *   begin                : Thursday, June 14, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: prune.php,v 1.9 2004/12/03 23:51:42 blackdeath_csmc Exp $
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

if (!defined('IN_PHPBB')) {
    die('Hacking attempt');
}

require $phpbb_root_path . 'includes/functions_search.php';

function prune($forum_id, $prune_date, $prune_all = false)
{
    global $db, $lang;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $prune_all = ($prune_all) ? '' : 'AND t.topic_vote = 0 AND t.topic_type <> ' . POST_ANNOUNCE;

    //

    // Those without polls and announcements ... unless told otherwise!

    //

    $sql = 'SELECT t.topic_id
		FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
		WHERE t.forum_id = $forum_id
			$prune_all
			AND ( p.post_id = t.topic_last_post_id
				OR t.topic_last_post_id = 0 )";

    if ('' != $prune_date) {
        $sql .= " AND p.post_time < $prune_date";
    }

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not obtain lists of topics to prune', '', __LINE__, __FILE__, $sql);
    }

    $sql_topics = '';

    while (false !== ($row = $db->sql_fetchrow($result))) {
        $sql_topics .= (('' != $sql_topics) ? ', ' : '') . $row['topic_id'];
    }

    $db->sql_freeresult($result);

    if ('' != $sql_topics) {
        $sql = 'SELECT post_id
			FROM ' . POSTS_TABLE . "
			WHERE forum_id = $forum_id
				AND topic_id IN ($sql_topics)";

        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not obtain list of posts to prune', '', __LINE__, __FILE__, $sql);
        }

        $sql_post = '';

        while (false !== ($row = $db->sql_fetchrow($result))) {
            $sql_post .= (('' != $sql_post) ? ', ' : '') . $row['post_id'];
        }

        $db->sql_freeresult($result);

        if ('' != $sql_post) {
            $sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . "
				WHERE topic_id IN ($sql_topics)";

            if (!$db->sql_query($sql, BEGIN_TRANSACTION)) {
                message_die(GENERAL_ERROR, 'Could not delete watched topics during prune', '', __LINE__, __FILE__, $sql);
            }

            $sql = 'DELETE FROM ' . TOPICS_TABLE . "
				WHERE topic_id IN ($sql_topics)";

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not delete topics during prune', '', __LINE__, __FILE__, $sql);
            }

            $pruned_topics = $db->sql_affectedrows();

            $sql = 'DELETE FROM ' . POSTS_TABLE . "
				WHERE post_id IN ($sql_post)";

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not delete post_text during prune', '', __LINE__, __FILE__, $sql);
            }

            $pruned_posts = $db->sql_affectedrows();

            $sql = 'DELETE FROM ' . POSTS_TEXT_TABLE . "
				WHERE post_id IN ($sql_post)";

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not delete post during prune', '', __LINE__, __FILE__, $sql);
            }

            remove_search_post($sql_post);

            return ['topics' => $pruned_topics, 'posts' => $pruned_posts];
        }
    }

    return ['topics' => 0, 'posts' => 0];
}

//
// Function auto_prune(), this function will read the configuration data from
// the auto_prune table and call the prune function with the necessary info.
//
function auto_prune($forum_id = 0)
{
    global $db, $lang;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $sql = 'SELECT *
		FROM ' . PRUNE_TABLE . "
		WHERE forum_id = $forum_id";

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not read auto_prune table', '', __LINE__, __FILE__, $sql);
    }

    if ($row = $db->sql_fetchrow($result)) {
        if ($row['prune_freq'] && $row['prune_days']) {
            $prune_date = time() - ($row['prune_days'] * 86400);

            $next_prune = time() + ($row['prune_freq'] * 86400);

            prune($forum_id, $prune_date);

            phpbbi_sync('forum', $forum_id);

            $sql = 'UPDATE ' . FORUMS_TABLE . "
				SET prune_next = $next_prune
				WHERE forum_id = $forum_id";

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not update forum table', '', __LINE__, __FILE__, $sql);
            }
        }
    }
}
