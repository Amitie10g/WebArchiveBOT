<?php
/**
  * WebArchiveBOT: botclases.php based MediaWiki for archiving external links to Web Archive
  *
  * @copyright (c) 2015-2017  Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
  *
  * Contains parts of the Chris G's Bot classes library
  *
  *  (c) 2008-2012       Chris G http://en.wikipedia.org/wiki/User:Chris_G
  *  (c) 2009-2010       Fale    http://en.wikipedia.org/wiki/User:Fale
  *  (c) 2010            Kaldari http://en.wikipedia.org/wiki/User:Kaldari
  *  (c) 2011            Gutza   http://en.wikipedia.org/wiki/User:Gutza
  *  (c) 2012            Sean    http://en.wikipedia.org/wiki/User:SColombo
  *  (c) 2012            Brain   http://en.wikipedia.org/wiki/User:Brian_McNeil
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
  *      Cobi    - [[User:Cobi]]         - Wrote the http class and some of the wikipedia class
  *      Chris   - [[User:Chris_G]]      - Wrote the most of the wikipedia class
  *      Fale    - [[User:Fale]]         - Polish, wrote the extended and some of the wikipedia class
  *      Kaldari - [[User:Kaldari]]      - Submitted a patch for the imagematches function
  *      Gutza   - [[User:Gutza]]        - Submitted a patch for http->setHTTPcreds(), and http->quiet
  *      Sean    - [[User:SColombo]]     - Wrote the lyricwiki class (now moved to lyricswiki.php)
  *      Brain   - [[User:Brian_McNeil]] - Wrote wikipedia->getfileuploader() and wikipedia->getfilelocation
  *      Davod   - [[User:Amitie_10g]]   - See bellow:
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
        private $site_url;
        private $email_operator;
        private $extlinks_bl;
        private $pages_per_query;
        private $public_html_path;
        private $json_file;
        private $json_file_cache;
        private $json_file_max_size;

        /**
          * This is the constructor
          * @param $url The Project URL (forwarded to the parent class)
          * @param $max_files_retrived Maximum ammount of files retreived per query
          * @param mail_operator The Operator's email address
          * @return void
         **/
        function __construct($url,$email_operator,$extlinks_bl,$pages_per_query=100,$public_html_path,$json_file,$json_file_cache,$json_file_max_size=1000){
                
                if(!is_array($extlinks_bl)) $extlinks_bl = null;
                if(!is_int($pages_per_query)) $pages_per_query = 100;
                if(!is_int($json_file_max_size)) $json_file_max_size = 1000;
         
                Wiki::__construct($url); // Pass main parameter to parent Class' __construct()
                $this->site_url = parse_url($this->url);
                $this->site_url = $this->site_url['scheme'].'://'.$this->site_url['host'].'/wiki/';
                $this->email_operator = $email_operator;
                $this->extlinks_bl = '/('.implode('|',$extlinks_bl).')/';
                $this->pages_per_query = $pages_per_query;
                $this->public_html_path = $public_html_path;
                $this->json_file = $json_file;
                $this->json_file_cache = $json_file_cache;
                $this->json_file_max_size = $json_file_max_size;
        }

        /**
         * Get the contents from the Wiki page. This is an alternative for getpage() with more features
         * @param $page The page that we're working
         * @param $props The properties that we want to obtain from the query (string or array)
         * @return array The API result (page contents and metadata in the desired format)
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
         * Get a list of the latest files uploaded to Commons
         * @param int $limit The maximum pages retrived
         * @return array The API result (the list of the latest files uploaded)
        **/
        function getLatestFiles(){
                $query = "?action=query&list=allimages&format=php&aisort=timestamp&aidir=older&aiprop=timestamp%7Ccanonicaltitle&ailimit=$this->pages_per_query";
                $query = $this->query($query);
                return $query['query']['allimages'];
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
        function getPagesExternalLinks($query){
                foreach($query as $page){

                        $canonicaltitle = $page['canonicaltitle'];
                        $timestamp = strtotime($page['timestamp']);

                        $links_g = $this->GetPageContents($canonicaltitle,'externallinks');
                        $links_g = $links_g['parse']['externallinks'];

                        if(!empty($links_g)){
                                $links_g = array_filter($links_g);
                                $links[$canonicaltitle] = array('timestamp'=>$timestamp,'urls'=>$links_g);
                        }
                }
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
        function archive($data){

                if(!is_array($data)) return false;
                $data = $this->archive1($data);

                if(empty($data)) return false;
                $data = $this->archive2($data);

                return $this->archive3($data);
        }

        /**
         * Step one of the Archive process: Query to Wayback Machine to archive the links and get the Archived URLs
         * @param array $data_g the array containing the filenames and URLs
         * @return array the data given with $data_g, with the Wayback Machine URLs instead
        **/
        function archive1($data_g){

                if(empty($data_g)) return false;

                foreach($data_g as $title=>$item){

                        $timestamp = $item['timestamp'];
                        $urls = $this->urls2archive_urls($item['urls']);
                        if(!empty($urls)) $data[$title] = array('timestamp'=>$timestamp,'urls'=>$urls);
                }

                return $data;
        }

        /**
         * Step two of the Archive process: Get the archived pages stored in the local JSON file, if exists, and append the new pages uploaded
         * @param array $data the array containing the data retrived in the first step
         * @param string $json_file the local JSON file (GZIP compressed)
         * @return array the contents from the local JSON
        **/
        function archive2($data){
                if(!is_array($data)) return false;
                if(is_file("$this->public_html_path/$this->json_file")) $json_data = json_decode(gzdecode(file_get_contents("$this->public_html_path/$this->json_file")),true);
                if(is_array($json_data)) $data = $data + $json_data;
                if(!empty($data)) array_multisort($data,SORT_DESC);

                return $data;
        }

        /**
         * Step three of the Archive process: Write the data retrived from the new files uploaded and the previous local JSON to the local JSON
         * @param array $data the data to be writen
         * @param string $json_file the local JSON filename
         * @param string $json_file_cache the local JSON filename (cache, to be used for the web application)
         * @return bool true if success, false if fail
        **/
        function archive3($data){

                $json_wrote = $this->archive31($data);
                $json_cache_wrote = $this->archive32($data);
                if($json_wrote && $json_cache_wrote) return true;
                else return false;
        }

        /**
         * Step 3.1 of the Archive process: Write the JSON file
         * @param array $data the data to be writen
         * @param string $json_file the JSON filename
         * @return bool true if success, false if error
        **/
        function archive31($data){

                if(!is_array($data)) return false;
                if(!is_int($this->json_file_max_size)) $this->json_file_max_size = 1000;
                $data = array_slice($data,0,$this->json_file_max_size,true);
                $data = gzencode(json_encode($data,JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE,8),9);

                if(file_put_contents("$this->public_html_path/$this->json_file",$data,LOCK_EX) != false) return true;
                else return false;
        }

        /**
         * Step 3.2 of the Archive process: Write the JSON cache file
         * @param array $data the data to be writen
         * @param string $json_file the JSON filename
         * @return bool true if success, false if error
        **/
        function archive32($data){

                if(!is_array($data)) return false;
                $data = array_slice($data,0,50,true);

                $data = json_encode($data,JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE,8);
                if(file_put_contents("$this->public_html_path/$this->json_file_cache",$data,LOCK_EX) != false) return true;
                else return false;
        }

        /**
         * Parse URLs and retrive the Archived version at Wayback Machine
         * @param array $urls the URLs to be parsed
         * return array the Wayback Machine URLs retrived
        **/
        function urls2archive_urls($urls){
                foreach($urls as $url){
                 
                        if(preg_match($this->extlinks_bl,$url)) continue;

                        $archive_g = file_get_contents('http://archive.org/wayback/available?url='.urlencode($url));
                        if($archive_g != '{"archived_snapshots":{}}'){
                                $archive = json_decode($archive_g,true);
                                $archive_timestamp = strtotime($archive['archived_snapshots']['closest']['timestamp']);
                        }

                        $timestamp = time();
                        if(!is_int($archive_timestamp)) $archive_timestamp = 0;
                        $window_time = $timestamp-$archive_timestamp;

                        if($window_time >= 172800){
                                $headers = @get_headers("https://web.archive.org/save/$url",1);
                         
                                if($headers[0] == "HTTP/1.1 403 FORBIDDEN") continue;

                                $location = $headers['Content-Location'];

                                if(!empty($location)){
                                        if(is_array($location)) $location = end($location);
                                        if(preg_match("/^\/web\/[0-9]{14}\/[\p{L}\p{N}\p{S}\p{P}\p{M}\p{Zs}]+$/",$location) === 1) $archive_urls[] = "https://web.archive.org$location";
                                        else echo "Wrong location: $location\n";
                                }
                        }
                }
         
                if(!empty($archive_urls)) return array_unique($archive_urls);
        }

        /**
         * Send email (mostly for errors)
         * @param string $message the message
         * @param string $subject the subject ("Errors with WebArchiveBOT" by default)
         * @return void
        **/
        function sendMail($message,$subject=null){
                if($subject == null) $subject = "Errors with WebArchiveBOT";
                $from = "webarchivebot-noreply@wmflabs.org";
                $to = $this->mail_operator;
                $headers = "From: WebArchiveBOT <$from>\r\n";
                mail($to,$subject,$message,$headers);
        }
}
?>
