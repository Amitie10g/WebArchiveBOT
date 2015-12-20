<?php

/**
 * WebArchiveBOT: botclases.php based MediaWiki for archiving external links to Web Archive
 *
 *  (c) 2015 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 *  This program is free software, and you are welcome to redistribute it under
 *  certain conditions. This program comes with ABSOLUTELY NO WARRANTY.
 *  see README.md and LICENSE for more information
 *
 **/

// Check if SAPI is CLI
if(php_sapi_name() != "cli") die("\nThis script should be executed from CLI.\n");

define('TEMP_PATH',realpath(sys_get_temp_dir()));

// Declare the arguments to be taken from command line. User and Password may be received
// from the arguments --user and --password for convenience, but them can also
// be hardcoded (see bellow)
$shortopts  = "";
$longopts   = array("user::","password::","project::","help::","license::","quiet::");
$options    = getopt($shortopts, $longopts);

if(empty($user))     $user     = $options['user'];
if(empty($password)) $password = $options['password'];
if(empty($project))  $project  = $options['project'];
$help                          = $options['help'];
$license                       = $options['license'];

// Declare the Help and License text
$help_text = <<<EOH

$bs::: WebArchiveBOT  Copyright (C) 2015  Davod (Amitie 10g) :::$be

This script is intended to check for new files uploaded to Wiki,
extract external links and save them at Web Archive by Wayback Machine.
$bs
Parameters:

   --user$be     Your Wiki username (hardcoded by default)

   $bs--password$be Your Wiki password (hardcoded by default)

   $bs--project$be  Your Wiki projet where you  will upload your file(s),  with the
	      "http(s)://"  prefix.  This parameter is optional;  the default
	      value is "https://commons.wikimedia.org"

   $bs--help$be     Show this help

   $bs--license$be  Show the license of this program

See README.md for detailed information about its usage.


EOH;

$license_text = <<<EOL
$bs
WebArchiveBOT  Copyright (C) 2015  Davod (Amitie 10g)$be

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


EOL;

// Output the Help and License as requested with --help and --license
if(isset($help)) die($help_text);
if(isset($license)) die($license_text);

require_once('class.php');

$wiki = new WebArchiveBOT($wiki_url,$quiet);

$login = $wiki->login($wiki_user,$wiki_password);
if($login['login']['result'] != 'Success') die('Not logged in');
			  
$wiki->setUserAgent('WebArchiveBOT/0,1 (https://github.com/Amitie10g/WebArchiveBOT; davidkingnt@gmail.com) Botclasses.php/1.0');

$files = $wiki->getLatestFiles($pages_per_query);

// External links blacklist that will never be requested to archive.
// Use valid regular expressions in each array value
$extlinks_bl = array('(([\w]+\.)*google\.[\w]+)',
		     '(([\w]+\.)*openstreetmap\.[\w]+)',
		     '(([\w]+\.)*creativecommons\.[\w]+)',
		     '(([\w]+\.)*wikipedia\.org)',
		     '(([\w]+\.)*wikimedia\.org)',
     		     '(([\w]+\.)*wmflabs\.org)',
     		     '(([\w]+\.)*gnu\.org\/copyleft)',
		     'validator\.w3\.org');
		     
		     

foreach($files['query']['allimages'] as $page){

	$canonicaltitle = $page['canonicaltitle'];
	$timestamp = strtotime($page['timestamp']);
	$links_g = $wiki->GetPageContents($canonicaltitle,'externallinks');
	$links_g = $wiki->clearLinks($links_g['parse']['externallinks'],$extlinks_bl,false);
	
	if(!empty($links_g)){
		$links_g = array_filter($links_g);
		$links[$canonicaltitle] = array('timestamp'=>$timestamp,'urls'=>$links_g);
	}
}

$result = $wiki->archive($links,$json_file,$json_file_cache);

if($result === true) echo "everything OK.\n";
else echo "errors ocurred.\n"
?>