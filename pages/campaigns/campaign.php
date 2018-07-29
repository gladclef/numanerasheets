<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
my_session_start();
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
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

function draw_character_tabs($a_characters, $b_is_gm) {
	global $fqdn;

	ob_start();
	?>
	<h3 style="display: table">Welcome, Game Master!</h3>
	<div class="character_tabs">
		<?php

		$s_selected = "selected";
		foreach ($a_characters as $a_character)
		{
			$charid = $a_character['id'];
			$s_char_name = $a_character['name'];
			if ($b_is_gm)
				$s_char_name = substr($s_char_name, 40);
			$s_js = "onclick='draw_character({$charid})' onmouseover='$(this).addClass(\"mouse_hover\")' onmouseout='$(this).removeClass(\"mouse_hover\")'";
			echo "<div class='tab {$s_selected}' charid='{$charid}' {$s_js}>{$s_char_name}</div>";
			$s_selected = "";
		}

		?>
	</div>
	<script type="text/javascript">
		var draw_character = function(charid) {
			var jtabCurr = $("div.tab.selected");
			var jtab = $("div.tab[charid=" + charid + "]");
			var jsheetContainer = $("#sheet_container");

			// check that we actually need an update
			if (parseInt(jtabCurr.attr("charid")) == parseInt(charid))
				return;

			// update the sheet
			var sheet = send_ajax_call("ajax.php", {
				"command": "draw_character",
				"charid": charid
			});
			jsheetContainer.html('');
			jsheetContainer.html(sheet);
			jtabCurr.removeClass("selected");
			jtab.addClass("selected");
		}
	</script>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_campaign_page() {
	global $global_user;
	global $maindb;

	$cid = intval(trim(get_get_var("id")));
	$b_is_gm = campaign_funcs::is_gm($cid);
	$s_campaign_name = campaign_funcs::get_name($cid);

	if (!$b_is_gm)
		character_funcs::check_create_character($cid);

	ob_start();
	?>
	<div style="width: 1000px">
		<h1 style="display: table"><?php echo $s_campaign_name; ?></h1>
		<?php

		$a_characters = campaign_funcs::get_characters($cid, $b_is_gm);
		if (!is_array($a_characters)) {
			echo "Database error";
			return;
		}
		if ($b_is_gm)
			echo draw_character_tabs($a_characters, $b_is_gm);

		?>
		<div id="sheet_container">
		<?php

		// draw the first character sheet
		if (count($a_characters) > 0)
			echo character_funcs::draw_character($a_characters[0]);
		else if ($b_is_gm) {
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
		}
		else
			echo "Programmer error!";

		?>
		</div>
		<script type="text/javascript">
			$(document).ready(function() {
				var jheaders = $.merge($.merge($("h1"), $("h2")), $("h3"));
				$.each(jheaders, function(k, v) {
					var jheader = $(v);
					jheader.css({
						"width": jheader.width() + "px",
						"margin": "0 auto"
					});
				});
			});
		</script>
	</div>
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
