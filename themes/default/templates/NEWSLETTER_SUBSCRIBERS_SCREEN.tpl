{TITLE}

{+START,LOOP,SUBSCRIBERS}
	<p>
		{TEXT}
	</p>

	{+START,IF_NON_EMPTY,{SUB}}
		<div class="wide_table_wrap"><table summary="{!COLUMNED_TABLE}" class="solidborder wide_table variable_table">
			<thead>
				<tr>
					<th>{!EMAIL_ADDRESS}</th>
					<th>{!FORENAME}</th>
					<th>{!SURNAME}</th>
					<th>{!NAME}</th>
					<!--<th>{!NEWSLETTER_SEND_ID}</th>
					<th>{!NEWSLETTER_HASH}</th>-->
				</tr>
			</thead>
			<tbody>
				{SUB}
			</tbody>
		</table></div>

		{+START,IF_NON_EMPTY,{RESULTS_BROWSER}}
			<div class="float_surrounder results_browser_spacing">
				{RESULTS_BROWSER}
			</div>
		{+END}
	{+END}
	{+START,IF_EMPTY,{SUB}}
		<p class="nothing_here">
			{!NONE}
		</p>
	{+END}
{+END}

{+START,IF_NON_EMPTY,{DOMAINS}}
	<h2>{!STATISTICS}</h2>

	<div class="wide_table_wrap"><table class="wide_table solidborder" summary="{!COLUMNED_TABLE}">
		<thead>
			<tr>
				<th>{!DOMAIN}</th>
				<th>{!COUNT_TOTAL} ({!OF,{$NUMBER_FORMAT*,{DOMAINS}}})</th>
			</tr>
		</thead>
		<tbody>
			{+START,LOOP,DOMAINS}
				<tr>
					<td>{_loop_key*}</td>
					<td>{$NUMBER_FORMAT*,{_loop_var}}</td>
				</tr>
			{+END}
		</tbody>
	</table></div>
{+END}
