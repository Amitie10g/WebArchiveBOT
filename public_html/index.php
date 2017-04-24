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

if(php_sapi_name() == "cli") die("\nThis script should be executed from Web.\n");

$json_file = 'archived.json.gz'; // The gzipped JSON file path
$json_file_cache = 'archived.json'; // The cached, plain JSON file path (to improve performance, specifically in Bastion server)
$site_url = 'https://commons.wikimedia.org/wiki/'; // https://commons.wikimedia.org/wiki/
$sitename = 'Wikimedia Commons'; // Wikimedia Commons

define('IN_WEBARCHIVEBOT',true);

require_once('template.php');
?>
