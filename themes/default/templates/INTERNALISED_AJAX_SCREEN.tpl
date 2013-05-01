{$SET,wrapper_id,ajax_block_wrapper_{$RAND%}}
<div id="{$GET*,wrapper_id}">
	{SCREEN_CONTENT}

	{$REQUIRE_JAVASCRIPT,javascript_ajax}
	{$REQUIRE_JAVASCRIPT,javascript_validation}

	<script type="text/javascript">// <![CDATA[
		add_event_listener_abstract(window,'load',function () {
			internalise_ajax_block_wrapper_links('{URL;/}',document.getElementById('{$GET;,wrapper_id}'),['.*'],{ },false,true);
		} );
	//]]></script>
</div>

{+START,IF_PASSED,CHANGE_DETECTION_URL}{+START,IF_NON_EMPTY,{CHANGE_DETECTION_URL}}
	<script type="text/javascript">
	// <![CDATA[
		if (typeof window.soundManager!='undefined')
		{
			soundManager.onload=function() {
				soundManager.createSound('message_received','{$BASE_URL;}/data/sounds/message_received.mp3');
			}
		}

		{+START,IF_NON_EMPTY,{REFRESH_TIME}}
			window.detect_interval=window.setInterval(
				function() {
					{+START,IF_PASSED,CHANGE_DETECTION_URL}
						if ((window.detect_change) && (detect_change('{CHANGE_DETECTION_URL/;}','{REFRESH_IF_CHANGED/;}')) && ((!document.getElementById('post')) || (document.getElementById('post').value=='')))
					{+END}
							call_block('{URL;/}','',document.getElementById('{$GET;,wrapper_id}'),false,null,true);
				},
				{REFRESH_TIME%}*1000);
		{+END}
	//]]></script>
{+END}{+END}
