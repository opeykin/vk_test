<?php

function redirect($url, $delay = 0) {
    header("refresh:$delay;url=$url");
    exit();
}

class ConfigSingleton {
    private static $config_instance;

    private function __construct() {
    }

    public static function getInstance() {
        if (self::$config_instance === null) {
            self::$config_instance = parse_ini_file('connection.cfg');
        }

        return self::$config_instance;
    }
    private function __clone() {
    }

    private function __wakeup() {
    }
}

function config()
{
    return ConfigSingleton::getInstance();
}

