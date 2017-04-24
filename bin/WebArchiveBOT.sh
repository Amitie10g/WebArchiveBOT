#!/usr/bin/php
<?php

/**
 * WebArchiveBOT: botclases.php based MediaWiki for archiving external links to Web Archive
 *
 *  (c) 2015-2017 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 **/

// :: This file is intended to be copied and edited
 
error_reporting(E_ALL ^ E_NOTICE);

// Edit the following as you need
$wiki_user = '';
$wiki_password = '';
$wiki_url = 'https://commons.wikimedia.org/w/api.php'; // https://commons.wikimedia.org/w/api.php
$pages_per_query = 100; // This should be 100 for normal users, much more for bots
$interval = 10; // Interval between every execution in minutes
$json_file = 'archived.json.gz'; // Compressed JSON (Absolute path!)
$json_file_cache = 'archived.json'; // Uncompressed JSON for caching (Absolute path!)
$email_operator = '';

ini_set("memory_limit",'1024M');

ini_set('xdebug.var_display_max_depth',-1);
ini_set('xdebug.var_display_max_children',-1);
ini_set('xdebug.var_display_max_data',-1);

define('IN_WEBARCHIVEBOT',true);

require_once('cli.php');

?>
