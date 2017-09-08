<?php

class CacheExpireTime
{
    const COUNT = 60;
    const PAGE = 60;
    const ITEM = 60;
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




