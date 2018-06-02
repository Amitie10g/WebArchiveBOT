<?php

//This, when running under ToolForge tool account
$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/replica.my.cnf");

$site_url           = ''; // The MediaWiki URL
$sitename           = ''; // The wiki name
$db_type            = ''; // mysql, pgsql or sqlite (default)
$db_server          = ''; // The DB server. For SQLite, use the *absolute path* to the DB file
$db_name            = ''; // For SQLite, leave empty
$db_user            = ''; // This too
$db_password        = ''; // This also too
?>
