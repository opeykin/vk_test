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

function keys_not_from_array($array, $keys)
{
    $result = array();

    foreach ($keys as $key) {
        if (!array_key_exists($key, $array)) {
            $result[] = $key;
        }
    }

    return $result;
}



