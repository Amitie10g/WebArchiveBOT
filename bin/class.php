<?php
/**
 * WebArchiveBOT: botclases.php based MediaWiki for archiving external links to Web Archive
 *
 * @copyright (c) 2015	Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 * Contains parts of the Chris G's Bot classes library
 *
 *  (c) 2008-2012	Chris G	http://en.wikipedia.org/wiki/User:Chris_G
 *  (c) 2009-2010	Fale	http://en.wikipedia.org/wiki/User:Fale
 *  (c) 2010		Kaldari	http://en.wikipedia.org/wiki/User:Kaldari
 *  (c) 2011		Gutza	http://en.wikipedia.org/wiki/User:Gutza
 *  (c) 2012		Sean	http://en.wikipedia.org/wiki/User:SColombo
 *  (c) 2012		Brain	http://en.wikipedia.org/wiki/User:Brian_McNeil
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 *  Developers (add yourself here if you worked on the code):
 *      Cobi    - [[User:Cobi]]         - Wrote the http class and some of the wikipedia class
 *      Chris   - [[User:Chris_G]]      - Wrote the most of the wikipedia class
 *      Fale    - [[User:Fale]]         - Polish, wrote the extended and some of the wikipedia class
 *      Kaldari - [[User:Kaldari]]      - Submitted a patch for the imagematches function
 *      Gutza   - [[User:Gutza]]        - Submitted a patch for http->setHTTPcreds(), and http->quiet
 *      Sean    - [[User:SColombo]]     - Wrote the lyricwiki class (now moved to lyricswiki.php)
 *      Brain   - [[User:Brian_McNeil]] - Wrote wikipedia->getfileuploader() and wikipedia->getfilelocation
 *	Davod   - [[User:Amitie_10g]]   - See bellow:
 *

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
	function data_encode($data,$keyprefix = "",$keypostfix = ""){
		assert(is_array($data));
		$vars=null;
		foreach($data as $key=>$value){
			if(is_array($value)) $vars .= $this->data_encode($value, $keyprefix.$key.$keypostfix.urlencode("["), urlencode("]"));
			else $vars .= $keyprefix.$key.$keypostfix."=".urlencode($value)."&";
		}
		return $vars;
	}

	/**
	  * This is the Construct.
	  * @return void
	 **/
	function __construct(){
		$this->ch = curl_init();
		$this->uid = dechex(rand(0,99999999));
		curl_setopt($this->ch,CURLOPT_COOKIEJAR,TEMP_PATH.'cluewikibot.cookies.'.$this->uid.'.dat');
		curl_setopt($this->ch,CURLOPT_COOKIEFILE,TEMP_PATH.'cluewikibot.cookies.'.$this->uid.'.dat');
		curl_setopt($this->ch,CURLOPT_MAXCONNECTS,100);
		$this->postfollowredirs = 0;
		$this->getfollowredirs = 1;
		$this->cookie_jar = array();
	}

	/**
	  * Send data through HTTP POST.
	  * @param $url The target URL.
	  * @param $data The POST data.
	  * @return mixed The response from Server.
	 **/
	function post($url,$data){
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
	function get($url){
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
	function setHTTPcreds($uname,$pwd){
		curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($this->ch, CURLOPT_USERPWD, $uname.":".$pwd);
	}

	/**
	  * This is the destruct.
	  * @return void
	 **/
	function __destruct (){
		curl_close($this->ch);
		@unlink('/tmp/cluewikibot.cookies.'.$this->uid.'.dat');
	}
}

/**
 * This class is interacts with the Wiki using api.php.
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
	function __construct ($url='https://commons.wikimedia.org/w/api.php',$hu=null,$hp=null){
		$this->http = new http;
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
	function __set($var,$val){
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
	function setUserAgent($userAgent){
		$this->http->userAgent = $userAgent;
	}

	/**
	  * Set/change the http header.
	  * @param $httpHeader The http header.
	 **/
	function setHttpHeader ( $httpHeader ){
		$this->http->httpHeader = $httpHeader;
	}

	/**
	  * Set/change the http headers.
	  * @param $httpHeader The http header.
	 **/
	function useDefaultHttpHeader (){
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
	function query($query,$post=null,$repeat=null,$url=null){

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
	function getpage($page,$revid=null,$detectEditConflict=false){
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
	function login($user,$pass){
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
	function setLogin($data){
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
 * @author Davod
 * @property string $url The Project URL (API path)
 * @property string $site_url The project URL (main page)
 **/
class WebArchiveBOT extends Wiki {
	public $url;
	public $site_url;

	/**
	  * This is the constructor
	  * @param $url The Project URL (forwarded to the parent class)
	  * @return void
	 **/
	function __construct($url,$max_files_retrived){
		Wiki::__construct($url); // Pass main parameter to parent Class' __construct()
		$this->site_url = parse_url($this->url);
		$this->site_url = $this->site_url['scheme'].'://'.$this->site_url['host'].'/wiki/';
	}

	/**
	 * Get the contents from the Wiki page. This is an alternative for getpage() with more features
	 * @param $page The page that we're working
	 * @param $props The properties that we want to obtain from the query (string or array), according to
	 * the 'prop' variable used by the MediaWiki API.
	 * @return array The API result (page contents and metadata according to $props)
	**/
	function getPageContents($page,$props=null){

		if(is_array($props)) $props = implode('%7C',$props);

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
	 * Get a list of the latest files uploaded to Wiki
	 * @param int $limit The maximum pages retrived
	 * @return array The API result (the list of the latest files uploaded)
	**/
	function getLatestFiles($limit=null){
		if(!is_int($limit)) $limit = 10;
		$query = "?action=query&list=allimages&format=php&aisort=timestamp&aidir=older&aiprop=timestamp%7Ccanonicaltitle&ailimit=$limit";
		$query = $this->query($query);		
		return $query['query']['allimages'];
	}

	/**
	 * Parse a list of URLs and remove blacklisted domain and empty-path ones
	 * @param array $links_g The URLs to be parsed
	 * @param array $blacklist the blacklisted domains
	 * @param bool $allow_empty_path To allow or not empy path links (with the domain name only)
	 * @return mixed The links filtered as array, or null if no valid links got
	**/
	function clearLinks($links_g,$blacklist=null,$allow_empty_path=true){
		if(!is_array($links_g)) return false;

		foreach($links_g as $link){
			if(preg_match('/^((http|https){1}\:)/',$link) == 0) $link = "http:$link";
			$host = parse_url($link,PHP_URL_HOST);

			if($this->inArray($host,$blacklist,true,true) === true){

				if($allow_empty_path === false){
					$path = parse_url($link,PHP_URL_PATH);

					if(empty($path) || $path == '/') continue;
					else $links[] = $link;
				}else $links[] = $link;
			}
		}
		if(!empty($links)){
			$links = array_unique($links);
			$links = array_filter($links);
		}
		return $links;
	}

	/**
	 * Wraper for in_array() that also parse regex
	 * @param mixed $needle The value to find.
	 * @param array $haystack The array where find in.
	 * @param bool $regex To allow or not regex (contents in $haystack should be string and valid regex).
	 * If false, then, in_array() will be used. Used only for regex, does not matter for non-regex search.
	 * @param bool $inverse To match or not the regex (no match is done with '?!')
	 * @return bool true if value were found in the array, false if not
	**/
	function inArray($needle,$haystack,$regex=false,$inverse=false){
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
	 * List the Pagenames list with the Timestamp and external links in an array, suitable for archive()
	 * @param array $query The query result from getLatestFiles(), $files['query']['allimages']
	 * @param array $haystack The array where find in.
	 * @return array The desired data ordered
	**/
	function getPagesExternalLinks($query,$extlinks_bl){
		$num = 0;
		foreach($query as $page){
			if(is_int($num/50)) sleep(5);
			$canonicaltitle = $page['canonicaltitle'];
			$timestamp = strtotime($page['timestamp']);

			$links_g = $this->GetPageContents($canonicaltitle,'externallinks');
			$links_g = $this->clearLinks($links_g['parse']['externallinks'],$extlinks_bl,false);

			if(!empty($links_g)){
				$links_g = array_filter($links_g);
				$links[$canonicaltitle] = array('timestamp'=>$timestamp,'urls'=>$links_g);
			}
			$num++;
		}
		var_dump($links);
		return $links;
	}

	/**
	 * Do the queries to save the given links to Web Archive, and check if them was already archived
	 * @param array $links_g The links (with the pagename as key) to save. The array should be composed as:
	 * * key: Canonical pagename
	 * * value: array:
	 *   * 'timestamp': The timestamp of the file uploaded to Wiki
	 *   * 'urls': The array with the URLs associated with the Pagename
	 * @param string $json_file The JSON file to store the results (gzipped). It should be writable.
	 * @param string $json_file_cache The JSON file to store the results (cache, latest 100 ones).
	 * @return bool true if everything is OK, or false in case of any error.
	**/
	function archive($links_g,$json_file,$json_file_cache){
		if(!is_array($links_g)) return false;

		foreach($links_g as $title=>$items){
			$timestamp_f = $items['timestamp'];
			$links = $items['urls'];
			if(empty($links)) continue;
			foreach($links as $link){
				$archive = file_get_contents('http://archive.org/wayback/available?url='.urlencode($link));
				if($archive != '{"archived_snapshots":{}}'){
					$archive = json_decode($archive,true);
					$archive_url_g = $archive['archived_snapshots']['closest']['url'];
					$archive_timestamp = strtotime($archive['archived_snapshots']['closest']['timestamp']);
				}

				$timestamp = time();
				if(!is_int($archive_timestamp)) $archive_timestamp = 0;
				$window_time = $timestamp-$archive_timestamp;

				if($window_time >= 172800){
					$headers = @get_headers("https://web.archive.org/save/$link");
					foreach($headers as $item){
						if(preg_match('/^(Content-Location\: \/web\/[\p{N}]{14}){1}/',$item) >= 1){
							$item = str_replace('Content-Location: /','https://web.archive.org/',$item);
							if(!empty($item)) $archive_url[] = $item;
						}
					}
				}
			}
			if(!empty($archive_url)) $data[$title] = array('timestamp'=>$timestamp_f,'urls'=>$archive_url);
		}

		if(is_file($json_file) && is_readable($json_file)){
			$zp = gzopen($json_file,'r');
			$json_data = gzread($zp,10485760);
			gzclose($zp);
			$json_data = json_decode($json_data,true);
			$data = $data + $json_data;
		}

		$data = array_filter($data);

		if(empty($data)) return false;

		array_multisort($data,SORT_DESC);

		if(file_put_contents($json_file,gzencode(str_replace('null,','',utf8_encode(json_encode($data,128))),9),LOCK_EX) === false) return false;

		$data = array_slice($data,0,100);

		if(file_put_contents($json_file_cache,str_replace('null,','',utf8_encode(json_encode($data,128))),LOCK_EX) === false) return false;

		return true;
	}
}
?>