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
 *                               pagestart.php
 *                            -------------------
 *   begin                : Thursday, Aug 2, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: pagestart.php,v 1.9 2004/12/03 23:51:42 blackdeath_csmc Exp $
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

define('IN_ADMIN', true);
// Include files
require_once $phpbb_root_path . 'common.php';

if (!is_object($xoopsUser)) {
    message_die(GENERAL_MESSAGE, 'Not Logged In - Access Denied');

//redirect(append_sid("login.php", true));
} elseif (!$xoopsUserIsAdmin) {
    message_die(GENERAL_MESSAGE, $lang['Not_admin']);
}

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX);
init_userprefs($userdata);
//
// End session management
//

if (empty($no_page_header)) {
    // Not including the pageheader can be neccesarry if META tags are

    // needed in the calling script.

    require __DIR__ . '/page_header_admin.php';
}
