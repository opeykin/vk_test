<?php
require_once 'utils/db_routines.php';


// all params checks
function get_form_params()
{
    $is_ok = true;
    $name = trim($_POST['name'] ?? '');
    $name_len = strlen($name);
    $name_error = '';

    if ($name_len > 255) {
        $name_error = 'Name is too long';
        $is_ok = false;
    }

    if ($name_len == 0) {
        $name_error = 'Name is required';
        $is_ok = false;
    }

    $price = $_POST['price'] ?? '';
    $price_error = '';

    if (strlen($price) == 0) {
        $price_error = 'Price is required';
        $is_ok = false;
    }

    // TODO: check too big int
    if (!is_numeric($price) || $price < 0 || strpos($price, '.') !== false) {
        $price_error = 'Should be positive integer';
        $is_ok = false;
    }

    //TODO: may be should check that img url is valid.

    return array(
        'id' => (int)($_POST['id'] ?? -1),
        'name' => $name,
        'name_error' => $name_error,
        'price' => $price,
        'price_error' => $price_error,
        'img' => $_POST['img'] ?? '',
        'description' => $_POST['description'] ?? '',
        'is_ok' => $is_ok
    );
}

function empty_form_params() {
    return array(
        'id' => -1,
        'name' => '',
        'name_error' => '',
        'price' => '',
        'price_error' => '',
        'img' => '',
        'description' => '',
        'is_ok' => true
    );
}