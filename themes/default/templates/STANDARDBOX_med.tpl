<div class="medborder">
	<div style="height: {HEIGHT'}; width: {WIDTH'}">
		{+START,IF_NON_EMPTY,{TITLE}}
			<h3 class="standardbox_title_med">{TITLE}</h3>
		{+END}
		<div class="medborder_box">
			{+START,IF_NON_EMPTY,{META}}
				<div class="medborder_detailhead_wrap">
					<div class="medborder_detailhead">
						{+START,LOOP,META}
							<div>{KEY}: {VALUE}</div>
						{+END}
					</div>
				</div>
			{+END}
			<div class="standardbox_main_classic"><div class="float_surrounder">
				{CONTENT}
			</div></div>
			{+START,IF_NON_EMPTY,{LINKS}}
				{$SET,linkbar,_false}
				<div class="standardbox_links_classic community_block_tagline"> [
					{+START,LOOP,LINKS}
						{+START,IF,{$GET,linkbar}} &middot; {+END}{_loop_var}{$SET,linkbar,_true}
					{+END}
				] </div>
			{+END}
		</div>
	</div>
</div>

