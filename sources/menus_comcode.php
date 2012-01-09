<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_menus
 */

/**
 * Takes some comcode, and return an XHTML menu created from it.
 *
 * @param  LONG_TEXT		The contents of the comcode menu tag
 * @param  SHORT_TEXT	An identifier for this menu (will be used as a unique id by menu javascript code)
 * @param  MEMBER			The member the menu is being built as
 * @param  ID_TEXT		The menu type (determines what templates get used)
 * @return tempcode		The generated tempcode of the menu
 */
function build_comcode_menu($comcode,$menu,$source_member,$type)
{
	// Reset
	$level=-1;
	$expanded=true;
	$expander=array();
	$expander[0]=-1;

	// Loop
	$i=0;
	$lines=explode(chr(10),$comcode);
	$stack=array(); // Stores the previous level(s) if we are jumping down to a further one
	$root_branch=array( // Our root branch is the first branch we load up
		'type'=>'root',
		'caption'=>$type,
		'special'=>$menu,
		'children'=>array(),
		'only_on_page'=>NULL,
		'modifiers'=>array()
	);
	$current_level=$root_branch;

	if (count($lines)==0) return new ocp_tempcode();
	// Fix up if lines aren't indented by one  -- Junk code, don't need it
/*	if ($lines[0][0]!=' ')
	{
		for ($j=0;$j<count($lines);$j++)
		{
			$lines[$j]=' '.$lines[$j];
		}
	}*/

	foreach ($lines as $line)
	{
		if (trim($line)=='')
		{
			if (($i!=0) && ($i<count($lines)-2))
			{
				$current=array(
					'type'=>'blank',
					'caption'=>NULL,
					'special'=>NULL,
					'children'=>NULL,
					'only_on_page'=>NULL,
					'modifiers'=>array()
				);
				$current_level[]=$current;
			}
			continue;
		}

		// Detect which level we are on
		$last_level=$level; // Only update our parent level if we actually went down a level last time

		// See what level we are on by counting the spaces
		for ($levels=1;$levels<10;$levels++)
		{
			if ($line[$levels-1]!=' ') break;
		}
		$level=$levels-1;

		if ($level>$last_level+1)
		{
			require_code('comcode_renderer');
			comcode_parse_error(false,array('CCP_MENU_JUMPYNESS'),$i,$comcode);
		}
		if (($last_level-$level==0) && ($current_level['type']=='drawer') && (strpos($line,'=')===false)) // little hack to make case of branch having no children work
		{
			$last_level++;
		}
		for ($x=0;$x<$last_level-$level;$x++)
		{
			if (strpos($line,'=')!==false)
			{
				require_code('comcode_renderer');
				comcode_parse_error(false,array('CCP_MENU_JUMPYNESS'),$i,$comcode);
			}
			
			$this_level=$current_level;
			$current_level=array_pop($stack);
			$current_level['children'][]=$this_level;
		}

		// Expansion method
		if ($line[$level]=='+') // Yes, it always is
		{
			$expanded=true;
			$expander[$level]=-1;
		}
		else // No (well maybe its not even expandable, maybe its a link)
		{
/*			$expand_this=get_param_integer('keep_'.$menu.'_expand_'.$i,0);
			$expanded=($expand_this==1); PROBLEMS WITH CACHE - SO WE'LL USE JAVASCRIPT FOR THIS  */
			$expanded=false;
			$expander[$level]=$i;
		}

		// Find where the URL starts
		$pos=strpos($line,'=');
		// Find the caption
		if ($pos===false)
		{
			$caption=rtrim(substr($line,($line[$level]!='+' && $line[$level]!='-')?$level:($level+1)));
		} else
		{
			$caption=rtrim(substr($line,$level,$pos-$level));
		}

		$modifiers=array();
		if ($caption[0]=='@')
		{
			$caption=substr($caption,1);
		}

		// For childed branches
		if ($pos===false)
		{
			// Are we expanding or contracting?
			if (($expanded) || ($expander[$level]==-1)) // If is naturally expanded, or there is nothing that can expand it (probably because it has no parent)
			{
				$modifiers['expanded']=1;
			}

			array_push($stack,$current_level);
			$current_level=array(
				'type'=>'drawer',
				'caption'=>$caption,
				'special'=>NULL,
				'children'=>array(),
				'only_on_page'=>NULL,
				'modifiers'=>$modifiers
			);
		} else // For simple link branches
		{
			$url=ltrim(substr($line,$pos+1));
			if ($url[0]=='~')
			{
				$url=substr($url,1);
				$modifiers['new_window']=1;
			}
			/*elseif ($url[0]=='?')	  Cache says no-no
			{
				$url=substr($url,1);
				$modifers['check_perms']=1;
			}*/

			$current_level['children'][]=array(
				'type'=>'link',
				'caption'=>$caption,
				'special'=>@html_entity_decode($url,ENT_QUOTES,get_charset()),
				'children'=>array(),
				'only_on_page'=>NULL,
				'modifiers'=>$modifiers
			);
		}

		$i++;
	}

	for ($x=0;$x<count($stack);$x++)
	{
		$this_level=$current_level;
		$current_level=array_pop($stack);
		$current_level['children'][]=$this_level;
	}

	return render_menu($current_level,$source_member,$type);
}

