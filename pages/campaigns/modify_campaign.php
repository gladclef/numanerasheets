<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
my_session_start();
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
require_once(dirname(__FILE__)."/../../tabs/tabs_functions.php");
require_once(dirname(__FILE__)."/../login/logout_bar.php");
require_once(dirname(__FILE__)."/character_funcs.php");
require_once(dirname(__FILE__)."/campaign_funcs.php");

function draw_back_to_campaign() {
	global $fqdn;

	$cid = intval(trim(get_get_var("id")));

	ob_start();
	?>
	<div style="padding-top:7px">
		<a href="campaign.php?id=<?php echo $cid; ?>">&#x2190; Back to Campaign</a>
	</div>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_share_campaign() {
	global $fqdn;

	$cid = intval(trim(get_get_var("id")));
	$b_is_gm = campaign_funcs::is_gm($cid);
	if (!$b_is_gm) {
		return "Only the campaign GM can modify the campaign.";
	}
	$a_campaigns = campaign_funcs::get_campaigns($cid);
	$s_shareKey = $a_campaigns[0]['shareKey'];

	ob_start();
	?>
	<h2 class="title">Share Campaign</h2>
	<div>If this campaign is private, then you can give this link to your friends so they can join the campaign.</div>
	<div><?php echo "https://{$fqdn}/pages/campaigns/join.php?id={$cid}&sharekey={$s_shareKey}"; ?></div>
	<br />
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_update_campaign() {
	global $maindb;

	$cid = intval(trim(get_get_var("id")));
	$b_is_gm = campaign_funcs::is_gm($cid);
	if (!$b_is_gm) {
		return "Only the campaign GM can modify the campaign.";
	}
	$s_campaign_name = htmlspecialchars(campaign_funcs::get_name($cid));
	$a_campaigns = campaign_funcs::get_campaigns($cid);
	$b_is_public = intval($a_campaigns[0]['public']) == 1;
	$b_passProtected = intval($a_campaigns[0]['passProtected']) == 1;

	ob_start();
	?>
	<h2 class="title">Update Campaign</h2>
	<form id="campaign_update_form">
		<input type="hidden" name="command" value="update_campaign">
		<input type="hidden" name="campaignId" value="<?php echo $cid; ?>">

		<div>
			<label>Name: </label>
			<input type="textarea" class="fill" name="campaign_name" value="<?php echo $s_campaign_name; ?>" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }" />
			<br />
		</div>
		<div>
			<label>Availability: </label>
			<select name="public">
				<option value="1" <?php echo ($b_is_public) ? "selected" : ""; ?>>public</option>
				<option value="0" <?php echo ($b_is_public) ? "" : "selected"; ?>>private</option>
			</select>
		</div>
		<div>
			<label>Use password: </label>
			<input type="checkbox" name="passProtected" <?php echo ($b_passProtected) ? "checked" : "" ?> />
			<input type="password" name="pass" value="" placeholder="password" class="fill" />
		</div>

		<input id="update_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','campaign_update_form');" value="Update" /><br />
		<label class="errors"></label><br />
	</form>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_kick_players() {
	global $global_user;
	global $maindb;

	$cid = intval(trim(get_get_var("id")));
	$uid = $global_user->get_id();
	$b_is_gm = campaign_funcs::is_gm($cid);
	if (!$b_is_gm) {
		return "Only the campaign GM can modify the campaign.";
	}
	$a_characters = campaign_funcs::get_characters($cid, $b_is_gm);
	$a_campaigns = campaign_funcs::get_campaigns($cid);
	$a_users = explodeIds(str_replace("|{$uid}|", "", $a_campaigns[0]['users']));

	ob_start();
	?>
	<h2 class="title">Kick Players</h2>
	<?php
	if (count($a_users) > 0) {
		foreach ($a_users as $s_uid) {
			$a_user = db_query("SELECT * FROM `[maindb]`.`users` WHERE `id`='{$s_uid}'",
			                   array("maindb"=>$maindb));
			$a_characters = campaign_funcs::get_characters($cid, FALSE, NULL, $a_user[0]['id']);
			if (count($a_characters) > 0) {
				$s_username = htmlspecialchars($a_user[0]['username']);
				$s_charname = htmlspecialchars($a_characters[0]['name']);
				?>
				<div>
					User "<?php echo $s_username; ?>"
					(character "<?php echo $s_charname; ?>")
					<input type="button" value="Kick" onclick="kickUser(<?php echo "'{$s_username}', {$a_user[0]['id']}"; ?>);" />
				</div>
				<?php
			}
		}
	} else {
		echo "<div>This campaign does not have any players, yet.</div>";
	}
	?>
	<form id="kick_user_form">
		<input type="hidden" name="command" value="kick_user">
		<input type="hidden" name="campaignId" value="<?php echo $cid; ?>">
		<input type="hidden" name="userId" value="" id="kickId">
		<label class="errors">&nbsp;</label><br />
	</form>
	<script type="text/javascript">
		window.kickUser = function(username, uid) {
			if (confirm('Are you sure you want to kick \"' + username + '\" from this campaign?')) {
				$('#kickId').val(uid);
				send_ajax_call_from_form('ajax.php', 'kick_user_form');
			}
		}
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_change_gm() {
	global $global_user;
	global $maindb;

	$cid = intval(trim(get_get_var("id")));
	$uid = $global_user->get_id();
	$b_is_gm = campaign_funcs::is_gm($cid);
	if (!$b_is_gm) {
		return "Only the campaign GM can modify the campaign.";
	}
	$a_characters = campaign_funcs::get_characters($cid, $b_is_gm);
	$a_campaigns = campaign_funcs::get_campaigns($cid);
	$a_users = explodeIds(str_replace("|{$uid}|", "", $a_campaigns[0]['users']));

	ob_start();
	?>
	<h2 class="title">Transfer GM</h2>
	<div style="margin-bottom: 10px;">
		This will grant a different player currently in the campaign GM status (ownership status). You will no longer be the GM of the campaign.
	</div>
	<?php
	if (count($a_users) > 0) {
		foreach ($a_users as $s_uid) {
			$a_user = db_query("SELECT * FROM `[maindb]`.`users` WHERE `id`='{$s_uid}'",
			                   array("maindb"=>$maindb));
			$a_characters = campaign_funcs::get_characters($cid, FALSE, NULL, $a_user[0]['id']);
			if (count($a_characters) > 0) {
				$s_username = htmlspecialchars($a_user[0]['username']);
				$s_charname = htmlspecialchars($a_characters[0]['name']);
				?>
				<div>
					User "<?php echo $s_username; ?>"
					(character "<?php echo $s_charname; ?>")
					<input type="button" value="Bestow GM-ship" onclick="changeGM(<?php echo "'{$s_username}', {$a_user[0]['id']}"; ?>);" />
				</div>
				<?php
			}
		}
	} else {
		echo "<div>This campaign does not have any other players, yet. Have the future GM join the campaign in order to grant them ownership.</div>";
	}
	?>
	<form id="change_gm_form">
		<input type="hidden" name="command" value="change_gm">
		<input type="hidden" name="campaignId" value="<?php echo $cid; ?>">
		<input type="hidden" name="userId" value="" id="grantId">
		<label class="errors">&nbsp;</label><br />
	</form>
	<script type="text/javascript">
		window.changeGM = function(username, uid) {
			if (confirm('Are you sure you want to grant all GM-ship to \"' + username + '\"?')) {
				$('#grantId').val(uid);
				send_ajax_call_from_form('ajax.php', 'change_gm_form');
			}
		}
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_character_access() {
	global $global_user;
	global $maindb;

	$cid = intval(trim(get_get_var("id")));
	$uid = $global_user->get_id();
	$b_is_gm = campaign_funcs::is_gm($cid);
	if (!$b_is_gm) {
		return "Only the campaign GM can modify the campaign.";
	}
	$s_campaign_name = htmlspecialchars(campaign_funcs::get_name($cid));
	$a_characters = campaign_funcs::get_characters($cid, $b_is_gm);
	if (count($a_characters) == 0) {
		return "There are no characters for the campaign.";
	}
	$a_campaigns = campaign_funcs::get_campaigns($cid);
	$a_user_ids = explodeIds($a_campaigns[0]['users']);
	$s_user_ids = "'" . implode("', '", $a_user_ids) . "'";
	$a_users = db_query("SELECT * FROM `[maindb]`.`users` WHERE `id` IN ({$s_user_ids})",
	                    array("maindb"=>$maindb));
	if (count($a_users) == 0) {
		echo "<div>This campaign does not have any other players, yet.</div>";
	}

	ob_start();
	?>
	<h2 class="title">Modify User Accesses</h2>
	<div style="margin-bottom: 10px;">
		Change which characters each user has access to.
	</div>
	<table id="characterAccesses">
		<tr>
			<th>Characters &#x2192;<br />Users &#x2193;</th>
			<?php
			foreach ($a_characters as $a_character) {
				$s_charname = htmlspecialchars($a_character['name']);
				$s_charId = $a_character['id'];
				echo "<th charId='{$s_charId}'>{$s_charname}</th>";
			}
			?>
		</tr>
		<?php
		foreach ($a_users as $a_user) {
			$s_username = htmlspecialchars($a_user['username']);
			$s_uid = $a_user['id'];

			echo "<tr>";
			echo "<td>{$s_username}</td>";
			foreach ($a_characters as $a_character) {
				$b_has_character = strpos($a_character['users'], "|{$s_uid}|") !== FALSE;
				$s_checked = $b_has_character ? "checked" : "";
				$s_charId = $a_character['id'];
				echo "<td><input type='checkbox' uid='{$s_uid}' charId='{$s_charId}' onclick='updateCharacterAccesses(this);' {$s_checked}/></td>";
			}
			echo "\n</tr>\n";
		}
		?>
	</table>
	<form id="modify_user_access_form">
		<input type="hidden" name="command" value="update_user_accesses">
		<input type="hidden" name="charId" value="">
		<input type="hidden" name="userIds" value="">
		<input type="hidden" name="campaignId" value="<?php echo $cid; ?>">
		<label class="errors">&nbsp;</label><br />
	</form>
	<script type="text/javascript">
		window.syncCharacterAccesses = function() {
			var jrows = $(".syncAccess");
			var jform = $("#modify_user_access_form");
			var jcharId = jform.find("[name=charId]");
			var juserIds = jform.find("[name=userIds]");
			var syncRowFunc = function(k, v) {
				var jcol = $(v);
				jcol.removeClass("syncAccess");
				jcharId.val(jcol.attr("charId"));
				juserIds.val(jcol.attr("userIds"));
				send_ajax_call_from_form("ajax.php", "modify_user_access_form", true);
			}
			$.each(jrows, syncRowFunc);
		}
		window.updateCharacterAccesses = function(inputVal) {
			// get the users of the character's column
			var jinput = $(inputVal);
			var charId = jinput.attr("charId");
			var jinputs = $("#characterAccesses").find("input[charId=" + charId + "]");

			// get a string of the selected characters
			var userIdsStr = "";
			var addCharacter = function(k, v) {
				var jv = $(v);
				if (jv.is(":checked")) {
					userIdsStr += "|" + jv.attr("uid") + "|";
				}
			}
			$.each(jinputs, addCharacter);

			// sync the new value
			var jtable = get_parent_by_tag("table", jinput);
			var jcol = jtable.find("th[charId=" + charId + "]");
			jcol.attr("userIds", userIdsStr);
			jcol.target = jcol;
			setUpdateTimeout(jcol, "syncAccess", "syncAccess", syncCharacterAccesses);
		}
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_copy_characters() {
	global $global_user;
	global $maindb;

	$cid = intval(trim(get_get_var("id")));
	$uid = $global_user->get_id();
	$b_is_gm = campaign_funcs::is_gm($cid);
	if (!$b_is_gm) {
		return "Only the campaign GM can copy characters.";
	}
	$a_characters = campaign_funcs::get_characters($cid, $b_is_gm);
	if (count($a_characters) == 0) {
		return "There are no characters for the campaign.";
	}

	ob_start();
	?>
	<h2 class="title">Copy Characters to Another Campaign</h2>
	<div style="margin-bottom: 10px;">
		Copies a character and all their attributes (skills, equipment, etc) to another campaign.
	</div>
	<div id="copy_to_campaign_form" style="display: hidden; position: fixed; margin: 0 auto; padding: 20px; background-color: white; border: 1px solid black; border-radius: 10px; box-shadow: 0px 0px 10px 5px #ccc;">
		<input type="hidden" name="command" value="copy_character">
		<input type="hidden" name="character_id" value="">
		<input type="hidden" name="campaign_id" value="<?php echo $cid; ?>">
		<label>The exact name of the campaign to copy to:</label><br />
		<input type="textarea" size="30" name="campaign_name" placeholder="Eg: The Great Divide" /><br />
		<input id="search_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','copy_to_campaign_form');" value="Copy" />
		<input type="button" onclick="$('#copy_to_campaign_form').hide()" value="Cancel" /><br />
		<label class="errors"></label>
	</div>
	<div>
		<?php
		foreach ($a_characters as $a_character) {
			$s_charname = $a_character['name'];
			$i_charId = intval($a_character['id']);
			echo "{$s_charname} <input type='button' charId='{$i_charId}' onclick='open_copy_character_form(this);' value='copy' /><br />\n";
		}
		?>
	</div>
	<script type="text/javascript">
		window.open_copy_character_form = function(button) {
			var jbutton = $(button);
			var jform = $("#copy_to_campaign_form");
			var jcharId = jform.find("input[name=character_id]");
			var charId = jbutton.attr("charId");
			jcharId.val(charId);
			jform.show();
			setTimeout(function() {
				winWidth = parseInt($(window).width());
				formWidth = jform.width();
				winHeight = parseInt($(window).height());
				formHeight = jform.height();
				jform.css({
					left: ((winWidth / 2 - formWidth / 2) + "px"),
					top: ((winHeight / 2 - formHeight / 2) + "px")
				});
			}, 0);
		}
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_remove_characters() {
	global $global_user;
	global $maindb;

	$cid = intval(trim(get_get_var("id")));
	$uid = $global_user->get_id();
	$b_is_gm = campaign_funcs::is_gm($cid);
	if (!$b_is_gm) {
		return "Only the campaign GM can remove characters.";
	}
	$a_characters = campaign_funcs::get_characters($cid, $b_is_gm);
	$a_campaigns = campaign_funcs::get_campaigns($cid);
	$a_removedIds = explodeIds($a_campaigns[0]['removedCharacters']);
	$s_removedIds = join("','", $a_removedIds);
	$a_removedCharacters = db_query("SELECT * FROM `[maindb]`.`characters` WHERE `id` IN ('{$s_removedIds}')",
	                                array("maindb"=>$maindb));
	if (count($a_characters) == 0) {
		return "There are no characters for the campaign.";
	}

	ob_start();
	?>
	<h2 class="title">Remove Old Characters</h2>
	<div style="margin-bottom: 10px;">
		"Removing" a character makes the character invisible in the list of character tabs at the top of the campaign page.
	</div>
	<table class="auto_size">
		<tr class="title start_collapsed" style="background-color: #ccc; cursor: pointer;">
			<td colspan="2" style="text-align: center; padding: 4px;">Remove or Restore Characters</td>
		</tr>
		<tr>
			<td style="text-align: center">Available Characters</td>
			<td style="text-align: center">Removed Characters</td>
		</tr>
		<tr>
			<td style="vertical-align: top">
				<ul id="visible_characters" class="removable_chars" style="width: 300px; min-height: 60px; padding: 5px; background-color: white; border: 1px solid black; border-radius: 10px; box-shadow: 0px 0px 5px #ccc inset;">
					<?php
					foreach ($a_characters as $a_character) {
						$s_charname = $a_character['name'];
						$i_charId = intval($a_character['id']);
						echo "<li class='character_block' charId='{$i_charId}' >{$s_charname}</li>\n";
					}
					?>
				</ul>
			</td>
			<td style="vertical-align: top">
				<ul id="hidden_characters" class="removable_chars" style="width: 300px; min-height: 60px; padding: 5px; background-color: white; border: 1px solid black; border-radius: 10px; box-shadow: 0px 0px 5px #ccc inset;">
					<?php
					foreach ($a_removedCharacters as $a_character) {
						$s_charname = $a_character['name'];
						$i_charId = intval($a_character['id']);
						echo "<li class='character_block' charId='{$i_charId}' >{$s_charname}</li>\n";
					}
					?>
				</ul>
			</td>
		</tr>
	</table>
	<div id="remove_chars_error"></div>
	<script type="text/javascript">
		$(document).ready(function() {
			var updateRemovedCharsList = function(event, ui) {
				var jitem = ui.item;
				var jtarget = $(event.target);
				var sourceIsAvail = jtarget.is("#visible_characters");
				var destIsAvail = jitem.parent().is("#visible_characters");

				// check if the container changed
				if (sourceIsAvail == destIsAvail)
					return;

				// update the lists
				var jerrors_label = $("#remove_chars_error");
				var posts = {
					"command": "remove_or_restore_character",
					"campaign_id": "<?php echo $cid; ?>",
					"character_id": jitem.attr("charid"),
					"isRemove": (destIsAvail) ? "0" : "1"
				}
				console.log(posts);
				set_html_and_fade_in(jerrors_label, "", "<span style='color:gray;font-weight:normal;'>syncing...</span>");
				send_async_ajax_call("ajax.php", posts, true, function(retval) {
					interpret_commands(retval, jerrors_label);
				});
			};
			var makeSortable = function() {
				$("#visible_characters, #hidden_characters").sortable({
					connectWith: ".removable_chars",
					stop: updateRemovedCharsList
				}).disableSelection();
			};
			makeSortable();
		});
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_modify_campaign_page() {
	global $global_user;
	global $maindb;
	global $fqdn;

	$cid = intval(trim(get_get_var("id")));
	$uid = $global_user->get_id();
	$b_is_gm = campaign_funcs::is_gm($cid);
	if (!$b_is_gm) {
		return "Only the campaign GM can modify the campaign.";
	}
	$s_campaign_name = htmlspecialchars(campaign_funcs::get_name($cid));
	$a_characters = campaign_funcs::get_characters($cid, $b_is_gm);
	$a_campaigns = campaign_funcs::get_campaigns($cid);
	$b_is_public = intval($a_campaigns[0]['public']) == 1;
	$b_passProtected = intval($a_campaigns[0]['passProtected']) == 1;
	$s_shareKey = $a_campaigns[0]['shareKey'];
	$a_users = explodeIds(str_replace("|{$uid}|", "", $a_campaigns[0]['users']));

	ob_start();

	?>
	<div style="width: 1000px">
		<br />

		<?php
		echo draw_share_campaign();
		echo draw_update_campaign();
		echo draw_character_access();
		echo draw_remove_characters();
		echo draw_kick_players();
		echo draw_change_gm();
		echo draw_copy_characters();
		?>
		
	</div>
	<script type="text/javascript">
		// styleing script
		window.startupStyling = function() {
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
			var setWindowTitle = function() {
				window.basicTitle = "<?php echo $s_campaign_name; ?> (" + window.location.href + ")";
				document.title = window.basicTitle;
			}
			collapseTitlesFunc();
			$.each($.merge(jautoCenter, jautoSize), autoSizeFunc);
			$.each(jfill, fillFunc);
			$.each(jcalculateCenter, autoCenterFunc);
			setWindowTitle();
		}
		$(document).ready(startupStyling);
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	$s_access = "".date("Y-m-d H:i:s");
	db_query("UPDATE `[maindb]`.`campaigns` SET `access`='[now]' WHERE `id`='[cid]'",
	         array("maindb"=>$maindb, "now"=>$s_access, "cid"=>$cid));

	return $s_page;
}

if ($global_user) {
		if ($global_user->exists_in_db()) {
				$s_drawval = array();
				$s_drawval[] = draw_page_head();
				$s_drawval[] = draw_logout_bar(draw_back_to_campaign());
				$s_drawval[] = "<dev id='content'>";
				$s_drawval[] = draw_modify_campaign_page();
				$s_drawval[] = "</dev>";
				$s_drawval[] = draw_page_foot();
				echo manage_output(implode("\n", $s_drawval));
		}
} else {
		logout_session();
}

?>
