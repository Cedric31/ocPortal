<?php

class Hook_symbol_FB_CONNECT_LOGGED_OUT
{
	function run($param)
	{
		require_code('facebook_connect');

		if (isset($GLOBALS['FACEBOOK_LOGOUT']))
			return '_true';
		return '_false';
	}
}
