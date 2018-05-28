<?php

//This, when running under ToolForge tool account
$ts_pw = posix_getpwuid(posix_getuid());
$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/replica.my.cnf");

$site_url           = '';
$sitename           = '';
$db_type            = ''; // mysql, postgres or sqlite (default)
$db_path            = 'webarchivebot.sqlite3'; // Absolute path!!!
$sql_user           = '';
$sql_password       = '';
$sql_type           = '';
$sql_server         = '';
?>
