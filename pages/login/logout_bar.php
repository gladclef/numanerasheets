<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");

function draw_logout_bar() {
	global $global_user;
	global $maindb;

	// some common variables
	$s_account_name = "__USERNAME__";
	$is_guest = $global_user->get_name() == "guest";

	// check if the user has access to the account tab
	// $a_account_access = db_query("SELECT `accesses` FROM `{$maindb}`.`tabs` WHERE `name`='Account'");
	// if ($a_account_access !== FALSE && count($a_account_access) > 0 && !$is_guest) {
	// 		if ($global_user->has_access($a_account_access[0]["accesses"])) {
	// 				$s_account_name = "<a href='#scroll_to_element' class='account_link' onclick='draw_tab(\"Account\");'>{$s_account_name}</a>";
	// 		}
	// }

	$s_retval = array();
	$s_retval[] = "<table class='logout_bar'><tr><td>";
	$s_retval[] = "Logged in: <span class='logout_label username_label'>".str_replace("__USERNAME__", $global_user->get_name(), $s_account_name)."</span>";
	$s_retval[] = '<span class="logout_button" onmouseover="$(this).addClass(\'mouse_hover\');" onmouseout="$(this).removeClass(\'mouse_hover\');">Logout</span>';
	$s_retval[] = "</td></tr></table>";
	return implode("\n", $s_retval);
}

?>