<?php
/**
  * WebArchiveBOT: botclases.php based MediaWiki script for archiving external links to Internet Archive Wayback Machine.
  *
  * @copyright (c) 2015-2018  Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
  *
  * Contains parts of the Chris G's Bot classes library - https://www.mediawiki.org/wiki/Manual:Chris_G%27s_botclasses
  *
  *  (c) 2008-2012	Chris G http://en.wikipedia.org/wiki/User:Chris_G
  *  (c) 2009-2010	Fale	http://en.wikipedia.org/wiki/User:Fale
  *  (c) 2010		Kaldari http://en.wikipedia.org/wiki/User:Kaldari
  *  (c) 2011		Gutza   http://en.wikipedia.org/wiki/User:Gutza
  *  (c) 2012		Sean	http://en.wikipedia.org/wiki/User:SColombo
  *  (c) 2012		Brain   http://en.wikipedia.org/wiki/User:Brian_McNeil
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
  *  Developers (add yourself here if you worked on the code):
  *	  Cobi	- [[User:Cobi]]	 - Wrote the http class and some of the wikipedia class
  *	  Chris   - [[User:Chris_G]]	  - Wrote the most of the wikipedia class
  *	  Fale	- [[User:Fale]]	 - Polish, wrote the extended and some of the wikipedia class
  *	  Kaldari - [[User:Kaldari]]	  - Submitted a patch for the imagematches function
  *	  Gutza   - [[User:Gutza]]	- Submitted a patch for http->setHTTPcreds(), and http->quiet
  *	  Sean	- [[User:SColombo]]	 - Wrote the lyricwiki class (now moved to lyricswiki.php)
  *	  Brain   - [[User:Brian_McNeil]] - Wrote wikipedia->getfileuploader() and wikipedia->getfilelocation
  *	  Davod   - [[User:Amitie_10g]]   - See bellow:
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
	public function __construct ($url,$hu=null,$hp=null){
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

		if(empty($url)) $url = $this->url;

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

	/**
	 * Gets the content of a page. Returns false on error.
	 * Use getPageContents() as alternative to get the page in multiple formats
	 * @param $page The wikipedia page to fetch.
	 * @param $revid The revision id to fetch (optional).
	 * @param $$detectEditConflict
	 * @return string The wikitext for the given page.
	 **/
	public function getpage($page,$revid=null,$detectEditConflict=false){
		$append = '';
		if ($revid!=null)
		$append = '&rvstartid='.$revid;
		$x = $this->query('?action=query&format=php&prop=revisions&titles='.urlencode($page).'&rvlimit=1&rvprop=content|timestamp'.$append);
		foreach ($x['query']['pages'] as $ret){
			if (isset($ret['revisions'][0]['*'])){
				if ($detectEditConflict)
				$this->ecTimestamp = $ret['revisions'][0]['timestamp'];
				return $ret['revisions'][0]['*'];
			}else return false;
		}
	}

	/**
	 * This function takes a username and password and logs you into Wiki.
	 * @param $user Username to login as.
	 * @param $pass Password that belongs to the username.
	 * @return array The API result
	 **/
	public function login($user,$pass){
		$post = array('lgname' => $user, 'lgpassword' => $pass);
		$ret = $this->query('?action=login&format=php',$post);
		/* This is now required - see https://phabricator.wikimedia.org/T25076 */
		if ($ret['login']['result'] == 'NeedToken'){
			$post['lgtoken'] = $ret['login']['token'];
			$ret = $this->query( '?action=login&format=php', $post );
		}
		if ($ret['login']['result'] != 'Success'){
			echo "Login error: \n";
			print_r($ret);
			die();
		} else {
			return $ret;
		}
	}

	/**
	 * crappy hack to allow users to use cookies from old sessions.
	 * @param $data The data to be parsed.
	 * @return void
	 **/
	public function setLogin($data){
		$this->http->cookie_jar = array(
		$data['cookieprefix'].'UserName' => $data['lgusername'],
		$data['cookieprefix'].'UserID' => $data['lguserid'],
		$data['cookieprefix'].'Token' => $data['lgtoken'],
		$data['cookieprefix'].'_session' => $data['sessionid'],
		);
	}
}

/**
 * This class is intended to do the archiving.
 * @author Davod.
 * @property string $api_url MediaWiki API URL
 * @property string $wiki_url MediaWiki Wiki URL
 * @property string $sitename MediaWiki site name
 * @property string $email_operator The emailaddress of the operator,to be used to send mails to him/her in case of error.
 * @property array $extlinks_bl The blacklisted URLs to exclude for archiving.
 * @property int $pages_per_query The maximum pages retrived per query (iteration) (250 by default).
 * @property string $db_server The database server address (absolute path for SQLite).
 * @property string $db_name The database name.
 * @property string $db_user The database access username.
 * @property string $db_password The database access password.
 * @property string $db_table The database access password.
 * @property string $tool_url The tool URL.
**/
class WebArchiveBOT extends Wiki {
	public  $api_url;
	private $wiki_url;
	private $sitename;
	private $email_operator;
	private $extlinks_bl;
	private $pages_per_query;
	private $db_server;
	private $db_name;
	private $db_user;
	private $db_password;
	private $db_table;
	private $tool_url;

	/**
	 * This is the constructor.
	 * @param string $api_url MediaWiki API URL
	 * @param string $wiki_url MediaWiki Wiki URL
	 * @param string $sitename MediaWiki site name
	 * @param string $email_operator The emailaddress of the operator,to be used to send mails to him/her in case of error.
	 * @param array $extlinks_bl The blacklisted URLs to exclude for archiving.
	 * @param int $pages_per_query The maximum pages retrived per query (iteration) (250 by default).
	 * @param string $db_server The database server address (absolute path for SQLite).
	 * @param string $db_name The database name.
	 * @param string $db_user The database access username.
	 * @param string $db_password The database access password.
	 * @param string $db_table The database access password.
	 * @param string $tool_url The tool URL.
	**/
	public function __construct($api_url,$wiki_url,$sitename,$email_operator,$extlinks_bl,$pages_per_query,$db_server,$db_name,$db_user,$db_password,$db_table){

		if(!is_array($extlinks_bl)) $extlinks_bl = null;
		
		Wiki::__construct($api_url); // Pass main parameter to parent Class' __construct()
		$this->api_url		= $api_url;
		$this->wiki_url		= $wiki_url;
		$this->tool_url		= dirname($_SERVER['SCRIPT_NAME']);
		$this->sitename		= $sitename;
		$this->email_operator	= $email_operator;
		$this->extlinks_bl	= '/('.implode('|',$extlinks_bl).')/';
		$this->pages_per_query	= $pages_per_query;
		$this->db_server	= $db_server;
		$this->db_name		= $db_name;
		$this->db_user		= $db_user;
		$this->db_password	= $db_password;
		$this->db_table		= $db_table;
	}

	/**
	 * Get the contents from the Wiki page. This is an alternative for getpage() with more features.
	 * @param $page The page that we're working.
	 * @param $props The properties that we want to obtain from the query (string or array).
	 * @return array The API result (page contents and metadata in the desired format).
	**/
	public function getPageContents($page,$props=null){

		if(is_array($props)) $props = implode('|',$props);

		if(!empty($_SESSION['wiki_page_contents'][$page][$props])) $contents = $_SESSION['wiki_page_contents'][$page][$props];
		else{
			$contents = $this->query("?action=parse&format=php&prop=$props&disabletoc=&mobileformat=&noimages=&page=".urlencode($page));
			$contents['parse']['text']['*'] = str_replace('<a','<a target="'.urlencode($page).'"',$contents['parse']['text']['*']);
			$contents['parse']['text']['*'] = str_replace('href="/wiki/','href="'.$this->site_url,$contents['parse']['text']['*']);
			$_SESSION['wiki_page_contents'][$page][$props] = $contents;
		}
		return $contents;
	}

	/**
	 * Get a list of the latest files uploaded to Commons.
	 * @param void
	 * @return array The API result (the list of the latest files uploaded).
	**/
	public function getLatestFiles(){
		$query = "?action=query&list=allimages&format=php&aisort=timestamp&aidir=older&aiprop=timestamp%7Ccanonicaltitle&ailimit=$this->pages_per_query";
		$query = $this->query($query);
		return $query['query']['allimages'];
	}

	/**
	 * Wraper for in_array() that also parse regex.
	 * @param mixed $needle The value to find.
	 * @param mixed $haystack The string/array where find in.
	 * @param bool $regex To allow or not regex (contents in $haystack should be string and valid regex).
	 * If false, then, in_array() will be used. Used only for regex, does not matter for non-regex search.
	 * @param bool $inverse To match or not the regex (no match is done with '?!').
	 * @return bool true if value were found in the array, false if not.
	**/
	public function inArray($needle,$haystack,$regex=false,$inverse=false){
		if(empty($needle)) return false;
		if($regex === true){
			$regex = implode('|',$haystack);
			if($inverse === true) $regex = "/^(?!$regex)/";
			else $regex = "/^($regex)/";

			if(preg_match($regex,$needle) >= 1) $found = true;
			else $found = false;
		}else $found = in_array($needle,$haystack);
		return $found;
	}

	 /**
	 * Query the Internet Archive API to get the latest archived version (or archive if not available yet).
	 * @param array $urls the URLs to be parsed.
	 * @return array the Wayback Machine URLs retrived.
	**/
	public function urls2archive_urls($urls,$json=false){
		foreach($urls as $url){

			if(preg_match($this->extlinks_bl,$url)) continue;

			// Get the latest archive, if available
			$archive_g = file_get_contents('http://archive.org/wayback/available?url='.urlencode($url));
			if($archive_g != '{"archived_snapshots":{}}'){
				$latest_archive = json_decode($archive_g,true);
				$archive_timestamp = strtotime($latest_archive['archived_snapshots']['closest']['timestamp']);
			}

			$timestamp = time();
			if(!is_int($archive_timestamp)) $archive_timestamp = 0;
			$window_time = $timestamp-$archive_timestamp;

			// Do the archive, if last archive has been created >2 days
			if($window_time >= 172800){
				$headers = @get_headers("https://web.archive.org/save/$url",1);

				if($headers[0] == "HTTP/1.1 403 FORBIDDEN") continue;

				$location = $headers['Content-Location'];

				if(!empty($location)){
					if(is_array($location)) $location = end($location);
					if(preg_match("/^\/web\/[0-9]{14}\/[\p{L}\p{N}\p{S}\p{P}\p{M}\p{Zs}]+$/",$location) === 1) $archive_urls[] = "https://web.archive.org$location";
					else echo "Wrong location: $location\n";
				}
			}else{
				$archive_urls[] = $latest_archive['archived_snapshots']['closest']['url'];
			}
		}
		
		if(!empty(array_filter($archive_urls))){
			if($json === true) $archive_urls = json_encode(array_unique(array_filter($archive_urls)));
			else $archive_urls = array_unique(array_filter($archive_urls));
			
			return $archive_urls;
		}else{
			return false;
		}
	}

	 /**
	 * Do the archive process: Query to Internet Archive and store the results in a local DB
	 * @param array $pages The pages retrived by getLatestFiles()
	 * @return bool The final results.
	**/
	public function archive($pages){

		if(!is_array($pages) || empty($pages)) return false;

		try{
			$dsn = "mysql:dbname=$this->db_name;host=$this->db_server";
			$db = new PDO($dsn,$this->db_user,$this->db_password);

		}catch (PDOException $e){
   			$message = 'Connection to the DB failed';
			echo "$message: " . $e->getMessage();
			$this->sendMail("$message: " . $e->getMessage());
			echo "\n";
			die;
		}

		$db->exec("CREATE TABLE IF NOT EXISTS `data`( `id` INT NOT NULL AUTO_INCREMENT, `pageid` INT NOT NULL, `title` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `timestamp` TIMESTAMP NOT NULL, `urls` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, UNIQUE KEY `id` (`id`) USING BTREE, UNIQUE KEY `pageid` (`pageid`) USING BTREE, PRIMARY KEY (`id`)) ENGINE = InnoDB;");

		foreach($pages as $page){
			$title = $page['canonicaltitle'];
			$timestamp = $page['timestamp'];
			
			$metadata = $this->GetPageContents($title,'externallinks');
			$pageid = $metadata['parse']['pageid'];
			$urls = $this->urls2archive_urls($metadata['parse']['externallinks'],true);
			
			if(empty($urls)) continue;

			$sql = "INSERT INTO data(`pageid`,`title`,`timestamp`,`urls`) VALUES ('$pageid','$title','$timestamp','$urls');";
			
			$stmt = $db->prepare($sql);
			$stmt->execute();
		}
		
		unset($db);
		return true;
	}

	/**
	 * Send email (mostly for errors).
	 * @param string $message the message.
	 * @param string $subject the subject ("Errors with WebArchiveBOT" by default).
	 * @return void
	**/
	public function sendMail($message,$subject=null){
		if($subject == null) $subject = "Errors with WebArchiveBOT";
		$from = "webarchivebot-noreply@wmflabs.org";
		$to = $this->email_operator;
		$headers = "From: WebArchiveBOT <$from>\r\n";
		mail($to,$subject,$message,$headers);
	}

	/**
	 * Get the page ID from MediaWiki, by the title
	 * @param string $title the title where the ID is needed.
	 * @return int
	**/	
	public function getPageid($title){
		
		if(empty($title)) return false;
		
		$query = '?action=query&format=php&titles='.urlencode($title);
		
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
	 * @param string $title The filename to search.
	 * @return array
	**/
	public function getArchive($limit=100,$title){
		
		// Max limit is hardcoded to 100.000 to prevent memory exhaustion
		if(!is_int($limit) || $limit > 10000) $limit = 100;

		$dsn = "mysql:dbname=$this->db_name;host=$this->db_server";

		try{
			$db = new PDO($dsn,$this->db_user,$this->db_password);
		}catch (PDOException $e){
			die("Connection to the DB failed: " . $e->getMessage());
		}

		// Get the page ID for faster search in the DB
		if($pageid = $this->getPageid($title)) $sql = "SELECT * FROM `$this->db_table` WHERE `pageid` = $pageid LIMIT 1;";
		else $sql = "SELECT * FROM `$this->db_table` ORDER BY `id` DESC LIMIT $limit";
		
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
	 * @param string $data The data to be parsed
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
				font-size:1vw;
			}
			h1{
				font-size:2vw;
			}
			h2{
				font-size:1.5vw;
			}
			a {
				overflow: hidden;
				white-space: nowrap;
				text-overflow: ellipsis;
			}

			input {
				font-size: 1.5vw;
			}
			
			@media screen and (max-width: 1024px) {
				font-size: 6vw;
			}

		</style>
	</head>
	<body>
		<div>
			<h1><a href="$this->tool_url">WebArchiveBOT, archived items</a></h1>
			<h2>This page lists the last 100 files uploaded to $this->sitename and their links archived at Internet Archive by Wayback Machine. Just press F5 for refresh.</h2>
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
		if(!empty($data)){
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
		}else{
			echo "<i>No data available</i>";
		}
		echo <<<EOC
		</div>
	</body>
</html>

EOC;
	}
}
