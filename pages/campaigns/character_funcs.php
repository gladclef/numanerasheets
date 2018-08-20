<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/campaign_funcs.php");

class character_funcs {
	public static function sortBySessionIds($a_entry1, $a_entry2) {
		$i_first = array_search($a_entry1['id'], $_SESSION['sort_ids']);
		$i_second = array_search($a_entry2['id'], $_SESSION['sort_ids']);
		return $i_first - $i_second;
	}

	public static function get_related_table_entries($a_character, $s_table) {
		global $maindb;

		$a_entryIds = explodeIds($a_character[$s_table]);
		$a_entries = array();
		if (trim(strlen($a_character[$s_table])) > 0 && count($a_entryIds) > 0) {
			$s_cypherIds = join("','", $a_entryIds);
			$a_entries = db_query("SELECT * FROM `[maindb]`.`[table]` WHERE `id` IN ('{$s_cypherIds}')",
			                      array("maindb"=>$maindb, "table"=>$s_table));
			$_SESSION['sort_ids'] = $a_entryIds;
			usort($a_entries, "character_funcs::sortBySessionIds");
			unset($_SESSION['sort_ids']);
		}
		return $a_entries;
	}

	public static function Core($a_character) {
		global $global_user;
		global $maindb;

		$a_character2 = escapeTextVals($a_character, array(
			'name',
			'descriptor',
			'type',
			'focus',
			'tier',
			'experience',
			'effort',
			'recoveryBonus',
			'statMightNotes',
			'statSpeedNotes',
			'statIntellectNotes'
		));

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupCore">Core</span>

			<div>
			<span><input class="col4" type="text" name="name" value="<?php echo $a_character2['name']; ?>" placeholder="name"> is a </span>
			<span><input class="col6" type="text" name="descriptor" value="<?php echo $a_character2['descriptor']; ?>" placeholder="descriptor"></span>
			<span><input class="col6" type="text" name="type" value="<?php echo $a_character2['type']; ?>" placeholder="type"> who </span>
			<span><input class="col4" type="text" name="focus" value="<?php echo $a_character2['focus']; ?>" placeholder="focus"></span>
			</div>

			<div>
			<span class="auto_size">Tier: </span><input class="col3" type="text" name="tier" value="<?php echo $a_character2['tier']; ?>" placeholder="tier"></span>
			<span class="auto_size">XP: </span><input class="col3" type="text" name="experience" value="<?php echo $a_character2['experience']; ?>" placeholder="experience"></span>
			<span class="auto_size">Effort: </span><input class="fill" type="text" name="effort" value="<?php echo $a_character2['effort']; ?>" placeholder="effort"></span>
			</div>

			<div style="margin:22px auto;">
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitIncreaseCapabilities" value="<?php echo "".$a_character2['benefitIncreaseCapabilities']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Increase Capabilities</span><br />
				<span class="calculate_center">+4 to stat pools</span>
			</span>
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitMoveTowardPerfection" value="<?php echo "".$a_character2['benefitMoveTowardPerfection']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Move Toward Perfection</span><br />
				<span class="calculate_center">+1 edge</span>
			</span>
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitExtraEffort" value="<?php echo "".$a_character2['benefitExtraEffort']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Extra Effort</span><br />
				<span class="calculate_center">+1 effort</span>
			</span>
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitSkillTraining" value="<?php echo "".$a_character2['benefitSkillTraining']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Skill Training</span><br />
				<span class="calculate_center">Train & Specialize</span>
			</span>
			<span class="col5 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="benefitOther" value="<?php echo "".$a_character2['benefitOther']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Other</span><br />
				<span class="calculate_center">Various Effects</span>
			</span>
			</div>

			<div style="margin:22px auto; width:100%">
			<span class="col7"></span>
			<span class="fill mediumText" style="text-align: center; display:inline-block">Recovery Rolls</span>
			<span class="col4 mediumText" style="text-align: center;">Damage</span>
			</div>
			<div>
			<span class="col7 checkCircleContainer" style="vertical-align: top; padding-top: 15px;">
				<span class="calculate_center">Recovery Bonus:</span><br />
				<input class="col7 auto_center" type="text" name="recoveryBonus" value="<?php echo $a_character2['recoveryBonus']; ?>" placeholder="recoveryBonus" style="text-align: center;">
			</span>
			<span class="col7 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="recoveryAction" value="<?php echo "".$a_character2['recoveryAction']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">Recovery Action</span>
			</span>
			<span class="col7 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="recovery10min" value="<?php echo "".$a_character2['recovery10min']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">10 Min Recovery</span>
			</span>
			<span class="col7 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="recovery1hr" value="<?php echo "".$a_character2['recovery1hr']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">1 Hr Recovery</span>
			</span>
			<span class="col7 checkCircleContainer">
				<span class="input checkCircle calculate_center" name="recovery10hr" value="<?php echo "".$a_character2['recovery10hr']; ?>"></span><br />
				<span class="calculate_center" style="font-weight: bold">10 Hr Recovery</span>
			</span>
			<span class="fill checkCircleContainer">
				<span style="display: block;">
					<span class="input checkCircle small sad" name="damageImpaired" value="<?php echo "".$a_character2['damageImpaired']; ?>" style="display:inline-block;"></span>
					<span class="fill" style="display:inline-block;">
						<span style="font-weight: bold">Impaired</span><br />
						<span style="font-size: 10px; height:60px;">+1 effort/level, Ignore minor/major effects, Combat rolls 17-20 only deal +1 damage</span>
					</span>
				</span>
				<span style="display: block;">
					<span class="input checkCircle small sad" name="damageDebilitated" value="<?php echo "".$a_character2['damageDebilitated']; ?>" style="display:inline-block;"></span>
					<span class="fill" style="display:inline-block;">
						<span style="font-weight: bold">Debilitated</span><br />
						<span style="font-size: 10px; height:60px;">Can't move more than immediate distance, can't move if speed is 0</span>
					</span>
				</span>
			</span>
			</div>

			<div>
			<span class="col3 largeText" style="text-align: center;">Might</span>
			<span class="col3 largeText" style="text-align: center;">Speed</span>
			<span class="col3 largeText" style="text-align: center;">Intellect</span>
			</div>
			<div style="margin-top:-10px">
			<span class="col3">
				<span class="col3"><span>Pool: </span><input class="fill" type="text" name="statMightPool" value="<?php echo $a_character2['statMightPool']; ?>" placeholder="Might Pool"></span>
				<hr class="col3" />
				<span class="col3"><span>Total: </span><input class="fill" type="text" name="statMightTotal" value="<?php echo $a_character2['statMightTotal']; ?>" placeholder="Might Total"></span>
				<span class="col3"><span>Edge: </span><input class="fill" type="text" name="statMightEdge" value="<?php echo $a_character2['statMightEdge']; ?>" placeholder="Might Edge"></span>
				<input class="col3" type="text" name="statMightNotes" style="font-size:12px; margin-top:3px" value="<?php echo $a_character2['statMightNotes']; ?>" placeholder="Notes">
			</span>
			<span class="col3">
				<span class="col3"><span>Pool: </span><input class="fill" type="text" name="statSpeedPool" value="<?php echo $a_character2['statSpeedPool']; ?>" placeholder="Speed Pool"></span>
				<hr class="col3" />
				<span class="col3"><span>Total: </span><input class="fill" type="text" name="statSpeedTotal" value="<?php echo $a_character2['statSpeedTotal']; ?>" placeholder="Speed Total"></span>
				<span class="col3"><span>Edge: </span><input class="fill" type="text" name="statSpeedEdge" value="<?php echo $a_character2['statSpeedEdge']; ?>" placeholder="Speed Edge"></span>
				<input class="col3" type="text" name="statSpeedNotes" style="font-size:12px; margin-top:3px" value="<?php echo $a_character2['statSpeedNotes']; ?>" placeholder="Notes">
			</span>
			<span class="col3">
				<span class="col3"><span>Pool: </span><input class="fill" type="text" name="statIntellectPool" value="<?php echo $a_character2['statIntellectPool']; ?>" placeholder="Intellect Pool"></span>
				<hr class="col3" />
				<span class="col3"><span>Total: </span><input class="fill" type="text" name="statIntellectTotal" value="<?php echo $a_character2['statIntellectTotal']; ?>" placeholder="Intellect Total"></span>
				<span class="col3"><span>Edge: </span><input class="fill" type="text" name="statIntellectEdge" value="<?php echo $a_character2['statIntellectEdge']; ?>" placeholder="Intellect Edge"></span>
				<input class="col3" type="text" name="statIntellectNotes" style="font-size:12px; margin-top:3px" value="<?php echo $a_character2['statIntellectNotes']; ?>" placeholder="Notes">
			</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_cyphers($a_cyphers) {
		ob_start();

		foreach ($a_cyphers as $a_cypher) {
		$a_cypher2 = escapeTextVals($a_cypher, array(
			'name',
			'level',
			'description',
			'partyUnderstanding'
		));
		?>
		<div style="border-left:1px solid black; padding-left:5px; position:relative;">
			<span class="collapsibleHeader" collapseid="cyph<?php echo $a_cypher2['id']; ?>">
				<span class="auto_size">Name: </span>
				<input class="col2" type="text" name="name" value="<?php echo $a_cypher2['name']; ?>" placeholder="name" table="cyphers" rowid="<?php echo $a_cypher2['id']; ?>">
				<span class="closeButton" onclick="remove(this, <?php echo $a_cypher2['id']; ?>, 'cyphers', 'Cypher');">X</span>
			</span>
			<div class="collapsibleBody">

				<div>
				<span class="auto_size">Level: </span><input class="col5" type="text" name="level" value="<?php echo $a_cypher2['level']; ?>" placeholder="level" table="cyphers" rowid="<?php echo $a_cypher2['id']; ?>">
				</div>

				<div class="mediumText" style="margin:0;"><span>Actual description</span></div>
				<div><textarea class="fill" rows="3" name="description" placeholder="description" table="cyphers" rowid="<?php echo $a_cypher2['id']; ?>"><?php echo $a_cypher2['description']; ?></textarea></div>

				<div class="mediumText" style="margin:0;"><span>Party members' understanding of cypher</span></div>
				<div><textarea class="fill" rows="3" name="partyUnderstanding" placeholder="understanding" table="cyphers" rowid="<?php echo $a_cypher2['id']; ?>"><?php echo $a_cypher2['partyUnderstanding']; ?></textarea></div>

				</span><span style="width:50px; display:inline-block; position:absolute;">

			</div>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Cyphers($a_character) {
		global $global_user;
		global $maindb;

		$a_cyphers = character_funcs::get_related_table_entries($a_character, "cyphers");
		$a_character2 = escapeTextVals($a_character, array(
			'cypherLimit',
		));

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupCyph">Cyphers</span>

			<div>
			<span class="col3"></span><span class="col3"></span>
			<span class="auto_size">Cypher Limit: </span><input class="fill" type="text" name="cypherLimit" value="<?php echo $a_character2['cypherLimit']; ?>" placeholder="cypherLimit"></span>
			</div>

			<div id="cypher_elements" class="elements">
			<?php
			echo character_funcs::draw_cyphers($a_cyphers);
			?>
			</div>
			
			<div>
			<span class="auto_center largeText" onclick="addNew('cyphers', 'Cypher', 'cypher_elements');" style="color:blue; text-decoration:underline; width:165px; cursor:pointer;">Add New Cypher</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_artifacts($a_artifacts) {
		ob_start();

		foreach ($a_artifacts as $a_artifact) {
		$a_artifact2 = escapeTextVals($a_artifact, array(
			'name',
			'depletion',
			'description',
			'partyUnderstanding'
		));
		?>
		<div style="border-left:1px solid black; padding-left:5px; position:relative;">
			<span class="collapsibleHeader" collapseid="art<?php echo $a_artifact2['id']; ?>">
				<span class="auto_size">Name: </span>
				<input class="col2" type="text" name="name" value="<?php echo $a_artifact2['name'] ?>" placeholder="name" table="artifacts" rowid="<?php echo $a_artifact2['id']; ?>">
				<span class="closeButton" onclick="remove(this, <?php echo $a_artifact2['id']; ?>, 'artifacts', 'Artifact');">X</span>
			</span>
			<div class="collapsibleBody">

				<div>
				<span class="auto_size">Depletion: </span><input class="col5" type="text" name="depletion" value="<?php echo $a_artifact2['depletion']; ?>" placeholder="depletion" table="artifacts" rowid="<?php echo $a_artifact2['id']; ?>">
				</div>

				<div class="mediumText" style="margin:0;"><span>Actual description</span></div>
				<div><textarea class="fill" rows="3" name="description" placeholder="description" table="artifacts" rowid="<?php echo $a_artifact2['id']; ?>"><?php echo $a_artifact2['description']; ?></textarea></div>

				<div class="mediumText" style="margin:0;"><span>Party members' understanding of artifact</span></div>
				<div><textarea class="fill" rows="3" name="partyUnderstanding" placeholder="understanding" table="artifacts" rowid="<?php echo $a_artifact2['id']; ?>"><?php echo $a_artifact2['partyUnderstanding']; ?></textarea></div>

				</span><span style="width:50px; display:inline-block; position:absolute;">

			</div>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Artifacts($a_character) {
		global $global_user;
		global $maindb;

		$a_artifacts = character_funcs::get_related_table_entries($a_character, "artifacts");

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupArt">Artifacts</span>

			<div id="artifact_elements" class="elements">
			<?php
			echo character_funcs::draw_artifacts($a_artifacts);
			?>
			</div>
			
			<div>
			<span class="auto_center largeText" onclick="addNew('artifacts', 'Artifact', 'artifact_elements');" style="color:blue; text-decoration:underline; width:165px; cursor:pointer;">Add New Artifact</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_skills($a_skills) {
		ob_start();

		foreach ($a_skills as $a_skill) {
		$a_skill2 = escapeTextVals($a_skill, array(
			'description'
		));
		?>
		<div class="mediumText"><span
			class="auto_size">Description: </span><input class="fill" type="text" name="description" value="<?php echo $a_skill2['description']; ?>" placeholder="skill" table="skills" rowid="<?php echo $a_skill2['id']; ?>"><span
			class="auto_size">T:</span><input type="checkbox" name="trained" <?php echo $a_skill2['trained'] ? "checked" : ""; ?> tooltip="trained" table="skills" rowid="<?php echo $a_skill2['id']; ?>"><span
			class="auto_size">S:</span><input type="checkbox" name="skilled" <?php echo $a_skill2['skilled'] ? "checked" : ""; ?> tooltip="skilled" table="skills" rowid="<?php echo $a_skill2['id']; ?>"><span
			class="auto_size">I:</span><input type="checkbox" name="inability" <?php echo $a_skill2['inability'] ? "checked" : ""; ?> tooltip="inability" table="skills" rowid="<?php echo $a_skill2['id']; ?>"><span
			class="closeButton small" onclick="remove(this, <?php echo $a_skill2['id']; ?>, 'skills', 'Skill', true);" style="margin-top:7px;">X</span>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Skills($a_character) {
		global $global_user;
		global $maindb;

		$a_skills = character_funcs::get_related_table_entries($a_character, "skills");

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupSkill">Skills</span>

			<div id="skill_elements" class="elements">
			<?php
			echo character_funcs::draw_skills($a_skills);
			?>
			</div>
			
			<div>
			<span class="auto_center largeText" onclick="addNew('skills', 'Skill', 'skill_elements');" style="color:blue; text-decoration:underline; width:165px; cursor:pointer;">
				Add New Skill
			</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_abilities($a_abilities) {
		ob_start();

		foreach ($a_abilities as $a_ability) {
		$a_ability2 = escapeTextVals($a_ability, array(
			'name',
			'cost',
			'description'
		));
		?>
		<div style="border-left:1px solid black; padding-left:5px; position:relative;">
			<span class="collapsibleHeader" collapseid="abil<?php echo $a_ability2['id']; ?>">
				<span class="auto_size">Name: </span>
				<input class="col2" type="text" name="name" value="<?php echo $a_ability2['name']; ?>" placeholder="name" table="abilities" rowid="<?php echo $a_ability2['id']; ?>">
				<span class="closeButton" onclick="remove(this, <?php echo $a_ability2['id']; ?>, 'abilities', 'Ability');">X</span>
			</span>
			<div class="collapsibleBody">

				<div>
				<span class="auto_size">Cost: </span><input class="col5" type="text" name="cost" value="<?php echo $a_ability2['cost']; ?>" placeholder="depletion" table="abilities" rowid="<?php echo $a_ability2['id']; ?>">
				</div>

				<div><span>Description</span></div>
				<div><textarea class="fill" rows="4" name="description" placeholder="description" table="abilities" rowid="<?php echo $a_ability2['id']; ?>"><?php echo $a_ability2['description']; ?></textarea></div>

			</div>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_inabilities($a_inabilities) {
		ob_start();

		foreach ($a_inabilities as $a_inability) {
		$a_inability2 = escapeTextVals($a_inability, array(
			'name',
			'description'
		));
		?>
		<div style="border-left:1px solid black; padding-left:5px; position:relative;">
			<span class="collapsibleHeader" collapseid="abil<?php echo $a_inability2['id']; ?>">
				<span class="auto_size">Name: </span>
				<input class="col2" type="text" name="name" value="<?php echo $a_inability2['name']; ?>" placeholder="name" table="inabilities" rowid="<?php echo $a_inability2['id']; ?>">
				<span class="closeButton" onclick="remove(this, <?php echo $a_inability2['id']; ?>, 'inabilities', 'Inability');">X</span>
			</span>
			<div class="collapsibleBody">
				<div><span>Description</span></div>
				<div><textarea class="fill" rows="4" name="description" placeholder="description" table="inabilities" rowid="<?php echo $a_inability2['id']; ?>"><?php echo $a_inability2['description']; ?></textarea></div>
			</div>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Abilities($a_character) {
		global $global_user;
		global $maindb;

		$a_abilities = character_funcs::get_related_table_entries($a_character, "abilities");
		$a_inabilities = character_funcs::get_related_table_entries($a_character, "inabilities");

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupAbil">Abilities</span>

			<div id="ability_elements" class="elements">
			<?php
			echo character_funcs::draw_abilities($a_abilities);
			?>
			</div>
			
			<div>
			<span class="auto_center largeText" onclick="addNew('abilities', 'Ability', 'ability_elements');" style="color:blue; text-decoration:underline; width:165px; cursor:pointer;">Add New Ability</span>
			</div>

			<hr class="col2 auto_center" style="display:block;">
			<span class="auto_center smallTitle">Inabilities</span>
			<div id="inability_elements" class="elements">
			<?php
			echo character_funcs::draw_inabilities($a_inabilities);
			?>
			</div>
			
			<div>
			<span class="auto_center largeText" onclick="addNew('inabilities', 'Inability', 'inability_elements');" style="color:blue; text-decoration:underline; width:175px; cursor:pointer;">Add New Inability</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_equipment($a_equipments) {
		ob_start();

		foreach ($a_equipments as $a_equipment) {
		$a_equipment2 = escapeTextVals($a_equipment, array(
			'name',
			'description'
		));
		$s_height = max(floor(strlen($a_equipment2['description']) / 40), 1) * 16;
		?>
		<div class="mediumText"><span
			class="auto_size">Name: </span><input class="col3" type="text" name="name" value="<?php echo $a_equipment2['name']; ?>" placeholder="equipment" table="equipment" rowid="<?php echo $a_equipment2['id']; ?>"><span
			class="auto_size">Description: </span><textarea class="fill smallTextarea" style="height:<?php echo $s_height; ?>px;" name="description" placeholder="description" table="equipment" rowid="<?php echo $a_equipment2['id']; ?>"><?php echo $a_equipment2['description']; ?></textarea><span
			class="closeButton small" onclick="remove(this, <?php echo $a_equipment2['id']; ?>, 'equipment', 'Equipment', true);" style="margin-top:7px;">X</span>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Equipment($a_character) {
		global $global_user;
		global $maindb;

		$a_equipment = character_funcs::get_related_table_entries($a_character, "equipment");

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupEquip">Equipment</span>

			<div id="equipment_elements" class="elements">
			<?php
			echo character_funcs::draw_equipment($a_equipment);
			?>
			</div>
			
			<div>
			<span class="auto_center largeText" onclick="addNew('equipment', 'Equipment', 'equipment_elements');" style="color:blue; text-decoration:underline; width:200px; cursor:pointer;">Add New Equipment</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_attacks($a_attacks) {
		ob_start();

		foreach ($a_attacks as $a_attack) {
		$a_attack2 = escapeTextVals($a_attack, array(
			'name',
			'damage',
			'modifier',
			'notes'
		));
		?>
		<div style="border-left:1px solid black; margin-bottom:15px; padding-left:5px;">
			<span class="col2 mediumText"><span
				class="auto_size">Name: </span><input class="fill" type="text" name="name" value="<?php echo $a_attack2['name']; ?>" placeholder="name" table="attacks" rowid="<?php echo $a_attack2['id']; ?>"><span
				class="closeButton small" onclick="remove(this, <?php echo $a_attack2['id']; ?>, 'attacks', 'Attack', true);" style="margin-top:7px;">X</span>
			</span>
			<div class="mediumText"><span
				class="auto_size">Damage: </span><input class="col6" type="text" name="damage" value="<?php echo $a_attack2['damage']; ?>" placeholder="damage" table="attacks" rowid="<?php echo $a_attack2['id']; ?>"><span
				class="auto_size">Modifier: </span><input class="col6" type="text" name="modifier" value="<?php echo $a_attack2['modifier']; ?>" placeholder="modifier" table="attacks" rowid="<?php echo $a_attack2['id']; ?>">
			</div>
			<div class="mediumText">
				<span>Notes: </span><br />
				<textarea class="col2" name="notes" placeholder="notes" table="attacks" rowid="<?php echo $a_attack2['id']; ?>" rows="2"><?php echo $a_attack2['notes']; ?></textarea>
			</div>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_armor($a_armors) {
		ob_start();

		foreach ($a_armors as $a_armor) {
		$a_armor2 = escapeTextVals($a_armor, array(
			'name',
			'cost',
			'modifier',
			'speedReduction',
			'notes'
		));
		?>
		<div style="border-left:1px solid black; margin-bottom:15px; padding-left:5px;">
			<span class="mediumText col2"><span
				class="auto_size">Name: </span><input class="col6" type="text" name="name" value="<?php echo $a_armor2['name']; ?>" placeholder="name" table="armor" rowid="<?php echo $a_armor2['id']; ?>"><span
				class="auto_size">Cost: </span><input class="fill" type="text" name="cost" value="<?php echo $a_armor2['cost']; ?>" placeholder="cost" table="armor" rowid="<?php echo $a_armor2['id']; ?>"><span
				class="closeButton small" onclick="remove(this, <?php echo $a_armor2['id']; ?>, 'armor', 'Armor', true);" style="margin-top:7px;">X</span>
			</span>
			<div class="mediumText"><span
				class="auto_size">Modifier: </span><input class="col6" type="text" name="modifier" value="<?php echo $a_armor2['modifier']; ?>" placeholder="modifier" table="armor" rowid="<?php echo $a_armor2['id']; ?>"><span
				class="auto_size">Speed Reduction: </span><input class="fill" type="text" name="speedReduction" value="<?php echo $a_armor2['speedReduction']; ?>" placeholder="speed reduction" table="armor" rowid="<?php echo $a_armor2['id']; ?>">
			</div>
			<div class="mediumText">
				<span>Notes: </span><br />
				<textarea class="col2" name="notes" placeholder="notes" table="armor" rowid="<?php echo $a_armor2['id']; ?>" rows="2"><?php echo $a_armor2['notes']; ?></textarea>
			</div>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Combat($a_character) {
		global $global_user;
		global $maindb;

		$a_attacks = character_funcs::get_related_table_entries($a_character, "attacks");
		$a_armors = character_funcs::get_related_table_entries($a_character, "armor");

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupCom">Combat</span>
			<div class="col2" style="display: inline-block; vertical-align: top;">
				<span class="auto_center smallTitle">Attacks</span>

				<div id="attack_elements" class="elements">
				<?php
				echo character_funcs::draw_attacks($a_attacks);
				?>
				</div>
				
				<div>
				<span class="auto_center largeText" onclick="addNew('attacks', 'Attack', 'attack_elements');" style="color:blue; text-decoration:underline; width:160px; cursor:pointer;">Add New Attack</span>
				</div>

			</div>
			<div class="col2 smallTitle">
				<span class="auto_center" style="color:gray; font-weight:bold; font-size:30px;">Armors</span>

				<div id="armor_elements" class="elements">
				<?php
				echo character_funcs::draw_armor($a_armors);
				?>
				</div>
				
				<div>
				<span class="auto_center largeText" onclick="addNew('armor', 'Armor', 'armor_elements');" style="color:blue; text-decoration:underline; width:160px; cursor:pointer;">Add New Armor</span>
				</div>

			</div>
		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_oddities($a_oddities) {
		ob_start();

		foreach ($a_oddities as $a_oddity) {
		$a_oddity2 = escapeTextVals($a_oddity, array(
			'name',
			'description'
		));
		$s_height = max(ceil(strlen($a_oddity2['description']) / 40), 1) * 16;
		?>
		<div class="mediumText"><span
			class="auto_size">Name: </span><input class="col3" type="text" name="name" value="<?php echo $a_oddity2['name']; ?>" placeholder="oddity" table="oddities" rowid="<?php echo $a_oddity2['id']; ?>"><span
			class="auto_size">Description: </span><textarea class="fill smallTextarea" style="height:<?php echo $s_height; ?>px;" type="text" name="description" placeholder="description" table="oddities" rowid="<?php echo $a_oddity2['id']; ?>" style="height:<?php echo $s_height; ?>px;"><?php echo $a_oddity2['description']; ?></textarea><span
			class="closeButton small" onclick="remove(this, <?php echo $a_oddity2['id']; ?>, 'oddities', 'Oddity', true);" style="margin-top:7px;">X</span>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Oddities($a_character) {
		global $global_user;
		global $maindb;

		$a_oddities = character_funcs::get_related_table_entries($a_character, "oddities");

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupOdd">Oddities</span>

			<div id="oddity_elements" class="elements">
			<?php
			echo character_funcs::draw_oddities($a_oddities);
			?>
			</div>
			
			<div>
			<span class="auto_center largeText" onclick="addNew('oddities', 'Oddity', 'oddity_elements');" style="color:blue; text-decoration:underline; width:200px; cursor:pointer;">Add New Oddity</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Character_Relations($a_character) {
		global $global_user;
		global $maindb;

		$a_relations = array(
			"PCs"=>array("key"=>"pcs"),
			"NPCs"=>array("key"=>"npcs"),
			"PCs Connections"=>array("key"=>"pcsConnections"),
			"Other Connections"=>array("key"=>"otherConnections")
		);

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" style="width:355px;" collapseid="GroupRel">Character Relations</span>

			<div id="character_relations">
			<?php
			foreach ($a_relations as $s_title=>$a_val) {
			$s_key = $a_val["key"];
			$a_character2 = escapeTextVals($a_character, array(
				$s_key
			));
			?>
				<div>
					<?php echo "<span class=\"collapsibleHeader\" collapseid=\"Rel{$s_key}\" style=\"font-size:30px;\">{$s_title}</span>" ?>
					<div class="collapsibleBody">
						<?php echo "<textarea class=\"fill collapsibleBody\" name=\"{$s_key}\" placeholder=\"{$s_title}\" rowid=\"{$a_character2['id']}\" rows=\"15\">{$a_character2[$s_key]}</textarea>"; ?>
					</div>
				</div>
			<?php
			}
			?>
			</div>
		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_places($a_places) {
		ob_start();

		foreach ($a_places as $a_place) {
		$a_place2 = escapeTextVals($a_place, array(
			'name',
			'description'
		));
		?>
		<div>
			<span class="collapsibleHeader" collapseid="place<?php echo $a_place2['id']; ?>">
				<span class="auto_size">Name: </span>
				<input class="col2" type="text" name="name" value="<?php echo $a_place2['name']; ?>" placeholder="name" table="places" rowid="<?php echo $a_place2['id']; ?>">
				<span class="closeButton" onclick="remove(this, <?php echo $a_place2['id']; ?>, 'places', 'Place', true);">X</span>
			</span>
			<div class="collapsibleBody" style="padding:5px 0 5px 5px;">
				<textarea class="fill" rows="15" name="description" placeholder="description" table="places" rowid="<?php echo $a_place2['id']; ?>"><?php echo $a_place2['description']; ?></textarea>
			</div>
		</div>
		<?php
		}

		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Places($a_character) {
		global $global_user;
		global $maindb;

		$a_places = character_funcs::get_related_table_entries($a_character, "places");

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupPlace">Places</span>

			<div id="place_elements" class="elements">
			<?php
			echo character_funcs::draw_places($a_places);
			?>
			</div>
			
			<div>
			<span class="auto_center largeText" onclick="addNew('places', 'Place', 'place_elements');" style="color:blue; text-decoration:underline; width:147px; cursor:pointer;">Add New Place</span>
			</div>

		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Journal($a_character) {
		global $global_user;
		global $maindb;

		$a_character2 = escapeTextVals($a_character, array(
			'campaignJournal'
		));

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupJour">Journal</span>

			<div>
				<?php echo "<textarea class=\"fill collapsibleBody\" name=\"campaignJournal\" placeholder=\"campaign journal\" rowid=\"{$a_character2['id']}\" rows=\"40\">{$a_character2['campaignJournal']}</textarea>"; ?>
			</div>
		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function Description($a_character) {
		global $global_user;
		global $maindb;

		$a_descriptions = array(
			"Background"=>array("key"=>"background", "rows"=>"10"),
			"Appearance"=>array("key"=>"appearance", "rows"=>"5"),
			"Mannerisms"=>array("key"=>"mannerisms", "rows"=>"5"),
			"Favorite Phrases"=>array("key"=>"favoritePhrases", "rows"=>"10"),
			"Unique Attributes"=>array("key"=>"uniqueAttributes", "rows"=>"5"),
			"Accomplishments"=>array("key"=>"accomplishments", "rows"=>"5")
		);

		ob_start();
		?>
		<div class="descriptor_group">
			<span class="auto_center title" collapseid="GroupDesc">Description</span>

			<div id="character_relations">
			<?php
			foreach ($a_descriptions as $s_title=>$a_val) {
			$s_key = $a_val["key"];
			$a_character2 = escapeTextVals($a_character, array(
				$s_key
			));
			?>
				<div>
					<?php echo "<span class=\"collapsibleHeader\" style=\"font-size:30px;\" collapseid=\"Desc{$a_val['key']}\">{$s_title}</span>" ?>
					<div class="collapsibleBody">
						<?php echo "<textarea class=\"fill\" name=\"{$s_key}\" placeholder=\"{$s_title}\" rowid=\"{$a_character2['id']}\" rows=\"{$a_val['rows']}\">{$a_character2[$s_key]}</textarea>"; ?>
					</div>
				</div>
			<?php
			}
			?>
			</div>
		</div>
		<?php
		$s_page = ob_get_contents();
		ob_end_clean();

		return $s_page;
	}

	public static function draw_character($a_character) {
		global $global_user;
		global $maindb;

		// check user access permissions
		$uid = $global_user->get_id();
		$cid = intval($a_character['campaign']);
		$charid = intval($a_character['id']);
		$b_has_access = strpos($a_character['users'], "|{$uid}|") !== FALSE;
		if (!$b_has_access && !campaign_funcs::is_gm($cid))
			return "You aren't authorized to see this character.";

		// draw each group
		$s_page = "";
		$s_campaign_name = htmlspecialchars(campaign_funcs::get_name($cid));
		$a_group_order = explode(",", $a_character['drawOrder']);
		foreach ($a_group_order as $s_group)
		{
			$s_page .= character_funcs::$s_group($a_character);
		}

		ob_start();
		?>
		<div class="hidden" id="character_id" value="<?php echo $charid; ?>"></div>
		<script type="text/javascript">
			// styleing script
			window.startupStyling = function() {
				var jtitles = $(".title");
				var jautoCenter = $(".auto_center");
				var jautoSize = $(".auto_size");
				var jcalculateCenter = $(".calculate_center");
				var jfill = $(".fill");
				var jcollapsibleHeaders = $(".collapsibleHeader");
				var currWidth = 0;
				var jerrors_label = $("#floater").find(".floater_errors");
				var registerCollapsibles = function() {
					var collapseStr = "";
					var registerCollapsed = function(k, v) {
						var jheader = $(v);
						if (jheader.hasClass("collapsed") || jheader.parent().hasClass("collapsed"))
							collapseStr += "|" + jheader.attr("collapseid") + "|";
					};
					$.each(jtitles, registerCollapsed);
					$.each(jcollapsibleHeaders, registerCollapsed);
					if (window.collapseUpdate != undefined)
						clearTimeout(window.collapseUpdate);
					window.collapseUpdate = setTimeout(function() {
						sendUpdate("collapseIds", collapseStr, "", "<?php echo $charid; ?>", jerrors_label);
					}, 500);
				};
				var collapseTitleFunc = function(e) {
					var jelement = $(e.target);
					var jgroup = jelement.parent();
					var jother = jelement.siblings();
					jother.stop();
					if (jgroup.hasClass("collapsed")) {
						jgroup.removeClass("collapsed");
						jother.show(200);
					} else {
						jgroup.addClass("collapsed");
						jother.hide(200);
					}
					registerCollapsibles();
				};
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
				var collapseFloaterFunc = function(e) {
					jfloater.stop();
					var newWidth = (jfloater.hasClass("collapsed")) ? 250 : 50;
					jfloater.animate({ width: (newWidth) + "px" });
				};
				var collapseCollapsible = function(e) {
					var jheader = $(e.target);
					var jbody = jheader.siblings();
					jbody.stop();
					if (jheader.hasClass("collapsed")) {
						jheader.removeClass("collapsed");
						jbody.show(200, function() {
							if (parseFloat(jbody.css("opacity")) < 0.00001)
								jbody.attr("style", "");
						});
					} else {
						jheader.addClass("collapsed");
						jbody.hide(200);
					}
					registerCollapsibles();
				};
				var collapsibleFunc = function(k, v) {
					var jheader = $(v);
					jheader.click(collapseCollapsible);
					jheader.children().click(function(event) { event.stopPropagation(); });
				};
				var collapseTitlesFunc = function(k, v) {
					var jtitle = $(v);
					jtitle.click(collapseTitleFunc);
				};
				window.autoCollapse = function(k, v) {
					var jelement = $(v);
					if (typeof window.collapseids === 'string' || window.collapseids instanceof String) {
						if (window.collapseids.length == 0)
							window.collapseids = [];
						else
							window.collapseids = window.collapseids.replace(/^\|/,"").replace(/\|$/,"").split("||");
					}
					if (window.collapseids.indexOf(jelement.attr("collapseid")) >= 0 && !jelement.hasClass("collapsed"))
						jelement.click();
				};
				window.collapseAll = function() {
					setTimeout(function() { $.each($(".title"), autoCollapse); }, 500);
					setTimeout(function() { $.each($(".collapsibleHeader"), autoCollapse); }, 500);
				};
				var setWindowTitle = function() {
					window.basicTitle = "<?php echo $s_campaign_name; ?> (" + window.location.href + ")";
					document.title = window.basicTitle;
				}
				$.each(jtitles, collapseTitlesFunc);
				$.each($.merge(jautoCenter, jautoSize), autoSizeFunc);
				$.each(jfill, fillFunc);
				$.each(jcalculateCenter, autoCenterFunc);
				$.each(jcollapsibleHeaders, collapsibleFunc);
				setWindowTitle();
			}
			$(document).ready(startupStyling);

			// functional scripts
			var startupFunctionality = function() {
				var jcheckCircles = $(".checkCircle");
				var jinputs = $("input");
				var jtextareas = $("textarea");
				var all = $(".checkCircle, input, textarea");
				var jerrors_label = $("#floater").find(".floater_errors");
				var jtitles = $(".title");
				var jcollapsibleHeaders = $(".collapsibleHeader");
				window.collapseids = "<?php echo $a_character['collapseIds']; ?>";
				all = all.filter(":not(.touched)");
				window.sendUpdate = function(name, val, table, rowid, jerrors_label) {
					posts = {
						"command": "update_character_sheet",
						"campaign_id": "<?php echo $cid; ?>",
						"character_id": "<?php echo $charid; ?>",
						"property": name,
						"value": val,
						"table": table,
						"rowid": rowid
					};
					set_html_and_fade_in(jerrors_label, "", "<span style='color:gray;font-weight:normal;'>syncing...</span>");
					send_async_ajax_call("ajax.php", posts, true, function(retval) {
						interpret_commands(retval, jerrors_label);
					});
				};
				var updateInputFunc = function(jelement) {
					var name = jelement.attr("name");
					var val = "";
					if (jelement.hasClass("checkCircle")) {
						val = jelement.attr("value");
					} else if (jelement.attr("type") == "checkbox") {
						val = jelement.is(":checked") ? 1 : 0;
					} else {
						val = jelement.val();
					}
					var table = (jelement.attr("table") === undefined) ? "" : jelement.attr("table");
					var rowid = (jelement.attr("rowid") === undefined) ? "" : jelement.attr("rowid");
					sendUpdate(name, val, table, rowid, jerrors_label);
				}
				var updateTimeoutFunc = function(className) {
					var doUpdate = function(k, v) {
						var jelement = $(v);
						updateInputFunc(jelement);
						jelement.removeClass(className);
					};
					var updateElements = $("." + className);
					if (updateElements.length > 0) $.each(updateElements, doUpdate);
					document.title = window.basicTitle;
				};
				var updateInputTimeoutFunc = function() {
					updateTimeoutFunc("updateInputWaiting");
				};
				var updateCheckCircleTimeoutFunc = function() {
					updateTimeoutFunc("updateCircleWaiting");
				};
				var setUpdateTimeout = function(e, timeoutName, className, updateFunc) {
					if (window[timeoutName] != undefined)
						clearTimeout(window[timeoutName]);
					$(e.target).addClass(className);
					if (document.title == window.basicTitle)
						document.title = "*" + window.basicTitle;
					window[timeoutName] = setTimeout(updateFunc, 1000);
				};
				var setUpdateInput = function(e) {
					setUpdateTimeout(e, "updateInputTimeout", "updateInputWaiting", updateInputTimeoutFunc);
				};
				var setUpdateCircle = function(e) {
					var jelement = $(e.target);
					jelement.attr("value", (parseInt(jelement.attr("value")) == 0) ? "1" : "0");
					setUpdateTimeout(e, "updateCircleTimeout", "updateCircleWaiting", updateCheckCircleTimeoutFunc);
				};
				jinputs.on("input", setUpdateInput);
				jcheckCircles.click(setUpdateCircle);
				jtextareas.on("input", setUpdateInput);
				all.addClass("touched");
				collapseAll();
			};
			$(document).ready(startupFunctionality);

			window.navigate = function(navElement) {
				var jnav = $(navElement);
				var name = jnav.text().trim().replace(" ","_");
				var jsheet = $("#sheet_container");
				var jtitles = jsheet.find(".title");
				var jtitle = $(jtitles[0]);
				for (var i = 0; i < jtitles.length; i++) {
					if ($(jtitles[i]).text().trim().replace(" ","_") == name) {
						jtitle = $(jtitles[i]);
					}
				}
				$([document.documentElement, document.body]).animate({
			        scrollTop: jtitle.parent().offset().top
			    }, 300);
			}
			window.addNew = function(table, description, container) {
				var jerrors_label = $("#floater").find(".floater_errors");
				var posts = {
					"command": "addNew",
					"campaign_id": <?php echo $cid; ?>,
					"character_id": <?php echo $charid; ?>,
					"table": table,
					"description": description,
					"container": container
				}
				interpret_commands(send_ajax_call("ajax.php", posts), jerrors_label);
			}
			window.remove = function(element, rowid, table, description, removeRow) {
				if (arguments.length < 5 || removeRow === undefined) removeRow = false;
				if (confirm("Are you sure you want to remove this " + description + "?"))
				{
					var jerrors_label = $("#floater").find(".floater_errors");
					var posts = {
						"command": "remove",
						"campaign_id": <?php echo $cid; ?>,
						"character_id": <?php echo $charid; ?>,
						"rowid": rowid,
						"table": table,
						"description": description,
						"removeRow": (removeRow) ? 1 : 0
					}
					var result = send_ajax_call("ajax.php", posts);
					interpret_commands(result, jerrors_label);
					if (result.indexOf("print success") >= 0) {
						get_parent_by_tag("div", $(element)).remove();
					}
				}
			}
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
			"campaign"=>$cid, //int
			"users"=>"|{$uid}|", //int
			"drawOrder"=>"Core,Cyphers,Artifacts,Skills,Abilities,Equipment,Combat,Oddities,Character_Relations,Places,Journal,Description", //varchar
			"collapseIds"=>""

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
			$a_char_ids = db_query("SELECT `id` FROM `[maindb]`.`characters` WHERE INSTR(`users`,'|[uid]|')",
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

	public static function add_reference_or_delete($charid, $s_column, $s_table, $i_tableRowId) {
		global $maindb;

		$a_vals = array(
			"maindb"=>$maindb,
			"refId"=>"|{$i_tableRowId}|",
			"refTable"=>$s_table,
			"charid"=>$charid
		);
		$s_where_clause = "WHERE `id`='[charid]'";
		$b_success = db_try_concat_str($maindb, "characters", $s_column, "refId", $s_where_clause, $a_vals);

		// failed, remove the referenced row
		if (!$b_success) {
			db_query("DELETE FROM `[maindb]`.`[refTable]` WHERE `id`='[refId]'", $a_vals);
			return "Not enough room in database";
		}

		return TRUE;
	}
}

?>
