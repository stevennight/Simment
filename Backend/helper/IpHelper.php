<?php
namespace helper;

class IpHelper
{
    public static function getUserIp(){
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && filter_var($_SERVER["HTTP_CF_CONNECTING_IP"], FILTER_VALIDATE_IP)) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        if (isset($_SERVER["HTTP_ALI-CDN-REAL-IP"]) && filter_var($_SERVER["HTTP_ALI-CDN-REAL-IP"], FILTER_VALIDATE_IP)) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_ALI-CDN-REAL-IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_ALI-CDN-REAL-IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP)) { $ip = $client; }
        elseif(filter_var($forward, FILTER_VALIDATE_IP)) { $ip = $forward; }
        else { $ip = $remote; }

        return $ip;
    }
}