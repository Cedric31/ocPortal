<!--
Disabled as we have it integrated into POINTS_GIVE since 4.4

{+START,BOX,{!CHARGE_USER},,light}
	<p>{!CHARGE_TEXT}</p>

	<form title="{!CHARGE_USER}" method="post" onsubmit="return checkForm(this);" action="{URL*}">
		<div>
			<input type="hidden" name="user" value="{USER*}" />
			<p class="points_page_field_wrap"><label for="charge_reason"><input maxlength="255" id="charge_reason" class="input_line_required" value="" type="text" name="reason" />{!REASON}</label>: </p>
			<p class="points_page_field_wrap"><label for="charge_amount"><input maxlength="8" id="charge_amount" class="input_integer_required" value="" type="text" name="amount" />{!AMOUNT}</label>: </p>
			<p class="proceed_button"><input id="charge_points_submit" class="button_pageitem" type="submit" value="{!CHARGE_USER}" /></p>
		</div>
	</form>
{+END}
-->
