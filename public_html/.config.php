<?php
//Do not edit this
if(!defined('IN_WEBARCHIVEBOT')){
  header('HTTP/1.0 403 Forbidden');
  die;
}

// Edit the following as you need
$site_url           = 'https://commons.wikimedia.org/wiki/'; // https://commons.wikimedia.org/wiki/
$sitename           = 'Wikimedia Commons'; // Wikimedia Commons
$json_file          = 'archived.json.gz'; // The gzipped JSON file path
$json_file_cache    = 'archived.json'; // The cached, plain JSON file path (to be used by the page)
$json_file_max_size = 1000; // Tme maximum size of the JSON file defined at the backend script
$redis_server       = '';
$redis_port         = '6379';
$redis_id           = @file_get_contents('.redis_id');
?>
