<?php

// Uncomment and set only if the 'replica.my.cnf' file cannot be set automatically (absolute path!!!)
//$ts_mycnf = '';

$wiki_user = ''; // Set as $ts_mycnf['user'] when using the replica file
$wiki_password = ''; // Set as $ts_mycnf['password'] when using the replica file
$wiki_url = '';
$pages_per_query = 100;
$interval = 5;
$email_operator = '';
$db_type            = ''; // mysql, postgres or sqlite (default)
$db_server          = ''; // The DB server. For SQLite use the absolute path to the DB file
$db_name            = ''; // For SQLite, leave empty
$db_user            = ''; // This too
$db_password        = ''; // This also too

$extlinks_bl[] = "^(\$schemes:)[\p{L}\p{N}]+";
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
