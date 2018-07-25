<?php

global $o_access_object;
require_once(dirname(__FILE__).'/../../resources/db_query.php');
require_once(dirname(__FILE__).'/../../resources/globals.php');
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
require_once(dirname(__FILE__).'/campaign_funcs.php');
require_once(dirname(__FILE__)."/../login/access_object.php");
require_once(dirname(__FILE__)."/../../objects/command.php");

class user_ajax {
	public static function check_campaign_name() {
		$s_name = get_post_var('campaign_name');
		$s_name_status = campaign_funcs::campaign_name_status($s_name);
		$a_parts = array("element_find_by"=>"#campaign_name_errors", "class"=>"hidden");
		switch ($s_name_status) {
		case 'blank':
			return json_encode(array(
				new command("print failure", "The campaign name is blank"),
				new command("remove class", $a_parts)));
		case 'taken':
			return json_encode(array(
				new command("print failure", "That campaign name is already taken."),
				new command("remove class", $a_parts)));
		case 'available':
			return json_encode(array(
				new command("print success", "That campaign name is available."),
				new command("remove class", $a_parts)));
		}
	}

	public static function create_campaign() {
		global $maindb;
		global $fqdn;

		$s_name = trim(get_post_var('campaign_name'));
		$b_public = intval(trim(get_post_var('public')));
		$b_passProtected = intval(trim(get_post_var('passProtected')));
		$s_pass = trim(get_post_var('pass'));
		
		// try creating the campaign
		$s_feedback = "";
		if (!campaign_funcs::create_campaign($s_name, $b_public, $b_passProtected, $s_pass, $s_feedback))
			return json_encode(array(
				new command("print failure", $s_feedback)));

		return json_encode(array(
			new command("print success", "Success! Campaign created!"),
			new command("reload page", "1000")));
	}

	/**
	 * Used to send a password reset link to an user.
	 * Only needs one valid username/email.
	 * @param  string $s_username The username of the user to reset the password for
	 *     Uses $_GET['username'] if not set
	 * @param  string $s_email    The email of the user to reset the password for
	 *     Uses $_GET['email'] if not set
	 * @return string             An array with either TRUE/FALSE, and one of 'A verification email has been sent to [email]', 'Please provide a username or email address', 'That username can't be found', 'That email can't be found', 'That username/email combination can't be found', 'Too many attempts have been made to reset the password. Please try again in [minutes] minutes.'
	 */
	public static function forgot_password($s_username = "", $s_email = "") {

		global $maindb;
		global $o_access_object;
		global $fqdn;

		// get the username or email, and the access object
		if ($s_username == "")
				$s_username = trim(get_post_var('username'));
		if ($s_email == "")
				$s_email = trim(get_post_var('email'));

		// determine which of the credentials were provided
		$b_username_provided = $s_username != "";
		$b_email_provided = $s_email != "";
		if (!$b_username_provided && !$b_email_provided)
				return array(FALSE, "Please provide a username or email address");

		// verify that the username and/or email exists
		$a_users = db_query("SELECT `username`,`email` FROM `[maindb]`.`users` WHERE `username`='[username]'", array('maindb'=>$maindb, 'username'=>$s_username));
		$b_user_exists = count($a_users) > 0;
		if ($b_user_exists) {
				$s_email = $a_users[0]['email'];
		} else {
				$a_users = db_query("SELECT `username`,`email` FROM `[maindb]`.`users` WHERE `email`='[email]'", array('maindb'=>$maindb, 'email'=>$s_email));
				$b_user_exists = count($a_users) > 0;
				if ($b_user_exists)
						$s_username = $a_users[0]['username'];
		}

		// check if there have been too many password reset attempts recently
		if ($b_user_exists) {
				$i_seconds_to_next_trial = $o_access_object->check_reset_access($s_username);
		} else {
				$i_seconds_to_next_trial = $o_access_object->check_reset_access("");
		}
		if ($i_seconds_to_next_trial > 0) {
				$i_minutes = (int)($i_seconds_to_next_trial / 60);
				return array(FALSE, "Too many attempts have been made to reset the password. Please try again in {$i_minutes} minutes.");
		}

		// return false if the email/username wasn't found
		if (!$b_user_exists) {
				if (!$b_username_provided)
						return array(FALSE, "That email can't be found");
				if (!$b_email_provided)
						return array(FALSE, "That username can't be found");
				return array(FALSE, "That username/email combination can't be found");
		}

		// send the verification email
		$s_reset_key = $o_access_object->get_reset_key($s_username, TRUE);
		$i_reset_time = $o_access_object->get_reset_expiration($s_username, TRUE);
		$i_reset_minutes = (int)(($i_reset_time - strtotime('now')) / 60);
		//$s_body = "A password reset attempt has been made with {$fqdn} for the user {$s_username}, registered with this email address. If you did not request this reset please ignore this email.\n\nYou have {$i_reset_minutes} minutes to click the link below to reset your password. Ignore this email if you do not want your password reset.\nhttps://{$fqdn}/pages/users/reset_password.php?username={$s_username}&key={$s_reset_key}";
		$s_body = "A password reset attempt has been made with {$fqdn} for the user {$s_username}, registered with this email address. If you did not request this reset please ignore this email.\n\nYou have {$i_reset_minutes} minutes to click the link below to reset your password. Ignore this email if you do not want your password reset.\nhttp://{$fqdn}/pages/users/reset_password.php?username={$s_username}&key={$s_reset_key}";
		//error_log($s_body);
		mail($s_email, "Request to Reset Beanweb Password", $s_body, "From: noreply@{$fqdn}");
		$a_email_parts = explode("@", $s_email, 2);
		$s_email_trimmed = $a_email_parts[1];
		return array(TRUE, "A verification email has been sent to ****@{$s_email_trimmed}");
	}

	public static function forgot_password_ajax() {
		$s_username = trim($_POST['username']);
		$s_email = trim($_POST['email']);
		$a_retval = self::forgot_password($s_username, $s_email);
		if ($a_retval[0]) {
			return json_encode(array(
				new command("print success", $a_retval[1])));
		} else {
			return json_encode(array(
				new command("print failure", $a_retval[1])));
		}
	}

	public static function reset_password_ajax() {
		$s_username = trim($_POST['username']);
		$s_key = trim($_POST['key']);
		$s_password = trim($_POST['password']);
		$a_retval = user_funcs::reset_password($s_username, $s_key, $s_password);
		if ($a_retval[0]) {
			return json_encode(array(
				new command("print success", $a_retval[1])));
		} else {
			return json_encode(array(
				new command("print failure", $a_retval[1])));
		}
	}
}

if (!$global_user) {
	logout_session();
}
if (isset($_POST['username']) && !isset($_POST['command']))
		$_POST['command'] = 'check_campaign_name';
if (isset($_POST['command'])) {
		$o_ajax = new user_ajax();
		$s_command = $_POST['command'];
		if (method_exists($o_ajax, $s_command)) {
				echo user_ajax::$s_command();
		} else {
				echo json_encode(array(
					'bad command'));
		}
}

?>