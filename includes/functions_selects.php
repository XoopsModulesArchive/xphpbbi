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
 *                            function_selects.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: functions_selects.php,v 1.8 2004/12/03 23:51:42 blackdeath_csmc Exp $
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
 *
 **************************************************************************
 * @param        $default
 * @param string $select_name
 * @param string $dirname
 * @return string
 */

//
// Pick a language, any language ...
//
function language_select($default, $select_name = 'language', $dirname = 'language')
{
    global $phpbb_root_path;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $dir = opendir($phpbb_root_path . $dirname);

    $lang = [];

    while ($file = readdir($dir)) {
        if (0 === stripos($file, "lang_") && !is_file(@phpbb_realpath($phpbb_root_path . $dirname . '/' . $file)) && !is_link(@phpbb_realpath($phpbb_root_path . $dirname . '/' . $file))) {
            $filename = trim(str_replace('lang_', '', $file));

            $displayname = preg_replace('/^(.*?)_(.*)$/', '\\1 [ \\2 ]', $filename);

            $displayname = preg_replace("/\[(.*?)_(.*)\]/", '[ \\1 - \\2 ]', $displayname);

            $lang[$displayname] = $filename;
        }
    }

    closedir($dir);

    @asort($lang);

    @reset($lang);

    $lang_select = '<select name="' . $select_name . '">';

    while (list($displayname, $filename) = @each($lang)) {
        $selected = (mb_strtolower($default) == mb_strtolower($filename)) ? ' selected="selected"' : '';

        $lang_select .= '<option value="' . $filename . '"' . $selected . '>' . ucwords($displayname) . '</option>';
    }

    $lang_select .= '</select>';

    return $lang_select;
}

//
// Pick a template/theme combo,
//
function style_select($default_style, $select_name = 'style', $dirname = 'templates')
{
    global $db;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $sql = 'SELECT themes_id, style_name
		FROM ' . THEMES_TABLE . '
		ORDER BY template_name, themes_id';

    if (!($result = $db->sql_query($sql))) {
        message_die(GENERAL_ERROR, "Couldn't query themes table", '', __LINE__, __FILE__, $sql);
    }

    $style_select = '<select name="' . $select_name . '">';

    while (false !== ($row = $db->sql_fetchrow($result))) {
        $selected = ($row['themes_id'] == $default_style) ? ' selected="selected"' : '';

        $style_select .= '<option value="' . $row['themes_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
    }

    $style_select .= '</select>';

    return $style_select;
}

//
// Pick a timezone
//
function tz_select($default, $select_name = 'timezone')
{
    global $sys_timezone, $lang;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    if (!isset($default)) {
        $default == $sys_timezone;
    }

    $tz_select = '<select name="' . $select_name . '">';

    while (list($offset, $zone) = @each($lang['tz'])) {
        $selected = ($offset == $default) ? ' selected="selected"' : '';

        $tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
    }

    $tz_select .= '</select>';

    return $tz_select;
}
