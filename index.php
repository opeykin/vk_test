<?php


require_once 'impl/index_impl.php';
require_once 'utils/utils.php';
require_once 'model/data_access.php';



$params = get_params();

if ($params == null) {
    redirect('index.php');
}

$items_count = model_count();
$page_count = (int)($items_count / PagingConstants::PAGE_SIZE + 1);
$page = $params['page'];

if ($page >= $page_count) {
    redirect('index.php');
}

$items = model_fetch_items_page($params['sort_field'], $params['sort_direction'], $page);

include 'templates/header.php';
include 'parts/list_controls.php';
include 'parts/items.php';
html_print_page_switcher($page, $page_count, $params['order']);
include 'templates/footer.php';
