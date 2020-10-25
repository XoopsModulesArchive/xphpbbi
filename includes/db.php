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
 *                                 db.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: db.php,v 1.7 2004/11/30 21:54:47 blackdeath_csmc Exp $
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

switch ($dbms) {
    case 'mysql':
        include $phpbb_root_path . 'db/mysql.php';
        break;
    case 'mysql4':
        include $phpbb_root_path . 'db/mysql4.php';
        break;
    case 'postgres':
        include $phpbb_root_path . 'db/postgres7.php';
        break;
    case 'mssql':
        include $phpbb_root_path . 'db/mssql.php';
        break;
    case 'oracle':
        include $phpbb_root_path . 'db/oracle.php';
        break;
    case 'msaccess':
        include $phpbb_root_path . 'db/msaccess.php';
        break;
    case 'mssql-odbc':
        include $phpbb_root_path . 'db/mssql-odbc.php';
        break;
}

// Make the database connection.
$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, false);
if (!$db->db_connect_id) {
    message_die(CRITICAL_ERROR, 'Could not connect to the database');
}
