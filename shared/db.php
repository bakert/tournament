<?php
/**
 * Database functions for bluebones.net PHP
 *
 * @package db
 */

//TODO phpdoc

define('MAXLEN_TEXT', 65535);

/**
 * Runs a SQL statement against the specified database and returns
 * the result.
 *
 * @param   $sql    SQL to run against database.
 * @param   $host   Host to attach to.
 * @param   $usr    Username for db.
 * @param   $pwd    Password for db.
 * @param   $db     MySQL database to attach to.
 * @param   $tz     Timezone as unquoted string in MySQL-acceptable format
 *                  or null to use server's timezone.
 * @return          true if insert/update succeeded, false on error,
 *                  array of associative arrays (rows) if select succeeded
 *                  (may have 0 entries if no rows to fetch).
 */
function db($sql, $db="bluebones", $host="localhost", $usr="root",
        $pwd="sleague", $tz=null) {
    mysql_connect($host, $usr, $pwd);
    if ($tz) { mysql_query("SET time_zone = '$tz'"); }
    mysql_select_db($db) or die("Unable to select database: " . $db);
    $res = parse_result(mysql_query($sql));
    mysql_close();
    return $res;
}

/**
 * Parses the result of a SQL query and returns an appropriate var.
 *
 * @res             Result to parse.
 * @return          true if insert/update succeeded, false on error,
 *                  array of associative arrays (rows) if select succeeded
 *                  (may have 0 entries if no rows to fetch).
 */
function parse_result($res) {
    if (is_bool($res)) {
        return $res;
    }
    $arr = array();
    $i = 0;
    while ($row = mysql_fetch_array($res)) {
        $arr[$i++] = $row;
    }
    return $arr;
}

/**
 * Quotes a string in single quotes and escapes any existing single quotes.
 *
 * @param   $s  String to escape.
 * @return      Escaped string.
 */
function quote_it($s) {
    if ($s === 'NULL') {
        return 'NULL';
    }
    //TODO or use mysql_real_escape_string
    return "'" . addslashes($s) . "'";
}

/**
 * Formats a PHP date for insertion into a SQL statement.
 *
 * @param   $date   Date to format.
 * @return          Formatted date.
 */
function sql_date($date) {
    return quote_it(date('Y-m-d H:i:s', $date));
}

//TODO docs for these three.
function start_transaction($db="bluebones", $host="localhost", $usr="root",
        $pwd="sleague") {
    $conn = mysql_connect($host, $usr, $pwd);
    @mysql_select_db($db) or die("Unable to select database: " . $db);
    mysql_query("START TRANSACTION");
    return $conn;
}

function transact_db($sql, $conn) {
    if (! $conn) {
        return false;
    }
    return parse_result(mysql_query($sql, $conn));
}

function commit($conn) {
    $res = mysql_query("COMMIT", $conn);
    mysql_close($conn);
    return $res;
}

function rollback($conn) {
    $res = mysql_query("ROLLBACK", $conn);
    mysql_close($conn);
    return $res;
}

?>