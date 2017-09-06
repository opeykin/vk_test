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

function model_cache_page_key($sort_column, $sort_direction)
{
    return "SELECT[$sort_column,$sort_direction]";
}

function model_cache_item_key($id)
{
    return "ID[$id]";
}

function model_cache_drop_page($sort_column, $sort_direction, $value)
{
    $key = model_cache_page_key($sort_column, $sort_direction);
    $result = cache()->get($key);

    if ($result === false) {
        return;
    }

    $first_value = $result[0][$sort_column];
    $last_value = $result[count($result) - 1][$sort_column];

    $min_value = min($first_value, $last_value);
    $max_value = max($first_value, $last_value);

    if ($min_value <= $value && $value <= $max_value) {
        cache()->delete($key);
    }
}

function model_cache_drop_single_item($id)
{
    $key = model_cache_item_key($id);
    cache()->delete($key);
}

function model_cache_drop($item)
{
    model_cache_drop_page('id', 'ASC', $item['id']);
    model_cache_drop_page('id', 'DESC', $item['id']);
    model_cache_drop_page('price', 'ASC', $item['price']);
    model_cache_drop_page('price', 'DESC', $item['price']);

    model_cache_drop_single_item($item['id']);
}

function model_add_item($item)
{
    $result = db_add_item(db(), $item);
    if (!$result)
        return false;

    $id = db_get_last_inserted_id(db());
    if ($id !== false) {
        $item['id'] = $id;
    }

    cache()->increment('count');
    model_cache_drop($item);

    return $id;
}

function model_delete_item($id)
{
    $item = db_fetch_item(db(), $id);
    $result = db_delete_item(db(), $id);

    if ($result) {
        cache()->decrement('count');
        model_cache_drop($item);
    }

    return $result;
}

function model_update_item($item)
{
    $result = db_update_item(db(), $item);

    if ($result) {
        model_cache_drop($item);
    }

    return $result;
}

function model_fetch_items_page($sort_column, $sort_direction, $page)
{
    $skip = $page * Constants::PAGE_SIZE;

    // cache only first page for now
    if ($page !== 0) {
        return db_fetch_items(db(), $sort_column, $sort_direction, $skip);
    }

    $key = model_cache_page_key($sort_column, $sort_direction);
    $cache = cache();
    $result = $cache->get($key);

    if ($result) {
        return $result;
    }

    $result = db_fetch_items(db(), $sort_column, $sort_direction, $skip);
    if ($result) {
        $cache->set($key, $result, Constants::CACHE_EXPIRE_TIME);
    }

    return $result;
}

function model_fetch_item($id)
{
    $cache = cache();

    $key = model_cache_item_key($id);
    $result = $cache->get($key);

    if ($result) {
        return $result;
    }

    $result = db_fetch_item(db(), $id);

    if ($result) {
        $cache->set($key, $result, Constants::CACHE_EXPIRE_TIME);
    }

    return $result;
}

