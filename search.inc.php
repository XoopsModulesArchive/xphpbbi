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
if (!define('IN_PHPBB', true)) {
    define('IN_PHPBB', true);
}
require XOOPS_ROOT_PATH . '/modules/xphpbbi/includes/constants.php';

function xphpbbi_search($queryarray, $andor, $limit, $offset, $userid)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $sql = 'SELECT pt.post_text, pt.post_subject, p.post_username, p.post_time, p.poster_id, t.topic_id, t.topic_title, t.forum_id, u.uname
			FROM ' . POSTS_TABLE . ' p,
				' . TOPICS_TABLE . ' t,
				' . POSTS_TEXT_TABLE . ' pt,
				' . FORUMS_TABLE . ' f
			LEFT JOIN ' . USERS_TABLE . ' u ON p.poster_id=u.uid
			WHERE t.topic_id = p.topic_id
			AND p.post_id = pt.post_id
			AND f.forum_id = t.forum_id
			';

    if (0 != $userid) {
        $sql .= ' AND p.poster_id = ' . $userid . ' ';
    }

    // because count() returns 1 even if a supplied variable

    // is not an array, we must check if $querryarray is really an array

    if (is_array($queryarray) && $count = count($queryarray)) {
        $sql .= " AND ((pt.post_text LIKE '%$queryarray[0]%' OR p.post_username LIKE '%$queryarray[0]%' OR pt.post_subject LIKE '%$queryarray[0]%' OR t.topic_title LIKE '%$queryarray[0]%' )";

        for ($i = 1; $i < $count; $i++) {
            $sql .= " $andor ";

            $sql .= "(pt.post_text LIKE '%$queryarray[$i]%' OR p.post_username LIKE '%$queryarray[$i]%' OR pt.post_subject LIKE '%$queryarray[$i]%' OR t.topic_title LIKE '%$queryarray[$i]%' )";
        }

        $sql .= ') ';
    }

    $sql .= 'ORDER BY p.post_time DESC';

    $result = $xoopsDB->query($sql, $limit, $offset);

    $ret = [];

    $i = 0;

    while (false !== ($myrow = $xoopsDB->fetchArray($result))) {
        $ret[$i]['image'] = 'images/search.gif';

        $ret[$i]['link'] = 'viewtopic.php?t=' . $myrow['topic_id']; //."&sid=$sid_bb ";

        $ret[$i]['title'] = $myrow['topic_title'];

        $ret[$i]['time'] = $myrow['post_time'];

        $ret[$i]['poster'] = ('Anonymous' == $myrow['uname']) ? $myrow['post_username'] : '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $myrow['poster_id'] . '">' . $myrow['uname'] . '</a>';

        $ret[$i]['uid'] = $myrow['poster_id'];

        $i++;
    }

    //var_dump($ret);exit;

    return $ret;
}
