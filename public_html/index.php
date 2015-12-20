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
	error_reporting(E_ALL ^ E_NOTICE);

	if(php_sapi_name() == "cli") die("\nThis script should be executed from Web.\n");

	$json_file = ''; // The gzipped JSON file path
	$json_file_cache = ''; // The cached, plain JSON file path (to improve performance, specifically in Bastion server) 
	$site_url = ''; // https://commons.wikimedia.org/wiki/
	$sitename = ''; // Wikimedia Commons

	$json_contents = @file_get_contents($json_file_cache);
	$json_contents = @json_decode($json_contents,true);

?><html>
	<head>
		<title>WebArchiveBOT</title>
		<meta charset=utf-8 />
		<meta http-equiv="refresh" content="120" />
	</head>
		<body>
		<div>
			<h1>WebArchiveBOT, archived items</h1>
			<p>This page lists the last 100 files uploaded to <?= $sitename ?> and
			their links archived at Internet Archive by Wayback Machine. You can
			download the <a href="<?= $json_file ?>">full list in JSON format</a>.</br>
			For more information, see the <a href="doc" target="blank">Documentation</a>.
			<a href="https://github.com/Amitie10g/WebArchiveBOT" target="blank">Source code</a> is available at GitHub under the GNU General Public License v3.</p>
		</div>
		<div>
<?php   if(is_file($json_file)){
                foreach($json_contents as $title=>$item){ 
?>                      <h2><a href="<?= $site_url ?><?= $title ?>" target="blank"><?= $title ?></a></h2>
                        <b>Uploaded: </b><?= strftime("%F %T",$item['timestamp']) ?>
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