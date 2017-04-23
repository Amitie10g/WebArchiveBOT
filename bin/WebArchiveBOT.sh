
#!/usr/bin/php
<?php

/**
 * WebArchiveBOT: botclases.php based MediaWiki for archiving external links to Web Archive
 *
 *  (c) 2015 - 2017 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 *  This program is free software, and you are welcome to redistribute it under
 *  certain conditions. This program comes with ABSOLUTELY NO WARRANTY.
 *  see README.md and LICENSE for more information.
 *
 **/
 
error_reporting(E_ALL ^ E_NOTICE);

// Edit the following as you need
$wiki_user = '';
$wiki_password = '';
$wiki_url = 'https://commons.wikimedia.org/w/api.php'; // https://commons.wikimedia.org/w/api.php
$pages_per_query = 100; // This should be 100 for normal users, much more for bots
$interval = 10; // Interval between every execution in minutes
$json_file = 'archived.json.gz'; // Compressed JSON (Absolute path!)
$json_file_cache = 'archived.json'; // Uncompressed JSON for caching (Absolute path!)
$email_operator = "";

ini_set("memory_limit",'1024M');

ini_set('xdebug.var_display_max_depth',-1);
ini_set('xdebug.var_display_max_children',-1);
ini_set('xdebug.var_display_max_data',-1);

require_once('cli.php');

?>
