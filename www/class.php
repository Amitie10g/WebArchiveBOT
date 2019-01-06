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
  * This class is designed to provide a simplified interface to cURL which maintains cookies.
  * @author Cobi
  * @property $ch The cURL class reference
  * @property $uid The cURL UID object
  * @property $cookie_jar The cURL cookie_jar object
  * @property $postfollowredirs
  * @property $getfollowredirs
  * @property $quiet
  * @property $userAgent The default User agent
  * @property $httpHeader
  * @property $defaultHttpHeader
**/
class http {
	private $ch;
	private $uid;
	public $cookie_jar;
	public $postfollowredirs;
	public $getfollowredirs;
	public $quiet=false;
	public $userAgent = 'php wikibot classes';
	public $httpHeader = array('Expect:');
	public $defaultHttpHeader = array('Expect:');
	/**
	  * This is the Construct.
	  * @return void
	 **/
	public function __construct(){
		$this->ch = curl_init();
		$this->uid = dechex(rand(0,99999999));
		curl_setopt($this->ch,CURLOPT_COOKIEJAR,TEMP_PATH.'/cluewikibot.cookies.'.$this->uid.'.dat');
		curl_setopt($this->ch,CURLOPT_COOKIEFILE,TEMP_PATH.'/cluewikibot.cookies.'.$this->uid.'.dat');
		curl_setopt($this->ch,CURLOPT_MAXCONNECTS,100);
		$this->postfollowredirs = 0;
		$this->getfollowredirs = 1;
		$this->cookie_jar = array();
	}
	/**
	  * Get the HTTP code from cURL.
	  * @return array
	 **/
	public function http_code(){
		return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	}
	/**
	  * Encode the HTTP data.
	  * @param $data
	  * @param $keyprefix
	  * @param $keypost
	  * @return array
	 **/
	public function data_encode($data,$keyprefix = "",$keypostfix = ""){
		assert(is_array($data));
		$vars=null;
		foreach($data as $key=>$value){
			if(is_array($value)) $vars .= $this->data_encode($value, $keyprefix.$key.$keypostfix.urlencode("["), urlencode("]"));
			else $vars .= $keyprefix.$key.$keypostfix."=".urlencode($value)."&";
		}
		return $vars;
	}
	/**
	  * Send data through HTTP POST.
	  * @param $url The target URL.
	  * @param $data The POST data.
	  * @return mixed The response from Server.
	 **/
	public function post($url,$data){
		$time = microtime(1);
		curl_setopt($this->ch,CURLOPT_URL,$url);
		curl_setopt($this->ch,CURLOPT_USERAGENT,$this->userAgent);
		/* Crappy hack to add extra cookies, should be cleaned up */
		foreach ($this->cookie_jar as $name => $value){
			if (empty($cookies)) $cookies = "$name=$value";
			else $cookies .= "; $name=$value";
		}
		if ($cookies != null)
		curl_setopt($this->ch,CURLOPT_COOKIE,$cookies);
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->postfollowredirs);
		curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
		curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $this->httpHeader );
		curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
		curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($this->ch,CURLOPT_POST,1);
		curl_setopt($this->ch,CURLOPT_POSTFIELDS, $data);
		$data = curl_exec($this->ch);
		return $data;
	}
	/**
	  * Send data through HTTP GET.
	  * @param $url The target URL.
	  * @return mixed The response from Server.
	 **/
	public function get($url){
		$time = microtime(1);
		curl_setopt($this->ch,CURLOPT_URL,$url);
		curl_setopt($this->ch,CURLOPT_USERAGENT,$this->userAgent);
		/* Crappy hack to add extra cookies, should be cleaned up */
		$cookies = null;
		foreach ($this->cookie_jar as $name => $value){
			if (empty($cookies)) $cookies = "$name=$value";
			else $cookies .= "; $name=$value";
		}
		if ($cookies != null)
		curl_setopt($this->ch,CURLOPT_COOKIE,$cookies);
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->getfollowredirs);
		curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
		curl_setopt($this->ch,CURLOPT_HEADER,0);
		curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
		curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($this->ch,CURLOPT_HTTPGET,1);
		//curl_setopt($this->ch,CURLOPT_FAILONERROR,1);
		$data = curl_exec($this->ch);
		return $data;
	}
	/**
	  * Set the HTTP credentials.
	  * @param $uname
	  * @param $pwd
	  * @return void
	 **/
	public function setHTTPcreds($uname,$pwd){
		curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($this->ch, CURLOPT_USERPWD, $uname.":".$pwd);
	}
	/**
	  * This is the destruct.
	  * @return void
	 **/
	public function __destruct (){
		curl_close($this->ch);
		@unlink(TEMP_PATH.'/cluewikibot.cookies.'.$this->uid.'.dat');
	}
}
/**
 * This class interacts with the Wiki using api.php.
 * This is a modified version of the original class.
 * @author Chris G and Cobi.
 * @property string $url The Project URL (API path).
 **/
class Wiki {
	private $http;
	private $token;
	private $ecTimestamp;
	public $url;
	public $echoRet = false; // For debugging unserialize errors
	/**
	  * This is the constructor.
	  * @param $url The project and API URL
	  * @param $hu
	  * @param $hp
	  * @return void
	 **/
	public function __construct ($url='https://commons.wikimedia.org/w/api.php',$hu=null,$hp=null){
		$this->http = new http();
		$this->token = null;
		$this->url = $url;
		$this->ecTimestamp = null;
		if ($hu!==null) $this->http->setHTTPcreds($hu,$hp);
	}
	/**
	  * The set.
	  * @param $var
	  * @param $val
	  * @return void
	 **/
	public function __set($var,$val){
		switch($var){
		case 'quiet':
			$this->http->quiet=$val;
			break;
		default:
			echo "WARNING: Unknown variable ($var)!\n";
		}
	}
	/**
	 * Set/change the user agent.
	 * @param $userAgent The user agent string.
	**/
	public function setUserAgent($userAgent){
		$this->http->userAgent = $userAgent;
	}
	/**
	  * Set/change the http header.
	  * @param $httpHeader The http header.
	 **/
	public function setHttpHeader ( $httpHeader ){
		$this->http->httpHeader = $httpHeader;
	}
	/**
	  * Set/change the http headers.
	  * @param $httpHeader The http header.
	 **/
	public function useDefaultHttpHeader (){
		$this->http->httpHeader = $this->http->defaultHttpHeader;
	}
	/**
	 * Sends a query to the API.
	 * @param $query The query string.
	 * @param $post POST data if its a post request (optional).
	 * @param $repeat How many times the request will be repeated.
	 * @param $url The URL where we want to work (for external services API).
	 * @return mixed The response from server (API result).
	 **/
	public function query($query,$post=null,$repeat=null,$url=null){
		if(empty($url)) $url = $this->api_url;
		if($post==null) $ret = $this->http->get($url.$query);
		else $ret = $this->http->post($url.$query,$post);
		if($this->http->http_code() != "200"){
			if($repeat < 10) return $this->query($query,$post,++$repeat);
		else throw new Exception("HTTP Error " . $this->http->http_code() . " - $url$query"  );
		}
		if($this->echoRet){
			if( @unserialize( $ret ) === false ){
				return array( 'errors' => array("The API query result can't be unserialized. Raw text is as follows: $ret\n" ) );
			}
		}
		return unserialize( $ret );
	}
}

/**
  * This class does the data retrival and printing.
  * @property string $url The Wiki site URL.
  * @property string $sitename The Wiki site name.
  * @property string $db_server The database server address (absolute path for SQLite).
  * @property string $db_name The database name.
  * @property string $db_user The database access username.
  * @property string $db_password The database access password.
**/
class WebArchiveBOT_WWW extends Wiki{

	public  $api_url;
	private $wiki_url;
	private $sitename;
	private $db_server;
	private $db_name;
	private $db_user;
	private $db_password;
	private $tool_url;

	/**
	 * This is the constructor.
	 * @param string $url The Project URL (API path).
	 * @param string $sitename The Wiki site name.
	 * @param string $db_type The database brand used.
	 * @param string $db_server The database server address (absolute path for SQLite).
	 * @param string $db_name The database name.
	 * @param string $db_user The database access username.
	 * @param string $db_password The database access password.
	 * @return void
	**/
	public function __construct($api_url,$wiki_url,$sitename,$db_server,$db_name,$db_user,$db_password){

		$this->api_url		= $api_url;
		$this->wiki_url		= $wiki_url;
		$this->sitename		= $sitename;
		$this->db_server	= $db_server;
		$this->db_name		= $db_name;
		$this->db_user		= $db_user;
		$this->db_password	= $db_password;
		$this->tool_url		= dirname(parse_url($_SERVER['PHP_SELF'],PHP_URL_PATH));
		Wiki::__construct($api_url); // Pass main parameter to parent Class' __construct()
	}
	
	/**
	 * Retrives the pageid from the Wiki, by providing the title. View README.md for details
	 * @param string $title The Project URL (API path).
	 * @return int
	**/
	public function getPageid($title){
		
		if(empty($title)) return false;
		
		// If the input is just the page ID (numeric value), just return it
		if(is_int($title)) return $title;
		
		$title = str_replace(array('%3A','%2F','%3F','%26','%3D','%23','%20',' '),array(':','/','?','&','=','#','_','_'),utf8_encode($title));
		$query = "?action=query&format=php&titles=$title";
		$query = $this->query($query);
		$query = $query['query']['pages'];
		
		foreach($query as $key=>$value){
			$pageid = $key;
		}
		
		if(is_int($pageid)) return $pageid;
		else return false;
	}

	/**
	 * Retrive the data.
	 * @param int $limit The maximum results queried to the DB.
	 * @param string $file The filename to search.
	 * @return array
	**/
	public function getArchive($limit=50,$file){
		
		// Max limit is hardcoded to 100.000 to prevent memory exhaustion
		if(!is_int($limit) || $limit > 100000) $limit = 50;

		$dsn = "mysql:dbname=$this->db_name;host=$this->db_server";

		try{
			$db = new PDO($dsn,$this->db_user,$this->db_password);
		}catch (PDOException $e){
			die("Connection to the DB failed: " . $e->getMessage());
		}


		// Get the page ID for faster search in the DB
		if(!empty($file)){
			
			$pageid = $this->getPageid($file);
			
			$sql = "SELECT * FROM `data` WHERE `pageid` = $pageid LIMIT 1;";
		}else{
			$sql = "SELECT * FROM data ORDER BY `id` DESC LIMIT $limit";
		}

		var_dump($sql);
		
		$stmt = $db->prepare($sql);
		
		if($stmt->execute() !== false){
			$result = $stmt->fetchAll();

			foreach($result as $row){
					
				$title = $row['title'];
				$timestamp = $row['timestamp'];
				$urls = json_decode($row['urls']);
				$data[$title] = array('timestamp'=>$timestamp,'urls'=>$urls);
			}
			if(empty($data)) $data = false;
		}else $data = false;
		
		return $data;
	}

	/**
	 * Prints the main page to the browser.
	 * @param int $limit The maximum results queried to the DB.
	 * @param string $file The filename to search.
	 * @return void
	**/
	public function printMain($data){
		
		echo <<<EOC
<html>
	<head>
		<meta charset="UTF-8">
		<title>WebArchiveBOT, archived items</title>
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
			<h1><a href="$this->tool_url">WebArchiveBOT, archived items</a></h1>
			<h2>This page lists the last 50 files uploaded to $this->sitename and their links archived at Internet Archive by Wayback Machine. Just press F5 for refresh.</h2>
			<p>You can download the latest [<a href="?json_output=100">100</a>] [<a href="?json_output=1000">1.000</a>] [<a href="?json_output=10000">10.000</a>] files list in JSON format.</p>
			<p>For more information, see the <a href="$this->tool_url/doc/index.html" target="blank">Documentation</a>.
			<a href="https://github.com/Amitie10g/WebArchiveBOT" target="blank">Source code</a> is available at GitHub under the GNU Affero General Public License v3.</p>

			<div>
				Find a file (with the prefix <code>File:</code>)&nbsp;
				<form method="get" action="$this->tool_url">
					<input type="text" name="file">
					<input type="submit">
				</form>
			
			</div>
		
		</div>
		<div>

EOC;
		foreach($data as $title=>$item){

			$url = $this->wiki_url . str_replace(array('%3A','%2F','%3F','%26','%3D','%23','%20',' '),array(':','/','?','&','=','#','_','_'),rawurlencode($title));
			$date = $item['timestamp'];
			
			echo <<<EOC
			<h2><a href="$url" target="blank">$title</a></h2>
			<b>Uploaded: </b>$date (UTC)
			<ul>

EOC;
			foreach($item['urls'] as $link){
				$escaped_link = str_replace(array('%3A','%2F','%3F','%26','%3D','%23','%20',' '),array(':','/','?','&','=','#','_','_'),rawurlencode($link));
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
