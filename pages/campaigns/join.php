<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
my_session_start();
require_once(dirname(__FILE__)."/../login/login.php");
get_logged_in();
require_once(dirname(__FILE__)."/campaign_funcs.php");

function draw_join_page() {
	global $global_user;
	global $maindb;
	global $fqdn;

	$cid = intval(trim(get_get_var("id")));
	$s_getShareKey = trim(get_get_var("sharekey"));
	$uid = $global_user->get_id();

	// get campaign stats
	$s_campaign_name = htmlspecialchars(campaign_funcs::get_name($cid));
	$a_campaigns = campaign_funcs::get_campaigns($cid);
	if (count($a_campaigns) == 0) {
		echo "Uknown campaign id \"{$cid}\"!";
		return;
	}
	$b_is_public = intval($a_campaigns[0]['public']) == 1;
	$b_passProtected = intval($a_campaigns[0]['passProtected']) == 1;
	$s_shareKey = $a_campaigns[0]['shareKey'];

	// try to join
	$sb_success = campaign_funcs::join_campaign($cid, NULL, $s_shareKey, $uid);

	// check if the user is part of the campaign
	$b_has_joined = campaign_funcs::user_in_campaign($cid, $uid);

	ob_start();

	if ($b_has_joined) {
		echo "Congradulations! You are now a part of the \"{$s_campaign_name}\" campaign!";
	}
	else {
	?>
	<div style="width: 1000px">
		<br />
		<div><?php echo $sb_success; ?></div><br /><br />

		<?php
		if ($b_passProtected) {
		?>
		<h2 class="title">Join Campaign</h2>
		<form id="campaign_join_form">
			<input type="hidden" name="command" value="join_campaign">
			<input type="hidden" name="campaignId" value="<?php echo $cid; ?>">
			<input type="hidden" name="uid" value="<?php echo $uid; ?>">
			<?php echo $b_is_public ? "" : "<input type=\"hidden\" name=\"shareKey\" value=\"{$s_getShareKey}\">"; ?>

			<div>
				<label>Password: </label>
				<input type="password" name="password" value="" placeholder="password" class="fill" onkeypress="if (event.keyCode==13){ $('#join_submit').click(); }" />
				<br />
			</div>

			<input id="join_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','campaign_join_form');" value="Join" /><br />
			<label class="errors"></label><br />
		</form>
		<?php
		}
		?>
	</div>
	<?php
	}
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

if ($global_user) {
		if ($global_user->exists_in_db()) {
				$s_drawval = array();
				$s_drawval[] = draw_page_head();
				$s_drawval[] = "<dev id='content'>";
				$s_drawval[] = draw_join_page();
				$s_drawval[] = "</dev>";
				$s_drawval[] = draw_page_foot();
				echo manage_output(implode("\n", $s_drawval));
		}
} else {
		logout_session();
		global $fqdn;
		echo "You must <a href=\"https://{$fqdn}/pages/login/index.php\">log in</a> before you can join this campaign.";
}

?>
