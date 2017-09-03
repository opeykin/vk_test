<?php


include_once 'header.php';
require_once 'utils/utils.php';
require_once 'add_impl.php';

$fields = fields();
if ($fields) {
    $db = db_connect();
    db_add_item($db, $fields['name'], $fields['price'], $fields['description'], $fields['img']);
    // TODO: die here if not added to db
    redirect('add_success.php');
} else {
    include_once 'add_form.php';
}

include_once 'footer.php';