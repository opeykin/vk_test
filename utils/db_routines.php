<?php

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