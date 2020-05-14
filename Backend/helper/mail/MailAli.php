<?php


namespace helper\mail;


class MailAli
{
    const SERVERS = [
        'dm.aliyuncs.com',
        'dm.ap-southeast-1.aliyuncs.com',
        'dm.ap-southeast-2.aliyuncs.com'
    ];
    const VERSIONS = [
        'dm.aliyuncs.com' => '2015-11-23',
        'dm.ap-southeast-1.aliyuncs.com' => '2017-06-22',
        'dm.ap-southeast-2.aliyuncs.com' => '2017-06-22'
    ];

    private $accessKeyId = null;
    private $accessKeySecret = null;
    private $server = null;
    private $accountName = null;
    private $addressType = null;
    private $fromAlias = null;

    public function __construct($config)
    {
        if(!isset($config['server'])){
            throw new \Exception('Nedd Api Server Address');
        }
        if(!in_array($config['server'], self::SERVERS)){
            throw new \Exception('Invalid Api Server Address');
        }
        $this->server = $config['server'];

        if(!isset($config['accountName'])){
            throw new \Exception('Nedd Account Name');
        }
        $this->accountName = $config['accountName'];

        if(!isset($config['addressType'])){
            throw new \Exception('Nedd Address Type');
        }
        $this->addressType = $config['addressType'];

        $this->fromAlias = $config['fromAlias']?:null;

        if(!isset($config['accessKeyId'])){
            throw new \Exception('Nedd AccessKey Id');
        }
        $this->accessKeyId = $config['accessKeyId'];

        if(!isset($config['accessKeySecret'])){
            throw new \Exception('Nedd AccessKey Secret');
        }
        $this->accessKeySecret = $config['accessKeySecret'];

    }

    public function send($receiver, $subject, $content, $contentType = 'text'){
        date_default_timezone_set('UTC');
        $timestamp = date('Y-m-d\TH:i:s\Z');    //UTC时间
        date_default_timezone_set(ini_get('date.timezone'));
        $data = [
            'Format' => 'JSON',
            'Version' => self::VERSIONS[$this->server],
            'AccessKeyId' => $this->accessKeyId,
            'SignatureMethod' => 'HMAC-SHA1',
            'Timestamp' => $timestamp,  //YYYY-MM-DDThh:mm:ssZ
            'SignatureVersion' => '1.0',
            'SignatureNonce' => mt_rand(100000, 999999),
            'AccountName' => $this->accountName,
            'AddressType' => $this->addressType,
            'ReplyToAddress' => 'true',
            'Subject' => $subject,
            'ToAddress' => $receiver,
            'Action' => 'SingleSendMail',
            'ClickTrace' => 0,
        ];
        $this->fromAlias?$data['FromAlias'] = $this->fromAlias:null;
        if($contentType === 'text'){
            $data['TextBody'] = $content;
        }else{
            $data['HtmlBody'] = $content;
        }

        //签名
        $this->paramSort($data);
        foreach ($data as $k => $v){
            $data[$k] = $this->encode($v);
        }
        $signature = $this->encryptTokey($data, 'GET');
        $data['Signature'] = $signature;
        $this->paramSort($data);

        $params = [];
        foreach($data as $k => $v){
            $params[] = $k . '=' . $v;
        }
        $paramsStr = implode('&', $params);

        $return = $this->geturl('https://'.$this->server.'/?'.$paramsStr);
        if(isset($return['Code'])){
            return false;
        }
        return true;
    }

    private function paramSort(&$data){
        ksort($data);
    }

    private function encode($v){
        $v = urlencode($v);
        $v = str_replace('+', '%20', $v);
        $v = str_replace('*', '%2A', $v);
        $v = str_replace('%7E', '~', $v);
//        $v = preg_replace('/\+/', '%20', $v);
//        $v = preg_replace('/\*/', '%2A', $v);
//        $v = preg_replace('/%7E/', '~', $v);
        return $v;
    }

    private function encryptTokey($data, $httpMethod){
        $dataStrArr = [];
        foreach($data as $k => $v){
            $dataStrArr[] = $k . '=' . $v;
        }
        $dataStr = implode('&', $dataStrArr);

        $stringToSign = $httpMethod . "&" .
            $this->encode("/") . "&" .
            $this->encode($dataStr);
        return base64_encode(hash_hmac("sha1", $stringToSign, $this->accessKeySecret . '&', true));
    }

    function geturl($url){
        $headerArray =array("Content-type:application/json;","Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
    }
}