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

/************************************************************************/
/*                                                                      */
/* This program is free software; you can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/*                                                                      */
/* This program is distributed in the hope that it will be useful, but  */
/* WITHOUT ANY WARRANTY; without even the implied warranty of           */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU     */
/* General Public License for more details.                             */
/*                                                                      */
/* You should have received a copy of the GNU General Public License    */
/* along with this program; if not, write to the Free Software          */
/* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307  */
/* USA                                                                  */
/************************************************************************/
define('IN_PHPBB', true);
require XOOPS_ROOT_PATH . '/modules/xphpbbi/includes/constants.php';

function xoops_module_install_xphpbbi(&$module)
{
    global $xoopsDB, $xoopsUser;

    if (!($xoopsDB->queryF('UPDATE ' . CONFIG_TABLE . " SET config_value = '" . $_SERVER['HTTP_HOST'] . "' WHERE config_name = 'server_name'"))) {
        echo '1';

        exit;

        return false;
    }

    $script_path = 'modules/xphpbbi/';

    if (!($xoopsDB->queryF('UPDATE ' . CONFIG_TABLE . " SET config_value = '" . $script_path . "' WHERE config_name = 'script_path'"))) {
        echo '2';

        exit;

        return false;
    }

    if (!($xoopsDB->queryF('UPDATE ' . CONFIG_TABLE . " SET config_value = '" . time() . "' WHERE config_name = 'board_startdate'"))) {
        echo '3';

        exit;

        return false;
    }

    $sql = $xoopsDB->query('SELECT uid FROM ' . USER_GROUP_TABLE . " WHERE groupid = '1' ");

    while (false !== ($row = $xoopsDB->fetchArray($sql))) {
        $nsql = 'INSERT INTO ' . USERS_TABLE_EXT . " (uid,user_level) VALUES ('" . $row['uid'] . "','1') ";

        if (!$xoopsDB->queryF($nsql)) {
            echo $nsql;

            exit;
        }
    }

    if (!($xoopsDB->queryF('INSERT INTO ' . USERS_TABLE . " VALUES (0, '', 'Anonymous', '', '', 'blank.gif', 0, '', '', '', 0, '', '', '', '', '', 0, 0, 0, 1, '', '0.0', 0, '', 0, 2, 0, '', '', '', 1)"))) {
        echo '4';

        exit;

        return false;
    }

    $sql = $xoopsDB->query('SELECT uid FROM ' . USERS_TABLE . " WHERE uname = 'Anonymous' ");

    $row = $xoopsDB->fetchArray($sql);

    if (ANONYMOUS != $row['uid']) {
        if (!($xoopsDB->queryF('UPDATE ' . USERS_TABLE . " SET uid='" . ANONYMOUS . "' WHERE uname = 'Anonymous'"))) {
            echo '5';

            exit;

            return false;
        }
    }

    return true;
}
