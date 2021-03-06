<?php
/**
 * WebArchiveBOT: botclases.php based MediaWiki tool for archiving external links to Web Archive
 *
 *  (c) 2015-2019 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
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

// Get te temp path from system default
define('TEMP_PATH',sys_get_temp_dir());

//This, when running under ToolForge tool account using the 'replica.my.cnf' file
if(is_callable('posix_getpwuid') && is_callable('posix_getuid')){
	$ts_pw = posix_getpwuid(posix_getuid());
	$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/replica.my.cnf");
}

require_once('config.php');
require_once('class.php');

if($debug === true){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);
}

$wiki = new WebArchiveBOT($api_url,$wiki_url,$sitename,$email_operator,$extlinks_bl,$pages_per_query,$db_server,$db_name,$db_user,$db_password,$db_table);

if(!empty($_GET['json_output'])){
	header('Content-Type: application/x-gzip');
	header('Content-Disposition: attachment; filename="archive.json.gz"');
	header('Content-Transfer-Encoding: binary');
	header('Accept-Ranges: bytes');

	header('Cache-Control: private');
	header('Pragma: private');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

	$limit = $_GET['json_output'] + 0;

	echo gzencode(json_encode($wiki->getArchive($limit,$_GET['file']),JSON_PRETTY_PRINT));
}else{

	$data = $wiki->getArchive($limit,$_GET['file']);

	$wiki->printMain($data);
}
