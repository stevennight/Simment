<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

try {
    session_start();
    ini_set('date.timezone', @CONFIG['global']['timezone']);

    $controller = isset($_REQUEST['controller'])?ucfirst($_REQUEST['controller']):null;
    if(!$controller){
        throw new Exception('unknown controller.');
    }
    $action = @$_REQUEST['action'];
    if(!$action){
        throw new Exception('unknown action.');
    }

    $controllerClass = 'controller\\' . $controller . 'Controller';
    /** @var controller\Controller $controllerObj */
    $controllerObj = new $controllerClass();
    if(!method_exists($controllerObj, $action.'Action')){
        throw new Exception('action ' . $action . ' does not exist.');
    }
    $controllerObj->action = $action;
    $res = $controllerObj->beforeAction();
    if($res){
        call_user_func([$controllerObj, $action.'Action']);
    }
    $controllerObj->afterAction();
} catch (\Exception $exception){
    echo json_encode([
        'code' => -1,
        'msg' => '内部错误'
    ]);
}
