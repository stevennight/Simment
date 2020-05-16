<?php
namespace helper;

class StringHelper {
    public static function htmlFilter($str){
        $str = str_replace([
            '&',
            '<',
            '>',
            ' ',
            '\'',
            '"'
        ], [
            '&amp;',
            '&lt;',
            '&gt;',
            '&nbsp;',
            '&apos;',
            '&quot;'
        ], $str);
        return $str;
    }

    public static function cacheKeyFilter($str){
        return preg_replace('/[^0-9a-zA-Z_]/', '_', $str);
    }

    public static function emailNotifySubject($siteName){
        return '['.$siteName.']有人回复了您的评论';
    }

    /**
     * @param string $siteName 站点名称
     * @param string $siteId 站点id
     * @param string $url 评论页面所在地址
     * @param string $email 接收者邮箱
     * @param string $myComment 我的评论
     * @param string $replier 回复者的用户名
     * @param string $replyComment 回复内容
     * @return string
     */
    public static function emailNotifyBody($siteName, $siteId, $url, $email, $myComment, $replier, $replyComment){
        $year = date('Y');
        $serverName = $_SERVER['SERVER_NAME'];
        $serverPort = in_array($_SERVER['SERVER_PORT'], [80, 443])?null:$_SERVER['SERVER_PORT'];
        $requestScheme = $_SERVER['REQUEST_SCHEME'];
        $nonce = mt_rand(100000, 999999);
        $key = md5($siteId . $nonce . $email);
        $unsubscribeUrl = $requestScheme . '://' . $serverName . ($serverPort ? ':' . $serverPort:'')  .
            '/api.php?controller=comment&action=mailunsubscribe&siteId=' .
            $siteId . '&email=' . $email  . '&nonce=' . $nonce .'&key=' . $key;
        $content = <<<HTML
            <style>
                hr {
                    width: 95%
                }
                a {
                    color: #666666;
                }
            </style>
            <div style="background: #eee; text-align: center; padding: 10px;">
                <div style="font-size: 25px; font-weight: bolder">$siteName</div>
                <div>评论地址：<a href="$url">$url</a></div>
                <div>
                    <div style="font-weight: bold">您的留言</div>
                    <div>$myComment</div>
                </div>
                <hr />
                <div>
                    <div style="font-weight: bold">$replier</div>
                    <div>$replyComment</div>
                </div>
                <hr />
                <div style="font-size: 10px; color: #666">不是您的订阅？<a href="$unsubscribeUrl">点击这里取消订阅</a></div>
                <hr />
                <div>©$year $siteName</div>
            </div>
HTML;
        return $content;
    }
}