<?php

require_once 'config.php';

class db {

    /**
     * @global array $config
     * @return \PDO
     */
    public static function connect() {
        global $config;
        return new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'], $config['user'], $config['password']);
    }
}
