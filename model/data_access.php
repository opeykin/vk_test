<?php

require_once 'db_routines.php';
require_once 'utils/utils.php';
require_once 'cache.php';


function model_count()
{
    $cache_key = cache_item_key("count");
    $lock_key = cache_lock_key($cache_key);

    $db_fetch = function () {
        return db_fetch_items_count(db());
    };

    $cache_save = function ($data) use ($cache_key) {
        cache()->set($cache_key, $data, CacheExpireTime::COUNT);
    };

    return model_fetch_with_cache_locks($cache_key, $lock_key, $cache_save, $db_fetch);
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
    $keys = array_map("cache_item_key", $ids);
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

/**
 * Fetching data with read through caching and cache locking to protect against stampeding herd problem.
 * @param string $cache_key cache record key
 * @param string $lock_key cache locking record key
 * @param callable $cache_save_cb callback to save data fetched from database to cache
 * @param callable $db_fetch_cb callback to fetch data from database
 * @param callable|null $db_result_transform [optional] transform applied to data fetched from db before return
 * @return bool|mixed the value stored in the cache or $db_result(data_fetched_from_db) or FALSE otherwise
 */
function model_fetch_with_cache_locks($cache_key, $lock_key, callable $cache_save_cb, callable $db_fetch_cb, callable $db_result_transform = null)
{
    $cache = cache();
    $cache_result = cache_fetch_or_lock($cache_key, $lock_key, $lock_result);

    if ($cache_result !== false)
        return $cache_result;

    $db_result = $db_fetch_cb();

    if ($lock_result) {
        if ($db_result)
            $cache_save_cb($db_result);
        $cache->delete($lock_key);
    }

    return $db_result;
}

function model_fetch_item($id)
{
    $cache_key = cache_item_key($id);
    $lock_key = cache_lock_key($cache_key);

    $cache_save = function ($data) use ($cache_key) {
        cache()->set($cache_key, $data, CacheExpireTime::ITEM);
    };

    $db_fetch = function () use ($id) {
        return db_fetch_item(db(), $id);
    };

    return model_fetch_with_cache_locks($cache_key, $lock_key, $cache_save, $db_fetch);
}

