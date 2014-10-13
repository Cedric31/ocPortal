<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core
 */

/**
 * Turn a boring timezone name into the pretty shortened list of ones Microsoft uses and others now use too.
 *
 * @param  string			Boring name
 * @return string			Nice name
 */
function make_nice_timezone_name($in)
{
	$list=get_timezone_list();
	if (array_key_exists($in,$list)) return $list[$in];
	return $in;
}

/**
 * Get a list of timezones.
 *
 * @return array			Timezone (map between boring-style and human-readable name). Sorted in offset order then likelihood orde.
 */
function _get_timezone_list()
{
   $ret=array(
      'Pacific/Niue'=>'(UTC-11:00) Niue, Pago Pago',
      'Pacific/Midway'=>'(UTC-11:00) Midway Island, Samoa',
      'Pacific/Apia'=>'(UTC-11:00) Apia',
      'Pacific/Fakaofo'=>'(UTC-10:00) Fakaofo, Johnston, Rarotonga',
      'America/Adak'=>'(UTC-10:00) Hawaii-Aleutian',
      'Pacific/Honolulu'=>'(UTC-10:00) Hawaii, Honolulu',
      'Pacific/Tahiti'=>'(UTC-10:00) Tahiti',
      'Pacific/Marquesas'=>'(UTC-09:30) Marquesas Islands',
      'America/Juneau'=>'(UTC-09:00) Juneau, Nome, Yakutat',
      'America/Anchorage'=>'(UTC-09:00) Alaska, Anchorage',
      'Pacific/Gambier'=>'(UTC-09:00) Gambier Islands',
      'Pacific/Pitcairn'=>'(UTC-08:00) Pitcairn Islands',
      'America/Tijuana'=>'(UTC-08:00) Baja California, Tijuana',
      'America/Dawson'=>'(UTC-08:00) Dawson, Vancouver, Whitehorse',
      'America/Los_Angeles'=>'(UTC-08:00) Los Angeles, Pacific Time (US & Canada)',
      'America/Boise'=>'(UTC-07:00) Boise',
      'America/Chihuahua'=>'(UTC-07:00) Chihuahua, La Paz (Mexico), Mazatlan',
      'America/Dawson_Creek'=>'(UTC-07:00) Dawson Creek',
      'America/Cambridge_Bay'=>'(UTC-07:00) Cambridge Bay, Edmonton, Inuvik, Yellowknife',
      'America/Denver'=>'(UTC-07:00) Denver, Mountain Time (US & Canada)',
      'America/Hermosillo'=>'(UTC-07:00) Hermosillo',
      'America/Phoenix'=>'(UTC-07:00) Arizona, Phoenix',
      'America/North_Dakota/Center'=>'(UTC-06:00) Center, New Salem',
      'America/Regina'=>'(UTC-06:00) Regina, Saskatchewan',
      'America/Guatemala'=>'(UTC-06:00) Central America, Guatemala',
      'America/Mexico_City'=>'(UTC-06:00) Guadalajara, Mexico City, Monterrey',
      'America/Belize'=>'(UTC-06:00) Belize, Costa Rica, El Salvador, Managua, Swift Current, Tegucigalpa',
      'America/Indiana/Knox'=>'(UTC-06:00) Knox, Menominee, Rainy River, Rankin Inlet, Winnipeg',
      'America/Chicago'=>'(UTC-06:00) Central Time (US & Canada), Chicago',
      'America/Merida'=>'(UTC-06:00) Merida',
      'Pacific/Easter'=>'(UTC-06:00) Easter Island',
      'Pacific/Galapagos'=>'(UTC-06:00) Galapagos',
      'America/Havana'=>'(UTC-05:00) Cuba',
      'America/Atikokan'=>'(UTC-05:00) Atikokan, Cayman, Jamaica, Panama, Port-au-Prince',
      'America/Detroit'=>'(UTC-05:00) Detroit, Grand Turk, Iqaluit, Louisville, Marengo, Monticello',
      'America/Montreal'=>'(UTC-05:00) Montreal, Nassau, Nipigon, Pangnirtung, Petersburg, Thunder Bay',
      'America/Toronto'=>'(UTC-05:00) Toronto, Vevay, Vincennes',
      'America/New_York'=>'(UTC-05:00) Eastern Time (US & Canada), New York',
      'America/Guayaquil'=>'(UTC-05:00) Guayaquil',
      'America/Bogota'=>'(UTC-05:00) Bogota, Lima, Quito',
      'America/Indiana/Indianapolis'=>'(UTC-05:00) Indiana (East), Indianapolis',
      'America/Caracas'=>'(UTC-04:30) Caracas',
      'America/Anguilla'=>'(UTC-04:00) Anguilla, Antigua, Curacao, Montserrat, St. Thomas',
      'Atlantic/Stanley'=>'(UTC-04:00) Faukland Islands',
      'Antarctica/Palmer'=>'(UTC-04:00) Palmer',
      'America/Aruba'=>'(UTC-04:00) Aruba, Barbados, Blanc-Sablon, Dominica, Grenada, Guadeloupe',
      'America/Martinique'=>'(UTC-04:00) Martinique, Port of Spain, Puerto Rico, Santo Domingo, St. Kitts, St. Lucia',
      'America/St_Vincent'=>'(UTC-04:00) St. Vincent, Tortola',
      'America/Goose_Bay'=>'(UTC-04:00) Atlantic Time (Goose Bay), Bermuda, Moncton',
      'America/Halifax'=>'(UTC-04:00) Atlantic Time (Canada), Halifax',
      'America/Boa_Vista'=>'(UTC-04:00) Boa Vista, Eirunepe, Porto Velho, Rio Branco',
      'America/Campo_Grande'=>'(UTC-04:00) Brazil, Cuiaba',
      'America/Manaus'=>'(UTC-04:00) Manaus',
      'America/Guyana'=>'(UTC-04:00) Guyana',
      'America/Thule'=>'(UTC-04:00) Thule',
      'America/Santiago'=>'(UTC-04:00) Santiago',
      'America/Asuncion'=>'(UTC-04:00) Asuncion',
      'America/La_Paz'=>'(UTC-04:00) Georgetown, La Paz (Bolivia), San Juan',
      'America/St_Johns'=>'(UTC-03:30) Newfoundland, St. Johns',
      'America/Argentina/Buenos_Aires'=>'(UTC-03:00) Buenos Aires',
      'America/Argentina/Catamarca'=>'(UTC-03:00) Catamarca, Cordoba, Jujuy, La Rioja, Mendoza, Rio Gallegos',
      'Antarctica/Rothera'=>'(UTC-03:00) Rothera, Tucuman, Ushuaia',
      'America/Araguaina'=>'(UTC-03:00) Araguaina, Bahia, Belem, Fortaleza, Maceio, Recife',
      'America/Sao_Paulo'=>'(UTC-03:00) Brasilia, Sao Paulo',
      'America/Godthab'=>'(UTC-03:00) Greenland',
      'America/Montevideo'=>'(UTC-03:00) Montevideo',
      'America/Cayenne'=>'(UTC-03:00) Cayenne',
      'America/Miquelon'=>'(UTC-03:00) Miquelon, St. Pierre',
      'America/Paramaribo'=>'(UTC-03:00) Paramaribo',
      'Atlantic/South_Georgia'=>'(UTC-02:00) South Georgia',
      'America/Noronha'=>'(UTC-02:00) Mid-Atlantic',
      'Atlantic/Azores'=>'(UTC-01:00) Azores',
      'Atlantic/Cape_Verde'=>'(UTC-01:00) Cape Verde Islands',
      'America/Scoresbysund'=>'(UTC-01:00) Scoresbysund',
      'Africa/El_Aaiun'=>'(UTC+00:00) El Aaiun, St. Helena',
      'Atlantic/Madeira'=>'(UTC+00:00) Madeira',
      'Europe/London'=>'(UTC+00:00) Belfast, Dublin, Edinburgh, Lisbon, London',
      'UTC'=>'(UTC) No daylight saving',
      'Africa/Abidjan'=>'(UTC+00:00) Abidjan, Accra, Bamako, Banjul, Bissau, Conakry',
      'Africa/Dakar'=>'(UTC+00:00) Dakar, Danmarkshavn, Freetown, Lome, Nouakchott, Ouagadougou',
      'Africa/Sao_Tome'=>'(UTC+00:00) Sao Tome',
      'Africa/Monrovia'=>'(UTC+00:00) Monrovia, Reykjavik',
      'Africa/Casablanca'=>'(UTC+00:00) Casablanca',
      'Atlantic/Canary'=>'(UTC+00:00) Canary, Faroe',
      'Europe/Belgrade'=>'(UTC+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague',
      'Europe/Warsaw'=>'(UTC+01:00) Sarajevo, Skopje, Warsaw, Zagreb',
      'Europe/Andorra'=>'(UTC+01:00) Andorra, Ceuta, Gibraltar, Luxembourg, Malta, Monaco',
      'Europe/Oslo'=>'(UTC+01:00) Oslo, Tirane, Tunis, Vaduz, Zurich',
      'Africa/Windhoek'=>'(UTC+01:00) Windhoek',
      'Europe/Brussels'=>'(UTC+01:00) Brussels, Copenhagen, Madrid, Paris',
      'Africa/Algiers'=>'(UTC+01:00) Algiers, West Central Africa',
      'Europe/Amsterdam'=>'(UTC+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
      'Africa/Bangui'=>'(UTC+01:00) Bangui, Brazzaville, Douala, Kinshasa, Lagos, Libreville',
      'Africa/Luanda'=>'(UTC+01:00) Luanda, Malabo, Ndjamena, Niamey, Porto-Novo',
      'Asia/Gaza'=>'(UTC+02:00) Gaza',
      'Europe/Simferopol'=>'(UTC+02:00) Simferopol, Uzhgorod, Zaporozhye',
      'Africa/Bujumbura'=>'(UTC+02:00) Bujumbura, Gaborone, Kigali, Lubumbashi, Lusaka, Maputo',
      'Europe/Minsk'=>'(UTC+02:00) Minsk',
      'Africa/Tripoli'=>'(UTC+02:00) Tripoli',
      'Europe/Chisinau'=>'(UTC+02:00) Chisinau, Kaliningrad, Nicosia, Syria',
      'Africa/Cairo'=>'(UTC+02:00) Cairo',
      'Europe/Helsinki'=>'(UTC+02:00) Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius',
      'Europe/Athens'=>'(UTC+02:00) Athens, Bucharest, Istanbul',
      'Asia/Jerusalem'=>'(UTC+02:00) Jerusalem',
      'Asia/Amman'=>'(UTC+02:00) Amman',
      'Asia/Beirut'=>'(UTC+02:00) Beirut',
      'Africa/Blantyre'=>'(UTC+02:00) Blantyre, Harare, Pretoria',
      'Africa/Johannesburg'=>'(UTC+02:00) Johannesburg, Maseru, Mbabane',
      'Asia/Kuwait'=>'(UTC+03:00) Kuwait, Riyadh',
      'Asia/Aden'=>'(UTC+03:00) Aden, Bahrain',
      'Asia/Baghdad'=>'(UTC+03:00) Baghdad',
      'Asia/Qatar'=>'(UTC+03:00) Qatar, Syowa',
      'Africa/Nairobi'=>'(UTC+03:00) Nairobi',
      'Africa/Addis_Ababa'=>'(UTC+03:00) Addis Ababa, Antananarivo, Asmara, Comoro, Dar es Salaam, Djibouti',
      'Africa/Kampala'=>'(UTC+03:00) Kampala, Khartoum, Mayotte, Mogadishu',
      'Europe/Moscow'=>'(UTC+03:00) Moscow, St. Petersburg, Volgograd',
      'Asia/Tehran'=>'(UTC+03:30) Tehran',
      'Asia/Dubai'=>'(UTC+04:00) Abu Dhabi, Muscat',
      'Asia/Baku'=>'(UTC+04:00) Baku',
      'Asia/Yerevan'=>'(UTC+04:00) Yerevan',
      'Asia/Tbilisi'=>'(UTC+04:00) Tbilisi',
      'Indian/Mauritius'=>'(UTC+04:00) Mauritius, Port Louis',
      'Indian/Reunion'=>'(UTC+04:00) Reunion',
      'Europe/Samara'=>'(UTC+04:00) Samara',
      'Indian/Mahe'=>'(UTC+04:00) Mahe',
      'Asia/Kabul'=>'(UTC+04:30) Kabul',
      'Indian/Kerguelen'=>'(UTC+05:00) Kerguelen',
      'Indian/Maldives'=>'(UTC+05:00) Maldives',
      'Asia/Karachi'=>'(UTC+05:00) Islamabad, Karachi',
      'Asia/Dushanbe'=>'(UTC+05:00) Dushanbe',
      'Asia/Ashgabat'=>'(UTC+05:00) Ashgabat',
      'Asia/Samarkand'=>'(UTC+05:00) Samarkand',
      'Asia/Tashkent'=>'(UTC+05:00) Tashkent',
      'Asia/Aqtau'=>'(UTC+05:00) Aqtau, Aqtobe',
      'Asia/Oral'=>'(UTC+05:00) Oral',
      'Asia/Yekaterinburg'=>'(UTC+05:00) Ekaterinburg',
      'Asia/Calcutta'=>'(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi',
      'Asia/Colombo'=>'(UTC+05:30) Colombo, Sri Jayawardenepura',
      'Asia/Katmandu'=>'(UTC+05:45) Kathmandu',
      'Indian/Chagos'=>'(UTC+06:00) Chagos, Mawson, Vostok',
      'Asia/Thimphu'=>'(UTC+06:00) Thimphu',
      'Asia/Dhaka'=>'(UTC+06:00) Astana, Dhaka',
      'Asia/Qyzylorda'=>'(UTC+06:00) Qyzylorda',
      'Asia/Bishkek'=>'(UTC+06:00) Bishkek',
      'Asia/Almaty'=>'(UTC+06:00) Almaty, Novosibirsk',
      'Asia/Omsk'=>'(UTC+06:00) Omsk',
      'Indian/Cocos'=>'(UTC+06:30) Cocos',
      'Asia/Rangoon'=>'(UTC+06:30) Yangon (Rangoon)',
      'Indian/Christmas'=>'(UTC+07:00) Christmas, Davis, Pontianak, Saigon',
      'Asia/Hovd'=>'(UTC+07:00) Hovd',
      'Asia/Krasnoyarsk'=>'(UTC+07:00) Krasnoyarsk',
      'Asia/Phnom_Penh'=>'(UTC+07:00) Phnom Penh, Vientiane',
      'Asia/Bangkok'=>'(UTC+07:00) Bangkok, Hanoi, Jakarta',
      'Antarctica/Casey'=>'(UTC+08:00) Casey, Harbin, Kashgar, Kuching',
      'Asia/Brunei'=>'(UTC+08:00) Brunei',
      'Asia/Makassar'=>'(UTC+08:00) Makassar',
      'Asia/Shanghai'=>'(UTC+08:00) Shanghai',
      'Asia/Hong_Kong'=>'(UTC+08:00) Beijing, Chongqing, Hong Kong, Urumqi',
      'Asia/Macau'=>'(UTC+08:00) Macau',
      'Asia/Irkutsk'=>'(UTC+08:00) Irkutsk, Ulaan Bataar',
      'Asia/Manila'=>'(UTC+08:00) Manila',
      'Asia/Kuala_Lumpur'=>'(UTC+08:00) Kuala Lumpur, Singapore',
      'Asia/Taipei'=>'(UTC+08:00) Taipei',
      'Australia/Perth'=>'(UTC+08:00) Perth',
      'Australia/Eucla'=>'(UTC+08:45) Eucla',
      'Asia/Choibalsan'=>'(UTC+09:00) Choibalsan',
      'Asia/Jayapura'=>'(UTC+09:00) Jayapura',
      'Asia/Pyongyang'=>'(UTC+09:00) Pyongyang',
      'Asia/Seoul'=>'(UTC+09:00) Seoul',
      'Pacific/Palau'=>'(UTC+09:00) Palau',
      'Asia/Dili'=>'(UTC+09:00) Dili',
      'Asia/Tokyo'=>'(UTC+09:00) Osaka, Sapporo, Tokyo',
      'Asia/Yakutsk'=>'(UTC+09:00) Yakutsk',
      'Australia/Darwin'=>'(UTC+09:30) Darwin',
      'Australia/Broken_Hill'=>'(UTC+09:30) Broken Hill',
      'Australia/Adelaide'=>'(UTC+09:30) Adelaide',
      'Antarctica/DumontDUrville'=>'(UTC+10:00) DumontDUrville, Saipan',
		'Australia/Melbourne'=>'(UTC+10:00) Melbourne',
		'Australia/Sydney'=>'(UTC+10:00) Canberra, Sydney',
      'Australia/Currie'=>'(UTC+10:00) Currie, Lindeman',
      'Australia/Brisbane'=>'(UTC+10:00) Brisbane',
      'Asia/Sakhalin'=>'(UTC+10:00) Sakhalin',
      'Australia/Hobart'=>'(UTC+10:00) Hobart',
      'Pacific/Truk'=>'(UTC+10:00) Truk',
      'Asia/Vladivostok'=>'(UTC+10:00) Vladivostok',
      'Pacific/Guam'=>'(UTC+10:00) Guam, Port Moresby',
      'Australia/Lord_Howe'=>'(UTC+10:30) Lord Howe Island',
      'Asia/Magadan'=>'(UTC+11:00) Magadan, New Caledonia, Solomon Islands',
      'Pacific/Kosrae'=>'(UTC+11:00) Kosrae',
      'Pacific/Noumea'=>'(UTC+11:00) Noumea',
      'Pacific/Ponape'=>'(UTC+11:00) Ponape',
      'Pacific/Guadalcanal'=>'(UTC+11:00) Guadalcanal',
      'Pacific/Efate'=>'(UTC+11:00) Efate',
      'Pacific/Norfolk'=>'(UTC+11:30) Norfolk Island',
      'Asia/Anadyr'=>'(UTC+12:00) Anadyr',
      'Pacific/Wake'=>'(UTC+12:00) Wake',
      'Antarctica/McMurdo'=>'(UTC+12:00) McMurdo',
      'Pacific/Fiji'=>'(UTC+12:00) Fiji, Marshall Islands',
      'Pacific/Tarawa'=>'(UTC+12:00) Tarawa',
      'Asia/Kamchatka'=>'(UTC+12:00) Kamchatka',
      'Pacific/Kwajalein'=>'(UTC+12:00) Kwajalein, Majuro',
      'Pacific/Nauru'=>'(UTC+12:00) Nauru',
      'Pacific/Auckland'=>'(UTC+12:00) Auckland, Wellington',
      'Pacific/Funafuti'=>'(UTC+12:00) Funafuti',
      'Pacific/Wallis'=>'(UTC+12:00) Wallis',
      'Pacific/Chatham'=>'(UTC+12:45) Chatham Islands',
      'Pacific/Enderbury'=>'(UTC+13:00) Enderbury',
      'Pacific/Tongatapu'=>'(UTC+13:00) Nuku Alofa, Tongatapu',
      'Pacific/Kiritimati'=>'(UTC+14:00) Kiritimati'
   );

	// Make shown times dynamic to consider DST etc
	foreach ($ret as $zone=>$title)
	{
		$offset=(tz_time(time(),$zone)-time())/3600.0;
		$new='(UTC';
		$new.=($offset<0.0)?'-':'+';
		$offset=abs($offset);
		$hours=intval(floor($offset));
		$new.=str_pad(strval($hours),2,'0',STR_PAD_LEFT);
		$new.=':';
		$new.=str_pad(strval(($hours-$offset)*60),2,'0',STR_PAD_LEFT);
		$new.=') ';
		$title=preg_replace('#^\(UTC[+-]\d\d:\d\d\) #',$new,$title);
		$ret[$zone]=$title;
	}

	return $ret;
}

/**
 * Check a POST inputted date for validity, and get the dat/time components.
 *
 * @param  ID_TEXT		The stub of the parameter name (stub_year, stub_month, stub_day, stub_hour, stub_minute)
 * @param  ?integer		Default year (NULL: none)
 * @param  ?integer		Default month (NULL: none)
 * @param  ?integer		Default day (NULL: none)
 * @param  boolean		Whether to allow over get parameters also
 * @return array			The date/time components
 */
function get_input_date_components($stub,$year=NULL,$month=NULL,$day=NULL,$get_also=false)
{
	$default_ret=array($year,$month,$day,0,0);

	$timezone=post_param('timezone',get_users_timezone());
	if ($get_also)
	{
		$date=either_param($stub,NULL);
		if (!is_null($date)) // HTML5 input style
		{
			$matches=array();
			if (preg_match('#^(\d\d\d\d)-(\d\d)-(\d\d)$#',$date,$matches)==0) return $default_ret;

			$year=intval($matches[1]);
			$month=intval($matches[2]);
			$day=intval($matches[3]);

			$time=either_param($stub.'_time',NULL);
			if ((!is_null($time)) && (preg_match('#^(\d\d):(\d\d)$#',$time,$matches)!=0))
			{
				$hour=intval($matches[1]);
				$minute=intval($matches[2]);
			} else
			{
				$hour=NULL;
				$minute=NULL;
			}
		} else // Legacy input style
		{
			$year=either_param_integer($stub.'_year',NULL);
			if (is_null($year)) return $default_ret;
			$month=either_param_integer($stub.'_month',NULL);
			if (is_null($month)) return $default_ret;
			$day=either_param_integer($stub.'_day',NULL);
			if (is_null($day)) return $default_ret;

			$hour=either_param_integer($stub.'_hour',NULL);
			$minute=either_param_integer($stub.'_minute',NULL);
		}
	} else
	{
		$date=post_param($stub,NULL);
		if (!is_null($date)) // HTML5 input style
		{
			$matches=array();
			if (preg_match('#^(\d\d\d\d)-(\d\d)-(\d\d)$#',$date,$matches)==0) return $default_ret;

			$year=intval($matches[1]);
			$month=intval($matches[2]);
			$day=intval($matches[3]);

			$time=post_param($stub.'_time',NULL);
			if ((!is_null($time)) && (preg_match('#^(\d\d):(\d\d)$#',$time,$matches)!=0))
			{
				$hour=intval($matches[1]);
				$minute=intval($matches[2]);
			} else
			{
				$hour=NULL;
				$minute=NULL;
			}
		} else // Legacy input style
		{
			$year=post_param_integer($stub.'_year',NULL);
			if (is_null($year)) return $default_ret;
			$month=post_param_integer($stub.'_month',NULL);
			if (is_null($month)) return $default_ret;
			$day=post_param_integer($stub.'_day',NULL);
			if (is_null($day)) return $default_ret;

			$hour=post_param_integer($stub.'_hour',NULL);
			$minute=post_param_integer($stub.'_minute',NULL);
		}
	}	

	if (is_null($hour))
	{
		if (strpos($stub,'end')!==false)
		{
			$hour=23;
			$minute=59;
		} else
		{
			$hour=0;
			$minute=0;
		}
	}

	if (!checkdate($month,$day,$year)) warn_exit(do_lang_tempcode('INVALID_DATE_GIVEN'));

	return array($year,$month,$day,$hour,$minute);
}

/**
 * Check a POST inputted date for validity, and get the Unix timestamp for the inputted date.
 *
 * @param  ID_TEXT		The stub of the parameter name (stub_year, stub_month, stub_day, stub_hour, stub_minute)
 * @param  boolean		Whether to allow over get parameters also
 * @param  boolean		Whether to do timezone conversion
 * @return ?TIME			The timestamp of the date (NULL: no input date was chosen)
 */
function _get_input_date($stub,$get_also=false,$do_timezone_conversion=true)
{
	$timezone=post_param('timezone',get_users_timezone());

	list($year,$month,$day,$hour,$minute)=get_input_date_components($stub,NULL,NULL,NULL,$get_also);
	if (is_null($year)) return NULL;

	$time=mktime($hour,$minute,0,$month,$day,$year);
	if ($do_timezone_conversion)
	{
		if (($year>=1970) || (@strftime('%Y',@mktime(0,0,0,1,1,1963))=='1963')) // Only try and do timezone conversion if we can do proper maths this far back
		{
			$time=$time*2-tz_time($time,$timezone);
		}
	}

	return $time;
}
