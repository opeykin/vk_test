<?php

function sanitized_post($param, $default = '')
{
    return filter_var ( trim($_POST[$param] ?? $default), FILTER_SANITIZE_STRING);
}

// all params checks
function get_form_params()
{
    $is_ok = true;
    $name = sanitized_post('name');
    $name_len = strlen($name);
    $name_error = '';

    if ($name_len > 64) {
        $name_error = 'Name is too long';
        $is_ok = false;
    }

    if ($name_len == 0) {
        $name_error = 'Name is required';
        $is_ok = false;
    }

    $price = sanitized_post('price');
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

    $img = sanitized_post('img');
    $img_error = '';

    if (strlen($img) > 1024) {
        $img_error = 'Maximum length is 1024 symbols.';
        $is_ok = false;
    }

    $description = sanitized_post('description');
    $description_error = '';
    if (strlen($description) > 2048) {
        $description_error = 'Maximum length is 2048 symbols.';
        $is_ok = false;
    }

    //TODO: may be should check that img url is valid.

    return array(
        'id' => (int)($_POST['id'] ?? -1),
        'name' => $name,
        'name_error' => $name_error,
        'price' => $price,
        'price_error' => $price_error,
        'img' => $img,
        'img_error' => $img_error,
        'description' => $description,
        'description_error' => $description_error,
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
        'img_error' => '',
        'description' => '',
        'description_error' => '',
        'is_ok' => true
    );
}
