<?php
session_start();
require_once 'config/app.php';
require_once 'config/database.php';

// Simple router
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Sanitize
$page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);

$controller_file = 'controllers/' . ucfirst($page) . 'Controller.php';

if (file_exists($controller_file)) {
    require_once $controller_file;
    $controllerClass = ucfirst($page) . 'Controller';
    $controller = new $controllerClass();
    $controller->index();
} else {
    // Default to home
    require_once 'controllers/HomeController.php';
    $controller = new HomeController();
    $controller->index();
}
