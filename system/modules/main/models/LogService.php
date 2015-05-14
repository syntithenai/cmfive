<?php

use Monolog\Logger as Logger;
use Monolog\Handler\RotatingFileHandler as RotatingFileHandler;

class LogService extends DbService {
    private $logger;
    
    public function __construct(\Web $w) {
        parent::__construct($w);
        
        if (class_exists("Logger")) {
            $this->logger = new Logger('cmfive');
        
            $filename = ROOT_PATH . "/log/cmfive.log";
            $this->logger->pushHandler(new RotatingFileHandler($filename));
        }
    }
    
    public function logger() { return $this->logger; }
    
    // Pass on missed calls to the logger (info, error, warning etc)
    public function __call($name, $arguments) {
        if (!empty($this->logger)) {
            $this->logger->$name($arguments[0]);
        }
    }
}