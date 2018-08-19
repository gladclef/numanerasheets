<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");

// checks the session for a logged in user
// @retval the user object or null
function get_logged_in() {
	global $global_user;

	if (!isset($_SESSION['loggedin']) || !isset($_SESSION['username']) || !isset($_SESSION['last_activity']) || !isset($_SESSION['crypt_password'])) {
			return NULL;
	}
	if (get_session_expired()) {
			$_POST['session_expired'] = 'Your session has expired';
			return NULL;
	}
	updateSessionTime();
	$o_user = new user($_SESSION['username'], NULL, urldecode($_SESSION['crypt_password']));
	if ($o_user->exists_in_db()) {
			$global_user = $o_user;
			return $o_user;
	}
	return NULL;
}

function updateSessionTime() {
	$_SESSION['last_activity'] = time();
}

function get_session_expired($b_isRecentActivity = FALSE) {
	$time_before_timeout = 60 * 12; // minutes
	if (isset($_SESSION['time_before_page_expires']))
			if ((int)$_SESSION['time_before_page_expires'] != 0)
					$time_before_timeout = (int)$_SESSION['time_before_page_expires'];
	if ($time_before_timeout < 0) {
			if ($b_isRecentActivity) updateSessionTime();
			return FALSE;
	}
	if ((time()-$_SESSION['last_activity'])/60 > $time_before_timeout) {
			return TRUE;
	} else {
			if ($b_isRecentActivity) updateSessionTime();
			return FALSE;
	}
}

function draw_disclaimer_bar() {

	ob_start();
	?>
	<div class="disclaimer"><div
		class="short">
			Numenera and its logo are trademarks of Monte Cook Games, LLC in the U.S.A. and other countries.
			All Monte Cook Games characters and character names, and the distinctive likenesses thereof, are trademarks of Monte Cook Games, LLC.
			Content derived from Monte Cook Games publications is © 2013-2017 Monte Cook Games, LLC.
		</div><div
		class="long">
			The Monte Cook Games logo, Numenera, and the Numenera logo are trademarks of Monte Cook Games, LLC in the U.S.A. and other countries.
			All Monte Cook Games characters and character names, and the distinctive likenesses thereof, are trademarks of Monte Cook Games, LLC.
			Content on this site or associated files derived from Monte Cook Games publications is © 2013-2017 Monte Cook Games, LLC.
			Monte Cook Games permits web sites and similar fan-created publications for their games, subject to the policy given at <a href="http://www.montecookgames.com/fan-use-policy/">http://www.montecookgames.com/fan-use-policy/</a>.
			The contents of this site are for personal, non-commercial use only.
			Monte Cook Games is not responsible for this site or any of the content, that did not originate directly from Monte Cook Games, on or in it.
			Use of Monte Cook Games’s trademarks and copyrighted materials anywhere on this site and its associated files should not be construed as a challenge to those trademarks or copyrights.
			Materials on this site may not be reproduced or distributed except with the permission of the site owner and in compliance with Monte Cook Games policy given at <a href="http://www.montecookgames.com/fan-use-policy/">http://www.montecookgames.com/fan-use-policy/</a>.
		</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function() {
			var jdisclaimer = $(".disclaimer");
			var jshort = jdisclaimer.find(".short");
			var jlong = jdisclaimer.find(".long");
			var jlink = jdisclaimer.find("a");
			var longVisible = false;

			jdisclaimer.click(function() {
				jshort.stop();
				jlong.stop();
				if (longVisible) {
					jshort.show(200);
					jlong.hide(200);
				} else {
					jshort.hide(200);
					jlong.show(200);
				}
				longVisible = !longVisible;
			});
			jlink.click(function(e) {
				e.stopPropagation();
			});
		});
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

// returns a string for the login page
function draw_login_page($session_expired_message) {

	ob_start();
	?>
	<script type="text/javascript">dont_check_session_expired = true;</script>

	<!--<div style='display:inline-block; margin:0 15px 0 0; vertical-align:middle;'>
	<span id='login_form_guest'>
		<input type='hidden' name='username' value='guest' />
		<input type='hidden' name='password' value='guest' />
		<span style='color:red;'>:</span>
		<span class='highlight_link' onclick='send_ajax_call_from_form("/pages/login/login_ajax.php","login_form_guest");'>Login As Guest</span>
		<span style='color:red;'>:</span>
	</span><br />
	</div>

	<div style='display:inline-block; margin:0 15px 0 0; vertical-align:middle;'>
	<span style='color:gray; font-style:italic;'>or</span>
	</div>-->

	<div style='display:inline-block; margin:0 0 0 0; vertical-align:middle;'>
		<div style="width:350px; margin:0 auto;">
			<form id='login_form'>
				<div style="display:inline-block">
					<label class='errors'><?php echo $session_expired_message; ?></label><br />
					<label name='username'>Username</label>
					<input type='text' size='20' name='username' id="username" onkeypress="if (event.keyCode==13){ $('#login_submit').click(); }"><br />
					<label name='password'>Password</label>
					<input type='password' size='20' name='password' onkeypress="if (event.keyCode==13){ $('#login_submit').click(); }">
				</div>
				<div style="display:inline-block">
					<input id='login_submit' type='button' value='Login' onclick='send_ajax_call_from_form("/pages/login/login_ajax.php",$(this).parent().parent().prop("id"));' />
				</div>
			</form>
		</div>
		<br />
		<div style="width:270px; margin:0 auto;">
			<span id="create_form">
				<input type="hidden" name="draw_create_user_page" value="1">
				<a href="#" class="black_link" onclick="send_ajax_call_from_form('/pages/users/ajax.php','create_form');">Create User</a>,
			</span>
			<span id="password_form">
				<input type="hidden" name="draw_forgot_password_page" value="1" />
				<a href="#" class="black_link" onclick="send_ajax_call_from_form('/pages/users/ajax.php','password_form');">Forgot Password</a>
			</span>
		</div>
	</div>
	<?php
	$s_page = ob_get_contents();
	$s_page .= draw_disclaimer_bar();
	ob_end_clean();

	$a_page[] = draw_page_head();
	$a_page[] = $s_page;
	$a_page[] = draw_page_foot();
	return implode("\n", $a_page);
}

function check_logged_in() {
	global $session_expired;
	my_session_start();

	$o_user = get_logged_in();
	if ($o_user === NULL)
			return FALSE;
	return TRUE;
}

?>