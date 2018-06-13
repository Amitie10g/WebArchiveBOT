<?php
/**
 * WebArchiveBOT: botclases.php based MediaWiki for archiving external links to Web Archive
 *
 *  (c) 2015-2018 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
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

/**
  * This class does the data retrival and printing.
  * @property string $site_url The Wiki site URL.
  * @property string $sitename The Wiki site name.
  * @property string $db_type The database brand used.
  * @property string $db_server The database server address (absolute path for SQLite).
  * @property string $db_name The database name.
  * @property string $db_user The database access username.
  * @property string $db_password The database access password.
**/
class WebArchiveBOT_WWW{

	public $site_url;
	public $sitename;
	public $db_type;
	public $db_server;
	public $db_name;
	public $db_user;
	public $db_password;

	/**
	 * This is the constructor.
	 * @param string $site_url The Wiki site URL.
	 * @param string $sitename The Wiki site name.
	 * @param string $db_type The database brand used.
	 * @param string $db_server The database server address (absolute path for SQLite).
	 * @param string $db_name The database name.
	 * @param string $db_user The database access username.
	 * @param string $db_password The database access password.
	 * @return void
	**/
	public function __construct($site_url,$sitename,$db_type,$db_server,$db_name,$db_user,$db_password){

		$this->site_url		= $site_url;
		$this->sitename		= $sitename;
		$this->db_type		= $db_type;
		$this->db_server	= $db_server;
		$this->db_name		= $db_name;
		$this->db_user		= $db_user;
		$this->db_password	= $db_password;
	}

	/**
	 * Retrive the data.
	 * @param int $limit The maximum results queried to the DB.
	 * @param string $file The filename to search.
	 * @return array
	**/
	public function getArchive($limit,$file){
		
		// Max limit is hardcoded to 100.000 to prevent memory exhaustion
		if(empty($limit) || !is_int($limit) || $limit > 100000) return false;

		if($this->db_type == "mysql"){

			$dsn = "mysql:dbname=$this->db_name;host=$this->db_server";

			try{
				$db = new PDO($dsn,$this->db_user,$this->db_password);
			}catch (PDOException $e){
				die('Connection to the DB failed.');
			}

		}elseif($this->db_type == "pgsql"){

			$dsn = "pgsql:dbname=$this->db_name;host=$this->db_server";
			
			try{
				$db = new PDO($dsn,$user,$password);
			}catch (PDOException $e){
				die('Connection to the DB failed.');
			}
		}else{

			$dsn = "sqlite:$this->db_server";
			
			try{
				$db = new PDO($dsn);
			}catch (PDOException $e){
				die('Connection to the DB failed.');
			}
			
		}

		$query = "SELECT * FROM data ORDER BY id DESC LIMIT $limit";

		if(isset($file)) $query = "SELECT * FROM data WHERE title = '". base64_encode($file) . "' LIMIT 1;";
		
		$result = $db->query($query);
		
		if($result !== false){

			$data = array();
			foreach($result as $row){
				$title = base64_decode($row['title']);
				$timestamp = $row['timestamp'];
				$urls = unserialize(base64_decode($row['urls']));
				$data[$title] = array('timestamp'=>$timestamp,'urls'=>$urls);
			}
		}
		
		return $data;
	}

	/**
	 * Prints the main page to the browser.
	 * @param int $limit The maximum results queried to the DB.
	 * @param string $file The filename to search.
	 * @return void
	**/
	public function printMain($limit,$file){

		$data = $this->getArchive($limit,$file);
		
		$tool_url = parse_url($_SERVER['PHP_SELF'],PHP_URL_PATH);
		//$tool_url = $tool_url['path'];
		
		var_dump($tool_url);
		
		echo <<<EOC
<!DOCTYPE HTML>
<html lang="en">
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
	<body>
		<div>
			<h1><a href="$tool_url">WebArchiveBOT, archived items</a></h1>
			<p>This page lists the last 50 files uploaded to $this->sitename and their links archived at Internet Archive by Wayback Machine.
			You can download the latest [<a href="?json_output=100">100</a>] [<a href="?json_output=1000">1.000</a>] [<a href="?json_output=10000">10.000</a>] files list in JSON format.</p>
			<p>For more information, see the <a href="$tool_url/doc/index.html" target="blank">Documentation</a>.
			<a href="https://github.com/Amitie10g/WebArchiveBOT" target="blank">Source code</a> is available at GitHub under the GNU Affero General Public License v3.</p>

			<div>
				Find a file (with the prefix <code>File:</code>)&nbsp;
				<form method="get" action="$tool_url">
					<input type="text" name="file">
					<input type="submit">
				</form>
			
			</div>
		
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
