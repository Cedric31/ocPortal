{TITLE}

<h2>{!DETAILS}</h2>

{VIEW_SPACE}

<h2>{!VIEW}</h2>

<p>
	{!BANNER_LOOKS}
</p>

<p>
	{BANNER}
</p>

{$,Load up the staff actions template to display staff actions uniformly (we relay our parameters to it)...}
{+START,INCLUDE,STAFF_ACTIONS}
	1_URL={EDIT_URL*}
	1_TITLE={!EDIT}
	1_ACCESSKEY=q
	1_REL=edit
{+END}
