<?php

global $o_access_object;
require_once(dirname(__FILE__).'/../../resources/db_query.php');
require_once(dirname(__FILE__).'/../../resources/globals.php');
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
require_once(dirname(__FILE__).'/campaign_funcs.php');
require_once(dirname(__FILE__)."/../login/access_object.php");
require_once(dirname(__FILE__)."/../../objects/command.php");
require_once(dirname(__FILE__).'/character_funcs.php');

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
			new command("reload page", "3000")));
	}

	public static function search_campaign() {
		global $maindb;
		global $fqdn;

		$s_name = trim(get_post_var('campaign_name'));
		$s_html = "<input type=\"hidden\" name=\"command\" value=\"join_campaign\">
		<input type=\"hidden\" name=\"campaignId\" value=\"-1\">
		<label class=\"errors hidden campaign_join_errors\" style='width:270px; margin:0 auto;'>&nbsp;</label>";
		$a_campaigns = campaign_funcs::search_campaign($s_name);

		if (is_array($a_campaigns) && count($a_campaigns) > 0 && strlen($s_name) > 0)
		{
			foreach ($a_campaigns as $a_campaign)
			{
				$cid = $a_campaign['id'];
				$cname = $a_campaign['name'];
				$pp = $a_campaign['passProtected'];
				$s_lock = $a_campaign['passProtected'] ? "<span style=\"font-style:normal;\"> &#128274;</span>" : "";
				$s_html = $s_html . "<button type=\"button\" class=\"campaign_button join\" onclick=\"provideJoinButton(this);\" campaignId='{$cid}'>{$cname}{$s_lock}</button>
				          <button type=\"button\" class=\"campaign_join_button\" onclick=\"join_campaign_btn_click(this);\" passProtected=\"{$pp}\" campaignId='{$cid}'>Join</button>";
			}
		}
		else
		{
			$s_html = $s_html . "<div style='color:gray; width:270px; margin:0 auto; text-align:center;'>Can't find any matching campaigns.</div>";
		}

		$a_parts = array("element_find_by"=>"#join_campaign_form", "html"=>$s_html);
		return json_encode(array(
			new command("set value", $a_parts)));
	}

	public static function join_campaign() {
		$cid = intval(trim($_POST['campaignId']));
		$password = isset($_POST['password']) ? trim(get_post_var('password')) : NULL;
		$shareKey = isset($_POST['shareKey']) ? trim(get_post_var('shareKey')) : NULL;
		$uid = isset($_POST['uid']) ? trim(get_post_var('uid')) : NULL;

		$sb_retval = campaign_funcs::join_campaign($cid, $password, $shareKey, $uid);
		if ($sb_retval === TRUE)
		{
			$s_campaign_name = campaign_funcs::get_name($cid);
			$s_success = "<div style='width:230px; background-color:#0d0; padding:20px; border-radius:10px; border:#0a0 solid 2px; margin:0 auto;'>Success! Joined the campaign \"{$s_campaign_name}\".</div>";
			$a_parts1 = array("element_find_by"=>"#join_campaign_form", "html"=>$s_success);
			$a_parts2 = array("element_find_by"=>"#success", "class"=>"hidden");
			$a_commands = array(
				new command("set value", $a_parts1),
				new command("remove class", $a_parts2));
			if (is_null($shareKey)) {
				$a_commands = array_merge($a_commands, array(
					new command("reload page", "3000")));
			}
			return json_encode($a_commands);
		}
		else
		{
			$a_parts = array("element_find_by"=>".campaign_join_errors", "class"=>"hidden");
			return json_encode(array(
				new command("print failure", $sb_retval),
				new command("remove class", $a_parts)));
		}
	}

	public static function create_character() {
		global $global_user;

		$uid = $global_user->get_id();
		$cid = intval(trim(get_post_var("campaign_id")));

		$sb_retval = character_funcs::create_character($cid, $uid);
		$a_parts = array("element_find_by"=>".create_character_errors", "class"=>"hidden");
		if ($sb_retval === TRUE)
		{
			return json_encode(array(
				new command("print success", "Success! Character created!"),
				new command("reload page", "3000"),
				new command("remove class", $a_parts)));
		}
		else
		{
			return json_encode(array(
				new command("print failure", $sb_retval),
				new command("remove class", $a_parts)));
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