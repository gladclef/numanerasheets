<?php

global $o_access_object;
require_once(dirname(__FILE__).'/../../resources/db_query.php');
require_once(dirname(__FILE__).'/../../resources/globals.php');
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
require_once(dirname(__FILE__).'/campaign_funcs.php');
require_once(dirname(__FILE__)."/../login/access_object.php");
require_once(dirname(__FILE__)."/../../objects/command.php");
require_once(dirname(__FILE__).'/character_funcs.php');
require_once(dirname(__FILE__).'/welcome_funcs.php');

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

	public static function update_campaign() {
		global $maindb;
		global $fqdn;

		$cid = trim(get_post_var('campaignId'));
		$s_name = trim(get_post_var('campaign_name'));
		$b_public = intval(trim(get_post_var('public')));
		$b_passProtected = intval(trim(get_post_var('passProtected')));
		$sn_pass = get_post_var('pass');
		$sn_pass = (strlen($sn_pass) > 0) ? $sn_pass : NULL;

		// check for permissions
		$b_is_gm = campaign_funcs::is_gm($cid);
		if (!$b_is_gm)
			return json_encode(array(
				new command("print failure", "You must be the GM to modify this campaign.")));

		// update the campaign!
		$a_vars = array(
			"name"=>$s_name,
			"public"=>($b_public ? 1 : 0),
			"passProtected"=>($b_passProtected ? 1 : 0)
		);
		$s_update = array_to_update_clause($a_vars);
		if ($sn_pass != NULL) {
			$a_vars = array_merge($a_vars, array("pass"=>$sn_pass));
			$s_update .= ",`pass`=AES_ENCRYPT('[name]','[pass]')";
		}
		$a_whereVars = array(
			"id"=>$cid
		);
		$s_where = array_to_where_clause($a_whereVars);
		$b_success = db_query("UPDATE `[maindb]`.`campaigns` SET {$s_update} WHERE {$s_where}",
		                      array_merge(array("maindb"=>$maindb), $a_vars, $a_whereVars));

		if (!$b_success)
			return json_encode(array(
				new command("print failure", "Database error")));
		return json_encode(array(
			new command("print success", "Campaign updated!")));
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
			$s_campaign_name = htmlspecialchars(campaign_funcs::get_name($cid));
			$a_parts = array("element_find_by"=>"#resume", "html"=>welcome_funcs::draw_continue_column());
			return json_encode(array(
				new command("print success", "Successfully joined the campaign \"{$s_campaign_name}\"!"),
				new command("set value", $a_parts)));
		}
		else
		{
			$a_parts = array("element_find_by"=>".campaign_join_errors", "class"=>"hidden");
			return json_encode(array(
				new command("print failure", $sb_retval),
				new command("remove class", $a_parts)));
		}
	}

	public static function kick_user() {
		global $maindb;

		$cid = trim(get_post_var('campaignId'));
		$uid = trim(get_post_var('userId'));

		// check for permissions
		$b_is_gm = campaign_funcs::is_gm($cid);
		if (!$b_is_gm)
			return json_encode(array(
				new command("print failure", "Only the campaign GM may kick users.")));

		// update the campaign
		$a_campaigns = campaign_funcs::get_campaigns($cid);
		$s_users = str_replace("|{$uid}|", "", $a_campaigns[0]['users']);
		$b_success = db_query("UPDATE `[maindb]`.`campaigns` SET `users`='[users]' WHERE `id`='[id]'",
		         array("maindb"=>$maindb, "users"=>$s_users, "id"=>$cid));
		if (!$b_success)
			return json_encode(array(
				new command("print failure", "Failed to kick user (database error)")));

		return json_encode(array(
			new command("print success", "User was kicked.")));
	}

	public static function change_gm() {
		global $maindb;
		global $global_user;

		$cid = trim(get_post_var('campaignId'));
		$uid = trim(get_post_var('userId'));

		// check for permissions
		$b_is_gm = campaign_funcs::is_gm($cid);
		if (!$b_is_gm)
			return json_encode(array(
				new command("print failure", "Only the campaign GM may change the GM.")));

		// change the GM user
		$a_update_vars = array(
			"gmUser"=>$uid
		);

		// get the username
		$a_users = db_query("SELECT * FROM `[maindb]`.`users` WHERE `id`='[id]'",
		                    array("maindb"=>$maindb, "id"=>$uid));
		if (!is_array($a_users) || count($a_users) == 0) {
			return json_encode(array(
				new command("print failure", "Failed to get user (database error)")));
		}

		// add the GM to list of users if not already there
		$a_campaigns = campaign_funcs::get_campaigns($cid);
		$s_users = $a_campaigns[0]['users'];
		$s_global_user_id = "|".$global_user->get_id()."|";
		if (strpos($s_users, $s_global_user_id) === FALSE) {
			$s_users .= $s_global_user_id;
			$a_update_vars["users"] = $s_users;
		}

		// update the database
		$s_update_clause = array_to_update_clause($a_update_vars);
		$b_success = db_query("UPDATE `[maindb]`.`campaigns` SET {$s_update_clause} WHERE `id`='[id]'",
		                      array_merge(array("maindb"=>$maindb, "id"=>$cid), $a_update_vars));
		if (!$b_success)
			return json_encode(array(
				new command("print failure", "Failed to change the GM user (database error)")));

		$s_new_GM_name = $a_users[0]["username"];
		return json_encode(array(
			new command("print success", "GM user changed to {$s_new_GM_name}.")));
	}

	public static function update_user_accesses() {
		global $maindb;
		global $global_user;

		$cid = trim(get_post_var('campaignId'));
		$uids = trim(get_post_var('userIds'));
		$charId = trim(get_post_var('charId'));

		// check for permissions
		$b_is_gm = campaign_funcs::is_gm($cid);
		if (!$b_is_gm)
			return json_encode(array(
				new command("print failure", "Only the campaign GM may change user accesses.")));

		// update the database
		$b_success = db_query("UPDATE `[maindb]`.`characters` SET `users`='[uids]' WHERE `id`='[charId]'",
		                      array("maindb"=>$maindb, "charId"=>$charId, "uids"=>$uids));
		if (!$b_success)
			return json_encode(array(
				new command("print failure", "Failed to update user accesses (database error)")));

		return json_encode(array(
			new command("print success", "User accesses updated")));
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

	public static function copy_character() {
		global $global_user;
		global $maindb;
		global $fqdn;

		$cid = intval(trim(get_post_var("campaign_id")));
		$charid = intval(trim(get_post_var("character_id")));
		$s_new_campaign_name = get_post_var("campaign_name");
		$b_is_gm = campaign_funcs::is_gm($cid);

		// check for user access
		if (!$b_is_gm) {
			return json_encode(array(
				new command("print failure", "Only the GM may copy a character to another campaign!")));
		}
		$sb_result = user_ajax::user_has_access($cid, $charid);
		if ($sb_result !== TRUE)
			return $sb_result;

		// get the character, users, and campaign
		$a_characters = campaign_funcs::get_characters($cid, $b_is_gm, $charid);
		if (!is_array($a_characters) || count($a_characters) == 0) {
			return json_encode(array(
				new command("print failure", "Unknown character \"{$charid}\"")));
		}
		$a_character = $a_characters[0];
		$a_char_users = explodeIds($a_character['users']);
		$a_char_users = (is_array($a_char_users)) ? $a_char_users : array();
		$a_users = db_query("SELECT `users`,`id` FROM `[maindb]`.`campaigns` WHERE `name`='[name]'",
		                    array("maindb"=>$maindb, "name"=>$s_new_campaign_name));
		if (!is_array($a_users) || count($a_users) == 0)
			return json_encode(array(
				new command("print failure", "Unknown campaign \"{$s_new_campaign_name}\"")));
		if (count($a_users) != 1)
			return json_encode(array(
				new command("print failure", "Error! There are two or more campaigns with the name \"{$s_new_campaign_name}\"")));
		$new_cid = intval($a_users[0]['id']);
		$a_users = explodeIds($a_users[0]['users']);
		if (!is_array($a_users)) {
			return json_encode(array(
				new command("print failure", "Add all users of this character as users of the other campaign, first.")));
		}

		// check for users/campaign access
		foreach ($a_char_users as $s_char_user) {
			$b_found = FALSE;
			foreach ($a_users as $s_user) {
				if (intval($s_char_user) == intval($s_user)) {
					$b_found = TRUE;
					break;
				}
			}
			if (!$b_found) {
				return json_encode(array(
					new command("print failure", "All users with access to this character do not have access to the other campaign.")));
			}
		}

		// copy the character
		$i_new_charId = character_funcs::copy_to_campaign($a_character['id'], $new_cid, FALSE);
		if (is_string($i_new_charId)) {
			return json_encode(array(
				new command("print failure", $i_new_charId)));
		}

		// update the campaign and users
		db_query("UPDATE `[maindb]`.`campaigns` SET `characters`=CONCAT(`characters`,'|[charId]|') WHERE `id`='[cid]'",
		         array("maindb"=>$maindb, "charId"=>$i_new_charId, "cid"=>$new_cid));
		foreach ($a_char_users as $s_user) {
			db_query("UPDATE `[maindb]`.`users` SET `characters`=CONCAT(`characters`,'|[charId]|') WHERE `id`='[uid]'",
			         array("maindb"=>$maindb, "charId"=>$i_new_charId, "uid"=>$s_user), TRUE);
		}

		return json_encode(array(
			new command("print success", "Copied character! <a href='https://{$fqdn}/pages/campaigns/campaign.php?id={$new_cid}'>See it in the other campaign!</a>")));
	}

	public static function remove_or_restore_character() {
		global $global_user;
		global $maindb;

		$cid = intval(trim(get_post_var("campaign_id")));
		$charid = intval(trim(get_post_var("character_id")));
		$b_is_remove = boolval(intval(get_post_var("isRemove")));
		$b_is_gm = campaign_funcs::is_gm($cid);

		// check for user access
		if (!$b_is_gm) {
			return json_encode(array(
				new command("print failure", "Only the GM may remove or restore characters!")));
		}

		// check that the character can be found in the campaign
		$s_source_col = ($b_is_remove) ? "characters" : "removedCharacters";
		$s_dest_col = ($b_is_remove) ? "removedCharacters" : "characters";
		$a_campaigns = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE `id`='[cid]' AND INSTR(`{$s_source_col}`,'|[charid]|')",
		                        array("maindb"=>$maindb, "cid"=>$cid, "charid"=>$charid));
		if (!is_array($a_campaigns) || count($a_campaigns) == 0) {
			return json_encode(array(
				new command("print failure", "That character can't be found in this campaign!")));
		}

		// move the character
		$b_success = db_query("UPDATE `[maindb]`.`campaigns` SET `{$s_source_col}`=REPLACE(`{$s_source_col}`,'|[charid]|','') WHERE `id`='[cid]'",
		                      array("maindb"=>$maindb, "cid"=>$cid, "charid"=>$charid));
		if (!$b_success) {
			return json_encode(array(
				new command("print failure", "Database error")));
		}
		$b_success = db_query("UPDATE `[maindb]`.`campaigns` SET `{$s_dest_col}`=CONCAT(`{$s_dest_col}`,'|[charid]|') WHERE `id`='[cid]'",
		                      array("maindb"=>$maindb, "cid"=>$cid, "charid"=>$charid));
		if (!$b_success) {
			return json_encode(array(
				new command("print failure", "Database error (2)")));
		}

		return json_encode(array(
			new command("print success", "Character updated!")));
	}

	public static function share_with_character() {
		global $global_user;
		global $maindb;

		$uid = $global_user->get_id();
		$cid = intval(trim(get_post_var("campaign_id")));
		$charid = intval(trim(get_post_var("character_id")));
		$rowid = intval(get_post_var("rowid"));
		$ocharid = intval(get_post_var("other_character_id"));
		$s_table = get_post_var("table");
		$s_description = get_post_var("description");
		$s_action = get_post_var("action");

		$o_hideCommand = new command("run script", 'setTimeout(function() { $("#share_with_character_form").hide(); }, 3000);');
		$o_reloadCommand = new command("run script", 'setTimeout(function() { draw_character('.$charid.'); }, 3000);');
		$o_printSuccessCommand = new command("print success", "Item action \"{$s_action}\" succeeded!");

		// check for user access
		if (!campaign_funcs::user_in_campaign($cid, $uid)) {
			return json_encode(array(
				new command("print failure", "You must be a part of the campaign to share items!")));
		}
		$b_is_gm = campaign_funcs::is_gm($cid);
		$a_characters = campaign_funcs::get_characters($cid, $b_is_gm, $charid, $uid);
		$s_charName = (is_array($a_characters) && count($a_characters) > 0) ? $a_characters[0]['name'] : "unknown";
		$a_rows = db_query("SELECT `id` FROM `[maindb]`.`characters` WHERE `id`='[charid]' AND INSTR(`[table]`,'|[rowid]|')",
		                   array("maindb"=>$maindb, "charid"=>$charid, "table"=>$s_table, "rowid"=>$rowid));
		if (!is_array($a_rows) || count($a_rows) == 0) {
			return json_encode(array(
				new command("print failure", "The character \"{$s_charName}\" must have that \"{$s_description}\" in order to share it!")));
		}

		// check the action string
		if ($s_action != "give" && $s_action != "copy" && $s_action != "share") {
			return json_encode(array(
				new command("print failure", "Uknown action \"{$s_action}\" to take with this {$s_description}!")));
		}
		if ($charid == $ocharid && $s_action != "copy") {
			return json_encode(array(
				new command("print failure", "Cannot \"{$s_action}\" this item to the same character! (only \"copy\" is valid for the same character)")));
		}

		// share the item
		if ($s_action == "share" || $s_action == "give") {
			$b_success = db_query("UPDATE `[maindb]`.`characters` SET `[table]`=CONCAT(`[table]`,'|[rowid]|') WHERE `id`='[ocharid]'",
			                      array("maindb"=>$maindb, "table"=>$s_table, "rowid"=>$rowid, "ocharid"=>$ocharid));
			if (!$b_success) {
				return json_encode(array(
					new command("print failure", "Database error. Failed to \"{$s_action}\" this item!")));
			}
		}

		// give the item
		if ($s_action == "give") {
			$b_success = db_query("UPDATE `[maindb]`.`characters` SET `[table]`=REPLACE(`[table]`,'|[rowid]|','') WHERE `id`='[charid]'",
			                      array("maindb"=>$maindb, "table"=>$s_table, "rowid"=>$rowid, "charid"=>$charid));
			if (!$b_success) {
				return json_encode(array(
					new command("print failure", "Database error. Failed to \"{$s_action}\" this item (shared it instead)!")));
			}
			// success!
			return json_encode(array(
				$o_hideCommand,
				$o_reloadCommand,
				$o_printSuccessCommand));
		}

		// copy the item
		if ($s_action == "copy") {
			// copy the row
			$a_where_vars = array(
				"id"=>$rowid
			);
			$a_inc_columns = array(
				"id"
			);
			$i_new_rowid = db_copy_row($s_table, $a_where_vars, $a_inc_columns);
			if (is_string($i_new_rowid)) {
				return json_encode(array(
					new command("print failure", "Error copying item! ({$i_new_rowid})")));
			}

			// assign the row
			$b_success = db_query("UPDATE `[maindb]`.`characters` SET `[table]`=CONCAT(`[table]`,'|[new_rowid]|') WHERE `id`='[ocharid]'",
			                      array("maindb"=>$maindb, "table"=>$s_table, "new_rowid"=>$i_new_rowid, "ocharid"=>$ocharid));
			if (!$b_success) {
				return json_encode(array(
					new command("print failure", "Database error. Failed to assign copied item to other character!")));
			}
		}

		return json_encode(array(
			$o_hideCommand,
			$o_printSuccessCommand));
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
				$s_response = user_ajax::$s_command();
				echo $s_response;
		} else {
				echo json_encode(array(
					'bad command'));
		}
}

?>