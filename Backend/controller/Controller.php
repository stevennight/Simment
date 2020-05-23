<?php
namespace controller;
use helper\mail\Mail;
use MongoDB;
use helper\CacheHelper;
use Desarrolla2\Cache\File as Cache;

class Controller
{
    public $conn = null;
    public $db = null;
    public $mailer = null;
    public $cache = null;
    public $action = null;
    public $needLogin = [];

    public $cacheDir = CONFIG['cache']['fileDir'];

    public function __construct()
    {
        // 邮件发送
        $this->mailer = new Mail(CONFIG['mail']);

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

    public function setHeader(){
        $site = @$_GET['site']; //请求时需要在get(url param)中放入site参数，包括submit等POST请求action。
        $origin = @$_SERVER['HTTP_REFERER'];

        if(!$origin) return true;   //直接访问，暂时定为可以访问。

        if(@CONFIG['global']['cors']){
            header("Access-Control-Allow-Credentials: true");   //允许带cookies
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");   //允许methods
            //has been blocked by CORS policy: Request header field content-type is not allowed by Access-Control-Allow-Headers in preflight response. ↓
            header("Access-Control-Allow-Headers: DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization");   //允许header
            //跨域origin
            if(!preg_match('/^((https|http):\/\/(.*?))+?(\/|$)/', $origin, $matches)){
                //referer不合法
                return false;
            }
            header("Access-Control-Allow-Origin: " . $matches[1]);
        }else{
            preg_match('/^((https|http):\/\/(.*?))+?(\/|$)/', $origin, $matches);
        }

        //来源地址是当前服务器地址时，
        $serverName = explode(':', $matches[3]);
        $server = $_SERVER['SERVER_NAME'];
        if(!in_array($_SERVER['SERVER_PORT'], [80, 443])){
            $server .= $_SERVER['SERVER_PORT']; //非80，443则加上端口。不强制区分http, https，因为如果使用CDN，客户端访问和回源有可能不在同一个端口（协议）。其它特殊情况暂不考虑。
        }
        //来源地址非当前服务器地址，并且不是指定的site地址时，返回false。
        if($matches[3] !== $site && $serverName[0] !== $server){
            return false;
        }
        return true;
    }
}