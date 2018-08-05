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

	public static function user_has_access($cid, $charid) {
		$b_is_gm = campaign_funcs::is_gm($cid);
		$sb_has_access = campaign_funcs::has_character_access($cid, $charid, $b_is_gm);
		if (is_string($sb_has_access)) {
			return json_encode(array(
				new command("print failure", $sb_has_access)));
		} else if (!$sb_has_access) {
			return json_encode(array(
				new command("print failure", "You don't have access to this character.")));
		}
		return TRUE;
	}

	public static function update_character_sheet() {
		global $global_user;

		$cid = intval(trim(get_post_var("campaign_id")));
		$charid = intval(trim(get_post_var("character_id")));
		$s_column = trim(get_post_var("property"));
		$s_value = trim(get_post_var("value"));
		$s_table = trim(get_post_var("table"));
		$i_rowid = intval(trim(get_post_var("rowid")));

		// check for user access
		$sb_result = user_ajax::user_has_access($cid, $charid);
		if ($sb_result !== TRUE)
			return $sb_result;

		// update the character
		if (isset($_POST["table"]) && $s_table != "" && $s_table != "characters") {
			$sb_retval = campaign_funcs::update_table($s_table, $i_rowid, $s_column, $s_value);
		} else {
			$sb_retval = campaign_funcs::update_character($charid, $s_column, $s_value);
		}
		if (is_string($sb_retval)) {
			return json_encode(array(
				new command("print failure", $sb_retval)));
		} else if (!$sb_retval) {
			return json_encode(array(
				new command("print failure", "Failed to update")));
		} else {
			return json_encode(array(
				new command("print success", "Changes synched")));
		}
	}

	public static function set_floater_pos() {
		$_SESSION["left"] = intval(get_post_var("left"));
		$_SESSION["top"] = intval(get_post_var("top"));
	}

	public static function getNewVars($s_table, $cid) {
		switch ($s_table) {
			case 'artifacts':
				return array(
					"name"=>"",
					"description"=>"",
					"depletion"=>"",
					"campaign"=>$cid,
					"partyUnderstanding"=>""
				);
			case 'cyphers':
				return array(
					"name"=>"",
					"level"=>"0",
					"description"=>"",
					"campaign"=>$cid,
					"partyUnderstanding"=>""
				);
				break;
			case 'skills':
				return array(
					"description"=>"",
					"trained"=>0,
					"skilled"=>0,
					"inability"=>0,
					"campaign"=>$cid
				);
			case 'abilities':
				return array(
					"description"=>"",
					"name"=>"",
					"cost"=>"",
					"campaign"=>$cid
				);
			case 'inabilities':
				return array(
					"description"=>"",
					"name"=>"",
					"campaign"=>$cid
				);
			case 'equipment':
				return array(
					"description"=>"",
					"name"=>"",
					"campaign"=>$cid
				);
			case 'attacks':
				return array(
					"name"=>"",
					"damage"=>"",
					"modifier"=>"",
					"notes"=>"",
					"campaign"=>$cid
				);
			case 'armor':
				return array(
					"name"=>"",
					"cost"=>"",
					"modifier"=>"",
					"speedReduction"=>"",
					"notes"=>"",
					"campaign"=>$cid
				);
			case 'oddities':
				return array(
					"name"=>"",
					"description"=>"",
					"campaign"=>$cid
				);
			case 'places':
				return array(
					"name"=>"",
					"description"=>"",
					"campaign"=>$cid
				);
			default:
				return FALSE;
		}
	}

	public static function addNew() {
		global $global_user;
		global $maindb;

		$cid = intval(trim(get_post_var("campaign_id")));
		$charid = intval(trim(get_post_var("character_id")));
		$s_table = trim(get_post_var("table"));
		$s_description = trim(get_post_var("description"));
		$s_container = trim(get_post_var("container"));

		// check for user access
		$sb_result = user_ajax::user_has_access($cid, $charid);
		if ($sb_result !== TRUE)
			return $sb_result;

		// create the element
		$a_vars = user_ajax::getNewVars($s_table, $cid);
		if ($a_vars === FALSE)
			return json_encode(array(
				new command("print failure", "Unknown table \"{$s_table}\"")));
		$s_insert_str = array_to_insert_clause($a_vars);
		$b_result = db_query("INSERT INTO `[maindb]`.`[table]` {$s_insert_str}",
		                     array_merge(array("maindb"=>$maindb, "table"=>$s_table), $a_vars));
		if (!$b_result)
			return json_encode(array(
				new command("print failure", "Database error creating new {$s_description}")));
		$si_tableRowId = user_ajax::get_latest_insert_id($s_table);
		if (is_string($si_tableRowId))
			return json_encode(array(
				new command("print failure", $si_tableRowId)));

		// try to relate it to the character
		$s_column = $s_table;
		$s_table = $s_table;
		$sb_result = character_funcs::add_reference_or_delete($charid, $s_column, $s_table, $si_tableRowId);

		$b_is_gm = campaign_funcs::is_gm($cid);
		$a_characters = campaign_funcs::get_characters($cid, $b_is_gm, $charid);
		$a_entries = character_funcs::get_related_table_entries($a_characters[0], $s_table);
		$s_drawFunc = "draw_{$s_table}";
		$a_parts = array("element_find_by"=>"#{$s_container}", "html"=>character_funcs::$s_drawFunc($a_entries));
		return json_encode(array(
			new command("print success", "Created new {$s_description}"),
			new command("set value", $a_parts),
			new command("run script", "startupStyling(); startupFunctionality(); window.collapseids=\"{$a_characters[0]['collapseIds']}\"; collapseAll();")));
	}

	public static function remove() {
		global $global_user;
		global $maindb;

		$cid = intval(trim(get_post_var("campaign_id")));
		$charid = intval(trim(get_post_var("character_id")));
		$i_rowid = intval(trim(get_post_var("rowid")));
		$s_table = trim(get_post_var("table"));
		$s_description = trim(get_post_var("description"));
		$b_removeRow = (bool)$_POST["removeRow"];

		// check for user access
		$sb_result = user_ajax::user_has_access($cid, $charid);
		if ($sb_result !== TRUE)
			return $sb_result;

		// remove the user's reference to it
		$b_is_gm = campaign_funcs::is_gm($cid);
		$a_characters = campaign_funcs::get_characters($cid, $b_is_gm, $charid);
		$s_ids = str_replace("|{$i_rowid}|", "", $a_characters[0][$s_table]);
		$sb_result = campaign_funcs::update_character($charid, $s_table, $s_ids);
		if ($sb_result === FALSE)
			return json_encode(array(
				new command("print failure", "Database error removing {$s_description}")));
		else if (is_string($sb_result))
			return json_encode(array(
				new command("print failure", $sb_result)));

		// remove the row, maybe
		if ($b_removeRow) {
			db_query("DELETE FROM `[maindb]`.`[table]` WHERE `id`='[id]'",
			         array("maindb"=>$maindb, "table"=>$s_table, "id"=>$i_rowid));
		}

		return json_encode(array(
			new command("print success", "{$s_description} removed")));
	}

	public static function get_latest_insert_id($s_table) {
		global $maindb;

		$id = 0;
		$a_ids = db_query("SELECT LAST_INSERT_ID() AS id");
		if (is_array($a_ids) && count($a_ids) > 0)
			$id = intval($a_ids[0]['id']);
		if ($id == 0)
			$a_ids = db_query("SELECT `id` FROM `[maindb]`.`[table]` ORDER BY `id` DESC LIMIT 1",
			                  array("maindb"=>$maindb, "table"=>$s_table));
		if (!is_array($a_ids) || count($a_ids) == 0) {
			error_log("Database error while trying to get created instance id!");
			return "Database error retrieving new instance's id";
		}
		return intval($a_ids[0]['id']);
	}

	public static function draw_character() {
		global $global_user;

		$cid = intval(trim(get_post_var("campaign_id")));
		$charid = intval(trim(get_post_var("character_id")));
		$b_is_gm = campaign_funcs::is_gm($cid);

		// check for user access
		$sb_result = user_ajax::user_has_access($cid, $charid);
		if ($sb_result !== TRUE)
			return $sb_result;

		// get the character
		$a_characters = campaign_funcs::get_characters($cid, $b_is_gm, $charid);
		if (!is_array($a_characters) || count($a_characters) == 0)
			return "Unknown character \"{$charid}\"";

		// draw the character
		return character_funcs::draw_character($a_characters[0]);
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