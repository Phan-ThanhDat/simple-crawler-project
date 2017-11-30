<?php

/**
 * Class App
 * @property PDO $pdoObject
 */
class App {

    protected
        $pdoObject = null;

    /**
     * @return PDO
     */
    public function getPDOObject() {
        global $pdoObject;
        if (!$pdoObject) {
            /**
             * Connect to MySQL and instantiate the PDO object.
             * Set the error mode to throw exceptions and disable emulated prepared statements.
             */
            $pdoObject = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ));
        }

        return $pdoObject;
    }
}

