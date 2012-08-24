<?php
/**
 * Database class is cover for PDO
 * It uses singleton pattern for make sure that app uses only one db connection
 */
class Database
{
    private static $_instance;

    public static function getInstance()
    {
        if ( !isset(self::$_instance) ) {
            self::$_instance = new PDO('mysql:host=localhost;dbname=zennex', 'root', 'root');
            self::$_instance->exec('SET CHARSET utf8');
        }

        return self::$_instance;
    }
}