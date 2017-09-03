<?php
require_once 'utils/db_routines.php';


// all params checks
function get_add_params()
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