<form title="{!JUMP} ({!FORM_AUTO_SUBMITS})" class="side_block_form" method="get" action="{$FIND_SCRIPT*,netlink}">
	<div>
		<div class="constrain_field">
			<p class="accessibility_hidden"><label for="netlink_url">{!JUMP}</label></p>
			<select{+START,IF,{$JS_ON}} onchange="this.form.submit();"{+END} onclick="this.form.submit();" id="netlink_url" name="url" class="wide_field">
				{CONTENT}
			</select>
		</div>
		{+START,IF,{$NOT,{$JS_ON}}}
			<input onclick="disable_button_just_clicked(this);" type="submit" value="{!PROCEED}" class="wide_button" />
		{+END}
	</div>
</form>

