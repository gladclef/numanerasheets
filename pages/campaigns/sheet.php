<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/campaign_funcs.php");

function draw_sheet($a_character) {
	global $global_user;
	global $maindb;

	ob_start();
	?>
	
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

/** @return TRUE on success, failure description string otherwise */
function create_character($cid, $uid) {
	global $maindb;

	// create the character
	$a_insert_vals = array(
		"name"=>"no name",
		"descriptor"=>"",
		"type"=>"",
		"focus"=>"",
		"tier"=>1,
		"effort"=>"1",
		"experience"=>"0",
		"benefitIncreaseCapabilities"=>0,
		"benefitMoveTowardPerfection"=>0, //bit
		"benefitExtraEffort"=>0, //bit
		"benefitSkillTraining"=>0, //bit
		"benefitOther"=>0, //bit
		"recoveryBonus"=>"2", //varchar
		"recoveryAction"=>0, //bit
		"recovery10min"=>0, //bit
		"recovery1hr"=>0, //bit
		"recovery10hr"=>0, //bit
		"damageImpaired"=>0, //bit
		"damageDebilitated"=>0, //bit
		"statMightPool"=>10, //int
		"statMightTotal"=>10, //int
		"statSpeedPool"=>10, //int
		"statSpeedTotal"=>10, //int
		"statIntellectPool"=>10, //int
		"statIntellectTotal"=>10, //int
		"statMightEdge"=>0, //int
		"statSpeedEdge"=>0, //int
		"statIntellectEdge"=>0, //int
		"statMightNotes"=>"", //varchar
		"statSpeedNotes"=>"", //varchar
		"statIntellectNotes"=>"", //varchar
		"skills"=>"", //varchar
		"attacks"=>"", //varchar
		"armor"=>"", //varchar
		"abilities"=>"", //varchar
		"inabilities"=>"", //varchar
		"cyphers"=>"", //varchar
		"cypherLimit"=>"", //varchar
		"equipment"=>"", //varchar
		"artifacts"=>"", //varchar
		"oddities"=>"", //varchar
		"shins"=>0, //int
		"background"=>"", //varchar
		"pcs"=>"", //varchar
		"npcs"=>"", //varchar
		"pcsConnections"=>"", //varchar
		"otherConnections"=>"", //varchar
		"appearance"=>"", //varchar
		"mannerisms"=>"", //varchar
		"favoritePhrases"=>"", //varchar
		"uniqueAttributes"=>"", //varchar
		"places"=>"", //varchar
		"campaignJournal"=>"", //varchar
		"accomplishments"=>"", //varchar
		"cyphersCharacterUnderstanding"=>"", //varchar
		"artifactsCharacterUnderstanding"=>"", //varchar
		"campaign"=>$cid, //int
		"user"=>$uid, //int
	);
	$s_insert_clause = array_to_insert_clause($a_insert_vals);
	$b_success = db_query("INSERT INTO `[maindb]`.`characters` {$s_insert_clause}",
	                      array_merge($a_insert_vals, array("maindb"=>$maindb)));
	if (!$b_success) {
		error_log("Database error while trying to insert new character sheet!");
		return "Database error during insert";
	}

	// get the id of the newly generated character
	$a_char_ids = db_query("SELECT LAST_INSERT_ID() AS id");
	if (is_array($a_char_ids) && count($a_char_ids) > 0)
		$charid = intval($a_char_ids[0]['id']);
	if ($charid == 0)
		$a_char_ids = db_query("SELECT `id` FROM `[maindb]`.`characters` WHERE `user`='[uid]'",
		                       array("maindb"=>$maindb, "uid"=>$uid))
	if (!is_array($a_char_ids) || count($a_char_ids) == 0) {
		error_log("Database error while trying to get created character's id!");
		return "Database error retrieving new character";
	}

	// Reference the character in other tables.
	// If the other tables can't fit the reference, then remove the character.
	$a_vals = array("uid"=>$uid, "cid"=>$cid, "newCharacter"=>"|{$charid}|");
	$b_success = db_try_concat_str($maindb, "users", "characters", "newCharacter", "WHERE `id`='[uid]'", $a_vals, TRUE); // TODO
	if (!$b_success) {
		db_query("DELETE FROM `[maindb]`.`characters` WHERE `id`='[charid]'",
		         array("maindb"=>$maindb, "charid"=>$charid));
		error_log("Not enough room in user to add character reference!");
		return "Database error while registering character with user";
	}
	$b_success = db_try_concat_str($maindb, "campaigns", "characters", "newCharacter", "WHERE `id`='[cid]'", $a_vals, TRUE); // TODO
	if (!$b_success) {
		db_query("DELETE FROM `[maindb]`.`characters` WHERE `id`='[charid]'",
		         array("maindb"=>$maindb, "charid"=>$charid));
		db_query("UPDATE `[maindb]`.`users` SET `characters`=REPLACE(`characters`,'|[charid]|','') WHERE `id`='[uid]'",
		         array("maindb"=>$maindb, "charid"=>$charid));
		error_log("Not enough room in campaign to add character reference!");
		return "Database error while registering character with campaign";
	}

	return TRUE;
}

function check_create_sheet($cid) {
	global $global_user;

	$a_characters = campaign_funcs::get_characters($cid, FALSE);
	if (is_array($a_characters) && count($a_characters) > 0)
	{
		return;
	}

	// no character found for user, create a new character
	$uid = $global_user->get_id();
	create_character($cid, $uid);
}

?>
