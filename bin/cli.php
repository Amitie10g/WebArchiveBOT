<?php
/**
 * WebArchiveBOT: botclases.php based MediaWiki script for archiving external links to Internet Archive Wayback Machine.
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
 **/

// :: This file is intended to be symlinked

if(!defined('IN_WEBARCHIVEBOT')) die;

ini_set('xdebug.var_display_max_depth',-1);
ini_set('xdebug.var_display_max_children',-1);
ini_set('xdebug.var_display_max_data',-1);

// Check if SAPI is CLI
if(php_sapi_name() != "cli") die("\nThis script should be executed from CLI.\n");

define('TEMP_PATH',realpath(sys_get_temp_dir()));

// Set the timezone to UTC
date_default_timezone_set('UTC');

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

$bs::: WebArchiveBOT  Copyright (C) 2015-2017  Davod (Amitie 10g) :::$be

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
if(isset($help)) die($help_text);
if(isset($license)) die($license_text);

require_once('class.php');

$wiki = new WebArchiveBOT($wiki_url,$email_operator,$extlinks_bl,$pages_per_query,$public_html_path,$json_file,$json_file_cache,$json_file_max_size,$redis_server,$redis_port);

$login = $wiki->login($wiki_user,$wiki_password);
if($login['login']['result'] != 'Success') die('Not logged in!');

$wiki->setUserAgent('WebArchiveBOT/1.0 (https://github.com/Amitie10g/WebArchiveBOT; davidkingnt@gmail.com) Botclasses.php/1.0');

if(!is_int($interval)) $interval = 10;
$interval = $interval*60;
$result = true;
while(true){
        $time = strftime('%F %T');
        echo "\n$time\nArchiving... ";

        try{
                $files  = $wiki->getLatestFiles();
                $links  = $wiki->getPagesExternalLinks($files);
                $result = $wiki->archive($links);

                if($result !== true) throw new Exception("errors ocurred when trying to archive. See the log for details.\n");
                echo "everything OK.\n";
                $memory_peak = memory_get_peak_usage (true);
                echo "Memory peak: $memory_peak\n";

        }catch (Exception $e){
                $message = $e->getMessage();
                $memory_peak = memory_get_peak_usage (true);

                echo "$message\nMemory peak: $memory_peak\n";

                $date = date("Y-m-d H:i:s");
                $message .= "\n\nMemory peak: $memory_peak\n\nGenerated: $date";

                $wiki->sendMail($message);
        }
        sleep($interval);
}
?>
