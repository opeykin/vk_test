<?php


require_once 'impl/index_impl.php';
require_once 'utils/utils.php';
require_once 'utils/model.php';



$params = get_params();

if ($params == null) {
    redirect('index.php');
}

$items_count = model_count();
$page_count = (int)($items_count / Constants::PAGE_SIZE + 1);
$page = $params['page'];

if ($page >= $page_count) {
    redirect('index.php');
}

//$skip = $page * Constants::PAGE_SIZE;

$items = model_fetch_items_page($params['sort_field'], $params['sort_direction'], $page);




include 'templates/header.php';
include 'parts/sorting_selector.php';

// TODO: check for empty db ($items == null)
include 'parts/items.php';

html_print_page_switcher($page, $page_count, $order);

include 'templates/footer.php';
