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
 *                             usercp_avatar.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: usercp_avatar.php,v 1.8 2004/12/03 23:51:43 blackdeath_csmc Exp $
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
 * @param $type
 * @param $error
 * @param $error_msg
 * @return false|string
 */

function check_image_type($type, &$error, &$error_msg)
{
    global $lang;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    switch ($type) {
        case 'jpeg':
        case 'pjpeg':
        case 'jpg':
            return '.jpg';
            break;
        case 'gif':
            return '.gif';
            break;
        case 'png':
            return '.png';
            break;
        default:
            $error = true;
            $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $lang['Avatar_filetype'] : $lang['Avatar_filetype'];
            break;
    }

    return false;
}

function user_avatar_delete($avatar_type, $avatar_file)
{
    global $board_config, $userdata;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    if (USER_AVATAR_UPLOAD == $avatar_type && '' != $avatar_file) {
        if (@file_exists(@phpbb_realpath('./' . $board_config['avatar_path'] . '/' . $avatar_file))) {
            @unlink('./' . $board_config['avatar_path'] . '/' . $avatar_file);
        }
    }

    return ", user_avatar = '', user_avatar_type = " . USER_AVATAR_NONE;
}

function user_avatar_gallery($mode, &$error, &$error_msg, $avatar_filename)
{
    global $board_config;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    if (file_exists(@phpbb_realpath($board_config['avatar_gallery_path'] . '/' . $avatar_filename)) && ('editprofile' == $mode)) {
        $return = ", user_avatar = '" . str_replace("\'", "''", $avatar_filename) . "', user_avatar_type = " . USER_AVATAR_GALLERY;
    } else {
        $return = '';
    }

    return $return;
}

function user_avatar_url($mode, &$error, &$error_msg, $avatar_filename)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    if (!preg_match('#^(http)|(ftp):\/\/#i', $avatar_filename)) {
        $avatar_filename = 'http://' . $avatar_filename;
    }

    if (!preg_match("#^((ht|f)tp://)([^ \?&=\#\"\n\r\t<]*?(\.(jpg|jpeg|gif|png))$)#is", $avatar_filename)) {
        $error = true;

        $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $lang['Wrong_remote_avatar_format'] : $lang['Wrong_remote_avatar_format'];

        return;
    }

    return ('editprofile' == $mode) ? ", user_avatar = '" . str_replace("\'", "''", $avatar_filename) . "', user_avatar_type = " . USER_AVATAR_REMOTE : '';
}

function user_avatar_upload($mode, $avatar_mode, $current_avatar, $current_type, &$error, &$error_msg, $avatar_filename, $avatar_realname, $avatar_filesize, $avatar_filetype)
{
    global $board_config, $db, $lang, $userdata;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $ini_val = (@phpversion() >= '4.0.0') ? 'ini_get' : 'get_cfg_var';

    if ('remote' == $avatar_mode && preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $avatar_filename, $url_ary)) {
        if (empty($url_ary[4])) {
            $error = true;

            $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $lang['Incomplete_URL'] : $lang['Incomplete_URL'];

            return;
        }

        $base_get = '/' . $url_ary[4];

        $port = (!empty($url_ary[3])) ? $url_ary[3] : 80;

        if (!($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr))) {
            $error = true;

            $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $lang['No_connection_URL'] : $lang['No_connection_URL'];

            return;
        }

        @fwrite($fsock, "GET $base_get HTTP/1.1\r\n");

        @fwrite($fsock, 'HOST: ' . $url_ary[2] . "\r\n");

        @fwrite($fsock, "Connection: close\r\n\r\n");

        unset($avatar_data);

        while (!@feof($fsock)) {
            $avatar_data .= @fread($fsock, $board_config['avatar_filesize']);
        }

        @fclose($fsock);

        if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)) {
            $error = true;

            $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $lang['File_no_data'] : $lang['File_no_data'];

            return;
        }

        $avatar_filesize = $file_data1[1];

        $avatar_filetype = $file_data2[1];

        if (!$error && $avatar_filesize > 0 && $avatar_filesize < $board_config['avatar_filesize']) {
            $avatar_data = mb_substr($avatar_data, mb_strlen($avatar_data) - $avatar_filesize, $avatar_filesize);

            $tmp_path = (!@$ini_val('safe_mode')) ? '/tmp' : './' . $board_config['avatar_path'] . '/tmp';

            $tmp_filename = tempnam($tmp_path, uniqid(mt_rand()) . '-');

            $fptr = @fopen($tmp_filename, 'wb');

            $bytes_written = @fwrite($fptr, $avatar_data, $avatar_filesize);

            @fclose($fptr);

            if ($bytes_written != $avatar_filesize) {
                @unlink($tmp_filename);

                message_die(GENERAL_ERROR, 'Could not write avatar file to local storage. Please contact the board administrator with this message', '', __LINE__, __FILE__);
            }

            [$width, $height] = @getimagesize($tmp_filename);
        } else {
            $l_avatar_size = sprintf($lang['Avatar_filesize'], round($board_config['avatar_filesize'] / 1024));

            $error = true;

            $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $l_avatar_size : $l_avatar_size;
        }
    } elseif ((file_exists(@phpbb_realpath($avatar_filename))) && preg_match('/\.(jpg|jpeg|gif|png)$/i', $avatar_realname)) {
        if ($avatar_filesize <= $board_config['avatar_filesize'] && $avatar_filesize > 0) {
            preg_match('#image\/[x\-]*([a-z]+)#', $avatar_filetype, $avatar_filetype);

            $avatar_filetype = $avatar_filetype[1];
        } else {
            $l_avatar_size = sprintf($lang['Avatar_filesize'], round($board_config['avatar_filesize'] / 1024));

            $error = true;

            $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $l_avatar_size : $l_avatar_size;

            return;
        }

        [$width, $height] = @getimagesize($avatar_filename);
    }

    if (!($imgtype = check_image_type($avatar_filetype, $error, $error_msg))) {
        return;
    }

    if ($width <= $board_config['avatar_max_width'] && $height <= $board_config['avatar_max_height']) {
        $new_filename = 'cavt-' . $userdata['uid'] . '-' . $userdata['uname'] . $imgtype;

        if ('editprofile' == $mode && USER_AVATAR_UPLOAD == $current_type && '' != $current_avatar) {
            if (file_exists(@phpbb_realpath('./' . $board_config['avatar_path'] . '/' . $current_avatar))) {
                @unlink('./' . $board_config['avatar_path'] . '/' . $current_avatar);
            }
        }

        if ('remote' == $avatar_mode) {
            @copy($tmp_filename, './' . $board_config['avatar_path'] . "/$new_filename");

            @unlink($tmp_filename);
        } else {
            if ('' != @$ini_val('open_basedir')) {
                if (@phpversion() < '4.0.3') {
                    message_die(GENERAL_ERROR, 'open_basedir is set and your PHP version does not allow move_uploaded_file', '', __LINE__, __FILE__);
                }

                $move_file = 'move_uploaded_file';
            } else {
                $move_file = 'copy';
            }

            $move_file($avatar_filename, './' . $board_config['avatar_path'] . "/$new_filename");
        }

        @chmod('./' . $board_config['avatar_path'] . "/$new_filename", 0777);

        $avatar_sql = ('editprofile' == $mode) ? ", user_avatar = '$new_filename', user_avatar_type = " . USER_AVATAR_UPLOAD : "'$new_filename', " . USER_AVATAR_UPLOAD;
    } else {
        $l_avatar_size = sprintf($lang['Avatar_imagesize'], $board_config['avatar_max_width'], $board_config['avatar_max_height']);

        $error = true;

        $error_msg = (!empty($error_msg)) ? $error_msg . '<br>' . $l_avatar_size : $l_avatar_size;
    }

    return $avatar_sql;
}

function display_avatar_gallery(
    $mode,
    &$category,
    &$user_id,
    &$email,
    &$current_email,
    &$coppa,
    &$username,
    &$email,
    &$new_password,
    &$cur_password,
    &$password_confirm,
    &$icq,
    &$aim,
    &$msn,
    &$yim,
    &$website,
    &$location,
    &$occupation,
    &$interests,
    &$signature,
    &$viewemail,
    &$notifypm,
    &$popup_pm,
    &$notifyreply,
    &$attachsig,
    &$allowhtml,
    &$allowbbcode,
    &$allowsmilies,
    &$hideonline,
    &$style,
    &$language,
    &$timezone,
    &$dateformat,
    $session_id
) {
    global $board_config, $db, $template, $lang, $images, $theme;

    global $phpbb_root_path;

    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $dir = @opendir($board_config['avatar_gallery_path']);

    $avatar_images = [];

    while ($file = @readdir($dir)) {
        if ('.' != $file && '..' != $file && !is_file($board_config['avatar_gallery_path'] . '/' . $file) && !is_link($board_config['avatar_gallery_path'] . '/' . $file)) {
            $sub_dir = @opendir($board_config['avatar_gallery_path'] . '/' . $file);

            $avatar_row_count = 0;

            $avatar_col_count = 0;

            while ($sub_file = @readdir($sub_dir)) {
                if (preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $sub_file)) {
                    $avatar_images[$file][$avatar_row_count][$avatar_col_count] = $file . '/' . $sub_file;

                    $avatar_name[$file][$avatar_row_count][$avatar_col_count] = ucfirst(str_replace('_', ' ', preg_replace('/^(.*)\..*$/', '\1', $sub_file)));

                    $avatar_col_count++;

                    if (5 == $avatar_col_count) {
                        $avatar_row_count++;

                        $avatar_col_count = 0;
                    }
                }
            }
        }
    }

    @closedir($dir);

    @ksort($avatar_images);

    @reset($avatar_images);

    if (empty($category)) {
        [$category,] = each($avatar_images);
    }

    @reset($avatar_images);

    $s_categories = '<select name="avatarcategory">';

    while (list($key) = each($avatar_images)) {
        $selected = ($key == $category) ? ' selected="selected"' : '';

        if (count($avatar_images[$key])) {
            $s_categories .= '<option value="' . $key . '"' . $selected . '>' . ucfirst($key) . '</option>';
        }
    }

    $s_categories .= '</select>';

    $s_colspan = 0;

    for ($i = 0, $iMax = count($avatar_images[$category]); $i < $iMax; $i++) {
        $template->assign_block_vars('avatar_row', []);

        $s_colspan = max($s_colspan, count($avatar_images[$category][$i]));

        for ($j = 0, $jMax = count($avatar_images[$category][$i]); $j < $jMax; $j++) {
            $template->assign_block_vars(
                'avatar_row.avatar_column',
                [
                    'AVATAR_IMAGE' => $board_config['avatar_gallery_path'] . '/' . $avatar_images[$category][$i][$j],
                    'AVATAR_NAME' => $avatar_name[$category][$i][$j],
                ]
            );

            $template->assign_block_vars(
                'avatar_row.avatar_option_column',
                [
                    'S_OPTIONS_AVATAR' => $avatar_images[$category][$i][$j],
                ]
            );
        }
    }

    $params = [
        'coppa',
        'user_id',
        'username',
        'email',
        'current_email',
        'cur_password',
        'new_password',
        'password_confirm',
        'icq',
        'aim',
        'msn',
        'yim',
        'website',
        'location',
        'occupation',
        'interests',
        'signature',
        'viewemail',
        'notifypm',
        'popup_pm',
        'notifyreply',
        'attachsig',
        'allowhtml',
        'allowbbcode',
        'allowsmilies',
        'hideonline',
        'style',
        'language',
        'timezone',
        'dateformat',
    ];

    $s_hidden_vars = '<input type="hidden" name="sid" value="' . $session_id . '"><input type="hidden" name="agreed" value="true">';

    for ($i = 0, $iMax = count($params); $i < $iMax; $i++) {
        $s_hidden_vars .= '<input type="hidden" name="' . $params[$i] . '" value="' . str_replace('"', '&quot;', $$params[$i]) . '">';
    }

    $template->assign_vars(
        [
            'L_AVATAR_GALLERY' => $lang['Avatar_gallery'],
            'L_SELECT_AVATAR' => $lang['Select_avatar'],
            'L_RETURN_PROFILE' => $lang['Return_profile'],
            'L_CATEGORY' => $lang['Select_category'],

            'S_CATEGORY_SELECT' => $s_categories,
            'S_COLSPAN' => $s_colspan,
            'S_PROFILE_ACTION' => append_sid("profile.php?mode=$mode"),
            'S_HIDDEN_FIELDS' => $s_hidden_vars,
        ]
    );
}
