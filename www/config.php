<?php

// Uncomment and set only if the 'replica.my.cnf' file cannot be set automatically (absolute path!!!)
//$ts_mycnf         = '';

$wiki_user          = ''; // The Wiki username.
$wiki_password      = ''; // The Wiki user password.
$api_url            = ''; // The MediaWiki API URL (eg. 'https://commons.wikimedia.org/w/api.php'). 
$wiki_url           = ''; // The MediaWiki Wiki URL (eg. 'https://commons.wikimedia.org/wiki/' with the final slash!). 
$sitename           = ''; // The target website name (eg. 'Wikimedia Commons').
$db_server          = ''; // The MySQL/MariaDB server address.
$db_name            = ''; // The DB name.
$db_user            = ''; // Set as $ts_mycnf['user'] when using the replica file (without the surrounding quotes!).
$db_password        = ''; // Set as $ts_mycnf['password'] when using the replica file (without the surrounding quotes!).
$db_table           = 'data'; // The table name ('data' by default)
$limit              = 100; // Maximum pages per query (100 is a sane limit)
$debug              = false;
