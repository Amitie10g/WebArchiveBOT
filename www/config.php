<?php

// Uncomment and set only if the 'replica.my.cnf' file cannot be set automatically (absolute path!!!)
//$ts_mycnf         = '';

$wiki_user          = ''; // Set as $ts_mycnf['user'] when using the replica file
$wiki_password      = ''; // Set as $ts_mycnf['password'] when using the replica file
$site_url           = ''; // The MediaWiki URL
$sitename           = ''; // The wiki name
$db_type            = ''; // mysql, pgsql or sqlite (default)
$db_server          = ''; // The DB server. For SQLite, use the *absolute path* to the DB file
$db_name            = ''; // For SQLite, leave empty
$db_user            = ''; // This too
$db_password        = ''; // This also too
