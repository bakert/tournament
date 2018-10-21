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
    $conn = mysqli_connect($host, $usr, $pwd);
    if ($tz) { mysqli_query($conn, "SET time_zone = '$tz'"); }
    mysqli_select_db($conn, $db) or die("Unable to select database: " . $db);
    $res = parse_result(mysqli_query($conn, $sql));
    mysqli_close($conn);
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
    while ($row = mysqli_fetch_array($res)) {
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

/**
 * Starts a transaction.
 *
 * @param   $db     MySQL database to attach to.
 * @param   $host   Host to attach to.
 * @param   $usr    Username for db.
 * @param   $pwd    Password for db.
 * @return          Connection object suitable for passing in to transact_db,
 *                  commit and rollback.
 */
function start_transaction($db="bluebones", $host="localhost", $usr="root",
        $pwd="sleague") {
    $conn = mysqli_connect($host, $usr, $pwd);
    mysqli_select_db($conn, $db) or die("Unable to select database: " . $db);
    mysqli_query($conn, "START TRANSACTION");
    return $conn;
}

/**
 * Performs a SQL query in an open transaction.
 *
 * @param $sql  Query to perform.
 * @param $conn Transaction connection (from start_transaction).
 * @return      Results of SQL query (false for failed, number of affected rows
 *              for a write, array of results for a read).
 */
function transact_db($sql, $conn) {
    if (! $conn) {
        return false;
    }
    return parse_result(mysqli_query($conn, $sql));
}

/**
 * Commit the transaction represented by $conn.
 *
 * @param $conn Transaction connection (from start_transaction).
 * @return      Result of COMMIT.
 */
function commit($conn) {
    $res = mysqli_query($conn, "COMMIT");
    mysqli_close($conn);
    return $res;
}

/**
 * Rollback the transaction represented by $conn.
 *
 * @param $conn Transaction connection (from start_transaction).
 * @return      Result of ROLLBACK.
 */
function rollback($conn) {
    $res = mysqli_query($conn, "ROLLBACK");
    mysqli_close($conn);
    return $res;
}
