<?php


namespace helper\mail;


class MailAli
{
    const SERVERS = [
        'dm.aliyuncs.com',
        'dm.ap-southeast-1.aliyuncs.com',
        'dm.ap-southeast-2.aliyuncs.com'
    ];

    private $apikey = null;
    private $server = null;

    public function __construct($config)
    {
        if(!isset($config['server'])){
            throw new \Exception('Nedd Api Server Address');
        }
        if(!in_array($config['server'], self::SERVERS)){
            throw new \Exception('Invalid Api Server Address');
        }
        $this->server = $config['server'];

        if(!isset($config['apikey'])){
            throw new \Exception('Nedd Api Key');
        }
        $this->apikey = $config['apikey'];
    }

    public function send(){}

    private function encryptTokey($data){
        return base64_encode(hash_hmac("sha1", $data, $this->apikey, true));
    }
}