<?php

class DatabaseHelper
{
    /**
     * Execute a SQL query and return the result.
     *
     * @param string $sql The SQL query.
     * @return mixed The result of the query.
     */
    public static function executeQuery($sql)
    {
        $result = db_query($sql);
        if (!$result) {
            error_log("Database query error: " . db_error());
        }
        return $result;
    }

    /**
     * Fetch a single row from the result set.
     *
     * @param mixed $result The result set.
     * @return array|null The fetched row as an associative array, or null if no more rows.
     */
    public static function fetchRow($result)
    {
        return db_fetch_array($result);
    }

    /**
     * Fetch the number of rows in the result set.
     *
     * @param mixed $result The result set.
     * @return int The number of rows.
     */
    public static function numRows($result)
    {
        return db_num_rows($result);
    }

    /**
     * Get the last inserted ID in the database.
     *
     * @return int The last inserted ID.
     */
    public static function getLastInsertId()
    {
        return db_insert_id();
    }

    /**
     * Start a database transaction.
     */
    public static function startTransaction()
    {
        self::executeQuery('START TRANSACTION');
    }

    /**
     * Commit the current database transaction.
     */
    public static function commit()
    {
        self::executeQuery('COMMIT');
    }

    /**
     * Rollback the current database transaction.
     */
    public static function rollback()
    {
        self::executeQuery('ROLLBACK');
    }
}

?>
