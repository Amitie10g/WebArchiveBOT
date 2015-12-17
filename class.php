<?php
/**
 * PassLicense: botclases.php based MediaWiki for semiautomated license review
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
 *    Class "wikipedia" renamed to "wiki"
 *    Functions in Class "extended" merged into "wiki", to avoid issues
 *    Some functions modified to accept more info
 *    Removed the echo statements. The classes should not output anything
 *    Removed parts commented intended for debugging purposes
 *    Removed several unneded functions
 *    Fixed the cURL issues with PHP5+
 *    Added several functions to interact with external services like Flickr
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
	  * Gets the ID for a page.
	  * @param $page The wikipedia page to get the id for.
	  * @return string The page ID.
	 **/
	function getpageid($page){
		$x = $this->query('?action=query&format=php&prop=revisions&titles='.urlencode($page).'&rvlimit=1&rvprop=content');
		foreach ($x['query']['pages'] as $ret){
			return $ret['pageid'];
		}
	}

	/**
	 * Returns an array with all the members of $category
	 * @param $category The category to use.
	 * @param $limit The maximum members returned (10 by default)
	 * @param $continue The parameters to continue previous query
	 * @param $subcat If returning subcategories
	 * @param $subcat (bool) Go into sub categories?
	 * @return array The category ID.
	**/
	function categorymembers($category,$limit=10,$continue=null,$subcat=false){

		$res = $this->query('?action=query&list=categorymembers&cmtype=file&cmsort=timestamp&cmdir=newer&format=php&cmtitle='.urlencode($category).'&cmlimit='.$limit.$continue);

		if (isset($res['error'])) return false;

		foreach($res['query']['categorymembers'] as $page){
			$title = str_replace(' ','_',$page['title']);
			// For a strange reason, "&cmtype=file&cmsort=timestamp" does not return File:-only reults
			if(preg_match('/^(File:){1}[\p{L}\p{N}\p{P}\p{S}_ ]+$/',$title) >= 1) $pages[] = $title;
		}

		// Previous and Next page (aka. Continue in the MediaWiki API) rewriten, and in developement
		// For now, the function will return just the members inside the limit of 250
		return $pages;
	}

	/**
	 * Returns an array with all the subpages of $page.
	 * @param $page
	 * @return array The subpages list.
	 **/
	function subpages($page){
		/* Calculate all the namespace codes */
		$ret = $this->query('?action=query&meta=siteinfo&siprop=namespaces&format=php');
		foreach ($ret['query']['namespaces'] as $x){
			$namespaces[$x['*']] = $x['id'];
		}
		$temp = explode(':',$page,2);
		$namespace = $namespaces[$temp[0]];
		$title = $temp[1];
		$continue = '';
		$subpages = array();
		while (true){
			$res = $this->query('?action=query&format=php&list=allpages&apprefix='.urlencode($title).'&aplimit=500&apnamespace='.$namespace.$continue);
			if (isset($x['error'])){
				return false;
			}
			foreach ($res['query']['allpages'] as $p){
				$subpages[] = $p['title'];
			}
			if (empty($res['query-continue']['allpages']['apfrom'])){
				return $subpages;
			} else {
				$continue = '&apfrom='.urlencode($res['query-continue']['allpages']['apfrom']);
			}
		}
	}

	/**
	 * This function takes a username and password and logs you into Wiki.
	 * @param $user Username to login as.
	 * @param $pass Password that corrisponds to the username.
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

	/**
	 * Check if we're allowed to edit $page.
	 * See http://en.wikipedia.org/wiki/Template:Bots
	 * for more info.
	 * @param $page The page we want to edit.
	 * @param $user The bot's username.
	 * @param $text
	 * @return bool
	 **/
	function nobots($page,$user=null,$text=null){
		if ($text == null){
			$text = $this->getpage($page);
		}
		if ($user != null){
			if (preg_match('/\{\{(nobots|bots\|allow=none|bots\|deny=all|bots\|optout=all|bots\|deny=.*?'.preg_quote($user,'/').'.*?)\}\}/iS',$text)){
				return false;
			}
		} else {
			if (preg_match('/\{\{(nobots|bots\|allow=none|bots\|deny=all|bots\|optout=all)\}\}/iS',$text)){
				return false;
			}
		}
		return true;
	}

	/**
	 * This function returns the edit token for the current user.
	 * @return string The edit token.
	 **/
	function getedittoken(){
		$x = $this->query('?action=query&prop=info&intoken=edit&titles=Main%20Page&format=php');
		foreach ($x['query']['pages'] as $ret){
			return $ret['edittoken'];
		}
	}
	/**
	 * Edits a page.
	 * @param $page Page name to edit.
	 * @param $data Data to post to page.
	 * @param $summary Edit summary to use.
	 * @param $minor Whether or not to mark edit as minor. (Default false)
	 * @param $bot Whether or not to mark edit as a bot edit. (Default true)
	 * @param $detectEC
	 * @param $maxlag
	 * @return array The API result
	 **/
	function edit($page,$data,$summary = '',$minor = false,$bot = true,$section = null,$detectEC=false,$maxlag=''){
		if ($this->token==null){
			$this->token = $this->getedittoken();
		}
		$params = array(
			'title' => $page,
			'text' => $data,
			'token' => $this->token,
			'summary' => $summary,
			'nocreate' => '1',
			'watchlist' => 'watch',
			($minor?'minor':'notminor') => '1',
			($bot?'bot':'notbot') => '1'
		);
		if ($section != null){
			$params['section'] = $section;
		}
		if ($this->ecTimestamp != null && $detectEC == true){
			$params['basetimestamp'] = $this->ecTimestamp;
			$this->ecTimestamp = null;
		}
		if ($maxlag!=''){
			$maxlag='&maxlag='.$maxlag;
		}
		return $this->query('?action=edit&format=php'.$maxlag,$params);
	}

	/**  BMcN 2012-09-16
	 * Retrieve a media file's actual location.
	 * @param $page The "File:" page on the wiki which the URL of is desired.
	 * @return string The URL pointing directly to the media file (Eg http://upload.mediawiki.org/wikipedia/en/1/1/Example.jpg)
	 **/
	function getfilelocation($page){
		$x = $this->query('?action=query&format=php&prop=imageinfo&titles='.urlencode($page).'&iilimit=1&iiprop=url');
		foreach ($x['query']['pages'] as $ret ){
			if (isset($ret['imageinfo'][0]['url'])){
				return $ret['imageinfo'][0]['url'];
			} else
				return false;
		}
	}

	/**  BMcN 2012-09-16
	 * Retrieve a media file's uploader.
	 * @param $page The "File:" page
	 * @return string The user who uploaded the topmost version of the file.
	 **/
	function getfileuploader ($page){
		$x = $this->query('?action=query&format=php&prop=imageinfo&titles='.urlencode($page).'&iilimit=1&iiprop=user');
		foreach ($x['query']['pages'] as $ret ){
			if (isset($ret['imageinfo'][0]['user'])){
				return $ret['imageinfo'][0]['user'];
			} else
				return false;
		}
	}

	/**
	 * Functions originaly from extended Class
	 **/

	/**
	 * Find a string
	 * @param $page The page we're working with.
	 * @param $string The string that you want to find.
	 * @return bool Value (1 found and 0 not-found)
	 **/
	function findstring( $page, $string )
	{
		$data = $this->getpage( $page );
		if( strstr( $data, $string ) )
			return 1;
		else
			return 0;
	}

	/**
	 * Replace a string
	 * @param $page The page we're working with.
	 * @param $string The string that you want to replace. (it can be a string or an array
	 * @param $newstring The string that will replace the present string. (same as above)
	 * @param $regex If $string will be considered as a regular expression
	 * @return string The new text of page
	 **/
	function replacestring($page,$string,$newstring,$regex=false){
		$data = $this->getpage($page);
		if($data != false){
			if(regex === true){
				if(is_array($string) && is_array($newstring)){
					foreach($string as $key=>$str){
						$data = preg_replace($str,$newstring[$key],$data);
					}

				}elseif(is_string($string) && is_string($newstring)){
					$data = preg_replace($newstring,$string,$data);
				}else{
					$data = false;
				}
			}else{
				$data = str_replace($string,$newstring,$data);
			}
			return $data;
		}
	}

	/**
	 * Get a template from a page
	 * @param $page The page we're working with
	 * @param $template The name of the template we are looking for
	 * @return string The searched (NULL if the template has not been found)
	 **/
	function gettemplate($page,$template){
		$data = $this->getpage($page);
		$template = preg_quote( $template, " " );
		$r = "/{{" . $template . "(?:[^{}]*(?:{{[^}]*}})?)+(?:[^}]*}})?/i";
		preg_match_all( $r, $data, $matches );
		if( isset( $matches[0][0])) return $matches[0][0];
		else return null;
	}
}

/**
 * This class is intended to do the license check/pass.
 * @author Davod
 * @property string $url The Project URL (API path)
 * @property string $site_url The project URL (main page)
 * @property string $flickr_licenses_blacklist The Flickr Licenses blacklist array
 * @property string $ipernity_licenses_blacklist The Ipernity Licenses blacklist array
 * @property string $picasa_licenses_blacklist The Picasa Licenses blacklist array
 * @property string $flickr_api_key The Flickr API key (optional)
 * @property string $ipernity_api_key The Ipernity API key (optional)
 **/
class WebArchiveBOT extends Wiki {
	public $url;
	public $site_url;
	private $flickr_api_key;
	private $ipernity_api_key;
	private $flickr_licenses_blacklist;
	private $ipernity_licenses_blacklist;
	private $picasa_licenses_blacklist;

	/**
	  * This is the constructor
	  * @param $url The Project URL (forwarded to the parent class)
	  * @param $flickr_licenses_blacklist The list of Flickr licenses ID not allowed at Wiki
	  * @param $ipernity_licenses_blacklist The list of Ipernity licenses ID not allowed at Wiki
	  * @param $picasa_licenses_blacklist The list of Picasa licenses ID not allowed at Wiki
	  * @param $flickr_api_key The Flckr API key
	  * @param $ipernity_api_key The Ipernity API key
	  * @return void
	 **/
	function __construct($url,
			     $flickr_licenses_blacklist,
			     $ipernity_licenses_blacklist,
			     $picasa_licenses_blacklist,
			     $flickr_api_key=null,
			     $ipernity_api_key=null){

		Wiki::__construct($url); // Pass main parameter to parent Class' __construct()
		$this->flickr_licenses_blacklist = $flickr_licenses_blacklist;
		$this->ipernity_licenses_blacklist = $ipernity_licenses_blacklist;
		$this->picasa_licenses_blacklist = $picasa_licenses_blacklist;
		$this->flickr_api_key = $flickr_api_key;
		$this->ipernity_api_key = $ipernity_api_key;
		$this->site_url = parse_url($this->url);
		$this->site_url = $this->site_url['scheme'].'://'.$this->site_url['host'].'/wiki/';
	}

	/**
	 * MediaWiki
	 **/

	/**
	 * Get the contents from the Wiki page in several formats, using the MediaWiki API (cached)
	 * @param $page The page that we're working
	 * @param $props The properties that we want to obtain from the query (string or array)
	 * @return array The contents as array
	**/
	function getPageContents($page,$props=null){

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
	 * Get the URL of the thumbnail of a File, best fit to width and height (cached)
	 * @param $page The page in File: namespace that we want to get the URL
	 * @param $width The desired width
	 * @param $height The desired height
	 * @return string The URL as string
	**/
	function getThumbURL($page,$width=null,$height=null){
		if(!is_numeric($width)) $width = '2000';
		if(!is_numeric($height)) $height = '2000';

		$page = urlencode($page);

		if(!empty($_SESSION['thumburl'][$page][$width.'_'.$height])) $thumburl = $_SESSION['thumburl'][$page][$width.'_'.$height];
		else{
			$thumburl = $this->query("?action=query&format=php&titles=$page&prop=imageinfo&iiprop=url&iiurlwidth=$width&iiurlheight=$height");

			$thumburl = $thumburl['query']['pages'];
			sort($thumburl);
			$thumburl = $thumburl[0]['imageinfo']['0']['thumburl'];

			$_SESSION['thumburl'][$page][$width.'_'.$height] = $thumburl;
		}

		return $thumburl;
	}

	/**
	 * Get the template tags from the given page, with its parameters (everything between {{}})
	 * @param $content The contents (wiki markup) that we're working with
	 * @param $tags The specific template tags what we want to match, as string or array
	 * @return array The desired template tags
	**/
	function getTemplates($content,$tags=null){
		if(!empty($tags)){
			if(is_array($tags)) $tags = implode('|',$tags);
			$tags = addcslashes($tags,'.\+*?[^]$(){}=!<>:-');
			$pattern_search = "/\({\{($tags){1}\}\})*/";
		}else $pattern_search = "/(\{\{[\p{L}\p{N}\p{P}\|= ]+\}\})+/";

		preg_match_all($pattern_search,$content,$templates);
		$templates = $templates[0];
		return $templates;
	}

	/**
	 * Get information about external sources from an URL. URL is parsed using regex,
	 * the relevant components are extracted from the first valid URL, and then the
	 * information is obtained using the external services API.
	 * @param $url_g The URL to be parsed, either string or array
	 * @return array An array with the following elements:
	 * * 'service'  The external service found
	 * * 'license'  The license text (eg. Creative Commons Attribution)
	 * * 'thumburl' The url of the file thumbnail, to be displayed at the page and compare it
	 *              with the file found at the Wiki
	 * * 'url'      The actual URL to the file located at the external service
	 * * 'allowed'  If the license is allowed at the Wiki (false if not)
	 * * If no valid URL found, return false.
	**/
	function getExternalInfo($url_g){

		if(is_array($url_g)){
			foreach($url_g as $url){

				// Flickr (normal link)
				if(preg_match('/^(http|https){1}\:\/\/(www\.|){1}(flickr\.com\/photos\/){1}[\w@]+\/[\w@]+/',$url) >= 1){
					$url = $url;
					$service = 'flickr';
					break;

				// Flcikr (short URL)
				}elseif(preg_match('/^(http|https){1}\:\/\/flic\.kr\/p\/[\w]+$/',$url) >= 1){
					$url = $url;
					$service = 'flickr_short';

				// Ipernity
				}elseif(preg_match('/^(http|https){1}\:\/\/(www\.|){1}(ipernity\.com\/doc\/){1}[\w@]+\/[\w@]+/',$url) >= 1){
					$url = $url;
					$service = 'ipernity';
					break;

				// Picasa (normal link)
				}elseif(preg_match('/^(http|https){1}\:\/\/(www\.|){1}picasaweb(\.google|){1}\.com\/[\p{L}\p{N}]+\/[\p{L}\p{N}]+#[\p{N}]+$/',$url) >= 1){
					$url = $url;
					$service = 'picasa_url';
					break;

				// Picasa (Google+ link)
				}elseif(preg_match('/^(http|https){1}\:\/\/plus\.google\.com\/photos\/\+[\p{L}\p{N}%]+\/albums\/[\p{N}]+\/[\p{N}]+\?pid\=[\p{N}]+&oid\=[\p{N}]+$/',$url) >= 1){
					$url = $url;
					$service = 'picasa_gplus';
					break;

				}// Add more services here
			}
		}else{

			// Flickr
			if(preg_match('/^(http|https){1}\:\/\/(www\.|){1}(flickr\.com\/photos\/){1}[\w@]+\/[\w@]+/',$url_g) >= 1){
				$url = $url_g;
				$service = 'flickr';

			// Flcikr (short URL)
			}elseif(preg_match('/^(http|https){1}\:\/\/flic\.kr\/p\/[\w]+$/',$url_g) >= 1){
				$url = $url_g;
				$service = 'flickr_short';

			// Ipernity
			}elseif(preg_match('/^(http|https){1}\:\/\/(www\.|){1}(ipernity\.com\/doc\/){1}[\w@]+\/[\w@]+/',$url_g) >= 1){
				$url = $url_g;
				$service = 'flickr';

			// Picasa (normal link)
			}elseif(preg_match('/^(http|https){1}\:\/\/(www\.|){1}picasaweb(\.google|){1}\.com\/[\p{L}\p{N}]+\/[\p{L}\p{N}]+#[\p{N}]+$/',$url) >= 1){
				$url = $url_g;
				$service = 'picasa_url';
				break;

			// Picasa (Google+ link)
			}elseif(preg_match('/^(http|https){1}\:\/\/plus\.google\.com\/photos\/\+[\p{L}\p{N}%]+\/albums\/[\p{N}]+\/[\p{N}]+\?pid\=[\p{N}]+&oid\=[\p{N}]+$/',$url) >= 1){
				$url = $url_g;
				$service = 'picasa_gplus';
				break;
			}
		}

		if(empty($url)) return false;

		switch($service){
			case 'flickr_short':
			case 'flickr':

				if(empty($this->flickr_api_key)) return false;

				switch($service){
					case 'flickr_short':
						$alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
						$photo_id = $this->getPhotoID($url,2);
						$photo_id = $this->flickrBase58Decode($photo_id,$alphabet);
						break;
					default:
						$photo_id = $this->getPhotoID($url,3);
						break;
				}

				$photo_info = $this->getFlickrInfo($photo_id);

				if($photo_info['stat'] == 'ok'){

					$photo_date_uploaded = strftime("%F %T",$photo_info['photo']['dates']['posted']);
					$photo_date_taken = $photo_info['photo']['dates']['taken'];

					$photo_license = $photo_info['photo']['license'];
					if(in_array($photo_license,$this->flickr_licenses_blacklist)) $allowed = false;
					$license = $this->getFlickrLicense($photo_license);

					$photourl = $photo_info['photo']['urls']['url'][0]['_content'];
					$thumburl = $this->getFlickrThumbURL($photo_id);

					$service = 'Flickr';

				}else $service = false;
				break;

			case 'ipernity':

				if(empty($this->ipernity_api_key)) return false;

				$photo_id = $this->getPhotoID($url,3);
				$photo_info = $this->getIpernityInfo($photo_id,$this->ipernity_api_key);

				if($photo_info['api']['status'] == 'ok'){
					$photo_license = $photo_info['doc']['license'];

					if(in_array($photo_license,$this->ipernity_licenses_blacklist)) $allowed = false;
					$license = $this->getIpernityLicense($photo_license);

					$thumburl = $this->getIpernityThumbURL($photo_info['doc']['thumbs']['thumb'],200);
					$photourl = $photo_info['doc']['link'];

					$photo_date_taken = $photo_info['doc']['dates']['created'];
					$photo_date_uploaded = strftime("%F %T",substr($photo_info['doc']['dates']['posted_at'],0,10));				

					$service = 'Ipernity';

				}else $service = false;
				break;

			case 'picasa_url':

				$url_r = str_replace('#','/',$url);
				$user = $this->getPhotoID($url_r,1);
				$album = $this->getPhotoID($url_r,2);
				$photo_id = $this->getPhotoID($url_r,3);
				$photo_info = $this->getPicasaInformation($user,$album,$photo_id);

				if($result !== false){
					$license = $photo_info['gphoto$license']['name'];
					if(in_array($photo_info['gphoto$license']['id'],$this->picasa_licenses_blacklist)) $allowed = false;

					$thumburl = $this->getPicasaThumbURL($photo_info['icon']['$t']);
					$photourl = $url;

					if(is_numeric($photo_info['exif$tags']['exif$time']['$t'])) $photo_date_taken = strftime("%F %T",substr($photo_info['exif$tags']['exif$time']['$t'],0,10));
					else $photo_date_taken = $photo_info['exif$tags']['exif$time']['$t'];

					if(is_numeric($photo_info['gphoto$timestamp']['$t'])) $photo_date_uploaded = strftime("%F %T",substr($photo_info['gphoto$timestamp']['$t'],0,10));
					else $photo_date_uploaded = $photo_info['gphoto$timestamp']['$t'];

					$service = 'Picasa';

				}else $service = false;
				break;
			// Add more services here
			default:
				$service = false;
				break;
		}

		if($service == false) return false;
		else return array('service'=>$service,
			     'license'=>$license,
			     'date_uploaded'=>$photo_date_uploaded,
			     'date_taken'=>$photo_date_taken,
			     'thumburl'=>$thumburl,
			     'photourl'=>$photourl,
			     'allowed'=>$allowed);
	}

	/**
	 * Get the closest (and not greater) number present in an array against an arbitrary number,
	 * specifically, the thumbnail sizes available, extracted from an external service API query.
	 * Credits to "Tim Cooper" at Stack Overflow: http://stackoverflow.com/users/142162/tim-cooper
	 * @param $haystack The arbitrary number where find them
	 * @param $needle The array with the (integer) values to get the closest one
	 * @param $use_key To return the array key instead of its value
	 * @return string The closest (and not greater) value or its key
	**/
	function bestFit($haystack,$needle,$use_key=false){
		$closest = null;
			foreach ($needle as $key=>$item){
				if ($closest === null || (abs($haystack - $closest) > abs($item - $haystack) && $item <= $haystack)){
					if($use_key === true) $closest = $key;
					else $closest = $item;
				}
			}
		return $closest;
	}

	/**
	 * Extract the desired parameter from an URL (mainly for the Photo ID).
	 * URL components are extracted using parse_url() with either
	 * PHP_URL_PATH (for services using URL rewrite) or PHP_URL_QUERY
	 * (for services that doesn't support it and receives the ID as a GET parameter).
	 * Photo ID from Flickr and Ipernity are fuond usually at the fourth position ($id[3]).
	 * @param $url The URL to be parsed
	 * @param $where The desired position or parameter to extract the ID
	 *   If integer, the position of the array given with explode()
	 *   If string, the name of the parameter in the URI
	 * @return string The numeric ID
	**/
	function getPhotoID($url,$where=null){
		if(is_int($where)){
			$id = explode('/',parse_url($url,PHP_URL_PATH));
			$id = $id[$where];
		}elseif(is_string($where)){
			$id = parse_url($url,PHP_URL_QUERY);
			$id = parse_str($url,$params);
			$id = $params[$where];
			if(empty($id)) $id = false;
		}else return false;

		return $id;
	}

	/**
	 * Get a valid colour in hexadecimal notation (for css) and return default values if them are not valid
	 * @param $color The colour in hexadecimal notation (eg ABF or ACDCEF)
	 * @param $bg If the colour is intended for background or foreground
	 * @return string The valid colour in hexadecimal nottaion, or '000' for foreground and 'fff' for background
	**/
	function hexColor($color,$bg=false){
		if($bg === false && preg_match('/^([0-9a-fA-F]{3}){1,2}$/',$color == 0) && $bg == false) $color = '000';
		elseif($bg === true && preg_match('/^([0-9a-fA-F]{3}){1,2}$/',$color == 0) && $bg == false) $color = 'fff';

		return $color;
	}

	/**
	 * Decode a base58 encoded Flickr shorten URL
	 * @param $num The sorten ID
	 * @param $alphabet The alphabet where find
	 * @return string The ID as base10
	**/
	function flickrBase58Decode($num,$alphabet){
		$decoded = 0;
		$multi = 1;
		while (strlen($num) > 0){
			$digit = $num[strlen($num)-1];
			$decoded += $multi * strpos($alphabet, $digit);
			$multi = $multi * strlen($alphabet);
			$num = substr($num, 0, -1);
		}

		return $decoded;
	}

	/**
	 * Flickr
	 **/

	/**
	 * Get useful information from a Flickr file using the Flickr API (cached)
	 * @param $id The Flickr File ID
	 * @return array The desired information
	**/
	function getFlickrInfo($id){
		if(!is_numeric($id)) return false;

		if(!empty($_SESSION['flickr_info'][$id])) $result = $_SESSION['flickr_info'][$id];
		else{
			$url = "https://api.flickr.com/services/rest/";
			$query = "?method=flickr.photos.getInfo&format=php_serial&api_key=$this->flickr_api_key&photo_id=$id";
			$result = $this->query($query,null,null,$url);
			$_SESSION['flickr_info'][$id] = $result;
		}

		if($result['stat'] == 'ok') return $result;
		else return false;
	}

	/**
	 * Get the license text from a Flickr License ID (cached)
	 * @param $id The Flickr License ID (NOT the file ID)
	 * @return string The license text
	**/
	function getFlickrLicense($id){
		if(!is_numeric($id)) return false;

		if(!empty($_SESSION['flickr_licenses'])) $result = $_SESSION['flickr_licenses'];
		else{
			$url    = "https://api.flickr.com/services/rest/";
			$query  = "?method=flickr.photos.licenses.getInfo&format=php_serial&api_key=$this->flickr_api_key";
			$result = $this->query($query,null,null,$url);
			$_SESSION['flickr_licenses'] = $result;
		}

		if($result['stat'] == 'ok'){
			$licenses = $result['licenses']['license'];

			foreach($licenses as $license){
				if($id == $license['id']){
					$name = $license['name'];
					break;
				}
			}
			if(!empty($name)) return $name;
			else return false;

		}else return false;
	}

	/**
	 * Get the thumbnail of a Flickr file using its ID (cached)
	 * @param $id The Flickr file ID
	 * @param $max_height = The maximum desired height
	 * @return string The numeric ID
	**/
	function getFlickrThumbURL($id,$max_height=200){
		if(!empty($_SESSION['flickr_thumburl'][$id])) $result = $_SESSION['flickr_thumburl'][$id];
		else{
			$url    = "https://api.flickr.com/services/rest/";
			$query  = "?method=flickr.photos.getSizes&format=php_serial&api_key=$this->flickr_api_key&photo_id=$id";
			$result = $this->query($query,null,null,$url);
			$_SESSION['flickr_thumburl'][$id] = $result;
		}

		if($result['stat'] == 'ok'){
			$get_sizes = $result['sizes']['size'];

			foreach($get_sizes as $size){
				$sizes[] = $size['height'];
			}

			$best_fit = $this->bestFit($max_height,$sizes,true);

			return $get_sizes[$best_fit]['source'];
		}else return false;
	}

	/**
	 * Ipernity
	 **/

	/**
	 * Get the information from a file in Ipernity using its ID
	 * @param $id The file ID
	 * @return array The information of the given file
	**/
	function getIpernityInfo($id){
		if(!empty($_SESSION['ipernity_info'][$id])) $result = $_SESSION['ipernity_info'][$id];
		else{
			$url    = "https://www.ipernity.com/api/doc.get/php/e";
			$query  = "?doc_id=$id&api_key=$this->ipernity_api_key";

			$result = $this->query($query,null,null,$url);
			$_SESSION['ipernity_info'][$id] = $result;
		}

		if($result['api']['status'] == 'ok') return $result;
		else return false;
	}

	/**
	 * Match the Ipernity license ID with the list of available licenses.
	 * Unlike Flickr, the licenses are not available through the API and
	 * should be stablished here statically.
	 * @param $id The License ID, obtained from getIpernityInfo()
	 * @return string The license text
	**/
	function getIpernityLicense($id){

		$licenses = array(0=>  "Copyright",
				  1=>  "Attribution (CC by)",
				  3=>  "Attribution+Non Commercial (CC by-nc)",
				  5=>  "Attribution+Non Deriv (CC by-nd)",
				  7=>  "Attribution+Non Commercial+Non Deriv (CC by-nc-nd)",
				  9=>  "Attribution+Share Alike (CC by-sa)",
				  11=> "Attribution+Non Commercial+Share Alike (CC by-nc-sa)",
				  255=>"Copyleft (PD author)");

		foreach($licenses as $key=>$lic){
			if($id == $key){
				$license = $lic;
				break;
			}
		}
		if(empty($license)) $license = false;
		return $license;
	}

	/**
	 * Extract the Thumbnail URL from an array of thumbnails, obtained
	 * with getIpernityInfo(), and find the best size with bestFit().
	 * @param $thumbs The Array containing the Thumbs element
	 * @param $max The maximum desired height
	 * @return string The desired URL of the thumbnail
	**/
	function getIpernityThumbURL($thumbs,$max=null){

		foreach($thumbs as $key=>$thumb){
			$h[$key] = $thumb['h'];
		}

		if(empty($h)) $h = 0;

		$best_fit = $this->bestFit($max,$h,true);
		// Workarround due bestFit() return the key one greater, but the value is correct
		$best_fit = $best_fit-1;

		return $thumbs[$best_fit]['url'];
	}

	/**
	 * Picasa
	 **/

	/**
	 * Get information about a file at Picasa, using the ATOM feed retreived as JSON (cached)
	 * @param $user The Username of the owner (either numeric or string)
	 * @param $album The Album (either numeric or string)
	 * @param $photoid The photo ID (as numeric)
	 * @return array The desired information
	**/
	function getPicasaInformation($user,$album,$photoid){
		if(!is_numeric($photoid)) return false;

		if(is_numeric($album)) $album_p = 'albumid';
		elseif(is_string($album)) $album_p = 'album';

		$url = "https://picasaweb.google.com/data/feed/api/user/$user/$album_p/$album/photoid/$photoid?hl=en&alt=json";

		if(!empty($_SESSION['picasa_info'][$user][$album][$photoid])) $result = $_SESSION['picasa_info'][$user][$album][$photoid];
		else{
			// Only public feeds will be reterived. Otherwise, am HTTP 404 will be got
			// Private feeds could be obtained with authentication using OAuth 2.0, but
			// that feature is in researching and developement
			$headers = get_headers($url.$query);
			if($headers[0] == 'HTTP/1.0 200 OK'){
				// Using file_get_contents() due cURL does not work with this result (tested)
				$result = file_get_contents($url);
				$result = @json_decode($result,true);
				if(empty($result['feed'])) $result = false;
				else $result = $result['feed'];
				$_SESSION['picasa_info'][$user][$album][$photoid] = $result;
			}else $result = false;
		}

		return $result;
	}

	/**
	 * Parse a Picasa file URL (direct link), and modify the height; bestfit() is not needed
	 * @param $url The URL to be parsed
	 * @param $bestfit The (maximum) desired height
	 * @return string The URL with the desired height established
	**/
	function getPicasaThumbURL($url,$bestfit=200){
		$url_a = parse_url($url);
		$url_p = explode('/',$url_a['path']);
		$url_p[5] = "h$bestfit";
		$url = $url_a['scheme'].'://'.$url_a['host'].implode('/',$url_p);
		return $url;
	}
	
	function getLatestFiles($limit=null){
		if(!is_int($limit)) $limit = 10;
		$query = "?action=query&list=allimages&format=php&aisort=timestamp&aidir=older&aiprop=timestamp%7Ccanonicaltitle&ailimit=$limit";
		return $this->query($query);
	}
	
	function clearLinks($links_g,$blacklist=null,$allow_empty_path=true){
		if(!is_array($links_g)) return false;

		foreach($links_g as $link){
			$host = parse_url($link,PHP_URL_HOST);
			if(preg_match('/^((http|https){1}\:)/',$link) == 0) $link = "http:$link";
			if($this->inArray($host,$blacklist,true,true)){
				if($allow_empty_path === false){
					$path = parse_url($link,PHP_URL_PATH);
					
					if(empty($path) || $path == '/') continue;
					else $links[] = $link;
				}else $links[] = $link;
			}
		}	
		return $links;
	}
	
	function archive($links,$title=null){
		if(!is_array($links)) return false;

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
			
			var_dump($window_time);
			
			if($window_time >= 43200){
				$headers = @get_headers("https://web.archive.org/save/$link");
				foreach($headers as $item){
					if(preg_match('/^(Content-Location\: \/web\/[\p{N}]{14}){1}/',$item) >= 1){
						$item = str_replace('Content-Location: /','https://web.archive.org/',$item);
						$archive_url[$title][$timestamp][] = array('timestamp'=>$timestamp,'urls'=>$item);
					}	
				}
			}else $archive_url[$title][$timestamp][] = array('timestamp'=>$timestamp,'urls'=>$archive_url_g);
		}

		$data = json_encode($archive_url);
		$data.= "\n";
		
		return file_put_contents($html_dir.'archived.json',utf8_encode($data),FILE_APPEND);
	}
	
	function inArray($needle,$haystack,$regex=false,$inverse=false){
		if($regex === true){
			$regex = implode('|',$haystack);
			if($inverse === true) $regex = "/^(?!$regex)/";
			else $regex = "/^($regex)/";

			if(preg_match($regex,$needle) >= 1) $found = true;
		}else $found = in_array($needle,$haystack);
		
		return $found;
	}
}
?>