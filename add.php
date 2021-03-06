<?php


require_once 'utils/utils.php';
require_once 'impl/form.php';
require_once 'model/data_access.php';

include_once 'templates/header.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $params = empty_form_params();
    include_once 'parts/add_form.php';
} else {

    $params = get_form_params();
    if ($params['is_ok']) {

        $id = model_add_item($params);
        if ($id !== false)
            redirect("item.php?id=$id");
        else
            redirect('index.php');

    } else {
        include_once 'parts/add_form.php';
    }
}

include_once 'templates/footer.php';