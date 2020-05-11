<?php
namespace helper;
use Desarrolla2\Cache\File as Cache;

class CacheHelper
{
    public static $cacheDir = CONFIG['cache']['fileDir'];
    public static $partCleanCount = CONFIG['cache']['partCleanCount'];  //部分清理时尝试清理的次数。

    public static function cleanCache(Cache $cache, $mode = 'all'){
        //清除过期的缓存文件。
        $files = [];
        $handler = opendir(static::$cacheDir);
        while (($filename = readdir($handler)) !== false) {//务必使用!==，防止目录下出现类似文件名“0”等情况
            if (is_file(static::$cacheDir . '/' .$filename)) {
                $key = str_replace('.php.cache', '', $filename);
                if($mode === 'all'){
                    //直接清理，清理所有遍历到并且过期的缓存文件。
                    $cache->get($key);    //如果key是过期的，读取时会自动删除。
                }else{
                    $files[] = $key ;
                }
            }
        }
        closedir($handler);

        if($mode === 'part'){
            //清除部分
            $max = count($files) - 1;
            if($max < 0) return;
            for($i = 0; $i < static::$partCleanCount; $i++){
                $rand = mt_rand(0, $max);   //随机选择文件删除。
                $key = $files[$rand];
                $cache->get($key);    //如果key是过期的，读取时会自动删除。
            }
        }
    }
}