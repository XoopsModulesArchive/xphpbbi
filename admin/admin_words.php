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
 *                              admin_words.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: admin_words.php,v 1.7 2004/11/30 21:54:46 blackdeath_csmc Exp $
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
    $file = basename(__FILE__);

    $module['General']['Word_Censor'] = (string)$file;

    return;
}

//
// Load default header
//
$phpbb_root_path = './../';
require __DIR__ . '/pagestart.php';

if (isset($_GET['mode']) || isset($_POST['mode'])) {
    $mode = ($_GET['mode']) ?: $_POST['mode'];

    $mode = htmlspecialchars($mode, ENT_QUOTES | ENT_HTML5);
} else {
    //

    // These could be entered via a form button

    //

    if (isset($_POST['add'])) {
        $mode = 'add';
    } elseif (isset($_POST['save'])) {
        $mode = 'save';
    } else {
        $mode = '';
    }
}

if ('' != $mode) {
    if ('edit' == $mode || 'add' == $mode) {
        $word_id = $_GET['id'] ?? 0;

        $word_id = (isset($_GET['id'])) ? (int)$_GET['id'] : 0;

        $template->set_filenames(
            [
                'body' => 'admin/words_edit_body.tpl',
            ]
        );

        $s_hidden_fields = '';

        if ('edit' == $mode) {
            if ($word_id) {
                $sql = 'SELECT * 
					FROM ' . WORDS_TABLE . " 
					WHERE word_id = $word_id";

                if (!$result = $db->sql_query($sql)) {
                    message_die(GENERAL_ERROR, 'Could not query words table', 'Error', __LINE__, __FILE__, $sql);
                }

                $word_info = $db->sql_fetchrow($result);

                $s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '">';
            } else {
                message_die(GENERAL_MESSAGE, $lang['No_word_selected']);
            }
        }

        $template->assign_vars(
            [
                'WORD' => $word_info['word'],
                'REPLACEMENT' => $word_info['replacement'],

                'L_WORDS_TITLE' => $lang['Words_title'],
                'L_WORDS_TEXT' => $lang['Words_explain'],
                'L_WORD_CENSOR' => $lang['Edit_word_censor'],
                'L_WORD' => $lang['Word'],
                'L_REPLACEMENT' => $lang['Replacement'],
                'L_SUBMIT' => $lang['Submit'],

                'S_WORDS_ACTION' => append_sid('admin_words.php'),
                'S_HIDDEN_FIELDS' => $s_hidden_fields,
            ]
        );

        $template->pparse('body');

        require __DIR__ . '/page_footer_admin.php';
    } elseif ('save' == $mode) {
        $word_id = (isset($_POST['id'])) ? (int)$_POST['id'] : 0;

        $word = (isset($_POST['word'])) ? trim($_POST['word']) : '';

        $replacement = (isset($_POST['replacement'])) ? trim($_POST['replacement']) : '';

        if ('' == $word || '' == $replacement) {
            message_die(GENERAL_MESSAGE, $lang['Must_enter_word']);
        }

        if ($word_id) {
            $sql = 'UPDATE ' . WORDS_TABLE . " 
				SET word = '" . str_replace("\'", "''", $word) . "', replacement = '" . str_replace("\'", "''", $replacement) . "' 
				WHERE word_id = $word_id";

            $message = $lang['Word_updated'];
        } else {
            $sql = 'INSERT INTO ' . WORDS_TABLE . " (word, replacement) 
				VALUES ('" . str_replace("\'", "''", $word) . "', '" . str_replace("\'", "''", $replacement) . "')";

            $message = $lang['Word_added'];
        }

        if (!$result = $db->sql_query($sql)) {
            message_die(GENERAL_ERROR, 'Could not insert data into words table', $lang['Error'], __LINE__, __FILE__, $sql);
        }

        $message .= '<br><br>' . sprintf($lang['Click_return_wordadmin'], '<a href="' . append_sid('admin_words.php') . '">', '</a>') . '<br><br>' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid('index.php?pane=right') . '">', '</a>');

        message_die(GENERAL_MESSAGE, $message);
    } elseif ('delete' == $mode) {
        if (isset($_POST['id']) || isset($_GET['id'])) {
            $word_id = $_POST['id'] ?? $_GET['id'];

            $word_id = (int)$word_id;
        } else {
            $word_id = 0;
        }

        if ($word_id) {
            $sql = 'DELETE FROM ' . WORDS_TABLE . " 
				WHERE word_id = $word_id";

            if (!$result = $db->sql_query($sql)) {
                message_die(GENERAL_ERROR, 'Could not remove data from words table', $lang['Error'], __LINE__, __FILE__, $sql);
            }

            $message = $lang['Word_removed'] . '<br><br>' . sprintf($lang['Click_return_wordadmin'], '<a href="' . append_sid('admin_words.php') . '">', '</a>') . '<br><br>' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid('index.php?pane=right') . '">', '</a>');

            message_die(GENERAL_MESSAGE, $message);
        } else {
            message_die(GENERAL_MESSAGE, $lang['No_word_selected']);
        }
    }
} else {
    $template->set_filenames(
        [
            'body' => 'admin/words_list_body.tpl',
        ]
    );

    $sql = 'SELECT * 
		FROM ' . WORDS_TABLE . ' 
		ORDER BY word';

    if (!$result = $db->sql_query($sql)) {
        message_die(GENERAL_ERROR, 'Could not query words table', $lang['Error'], __LINE__, __FILE__, $sql);
    }

    $word_rows = $db->sql_fetchrowset($result);

    $word_count = count($word_rows);

    $template->assign_vars(
        [
            'L_WORDS_TITLE' => $lang['Words_title'],
            'L_WORDS_TEXT' => $lang['Words_explain'],
            'L_WORD' => $lang['Word'],
            'L_REPLACEMENT' => $lang['Replacement'],
            'L_EDIT' => $lang['Edit'],
            'L_DELETE' => $lang['Delete'],
            'L_ADD_WORD' => $lang['Add_new_word'],
            'L_ACTION' => $lang['Action'],

            'S_WORDS_ACTION' => append_sid('admin_words.php'),
            'S_HIDDEN_FIELDS' => '',
        ]
    );

    for ($i = 0; $i < $word_count; $i++) {
        $word = $word_rows[$i]['word'];

        $replacement = $word_rows[$i]['replacement'];

        $word_id = $word_rows[$i]['word_id'];

        $row_color = (!($i % 2)) ? $theme['td_color1'] : $theme['td_color2'];

        $row_class = (!($i % 2)) ? $theme['td_class1'] : $theme['td_class2'];

        $template->assign_block_vars(
            'words',
            [
                'ROW_COLOR' => '#' . $row_color,
                'ROW_CLASS' => $row_class,
                'WORD' => $word,
                'REPLACEMENT' => $replacement,

                'U_WORD_EDIT' => append_sid("admin_words.php?mode=edit&amp;id=$word_id"),
                'U_WORD_DELETE' => append_sid("admin_words.php?mode=delete&amp;id=$word_id"),
            ]
        );
    }
}

$template->pparse('body');

require __DIR__ . '/page_footer_admin.php';
