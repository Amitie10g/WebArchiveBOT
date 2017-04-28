#!/usr/bin/php
<?php

/**
 * WebArchiveBOT: botclases.php based MediaWiki for archiving external links to Web Archive
 *
 *  @copyright (c) 2015-2017 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
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

// Edit this only if you know what you doing
error_reporting(E_ALL ^ E_NOTICE);
ini_set("memory_limit",'1024M');

// Edit the following as you need
$wiki_user = '';
$wiki_password = '';
$wiki_url = 'https://commons.wikimedia.org/w/api.php'; // https://commons.wikimedia.org/w/api.php
$pages_per_query = 100; // This should be 100 for normal users, much more for bots
$interval = 10; // Interval between every execution in minutes
$public_html_path = ''; // Path to public_html directory, where the JSON fires will be stored (absolute path!)
$json_file = 'archived.json.gz'; // Compressed JSON filename
$json_file_cache = 'archived.json'; // Uncompressed JSON filename, for caching
$json_file_max_size = 1000; //Maximum ammount of files stored in the JSON
$email_operator = '';

// Excluded external links
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

// Wikimedia links
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

// Do not edit from here
define('IN_WEBARCHIVEBOT',true);
require_once('cli.php');
?>
