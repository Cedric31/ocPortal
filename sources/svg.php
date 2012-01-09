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
 * @package		stats
 */

// http://www.adobe.com/svg/viewer/install/beta.html

/**
 * Standard code module initialisation function.
 */
function init__svg()
{
	require_css('svg');

	// Some default values
	define('VIEWPORT_WIDTH',1024.0);
	define('VIEWPORT_HEIGHT',400.0);
	define('SVG_WIDTH',1024.0);
	define('SVG_HEIGHT',400.0);
	define('Y_LABEL_WIDTH',50.0);
	define('Y_AXIS_WIDTH',50.0);
	define('PLOT_WIDTH_BIAS',10.0);
	define('PLOT_WIDTH',SVG_WIDTH-Y_LABEL_WIDTH-Y_AXIS_WIDTH-PLOT_WIDTH_BIAS);
	define('X_LABEL_HEIGHT',13.0);
	define('X_AXIS_HEIGHT',13.0);
	define('PLOT_HEIGHT_BIAS',10.0);
	define('PLOT_HEIGHT',SVG_HEIGHT-X_LABEL_HEIGHT-X_AXIS_HEIGHT-PLOT_HEIGHT_BIAS);
	define('X_PADDING',2.0);
	define('Y_PADDING',2.0);
	define('MIN_Y_MARKER_DISTANCE',14.0);
	define('MIN_X_MARKER_DISTANCE',14.0);
	define('TEXT_HEIGHT',14.0);
	define('BAR_WIDTH',15.0);
	
	global $CSS_FILE_CONTENTS;
	$CSS_FILE_CONTENTS='';
}

/**
 * Get the SVG markup for a segment of a circle. It is designed to be used in the construction of a pie chart
 *
 * @param  string			The hexadecimal-formatted colour for the shape
 * @param  integer		The angle of the segment
 * @param  integer		The radius of the segment
 * @param  integer		The X position of the start vertex
 * @param  integer		The Y position of the start vertex
 * @param  integer		The X position of the end vertex
 * @param  integer		The Y position of the end vertex
 * @return string			The SVG markup for the described segment
*/
function _draw_segment($colour,$angle,$radius,$start_x,$start_y,$end_x,$end_y)
{
	if ($angle==360) return '<circle cx="'.float_to_raw_string(X_PADDING+floatval($radius)).'" cy="'.float_to_raw_string(Y_PADDING+floatval($radius)).'" r="'.float_to_raw_string(floatval($radius)).'" style="fill: #'.($colour).';" class="pie_chart" />'.chr(10);
	else return '<path d="M'.float_to_raw_string(X_PADDING+floatval($radius)).','.float_to_raw_string(Y_PADDING+floatval($radius)).' L'.float_to_raw_string(X_PADDING+floatval($start_x)).','.float_to_raw_string(Y_PADDING+floatval($start_y)).' A'.float_to_raw_string(floatval($radius)).','.float_to_raw_string(floatval($radius)).' 0 '.float_to_raw_string(($angle>180)?1.0:0.0).',1,'.float_to_raw_string(X_PADDING+floatval($end_x)).','.float_to_raw_string(Y_PADDING+floatval($end_y)).' Z" style="fill: #'.($colour).';" class="pie_chart" />'.chr(10);
}

/**
 * Get the SVG markup for a key for a chart, such as a pie chart, using the specified data
 *
 * @param	array				An array of the data to be keyed up
 * @param	string			The starting colour for the key
 * @param	integer			The starting X position
 * @param	integer			The starting Y position
 * @param	string			The units (e.g. %)
 * @return  string			The SVG markup for the described key
*/
function _draw_key($data,$start_colour,$start_x,$start_y,$units='')
{
	if (!defined('BOX_SIZE'))
	{
		define('BOX_SIZE',10.0);
		define('BOX_SPACING',5.0);
	}

	$output='';
	$colour=$start_colour;
	$i=0;

	foreach ($data as $key=>$_value)
	{
		if (is_array($_value)) $_value=array_shift($_value);

		$value=float_format($_value);
	
		if (strlen($value)>100) $value=substr($value,0,40).'&hellip;'.substr($value,-40);

		if ($key=='') $key=do_lang('UNKNOWN');

		$output.='<rect x="'.float_to_raw_string(floatval($start_x)).'" y="'.float_to_raw_string(floatval($start_y)+floatval($i)*(BOX_SPACING+BOX_SIZE)).'" width="'.float_to_raw_string(BOX_SIZE).'" height="'.float_to_raw_string(BOX_SIZE).'" style="fill: #'.($colour).';" class="key_box" />'.chr(10);
		$output.='<text x="'.float_to_raw_string(floatval($start_x)+BOX_SPACING+BOX_SIZE).'" y="'.float_to_raw_string(floatval($start_y)+floatval($i)*(BOX_SPACING+BOX_SIZE)+BOX_SIZE).'" class="key_text">'.escape_html($key).' ('.escape_html($value.$units).')</text>'.chr(10);

		$colour=_get_next_colour($colour);
		$i++;
	}

	return $output;
}

/**
 * Get the next hexadecimal colour from the specified one, where each of the Red, Green or Blue columns can either be 00, 33, 66 or 99, to provide a viewable contrast between two adjacent colours
 *
 * @param	string			The hexadecimal-format colour to be incremented
 * @return  string			The incremented hexadecimal colour
*/
function _get_next_colour($current_colour)
{
	$all_of=array_values(array_unique(array('333333','333366','333399','3333CC','3333FF','336633','339933','33CC33','33FF33','663333','993333','CC3333','FF3333','663333','663366','663399','6633CC','6633FF','666633','669933','66CC33','66FF33','993333','993366','93399','9933CC','9933FF','996633','999933','99CC33','99FF33','CC3333','CC3366','CC3399','CC33CC','CC33FF','CC6633','CC9933','CCCC33','CCFF33','FF3333','FF3366','FF3399','FF33CC','FF33FF','FF6633','FF9933','FFCC33','FFFF33','336633','336666','336699','3366CC','3366FF','339933','339966','339999','3399CC','3399FF','33CC33','33CC66','33CC99','33CCCC','33CCFF','33FF33','33FF66','33FF99','33FFCC','33FFFF')));

	$key_pos=array_search($current_colour,$all_of);
	$next_key_pos=$key_pos+3;
	if ($next_key_pos>=count($all_of)) $next_key_pos=$next_key_pos%count($all_of);
	return $all_of[$next_key_pos];
}

/**
 * Get the markup required to start a new SVG document
 *
 * @return string			The markup
*/
function _start_svg()
{
	$theme=@method_exists($GLOBALS['FORUM_DRIVER'],'get_theme')?$GLOBALS['FORUM_DRIVER']->get_theme():'default';
	if (file_exists(get_custom_file_base().'/themes/'.$theme.'/css_custom/svg.css'))
	{
		$css_file=get_custom_base_url().'/themes/'.$theme.'/css_custom/svg.css';
		$css_file_path=get_custom_file_base().'/themes/'.$theme.'/css_custom/svg.css';
	}
	elseif (file_exists(get_custom_file_base().'/themes/'.$theme.'/css/svg.css'))
	{
		$css_file=get_custom_base_url().'/themes/'.$theme.'/css/svg.css';
		$css_file_path=get_custom_file_base().'/themes/'.$theme.'/css/svg.css';
	}
	elseif (file_exists(get_custom_file_base().'/themes/default/css_custom/svg.css'))
	{
		$css_file=get_custom_base_url().'/themes/default/css_custom/svg.css';
		$css_file_path=get_custom_file_base().'/themes/default/css_custom/svg.css';
	}
	else
	{
		$css_file=get_base_url().'/themes/default/css/svg.css';
		$css_file_path=get_file_base().'/themes/default/css/svg.css';
	}
	$js_file=str_replace(get_custom_file_base(),get_custom_base_url(),javascript_enforce('javascript'));
	global $CSS_FILE_CONTENTS;
	$CSS_FILE_CONTENTS=file_get_contents($css_file_path);
	return '<'.'?xml version="1.0" ?'.'>
<'.'?xml-stylesheet href="'.escape_html($css_file).'" type="text/css"?'.'>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 '.float_to_raw_string(VIEWPORT_WIDTH).' '.float_to_raw_string(VIEWPORT_HEIGHT).'" preserveAspectRatio="XMinYMin meet" width="'.float_to_raw_string(SVG_WIDTH).'" height="'.float_to_raw_string(SVG_HEIGHT).'" version="1.1">
<script type="text/javascript">// <![CDATA[
if (typeof window.addEventListenerAbstract==\'undefined\') addEventListenerAbstract=function(element,the_event,command,capture)
{
	if(element)
	{
		if (typeof element.simulated_events==\'undefined\') element.simulated_events=[];
		if (typeof element.simulated_events[the_event]==\'undefined\') element.simulated_events[the_event]=[];
		element.simulated_events[the_event].push(command);
		
		if(typeof element.addEventListener!=\'undefined\')
		{
			return element.addEventListener(the_event,command,capture);
		}
		else if(typeof element.attachEvent!=\'undefined\')
		{
			return element.attachEvent("on"+the_event,command);
		}
		else return false;
	}
	else return false;
};
//]]></script>
<script xlink:href="'.escape_html($js_file).'" />'.chr(10);
}

/**
 * Get the markup required to finish an SVG document
 *
 * @param  string			The plot markup to insert first
 * @return string			The markup
*/
function _finish_svg($plot='')
{
	return $plot.'</svg>'.chr(10);
}

/**
 * Take the SVG and make all the styles inline. Disabled as not needed for native SVG implementations
 *
 * @param  string			The SVG
 * @return string			... made to have inline styles
*/
function _filter_svg_css($plot)
{
	return $plot;
}

/**
 * Get the markup for a standard pair of axes
 *
 * @param	float				Maximum Y value
 * @param	float				Y scale
 * @param	string			X axis label
 * @param	string			Y axis label
 * @return  string			The markup
*/
function _draw_axes($max_y,$y_scale,$x_label='X axis',$y_label='Y axis')
{
	$output='';
	$prev_i=0;

	// Draw Y-axis markers, labels, and background lines
	for ($i=1;$i<intval(ceil($max_y))+1;$i++)
	{
		if (abs($i*$y_scale-$prev_i*$y_scale)>=MIN_Y_MARKER_DISTANCE)
		{
			$output.='<text x="'.float_to_raw_string(Y_LABEL_WIDTH).'" y="'.float_to_raw_string(PLOT_HEIGHT-($i*$y_scale)+PLOT_HEIGHT_BIAS).'" class="axis_marker_text">'.integer_format($i).'</text>'.chr(10);
			$output.='<line x1="'.float_to_raw_string(Y_LABEL_WIDTH+(Y_AXIS_WIDTH/2)).'" y1="'.float_to_raw_string(PLOT_HEIGHT-($i*$y_scale)+PLOT_HEIGHT_BIAS).'" x2="'.float_to_raw_string(Y_LABEL_WIDTH+Y_AXIS_WIDTH).'" y2="'.float_to_raw_string(PLOT_HEIGHT-($i*$y_scale)+PLOT_HEIGHT_BIAS).'" class="axis_marker" />'.chr(10);
			$output.='<line x1="'.float_to_raw_string(Y_LABEL_WIDTH+Y_AXIS_WIDTH).'" y1="'.float_to_raw_string(PLOT_HEIGHT-($i*$y_scale)+PLOT_HEIGHT_BIAS).'" x2="'.float_to_raw_string(SVG_WIDTH).'" y2="'.float_to_raw_string(PLOT_HEIGHT-($i*$y_scale)+PLOT_HEIGHT_BIAS).'" class="axis_background_line" />'.chr(10);
			$prev_i=$i;
		}
	}
	$output.='<text x="'.float_to_raw_string(Y_LABEL_WIDTH).'" y="'.float_to_raw_string(PLOT_HEIGHT+PLOT_HEIGHT_BIAS).'" class="axis_marker_text">0</text>'.chr(10);

	// X axis
	$output.='<line x1="'.float_to_raw_string(Y_LABEL_WIDTH).'" y1="'.float_to_raw_string(PLOT_HEIGHT+PLOT_HEIGHT_BIAS).'" x2="'.float_to_raw_string(SVG_WIDTH).'" y2="'.float_to_raw_string(PLOT_HEIGHT+PLOT_HEIGHT_BIAS).'" class="axis_line" />'.chr(10);

	// X axis label
	$output.='<text x="'.float_to_raw_string(Y_LABEL_WIDTH+Y_AXIS_WIDTH+X_PADDING).'" y="'.float_to_raw_string(PLOT_HEIGHT+X_AXIS_HEIGHT+PLOT_HEIGHT_BIAS).'" class="axis_text">'.escape_html($x_label).'</text>'.chr(10);

	// Y axis
	$output.='<line x1="'.float_to_raw_string(Y_LABEL_WIDTH+Y_AXIS_WIDTH).'" y1="'.float_to_raw_string(0.0).'" x2="'.float_to_raw_string(Y_LABEL_WIDTH+Y_AXIS_WIDTH).'" y2="'.float_to_raw_string(PLOT_HEIGHT+X_AXIS_HEIGHT+PLOT_HEIGHT_BIAS).'" class="axis_line" />'.chr(10);

	// Y axis label
	$output.='<text transform="translate('.float_to_raw_string(Y_LABEL_WIDTH).','.float_to_raw_string(PLOT_HEIGHT+PLOT_HEIGHT_BIAS).') rotate(270)" class="axis_text">'.escape_html($y_label).'</text>'.chr(10);

	return $output;
}

/**
 * Get the markup for an average line
 *
 * @param  float			Average value
 * @param  float			Y scale
 * @return string			The markup
*/
function _draw_average($average,$y_scale)
{
	if ($average>0.0)
	{
		// Draw an average line
		return '<line x1="'.float_to_raw_string(Y_LABEL_WIDTH+Y_AXIS_WIDTH).'" y1="'.float_to_raw_string(PLOT_HEIGHT-$average*$y_scale+PLOT_HEIGHT_BIAS).'" x2="'.float_to_raw_string(SVG_WIDTH).'" y2="'.float_to_raw_string(PLOT_HEIGHT-$average*$y_scale+PLOT_HEIGHT_BIAS).'" class="average_line" />'.chr(10);
	}
	return '';
}

/**
 * Create a bar chart from the specified data and return the SVG markup
 *
 * @param  array			The data to be used in the creation of the bar chart
 * @param  string			The X axis label
 * @param  string			The Y axis label
 * @param  string			The X axis units label
 * @param  string			The Y axis units label
 * @return string			The SVG markup for the described bar chart
*/
function create_bar_chart($data,$x_label='X axis',$y_label='Y axis',$x_units='',$y_units='')
{
	if ($x_units!='') $x_label.=' ('.$x_units.')';
	if ($y_units!='') $y_label.=' ('.$y_units.')';

	// Work out some stats about our graph
	$average=0.0;
	$max_y=0.0;
	foreach ($data as $value)
	{
		if (is_array($value)) $value=array_shift($value);
		
		if ((is_float($value)?$value:floatval($value))>$max_y) $max_y=is_float($value)?$value:floatval($value);
		$average+=$value;
	}
	$max_x=count($data);
	if (count($data)!=0)
	{
		$average/=count($data);
	}
	if ($max_y==0.0) $max_y=1.0;
	$y_scale=PLOT_HEIGHT/$max_y;

	// Start of output
	$output=_start_svg();

	// Draw bars
	$i=0;
	$colour='333333';
	$plot='';
	$labels='';
	foreach ($data as $key=>$value)
	{
		if (is_array($value)) $value=array_shift($value);
		
		$x=Y_LABEL_WIDTH+Y_AXIS_WIDTH+X_PADDING+$i*(X_PADDING+BAR_WIDTH);
		$y=PLOT_HEIGHT-$value*$y_scale+PLOT_HEIGHT_BIAS;
		$height=(is_float($value)?$value:floatval($value))*$y_scale;

		// Bar and label
		$plot.='<rect id="'.float_to_raw_string($x).float_to_raw_string($y).'_bar" x="'.float_to_raw_string($x).'" y="'.float_to_raw_string($y).'" width="'.float_to_raw_string(BAR_WIDTH).'" height="'.float_to_raw_string($height).'" style="fill: #'.($colour).';" class="bar_chart" />'.chr(10);
		$labels.='<text style="fill: '.(($height==0.0)?'black':'white').'; font-weight: normal" id="'.float_to_raw_string($x).float_to_raw_string($y).'" transform="translate('.float_to_raw_string($x+TEXT_HEIGHT-3).','.float_to_raw_string(PLOT_HEIGHT+PLOT_HEIGHT_BIAS-TEXT_HEIGHT).') rotate(270)" class="bar_chart_text">'.escape_html($key).'</text>
		<script type="text/javascript">
		<![CDATA[
			pageLoaded=true;
			addEventListenerAbstract(document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'"),"mouseover",function(event) { if (window.current_bar) window.current_bar.de_clarify(); window.current_bar=this; document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'").setAttribute("style","fill: red; background-color: black; z-index: 999999;"); },false);
			document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'").de_clarify=function(event) { document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'").setAttribute("style","fill: '.(($height==0.0)?'black':'white').'"); };
			addEventListenerAbstract(document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'"),"focus",function(event) { this.onmouseover(event); },false);
			addEventListenerAbstract(document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'_bar"),"mouseover",function(event) { if (window.current_bar) window.current_bar.de_clarify(); window.current_bar=this; document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'").setAttribute("style","fill: red; background-color: black; z-index: 999999;"); },false);
			document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'_bar").de_clarify=function(event) { document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'").setAttribute("style","fill: '.(($height==0.0)?'black':'white').'"); };
			addEventListenerAbstract(document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'_bar"),"focus",function(event) { this.onmouseover(event); },false);
		]]>
		</script>'.chr(10);

		// Iterate
		$i++;
		$colour=_get_next_colour($colour);
	}

	$output.=_draw_axes($max_y,$y_scale,$x_label,$y_label);
	$output.=_draw_average($average,$y_scale);
	$output.=_finish_svg($plot.$labels);

	return _filter_svg_css($output);
}

/**
 * Create a scatter graph using the data provided and return the SVG markup
 *
 * @param  array			The data to be used in the creation of the scatter graph
 * @param  string			The X axis label
 * @param  string			The Y axis label
 * @param  string			The X axis units label
 * @param  string			The Y axis units label
 * @return string			The SVG markup for the described scatter graph
*/
function create_scatter_graph($data,$x_label='X Axis',$y_label='Y Axis',$x_units='',$y_units='')
{
	if (!defined('CROSS_SIZE')) define('CROSS_SIZE',8.0);

	if ($x_units!='') $x_label.=' ('.$x_units.')';
	if ($y_units!='') $y_label.=' ('.$y_units.')';

	// Work out some stats about our graph
	$average=0.0;
	$max_x=0.0;
	$max_y=0.0;
	foreach ($data as $value)
	{
		if (array_key_exists(0,$value)) $value=array_shift($value);
		if (is_integer($value['t'])) $value['t']=floatval($value['t']);
		if (is_integer($value['value'])) $value['value']=floatval($value['value']);

		if ($value['t']>$max_x) $max_x=$value['t'];
		if ($value['value']>$max_y) $max_y=$value['value'];
		$average+=$value['value'];
	}

	$average/=count($data);
	if ($max_x>0.0) $x_scale=PLOT_WIDTH/$max_x;
	else $x_scale=PLOT_WIDTH;
	if ($max_y>0.0) $y_scale=PLOT_HEIGHT/$max_y;
	else $y_scale=PLOT_HEIGHT;

	// Start of output
	$output=_start_svg();

	// Draw lines
	$first=true;
	$height_differential_plots=0.0;
	$x=0.0;
	$prev_x=0.0;
	$prev_value=array();
	$plot='';
	$path_data='';
	$labels='';
	foreach ($data as $value)
	{
		if (array_key_exists(0,$value)) $value=array_shift($value);
		
		$x=Y_LABEL_WIDTH+Y_AXIS_WIDTH+X_PADDING+$value['t']*$x_scale;

		// Are we too close to the last one?
		if (($first) || (abs($x-$prev_x)>MIN_X_MARKER_DISTANCE) || (abs($value['value']-$prev_value['value'])>$value['value']/3))
		{
			$y=PLOT_HEIGHT-$value['value']*$y_scale+PLOT_HEIGHT_BIAS;

			// The line
			if (!$first) $path_data.=' ';
			$path_data.=float_to_raw_string($x).','.float_to_raw_string($y);

			// The cross
			$plot.='<line x1="'.float_to_raw_string($x-CROSS_SIZE/2.0).'" y1="'.float_to_raw_string($y-CROSS_SIZE/2.0).'" x2="'.float_to_raw_string($x+CROSS_SIZE/2.0).'" y2="'.float_to_raw_string($y+CROSS_SIZE/2.0).'" class="scatter_graph_marker" />'.chr(10);
			$plot.='<line x1="'.float_to_raw_string($x+CROSS_SIZE/2.0).'" y1="'.float_to_raw_string($y-CROSS_SIZE/2.0).'" x2="'.float_to_raw_string($x-CROSS_SIZE/2.0).'" y2="'.float_to_raw_string($y+CROSS_SIZE/2.0).'" class="scatter_graph_marker" />'.chr(10);

			// The label
			if (($first) || (abs($x-$prev_x)>MIN_X_MARKER_DISTANCE))
			{
				$labels.='<text id="'.float_to_raw_string($x).float_to_raw_string($y).'" transform="translate('.float_to_raw_string($x+TEXT_HEIGHT/2).','.float_to_raw_string(PLOT_HEIGHT+X_AXIS_HEIGHT+PLOT_HEIGHT_BIAS).') rotate(270)" class="scatter_graph_text">'.escape_html($value['key']).'</text>
				<script type="text/javascript">
				<![CDATA[
					pageLoaded=true;
					addEventListenerAbstract(document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'"),"mouseover",function(event) { this.setAttribute("style","fill: red; stroke: red; background-color: black; z-index: 999999;"); },false);
					addEventListenerAbstract(document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'"),"mouseout",function(event) { this.setAttribute("style",""); },false);
					addEventListenerAbstract(document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'"),"focus",function(event) { this.onmouseover(event); },false);
					addEventListenerAbstract(document.getElementById("'.float_to_raw_string($x).float_to_raw_string($y).'"),"blur",function(event) { this.onmouseout(event); },false);
				]]>
				</script>'.chr(10);
			}
		}

		$prev_x=$x;
		$prev_value=$value;
		$first=false;
	}

	$plot.='<polyline points="'.$path_data.'" class="scatter_graph" />'.chr(10);

	$output.=_draw_axes($max_y,$y_scale,$x_label,$y_label);
	$output.=_draw_average($average,$y_scale);
	$output.=_finish_svg($plot.$labels);

	return _filter_svg_css($output);
}

/**
 * Draw a pie chart with the specified data and return the SVG markup
 *
 * @param  array	The data to be used in the creation of the pie chart
 * @return string	The SVG markup for the described pie chart
*/
function create_pie_chart($data)
{
	if (!defined('PIE_RADIUS')) define('PIE_RADIUS',190.0);

	arsort($data);

	// Work out some stats about our graph
	$_max_degrees=0.0;
	foreach ($data as $value)
	{
		if (is_array($value)) $value=array_shift($value);
		
		$_max_degrees+=$value;
	}
	$max_degrees=round($_max_degrees);
	if (($max_degrees<355.0) || ($max_degrees>365.0)) fatal_exit(do_lang_tempcode('_BAD_INPUT',float_format($max_degrees)));

	// Start of output
	$output=_start_svg();

	$angle=0.0;
	$colour='333333';
	$data2=array();
	$plot='';
	foreach ($data as $key=>$value)
	{
		if (is_array($value)) $value=array_shift($value);
		
		// We're using degrees again
		$start_angle=$angle;
		$angle+=$value;
		$end_angle=$angle;

		$plot.=_draw_segment($colour,$value,PIE_RADIUS,PIE_RADIUS+cos(deg2rad($start_angle))*PIE_RADIUS,PIE_RADIUS+sin(deg2rad($start_angle))*PIE_RADIUS,PIE_RADIUS+(cos(deg2rad($end_angle))*PIE_RADIUS),PIE_RADIUS+(sin(deg2rad($end_angle))*PIE_RADIUS));
		$colour=_get_next_colour($colour);
		$data2[$key]=round(($value/360)*100,1);
	}

	$output.=_draw_key($data2,'333333',2*PIE_RADIUS+3*X_PADDING,Y_PADDING,'%');
	$output.=_finish_svg($plot);

	return _filter_svg_css($output);
}


