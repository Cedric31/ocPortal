<h2>{TITLE}</h2>

{+START,IF_NON_EMPTY,{SUBCATEGORIES}}
	{+START,BOX,{!SUBCATEGORIES_HERE}}
		{SUBCATEGORIES}
	{+END}
	{+START,IF_NON_EMPTY,{DOWNLOADS}}
		<br />
	{+END}
{+END}

{+START,IF_NON_EMPTY,{DOWNLOADS}}
	{DOWNLOADS}
{+END}

{+START,IF_NON_EMPTY,{SUBMIT_URL}}
	<p class="community_block_tagline">
		[ <a rel="add" href="{SUBMIT_URL*}">{!ADD_DOWNLOAD}</a> ]
	</p>
{+END}
