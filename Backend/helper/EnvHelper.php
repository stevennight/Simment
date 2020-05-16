<?php
namespace helper;

class EnvHelper
{
    public static function isHttps(){
        if(@$_SERVER['HTTP_X_CLIENT_SCHEME'] === 'https'){
            return true;
        }
        if(@$_SERVER['REQUEST_SCHEME'] === 'https'){
            return true;
        }
        return false;
    }
}