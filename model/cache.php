<?php

class CacheExpireTime
{
    const COUNT = 60;
    const PAGE = 60;
    const ITEM = 60;
    const LOCK = 1;
}

class CacheSingleton {
    private static $cache_instance;

    private function __construct() {
    }

    public static function getInstance() {
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

function cache()
{
    return CacheSingleton::getInstance();
}

function cache_page_key($sort_column, $sort_direction)
{
    return "SELECT[$sort_column,$sort_direction]";
}

function cache_item_key($id)
{
    return $id;
}

function cache_paging_key($sort_column, $sort_direction, $page, $version)
{
    // TODO: Too long key. apply md5 or something.
    return "PAGING[$sort_column,$sort_direction,$page,$version]";
}

function cache_drop_item($id)
{
    cache()->delete(cache_item_key($id));
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
        $key = cache_item_key($item['id']);

        // TODO: should probably CAS here. In theory - item can be updated
        // between db fetch and cache write.
        $cache->set($key, $item, CacheExpireTime::ITEM);
    }
}

/**
 * Fetch a record from cache of lock the record for db fetching
 * @param string $cache_key cache record key
 * @param string $lock_key cache locking record key
 * @param bool $lock_result reference. TRUE is locked, FALSE - otherwise
 * @param int $max_lock_attempts [optional] how many time should try to lock record
 * @param int $sleep_time_ms [optional] sleep time in milliseconds between lock attempts
 * @return bool|mixed the value stored in the cache or FALSE otherwise
 */
function cache_fetch_or_lock($cache_key, $lock_key, &$lock_result, $max_lock_attempts = 5, $sleep_time_ms = 100000)
{
    $cache = cache();

    for ($i = 0; $i < $max_lock_attempts; $i++) {

        $cache_result = $cache->get($cache_key);
        if ($cache_result)
            return $cache_result;

        $lock_result = $cache->add($lock_key, true, CacheExpireTime::LOCK);
        if ($lock_result)
            return false;

        usleep($sleep_time_ms);
    }

    return false;
}




