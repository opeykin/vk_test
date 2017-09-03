<?php


require_once 'impl/index_impl.php';
require_once 'utils/utils.php';


$params = get_params();

if ($params == null) {
    redirect('index.php');
}

$db = db_connect() or die('Can\'t connect');

$items_count = db_fetch_items_count($db);
$page_count = (int)($items_count / Constants::PAGE_SIZE + 1);
$page = $params['page'];

if ($page >= $page_count) {
    redirect('index.php');
}

$skip = $page * Constants::PAGE_SIZE;

$items = db_fetch_items($db, $params['sort_field'], $params['sort_direction'], $skip);




include 'templates/header.php';
include 'parts/sorting_selector.php';
include 'parts/items.php';
html_print_page_switcher($page, $page_count, $order);

include 'templates/footer.php';
