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
$username = "dev";
$pwd = "dev";
$hostname = "localhost";

$db = mysqli_connect($hostname, $username, $pwd) or die("Can't connect");
mysqli_select_db($db, "vk_test") or die("Cant' select db");

$result = mysqli_query($db, "SELECT id, price FROM items ORDER BY price LIMIT 10");
while ($row = mysqli_fetch_row($result)) {
    echo $row[0].": ".$row[1]."<br>";
}

?>

<!--<script src="https://code.jquery.com/jquery-3.2.1.min.js"-->
<!--integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>-->
<!--<script>window.jQuery || document.write('<script src="js/vendor/jquery-3.2.1.min.js"><\/script>')</script>-->
<!--<script src="js/plugins.js"></script>-->
<script src="js/main.js"></script>

</body>
</html>
