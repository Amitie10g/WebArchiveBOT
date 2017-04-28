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
 
 // :: This file is intended to be symlinked

if(!defined('IN_WEBARCHIVEBOT')){
    header('HTTP/1.0 403 Forbidden');
    die;
}

if(class_exists('Redis') && is_file('.redis_id')){
	
	$redis = new Redis();
	$redis->pconnect($redis_server,$redis_port,0,$redis_id);

	$list = /*unserialize(*/$redis->get('list')/*)*/;
	
}else{
	$list = json_decode(file_get_contents($json_file_cache),true);
}

var_dump($list);

?><?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>WebArchiveBOT, archived items</title>
		<meta http-equiv="refresh" content="120" />
		<style type="text/css">
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
		<p>This page lists the last 50 files uploaded to <?= $sitename ?> and their links archived at Internet Archive by Wayback Machine. You can download the <a href="<?= $json_file ?>">latest  <?= number_format($json_file_max_size,0,'','.') ?> files listed in JSON format</a>.<br />
		For more information, see the <a href="doc" target="blank">Documentation</a>. <a href="https://github.com/Amitie10g/WebArchiveBOT" target="blank">Source code</a> is available at GitHub under the GNU Affero General Public License v3.</p>
	</div>
	<div>
<?php if(!empty($list)){

	foreach($list as $title=>$item){
?>
		<h2><a href="<?= $site_url ?><?= str_replace(array('%3A','%2F','%3F','%26','%3D','%23'),array(':','/','?','&','=','#'),rawurlencode($title)) ?>" target="blank"><?= $title ?></a></h2>
		<b>Uploaded: </b><?= strftime("%F %T",$item['timestamp']) ?> (UTC)
		<ul>
<?php foreach($item['urls'] as $link){ ?>
			<li><a href="<?= str_replace(array('%3A','%2F','%3F','%26','%3D','%23'),array(':','/','?','&','=','#'),rawurlencode($link)) ?>" target="blank"><?= $link ?></a></li>
<?php } ?>
		</ul>
<?php } ?>
<?php }else{ ?>
		<p>No links archived yet</p>
<?php   } ?>
		</div>
	</body>
</html>
