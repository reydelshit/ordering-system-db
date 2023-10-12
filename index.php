<?php

$request_uri = $_SERVER['REQUEST_URI'];

$uri_parts = explode('/', trim($request_uri, '/'));


$controller = isset($uri_parts[0]) ? $uri_parts[0] : 'default';
$action = isset($uri_parts[1]) ? $uri_parts[1] : 'index';

$controller_file = "controllers/{$controller}.php";

if (file_exists($controller_file)) {
    require_once($controller_file);


    $action_function = "{$controller}_{$action}";

    if (function_exists($action_function)) {
        call_user_func($action_function);
    } else {

        header('HTTP/1.0 404 Not Found');
        echo '404 Not Found';
    }
} else {

    header('HTTP/1.0 404 Not Found');
    echo '404 Not Found';
}
