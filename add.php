<?php


include_once 'header.php';
require_once 'utils/db_routines.php';
require_once 'utils/Constants.php';

// all params checks
function fields()
{
    if (empty($_POST['name']) || empty($_POST['price'])) {
        return false;
    }

    $name = trim($_POST['name']);
    $name_len = strlen($name);
    if ($name_len > 255 || $name_len == 0) {
        return false;
    }

    $price = $_POST['price'];
    // TODO: check too big int
    if (!is_numeric($price) || $price < 0 || strpos($price, '.') !== false) {
        return false;
    }

    //TODO: may be should check that img url is valid.

    return array(
        'name' => $name,
        'price' => $price,
        'img' => $_POST['img'] ?? '',
        'description' => $_POST['description'] ?? ''
    );
}

$fields = fields();
if ($fields) {
    $db_config = parse_ini_file(Constants::DB_CONFIG_PATH);
    $handle = db_connect($db_config);
    db_add_item($handle, $fields['name'], $fields['price'], $fields['description'], $fields['img']);
    echo "SUCCESS";
    header('refresh:5;url=index.php');
} else {
    include_once 'add_form.php';
}

include_once 'footer.php';