<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
my_session_start();
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
require_once(dirname(__FILE__)."/../../tabs/tabs_functions.php");
require_once(dirname(__FILE__)."/../login/logout_bar.php");
require_once(dirname(__FILE__)."/welcome_funcs.php");

if ($global_user) {
		if ($global_user->exists_in_db()) {
				$s_drawval = array();
				$s_drawval[] = draw_page_head();
				$s_drawval[] = draw_logout_bar();
				$s_drawval[] = "<br /><br /><dev id='content'>";
				$s_drawval[] = welcome_funcs::draw_welcome_page();
				$s_drawval[] = "</dev>";
				$s_drawval[] = draw_page_foot();
				echo manage_output(implode("\n", $s_drawval));
		}
} else {
		logout_session();
}

?>
