#!/usr/bin/php
<?php
error_reporting(E_ALL ^ E_NOTICE);
define('IN_WebArchiveBOT',true);

// Edit the following as you need
$wiki_user = '';
$wiki_password = '';
$wiki_url = ''; // https://commons.wikimedia.org/w/api.php
$flickr_api_key = '';
$ipernity_api_key = '';
$max_queries = 30; // This should be 30 for normal users, much more for bots

require_once('cli.php');
?>