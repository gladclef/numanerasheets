<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
my_session_start();
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
require_once(dirname(__FILE__)."/../../resources/goodWords.php");
require_once(dirname(__FILE__)."/../../tabs/tabs_functions.php");
require_once(dirname(__FILE__)."/../login/logout_bar.php");
require_once(dirname(__FILE__)."/character_funcs.php");
require_once(dirname(__FILE__)."/campaign_funcs.php");

function draw_back_to_welcome() {
	global $fqdn;

	ob_start();
	?>
	<div style="padding-top:7px">
		<a href="https://<?php echo $fqdn; ?>">&#x2190; Back to Welcome</a>
	</div>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_character_tab_scripts($cid)
{
	global $fqdn;

	ob_start();
	?>
	<script type="text/javascript">
		var draw_character = function(charid) {
			var jtabCurr = $("div.tab.selected");
			var jtab = $("div.tab[charid=" + charid + "]");
			var jsheetContainer = $("#sheet_container");

			// update the sheet
			var sheet = send_ajax_call("ajax.php", {
				"command": "draw_character",
				"campaign_id": <?php echo $cid; ?>,
				"character_id": charid
			});
			jsheetContainer.html('');
			jsheetContainer.html(sheet);
			jtabCurr.removeClass("selected");
			jtab.addClass("selected");
		}

		var create_new_character = function() {
			var jerrors_label = $("#floater").find(".floater_errors");
			posts = {
				"command": "create_character",
				"campaign_id": <?php echo $cid; ?>
			};
			set_html_and_fade_in(jerrors_label, "", "<span style='color:gray;font-weight:normal;'>creating character...</span>");
			send_ajax_call("ajax.php", posts, function(retval) {
				interpret_commands(retval, jerrors_label);
			});
		}
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_character_tabs($a_characters, $b_is_gm, $cid) {
	global $fqdn;

	ob_start();
	?>
	<div class="character_tabs">
		<?php

		$s_selected = "selected";
		foreach ($a_characters as $a_character)
		{
			$charid = $a_character['id'];
			$s_char_name = $a_character['name'];
			if ($b_is_gm)
				$s_char_name = htmlspecialchars(substr($s_char_name, 0, 40));
			$s_js = "onclick='draw_character({$charid})' onmouseover='$(this).addClass(\"mouse_hover\")' onmouseout='$(this).removeClass(\"mouse_hover\")'";
			echo "<div class='tab {$s_selected}' charid='{$charid}' {$s_js}>{$s_char_name}</div>\n";
			$s_selected = "";
		}

		if ($b_is_gm) {
			$s_js = "onclick='create_new_character()' onmouseover='$(this).addClass(\"mouse_hover\")' onmouseout='$(this).removeClass(\"mouse_hover\")'";
			echo "<div class='tab tab_add_new tooltip' {$s_js}>&nbsp;<span class='tooltiptext'>Create New Character</span></div>\n";
		}

		?>
	</div>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_floater($a_groups) {
	$s_style = (isset($_SESSION["left"])) ? "left: {$_SESSION['left']}px; top: {$_SESSION['top']};" : "";

	ob_start();
	?>
	<div id="floater" style="<?php echo $s_style; ?>">
		<div class="dragHandle">
			<input type="button" class="collapseButton" value="-">
		</div>
		<div class="links">
			<?php
			foreach ($a_groups as $s_group) {
				echo "<div class=\"link\">";
				echo "<div class=\"dragLink\"></div>";
				echo "<span onclick=\"navigate(this);\" class=\"fill navigate\" style=\"display: inline-block;\">$s_group</span>";
				echo "</div>";
			}
			if (count($a_groups) == 0)
				echo "<div>Create a Character!</div>"
			?>
		</div>
		<label class="errors floater_errors">&nbsp;</label>
		<input type="button" class="hidden reloadButton" onclick="location.reload(true);" value="Reload to see changes">
	</div>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_share_form($a_campaigns) {
	global $maindb;

	$cid = intval($a_campaigns[0]['id']);
	$a_characterIds = explodeIds($a_campaigns[0]['characters']);
	$s_characterIds = join(",", $a_characterIds);
	if (count($a_characterIds) > 0)
		$a_characters = db_query("SELECT `id`,`name` FROM `[maindb]`.`characters` WHERE `id` IN ({$s_characterIds})",
		                         array("maindb"=>$maindb));
	else
		$a_characters = array();

	ob_start();
	?>

	<div id="share_with_character_form" style="display: none; position: fixed; margin: 0 auto; padding: 20px; background-color: white; border: 1px solid black; border-radius: 10px; box-shadow: 0px 0px 10px 5px rgba(0,0,0,0.3);">
		<input type="hidden" name="command" value="share_with_character">
		<input type="hidden" name="campaign_id" value="<?php echo $cid; ?>">
		<input type="hidden" name="character_id" value="">
		<input type="hidden" name="rowid" value="">
		<input type="hidden" name="table" value="">
		<input type="hidden" name="description" value="">
		<label>
			Which character do you want to
			<select name="action">
				<option value="give">give</option>
				<option value="copy">copy</option>
				<option value="share">share</option>
			</select>
			this
			<span class='description'></span> "<span class='name'></span>"
			to?
		</label><br />
		<select name="other_character_id" style="margin: 5px 0;">
			<?php
			foreach ($a_characters as $a_character) {
				$i_char_id = intval($a_character['id']);
				$s_char_name = $a_character['name'];
				$s_sanitized = htmlspecialchars($s_char_name);
				echo "<option value=\"{$i_char_id}\">{$s_sanitized}</option>\n";
			}
			?>
		</select><br />
		<input id="share_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','share_with_character_form');" value="Submit" />
		<input type="button" onclick="$('#share_with_character_form').hide()" value="Cancel" /><br />
		<label class="errors"></label>
	</div>

	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_campaign_page() {
	global $global_user;
	global $maindb;
	global $goodWords;

	$cid = intval(trim(get_get_var("id")));
	$a_campaign = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE `id`='[cid]'",
	                       array("maindb"=>$maindb, "cid"=>$cid));
	$a_gm_user = db_query("SELECT * FROM `[maindb]`.`users` WHERE `id`='[gmUser]'",
	                      array("maindb"=>$maindb, "gmUser"=>$a_campaign[0]["gmUser"]));
	$b_is_gm = campaign_funcs::is_gm($cid);
	$s_campaign_name = htmlspecialchars(campaign_funcs::get_name($cid));
	$s_campaign_link = ($b_is_gm) ? "<a href=\"modify_campaign.php?id={$cid}\">{$s_campaign_name}</a>" : $s_campaign_name;
	$charid = "";

	if (!$b_is_gm)
		character_funcs::check_create_character($cid);
	$a_characters = campaign_funcs::get_characters($cid, $b_is_gm);

	ob_start();

	$a_group_order = array();
	if (count($a_characters) > 0) {
		$a_group_order = explode(",", $a_characters[0]['drawOrder']);
		$charid = $a_characters[0]['id'];
	}
	?>
	<div style="width: 1000px">
		<h1 style="display: table"><?php echo $s_campaign_link; ?></h1>
		<?php
		if ($b_is_gm) {
			echo "<h3 style='display: table'>Welcome, Game Master!</h3>\n";
		} else {
			$s_gm_name = $a_gm_user[0]['username'];
			$s_good_word = $goodWords[rand(0,count($goodWords)-1)];
			echo "<h3 style='display: table'>GM'd by the {$s_good_word} {$s_gm_name}</h3>\n";
		}

		if (!is_array($a_characters)) {
			echo "Database error";
			return;
		}
		if ($b_is_gm || count($a_characters) > 1) {
			echo draw_character_tabs($a_characters, $b_is_gm, $cid);
		} else {
			echo "<span style='display:block; height:14px;'></span>";
		}
		echo draw_character_tab_scripts($cid);

		?>
		<div id="sheet_container">
		<?php

		// draw the first character sheet
		if (count($a_characters) > 0) {
			echo character_funcs::draw_character($a_characters[0]);
		} else if ($b_is_gm) {
			?>
			<div style="padding: 20px; width: 300px; margin: 50px auto; border-radius: 10px; border: 1px solid black; background-color: #eee">
				There are no characters created for this campaign, yet.<br />
				<form id="create_character_form" style="margin:0">
					<input type="hidden" name="command" value="create_character">
					<input type="hidden" name="campaign_id" value="<?php echo $cid; ?>">
					<span style="text-decoration: underline; color: blue; cursor: pointer;" onclick="send_ajax_call_from_form('ajax.php','create_character_form')">Create New Character</span><br />
					<label class="errors hidden create_character_errors" style='width:270px; margin:0 auto;'>&nbsp;</label>
				</form>
			</div>
			<?php
		} else {
			echo "Programmer error!";
		}

		?>
		</div>
	</div>

	<script type="text/javascript">
		// styling scripts
		$(document).ready(function() {
			var jheaders = $.merge($.merge($("h1"), $("h2")), $("h3"));
			var jfloater = $("#floater");
			var jfloaterCollapseButton = jfloater.find(".collapseButton");
			var adjustHeadersFunc = function(k, v) {
				var jheader = $(v);
				jheader.css({
					"width": (jheader.width()*1.1) + "px",
					"margin": "0 auto"
				});
			}
			var collapseFloaterFunc = function(e) {
				jfloater.stop();
				if (jfloater.hasClass("collapsed")) {
					jfloater.removeClass("collapsed");
					jfloaterCollapseButton.attr('value', '-');
					jfloater.animate({ width: "150px" });
				} else {
					jfloater.addClass("collapsed");
					jfloaterCollapseButton.attr('value', '+');
					jfloater.animate({ width: "26px" });
				}
			}
			$.each(jheaders, adjustHeadersFunc);
			jfloater.draggable({ handle: ".dragHandle", cancel: ".collapseButton" });
			jfloaterCollapseButton.click(collapseFloaterFunc);
		});

		// functional scripts
		$(document).ready(function() {
			var jfloater = $("#floater");
			var jfloaterLinks = jfloater.find(".links");
			var jerrors_label = jfloater.find(".errors");
			var jreloadButton = jfloater.find(".reloadButton");
			var enforceFloaterLimits = function() {
				var left = parseInt(jfloater.css("left"));
				var top = parseInt(jfloater.css("top"));
				var jdoc = $(document);
				var maxRight = jdoc.width() - 150;
				var maxBottom = jdoc.height() - 50;
				if (left < 0) jfloater.css({ "left": "0px" })
				if (top < 0) jfloater.css({ "top": "0px" })
				if (left > maxRight) jfloater.css({ "left": (maxRight + "px") })
				if (top > maxBottom) jfloater.css({ "top": (maxBottom + "px") })
			}
			var draggableProps = {
				handle: ".dragHandle",
				cancel: ".collapseButton",
				containment: "document",
				stop: function() {
					var left = jfloater.css("left");
					var top = jfloater.css("top");
					posts = {
						"command": "set_floater_pos",
						"left": left,
						"top": top
					};
					send_async_ajax_call("ajax.php", posts, true);
				}
			}
			var enableLinksFunc = function() {
				var charid = parseInt($("#character_id").attr("value"));
				jfloaterLinks.sortable({
					handle: ".dragLink",
					stop: function(event, ui) {
						var jlinks = jfloaterLinks.find(".link");
						var order = null;
						$.each(jlinks, function(k, v) {
							if (order == null)
								order = "";
							else
								order += ",";
							order += $(v).text().trim();
						});
						posts = {
							"command": "update_character_sheet",
							"campaign_id": "<?php echo $cid; ?>",
							"character_id": charid,
							"property": "drawOrder",
							"value": order
						};
						set_html_and_fade_in(jerrors_label, "", "<span style='color:gray;font-weight:normal;'>syncing...</span>");
						send_async_ajax_call("ajax.php", posts, true, function(retval) {
							interpret_commands(retval, jerrors_label);
							if (retval.indexOf("print success") >= 0) {
								jreloadButton.show(200);
							}
						});
					}
				});
				jfloaterLinks.disableSelection();
			}
			enforceFloaterLimits();
			enableLinksFunc();
			jfloater.draggable(draggableProps);
		});
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();
	$s_page .= draw_floater($a_group_order);
	$s_page .= draw_share_form($a_campaign);

	$s_access = "".date("Y-m-d H:i:s");
	db_query("UPDATE `[maindb]`.`campaigns` SET `access`='[now]' WHERE `id`='[cid]'",
	         array("maindb"=>$maindb, "now"=>$s_access, "cid"=>$cid));

	return $s_page;
}

if ($global_user) {
		if ($global_user->exists_in_db()) {
				$s_drawval = array();
				$s_drawval[] = draw_page_head();
				$s_drawval[] = draw_logout_bar(draw_back_to_welcome());
				$s_drawval[] = "<dev id='content'>";
				$s_drawval[] = draw_campaign_page();
				$s_drawval[] = "</dev>";
				$s_drawval[] = draw_page_foot();
				echo manage_output(implode("\n", $s_drawval));
		}
} else {
		logout_session();
}

?>
