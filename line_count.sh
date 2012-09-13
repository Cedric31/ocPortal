#!/bin/bash

wc -l `find . -name "*.css" -or -name "*.php" -or -name "*.tpl" -or -name "*.ini" -or -name "*.htm" -or -name "*.sh" -or -name "*.java" -or -name "*.js" -or -name "*.txt" -or -name "*.bat" -or -name "*.config" -or -name "*.htaccess" -or -name "*.hdf" | grep -v "\(\./data_custom/errorlog.php\|\./data_custom/execute_temp.php\|\./data_custom/jabber-logs\|\./data_custom/javaupload\|\./data_custom/latest_activity.txt\|\./data_custom/permissioncheckslog.php\|\./docs/api\|\./docs/ocportal-api-template\|\./themes/default/templates_cached/EN\|\./data_custom/upload-crop\|\\./info\\.php\|\\./uploads\|\\./_config\\.php\|\\./sources_custom/browser_detect\\.php\|\\./sources_custom/facebook\|\\./sources_custom/geshi\|\\./sources_custom/getid3\|\\./sources_custom/GTranslate\\.php\|\\./sources_custom/openid\\.php\|\\./sources_custom/php-crossword\|\\./sources_custom/programe\|\\./sources_custom/stemmer_EN\\.php\|\\./sources_custom/Swift-4\\.1\\.1\|\\./sources_custom/twitter\\.php\|\\./tracker\|\\./_old\|\\./_tests/html_dump\|\\./_tests/ocptest\|\\./_tests/screens_tested\|\\./_tests/simpletest\|\\./persistant_cache\|\\./persistent_cache\|\\./exports\|\\./imports\|\\./data_custom/builds/debian\|\\./data/ckeditor\|\\./data/editarea\|\\./data/javaupload\|\\./nbproject\|\\./_tests/codechecker/codechecker\\.app\|\\./_tests/codechecker/netbeans/nbproject\|\\./_tests/codechecker/netbeans/build\|\\./_tests/codechecker/netbeans/dist\|\\./safe_mode_temp\|\\./themes/default/templates/JAVASCRIPT_SWFUPLOAD\\.tpl\|\\./themes/default/templates/JAVASCRIPT_XSL_MOPUP\\.tpl\|\\./themes/default/templates/JAVASCRIPT_YAHOO_2\\.tpl\|\\./themes/default/templates/JAVASCRIPT_YAHOO_EVENTS\\.tpl\|\\./themes/default/templates/JAVASCRIPT_YAHOO\\.tpl\|\\./themes/default/templates/JAVASCRIPT_MORE\\.tpl\|\\./themes/default/templates/JAVASCRIPT_JWPLAYER\\.tpl\|\\./themes/default/templates/JAVASCRIPT_COLOUR_PICKER\\.tpl\|\\./themes/default/templates/JAVASCRIPT_DRAGDROP\\.tpl\|\\./themes/default/templates/JAVASCRIPT_SOUND\\.tpl\|\\./themes/default/templates/JAVASCRIPT_DATE_CHOOSER\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_XMPP_DOM-ALL\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_XMPP_XMPP4JS\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_PLUPLOAD\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_JQUERY\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_XMPP_PROTOTYPE\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_SWFUPLOAD\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_JQUERY_EFFECTS_CORE\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_COLUMNS\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_XMPP_CRYPTO\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_JQUERY_FLIP\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_TAG_CLOUD\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_JQUERY_UI_CORE\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_OPENID\\.tpl\|\\./themes/default/templates_custom/JAVASCRIPT_BASE64\\.tpl\|\./data/modules/admin_stats/IP_Country\.txt\|.*/.htaccess\|.*/index\.html\).*"`
