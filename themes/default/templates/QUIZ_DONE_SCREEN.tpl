{TITLE}

{+START,IF,{$NEQ,{TYPE},SURVEY}}
	<h2>{!_RESULTS}</h2>
{+END}

{$TRIM,{RESULT}}

{$TRIM,{CORRECTIONS_TO_SHOW}}

{+START,IF_NON_EMPTY,{MESSAGE}}
	{$PARAGRAPH,{MESSAGE}}
{+END}

