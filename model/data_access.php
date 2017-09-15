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
    $cache_keys = array_map("cache_item_key", $ids);
    $lock_key = cache_lock_key_multi($cache_keys);

    $cache_key_to_id = array_combine($cache_keys, $ids);

    $cache_save = function ($items) {
        $data_with_keys = array();

        foreach ($items as $item) {
            $key = cache_item_key($item['id']);
            $data_with_keys[$key] = $item;
        }

        cache()->setMulti($data_with_keys, CacheExpireTime::ITEM);
    };

    $db_fetch = function ($uncached_item_keys) use($cache_key_to_id) {
        $uncached_item_ids = array();

        foreach ($uncached_item_keys as $uncached_item_key)
            $uncached_item_ids[] = $cache_key_to_id[$uncached_item_key];

        return db_fetch_items(db(), $uncached_item_ids);
    };

    $combine = function ($items_from_cache, $items_from_db) use ($ids) {
        return combine_items($ids, $items_from_cache, $items_from_db);
    };

    return model_fetch_multi_with_cache_locks($cache_keys, $lock_key, $cache_save, $db_fetch, $combine);
}

function model_fetch_ids($sort_column, $sort_direction, $page)
{
    $version = cache()->get('paging_version', 'cache_init_paging');

    if ($version == false)
        return false;

    $cache_key = cache_page_key($sort_column, $sort_direction, $page, $version);
    $lock_key = cache_lock_key($cache_key);

    $first_fetch_page = $page -  $page % PagingConstants::PAGE_FETCH_COUNT;

    $cache_save = function ($ids) use ($first_fetch_page, $sort_column, $sort_direction, $version) {
        cache_save_pages($first_fetch_page, $sort_column, $sort_direction, $version, $ids);
    };

    $db_fetch = function () use($sort_column, $sort_direction, $first_fetch_page) {
        $skip = $first_fetch_page * PagingConstants::PAGE_SIZE;
        $count = PagingConstants::PAGE_FETCH_COUNT * PagingConstants::PAGE_SIZE;
        return db_fetch_ids(db(), $sort_column, $sort_direction, $skip, $count);
    };

    $db_return_result_transform = function ($ids) use ($first_fetch_page, $page) {
        $offset = ($page - $first_fetch_page) * PagingConstants::PAGE_SIZE;
        return array_slice($ids, $offset, PagingConstants::PAGE_SIZE);
    };

    return model_fetch_with_cache_locks($cache_key, $lock_key, $cache_save, $db_fetch, $db_return_result_transform);
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
 * @param callable|null $db_return_result_transform [optional] transform applied to data fetched from db before return
 * @return bool|mixed the value stored in the cache or $db_result(data_fetched_from_db) or FALSE otherwise
 */
function model_fetch_with_cache_locks($cache_key, $lock_key, callable $cache_save_cb, callable $db_fetch_cb, callable $db_return_result_transform = null)
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

    if ($db_return_result_transform)
        return $db_return_result_transform($db_result);
    else
        return $db_result;
}

/**
 * Fetching data multi key data with read through caching and cache locking to protect against stampeding herd problem.
 * Items are locked all together with single $lock_key.
 * @param array $cache_keys cache record keys
 * @param string $lock_key cache locking record key
 * @param callable $cache_save_cb callback to save data fetched from database to cache
 * @param callable $db_fetch_cb callback to fetch data from database
 * @param callable $combine_cb callback to combine items from cache and database
 * @return bool|mixed the value from cache/database or FALSE otherwise
 */
function model_fetch_multi_with_cache_locks(array $cache_keys, $lock_key, callable $cache_save_cb, callable $db_fetch_cb, callable $combine_cb)
{
    $cache_result = cache_fetch_multi_or_lock($cache_keys, $lock_key, $lock_result);

    if (count($cache_result) === count($cache_keys))
        return $combine_cb(array_values($cache_result), array());

    $uncached_item_keys = keys_not_from_array($cache_result, $cache_keys);
    $db_result = $db_fetch_cb($uncached_item_keys);

    if ($lock_result) {
        if ($db_result)
            $cache_save_cb($db_result);
        cache()->delete($lock_key);
    }

    if ($db_result === false) {
        return false;
    }

    return $combine_cb(array_values($cache_result), $db_result);
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

