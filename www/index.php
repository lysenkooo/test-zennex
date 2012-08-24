<?php
/**
 * Input point of web application
 */
// uncomment these strings before debugging
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
// class autoloading
function __autoload($className) {
    $filePath = 'app/' . $className . '.php';
    if ( file_exists($filePath) ) {
        include $filePath;
    } else {
        throw new Exception(__FILE__ . ' : Class not found ' . $filePath);
    }
}
// main block
try {
    ob_start();
    $controller = new MainController();
    // $_GET['action'] contains controller method that should be called
    if ( isset($_GET['action']) ) {
        $action = strtolower($_GET['action']) . 'Action';
        if ( method_exists($controller, $action) ) {
            $controller->$action();
        }
        } else {
            // main action if other not defined
            $controller->showAction();
        }
} catch (Exception $e) {
    if (ini_get('display_errors')) {
        echo $e->getMessage();
    }
}