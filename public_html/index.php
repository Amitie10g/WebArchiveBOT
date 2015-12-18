<?php

/**
 * WebArchiveBOT: botclases.php based MediaWiki for archiving external links to Web Archive
 *
 *  (c) 2015 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 *  This program is free software, and you are welcome to redistribute it under
 *  certain conditions. This program comes with ABSOLUTELY NO WARRANTY.
 *  see README.md and COPYING for more information
 *
 **/
error_reporting(E_ALL ^ E_NOTICE);

if(php_sapi_name() == "cli") die("\nThis script should be executed from Web.\n");

$json_file = 'archived.json'; // Absolute path!
$site_url = 'https://commons.wikimedia.org/wiki/';
$sitename = "Wikimedia Commons";

$limit = abs((int)$_GET['limit']);
if($limit == 0) $limit = 100;

$contents = @file_get_contents($json_file);
$json_contents = @json_decode($contents,true);
rsort($json_contents);
$json_contents = @array_slice($json_contents,0,$limit);

?><html>
	<head>
		<title>WebArchiveBOT, archived items</title>
	</head>
		<body>
		<div>
			<h1>WebArchiveBOT, archived items</h1>
			<p>This page lists the last 100 files uploaded to <?= $sitename ?> and
			their links archived at Internet Archive by Wayback Machine. You can
			download the <a href="<?= $json_file ?>" target="_blank">full list in JSON format</a>.</br>
			For more information, see the <a href="doc" target="blank">Documentation</a>.
			<a href="https://github.com/Amitie10g/WebArchiveBOT" target="blank">Source code</a> is available at GitHub under the GNU General Public License v3.</p>
		</div>
		<div>
<?php if(is_file($json_file)){
	foreach($json_contents as $items){
		foreach($items as $key=>$item){
?>			<h2><a href="<?= $site_url ?><?= $key ?>"><?= $key ?></h2>
			<ul>
<?php			foreach($item as $link){
?>				<li><a href="<?= $link ?>"><?= $link ?></a></li>
<?php			}
?>			</ul>
<?php		}
	}
	}else{
?>			<p>No links archived yet</p>
<?php } ?>		</div>
	</body>
</html>
