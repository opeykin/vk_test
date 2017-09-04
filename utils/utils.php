<?php

function redirect($url, $delay = 0) {
    header("refresh:$delay;url=$url");
    exit();
}

