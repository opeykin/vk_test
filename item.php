<?php


require_once 'utils/utils.php';
require_once 'utils/model.php';

$id = (int)$_GET['id'] ?? -1;

$item = model_fetch_item($id);

if (!$item) {
    redirect('index.php');
}

include 'templates/header.php';
include 'parts/item.php';
include 'templates/footer.php';