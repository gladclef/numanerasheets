<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
my_session_start();
require_once(dirname(__FILE__)."/../../resources/check_logged_in.php");
require_once(dirname(__FILE__)."/../../tabs/tabs_functions.php");
require_once(dirname(__FILE__)."/../login/logout_bar.php");

function draw_create_column() {
	global $global_user;
	global $maindb;

	?>
	<div class="column">
		<h1 style="margin: 0 auto;">Create a Campaign</h1>
		<div class="adventure_column">
			<div id="create_campaign_form" style="margin:10px;">
				<input type="hidden" name="command" value="create_campaign">

				<div id="choose_campaign_name_form">
					<input type="hidden" name="command" value="check_campaign_name">
					<label>Choose a campaign name:</label><br />
					<input type="textarea" size="30" name="campaign_name" placeholder="Eg: The Great Divide" onblur="send_ajax_call_from_form('ajax.php','choose_campaign_name_form');" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }" /><br />
					<label class="errors hidden" id="campaign_name_errors">&nbsp;</label><br />
				</div>

				<label>Make this campaign: </label>
				<select name="public" id="public" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }">
					<option value="1">Public</option>
					<option value="0">Private</option>
				</select><br /><br />
				<div id="passProtectedDiv">
					<label>Require password:</label>
					<input type="checkbox" name="passProtected" id="passProtected" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }" /><br />
				</div>
				<div id="passDiv" class="hidden">
					<br />
					<label>Password</label>
					<input type="password" name="pass" id="pass" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }" /><br /><br />
				</div>
				<label class="errors"></label><br />
				<input id="create_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','create_campaign_form');" value="Create" /><br />
			</div>
			<script type="text/javascript">
				$(document).ready(function() {
					var t = 100;
					var passProtected = $("#passProtected");
					var passDiv = $("#passDiv");
					var passShow = function() {
						passDiv.stop();
						if (passProtected.is(":checked")) {
							passDiv.show(t);
						} else {
							passDiv.hide(t);
						}
					}
					passProtected.click(passShow);
				});
			</script>
		</div>
	</div>
	<?php
}

function draw_join_column() {
	global $global_user;
	global $maindb;

	?>
	<div id="join" class="column">
		<h1 style="margin: 0 auto;">Join Your Companions</h1>
		<div class="adventure_column">
			<div id="search_campaign_form" style="margin:10px;">
				<input type="hidden" name="command" value="search_campaign">
				<label>Find by name:</label><br />
				<input type="textarea" size="30" name="campaign_name" placeholder="Eg: The Great Divide" onkeypress="if (event.keyCode==13){ $('#search_submit').click(); }" /><br />
				<label class="errors"></label><br />
				<input id="search_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','search_campaign_form');" value="Search" /><br />
			</div>
			<div id="join_campaign_form">
			</div>
		</div>
		<div class="passPrompt overlay" onclick="cancel_join_pass();"></div>
		<div id="passPrompt" class="popup_input">
			<span class="name" style="font-weight: bold;"></span>
			<div>Password required:</div>
			<input type="hidden" name="command" value="join_campaign">
			<input type="hidden" name="campaignId" value="-1">
			<input type="password" name="password" onkeypress="if (event.keyCode==13){ $('#pass_submit').click(); }"><br />
			<div style="margin: 5px; text-align: right;">
				<input type="button" id="pass_submit" onclick="join_campaign_pass_submit()" value="Submit">
				<input type="button" onclick="cancel_join_pass();" value="Cancel">
			</div>
			<label class="errors hidden campaign_join_errors" style='width:270px; margin:0 auto;'>&nbsp;</label>
		</div>
	</div>
	<script type="text/javascript">
		var provideJoinButton = function(button)
		{
			var jbutton = $(button);
			var id = jbutton.attr("campaignId");
			var jjoin = $(".campaign_join_button[campaignId=" + id + "]");
			var jbuttonOthers = $(".campaign_button.join").not(jbutton);
			var jjoinOthers = $(".campaign_join_button").not(jjoin);
			jbuttonOthers.stop();
			jjoinOthers.stop();
			jbuttonOthers.animate({width:270}, 200);
			jjoinOthers.hide(200);
			jbutton.stop();
			jjoin.stop();
			if (jjoin.is(":hidden"))
			{
				jjoin.show(200);
				jbutton.animate({width:220}, 200);
				$("input[name=campaignId]").attr("value", id);
			}
			else
			{
				jbutton.animate({width:270}, 200);
				jjoin.hide(200);
				$("input[name=campaignId]").attr("value", -1);
			}
		}
		var join_campaign_btn_click = function(button) {
			var jjoin = $(button);
			var id = jjoin.attr("campaignId");
			var jbutton = $(".campaign_button[campaignId=" + id + "]");
			var passProtected = parseInt(jjoin.attr("passProtected"));
			var name = jbutton.html();
			var joverlay = $(".overlay");
			var jpassPrompt = $("#passPrompt");
			var jpass = jpassPrompt.find("input[type=password]");
			var jpassName = jpassPrompt.find(".name");
			var name = jbutton.html();
			if (passProtected) {
				jpass.html('');
				jpassName.html('');
				jpassName.html(name);
				jpassPrompt.show();
				joverlay.show();
			} else {
				send_ajax_call_from_form('ajax.php','join_campaign_form');
			}
		}
		var join_campaign_pass_submit = function() {
			send_ajax_call_from_form('ajax.php','passPrompt');
		}
		var cancel_join_pass = function() {
			$('#passPrompt').hide();
			$('.overlay').hide();
		}
	</script>
	<?php
}

function draw_continue_column() {
	global $global_user;
	global $maindb;

	?>
	<div id="resume" class="column">
		<h1 style="margin: 0 auto;">Continue the Adventure</h1>
		<div class="adventure_column">
			<?php
			$uid = $global_user->get_id();
			$a_campaigns = db_query("SELECT * FROM `[maindb]`.`campaigns` WHERE INSTR(`users`, '|[uid]|') ORDER BY `access` DESC",
								    array("maindb"=>$maindb, "uid"=>$uid));
			if (is_array($a_campaigns) && count($a_campaigns) > 0)
			{
				foreach($a_campaigns as $a_campaign) {
					$cid = $a_campaign['id'];
					$cname = $a_campaign['name'];
					echo "<button class=\"campaign_button continue\" onclick=\"window.location.href='campaign.php?id={$cid}'\">{$cname}</button>";
				}
			}
			else
			{
				echo "<div style='color:gray; width:270px; margin:0 auto; text-align:center;'>You aren't a part of any campaigns yet.</div>";
			}
			?>
		</div>
	</div>
	<?php
}

function draw_welcome_page() {
	global $global_user;
	global $maindb;

	ob_start();
	?>
	<div class="row">
		<?php

		draw_create_column();
		draw_join_column();
		draw_continue_column();

		?>
	</div>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

if ($global_user) {
		if ($global_user->exists_in_db()) {
				$s_drawval = array();
				$s_drawval[] = draw_page_head();
				$s_drawval[] = draw_logout_bar();
				$s_drawval[] = "<br /><br /><dev id='content'>";
				$s_drawval[] = draw_welcome_page();
				$s_drawval[] = "</dev>";
				$s_drawval[] = draw_page_foot();
				echo manage_output(implode("\n", $s_drawval));
		}
} else {
		logout_session();
}

?>
