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
$email_operator		= '';
$limit              = 100; // Maximum pages per query (100 is a sane limit)
$debug              = false; // Set true to increase the verbosity
$pages_per_query	= 100;
$interval			= 5;

// Set blacklisted URLs (regex, one URL per line)
$extlinks_bl[] = '^(?!(http(s){0,1}:\/\/))[\p{L}\p{N}]+';
$extlinks_bl[] = '(([\p{P}\p{N}]+\.)*google\.[\p{L}\p{N}]+)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*openstreetmap\.[\p{L}\p{N}]+)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*creativecommons\.[\p{L}\p{N}]+)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*gnu\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*artlibre\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*flickr\.com)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*archive\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*facebook\.com)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wipo\.int)';
$extlinks_bl[] = '(validator\.w3\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wikipedia\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wiktionary\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wikiquote\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wikibooks\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wikisource\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wikinews\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wikiversity\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wikimedia\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wikidata\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*wmflabs\.org)';
$extlinks_bl[] = '(([\p{L}\p{N}]+\.)*mediawiki\.org)';