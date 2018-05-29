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

	public $site_url;
	public $sitename;
	public $db_type;
	public $db_server;
	public $db_name;
	public $db_user;
	public $db_password;
	public $limit;

	public function __construct($site_url,$sitename,$db_type,$db_server,$db_name,$db_user,$db_password,$limit){

		$this->site_url = $site_url;
		$this->sitename = $sitename;
		$this->db_type  = $db_type;
		$this->db_server = $db_server;
		$this->db_name = $db_name;
		$this->db_user = $db_user;
		$this->db_password = $db_password;
		$this->limit = $limit;
	}

	public function getArchive($limit,$file){
		
		if(empty($limit)) $limit = 1000;
		if(!is_int($limit)) return false;

		if($limit === 0) $query = "SELECT * FROM data ORDER BY id DESC";
		else $query = "SELECT * FROM data ORDER BY id DESC LIMIT $this->limit";

		if(isset($file)) $query = "SELECT * FROM data WHERE title = '". base64_encode($file) . "';";

		var_dump($query);
		
		if($this->db_type == "mysql"){
			
			$dsn = "mysql:dbname=$this->db_name;host=$this->db_server";
			$db = new PDO($dsn,$user,$password);
			
		}elseif($this->db_type == "postgres"){

			$dsn = "pgsql:dbname=$this->db_name;host=$this->db_server";
			$db = new PDO($dsn,$user,$password);

		}else{

			$dsn = "sqlite:$this->db_server";
			$db = new PDO($dsn);
			
		}
		
		$result = $db->query($query);
		
		var_dump($result);

		if($result !== false){

			$data = array();
			foreach($result as $row){
				$title = base64_decode($row['title']);
				$timestamp = $row['timestamp'];
				$urls = unserialize(base64_decode($row['urls']));
				$data[$title] = array('timestamp'=>$timestamp,'urls'=>$urls);
			}
		}
		
		var_dump($data);

		return $data;
	}

	public function printMain($limit,$file){
		
		var_dump($limit);
		
		$data = $this->getArchive($limit,$file);
		
		var_dump($data);

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

$file = $_GET['file'];
$json_output = $_GET['json_output'] + 0;	   
		   
$web = new WebArchiveBOT_WWW($site_url,$sitename,$db_type,$db_server,$db_name,$db_user,$db_password,$json_output);

if(isset($_GET['json_output'])){
	header('Content-Type: application/x-gzip');
	header('Content-Disposition: attachment; filename="archive.json.gz"');
	header('Content-Transfer-Encoding: binary');
	header('Accept-Ranges: bytes');

	header('Cache-Control: private');
	header('Pragma: private');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

	echo gzencode(json_encode($web->get_archive($json_output)));
}else{
	$web->printMain(50,$file);
}
?>
