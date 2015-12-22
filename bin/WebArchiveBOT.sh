#!/usr/bin/php
<?php
error_reporting(E_ALL ^ E_NOTICE);

// Edit the following as you need
$wiki_user = '';
$wiki_password = '';
$wiki_url = ''; // https://commons.wikimedia.org/w/api.php
$pages_per_query = 100; // This should be 100 for normal users, much more for bots
$json_file = ''; // The gzipped JSON file path
$json_file_cache = ''; // The cached, plain JSON file path (to improve performance, specifically in Bastion server) 

require_once('cli.php');
?>