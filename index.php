<?php


require_once 'utils/db_routines.php';
require_once 'utils/Constants.php';




function row_to_html($row)
{
    return
        "<div class='list_item clearfix'>
    <div class='list_photo image_wrapper fl_l'>
        <img src=\"$row[1]\">
    </div>
    <div class='list_text_block'>
        <div>$row[2]</div>
        <div>$row[3]</div>
        <div>$row[4]</div>
    </div>
</div>";
}

function html_print_page_link($url, $order, $page, $text) {
    $url_with_params = "$url?order=$order&page=$page";
    echo "<a href='$url_with_params'>$text</a>";
}

function html_print_page_switcher($cur_page, $page_count, $order)
{
    echo '<div>';

    $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    if ($cur_page > 0) {
        html_print_page_link($url, $order, 0, '0');
    }

    if ($cur_page > 1) {
        html_print_page_link($url, $order, $cur_page - 1, '...');
    }

    html_print_page_link($url, $order, $cur_page, $cur_page);

    if ($cur_page < $page_count - 2) {
        html_print_page_link($url, $order, $cur_page + 1, '...');
    }

    if ($cur_page < $page_count - 1) {
        html_print_page_link($url, $order, $page_count - 1, $page_count - 1);
    }

    echo '</div>';
}

function html_print_rows($rows)
{
    echo '<div>';

    foreach ($rows as &$row) {
        echo row_to_html($row);
    }

    echo '</div>';
}


function get_page()
{
    if (!isset($_GET['page']))
        return 0;

    $page = $_GET['page'];
    if (!is_numeric($page)) {
        error_log("Illegal GET param. page is not a number: $page");
        return 0;
    }

    return (int)$page;
}

function get_order()
{
    $order = (int)($_GET['order'] ?? 0);
    if (!is_numeric($order) || $order < 0 || $order >= 3) {
        error_log("Illegal GET param. order has unknown value: $order");
        return 0;
    }

    return (int)$order;

}

function get_sorting($order)
{
    switch ($order) {
        case '0': // cheap
            return array('price', 'ASC');
        case '1': // expensive
            return array('price', 'DESC');
        case '2': // old
            return array('id', 'ASC');
        case '3': // new
            return array('id', 'DESC');
    }

    return array('price', 'ASC');
}


function db_fetch_items($handle, $sort_column, $sort_direction, $skip)
{
    $page_size = Constants::PAGE_SIZE;
    $result = mysqli_query($handle, "SELECT id, img, name, price, description  FROM items ORDER BY $sort_column $sort_direction LIMIT $skip, $page_size");
    $rows = mysqli_fetch_all($result, MYSQLI_NUM);
    return $rows;
}

function db_fetch_items_count($handle)
{
    $result = mysqli_query($handle, 'SELECT count(*) FROM items;');
    return mysqli_fetch_row($result)[0];
}

include_once 'header.php';
include 'sorting_selector.php';


$order = get_order();
$sorting = get_sorting($order);
$sort_column = $sorting[0];
$sort_direction = $sorting[1];


$db_config = parse_ini_file(Constants::DB_CONFIG_PATH);
$handle = db_connect($db_config) or die('Can\'t connect');

$items_count = db_fetch_items_count($handle);
$page_count = (int)($items_count / Constants::PAGE_SIZE + 1);
$page = max(min(get_page(), $page_count - 1), 0);
$skip = $page * Constants::PAGE_SIZE;

$rows = db_fetch_items($handle, $sort_column, $sort_direction, $skip);

html_print_rows($rows);
html_print_page_switcher($page, $page_count, $order);


include_once 'footer.php';
