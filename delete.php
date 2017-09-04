<?php

require_once 'utils/utils.php';
require_once 'utils/db_routines.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = (int)$_GET['id'];
$db = db_connect();

if (db_delete_item($db, $id)) {
    redirect('delete_success.php');
} else {
    redirect('index.php');
}
