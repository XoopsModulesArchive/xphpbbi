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

function xphpbbi_show($options)
{
    global $HTTP_COOKIE_VARS;

    global $xoopsDB, $xoopsUser, $xoopsTpl, $xoopsUserIsAdmin;

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    switch ($options[2]) {
        case 'views':
            $order = 't.topic_views';
            break;
        case 'replies':
            $order = 't.topic_replies';
            break;
        case 'time':
        default:
            $order = 'p.post_time';
            break;
    }

    if (is_object($xoopsUser)) {
        $sql = 'SELECT * FROM ' . USERS_TABLE_EXT . " WHERE uid='" . $xoopsUser->getVar('uid') . "'";

        $userinfo = $xoopsDB->fetchArray($xoopsDB->query($sql));
    }

    if (is_object($xoopsUser)) {
        if (ADMIN == $userinfo['user_level'] || $xoopsUserIsAdmin) {
            $auth_level = AUTH_ADMIN;
        } elseif (MOD == $userinfo['user_level']) {
            $auth_level = AUTH_MOD;
        } elseif (USER == $userinfo['user_level']) {
            $auth_level = AUTH_REG;
        }
    } else {
        $auth_level = AUTH_ALL;
    }

    if (is_object($xoopsUser)) {
        $sql = 'SELECT f.forum_id
				FROM ' . FORUMS_TABLE . ' f,' . AUTH_ACCESS_TABLE . ' aa,' . USER_GROUP_TABLE . ' ug
				WHERE ug.uid = ' . $xoopsUser->getVar('uid') . "
				AND (f.forum_id=aa.forum_id
				AND aa.groupid=ug.groupid
				AND (aa.auth_read=1 OR aa.auth_mod=1) OR f.auth_read<='" . $auth_level . "')
				GROUP BY f.forum_id";
    } else {
        $sql = 'SELECT f.forum_id
				FROM ' . FORUMS_TABLE . " f
				WHERE (f.auth_read<='" . $auth_level . "')
				GROUP BY f.forum_id";
    }

    $auth_forums = '';

    $count = 0;

    if ($result = $xoopsDB->query($sql)) {
        while (false !== ($auth_array = $xoopsDB->fetchArray($result))) {
            if (0 == $count) {
                $auth_forums .= ' AND (';
            }

            if ($count > 0) {
                $auth_forums .= ' OR ';
            }

            $auth_forums .= "f.forum_id='" . $auth_array['forum_id'] . "'";

            $count++;
        }
    }

    if ('' != $auth_forums) {
        $auth_forums .= ') ';
    }

    $query = 'SELECT t.topic_id, t.topic_title, t.topic_views, t.topic_replies, t.topic_status, t.topic_type,
			p.post_username, p.poster_id, p.post_time, f.forum_id, f.forum_name, f.forum_status, f.auth_read
			FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . " f
			WHERE t.forum_id = f.forum_id
				AND (t.topic_last_post_id = p.post_id)
				$auth_forums
				ORDER BY " . $order . ' DESC';

    if (!$result = $xoopsDB->query($query, $options[0], 0)) {
        return false;
    }

    if (0 != $options[1]) {
        $block['full_view'] = true;
    } else {
        $block['full_view'] = false;
    }

    $block['lang_forum'] = _MB_XPHPBBI_FORUM;

    $block['lang_topic'] = _MB_XPHPBBI_TOPIC;

    $block['lang_replies'] = _MB_XPHPBBI_RPLS;

    $block['lang_views'] = _MB_XPHPBBI_VIEWS;

    $block['lang_by'] = _MB_XPHPBBI_BY;

    $block['lang_lastpost'] = _MB_XPHPBBI_LPOST;

    $block['lang_visitforums'] = _MB_XPHPBBI_VSTFRMS;

    //------------------------

    // Set board_config array

    //------------------------

    $board_config = [];

    $config = $xoopsDB->query('SELECT * FROM ' . CONFIG_TABLE . ' ');

    while (false !== ($bconfig = $xoopsDB->fetchArray($config))) {
        $board_config[$bconfig['config_name']] = $bconfig['config_value'];
    }

    //------------------------

    // End set board_config

    //------------------------

    $tracking_topics = (isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t'])) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) : '';

    $tracking_forums = (isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f'])) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) : '';

    //------------------------

    // Set template name

    //------------------------

    //$tpl_user = $xoopsDB->fetchArray($xoopsDB->query ("SELECT template_name FROM ".THEMES_TABLE." WHERE themes_id = ".$meminfo['user_style'].""));

    $tpl_default = $xoopsDB->fetchArray($xoopsDB->query('SELECT template_name FROM ' . THEMES_TABLE . ' WHERE themes_id = ' . $board_config['default_style'] . ''));

    if ($board_config['override_user_style'] or empty($tpl_user['template_name'])) {
        $tpl_name = $tpl_default['template_name'];
    }

    //else

    //	$tpl_name = $tpl_user['template_name'];

    //------------------------

    // END set template name

    //------------------------

    while (false !== ($arr = $xoopsDB->fetchArray($result))) {
        $sql = 'SELECT uname FROM ' . USERS_TABLE . " WHERE uid = '" . $arr['poster_id'] . "' LIMIT 1";

        $uname = $xoopsDB->fetchArray($xoopsDB->query($sql));

        //------------------------

        // Folder picture start

        //------------------------

        if (TOPIC_MOVED == $arr['topic_status']) {
            $icon_name = 'folder';
        } else {
            if (POST_ANNOUNCE == $arr['topic_type']) {
                $img_name = 'folder_announce';
            } elseif (POST_STICKY == $arr['topic_type']) {
                $img_name = 'folder_sticky';

                $img_name_new = 'folder_sticky_new';
            } elseif (TOPIC_LOCKED == $arr['topic_status']) {
                $img_name = 'folder_locked';

                $img_name_new = 'folder_locked_new';
            } elseif ($arr['topic_replies'] >= $board_config['hot_threshold']) {
                $img_name = 'folder_hot';

                $img_name_new = 'folder_hot_new';
            } else {
                $img_name = 'folder';

                $img_name_new = 'folder_new';
            }

            if (is_object($xoopsUser)) {
                if ($arr['post_time'] > $userinfo['user_lastvisit']) {
                    if (!empty($tracking_topics) || !empty($tracking_forums) || isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'])) {
                        $unread_topics = true;

                        if (!empty($tracking_topics[$arr['topic_id']])) {
                            if ($tracking_topics[$arr['topic_id']] >= $arr['post_time']) {
                                $unread_topics = false;
                            }
                        }

                        if (!empty($tracking_forums[$arr['forum_id']])) {
                            if ($tracking_forums[$arr['forum_id']] >= $arr['post_time']) {
                                $unread_topics = false;
                            }
                        }

                        if (isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'])) {
                            if ($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'] >= $arr['post_time']) {
                                $unread_topics = false;
                            }
                        }

                        if ($unread_topics) {
                            $icon_name = $img_name_new;
                        } else {
                            $icon_name = $img_name;
                        }
                    } else {
                        $icon_name = $img_name_new;
                    }
                } else {
                    $icon_name = $img_name; // End tracking = true
                }
            } else {
                $icon_name = $img_name;    //End uid_bb
            }
        }    // End topic_movie

        //------------------------			//End hack image_icon_show

        //------------------------

        if (empty($arr['post_username'])) {
            $topic['isuser'] = 1;

            $topic['poster_name'] = $uname['uname'];
        } else {
            $topic['isuser'] = 0;

            $topic['poster_name'] = $arr['post_username'];
        }

        $topic['forum_id'] = $arr['forum_id'];

        $topic['forum_name'] = htmlspecialchars($arr['forum_name'], ENT_QUOTES | ENT_HTML5);

        $topic['id'] = $arr['topic_id'];

        $topic['title'] = htmlspecialchars($arr['topic_title'], ENT_QUOTES | ENT_HTML5);

        $topic['replies'] = $arr['topic_replies'];

        $topic['views'] = $arr['topic_views'];

        $topic['time'] = formatTimestamp($arr['post_time'], 'd-M, H:m A');

        $topic['sess_id'] = session_id();

        $topic['poster_id'] = $arr['poster_id'];

        $topic['pages'] = show_page($arr['topic_replies'], $arr['topic_id']);

        $topic['img_dir'] = XOOPS_URL . '/modules/xphpbbi/templates/' . $tpl_name . '/images/' . $icon_name . '.gif';

        $block['topics'][] = &$topic;

        unset($topic);
    }    // End while

    return $block;
}    // End function

function xphpbbi_edit($options)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $inputtag = "<input type='text' name='options[0]' value='" . $options[0] . "'>";

    $form = sprintf(_MB_XPHPBBI_DISPLAY, $inputtag);

    $form .= '<br>' . _MB_XPHPBBI_DISPLAYF . "&nbsp;<input type='radio' name='options[1]' value='1'";

    if (1 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _YES . "<input type='radio' name='options[1]' value='0'";

    if (0 == $options[1]) {
        $form .= ' checked';
    }

    $form .= '>&nbsp;' . _NO;

    $form .= '<input type="hidden" name="options[2]" value="' . $options[2] . '">';

    return $form;
}

function show_page($data, $t)
{
    global $sid_bb, $board_config;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $pages = 1;

    if (0 == (($data + 1) % $board_config['posts_per_page'])) {
        $pages = ($data + 1) / $board_config['posts_per_page'];
    } else {
        $number = (($data + 1) / $board_config['posts_per_page']);

        $pages = ceil($number);
    }

    $pages_link = '';

    if ($pages > 1) {
        $pages_link = "<span style='font-size:10px; font-weight:bold; font-family:verdana,tahoma;'>(" . _MB_XPHPBBI_PAGES . ' ';

        for ($i = 0; $i < $pages; ++$i) {
            $real_no = $i * $board_config['posts_per_page'];

            $page_no = $i + 1;

            if (4 == $page_no) {
                $pages_link .= "<a href='" . XOOPS_URL . "/modules/xphpbbi/viewtopic.php?t=$t&start=" . ($pages - 1) * $board_config['posts_per_page'] . "'>... $pages</a>";

                break;
            }  

            $pages_link .= "<a href='" . XOOPS_URL . "/modules/xphpbbi/viewtopic.php?t=$t&start=$real_no'> $page_no </a>";
        }

        $pages_link .= ')</span>';
    }

    return $pages_link;
}
