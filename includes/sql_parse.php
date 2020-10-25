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
 *                             sql_parse.php
 *                              -------------------
 *     begin                : Thu May 31, 2001
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: sql_parse.php,v 1.8 2004/12/03 23:51:43 blackdeath_csmc Exp $
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

/***************************************************************************
 *
 *    These functions are mainly for use in the db_utilities under the admin
 *    however in order to make these functions available elsewhere, specifically
 *    in the installation phase of phpBB I have seperated out a couple of
 *    functions into this file.  JLH
 *
 * \**************************************************************************
 * @param $output
 * @return string
 */

//
// remove_comments will strip the sql comment lines out of an uploaded sql file
// specifically for mssql and postgres type files in the install....
//
function remove_comments(&$output)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $lines = explode("\n", $output);

    $output = '';

    // try to keep mem. use down

    $linecount = count($lines);

    $in_comment = false;

    for ($i = 0; $i < $linecount; $i++) {
        if (preg_match("/^\/\*/", preg_quote($lines[$i]))) {
            $in_comment = true;
        }

        if (!$in_comment) {
            $output .= $lines[$i] . "\n";
        }

        if (preg_match("/\*\/$/", preg_quote($lines[$i]))) {
            $in_comment = false;
        }
    }

    unset($lines);

    return $output;
}

//
// remove_remarks will strip the sql comment lines out of an uploaded sql file
//
function remove_remarks($sql)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    $lines = explode("\n", $sql);

    // try to keep mem. use down

    $sql = '';

    $linecount = count($lines);

    $output = '';

    for ($i = 0; $i < $linecount; $i++) {
        if (($i != ($linecount - 1)) || (mb_strlen($lines[$i]) > 0)) {
            if ('#' != $lines[$i][0]) {
                $output .= $lines[$i] . "\n";
            } else {
                $output .= "\n";
            }

            // Trading a bit of speed for lower mem. use here.

            $lines[$i] = '';
        }
    }

    return $output;
}

//
// split_sql_file will split an uploaded sql file into single sql statements.
// Note: expects trim() to have already been run on $sql.
//
function split_sql_file($sql, $delimiter)
{
    global $xoopsDB, $xoopsUser, $xoopsTpl;

    // Split up our string into "possible" SQL statements.

    $tokens = explode($delimiter, $sql);

    // try to save mem.

    $sql = '';

    $output = [];

    // we don't actually care about the matches preg gives us.

    $matches = [];

    // this is faster than calling count($oktens) every time thru the loop.

    $token_count = count($tokens);

    for ($i = 0; $i < $token_count; $i++) {
        // Don't wanna add an empty string as the last thing in the array.

        if (($i != ($token_count - 1)) || (mb_strlen($tokens[$i] > 0))) {
            // This is the total number of single quotes in the token.

            $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);

            // Counts single quotes that are preceded by an odd number of backslashes,

            // which means they're escaped quotes.

            $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

            $unescaped_quotes = $total_quotes - $escaped_quotes;

            // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.

            if (0 == ($unescaped_quotes % 2)) {
                // It's a complete sql statement.

                $output[] = $tokens[$i];

                // save memory.

                $tokens[$i] = '';
            } else {
                // incomplete sql statement. keep adding tokens until we have a complete one.

                // $temp will hold what we have so far.

                $temp = $tokens[$i] . $delimiter;

                // save memory..

                $tokens[$i] = '';

                // Do we have a complete statement yet?

                $complete_stmt = false;

                for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
                    // This is the total number of single quotes in the token.

                    $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);

                    // Counts single quotes that are preceded by an odd number of backslashes,

                    // which means they're escaped quotes.

                    $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

                    $unescaped_quotes = $total_quotes - $escaped_quotes;

                    if (1 == ($unescaped_quotes % 2)) {
                        // odd number of unescaped quotes. In combination with the previous incomplete

                        // statement(s), we now have a complete statement. (2 odds always make an even)

                        $output[] = $temp . $tokens[$j];

                        // save memory.

                        $tokens[$j] = '';

                        $temp = '';

                        // exit the loop.

                        $complete_stmt = true;

                        // make sure the outer loop continues at the right point.

                        $i = $j;
                    } else {
                        // even number of unescaped quotes. We still don't have a complete statement.

                        // (1 odd and 1 even always make an odd)

                        $temp .= $tokens[$j] . $delimiter;

                        // save memory.

                        $tokens[$j] = '';
                    }
                } // for..
            } // else
        }
    }

    return $output;
}
