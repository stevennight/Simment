<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

//清理缓存文件
if(CONFIG['cache']['cron'])
    \helper\CacheHelper::cleanCache(new \Desarrolla2\Cache\File(CONFIG['cache']['fileDir']), 'all');