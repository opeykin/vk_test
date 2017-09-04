<?php

require_once 'db_routines.php';

function model_get_cache($config)
{
    $cache = new Memcached();
    $cache->addServer($config['memcached_host'], $config['memcached_port']);
    return $cache;
}

class Connections {
    private static $db_instance;
    private static $config_instance;
    private static $cache_instance;

    private function __construct() {
    }

    public static function getConfigInstance() {
        if (self::$config_instance === null) {
            self::$config_instance = parse_ini_file(Constants::DB_CONFIG_PATH);;
        }

        return self::$config_instance;
    }

    public static function getDbInstance() {
        if (self::$db_instance === null) {
            self::$db_instance = db_connect(self::getConfigInstance());
        }

        return self::$db_instance;
    }

    public static function getCacheInstance() {
        if (self::$cache_instance === null) {
            self::$cache_instance = model_get_cache(self::getConfigInstance());
        }

        return self::$cache_instance;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

function db()
{
    return Connections::getDbInstance();
}

function cache()
{
    return Connections::getCacheInstance();
}

function model_count()
{
    $cache = cache();
    $count = $cache->get('count');

    if ($count === false) {
        $count = db_fetch_items_count(db());
        $cache->set('count', $count, Constants::CACHE_EXPIRE_TIME);
    }

    return $count;
}

function model_add_item($name, $price, $description, $img)
{
    $result = db_add_item(db(), $name, $price, $description, $img);
    if ($result) {
        cache()->increment('count');
    }

    return $result;
}

function model_delete_item($id)
{
    $result = db_delete_item(db(), $id);
    if ($result) {
        cache()->decrement('count');
    }

    return $result;
}