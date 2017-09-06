<?php

class Constants
{
    const PAGE_SIZE = 50;
    const DB_CONFIG_PATH = 'connection.cfg';
    const CACHE_EXPIRE_TIME = 60;
}

function db_connect($config)
{
    $db = mysqli_connect($config['db_host'], $config['user'], $config['password'], $config['database']);
    if (!$db) {
        error_log('[db_connect] ' . mysqli_connect_error());
        return false;
    }

    return $db;
}

function db_get_last_inserted_id($db) {
    $result = mysqli_query($db, 'SELECT @id := LAST_INSERT_ID();');
    return $result ? mysqli_fetch_row($result)[0] : false;
}

function db_add_item($db, $item)
{
//    $query = "INSERT INTO items (name, price, description, img) VALUE ('$name', $price, '$description', '$img');";
    $query = "INSERT INTO items (name, price, description, img) VALUE (?, ?, ?, ?);";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'siss', $item['name'], $item['price'], $item['description'], $item['img']);

    if (!mysqli_stmt_execute($stmt)) {
        error_log("[db_add_item] " . mysqli_error($db)."\nquery: $query");
        return false;
    }

    return true;
}

function db_fetch_items($db, $sort_column, $sort_direction, $skip)
{
    $page_size = Constants::PAGE_SIZE;

    $query =
     "SELECT l.id, l.img, l.name, l.price, l.description
     FROM (
            SELECT id
            FROM items
            ORDER BY $sort_column $sort_direction
            LIMIT $skip, $page_size) r
     JOIN items l ON l.id = r.id
     ORDER BY l.$sort_column $sort_direction;";

    $result = mysqli_query($db, $query);
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $rows;
}

function db_fetch_item($db, $id)
{
    $result = mysqli_query($db, "SELECT * FROM items WHERE id=$id;");

    if (!$result) {
        error_log("[db_fetch_item] " . mysqli_error($db));
        return null;
    }

    return mysqli_fetch_assoc($result);
}

function db_fetch_items_count($db)
{
    $result = mysqli_query($db, 'SELECT count(*) FROM items;');
    return $result ? mysqli_fetch_row($result)[0] : false;
}

function db_update_item($db, $item)
{
    $query = "UPDATE items SET name = ?, price = ?, description = ?, img = ? WHERE id = ?;";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'sissi', $item['name'], $item['price'], $item['description'], $item['img'], $item['id']);

    if (!mysqli_stmt_execute($stmt)) {
        error_log("[db_update_item] " . mysqli_error($db)."\nquery: $query");
        return false;
    }

    return true;
}

function db_delete_item($db, $id)
{
    return mysqli_query($db, "DELETE FROM items WHERE id=$id");
}