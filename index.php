<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="manifest" href="site.webmanifest">
    <link rel="apple-touch-icon" href="icon.png">

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<?php

function get_order()
{
    if (!isset($_GET['order']))
        return 0;

    if (is_numeric($_GET['order'])) {
        return (int)$_GET['order'];
    }

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



function db_connect($host = '', $user = '', $password = '', $database = '')
{
    $handle = mysqli_connect($host, $user, $password, $database);
    if (!$handle) {
        $msg = 'MySQL connection failed.';
        $data = "host=$host user=$user db=$database";
        error_log($msg . ' ' . mysqli_connect_error() . ' ' . $data);
        return false;
    }

    return $handle;
}

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


$PAGE_SIZE = 50;


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

$skip = get_page() * $PAGE_SIZE;
$sorting = get_sorting();
$sort_column = $sorting[0];
$sort_direction = $sorting[1];

$handle = db_connect('localhost', 'dev', 'dev', 'vk_test2') or die('Can\'t connect');

$result = mysqli_query($handle, "SELECT id, img, name, price, description  FROM items ORDER BY $sort_column $sort_direction LIMIT $skip, $PAGE_SIZE");
$rows = mysqli_fetch_all($result, MYSQLI_NUM);
rows_to_html($rows);
mysqli_close($handle);

?>

<!--<script src="https://code.jquery.com/jquery-3.2.1.min.js"-->
<!--integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>-->
<!--<script>window.jQuery || document.write('<script src="js/vendor/jquery-3.2.1.min.js"><\/script>')</script>-->
<!--<script src="js/plugins.js"></script>-->
<script src="js/main.js"></script>

</body>
</html>
