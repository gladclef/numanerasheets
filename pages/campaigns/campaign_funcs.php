<?php

require_once(dirname(__FILE__).'/../../resources/db_query.php');
require_once(dirname(__FILE__).'/../../resources/globals.php');
require_once(dirname(__FILE__)."/../login/access_object.php");

class campaign_funcs {
	public static function create_campaign($s_name, $b_public, $b_passProtected, $s_pass, &$s_feedback = "") {
		global $maindb;
		global $mysqli;
		global $global_user;
		
		// check that the data is good
		$s_name_status = campaign_funcs::campaign_name_status($s_name);
		if ($s_name_status != 'available') {
			$s_feedback = "Campaign name is {$s_name_status}.";
			return FALSE;
		}
		if ($b_public == '0' && $b_passProtected == '1' && $s_pass == '') {
			$s_feedback = "Empty password.";
			return FALSE;
		}
		
		// create the campaign
		$uid = $global_user->get_id();
		$s_key = md5("hello world".$s_name.date("Y-m-d H:i:s"));
		$s_access = "".date("Y-m-d H:i:s");
		if (!$b_passProtected)
			$s_pass = "";
		db_query("INSERT INTO `[maindb]`.`campaigns` (`name`,`pass`,`users`,`characters`,`gmUser`,`public`,`shareKey`,`passProtected`,`access`) VALUES ('[name]',AES_ENCRYPT('[name]','[pass]'),'[users]','[characters]','[gmUser]','[public]','[shareKey]','[passProtected]','[access]')",
				 array('maindb'=>$maindb, 'name'=>$s_name, 'pass'=>$s_pass, 'users'=>"|{$uid}|", 'characters'=>'', 'gmUser'=>$uid, 'public'=>$b_public, 'shareKey'=>$s_key, 'passProtected'=>$b_passProtected, 'access'=>$s_access));
		if ($mysqli->affected_rows > 0) {
			return TRUE;
		}
		$s_feedback = "Failed to add campaign to database.";
		return FALSE;
	}

	/**
	 * Checks that a campaign name doesn't exist, yet
	 * @$s_name string The campaign name to be checking for
	 * @return  string One of "blank", "taken", or "available"
	 */
	public static function campaign_name_status($s_name) {
		global $maindb;
		if (strlen($s_name) == 0)
				return 'blank';
		$a_names = db_query("SELECT `id` FROM `[maindb]`.`campaigns` WHERE `name`='[name]'",
		                    array('maindb'=>$maindb, 'name'=>$s_name));
		if (count($a_names) > 0)
				return 'taken';
		else
				return 'available';
	}

	/**
	 * Finds all campaigns containing the given name.
	 * @$s_name string The campaign name to be checking for
	 * @return An array of all matching campaigns.
	 */
	public static function search_campaign($s_name, $b_include_private = FALSE) {
		global $maindb;
		$s_include_private = $b_include_private ? "" : "AND `public`='1'";
		$a_campaigns = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE `name` like '%[name]%' {$s_include_private} ORDER BY `access` DESC",
	                            array('maindb'=>$maindb, 'name'=>$s_name));
		return $a_campaigns;
	}

	public static function join_campaign($cid, $s_pass = NULL, $s_shareKey = NULL, $uid = NULL) {
		global $global_user;
		global $maindb;

		$uid = is_null($uid) ? $global_user->get_id() : $uid;
		$a_vals = array("maindb"=>$maindb, "cid"=>$cid, "uid"=>"|{$uid}|", "pass"=>$s_pass, "shareKey"=>$s_shareKey);

		// verify password/share key
		$s_passSearch = is_null($s_pass) ? "" : "AND `pass`=AES_ENCRYPT(CONCAT('rainbow',`name`),'[pass]')";
		$s_shareKeySearch = is_null($s_shareKey) ? "" : "AND `shareKey`='[shareKey]'";
		$a_campaigns = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE `id`='[cid]' {$s_passSearch} {$s_shareKeySearch}", $a_vals);
		if (!is_array($a_campaigns))
			return "There was a database error (1)";
		if (count($a_campaigns) == 0)
		{
			$a_campaigns = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE `id`='[cid]'", $a_vals);
			if (!is_array($a_campaigns))
				return "There was a database error (2)";
			if (intval($a_campaigns[0]['public']) == 0 && $s_shareKey != $a_campaigns[0]['shareKey'])
				return "Incorrect share key for this private campaign";
			if (intval($a_campaigns[0]['passProtected']) == 1)
				return "Incorrect password";
			return "There was a database error (3)";
		}
		if (intval($a_campaigns[0]['public']) == 0 && is_null($s_shareKey))
			return "This campaign is private. You must have the proper share key to join this campaign.";
		if (intval($a_campaigns[0]['passProtected']) == 1 && is_null($s_pass))
			return "This campaign is password protected";

		// attempt to join
		if (campaign_funcs::user_in_campaign($cid))
			return "You are already a part of this campaign.";
		if (!db_try_concat_str($maindb, "campaigns", "users", "uid", "WHERE `id`='[cid]'", $a_vals))
			return "Failed to update database";
		return TRUE;
	}

	public static function user_in_campaign($cid, $uid) {
		global $maindb;

		$a_campaigns = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE `id`='[cid]' AND INSTR(`users`,'|[uid]|')",
		                        array('maindb'=>$maindb, 'cid'=>$cid, 'uid'=>$uid));
		if (!is_array($a_campaigns))
			return FALSE;
		return count($a_campaigns) > 0;
	}

	public static function get_campaigns($cid) {
		global $maindb;

		return db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE `id`='[cid]'",
		                array('maindb'=>$maindb, 'cid'=>$cid));
	}

	public static function get_name($cid) {
		$a_campaigns = campaign_funcs::get_campaigns($cid);
		if (!is_array($a_campaigns))
			return "error";
		return $a_campaigns[0]['name'];
	}

	public static function is_gm($cid)
	{
		global $global_user;

		if (!$global_user)
			return NULL;
		$a_campaigns = campaign_funcs::get_campaigns($cid);
		if (!is_array($a_campaigns))
			return NULL;

		return (intval($a_campaigns[0]['gmUser']) == $global_user->get_id());
	}

	public static function get_characters($cid, $b_is_gm)
	{
		global $global_user;
		global $maindb;

		$uid = $global_user->get_id();
		$s_filter_user = ($b_is_gm) ? "" : "AND `user`='[uid]'";
		$a_characters = db_query("SELECT * FROM `[maindb]`.`characters` WHERE `campaign`='[cid]' {$s_filter_user}",
		                         array("maindb"=>$maindb, "cid"=>$cid, "uid"=>$uid));
		return $a_characters;
	}
}

?>