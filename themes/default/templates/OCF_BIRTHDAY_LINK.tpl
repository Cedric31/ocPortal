{+START,IF_ADJACENT,OCF_BIRTHDAY_LINK}, {+END}<span class="birthday">{+START,IF_PASSED,HIGHLIGHT_NAME}{+START,IF,{HIGHLIGHT_NAME}}<em>{+END}{+END}<a {+START,IF_PASSED,COLOUR}class="{COLOUR}" {+END}title="{USERNAME*}: {!MEMBER}" href="{PROFILE_URL*}">{USERNAME*}</a>{+START,IF_PASSED,HIGHLIGHT_NAME}{+START,IF,{HIGHLIGHT_NAME}}</em>{+END}{+END}&nbsp;<a href="{BIRTHDAY_LINK*}" title="{!CREATE_BIRTHDAY_TOPIC}"><img alt="" src="{$IMG*,birthday_icon}" /></a>{+START,IF_PASSED,AGE}&nbsp;({AGE*}){+END}</span>
