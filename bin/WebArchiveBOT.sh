#!/usr/bin/php
<?php
error_reporting(E_ALL ^ E_NOTICE);

// Edit the following as you need
$wiki_user = '';
$wiki_password = '';
$wiki_url = ''; // https://commons.wikimedia.org/w/api.php
$pages_per_query = 300; // This should be 300 for normal users, much more for bots
$interval = 10; // Interval of every execution in minutes
$json_file = ''; // Compressed JSON (Absolute path!)
$json_file_cache = ''; // Uncompressed JSON for caching
$email_operator = ''; // Your email

require_once('cli.php');
?>s



