<?php

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/' :
        require __DIR__ . '/app/api/show_game.php';
        break;
    case '' :
        require __DIR__ . '/app/api/show_game.php';
        break;
    case '/login' :
        require __DIR__ . '/app/api/login.php';
        break;
    case '/ajax' :
        require __DIR__ . '/app/api/MainController.php';
        break;
    case '/test' :
        require __DIR__ . '/app/api/test.php';
        break;
    case '/chat' :
        require __DIR__ . '/app/api/chat.php';
        break;
    default:
//        http_response_code(404);
        require __DIR__ . '/app/api/404.php';
        break;
}
