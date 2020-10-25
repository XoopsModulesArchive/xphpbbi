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
 *                            admin_disallow.php
 *                            -------------------
 *   begin                : Tuesday, Oct 05, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: admin_disallow.php,v 1.7 2004/11/30 21:54:46 blackdeath_csmc Exp $
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

    $module['Users']['Disallow'] = $filename; // by kou

    return;
}

//
// Include required files, get $phpEx and check permissions
//
$phpbb_root_path = './../';
require __DIR__ . '/pagestart.php';

if (isset($_POST['add_name'])) {
    include $phpbb_root_path . 'includes/functions_validate.php';

    $disallowed_user = (isset($_POST['disallowed_user'])) ? trim($_POST['disallowed_user']) : trim($_GET['disallowed_user']);

    if ('' == $disallowed_user) {
        message_die(MESSAGE, $lang['Fields_empty']);
    }

    if (!validate_username($disallowed_user)) {
        $message = $lang['Disallowed_already'];
    } else {
        $sql = 'INSERT INTO ' . DISALLOW_TABLE . " (disallow_username) 
			VALUES('" . str_replace("\'", "''", $disallowed_user) . "')";

        $result = $db->sql_query($sql);

        if (!$result) {
            message_die(GENERAL_ERROR, 'Could not add disallowed user.', '', __LINE__, __FILE__, $sql);
        }

        $message = $lang['Disallow_successful'];
    }

    $message .= '<br><br>' . sprintf($lang['Click_return_disallowadmin'], '<a href="' . append_sid('admin_disallow.php') . '">', '</a>') . '<br><br>' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid('index.php?pane=right') . '">', '</a>');

    message_die(GENERAL_MESSAGE, $message);
} elseif (isset($_POST['delete_name'])) {
    $disallowed_id = (isset($_POST['disallowed_id'])) ? (int)$_POST['disallowed_id'] : (int)$_GET['disallowed_id'];

    $sql = 'DELETE FROM ' . DISALLOW_TABLE . " 
		WHERE disallow_id = $disallowed_id";

    $result = $db->sql_query($sql);

    if (!$result) {
        message_die(GENERAL_ERROR, "Couldn't removed disallowed user.", '', __LINE__, __FILE__, $sql);
    }

    $message .= $lang['Disallowed_deleted'] . '<br><br>' . sprintf($lang['Click_return_disallowadmin'], '<a href="' . append_sid('admin_disallow.php') . '">', '</a>') . '<br><br>' . sprintf(
        $lang['Click_return_admin_index'],
        '<a href="' . append_sid('index.php?pane=right') . '">',
        '</a>'
    );

    message_die(GENERAL_MESSAGE, $message);
}

//
// Grab the current list of disallowed usernames...
//
$sql = 'SELECT * 
	FROM ' . DISALLOW_TABLE;
$result = $db->sql_query($sql);
if (!$result) {
    message_die(GENERAL_ERROR, "Couldn't get disallowed users.", '', __LINE__, __FILE__, $sql);
}

$disallowed = $db->sql_fetchrowset($result);

//
// Ok now generate the info for the template, which will be put out no matter
// what mode we are in.
//
$disallow_select = '<select name="disallowed_id">';

if ('' == trim($disallowed)) {
    $disallow_select .= '<option value="">' . $lang['no_disallowed'] . '</option>';
} else {
    $user = [];

    for ($i = 0, $iMax = count($disallowed); $i < $iMax; $i++) {
        $disallow_select .= '<option value="' . $disallowed[$i]['disallow_id'] . '">' . $disallowed[$i]['disallow_username'] . '</option>';
    }
}

$disallow_select .= '</select>';

$template->set_filenames(
    [
        'body' => 'admin/disallow_body.tpl',
    ]
);

$template->assign_vars(
    [
        'S_DISALLOW_SELECT' => $disallow_select,
        'S_FORM_ACTION' => append_sid('admin_disallow.php'),

        'L_INFO' => $output_info,
        'L_DISALLOW_TITLE' => $lang['Disallow_control'],
        'L_DISALLOW_EXPLAIN' => $lang['Disallow_explain'],
        'L_DELETE' => $lang['Delete_disallow'],
        'L_DELETE_DISALLOW' => $lang['Delete_disallow_title'],
        'L_DELETE_EXPLAIN' => $lang['Delete_disallow_explain'],
        'L_ADD' => $lang['Add_disallow'],
        'L_ADD_DISALLOW' => $lang['Add_disallow_title'],
        'L_ADD_EXPLAIN' => $lang['Add_disallow_explain'],
        'L_USERNAME' => $lang['Username'],
    ]
);

$template->pparse('body');

require __DIR__ . '/page_footer_admin.php';
