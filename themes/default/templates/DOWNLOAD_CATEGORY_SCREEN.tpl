{TITLE}

{+START,IF_NON_EMPTY,{DESCRIPTION}}
	<div itemprop="description">
		{DESCRIPTION}
	</div>
{+END}

{$SET,bound_catalogue_entry,{$CATALOGUE_ENTRY_FOR,download_category,{ID}}}
{+START,IF_NON_EMPTY,{$GET,bound_catalogue_entry}}{$CATALOGUE_ENTRY_ALL_FIELD_VALUES,{$GET,bound_catalogue_entry}}{+END}

{+START,IF_NON_EMPTY,{SUBCATEGORIES}}
	<div class="box box___download_category_screen"><div class="box_inner compacted_subbox_stream">
		<h2>{$?,{$EQ,{ID},1},{!CATEGORIES},{!SUBCATEGORIES_HERE}}</h2>

		<div>
			{SUBCATEGORIES}
		</div>
	</div></div>
{+END}

{+START,IF_NON_EMPTY,{DOWNLOADS}}
	{DOWNLOADS}

	<div class="box category_sorter inline_block"><div class="box_inner">
		{$SET,show_sort_button,1}
		{SORTING}
	</div></div>
{+END}

{+START,IF,{$CONFIG_OPTION,show_content_tagging}}{TAGS}{+END}

{$REVIEW_STATUS,download_category,{ID}}

{+START,INCLUDE,NOTIFICATION_BUTTONS}
	NOTIFICATIONS_TYPE=download
	NOTIFICATIONS_ID={ID}
	BREAK=1
	RIGHT=1
{+END}

{$,Load up the staff actions template to display staff actions uniformly (we relay our parameters to it)...}
{+START,INCLUDE,STAFF_ACTIONS}
	1_URL={SUBMIT_URL*}
	1_TITLE={!ADD_DOWNLOAD}
	1_REL=add
	2_URL={ADD_CAT_URL*}
	2_TITLE={!ADD_DOWNLOAD_CATEGORY}
	2_REL=add
	3_ACCESSKEY=q
	3_URL={EDIT_CAT_URL*}
	3_TITLE={!EDIT_DOWNLOAD_CATEGORY}
	3_REL=edit
{+END}

{+START,IF,{$CONFIG_OPTION,show_screen_actions}}{+START,IF_PASSED,_TITLE}{$BLOCK,failsafe=1,block=main_screen_actions,title={$META_DATA,title}}{+END}{+END}

{+START,IF_NON_EMPTY,{SUBCATEGORIES}}{+START,IF,{$EQ,{ID},1}}
	<hr class="spaced_rule" />

	<div class="boxless_space">
		{+START,BOX}{$BLOCK,block=main_multi_content,param=download,filter={ID}*,no_links=1,efficient=0,give_context=0,include_breadcrumbs=1,render_if_empty=1,max=10,mode=recent,title={!RECENT,10,{!SECTION_DOWNLOADS}}}{+END}

		{+START,IF,{$CONFIG_OPTION,is_on_rating}}
			{+START,BOX}{$BLOCK,block=main_multi_content,param=download,filter={ID}*,no_links=1,efficient=0,give_context=0,include_breadcrumbs=1,render_if_empty=1,max=10,mode=top,title={!TOP,10,{!SECTION_DOWNLOADS}}}{+END}
		{+END}
	</div>
{+END}{+END}
