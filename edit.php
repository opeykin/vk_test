<?php

require_once 'utils/utils.php';
require_once 'impl/form.php';
require_once 'model/data_access.php';


include 'templates/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id']))
        redirect('index.php');

    $id = (int)$_GET['id'];
    $params = db_fetch_item(db(), $id);

    if (!$params)
        redirect('index.php');

    include 'parts/edit_form.php';
} else {
    $params = get_form_params();
    if ($params['is_ok']) {
        model_update_item($params);
        redirect('item.php?id='.$params['id']);
    } else {
        include 'parts/edit_form.php';
    }
}


include 'templates/footer.php';