<?php


function html_print_page_link($url, $order, $page, $text, $selected = false) {
    $url_with_params = "$url?order=$order&page=$page";
    $class = $selected ? '' : 'no_underline';
    echo "<a class='$class' href='$url_with_params'>$text</a>";
}

function html_print_page_switcher($cur_page, $page_count, $order)
{
    echo '<div id="page_navigation">';

    $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    if ($cur_page > 0) {
        html_print_page_link($url, $order, 0, '0');
    }

    if ($cur_page > 1) {
        html_print_page_link($url, $order, $cur_page - 1, '...');
    }

    html_print_page_link($url, $order, $cur_page, $cur_page, true);

    if ($cur_page < $page_count - 2) {
        html_print_page_link($url, $order, $cur_page + 1, '...');
    }

    if ($cur_page < $page_count - 1) {
        html_print_page_link($url, $order, $page_count - 1, $page_count - 1);
    }

    echo '</div>';
}

function get_sort_field($order)
{
    if ($order < 2)
        return 'price';

    return 'id';
}

function get_sort_direction($order) {
    if ($order == 0 || $order == 2)
        return 'ASC';

    return 'DESC';
}

function get_params()
{
    $page = $_GET['page'] ?? 0;
    if (!is_numeric($page) || $page < 0) {
        error_log("Illegal GET param. page is not a positive number: $page");
        return null;
    }

    $order = $_GET['order'] ?? 0;
    if (!is_numeric($order) || $order < 0 || $order > 3) {
        error_log("Illegal GET param. order has unknown value: $order");
        return null;
    }

    return array(
        'page' => (int)$page,
        'order' =>(int) $order,
        'sort_field' => get_sort_field($order),
        'sort_direction' => get_sort_direction($order)
    );
}

