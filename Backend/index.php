<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use \helper\LogHelper;

ini_set('date.timezone', @CONFIG['global']['timezone']);
if(@CONFIG['global']['cookieDomain']){
    //设置了cookieDomain时，修改session的cookie_domain
    ini_set('session.cookie_domain', CONFIG['global']['cookieDomain']);
}
if(@CONFIG['global']['cookieCors']){
    //关闭跨域是无需设置samesite。
    ini_set('session.cookie_samesite', 'None'); //部分浏览器可能无法支持，目前遇到9、10大版本的移动端小米浏览器无法支持，导致直接不设置cookies。
}
if(\helper\EnvHelper::isHttps()){
    //建议使用https, cookies_samesite None的设置下如果非https（secure为false)，目前chrome会提示警告⚠️
    ini_set('session.cookie_secure', true);
}
ini_set('session.name', '__simment');

//log记录
global $logger;
$logger = new LogHelper(@CONFIG['log']);

try {
    session_start();

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
    $logger->log($exception->getMessage());
    $logger->log($exception->getTraceAsString(), LogHelper::LEVEL_TRACE);
}
