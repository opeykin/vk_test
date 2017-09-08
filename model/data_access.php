<?php

require_once 'db_routines.php';
require_once 'utils/utils.php';
require_once 'cache.php';


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

function model_fetch_item($id)
{
    $cache = cache();

    $key = cache_item_key($id);
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

