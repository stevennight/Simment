<?php
namespace controller;
use MongoDB;
use helper\CacheHelper;
use Desarrolla2\Cache\File as Cache;

class Controller
{
    public $conn = null;
    public $db = null;
    public $cache = null;
    public $action = null;
    public $needLogin = [];

    public $cacheDir = CONFIG['cache']['fileDir'];

    public function __construct()
    {
        //DB连接
        $connStr = 'mongodb://' . CONFIG['DB']['host'] . ':' . CONFIG['DB']['port'];
        $this->conn = new MongoDB\Client($connStr, CONFIG['DB']['params']);
        $this->db = $this->conn->selectDatabase(CONFIG['DB']['db']);

        //使用缓存时请务必要设置过期时间，否则无法自动清除。（过期清除）
        $this->cache = new Cache($this->cacheDir);
        CacheHelper::cleanCache($this->cache, 'part');  //每次访问清理一次。
    }

    public function response($data, $type = 'json'){
        switch ($type){
            case 'json':
                echo json_encode($data);
                break;
            case 'plain':
                echo $data;
                break;
            default:
                throw new \Exception('unknown return type.');
        }
        return;
    }

    public function beforeAction(){
        //login_check
        if(!empty($this->needLogin) && in_array($this->action, $this->needLogin) && !isset($_SESSION['user'])){
            $this->response([
                'code' => 401,
                'msg' => '需要登录'
            ]);
            return false;
        }
        return true;
    }
    public function afterAction(){
    }

    public function userLogined(){
        return @$_SESSION['user'];
    }
}