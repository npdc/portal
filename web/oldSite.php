<?php

#catch url's from the old website and redirect
if(array_key_exists('page', $_GET)) {
    if(
        strpos($_GET['page'], '..') !== false
        || strpos($_GET['page'], '/') !== false
    ){
        die('ILLEGAL URL');
    }
    //header("HTTP/1.1 301 Moved Permanently"); 
    switch (strtolower($_GET['page'])) {
        case 'project_list':
            $url = 'project';
            break;
        default:
            $url = strtolower($_GET['page']);
    }
    header('Location: ' . BASE_URL . '/' . $url);
    die();
}
