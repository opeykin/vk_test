<?php


require_once 'index_impl.php';

include_once 'header.php';
include 'sorting_selector.php';


$params = get_params();

if ($params == null) {
    redirect('index.php');
}

$db_config = parse_ini_file(Constants::DB_CONFIG_PATH);
$handle = db_connect($db_config) or die('Can\'t connect');

$items_count = db_fetch_items_count($handle);
$page_count = (int)($items_count / Constants::PAGE_SIZE + 1);
$page = $params['page'];

if ($page >= $page_count) {
    redirect('index.php');
}

$skip = $page * Constants::PAGE_SIZE;

$rows = db_fetch_items($handle, $params['sort_field'], $params['sort_direction'], $skip);

html_print_rows($rows);
html_print_page_switcher($page, $page_count, $order);


include_once 'footer.php';
