<?php

include_once 'header.php';
require_once 'utils/db_routines.php';
require_once 'utils/Constants.php';

function get_order()
{
    if (!isset($_GET['order']))
        return 0;

    if (is_numeric($_GET['order']))
        return (int)$_GET['order'];

    return 0;
}

// sorter with correct element selected
// TODO: Look like shit. Do people even generate stuff like this?

echo '<div class="clearfix">
    <div class="fl_l">I want to see</div>
    <select onchange="document.location.href=\'?order=\'+this.selectedIndex">';

$order = get_order();
$sort_items = array('Cheap', 'Expensive', 'Old', 'New');

for ($i = 0; $i < count($sort_items); $i++) {
    echo '<option ';
    if ($i === $order)
        echo 'selected';
    echo ">$sort_items[$i]</option>";
}

echo '</select></div>';




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
    $order = get_order();
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


function fetch_items_from_db($sort_column, $sort_direction, $skip)
{
    $db_config = parse_ini_file(Constants::DB_CONFIG_PATH);
    $handle = db_connect($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']) or die('Can\'t connect');
    $page_size = Constants::PAGE_SIZE;
    $result = mysqli_query($handle, "SELECT id, img, name, price, description  FROM items ORDER BY $sort_column $sort_direction LIMIT $skip, $page_size");
    $rows = mysqli_fetch_all($result, MYSQLI_NUM);
    mysqli_close($handle);
    return $rows;
}

$skip = get_page() * Constants::PAGE_SIZE;
$sorting = get_sorting();
$sort_column = $sorting[0];
$sort_direction = $sorting[1];


$rows = fetch_items_from_db($sort_column, $sort_direction, $skip);

rows_to_html($rows);


include_once 'footer.php';
