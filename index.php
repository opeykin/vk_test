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

function rows_to_html($rows)
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


function get_sorting()
{
    $order = (int)($_GET['order'] ?? 0);

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

    error_log("Illegal GET param. order has unknown value: $order");

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

$skip = get_page() * Constants::PAGE_SIZE;
$sorting = get_sorting();
$sort_column = $sorting[0];
$sort_direction = $sorting[1];


$db_config = parse_ini_file(Constants::DB_CONFIG_PATH);
$handle = db_connect($db_config) or die('Can\'t connect');

$rows = db_fetch_items($handle, $sort_column, $sort_direction, $skip);
$items_count = db_fetch_items_count($handle);

rows_to_html($rows);

echo 'Pages: ' . (int)($items_count / Constants::PAGE_SIZE + 1);


include_once 'footer.php';
