<?php



require_once 'utils/utils.php';
require_once 'impl/add_impl.php';

include_once 'templates/header.php';

$params = get_add_params();
if ($params) {
    $db = db_connect();
    db_add_item($db, $params['name'], $params['price'], $params['description'], $params['img']);
    // TODO: die here if not added to db
    redirect('add_success.php');
} else {
    include_once 'parts/add_form.php';
}

include_once 'templates/footer.php';