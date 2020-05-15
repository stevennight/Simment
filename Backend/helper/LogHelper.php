<?php
namespace helper;

class LogHelper
{
    const LEVEL_INFO = 1;
    const LEVEL_WARN = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_TRACE = 4;
    const LEVEL_DEBUG = 5;
    const LEVEL = [
        self::LEVEL_INFO => 'info',
        self::LEVEL_WARN => 'warn',
        self::LEVEL_ERROR => 'error',
        self::LEVEL_TRACE => 'trace',
        self::LEVEL_DEBUG => 'debug'
    ];

    private $logFile = null;
    private $switch = false;

    public function __construct($config)
    {
        $switch = @$config['switch'];
        if(!$switch){
            return;
        }
        $this->switch = $switch;

        $logFile = @$config['file'];
        if(!$logFile){
            throw new \Exception('log file not set');
        }
        $this->logFile = $logFile;
    }

    public function log($message, $level = self::LEVEL_ERROR){
        if(!$this->switch) return;
        $levelStr = self::LEVEL[$level];
        $datetime = date('Y-m-d H:i:s');
        file_put_contents(
            $this->logFile,
            "[$levelStr][$datetime]$message" . PHP_EOL,
            FILE_APPEND
        );
    }
}