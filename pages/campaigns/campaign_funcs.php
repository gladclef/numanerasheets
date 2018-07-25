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
		error_log($b_public);
		error_log($b_passProtected);
		if ($b_public == 0 && $b_passProtected == 1)
			db_query("INSERT INTO `[maindb]`.`campaigns` (`name`,`pass`,`users`,`characters`,`gmUser`,`public`,`shareKey`,`passProtected`) VALUES ('[name]',AES_ENCRYPT('[name]','[pass]'),'[users]','[characters]','[gmUser]','[public]','[shareKey]','[passProtected]')",
					 array('maindb'=>$maindb, 'name'=>$s_name, 'pass'=>$s_pass, 'users'=>"|{$uid}|", 'characters'=>'', 'gmUser'=>$uid, 'public'=>$b_public, 'shareKey'=>$s_key, 'passProtected'=>$b_passProtected), TRUE);
		else
			db_query("INSERT INTO `[maindb]`.`campaigns` (`name`,`pass`,`users`,`characters`,`gmUser`,`public`,`shareKey`,`passProtected`) VALUES ('[name]','','[users]','[characters]','[gmUser]','[public]','[shareKey]','[passProtected]')",
					 array('maindb'=>$maindb, 'name'=>$s_name, 'users'=>"|{$uid}|", 'characters'=>'', 'gmUser'=>$uid, 'public'=>$b_public, 'shareKey'=>$s_key, 'passProtected'=>$b_passProtected), TRUE);
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
}

?>