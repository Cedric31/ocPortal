{+START,BOX,{USERNAME*},,{$?,{$GET,in_panel},panel,classic},tray_open}
	{+START,IF_NON_EMPTY,{AVATAR_URL}}
		<div class="personal_stats_avatar"><img src="{AVATAR_URL*}" title="{!AVATAR}" alt="{!AVATAR}" /></div>
	{+END}

	{+START,IF_NON_EMPTY,{CONTENT}}
		<ul class="compact_list">
			{CONTENT}
		</ul>
	{+END}
	{+START,IF_NON_EMPTY,{LINKS}}
		<div class="community_block_tagline{+START,IF_NON_EMPTY,{CONTENT}} community_block_tagline_splitter{+END}">
			{LINKS}
		</div>
	{+END}

	{+START,IF,{$NEQ,{$CPF_VALUE,m_password_compat_scheme},facebook}}
		{+START,IF_NON_EMPTY,{$CONFIG_OPTION,facebook_appid}}
			<p class="community_block_tagline">
				<span xmlns:fb="http://api.facebook.com/1.0/">
					<fb:login-button perms="email,user_birthday"></fb:login-button>
				</span>
			</p>
		{+END}
	{+END}
{+END}
