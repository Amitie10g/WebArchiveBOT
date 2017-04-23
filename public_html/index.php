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
error_reporting(E_ALL ^ E_NOTICE);

if(php_sapi_name() == "cli") die("\nThis script should be executed from Web.\n");

$json_file = 'archived.json.gz'; // The gzipped JSON file path
$json_file_cache = 'archived.json'; // The cached, plain JSON file path (to improve performance, specifically in Bastion server)
$site_url = 'https://commons.wikimedia.org/wiki/'; // https://commons.wikimedia.org/wiki/
$sitename = 'Wikimedia Commons'; // Wikimedia Commons

$json_contents = json_decode(file_get_contents($json_file_cache),true);

?><html>
        <head>
                <title>WebArchiveBOT, archived items</title>
                <meta charset=utf-8 />
                <meta http-equiv="refresh" content="120" />
                <style>
                        body{
                                font-family:Roboto,droid-sans,Arial,sans-serif;
                        }
                        h1{
                                font-size:16pt;
                        }
                        h2{
                                font-size:14pt;
                        }
                        a {
                                overflow: hidden;
                                white-space: nowrap;
                                text-overflow: ellipsis;
                        }

                </style>
        </head>
                <body>
                <div>
                        <h1>WebArchiveBOT, archived items</h1>
                        <p>This page lists the last 50 files uploaded to <?= $sitename ?> and
                        their links archived at Internet Archive by Wayback Machine. You can
                        download the <a href="<?= $json_file ?>">latest 1000 files listed in JSON format</a>.</br>
                        For more information, see the <a href="doc" target="blank">Documentation</a>.
                        <a href="https://github.com/Amitie10g/WebArchiveBOT" target="blank">Source code</a> is available at GitHub under the GNU Affero General Public License v3.</p>
                </div>
                <div>
<?php if(is_file($json_file_cache)){

                foreach($json_contents as $title=>$item){

?>                      <h2><a href="<?= $site_url ?><?= $title ?>" target="blank"><?= $title ?></a></h2>
                        <b>Uploaded: </b><?= strftime("%F %T",$item['timestamp']) ?> (UTC)
                        <ul>
<?php                   foreach($item['urls'] as $link){
?>                              <li><a href="<?= $link ?>" target="blank"><?= $link ?></a></li>
<?php                   }
?>
                        </ul>
<?php           }
?>
<?php   }else{
?>                      <p>No links archived yet</p>
<?php   }
?>              </div>
        </body>
</html>
