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
 *                               constants.php
 *                            -------------------
 *   begin                : Saturday', Feb 13', 2001
 *   copyright            : ('C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: constants.php,v 1.11 2004/12/03 23:51:42 blackdeath_csmc Exp $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License', or
 *   ('at your option) any later version.
 *
 ***************************************************************************/

if (!defined('IN_PHPBB')) {
    die('Hacking attempt');
}

// Debug Level
//define('DEBUG', 1); // Debugging on
define('DEBUG', 1); // Debugging off

// User Levels <- Do not change the values of USER or ADMIN
define('DELETED', -1);
define('ANONYMOUS', 0);

define('USER', 0);
define('ADMIN', 1);
define('MOD', 2);

// User related
define('USER_ACTIVATION_NONE', 0);
define('USER_ACTIVATION_SELF', 1);
define('USER_ACTIVATION_ADMIN', 2);

define('USER_AVATAR_NONE', 0);
define('USER_AVATAR_UPLOAD', 1);
define('USER_AVATAR_REMOTE', 2);
define('USER_AVATAR_GALLERY', 3);

// Group settings
define('GROUP_OPEN', 0);
define('GROUP_CLOSED', 1);
define('GROUP_HIDDEN', 2);

// Forum state
define('FORUM_UNLOCKED', 0);
define('FORUM_LOCKED', 1);

// Topic status
define('TOPIC_UNLOCKED', 0);
define('TOPIC_LOCKED', 1);
define('TOPIC_MOVED', 2);
define('TOPIC_WATCH_NOTIFIED', 1);
define('TOPIC_WATCH_UN_NOTIFIED', 0);

// Topic types
define('POST_NORMAL', 0);
define('POST_STICKY', 1);
define('POST_ANNOUNCE', 2);
define('POST_GLOBAL_ANNOUNCE', 3);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);

// Error codes
define('GENERAL_MESSAGE', 200);
define('GENERAL_ERROR', 202);
define('CRITICAL_MESSAGE', 203);
define('CRITICAL_ERROR', 204);

// Private messaging
define('PRIVMSGS_READ_MAIL', 0);
define('PRIVMSGS_NEW_MAIL', 1);
define('PRIVMSGS_SENT_MAIL', 2);
define('PRIVMSGS_SAVED_IN_MAIL', 3);
define('PRIVMSGS_SAVED_OUT_MAIL', 4);
define('PRIVMSGS_UNREAD_MAIL', 5);

// URL PARAMETERS
define('POST_TOPIC_URL', 't');
define('POST_CAT_URL', 'c');
define('POST_FORUM_URL', 'f');
define('POST_USERS_URL', 'u');
define('POST_POST_URL', 'p');
define('POST_GROUPS_URL', 'g');

// Session parameters
define('SESSION_METHOD_COOKIE', 100);
define('SESSION_METHOD_GET', 101);

// Page numbers for session handling
define('PAGE_INDEX', 0);
define('PAGE_LOGIN', -1);
define('PAGE_SEARCH', -2);
define('PAGE_REGISTER', -3);
define('PAGE_PROFILE', -4);
define('PAGE_VIEWONLINE', -6);
define('PAGE_VIEWMEMBERS', -7);
define('PAGE_FAQ', -8);
define('PAGE_POSTING', -9);
define('PAGE_PRIVMSGS', -10);
define('PAGE_GROUPCP', -11);
define('PAGE_TOPIC_OFFSET', 5000);

// Auth settings
define('AUTH_LIST_ALL', 0);
define('AUTH_ALL', 0);

define('AUTH_REG', 1);
define('AUTH_ACL', 2);
define('AUTH_MOD', 3);
define('AUTH_ADMIN', 5);

define('AUTH_VIEW', 1);
define('AUTH_READ', 2);
define('AUTH_POST', 3);
define('AUTH_REPLY', 4);
define('AUTH_EDIT', 5);
define('AUTH_DELETE', 6);
define('AUTH_ANNOUNCE', 7);
define('AUTH_STICKY', 8);
define('AUTH_POLLCREATE', 9);
define('AUTH_VOTE', 10);
define('AUTH_ATTACH', 11);

// Table names
global $xoopsDB;
define('AUTH_ACCESS_TABLE', $xoopsDB->prefix('phpbbi_auth_access'));
define('BANLIST_TABLE', $xoopsDB->prefix('phpbbi_banlist'));
define('CATEGORIES_TABLE', $xoopsDB->prefix('phpbbi_categories'));
define('CONFIG_TABLE', $xoopsDB->prefix('phpbbi_config'));
define('DISALLOW_TABLE', $xoopsDB->prefix('phpbbi_disallow'));
define('FORUMS_TABLE', $xoopsDB->prefix('phpbbi_forums'));
define('GROUPS_TABLE', $xoopsDB->prefix('groups'));
define('POSTS_TABLE', $xoopsDB->prefix('phpbbi_posts'));
define('POSTS_TEXT_TABLE', $xoopsDB->prefix('phpbbi_posts_text'));
define('PRIVMSGS_TABLE', $xoopsDB->prefix('phpbbi_privmsgs'));
define('PRIVMSGS_TEXT_TABLE', $xoopsDB->prefix('phpbbi_privmsgs_text'));
define('PRIVMSGS_IGNORE_TABLE', $xoopsDB->prefix('phpbbi_privmsgs_ignore'));
define('PRUNE_TABLE', $xoopsDB->prefix('phpbbi_forum_prune'));
define('RANKS_TABLE', $xoopsDB->prefix('ranks'));
define('SEARCH_TABLE', $xoopsDB->prefix('phpbbi_search_results'));
define('SEARCH_WORD_TABLE', $xoopsDB->prefix('phpbbi_search_wordlist'));
define('SEARCH_MATCH_TABLE', $xoopsDB->prefix('phpbbi_search_wordmatch'));
define('SESSIONS_TABLE', $xoopsDB->prefix('session'));
define('SESSIONS_TABLE_EXT', $xoopsDB->prefix('phpbbi_session_ext'));
define('SMILIES_TABLE', $xoopsDB->prefix('smiles'));
define('THEMES_TABLE', $xoopsDB->prefix('phpbbi_themes'));
define('THEMES_NAME_TABLE', $xoopsDB->prefix('phpbbi_themes_name'));
define('TOPICS_TABLE', $xoopsDB->prefix('phpbbi_topics'));
define('TOPICS_WATCH_TABLE', $xoopsDB->prefix('phpbbi_topics_watch'));
define('USER_GROUP_TABLE', $xoopsDB->prefix('groups_users_link'));
define('USERS_TABLE', $xoopsDB->prefix('users'));
define('USERS_TABLE_EXT', $xoopsDB->prefix('phpbbi_user_ext'));
define('WORDS_TABLE', $xoopsDB->prefix('phpbbi_words'));
define('VOTE_DESC_TABLE', $xoopsDB->prefix('phpbbi_vote_desc'));
define('VOTE_RESULTS_TABLE', $xoopsDB->prefix('phpbbi_vote_results'));
define('VOTE_USERS_TABLE', $xoopsDB->prefix('phpbbi_vote_voters'));
