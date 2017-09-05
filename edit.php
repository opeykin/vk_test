<?php

require_once 'utils/db_routines.php';
require_once 'utils/utils.php';
require_once 'impl/form.php';
require_once 'utils/model.php';


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
        // TODO: die if failed
        redirect('edit_success.php');
    } else {
        include 'parts/edit_form.php';
    }
}


include 'templates/footer.php';