<?php
$config = [
    'global' => [
        'timezone' => 'Asia/Shanghai'
    ],
    'admin' => [
        'username' => 'root',
        'password' => '2dfa2f725cecc23403bbec1e401abddb', //md5(user+password)
    ],
    'DB' => [
        'host' => '127.0.0.1',
        'port' => '27017',
        'params' => [
            'compressors' => 'disabled',
            'gssapiServiceName' => 'mongodb'
        ],
        'db' => 'comment',
    ],
    'cache' => [
        'fileDir' => __DIR__ . '/runtime/cache',
        'partCleanCount' => 5,  //缓存文件部分清理时尝试清理的次数
        'cron' => true  //使用cron，请在crontab中定时运行根目录的cron.php
    ],
];
!defined('CONFIG')?define('CONFIG', $config):null;