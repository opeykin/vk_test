<?php

require_once 'db_routines.php';
require_once 'utils/utils.php';

function model_get_cache($config)
{
    $cache = new Memcached();
    $cache->addServer($config['memcached_host'], $config['memcached_port']);
    return $cache;
}

class Connections {
    private static $db_instance;
    private static $cache_instance;

    private function __construct() {
    }

    public static function getDbInstance() {
        if (self::$db_instance === null) {
            self::$db_instance = db_connect(config());
        }

        return self::$db_instance;
    }

    public static function getCacheInstance() {
        if (self::$cache_instance === null) {
            $config = config();
            self::$cache_instance = new Memcached();
            self::$cache_instance->addServer($config['memcached_host'], $config['memcached_port']);
        }

        return self::$cache_instance;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

class CacheExpireTime
{
    const COUNT = 60;
    const PAGE = 60;
    const ITEM = 60;
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
        $cache->set('count', $count, CacheExpireTime::COUNT);
    }

    return $count;
}

function model_cache_page_key($sort_column, $sort_direction)
{
    return "SELECT[$sort_column,$sort_direction]";
}

function model_cache_item_key($id)
{
    return $id;
}

function cache_paging_key($sort_column, $sort_direction, $page, $version)
{
    // TODO: Too long key. apply md5 or something.
    return "PAGING[$sort_column,$sort_direction,$page,$version]";
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

function cache_drop_item($id)
{
    cache()->delete(model_cache_item_key($id));
}

function cache_drop_paging()
{
    cache()->increment('paging_version');
}

function cache_on_item_add()
{
    cache()->increment('count');
    cache_drop_paging();
}

function cache_on_item_delete($id)
{
    cache()->decrement('count');
    cache_drop_item($id);
    cache_drop_paging();
}

function cache_on_item_update($id)
{
    cache_drop_item($id);
}


function cache_on_items_fetch($items) {
    $cache = cache();
    foreach ($items as $item) {
        $key = model_cache_item_key($item['id']);
        $cache->set($key, $item, CacheExpireTime::ITEM);
    }
}

function model_add_item($item)
{
    $result = db_add_item(db(), $item);
    if (!$result)
        return false;

    $id = db_get_last_inserted_id(db());
    $item['id'] = $id;

    cache_on_item_add();

    return $id;
}

function model_delete_item($id)
{
    $result = db_delete_item(db(), $id);

    if ($result) {
        cache_on_item_delete($id);
    }

    return $result;
}

function model_update_item($item)
{
    $result = db_update_item(db(), $item);

    if ($result) {
        cache_on_item_update($item['id']);
    }

    return $result;
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

function get_values_from_either_array_in_order($keys, $array1, $array2)
{
    $result = array();

    foreach ($keys as $key) {
        if (array_key_exists($key, $array1))
            $result[] =  $array1[$key];
        else
            $result[] =  $array2[$key];
    }

    return $result;
}

function add_in_id_order(&$array, $id_to_position, $items)
{
    foreach ($items as $item) {
        $id = $item['id'];
        $position = $id_to_position[$id];
        $array[$position] = $item;
    }
}

/*
 * Some items are fetched from cache, some from db. Order is lost.
 * Combine two arrays correct id order
*/
function combine_items($ids, $items_from_cache, $items_from_db)
{
    $result = array_fill(0, count($ids), 0);
    $id_to_position = array_flip($ids);

    add_in_id_order($result, $id_to_position, $items_from_cache);
    add_in_id_order($result, $id_to_position, $items_from_db);

    return $result;
}

function model_fetch_items($ids)
{
    $keys = array_map("model_cache_item_key", $ids);
    $items_from_cache = cache()->getMulti($keys);
    $uncached_ids = keys_not_from_array($items_from_cache, $ids);
    $items_from_db = db_fetch_items(db(), $uncached_ids);

    if ($items_from_db === false)
        return false;

    cache_on_items_fetch($items_from_db);

    return combine_items($ids, array_values($items_from_cache), $items_from_db);
}

function cache_init_paging($cache, $key, &$value)
{
    $value = 1;
    return true;
}

function model_fetch_ids($sort_column, $sort_direction, $page)
{
    $paging_version = cache()->get('paging_version', 'cache_init_paging');

    if ($paging_version == false)
        return false;

    $key = cache_paging_key($sort_column, $sort_direction, $page, $paging_version);
    $ids_from_cache = cache()->get($key);

    if ($ids_from_cache !== false) {
        return $ids_from_cache;
    }

    $ids_from_db = db_fetch_ids(db(), $sort_column, $sort_direction, $page);

    if ($ids_from_db !== false) {
        cache()->set($key, $ids_from_db, CacheExpireTime::PAGE);
    }

    return $ids_from_db;
}

function model_fetch_items_page($sort_column, $sort_direction, $page)
{
    $ids = model_fetch_ids($sort_column, $sort_direction, $page);

    if (!$ids)
        return false;

    return model_fetch_items($ids);
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
        $cache->set($key, $result, CacheExpireTime::ITEM);
    }

    return $result;
}

