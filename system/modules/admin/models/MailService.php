<?php

class MailService extends DbService {
	
	private $transport;
	
	public function __construct(Web $w) {
		parent::__construct($w);
		$this->initTransport();
	}
	
    /**
     * Sends an email using config array from /config.php and the swiftmailer lib
     * for transport. 
     * 
     * @global Array $EMAIL_CONFIG
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $body
     * @param string $cc (optional)
     * @param string $bcc (optional)
     * @param Array $attachments (optional)
     * @return int
     */
    public function sendMail($to, $from, $subject, $body, $cc = null, $bcc = null, $attachments = array()) {
        global $EMAIL_CONFIG;
        
        if ($this->transport === NULL) {
        	$this->w->logError("Could not send mail to {$to} from {$from} about {$subject} no email transport defined!");
        	return;
        }

        $mailer = Swift_Mailer::newInstance($this->transport);

        // Create message
        $message = Swift_Message::newInstance($subject)
                        ->setFrom($from)->setTo($to)
                        ->setBody($body)->addPart($body, 'text/html');
        if (!empty($cc)) {
            $message->setCc($cc);
        }
        if (!empty($bcc)) {
            $message->setBcc($bcc);
        }

        // Add attachments
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
            	if (!empty($attachment)) {
                	$message->attach(Swift_Attachment::fromPath($attachment));
            	}
            }
        }

        // Send
        $result = $mailer->send($message, $failures);
        if (!$result) {
            
        }
        return $result;
    }

    private function initTransport() {
    	global $EMAIL_CONFIG;
        $layer = $EMAIL_CONFIG['layer'];
        switch ($layer) {
        	case "smtp": 
        		$this->transport = Swift_SmtpTransport::newInstance($EMAIL_CONFIG["host"], $EMAIL_CONFIG["port"], 'ssl')
                ->setUsername($EMAIL_CONFIG["username"])
                ->setPassword($EMAIL_CONFIG["password"]);
                break;
        	case "sendmail":
        		if (!empty($EMAIL_CONFIG["command"])) {
        			$this->transport = Swift_SendmailTransport::newInstance($EMAIL_CONFIG["command"]);
        		} else {
        			$this->transport = Swift_SendmailTransport::newInstance();
        		}
        		break;
        }
    }

}
