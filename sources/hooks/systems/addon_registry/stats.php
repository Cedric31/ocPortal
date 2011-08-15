<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

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

class Hook_addon_registry_stats
{

	/**
	 * Get a list of file permissions to set
	 *
	 * @return array			File permissions to set
	 */
	function get_chmod_array()
	{
		return array();
	}

	/**
	 * Get the version of ocPortal this addon is for
	 *
	 * @return float			Version number
	 */
	function get_version()
	{
		return ocp_version_number();
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'Show advanced graphs (analytics) and dumps of raw data relating to your website activity.';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array(),
		);
	}

	/**
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(

			'sources/hooks/modules/admin_setupwizard/stats.php',
			'sources/hooks/systems/config_default/stats_store_time.php',
			'sources/hooks/systems/config_default/super_logging.php',
			'sources/hooks/systems/realtime_rain/stats.php',
			'data/modules/admin_cleanup/page_stats.php.pre',
			'sources/hooks/modules/admin_cleanup/page_stats.php',
			'sources/hooks/systems/cron/stats_clean.php',
			'sources/hooks/systems/do_next_menus/stats.php',
			'sources/hooks/systems/non_active_urls/stats.php',
			'sources/hooks/systems/addon_registry/stats.php',
			'sources/hooks/modules/admin_import_types/stats.php',
			'sources/hooks/modules/admin_stats/.htaccess',
			'sources/hooks/modules/admin_stats/index.html',
			'STATS_GRAPH.tpl',
			'STATS_SCREEN.tpl',
			'STATS_SCREEN_ISCREEN.tpl',
			'STATS_OVERVIEW_SCREEN.tpl',
			'adminzone/pages/modules/admin_stats.php',
			'stats.css',
			'svg.css',
			'themes/default/images/bigicons/clear_stats.png',
			'data/modules/admin_stats/.htaccess',
			'data/modules/admin_stats/index.html',
			'data/modules/admin_stats/IP_Country.txt', // http://geolite.maxmind.com/download/geoip/database/
			'data_custom/modules/admin_stats/index.html',
			'lang/EN/stats.ini',
			'sources/hooks/modules/admin_cleanup/stats.php',
			'themes/default/images/bigicons/users_online.png',
			'themes/default/images/pagepics/statistics_usersonline.png',
			'themes/default/images/bigicons/statistics_search.png',
			'themes/default/images/pagepics/statistics_search.png',
			'sources/svg.php',
			'themes/default/images/bigicons/geolocate.png',
			'themes/default/images/bigicons/load_times.png',
			'themes/default/images/bigicons/page_views.png',
			'themes/default/images/bigicons/statistics.png',
			'themes/default/images/bigicons/submits.png',
			'themes/default/images/bigicons/top_keywords.png',
			'themes/default/images/bigicons/top_referrers.png',
			'themes/default/images/pagepics/installgeolocationdata.png',
			'themes/default/images/pagepics/loadtimes.png',
			'themes/default/images/pagepics/statistics.png',
			'themes/default/images/pagepics/statistics_clear.png',
			'themes/default/images/pagepics/statistics_google.png',
			'themes/default/images/pagepics/statistics_pageviews.png',
			'themes/default/images/pagepics/statistics_referrers.png',
			'themes/default/images/pagepics/submits.png',
		);
	}


	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array                 The mapping
	*/
	function tpl_previews()
	{
	   return array(
			'STATS_GRAPH.tpl'=>'administrative__stats_screen',
			'STATS_SCREEN.tpl'=>'administrative__stats_screen',
			'STATS_OVERVIEW_SCREEN.tpl'=>'administrative__stats_screen_overview',
			'STATS_SCREEN_ISCREEN.tpl'=>'administrative__stats_screen_iscreen',
		);
	}


	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__stats_screen()
	{
		$graph = do_lorem_template('STATS_GRAPH',array('GRAPH'=>placeholder_url(),'TITLE'=>lorem_phrase(),'TEXT'=>lorem_sentence(),'KEYWORDS_SHARE'=>lorem_word(),'DESCRIPTION_KEYWORDS_SHARE'=>lorem_word()));

	   //results_table starts
		//results_entry starts
		$cells = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>$v)));
		}
		$fields_title = $cells;

		$fields = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells = new ocp_tempcode();
			foreach (placeholder_array() as $k=>$v)
			{
				$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>lorem_word()),NULL,false,'RESULTS_TABLE_FIELD'));
			}
			$fields->attach(do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false,'RESULTS_TABLE_ENTRY'));
		}
		//results_entry ends

		$selectors = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$selectors->attach(do_lorem_template('RESULTS_BROWSER_SORTER',array('SELECTED'=>'','NAME'=>lorem_word(),'VALUE'=>lorem_word())));
		}
		$sort = do_lorem_template('RESULTS_BROWSER_SORT',array('HIDDEN'=>'','SORT'=>lorem_word(),'RAND'=>placeholder_random(),'URL'=>placeholder_url(),'SELECTORS'=>$selectors));

		$results_table = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$fields,'MESSAGE'=>new ocp_tempcode(),'SORT'=>$sort,'BROWSER'=>lorem_word(),'WIDTHS'=>array()),NULL,false,'RESULTS_TABLE');
		//results_table ends

	   return array(
			lorem_globalise(
				do_lorem_template('STATS_SCREEN',array(
					'TITLE'=>lorem_title(),
					'GRAPH'=>$graph,
					'STATS'=>$results_table,
				)
         ),NULL,'',true),
	   );
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__stats_screen_overview()
	{
		//results_table starts
		//results_entry starts
		$cells = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>$v)));
		}
		$fields_title = $cells;

		$fields = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells = new ocp_tempcode();
			foreach (placeholder_array() as $k=>$v)
			{
				$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>lorem_word()),NULL,false,'RESULTS_TABLE_FIELD'));
			}
			$fields->attach(do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false,'RESULTS_TABLE_ENTRY'));
		}
		//results_entry ends

		$selectors = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$selectors->attach(do_lorem_template('RESULTS_BROWSER_SORTER',array('SELECTED'=>'','NAME'=>lorem_word(),'VALUE'=>lorem_word())));
		}
		$sort = do_lorem_template('RESULTS_BROWSER_SORT',array('HIDDEN'=>'','SORT'=>lorem_word(),'RAND'=>placeholder_random(),'URL'=>placeholder_url(),'SELECTORS'=>$selectors));

		$list_views = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$fields,'MESSAGE'=>new ocp_tempcode(),'SORT'=>$sort,'BROWSER'=>lorem_word(),'WIDTHS'=>array()),NULL,false,'RESULTS_TABLE');
		//results_table ends

	   return array(
         lorem_globalise(
				do_lorem_template('STATS_OVERVIEW_SCREEN',
					array(
						'TITLE'=>lorem_title(),
						'STATS_VIEWS'=>$list_views,
						'GRAPH_VIEWS_MONTHLY'=>lorem_phrase(),
						'STATS_VIEWS_MONTHLY'=>lorem_phrase(),
					)
	         ),NULL,'',true),
	   );
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__stats_screen_iscreen()
	{
		$graph_regionality = do_lorem_template('STATS_GRAPH',array('GRAPH'=>placeholder_url(),'TITLE'=>lorem_phrase(),'TEXT'=>lorem_sentence(),'KEYWORDS_SHARE'=>lorem_word(),'DESCRIPTION_KEYWORDS_SHARE'=>lorem_word()));
		$graph_keywords = $graph_regionality;

		//results_entry starts
		$cells = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>$v)));
		}
		$fields_title = $cells;

		$fields = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$cells = new ocp_tempcode();
			foreach (placeholder_array() as $k=>$v)
			{
				$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>lorem_word()),NULL,false,'RESULTS_TABLE_FIELD'));
			}
			$fields->attach(do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false,'RESULTS_TABLE_ENTRY'));
		}
		//results_entry ends

		$selectors = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
			$selectors->attach(do_lorem_template('RESULTS_BROWSER_SORTER',array('SELECTED'=>'','NAME'=>lorem_word(),'VALUE'=>lorem_word())));
		$sort = do_lorem_template('RESULTS_BROWSER_SORT',array('HIDDEN'=>'','SORT'=>lorem_word(),'RAND'=>placeholder_random(),'URL'=>placeholder_url(),'SELECTORS'=>$selectors));
		$list_regionality = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$fields,'MESSAGE'=>new ocp_tempcode(),'SORT'=>$sort,'BROWSER'=>lorem_word(),'WIDTHS'=>array()),NULL,false,'RESULTS_TABLE');

		$selectors = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
			$selectors->attach(do_lorem_template('RESULTS_BROWSER_SORTER',array('SELECTED'=>'','NAME'=>lorem_word(),'VALUE'=>lorem_word())));
		$sort = do_lorem_template('RESULTS_BROWSER_SORT',array('HIDDEN'=>'','SORT'=>lorem_word(),'RAND'=>placeholder_random(),'URL'=>placeholder_url(),'SELECTORS'=>$selectors));
		$list_views = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$fields,'MESSAGE'=>new ocp_tempcode(),'SORT'=>$sort,'BROWSER'=>lorem_word(),'WIDTHS'=>array()),NULL,false,'RESULTS_TABLE');

		$selectors = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
			$selectors->attach(do_lorem_template('RESULTS_BROWSER_SORTER',array('SELECTED'=>'','NAME'=>lorem_word(),'VALUE'=>lorem_word())));
		$sort = do_lorem_template('RESULTS_BROWSER_SORT',array('HIDDEN'=>'','SORT'=>lorem_word(),'RAND'=>placeholder_random(),'URL'=>placeholder_url(),'SELECTORS'=>$selectors));
		$list_keywords = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$fields,'MESSAGE'=>new ocp_tempcode(),'SORT'=>$sort,'BROWSER'=>lorem_word(),'WIDTHS'=>array()),NULL,false,'RESULTS_TABLE');

		//results_table ends

		return array(
			lorem_globalise(do_lorem_template('STATS_SCREEN_ISCREEN',
				array(
					'TITLE'=>lorem_title(),
					'GRAPH_REGIONALITY'=>$graph_regionality,
					'STATS_REGIONALITY'=>$list_regionality,
					'STATS_VIEWS'=>$list_views,
					'GRAPH_KEYWORDS'=>$graph_keywords,
					'STATS_KEYWORDS'=>$list_keywords,
					'GRAPH_VIEWS_HOURLY'=>lorem_phrase(),
					'STATS_VIEWS_HOURLY'=>lorem_phrase(),
					'GRAPH_VIEWS_DAILY'=>lorem_phrase(),
					'STATS_VIEWS_DAILY'=>lorem_phrase(),
					'GRAPH_VIEWS_WEEKLY'=>lorem_phrase(),
					'STATS_VIEWS_WEEKLY'=>lorem_phrase(),
					'GRAPH_VIEWS_MONTHLY'=>lorem_phrase(),
					'STATS_VIEWS_MONTHLY'=>lorem_phrase(),
					'GRAPH_IP'=>placeholder_ip(),
					'STATS_IP'=>placeholder_ip(),
					'GRAPH_BROWSER'=>lorem_phrase(),
					'STATS_BROWSER'=>lorem_phrase(),
					'GRAPH_REFERRER'=>lorem_phrase(),
					'STATS_REFERRER'=>lorem_phrase(),
					'GRAPH_OS'=>lorem_phrase(),
					'STATS_OS'=>lorem_phrase(),
				)
		   ),NULL,'',true),
		);
	}
}