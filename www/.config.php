<?php

//This, when running under ToolForge tool account
$ts_pw = posix_getpwuid(posix_getuid());
$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/replica.my.cnf");

$site_url           = '';
$sitename           = '';
$db_type            = ''; // mysql, postgres or sqlite (default)
$sql_user           = '';
$sql_password       = '';
$sql_server         = '';
$db                 = ''; // The DB name. For SQLite, the absolute path
?>
