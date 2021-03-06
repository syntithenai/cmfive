<?php
function forgotpassword_GET(Web $w) {
	// $loginform = Html::form(array(
	// array("Reset Password","section"),
	// array("Your Login","text","login"),
	// ),$w->localUrl("auth/forgotpassword"),"POST","Reset");
	// $w->out($loginform);	
	$w->ctx("pagetitle", "Forgot Password");
}

function forgotpassword_POST(Web $w) {
	$login = $w->request("login");
	$user = $w->Auth->getUserForLogin($login);
	$responseString = "If this account exists then a password reset email has been just sent to the associated email address.";
	
	// For someone trying to gain access to a system, this is one of the
	// easiest ways to find a valid login, using the security through obscurity
	// principle, we dont tell them if it was a valid user or not, and we can log if they get it wrong
	// Note the previous message was "Could not find your account"
	if (!$user) {
		$w->msg($responseString,"/auth/login");
	}
	$user_contact = $user->getContact();
	
	// Generate password reset token 
	// We can use the cstrong to check that a cryptographically secure token was generated
	$token = sha1(openssl_random_pseudo_bytes(32, $cstrong));
	$user->password_reset_token = $token;
	$user->dt_password_reset_at = $user->time2Dt();
	$user->update();
	
	// Send email
	$message = "Hello {$user->getFullName()},\n<br/>";
	$message .= "Please go to this link to reset your password:<br/>\n";
	$message .= "<a href=\"http://".$_SERVER["HTTP_HOST"]."/auth/resetpassword?email={$user_contact->email}&token=$token\">http://"
			.$_SERVER["HTTP_HOST"]."/auth/resetpassword?email={$user_contact->email}&token=$token</a>\n<br/>You have 24 hours to reset your password.<br/><br/>";
	$message .= "Thank you,\n<br/>cmfive support";
	
	$result = $w->Mail->sendMail($user_contact->email, "support@tripleacs.com", "cmfive password reset", $message);
	if ($result !== 0) {
		$w->msg($responseString, "/auth/login");
	} else {
		$w->error("There was a problem sending an email, check your settings.", "/auth/login");
	}
	
	// explain
}