	</div>
	{+START,IF,{SHOW_BOTTOM}}
		<div id="footer">
			<div class="footer-in">
				<ul class="footer1">
					<li>
						<p>
							Powered by <a href="http://ocportal.com" target="_blank">ocPortal</a> and designed by <a href="http://ocproducts.com" target="_blank">ocProducts</a>, {$COPYRIGHT`}
						</p>
						<p>
							<a class="associated_details" href="{$PAGE_LINK*,adminzone}">[Admin Zone]</a>
						</p>
					</li>
				</ul>
				<ul class="footer2">
					<li>{$BLOCK,block=side_stored_menu,param=root_website,type=zone}</li>
				</ul>
				<br /><br />
			</div>
			<div class="footer-in">
				<ul class="footer1">
					<li><a href="{$PAGE_LINK*,adminzone}">[Admin Zone]</a></li>
				</ul>
			</div>
		</div>
	{+END}

	{$JS_TEMPCODE,footer}
	<script type="text/javascript">// <![CDATA[
		scriptLoadStuff();
		if (typeof window.scriptPageRendered!='undefined') scriptPageRendered();

		{+START,IF,{$EQ,{$_GET,wide_print},1}}try { window.print(); } catch (e) {};{+END}
	//]]></script>
	{$EXTRA_FOOT}
</body>
</html>

