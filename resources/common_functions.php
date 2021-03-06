<?php
require_once(dirname(__FILE__)."/globals.php");

function get_post_var($postname, $s_default = '') {
	return isset($_POST[$postname]) ? $_POST[$postname] : $s_default;
}

function get_get_var($getname, $s_default = '') {
	return isset($_GET[$getname]) ? $_GET[$getname] : $s_default;
}

function my_session_start() {
	global $session_started;
	if ($session_started === FALSE) {
			$session_started = TRUE;
			session_start();
	}
}

function login_session($o_user) {
	my_session_start();
	$_SESSION['username'] = $o_user->get_name();
	$_SESSION['last_activity'] = time();
	$_SESSION['crypt_password'] = urlencode($o_user->get_crypt_password());
	$_SESSION['loggedin'] = 1;
	$_SESSION['time_before_page_expires'] = (int)$o_user->get_server_setting('session_timeout');
	remove_timestamp_on_saves();
}

// from http://webcheatsheet.com/php/get_current_page_url.php
function curPageURL() {
	$pageURL = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
	$pageURL = 'http://';
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

// removes the timestamp on all semesters so that new
// incoming data can be written (see resources/ajax_calls.php)
function remove_timestamp_on_saves() {
	global $maindb;
	global $global_user;
	$user_id = $global_user->get_id();
	
	/*$a_semester_classes = db_query("SELECT `id` FROM `[maindb]`.`[table]` WHERE `user_id`='[user_id]'", array("maindb"=>$maindb, "table"=>"semester_classes", "user_id"=>$user_id));
	if ($a_semester_classes === FALSE)
			return;
	foreach($a_semester_classes as $a_semester_class)
			db_query("UPDATE `[maindb]`.`[table]` SET `time_submitted`='0000-00-00 00:00:00' WHERE `id`='[id]'", array("maindb"=>$maindb, "table"=>"semester_classes", "id"=>$a_semester_class['id']));*/
}

function logout_session() {
	my_session_start();
	if (isset($_SESSION)) {
			foreach($_SESSION as $k=>$v) {
					$_SESSION[$k] = NULL;
					unset($_SESSION[$k]);
			}
	}
}

function dont_check_session_expired() {
	global $global_user;
	if (!is_object($global_user)) {
			return "";
	}
	if (!method_exists($global_user, "get_server_setting")) {
			return "";
	}
	if ($global_user->get_server_setting("session_timeout") == "-1") {
			return "<script type='text/javascript'>dont_check_session_expired = true;</script>";
	}
	return "";
}

function draw_favicon_links() {
	$retval = array();
	$retval[] = '<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">';
	$retval[] = '<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">';
	$retval[] = '<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">';
	$retval[] = '<link rel="manifest" href="/site.webmanifest">';
	$retval[] = '<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">';
	$retval[] = '<meta name="msapplication-TileColor" content="#da532c">';
	$retval[] = '<meta name="theme-color" content="#ffffff">';
	return $retval;
}

function draw_page_head($outside_content = '') {
	global $global_path_to_jquery;
	global $global_path_to_jquery_ui;
	$a_page = array();
	$a_page[] = "<meta content=\"text/html;charset=utf-8\" http-equiv=\"Content-Type\">";
	$a_page[] = "<html>";
	$a_page[] = "<head>";
	$a_page[] = "<link href='/css/main.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/login_logout.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/popup_notifications.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/auto_table.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/Settings.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/tabs.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/descriptor_group.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/tooltip.css' rel='stylesheet' type='text/css'>";
	//$a_page[] = "<link href='/css/calendar.css' rel='stylesheet' type='text/css'>";
	$a_page[] = '<script src="'.$global_path_to_jquery.'"></script>';
	$a_page[] = '<script src="'.$global_path_to_jquery_ui.'"></script>';
	$a_page[] = '<script src="/js/common_functions.js"></script>';
	$a_page[] = '<script src="/js/ajax.js"></script>';
	$a_page[] = '<script src="/js/login_logout.js"></script>';
	$a_page[] = '<script src="/js/main.js"></script>';
	$a_page[] = '<script src="/js/storage.js"></script>';
	$a_page[] = '<script src="/js/popup_notifications.js"></script>';
	$a_page[] = '<script src="/js/tab_functions.js"></script>';
	$a_page[] = '<script src="/js/table_functions.js"></script>';
	//$a_page[] = '<script src="/js/tab_custom.js"></script>';
	//$a_page[] = '<script src="/js/calendar_preview.js"></script>';
	//$a_page[] = '<script src="/js/feedback.js"></script>';
	$a_page = array_merge($a_page, draw_favicon_links());
	$a_page[] = dont_check_session_expired();
	$a_page[] = "</head>";
	$a_page[] = "<body>";
	$a_page[] = "<table class='main_page_container'><tr><td class='centered'>";
	$a_page[] = "<table class='main_page_content'><tr><td>";
	$a_page[] = $outside_content."</td></tr><tr><td>";
	$a_page[] = "<table style='border:2px solid black;border-radius:5px;padding:15px 30px;margin:0 auto;background-color:#fff;'><tr><td>";
	return implode("\n", $a_page);
}

function draw_page_foot() {
	$a_page = array();
	$a_page[] = "</td></tr></table>";
	$a_page[] = "</td></tr></table>";
	$a_page[] = "</td></tr></table>";
	$a_page[] = "</body>";
	$a_page[] = "<script type='text/javascript'>set_body_min_height();</script>";
	$a_page[] = "</html>";
	return implode("\n", $a_page);
}

function manage_output($s_output) {
	
	// insert the latest datetime stamp into each javascript link
	$parts_explode = "<script";
	$a_parts = explode($parts_explode, $s_output);
	for ($i = 0; $i < count($a_parts); $i++) {
			$mid_explode = "</script";
			$a_mid = explode($mid_explode, $a_parts[$i]);
			$mid_index = 0;
			$s_mid = $a_mid[$mid_index];
			$js_pos = stripos($s_mid, ".js");
			$moddatetime = "";
			if ($js_pos !== FALSE) {
					$js_string = substr($s_mid, 0, $js_pos+3);
					$js_rest = substr($s_mid, $js_pos+3);
					$single_pos = (int)strrpos($js_string, "'");
					$double_pos = (int)strrpos($js_string, '"');
					$js_substr = substr($js_string, max($single_pos, $double_pos)+1);
					$modtime = filemtime(dirname(__FILE__)."/../{$js_substr}");
					$moddatetime = urlencode(date("Y-m-d H:i:s", $modtime));
					$a_mid[$mid_index] = "{$js_string}?{$moddatetime}{$js_rest}";
			}
			$a_parts[$i] = implode($mid_explode, $a_mid);
	}
	$s_output = implode($parts_explode, $a_parts);

	return $s_output;
}

function error_log_array($a_output, $i_tab_level = 0) {
	$s_tab_prefix = (count($a_output) > 9) ? "    " : "   ";
	$s_tab = str_repeat($s_tab_prefix, $i_tab_level);
	foreach ($a_output as $k=>$v) {
		if (is_object($v)) {
			$v = (array)$v;
		}
		$sk = (is_numeric($k) || is_bool($k)) ? "$k" : "\"{$k}\"";
		if (is_array($v)) {
			error_log("{$s_tab}{$sk}: **** array ****");
			error_log_array($v, $i_tab_level+1);
		} else {
			$sv = (is_numeric($v) || is_bool($v)) ? "$v" : "\"{$v}\"";
			error_log("{$s_tab}{$sk}: {$sv}");
		}
	}
}

function school_time_to_real_time($s_semester, $s_year) {
	if ($s_semester == "30") {
			$s_load_year = (int)$s_year;
			$s_load_semester = "spr";
			$s_name = "Spring {$s_year}";
	} else if ($s_semester == "10") {
			$s_load_year = ((int)$s_year) - 1;
			$s_load_semester = "sum";
			$s_name = "Summer {$s_year}";
	} else if ($s_semester == "20") {
			$s_load_year = ((int)$s_year) - 1;
			$s_load_semester = "fal";
			$s_name = "Fall {$s_year}";
	}
	return array("year"=>$s_load_year, "semester"=>$s_load_semester, "name"=>$s_name);
}

function get_real_year($s_semester, $s_year) {
	$a_semester = school_time_to_real_time($s_semester, $s_year);
	return $a_semester["year"];
}

function get_real_semester($s_semester, $s_year) {
	$a_semester = school_time_to_real_time($s_semester, $s_year);
	return $a_semester["semester"];
}

function endsWith($haystack, $needle) {
    $length = strlen($needle);

    return $length === 0 || 
    (substr($haystack, -$length) === $needle);
}

function explodeIds($s_ids) {
	if (!is_string($s_ids))
		return $s_ids;
	if ($s_ids == "")
		return array();
	$a_ids = explode("||", $s_ids);
	return str_replace("|", "", $a_ids);
}

function implodeIds($a_ids) {
	if (!is_array($a_ids) || count($a_ids) == 0) {
		return "";
	}
	return "|" . join("||", $a_ids) . "|";
}

function escapeTextVals($a_vals, $a_keys) {
	$a_vals_obj = new ArrayObject($a_vals);
	$a_vals2 = $a_vals_obj->getArrayCopy();
	foreach ($a_keys as $s_key) {
		$a_vals2[$s_key] = htmlspecialchars($a_vals2[$s_key]);
	}
	return $a_vals2;
}

function getValuesOfInnerArraysByKey($a_array, $si_key) {
	if (count($a_array) == 0)
		return array();

	$a_retval = array();
	foreach ($a_array as $a_inner_array) {
		if (!array_key_exists($si_key, $a_inner_array))
			continue;
		$a_retval[] = $a_inner_array[$si_key];
	}

	return $a_retval;
}

?>