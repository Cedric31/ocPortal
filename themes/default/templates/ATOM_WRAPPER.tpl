<?xml version="1.0" encoding="{!charset}"?>
<?xml-stylesheet href="{$FIND_SCRIPT*,backend}?type=xslt-atom" type="text/xsl"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{$LANG*}">
	<title>{$SITE_NAME*}: {MODE*}</title>
	<link rel="alternate" type="html" href="{$BASE_URL*}" title="{$SITE_NAME*}" />
	<link rel="self" href="{$FIND_SCRIPT*,backend}?type=atom&amp;mode={MODE*}&amp;cutoff={CUTOFF*}&amp;filter={FILTER*}" />
	<updated>{DATE*}</updated>
	<author>
		<name>{$SITE_NAME*}</name>
		<uri>{$BASE_URL*}</uri>
		<email>{$STAFF_ADDRESS}</email>
	</author>
	<subtitle type="html">{ABOUT}</subtitle>
	<generator uri="{$BRAND_BASE_URL*}/" version="{VERSION*}">{$BRAND_NAME*}</generator>
	<rights type="html">{COPYRIGHT}</rights>
	<logo>{LOGO_URL}</logo>
	<icon>{$IMG*,favicon}</icon>

	{CONTENT}
</feed>


