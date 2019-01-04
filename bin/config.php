<?php

// Uncomment and set only if the 'replica.my.cnf' file cannot be set automatically (absolute path!!!)
//$ts_mycnf = '';

$wiki_user = ''; 
$wiki_password = ''; 
$wiki_url = '';
$pages_per_query = 100;
$interval = 5;
$email_operator = '';
$db_server          = ''; 
$db_name            = '';
$db_user            = ''; // Set as $ts_mycnf['user'] when using the replica file
$db_password        = ''; // Set as $ts_mycnf['password'] when using the replica file

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
