<?php

use Monolog\Logger as Logger;
use Monolog\Handler\RotatingFileHandler as RotatingFileHandler;
use Monolog\Processor\WebProcessor as WebProcessor;
use \Monolog\Processor\IntrospectionProcessor as IntrospectionProcessor;

class LogService extends \DbService {
    private $logger;
    
    public function __construct(\Web $w) {
        parent::__construct($w);
        
        $this->logger = new Logger('cmfive');
        $filename = ROOT_PATH . "/log/cmfive.log";
        $this->logger->pushHandler(new RotatingFileHandler($filename));
    }
    
    public function logger() { return $this->logger; }
    
    // Pass on missed calls to the logger (info, error, warning etc)
    public function __call($name, $arguments) {
        if (!empty($this->logger)) {
            if ($arguments[0] === "info" || stristr($name, "err") !== FALSE) {
                // Add the introspection processor if an error (Adds the line/file/class/method from which the log call originated)
                $this->logger->pushProcessor(new IntrospectionProcessor());
                $this->logger->pushProcessor(new WebProcessor());
            }
            $this->logger->$name($arguments[0], array("user" => $this->w->session('user_id')));
        }
    }
}
