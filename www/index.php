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

require_once('.config.php');

class WebArchiveBOT_WWW{

	public string $site_url;
	public string $sitename;
	public string $db_path;

	public function __construct(string $site_url,string $sitename,string $db_path){

		$this->site_url = $site_url;
		$this->sitename = $sitename;
		$this->db_path = $db_path;
	}

	public function get_archive(int $limit,bool $json=false): mixed{

		if(!is_int($limit)) return false;

		if($limit === 0) $query = "SELECT * FROM `data`";
		else $query = "SELECT * FROM `data` ORDER BY `id` DESC LIMIT $limit";

		$db = new SQLite3($this->db_path);

		$result = $db->query($query);

		if($result !== false){

			$data = array();
			while($row = $result->fetchArray(SQLITE3_ASSOC)){
				$title = base64_decode($row['title']);
				$timestamp = $row['timestamp'];
				$urls = unserialize(base64_decode($row['urls']));
				$data[$title] = array('timestamp'=>$timestamp,'urls'=>$urls);
			}
		}

		$db->close();

		if($json === true) $data = json_encode($data,JSON_PRETTY_PRINT);

		return $data;
	}

	public function print_main():void {

		$db = new SQLite3($this->db_path);

		$query = "SELECT * FROM `data` ORDER BY `id` DESC LIMIT 50";

		$result = $db->query($query);

		if($result !== false){

			$data = array();
			while($row = $result->fetchArray(SQLITE3_ASSOC)){
				$title = base64_decode($row['title']);
				$timestamp = $row['timestamp'];
				$urls = unserialize(base64_decode($row['urls']));
				$data[$title] = array('timestamp'=>$timestamp,'urls'=>$urls);
			}
		}

		echo <<<EOC
<!DOCTYPE HTML>
<html lang="en">

EOC;

		echo <<<EOC
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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

EOC;

		echo <<<EOC
	<body>

EOC;

		echo <<<EOC
		<div>
			<h1>WebArchiveBOT, archived items</h1>
			<p>This page lists the last 50 files uploaded to $this->sitename and their links archived at Internet Archive by Wayback Machine.
			You can download the <a href="?json_output">whole list</a> [<a href="?json_output=100">100</a>] [<a href="?json_output=1000">1.000</a>] [<a href="?json_output=10000">10.000</a>] [<a href="/webarchivebot/archived-history.json.gz">History</a>] in JSON format.</p>
			<p>For more information, see the <a href="doc/index.html" target="blank">Documentation</a>.
			<a href="https://github.com/Amitie10g/WebArchiveBOT" target="blank">Source code</a> is available at GitHub under the GNU Affero General Public License v3.</p>
		</div>
		<div>

EOC;
		foreach($data as $title=>$item){

			$url = $this->site_url . str_replace(array('%3A','%2F','%3F','%26','%3D','%23'),array(':','/','?','&','=','#'),rawurlencode($title));
			$date = strftime("%F %T",$item['timestamp']);

			echo <<<EOC
			<h2><a href="$url" target="blank">$title</a></h2>
			<b>Uploaded: </b>$date (UTC)
			<ul>

EOC;
			foreach($item['urls'] as $link){
				$escaped_link = str_replace(array('%3A','%2F','%3F','%26','%3D','%23'),array(':','/','?','&','=','#'),rawurlencode($link));
				echo <<<EOC
				<li><a href="$escaped_link" target="blank">$link</a></li>

EOC;

			}
		echo <<<EOC
			</ul>

EOC;
		}
		echo <<<EOC
		</div>
	</body>
</html>

EOC;
	}
}

$web = new WebArchiveBOT_WWW($site_url,$sitename,$db_path);

$json_output = $_GET['json_output'] + 0;

if(isset($_GET['json_output'])){
	header('Content-Type: application/x-gzip');
	header('Content-Disposition: attachment; filename="archive.json.gz"');
	header('Content-Transfer-Encoding: binary');
	header('Accept-Ranges: bytes');

	header('Cache-Control: private');
	header('Pragma: private');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

	echo gzencode($web->get_archive($json_output,true));
}else{
	$web->print_main();
}
?>