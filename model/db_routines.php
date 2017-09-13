<?php

require_once 'utils/utils.php';


function db_connect($config)
{
    $db = mysqli_connect($config['db_host'], $config['user'], $config['password'], $config['database']);
    if (!$db) {
        error_log('[db_connect] ' . mysqli_connect_error());
        return false;
    }

    return $db;
}

class DbSingleton {
    private static $db_instance;

    private function __construct() {
    }

    public static function getInstance() {
        if (self::$db_instance === null) {
            self::$db_instance = db_connect(config());
        }

        return self::$db_instance;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}

function db()
{
    return DbSingleton::getInstance();
}

class Constants
{
    const PAGE_SIZE = 50;
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

function fetch_all($db, $query, $result_type = MYSQLI_ASSOC)
{
    $result = mysqli_query($db, $query);

    if ($result)
        return mysqli_fetch_all($result, $result_type);
    else
        return false;
}

function flatten_single($array)
{
    if ($array === false)
        return false;

    $result = array();

    foreach ($array as $elem) {
        $result[] = $elem[0];
    }

    return $result;
}

function db_fetch_ids($db, $sort_column, $sort_direction, $skip, $count)
{
    $query = "SELECT id FROM items ORDER BY $sort_column $sort_direction LIMIT $skip, $count";
    $ids =  fetch_all($db, $query, MYSQLI_NUM);
    return flatten_single($ids);
}

function db_fetch_items($db, $ids)
{
    if (empty($ids))
        return array();

    $ids_str = join(',', $ids);
    $query = "SELECT * FROM items WHERE id IN ($ids_str)";
    return fetch_all($db, $query);
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