<?php

class Constants
{
    const PAGE_SIZE = 50;
    const DB_CONFIG_PATH = 'db.cfg';
}

function db_connect($config)
{
    $handle = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
    if (!$handle) {
        error_log('[db_connect] ' . mysqli_connect_error());
        return false;
    }

    return $handle;
}

function db_add_item($handle, $name, $price, $description, $img)
{
//    $query = "INSERT INTO items (name, price, description, img) VALUE ('$name', $price, '$description', '$img');";
    $query = "INSERT INTO items (name, price, description, img) VALUE (?, ?, ?, ?);";
    $stmt = mysqli_prepare($handle, $query);
    mysqli_stmt_bind_param($stmt, 'siss', $name, $price, $description, $img);

    if (!mysqli_stmt_execute($stmt)) {
        error_log("[db_add_item] " . mysqli_error($handle)."\nquery: $query");
        return false;
    }

    return true;
}

function db_fetch_items($handle, $sort_column, $sort_direction, $skip)
{
    $page_size = Constants::PAGE_SIZE;
    $result = mysqli_query($handle, "SELECT id, img, name, price, description  FROM items ORDER BY $sort_column $sort_direction LIMIT $skip, $page_size");
    $rows = mysqli_fetch_all($result, MYSQLI_NUM);
    return $rows;
}

function db_fetch_items_count($handle)
{
    $result = mysqli_query($handle, 'SELECT count(*) FROM items;');
    return mysqli_fetch_row($result)[0];
}