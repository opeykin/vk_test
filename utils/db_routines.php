<?php

function db_connect($host = '', $user = '', $password = '', $database = '')
{
    $handle = mysqli_connect($host, $user, $password, $database);
    if (!$handle) {
        $msg = 'MySQL connection failed.';
        $data = "host=$host user=$user db=$database";
        error_log($msg . ' ' . mysqli_connect_error() . ' ' . $data);
        return false;
    }

    return $handle;
}