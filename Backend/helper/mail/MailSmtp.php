<?php
namespace helper\mail;
use Nette\Mail\SmtpMailer;
use Nette\Mail\Message;

class MailSmtp
{
    private $mailer = null;
    private $username = null;
    private $from = null;
    private $senderAlias = null;

    public function __construct($config){
        $server = @$config['server'];
        if(empty($server)) throw new \Exception('Nedd Smtp Server Address');
        $port = @$config['port'];
        if(empty($port)) throw new \Exception('Nedd Smtp Server Port');
        $this->username = $username = @$config['username'];
        if(empty($username)) throw new \Exception('Nedd Username');
        $this->from = $from = @$config['from'];
        if(empty($from)) throw new \Exception('Nedd From');
        $password = @$config['password'];
        if(empty($password)) throw new \Exception('Nedd Password');
        $secure = @$config['secure'];
        $timeout = @$config['timeout']?:20;
        $persistent = @$config['persistent']?:false;
        $context = @$config['context']?:null;
        $this->senderAlias = @$config['senderAlias']?:$username;

        $this->mailer = new SmtpMailer([
            'host' => $server,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'secure' => $secure,
            'timeout' => $timeout,
            'persistent' => $persistent,
            'context' => $context
        ]);
    }

    public function send($receiver, $subject, $content, $contentType = 'text') {
        $mail = new Message;
        $mail->setFrom($this->senderAlias . ' <' . $this->from . '>')
            ->addTo($receiver)
            ->setSubject($subject);

        if($contentType === 'text'){
            $mail->setBody($content);
        }else{
            $mail->setHtmlBody($content);
        }

        $this->mailer->send($mail);
    }
}