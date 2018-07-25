<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
my_session_start();
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
require_once(dirname(__FILE__)."/../../tabs/tabs_functions.php");

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

function draw_welcome_page() {
	global $global_user;
	global $maindb;

	ob_start();
	?>
	<div class="row">
		<div id="resume" class="column">
			<h1>Resume Campaign</h1>
			<div style="scroll-behavior:auto; width:300px; height:400px; border-radius:3px; background-color:#ccc;" class="inset_shadow">
				<?php
				$uid = $global_user->get_id();
				$a_campaigns = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE INSTR(`users`, '|[uid]|')",
									    array("maindb"=>$maindb, "uid"=>$uid));
				foreach($a_campaigns as $a_campaign) {
					$cid = $a_campaign['id'];
					$cname = $a_campaign['name'];
					echo "<button class=\"campaign_button\" onclick=\"window.location.href='campaign.php?id={$cid}'\">{$cname}</button>";
				}
				?>
			</div>
		</div>

		<div id="join" class="column">
			<h1>Join Existing Campaign</h1>
			<form id="search_campaign_form">
				<input type="hidden" name="command" value="search_campaign">

				<div id="search_campaign_name_form">
					<label>Find by name:</label><br />
					<input type="textarea" size="30" name="campaign_name" placeholder="Eg: The Great Divide" onblur="send_ajax_call_from_form('ajax.php','search_campaign_name_form');" onkeypress="if (event.keyCode==13){ $('#search_submit').click(); }" />
					<label class="errors">&nbsp;</label>
				</div>

				<label class="errors"></label><br />
				<input id="search_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','search_campaign_form');" value="Submit" /><br />
			</form>
		</div>

		<div class="column">
			<h1>Create New Campaign</h1>
			<form id="create_campaign_form">
				<input type="hidden" name="command" value="create_campaign">

				<div id="choose_campaign_name_form">
					<input type="hidden" name="command" value="check_campaign_name">
					<label>Choose a campaign name:</label><br />
					<input type="textarea" size="30" name="campaign_name" placeholder="Eg: The Great Divide" onblur="send_ajax_call_from_form('ajax.php','choose_campaign_name_form');" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }" /><br />
					<label class="errors hidden" id="campaign_name_errors">&nbsp;</label>
				</div>

				<label>Make this campaign: </label>
				<select name="public" id="public" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }">
					<option value="1">Public</option>
					<option value="0">Private</option>
				</select><br />
				<div id="passProtectedDiv" class="hidden">
					<label>Require password:</label>
					<input type="checkbox" name="passProtected" id="passProtected" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }" />
				</div>
				<div id="passDiv" class="hidden">
					<label>Password</label>
					<input type="password" name="pass" id="pass" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }" /><br />
				</div>
				<label class="errors"></label><br />
				<input id="create_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','create_campaign_form');" value="Submit" /><br />
			</form>
			<script type="text/javascript">
				$(document).ready(function() {
					var t = 100;
					var public = $("#public");
					var passProtected = $("#passProtected");
					var passProtectedDiv = $("#passProtectedDiv");
					var passDiv = $("#passDiv");
					var passShow = function() {
						passDiv.stop();
						if (public.val() == "1" || !passProtected.is(":checked")) {
							passDiv.hide(t);
						} else {
							passDiv.show(t);
						}
					}
					var passProtectedShow = function() {
						passProtectedDiv.stop();
						if (public.val() == "1") {
							passProtectedDiv.hide(t);
						} else {
							passProtectedDiv.show(t);
						}
						passShow();
					};
					public.change(passProtectedShow);
					passProtected.click(passShow);
				});
			</script>
		</div>
	</div>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

if ($global_user) {
		if ($global_user->exists_in_db()) {
				$s_drawval = array();
				$s_drawval[] = draw_page_head();
				$s_drawval[] = draw_logout_bar();
				$s_drawval[] = "<br /><br /><dev id='content'>";
				$s_drawval[] = draw_welcome_page();
				$s_drawval[] = "</dev>";
				$s_drawval[] = draw_page_foot();
				echo manage_output(implode("\n", $s_drawval));
		}
} else {
		logout_session();
}

?>
