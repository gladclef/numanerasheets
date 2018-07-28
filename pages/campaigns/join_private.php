<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__).'/../../resources/db_query.php');
require_once(dirname(__FILE__).'/campaign_funcs.php');

function draw_success_div($a_campaign, $b_is_hidden)
{
	global $fqdn;

	$s_name = $a_campaign['name'];
	$s_hidden = $b_is_hidden ? "class=\"hidden\"" : "";

	ob_start();
	?>

	<div id="success" <?php echo $s_hidden; ?> style="background-color:lightgray; border:2px solid gray; border-radius: 10px; margin:10px 0; padding:10px;">
		Success! You are now a part of the campaign "<?php echo $s_name; ?>"!<br />
		Click <a href='https://<?php echo $fqdn; ?>'>here</a> to go to the login page.
	</div>

	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_pass_join($a_campaigns, $s_shareKey, $cid, $uid)
{
	ob_start();
	?>

	<div id="join_campaign_form">
		<input type="hidden" name="command" value="join_campaign">
		<input type="hidden" name="shareKey" value="<?php echo $s_shareKey; ?>">
		<input type="hidden" name="campaignId" value="<?php echo $cid; ?>">
		<input type="hidden" name="userId" value="<?php echo $uid; ?>">
		<label>Enter the password for this campaign:</label><br />
		<input type="password" name="password" id="pass" onkeypress="if (event.keyCode==13){ $('#join_submit').click(); }" /><br /><br />
		<input id="join_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','join_campaign_form');" value="Join" /><br />
		<label class="errors"></label>
	</div>

	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	$s_page .= draw_success_div($a_campaigns[0], TRUE);
	return $s_page;
}

function draw_join_private_page()
{
	global $maindb;

	$s_shareKey = $_GET['share_key'];
	$cid = $_GET['campaign_id'];
	$uid = $_GET['user_id'];

	// verify the campaign id, user id, and check for password protection
	$a_campaigns = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE `id`='[cid]'",
	                        array("maindb"=>$maindb, "cid"=>$cid));
	$a_users = db_query("SELECT `id` FROM `[maindb]`.`users` WHERE `id`='[uid]'",
                        array("maindb"=>$maindb, "uid"=>$uid));
	if (!is_array($a_campaigns) || !is_array($a_users)) {
		return "Database error";
	}
	if (count($a_campaigns) == 0)
	{
		return "Unknown campaign id.";
	}
	if (count($a_users) == 0)
	{
		return "Uknown user id.";
	}
	if (intval($a_campaigns[0]['passProtected']) == 1)
	{
		return draw_pass_join($a_campaigns, $s_shareKey, $cid, $uid);
	}

	// try to join the campaign
	$sb_success = join_campaign($cid, NULL, $s_shareKey, $uid);
	if ($sb_success === TRUE)
	{
		return draw_success_div($a_campaigns[0], FALSE);
	}
}

if (!isset($_GET['share_key']))
{
	echo "No share key for private campaign.";
}
else if (!isset($_GET['campaign_id']))
{
	echo "No campaign id.";
}
else if (!isset($_GET['user_id']))
{
	echo "No user id.";
}
else
{
	$s_drawval = array();
	$s_drawval[] = draw_page_head();
	$s_drawval[] = "<dev id='content'>";
	$s_drawval[] = draw_join_private_page();
	$s_drawval[] = "</dev>";
	$s_drawval[] = draw_page_foot();
	echo manage_output(implode("\n", $s_drawval));
}

?>