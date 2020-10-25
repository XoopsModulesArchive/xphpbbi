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
 *                                common.php
 *                            -------------------
 *   begin                : Saturday, Feb 23, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: common.php,v 1.9 2004/12/04 07:11:06 blackdeath_csmc Exp $
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

function unset_vars(&$var)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    while (list($var_name, $null) = @each($var)) {
        unset($GLOBALS[$var_name]);
    }
}

set_magic_quotes_runtime(0); // Disable magic_quotes_runtime

$ini_val = (@phpversion() >= '4.0.0') ? 'ini_get' : 'get_cfg_var';

// Unset globally registered vars - PHP5 ... hhmmm
if ('1' == @$ini_val('register_globals') || 'on' == mb_strtolower(@$ini_val('register_globals'))) {
    $var_prefix = 'HTTP';

    $var_suffix = '_VARS';

    $test = ['_GET', '_POST', '_SERVER', '_COOKIE', '_ENV'];

    foreach ($test as $var) {
        if (is_array(${$var_prefix . $var . $var_suffix})) {
            unset_vars(${$var_prefix . $var . $var_suffix});
        }

        if (is_array(${$var})) {
            unset_vars(${$var});
        }
    }

    if (is_array(${'_FILES'})) {
        unset_vars(${'_FILES'});
    }

    if (is_array(${'HTTP_POST_FILES'})) {
        unset_vars(${'HTTP_POST_FILES'});
    }
}

//
// addslashes to vars if magic_quotes_gpc is off
// this is a security precaution to prevent someone
// trying to break out of a SQL statement.
//
if (!get_magic_quotes_gpc()) {
    if (is_array($_GET)) {
        while (list($k, $v) = each($_GET)) {
            if (is_array($_GET[$k])) {
                while (list($k2, $v2) = each($_GET[$k])) {
                    $_GET[$k][$k2] = addslashes($v2);
                }

                @reset($_GET[$k]);
            } else {
                $_GET[$k] = addslashes($v);
            }
        }

        @reset($_GET);
    }

    if (is_array($_POST)) {
        while (list($k, $v) = each($_POST)) {
            if (is_array($_POST[$k])) {
                while (list($k2, $v2) = each($_POST[$k])) {
                    $_POST[$k][$k2] = addslashes($v2);
                }

                @reset($_POST[$k]);
            } else {
                $_POST[$k] = addslashes($v);
            }
        }

        @reset($_POST);
    }

    if (is_array($HTTP_COOKIE_VARS)) {
        while (list($k, $v) = each($HTTP_COOKIE_VARS)) {
            if (is_array($HTTP_COOKIE_VARS[$k])) {
                while (list($k2, $v2) = each($HTTP_COOKIE_VARS[$k])) {
                    $HTTP_COOKIE_VARS[$k][$k2] = addslashes($v2);
                }

                @reset($HTTP_COOKIE_VARS[$k]);
            } else {
                $HTTP_COOKIE_VARS[$k] = addslashes($v);
            }
        }

        @reset($HTTP_COOKIE_VARS);
    }
}

//
// Define some basic configuration arrays this also prevents
// malicious rewriting of language and otherarray values via
// URI params
//
$board_config = [];
$userdata = [];
$theme = [];
$images = [];
$lang = [];
$nav_links = [];
$gen_simple_header = false;

//+----------------------------------------------
//| XOOPS mainfile include -- Koudanshi
//+----------------------------------------------
if (file_exists(__DIR__ . '/../../mainfile.php')) {
    require dirname(__DIR__, 2) . '/mainfile.php';
} else {
    require dirname(__DIR__, 3) . '/mainfile.php';
}
$phpbb_root_path = XOOPS_ROOT_PATH . '/modules/xphpbbi/';

$dbms = 'mysql4';
$dbhost = XOOPS_DB_HOST;
$dbname = XOOPS_DB_NAME;
$dbuser = XOOPS_DB_USER;
$dbpasswd = XOOPS_DB_PASS;

include $phpbb_root_path . 'includes/constants.php';
include $phpbb_root_path . 'includes/template.php'; // Define config informations
include $phpbb_root_path . 'includes/sessions.php';
include $phpbb_root_path . 'includes/auth.php';
include $phpbb_root_path . 'includes/functions.php';
include $phpbb_root_path . 'includes/db.php';

//
// Obtain and encode users IP
//
// I'm removing HTTP_X_FORWARDED_FOR ... this may well cause other problems such as
// private range IP's appearing instead of the guilty routable IP, tough, don't
// even bother complaining ... go scream and shout at the idiots out there who feel
// "clever" is doing harm rather than good ... karma is a great thing ... :)
//
$client_ip = (!empty($HTTP_SERVER_VARS['REMOTE_ADDR'])) ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : ((!empty($HTTP_ENV_VARS['REMOTE_ADDR'])) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : $REMOTE_ADDR);
$user_ip = encode_ip($client_ip);

//
// Setup forum wide options, if this fails
// then we output a CRITICAL_ERROR since
// basic forum information is not available
//
$sql = 'SELECT *
	FROM ' . CONFIG_TABLE;
if (!($result = $db->sql_query($sql))) {
    message_die(CRITICAL_ERROR, 'Could not query config information', '', __LINE__, __FILE__, $sql);
}

while (false !== ($row = $db->sql_fetchrow($result))) {
    $board_config[$row['config_name']] = $row['config_value'];
}

if (file_exists('contrib')) {
    message_die(GENERAL_MESSAGE, 'Please ensure both the contrib/ directories are deleted');
}

if (file_exists('install') || file_exists('contrib')) {
    message_die(GENERAL_MESSAGE, 'Please ensure both the install/ and contrib/ directories are deleted');
}
//
// Show 'Board is disabled' message if needed.
//
if ($board_config['board_disable'] && !defined('IN_ADMIN') && !defined('IN_LOGIN')) {
    message_die(GENERAL_MESSAGE, 'Board_disable', 'Information');
}
