<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/campaign_funcs.php");

class character_funcs {
	public static function Core($a_character) {
		global $global_user;
		global $maindb;

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title">Core</span>

			<div>
			<span><input class="col4" type="text" name="name" value="<?php echo $a_character['name']; ?>" placeholder="name"> is a </span>
			<span><input class="col6" type="text" name="descriptor" value="<?php echo $a_character['descriptor']; ?>" placeholder="descriptor"></span>
			<span><input class="col6" type="text" name="type" value="<?php echo $a_character['type']; ?>" placeholder="type"> who </span>
			<span><input class="col4" type="text" name="focus" value="<?php echo $a_character['focus']; ?>" placeholder="focus"></span>
			</div>

			<div>
			<span class="auto_size">Tier: </span><input class="col3" type="text" name="tier" value="<?php echo $a_character['tier']; ?>" placeholder="tier"></span>
			<span class="auto_size">XP: </span><input class="col3" type="text" name="experience" value="<?php echo $a_character['experience']; ?>" placeholder="experience"></span>
			<span class="auto_size">Effort: </span><input class="fill" type="text" name="effort" value="<?php echo $a_character['effort']; ?>" placeholder="effort"></span>
			</div>

			<div style="margin:22px auto;">
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitIncreaseCapabilities" value="<?php echo "".$a_character['benefitIncreaseCapabilities']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Increase Capabilities</span><br />
				<span class="calculate_center">+4 to stat pools</span>
			</span>
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitMoveTowardPerfection" value="<?php echo "".$a_character['benefitIncreaseCapabilities']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Move Toward Perfection</span><br />
				<span class="calculate_center">+1 edge</span>
			</span>
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitExtraEffort" value="<?php echo "".$a_character['benefitIncreaseCapabilities']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Extra Effort</span><br />
				<span class="calculate_center">+1 effort</span>
			</span>
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitSkillTraining" value="<?php echo "".$a_character['benefitIncreaseCapabilities']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Skill Training</span><br />
				<span class="calculate_center">Train & Specialize</span>
			</span>
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitOther" value="<?php echo "".$a_character['benefitIncreaseCapabilities']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Other</span><br />
				<span class="calculate_center">Various Effects</span>
			</span>
			</div>

			<div style="margin:22px auto; width:100%">
			<span class="col7"></span>
			<span class="fill" style="font-size: 16px; text-align: center; display:inline-block">Recovery Rolls</span>
			<span class="col4" style="font-size: 16px; text-align: center;">Damage</span>
			</div>
			<div>
			<span class="col7 checkCircleContainer" style="vertical-align: top; padding-top: 15px;">
				<span class="calculate_center">Recovery Bonus:</span><br />
				<input class="col7 auto_center" type="text" name="recoveryBonus" value="<?php echo $a_character['recoveryBonus']; ?>" placeholder="recoveryBonus" style="text-align: center;">
			</span>
			<span class="col7 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="recoveryAction" value="<?php echo "".$a_character['recoveryAction']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Recovery Action</span>
			</span>
			<span class="col7 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="recovery10min" value="<?php echo "".$a_character['recovery10min']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">10 Min Recovery</span>
			</span>
			<span class="col7 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="recovery1hr" value="<?php echo "".$a_character['recovery1hr']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">1 Hr Recovery</span>
			</span>
			<span class="col7 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="recovery10hr" value="<?php echo "".$a_character['recovery10hr']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">10 Hr Recovery</span>
			</span>
			<span class="fill checkCircleContainer">
				<span style="display: block;">
					<span class="input checkCircle small sad" name="damageImpaired" value="<?php echo "".$a_character['damageImpaired']; ?>" style="display:inline-block;"></span>
					<span class="fill" style="display:inline-block;">
						<span style="font-weight: bold">Impaired</span><br />
						<span style="font-size: 10px; height:60px;">+1 effort/level, Ignore minor/major effects, Combat rolls 17-20 only deal +1 damage</span>
					</span>
				</span>
				<span style="display: block;">
					<span class="input checkCircle small sad" name="damageDebilitated" value="<?php echo "".$a_character['damageDebilitated']; ?>" style="display:inline-block;"></span>
					<span class="fill" style="display:inline-block;">
						<span style="font-weight: bold">Debilitated</span><br />
						<span style="font-size: 10px; height:60px;">Can't move more than immediate distance, can't move if speed is 0</span>
					</span>
				</span>
			</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;

		// "damageImpaired"=>0, //bit
		// "damageDebilitated"=>0, //bit
		// "statMightPool"=>10, //int
		// "statMightTotal"=>10, //int
		// "statSpeedPool"=>10, //int
		// "statSpeedTotal"=>10, //int
		// "statIntellectPool"=>10, //int
		// "statIntellectTotal"=>10, //int
		// "statMightEdge"=>0, //int
		// "statSpeedEdge"=>0, //int
		// "statIntellectEdge"=>0, //int
		// "statMightNotes"=>"", //varchar
		// "statSpeedNotes"=>"", //varchar
		// "statIntellectNotes"=>"", //varchar
		return "";
	}

	public static function Cyphers($a_character) {
		// "cyphers"=>"", //varchar
		// "cyphersCharacterUnderstanding"=>"", //varchar
		// "cypherLimit"=>"", //varchar
		return "";
	}

	public static function Artifacts($a_character) {
		// "artifacts"=>"", //varchar
		// "artifactsCharacterUnderstanding"=>"", //varchar
		return "";
	}

	public static function Skills($a_character) {
		// "skills"=>"", //varchar
		return "";
	}

	public static function Abilities($a_character) {
		// "abilities"=>"", //varchar
		// "inabilities"=>"", //varchar
		return "";
	}

	public static function Equipment($a_character) {
		// "equipment"=>"", //varchar
		// "shins"=>0, //int
		return "";
	}

	public static function Combat($a_character) {
		// "attacks"=>"", //varchar
		// "armor"=>"", //varchar
		return "";
	}

	public static function Oddities($a_character) {
		// "oddities"=>"", //varchar
		return "";
	}

	public static function Character_Relations($a_character) {
		// "pcs"=>"", //varchar
		// "npcs"=>"", //varchar
		// "pcsConnections"=>"", //varchar
		// "otherConnections"=>"", //varchar
		return "";
	}

	public static function Places($a_character) {
		// "places"=>"", //varchar
		return "";
	}

	public static function Journal($a_character) {
		// "campaignJournal"=>"", //varchar
		return "";
	}

	public static function Description($a_character) {
		// "background"=>"", //varchar
		// "appearance"=>"", //varchar
		// "mannerisms"=>"", //varchar
		// "favoritePhrases"=>"", //varchar
		// "uniqueAttributes"=>"", //varchar
		// "accomplishments"=>"", //varchar
		return "";
	}

	public static function draw_character($a_character) {
		global $global_user;
		global $maindb;

		// check user access permissions
		$uid = $global_user->get_id();
		$cid = intval($a_character['campaign']);
		$char_userid = intval($a_character['user']);
		if ($uid != $char_userid && !campaign_funcs::is_gm($cid))
			return "You aren't authorized to see this character.";

		// draw each group
		$s_page = "";
		$a_group_order = explode(",", $a_character['drawOrder']);
		foreach ($a_group_order as $s_group)
		{
			$s_page .= character_funcs::$s_group($a_character);
		}

		ob_start();
		?>
		<script type="text/javascript">
			// styleing script
			$(document).ready(function() {
				var jautoCenter = $(".auto_center");
				var jautoSize = $(".auto_size");
				var jcalculateCenter = $(".calculate_center");
				var jfill = $(".fill");
				var currWidth = 0;
				var autoSizeFunc = function(k, v) {
					var jelement = $(v);
					jelement.css({"width": jelement.width() + "px"});
				};
				var countWidthFunc = function(k, v) {
					currWidth += $(v).width();
				};
				var fillFunc = function(k, v) {
					var jelement = $(v);
					var jparent = jelement.parent();
					var jsiblings = jelement.siblings();
					var parentWidth = jparent.width();
					currWidth = 0;
					$.each(jsiblings, countWidthFunc);
					jelement.css({"width": (parentWidth - currWidth - (parentWidth / 30)) + "px"});
				};
				var autoCenterFunc = function(k, v) {
					var jelement = $(v);
					var jparent = jelement.parent();
					autoSizeFunc(k, jelement);
					jelement.css({"margin-left": (jparent.width() / 2 - jelement.width() / 2 - 2) + "px"});
				};
				$.each($.merge(jautoCenter, jautoSize), autoSizeFunc);
				$.each(jfill, fillFunc);
				$.each(jcalculateCenter, autoCenterFunc);
			});

			// functional scripts
			$(document).ready(function() {
				var jcheckCircles = $(".checkCircle");
				var jinputs = $("input");
				var updateInputFunc = function(jinput) {

				}
				var updateCheckCircleFunc = function(e) {
					var jelement = $(e.target);
					jelement.attr("value", (parseInt(jelement.attr("value")) == 0) ? "1" : "0");
					updateInputFunc(e);
				}
				jinputs.change(updateInputFunc);
				jcheckCircles.click(updateCheckCircleFunc);
			});
		</script>
		<?php
		$s_page .= ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	/** @return TRUE on success, failure description string otherwise */
	public static function create_character($cid, $uid) {
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
			"drawOrder"=>"Core,Cyphers,Artifacts,Skills,Abilities,Equipment,Combat,Oddities,Character_Relations,Places,Journal,Description", //varchar
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
			                       array("maindb"=>$maindb, "uid"=>$uid));
		if (!is_array($a_char_ids) || count($a_char_ids) == 0) {
			error_log("Database error while trying to get created character's id!");
			return "Database error retrieving new character";
		}

		// Reference the character in other tables.
		// If the other tables can't fit the reference, then remove the character.
		$a_vals = array("uid"=>$uid, "cid"=>$cid, "newCharacter"=>"|{$charid}|");
		$b_success = db_try_concat_str($maindb, "users", "characters", "newCharacter", "WHERE `id`='[uid]'", $a_vals); // TODO
		if (!$b_success) {
			db_query("DELETE FROM `[maindb]`.`characters` WHERE `id`='[charid]'",
			         array("maindb"=>$maindb, "charid"=>$charid));
			error_log("Not enough room in user to add character reference!");
			return "Database error while registering character with user";
		}
		$b_success = db_try_concat_str($maindb, "campaigns", "characters", "newCharacter", "WHERE `id`='[cid]'", $a_vals); // TODO
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

	public static function check_create_character($cid) {
		global $global_user;

		$a_characters = campaign_funcs::get_characters($cid, FALSE);
		if (is_array($a_characters) && count($a_characters) > 0)
		{
			return;
		}

		// no character found for user, create a new character
		$uid = $global_user->get_id();
		character_funcs::create_character($cid, $uid);
	}
}

?>
