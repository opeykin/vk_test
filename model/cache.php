<?php

require_once 'utils/utils.php';

class CacheExpireTime
{
    const COUNT = 300;
    const PAGE = 300;
    const ITEM = 300;
    const LOCK = 1;
}

class CacheConstants
{
    const CACHE_LOCK_RETRY_COUNT = 5;
    const CACHE_LOCK_RETRY_DELAY_MS = 100000;
}

class PagingConstants
{
    const PAGE_SIZE = 50;
    const PAGE_FETCH_COUNT = 500;
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

function cache_item_key($id)
{
    return "ITEM[$id]";
}

function cache_lock_key($cache_key)
{
    return "LOCK[$cache_key]";
}

function cache_lock_key_multi(array $cache_keys)
{
    sort($cache_keys);
    $keys_md5 = md5(join('', $cache_keys));
    return "LOCK_MULTI[$keys_md5]";
}

function cache_page_key($sort_column, $sort_direction, $page, $version)
{
    // TODO: Too long key. apply md5 or something.
    return "PAGE[$sort_column,$sort_direction,$page,$version]";
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
    cache_drop_paging();
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
 * @return bool|mixed the value stored in the cache or FALSE otherwise
 */
function cache_fetch_or_lock($cache_key, $lock_key, &$lock_result)
{
    $cache = cache();

    for ($i = 0; $i < CacheConstants::CACHE_LOCK_RETRY_COUNT; $i++) {

        $cache_result = $cache->get($cache_key);
        if ($cache_result)
            return $cache_result;

        $lock_result = $cache->add($lock_key, true, CacheExpireTime::LOCK);
        if ($lock_result)
            return false;

        usleep(CacheConstants::CACHE_LOCK_RETRY_DELAY_MS);
    }

    return false;
}

/**
 * Fetch a record from cache of lock the record for db fetching
 * @param array $cache_keys cache record keys
 * @param string $lock_key cache locking record key
 * @param bool $lock_result reference. TRUE is locked, FALSE - otherwise
 * @return array of items fetched from cache. In case of error returns empty array
 */
function cache_fetch_multi_or_lock(array $cache_keys, $lock_key, &$lock_result)
{
    $cache = cache();
    $cached_elements = array();
    $cache_keys_to_fetch = $cache_keys;

    for ($i = 0; $i < CacheConstants::CACHE_LOCK_RETRY_COUNT; $i++) {

        $get_result = $cache->getMulti($cache_keys_to_fetch);
        if ($get_result) {
            $cache_keys_to_fetch = keys_not_from_array($get_result, $cache_keys);
            $cached_elements = array_merge($cached_elements, $get_result);
            if (count($cache_keys_to_fetch) == 0)
                return $cached_elements;
        }

        $lock_result = $cache->add($lock_key, true, CacheExpireTime::LOCK);
        if ($lock_result)
            break;

        usleep(CacheConstants::CACHE_LOCK_RETRY_DELAY_MS);
    }

    return $cached_elements;
}

function cache_init_paging($cache, $key, &$value)
{
    $value = 1;
    return true;
}

function cache_save_pages($first_fetch_page, $sort_column, $sort_direction, $version, $ids)
{
    $items = array();
    $array_size = count($ids);

    for ($offset = 0; $offset < $array_size; $offset += PagingConstants::PAGE_SIZE) {
        $page_ids = array_slice($ids, $offset, PagingConstants::PAGE_SIZE);
        $page = $first_fetch_page + intdiv($offset, PagingConstants::PAGE_SIZE);
        $key = cache_page_key($sort_column, $sort_direction, $page, $version);
        $items[$key] = $page_ids;
    }

    cache()->setMulti($items, CacheExpireTime::PAGE);
}




