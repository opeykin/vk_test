<?php

require_once 'utils/utils.php';
require_once 'model/data_access.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = (int)$_GET['id'];

if (model_delete_item($id)) {
    redirect('delete_success.php');
} else {
    redirect('index.php');
}
