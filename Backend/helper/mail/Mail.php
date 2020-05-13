<?php
namespace helper\mail;


class Mail
{
    const TYPES = [
        'smtp', 'ali'
    ];

    public $mailer;

    public function __construct($config)
    {
        $type = @$config['type'];
        if(!$type){
            throw new \Exception('Nedd Send Mail Type');
        }

        if(!in_array($type, self::TYPES)){
            throw new \Exception('Unknow Send Mail Type');
        }
        $classname = 'helper\mail\Mail' . ucfirst($type);
        $this->mailer = new $classname($config);
    }

    public function send($receiver, $subject, $content, $contentType = 'text'){
        $this->mailer->send($receiver, $subject, $content, $contentType);
    }
}