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
 *                              page_tail.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: page_tail.php,v 1.8 2004/12/03 23:51:42 blackdeath_csmc Exp $
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

//
// Show the overall footer.
//
$admin_link = (is_object($xoopsUser) && $xoopsUserIsAdmin) ? '<a href="admin/index.php?sid=' . $userdata['sess_id'] . '">' . $lang['Admin_panel'] . '</a>' : '';

$template->set_filenames(
    [
        'overall_footer' => (empty($gen_simple_header)) ? 'overall_footer.tpl' : 'simple_footer.tpl',
    ]
);

$template->assign_vars(
    [
        'PHPBB_VERSION' => '2' . $board_config['version'],
        'TRANSLATION_INFO' => $lang['TRANSLATION_INFO'] ?? '',
        'ADMIN_LINK' => $admin_link,
    ]
);

$template->pparse('overall_footer');
// Close our DB connection.
//
$db->sql_close();

//XOOPS
require XOOPS_ROOT_PATH . '/footer.php';

exit;
