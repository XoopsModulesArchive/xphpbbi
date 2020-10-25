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
 *                              functions_search.php
 *                              -------------------
 *     begin                : Wed Sep 05 2001
 *     copyright            : (C) 2002 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: functions_search.php,v 1.9 2004/12/03 23:51:42 blackdeath_csmc Exp $
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 **************************************************************************
 * @param $mode
 * @param $entry
 * @param $stopword_list
 * @param $synonym_list
 * @return string|string[]|null
 */

function clean_words($mode, &$entry, $stopword_list, $synonym_list)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    static $drop_char_match = ['^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!'];

    static $drop_char_replace = [' ', ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', ' ', ' ', ' ', '', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '];

    $entry = ' ' . strip_tags(mb_strtolower($entry)) . ' ';

    if ('post' == $mode) {
        // Replace line endings by a space

        $entry = preg_replace('/[\n\r]/is', ' ', $entry);

        // HTML entities like &nbsp;

        $entry = preg_replace('/\b&[a-z]+;\b/', ' ', $entry);

        // Remove URL's

        $entry = preg_replace('/\b[a-z0-9]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?/', ' ', $entry);

        // Quickly remove BBcode.

        $entry = preg_replace('/\[img:[a-z0-9]{10,}\].*?\[\/img:[a-z0-9]{10,}\]/', ' ', $entry);

        $entry = preg_replace('/\[\/?url(=.*?)?\]/', ' ', $entry);

        $entry = preg_replace('/\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]/', ' ', $entry);
    } elseif ('search' == $mode) {
        $entry = str_replace(' +', ' and ', $entry);

        $entry = str_replace(' -', ' not ', $entry);
    }

    //

    // Filter out strange characters like ^, $, &, change "it's" to "its"

    //

    for ($i = 0, $iMax = count($drop_char_match); $i < $iMax; $i++) {
        $entry = str_replace($drop_char_match[$i], $drop_char_replace[$i], $entry);
    }

    if ('post' == $mode) {
        $entry = str_replace('*', ' ', $entry);

        // 'words' that consist of <3 or >20 characters are removed.

        $entry = preg_replace('/[ ]([\S]{1,2}|[\S]{21,})[ ]/', ' ', $entry);
    }

    if (!empty($stopword_list)) {
        for ($j = 0, $jMax = count($stopword_list); $j < $jMax; $j++) {
            $stopword = trim($stopword_list[$j]);

            if ('post' == $mode || ('not' != $stopword && 'and' != $stopword && 'or' != $stopword)) {
                $entry = str_replace(' ' . trim($stopword) . ' ', ' ', $entry);
            }
        }
    }

    if (!empty($synonym_list)) {
        for ($j = 0, $jMax = count($synonym_list); $j < $jMax; $j++) {
            [$replace_synonym, $match_synonym] = preg_split(' ', trim(mb_strtolower($synonym_list[$j])));

            if ('post' == $mode || ('not' != $match_synonym && 'and' != $match_synonym && 'or' != $match_synonym)) {
                $entry = str_replace(' ' . trim($match_synonym) . ' ', ' ' . trim($replace_synonym) . ' ', $entry);
            }
        }
    }

    return $entry;
}

function split_words($entry, $mode = 'post')
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    // If you experience problems with the new method, uncomment this block.

    /*
        $rex = ( $mode == 'post' ) ? "/\b([\w??-?][\w??-?']*[\w??-?]+|[\w??-?]+?)\b/" : '/(\*?[a-z0-9??-?]+\*?)|\b([a-z0-9??-?]+)\b/';
        preg_match_all($rex, $entry, $split_entries);

        return $split_entries[1];
    */

    // Trim 1+ spaces to one space and split this trimmed string into words.

    return explode(' ', trim(preg_replace('#\s+#', ' ', $entry)));
}

function add_search_words($mode, $post_id, $post_text, $post_title = '')
{
    global $db, $phpbb_root_path, $board_config, $lang;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $stopword_array = @file($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/search_stopwords.txt');

    $synonym_array = @file($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/search_synonyms.txt');

    $search_raw_words = [];

    $search_raw_words['text'] = split_words(clean_words('post', $post_text, $stopword_array, $synonym_array));

    $search_raw_words['title'] = split_words(clean_words('post', $post_title, $stopword_array, $synonym_array));

    @set_time_limit(0);

    $word = [];

    $word_insert_sql = [];

    while (list($word_in, $search_matches) = @each($search_raw_words)) {
        $word_insert_sql[$word_in] = '';

        if (!empty($search_matches)) {
            for ($i = 0, $iMax = count($search_matches); $i < $iMax; $i++) {
                $search_matches[$i] = trim($search_matches[$i]);

                if ('' != $search_matches[$i]) {
                    $word[] = $search_matches[$i];

                    if (!mb_strstr($word_insert_sql[$word_in], "'" . $search_matches[$i] . "'")) {
                        $word_insert_sql[$word_in] .= ('' != $word_insert_sql[$word_in]) ? ", '" . $search_matches[$i] . "'" : "'" . $search_matches[$i] . "'";
                    }
                }
            }
        }
    }

    if (count($word)) {
        sort($word);

        $prev_word = '';

        $word_text_sql = '';

        $temp_word = [];

        for ($i = 0, $iMax = count($word); $i < $iMax; $i++) {
            if ($word[$i] != $prev_word) {
                $temp_word[] = $word[$i];

                $word_text_sql .= (('' != $word_text_sql) ? ', ' : '') . "'" . $word[$i] . "'";
            }

            $prev_word = $word[$i];
        }

        $word = $temp_word;

        $check_words = [];

        switch (SQL_LAYER) {
            case 'postgresql':
            case 'msaccess':
            case 'mssql-odbc':
            case 'oracle':
            case 'db2':
                $sql = 'SELECT word_id, word_text
					FROM ' . SEARCH_WORD_TABLE . "
					WHERE word_text IN ($word_text_sql)";
                if (!($result = $db->sql_query($sql))) {
                    message_die(GENERAL_ERROR, 'Could not select words', '', __LINE__, __FILE__, $sql);
                }

                while (false !== ($row = $db->sql_fetchrow($result))) {
                    $check_words[$row['word_text']] = $row['word_id'];
                }
                break;
        }

        $value_sql = '';

        $match_word = [];

        for ($i = 0, $iMax = count($word); $i < $iMax; $i++) {
            $new_match = true;

            if (isset($check_words[$word[$i]])) {
                $new_match = false;
            }

            if ($new_match) {
                switch (SQL_LAYER) {
                    case 'mysql':
                    case 'mysql4':
                        $value_sql .= (('' != $value_sql) ? ', ' : '') . '(\'' . $word[$i] . '\', 0)';
                        break;
                    case 'mssql':
                    case 'mssql-odbc':
                        $value_sql .= (('' != $value_sql) ? ' UNION ALL ' : '') . "SELECT '" . $word[$i] . "', 0";
                        break;
                    default:
                        $sql = 'INSERT INTO ' . SEARCH_WORD_TABLE . " (word_text, word_common)
							VALUES ('" . $word[$i] . "', 0)";
                        if (!$db->sql_query($sql)) {
                            message_die(GENERAL_ERROR, 'Could not insert new word', '', __LINE__, __FILE__, $sql);
                        }
                        break;
                }
            }
        }

        if ('' != $value_sql) {
            switch (SQL_LAYER) {
                case 'mysql':
                case 'mysql4':
                    $sql = 'INSERT IGNORE INTO ' . SEARCH_WORD_TABLE . " (word_text, word_common)
						VALUES $value_sql";
                    break;
                case 'mssql':
                case 'mssql-odbc':
                    $sql = 'INSERT INTO ' . SEARCH_WORD_TABLE . " (word_text, word_common)
						$value_sql";
                    break;
            }

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not insert new word', '', __LINE__, __FILE__, $sql);
            }
        }
    }

    while (list($word_in, $match_sql) = @each($word_insert_sql)) {
        $title_match = ('title' == $word_in) ? 1 : 0;

        if ('' != $match_sql) {
            $sql = 'INSERT INTO ' . SEARCH_MATCH_TABLE . " (post_id, word_id, title_match) 
				SELECT $post_id, word_id, $title_match
					FROM " . SEARCH_WORD_TABLE . "
					WHERE word_text IN ($match_sql)";

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not insert new word matches', '', __LINE__, __FILE__, $sql);
            }
        }
    }

    if ('single' == $mode) {
        remove_common('single', 4 / 10, $word);
    }
}

//
// Check if specified words are too common now
//
function remove_common($mode, $fraction, $word_id_list = [])
{
    global $db;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $sql = 'SELECT COUNT(post_id) AS total_posts
		FROM ' . POSTS_TABLE;

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, 'Could not obtain post count', '', __LINE__, __FILE__, $sql);
    }

    $row = $db->sql_fetchrow($result);

    if ($row['total_posts'] >= 100) {
        $common_threshold = floor($row['total_posts'] * $fraction);

        if ('single' == $mode && count($word_id_list)) {
            $word_id_sql = '';

            for ($i = 0, $iMax = count($word_id_list); $i < $iMax; $i++) {
                $word_id_sql .= (('' != $word_id_sql) ? ', ' : '') . "'" . $word_id_list[$i] . "'";
            }

            $sql = 'SELECT m.word_id
				FROM ' . SEARCH_MATCH_TABLE . ' m, ' . SEARCH_WORD_TABLE . " w
				WHERE w.word_text IN ($word_id_sql)
					AND m.word_id = w.word_id
				GROUP BY m.word_id
				HAVING COUNT(m.word_id) > $common_threshold";
        } else {
            $sql = 'SELECT word_id
				FROM ' . SEARCH_MATCH_TABLE . "
				GROUP BY word_id
				HAVING COUNT(word_id) > $common_threshold";
        }

        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not obtain common word list', '', __LINE__, __FILE__, $sql);
        }

        $common_word_id = '';

        while (false !== ($row = $db->sql_fetchrow($result))) {
            $common_word_id .= (('' != $common_word_id) ? ', ' : '') . $row['word_id'];
        }

        $db->sql_freeresult($result);

        if ('' != $common_word_id) {
            $sql = 'UPDATE ' . SEARCH_WORD_TABLE . '
				SET word_common = ' . true . "
				WHERE word_id IN ($common_word_id)";

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not delete word list entry', '', __LINE__, __FILE__, $sql);
            }

            $sql = 'DELETE FROM ' . SEARCH_MATCH_TABLE . "
				WHERE word_id IN ($common_word_id)";

            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not delete word match entry', '', __LINE__, __FILE__, $sql);
            }
        }
    }
}

function remove_search_post($post_id_sql)
{
    global $db;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $words_removed = false;

    switch (SQL_LAYER) {
        case 'mysql':
        case 'mysql4':
            $sql = 'SELECT word_id
				FROM ' . SEARCH_MATCH_TABLE . "
				WHERE post_id IN ($post_id_sql)
				GROUP BY word_id";
            if ($result = $db->sql_query($sql)) {
                $word_id_sql = '';

                while (false !== ($row = $db->sql_fetchrow($result))) {
                    $word_id_sql .= ('' != $word_id_sql) ? ', ' . $row['word_id'] : $row['word_id'];
                }

                $sql = 'SELECT word_id
					FROM ' . SEARCH_MATCH_TABLE . "
					WHERE word_id IN ($word_id_sql)
					GROUP BY word_id
					HAVING COUNT(word_id) = 1";

                if ($result = $db->sql_query($sql)) {
                    $word_id_sql = '';

                    while (false !== ($row = $db->sql_fetchrow($result))) {
                        $word_id_sql .= ('' != $word_id_sql) ? ', ' . $row['word_id'] : $row['word_id'];
                    }

                    if ('' != $word_id_sql) {
                        $sql = 'DELETE FROM ' . SEARCH_WORD_TABLE . "
							WHERE word_id IN ($word_id_sql)";

                        if (!$db->sql_query($sql)) {
                            message_die(GENERAL_ERROR, 'Could not delete word list entry', '', __LINE__, __FILE__, $sql);
                        }

                        $words_removed = $db->sql_affectedrows();
                    }
                }
            }
            break;
        default:
            $sql = 'DELETE FROM ' . SEARCH_WORD_TABLE . '
				WHERE word_id IN (
					SELECT word_id
					FROM ' . SEARCH_MATCH_TABLE . '
					WHERE word_id IN (
						SELECT word_id
						FROM ' . SEARCH_MATCH_TABLE . "
						WHERE post_id IN ($post_id_sql)
						GROUP BY word_id
					)
					GROUP BY word_id
					HAVING COUNT(word_id) = 1
				)";
            if (!$db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not delete old words from word table', '', __LINE__, __FILE__, $sql);
            }

            $words_removed = $db->sql_affectedrows();

            break;
    }

    $sql = 'DELETE FROM ' . SEARCH_MATCH_TABLE . "
		WHERE post_id IN ($post_id_sql)";

    if (!$db->sql_query($sql)) {
        message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
    }

    return $words_removed;
}

//
// Username search
//
function username_search($search_match)
{
    global $db, $board_config, $template, $lang, $images, $theme, $phpbb_root_path;

    global $starttime, $gen_simple_header;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $gen_simple_header = true;

    $username_list = '';

    if (!empty($search_match)) {
        $username_search = str_replace("*", '%', trim(strip_tags($search_match)));

        $sql = 'SELECT uname
			FROM ' . USERS_TABLE . "
			WHERE uname LIKE '" . str_replace("\'", "''", $username_search) . "' AND uid <> " . ANONYMOUS . '
			ORDER BY uname';

        if (!($result = $db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not obtain search results', '', __LINE__, __FILE__, $sql);
        }

        if ($row = $db->sql_fetchrow($result)) {
            do {
                $username_list .= '<option value="' . $row['uname'] . '">' . $row['uname'] . '</option>';
            } while (false !== ($row = $db->sql_fetchrow($result)));
        } else {
            $username_list .= '<option>' . $lang['No_match'] . '</option>';
        }

        $db->sql_freeresult($result);
    }

    $page_title = $lang['Search'];

    include $phpbb_root_path . 'includes/nowrap_header.php';

    $template->set_filenames(
        [
            'search_user_body' => 'search_username.tpl',
        ]
    );

    $template->assign_vars(
        [
            'USERNAME' => (!empty($search_match)) ? strip_tags($search_match) : '',

            'L_CLOSE_WINDOW' => $lang['Close_window'],
            'L_SEARCH_USERNAME' => $lang['Find_username'],
            'L_UPDATE_USERNAME' => $lang['Select_username'],
            'L_SELECT' => $lang['Select'],
            'L_SEARCH' => $lang['Search'],
            'L_SEARCH_EXPLAIN' => $lang['Search_author_explain'],
            'L_CLOSE_WINDOW' => $lang['Close_window'],

            'S_USERNAME_OPTIONS' => $username_list,
            'S_SEARCH_ACTION' => append_sid('search.php?mode=searchuser'),
        ]
    );

    if ('' != $username_list) {
        $template->assign_block_vars('switch_select_name', []);
    }

    $template->pparse('search_user_body');

    include $phpbb_root_path . 'includes/page_tail.php';
}
