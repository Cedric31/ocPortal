{+START,IF,{$OR,{$VALUE_OPTION,likes},{$IS_NON_EMPTY,{ERROR}}}}
	{+START,IF_NON_EMPTY,{TITLE}}
		<strong>{TITLE*}:</strong><br />
	{+END}

	{$,Visually show results}
	{$SET,rating_loop,0}
	{+START,WHILE,{$LT,{$GET,rating_loop},{$ROUND,{$DIV_FLOAT,{RATING},2}}}}
		<img src="{$IMG*,rating}" title="{!HAS_RATING,{$ROUND,{$DIV_FLOAT,{RATING},2}}}" alt="{!HAS_RATING,{$ROUND,{$DIV_FLOAT,{RATING},2}}}" />
		{$INC,rating_loop}
	{+END}
	{+START,IF,{$VALUE_OPTION,likes}}{+START,IF_PASSED,LIKED_BY}{+START,IF_NON_EMPTY,{LIKED_BY}}
		{$SET,done_one_liker,0}
		{+START,LOOP,LIKED_BY}{+START,IF_NON_EMPTY,{$AVATAR,{MEMBER_ID}}}{+START,IF,{$NOT,{$GET,done_one_liker,0}}}({+END}<a href="{$MEMBER_PROFILE_LINK*,{MEMBER_ID}}"><img width="10" height="10" src="{$AVATAR*,{MEMBER_ID}}" title="{!LIKED_BY} {USERNAME*}" alt="{!LIKED_BY} {USERNAME*}" /></a>{+START,IF,{$NOT,{$GET,done_one_liker,0}}}){+END}{$SET,done_one_liker,1}{+END}{+END}
	{+END}{+END}{+END}
{+END}

{$,Semantics to show results}
{+START,IF,{$VALUE_OPTION,html5}}
	<meta itemprop="ratingCount" content="{$PREG_REPLACE*,[^\d],,{NUM_RATINGS}}" />
	{+START,LOOP,_RATING}
		<meta itemprop="ratingValue" content="{RATING*}" />
	{+END}
{+END}
