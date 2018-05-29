<?php
/**
 * WebArchiveBOT: botclases.php based MediaWiki script for archiving external links to Internet Archive Wayback Machine.
 *
 *  @copyright (c) 2015-2018 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
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
 **/

$ts_pw = posix_getpwuid(posix_getuid());

require_once('config.php');
require_once('class.php');

// Check if SAPI is CLI
if(php_sapi_name() != "cli") die("\nThis script should be executed from CLI.\n");

define('TEMP_PATH',realpath(sys_get_temp_dir()));

// Declare the arguments to be taken from command line. User and Password may be received
// from the arguments --user and --password for convenience, but them can also
// be hardcoded (see bellow)
$shortopts	= "";
$longopts	= array("help","license","debug");
$options	= getopt($shortopts,$longopts);

if(isset($options['help'])) $help = true;
if(isset($options['license'])) $license = true;
if(isset($options['debug'])) $debug = true;

// Declare the Help and License text
$help_text = <<<EOH

$bs::: WebArchiveBOT  Copyright (C) 2015-2017  Davod (Amitie 10g) :::$be

This script is intended to check for new files uploaded to Wiki,
extract external links and save them at Web Archive by Wayback Machine.
$bs
Parameters:

   $bs--debug$be   If you want to output debug information

   $bs--help$be	Show this help

   $bs--license$be Show the license of this program

See README.md for detailed information about its usage.

EOH;

$license_text = <<<EOL
$bs
WebArchiveBOT  Copyright (C) 2015-2017  Davod (Amitie 10g)$be

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
if($help === true) die($help_text);
if($license === true) die($license_text);

$wiki = new WebArchiveBOT($wiki_url,$email_operator,$extlinks_bl,$pages_per_query,$db_type,$db_server,$db_name,$db_user,$db_password);

$login = $wiki->login($wiki_user,$wiki_password);

try{
	if($login['login']['result'] != 'Success') throw new Exception("unable to login. Check your credentials.\n");
}catch (Exception $e){
	$message = $e->getMessage();
	$memory_peak = memory_get_peak_usage (true);

	echo "$message\nMemory peak: $memory_peak\n";

	$date = date("Y-m-d H:i:s");
	$message .= "\n\nMemory peak: $memory_peak\n\nGenerated: $date";

	$wiki->sendMail($message);

	die;
}

$wiki->setUserAgent('WebArchiveBOT/1.0 (https://github.com/Amitie10g/WebArchiveBOT; davidkingnt@gmail.com) Botclasses.php/1.1');

if(!is_int($interval)) $interval = 10;
$interval = $interval*60;
$result = true;
$iteration = 0;
while(true){
	$time = strftime('%F %T');
	echo "\n$time\nArchiving... ";

	try{
		if($iteration%1000 == 0 && $iteration != 0) $rotate = true;
		else $rotate = false;
		$files  = $wiki->getLatestFiles();
		$result = $wiki->archive($files);

		if($result !== true) throw new Exception("errors ocurred when trying to archive. See the log for details.\n");
		echo "everything OK.\n";
		if($debug === true){
			$memory_peak = memory_get_peak_usage (true);
			echo "Memory peak: $memory_peak\n";
		}

	}catch (Exception $e){
		$message = $e->getMessage();
		$memory_peak = memory_get_peak_usage (true);

		echo "$message\nMemory peak: $memory_peak\n";

		$date = date("Y-m-d H:i:s");
		$message .= "\n\nMemory peak: $memory_peak\n\nGenerated: $date";

		$wiki->sendMail($message);
	}
	$iteration++;
	sleep($interval);
}
?>
