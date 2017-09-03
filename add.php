<?php



require_once 'utils/utils.php';
require_once 'impl/form.php';

include_once 'templates/header.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $params = array();
    include_once 'parts/add_form.php';
} else {
    $params = get_form_params();
    if ($params['is_ok']) {
        $db = db_connect();
        db_add_item($db, $params['name'], $params['price'], $params['description'], $params['img']);
        // TODO: die here if not added to db
        redirect('add_success.php');
    } else {
        include_once 'parts/add_form.php';
    }
}

include_once 'templates/footer.php';