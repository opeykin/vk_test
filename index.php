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
$handle = db_connect('localhost', 'dev', 'dev', 'vk_test2') or die('Can\'t connect');

$result = mysqli_query($handle, "SELECT id, img, name, price, description  FROM items ORDER BY price LIMIT $PAGE_SIZE");
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
