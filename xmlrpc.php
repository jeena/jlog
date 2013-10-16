<?php # A class for getting and sending Pingbacks
if (!isset($HTTP_RAW_POST_DATA))
	$HTTP_RAW_POST_DATA = file_get_contents('php://input');

 $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
 if(!defined('JLOG_BASEPATH')) require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'ixr-library.inc.php');
 require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'jlogHTTP_Request.php');


    if(defined("JLOG_ADMIN") === false) {
        function ping($args) {

            $pingback = new Jlog_GetPingback(JLOG_DB_CONTENT, JLOG_DB_COMMENTS, JLOG_PATH, new_sid());
            $pingback->get_ping($args);
            if($pingback->validate()) {
                $pingback->write_to_db();
                return "Thanks for your ping.";
            }
        }
        $server = new IXR_Server(array('pingback.ping' => 'ping'));
    }

class Jlog_GetPingback {

    var $errors = array();        // array
    var $method = "";             // string
    var $sourceURI = "";          // string
    var $targetURI = array();     // array incl: orginal, parsed [array from parse_url()], y, m, url
    var $title = "";              // string
    var $sid = "";                // string

    function Jlog_GetPingback($db_content, $db_comments, $path, $sid = NULL) {
        $this->db_content = $db_content;
        $this->db_comments = $db_comments;
        $this->path = $path;
        if($sid != NULL) $this->sid = $sid;
    }

    function get_ping($uris) {

        $ymurls = array();
        $tmp_host_got = "";
        $tmp_host_path_parsed = array();
        $tmp_host_path = "";

        $this->sourceURI = trim($uris[0]);
        $this->targetURI['orginal'] = trim(str_replace(array('&quot;','&lt;', '&gt;', '&amp;'), array('"', '<', '>', '&'), $uris[1]));
        $this->targetURI['parsed'] = parse_url($this->targetURI['orginal']);
        $tmp_host_got = str_replace('www.', '', $this->targetURI['parsed']['host']).$this->targetURI['parsed']['path'];
        $tmp_host_path_parsed = parse_url($this->path);
        $tmp_host_path = str_replace('www.', '', $tmp_host_path_parsed['host']).'/log.php';

        if(!empty($this->targetURI['parsed']['query']) AND ($tmp_host_got == $tmp_host_path)) {

            $ymurls = explode('&', $this->targetURI['parsed']['query']);
            $this->_counter = count($ymurls);

            foreach($ymurls AS $ymurl) {
                if(substr($ymurl, 0, 2) == 'y=') $this->targetURI['y'] = substr($ymurl, 2);
                elseif(substr($ymurl, 0, 2) == 'm=') $this->targetURI['m'] = substr($ymurl, 2);
                elseif(substr($ymurl, 0, 4) == 'url=') $this->targetURI['url'] = substr($ymurl, 4);
            }
        }
        else {
            ### Plugin Hook
            global $plugins;
            $tmp_URI = $plugins->callHook('xmlrpcPermalink', $this->targetURI['orginal']);
            
            $regex = "#^".$this->path."/([0-9]{4})/?([0-9]{2})/?([a-z0-9_\-]+)$#";
            preg_match($regex, $tmp_URI, $matches);
            $this->targetURI['y'] = $matches[1];
            $this->targetURI['m'] = $matches[2];
            $this->targetURI['url'] = $matches[3];
        }

    }

    function validate() {


        if(!strpos($this->targetURI['orginal'], str_replace(array('http://', 'https://'), '', str_replace('www.', '', $this->path))))
            $this->send_error(0, 'Target URI ('.$this->targetURI['orginal'].') is not this page: '.$this->path);


        // is there such a post?
        $sql = "SELECT  id, allowpingback FROM ".$this->db_content." WHERE
                        YEAR(date)  = '".escape_for_mysql($this->targetURI['y'])."' AND
                        MONTH(date) = '".escape_for_mysql($this->targetURI['m'])."' AND
                        url         = '".escape_for_mysql($this->targetURI['url'])."' AND
                        section     = 'weblog'
                  LIMIT 1";
        $blog = new Query($sql);
       if($blog->error()) $this->send_error(0, 'Could not read my database.');
        $blogrow = $blog->fetch();

        if($blog->numRows() != 1) $this->send_error(32, 'The specified target URI does not exist.'.$this->targetURI['orginal']);
        if($blogrow['allowpingback'] === 0) $this->send_error(33, 'The specified target URI cannot be used as a target. It it is not a pingback-enabled resource.');
        else $this->reference = $blogrow['id'];

        $s = new HTTP_Request($this->sourceURI);
        if(PEAR::isError($s->sendRequest())) $this->send_error(16, 'The source URI does not exist.');
        else {
            $source = $s->getResponseBody();
            $source = strip_tags(str_replace('<!DOCTYPE','<DOCTYPE', $source), '<title><a>');

            if (!$this->isLinkInHTML($this->targetURI['orginal'], $source))
                $this->send_error(17, 'The source URI does not contain a link to the target URI, and so cannot be used as a source.');

            preg_match('|<title>([^<]*?)</title>|is', $source, $title);

            if(! $utf8 = preg_match ('/charset\s*=\s*utf-8/i', $s->getResponseHeader("Content-Type")))
                $utf8 = 'application/xhtml+xml' == strtolower(trim($s->getResponseHeader("Content-Type")));

            // since text in database is utf8 encoded, we need to *en*code the title to utf8 if it isn't already, not *de*code it
            $this->title = empty($title[1]) ? $this->sourceURI : html_entity_decode($utf8 ? $title[1] : utf8_encode($title[1]));
        }

        $sql = "SELECT COUNT(*) AS ping FROM ".$this->db_comments." WHERE
                    reference = '".escape_for_mysql($blogrow['id'])."' AND
                    homepage = '".escape_for_mysql($this->sourceURI)."' AND
                    name = '".escape_for_mysql($this->title)."' AND
                    type = 'pingback'
                  LIMIT 1";
        $p = new Query($sql);
        if($p->error()) $this->send_error(0, 'Could not read my database.');
        $f = $p->fetch();

        if($f['ping'] > 0) $this->send_error(48, 'The pingback has already been registered.');

        if(count($this->errors) > 0) return false;
        else return true;
    }

    function write_to_db() {
        $sql = "INSERT INTO ".$this->db_comments." (
            sid,
            name,
            homepage,
            reference,
            date,
            type
          )
           VALUES (
            '".escape_for_mysql($this->sid)."',
            '".escape_for_mysql($this->title)."',
            '".escape_for_mysql($this->sourceURI)."',
            '".escape_for_mysql($this->reference)."',
            NOW(),
            'pingback'
           )";
        $ping = new Query($sql);

       if($ping->error()) $this->send_error(0, 'Could not write to database.');

    }
    
    function send_error($nr, $string) {
        $this->errors[] = $nr." ".$string;
        $error = new IXR_Error($nr, $string);
        $this->send_xml($error->getXml());
    }
    
    function get_errors() {
        return $this->errors;
    }
    
    function send_xml($xml) {
        header('Connection: close');
        header('Content-Length: '.strlen($xml));
        header('Content-Type: text/xml');
        header('Date: '.date('r'));
        echo $xml;
        exit;
    }

    function isLinkInHTML($search, $html) {
        preg_match_all('#<a[^>]+href\s*=\s*("([^"]+)"|\'([^\']+)\')[^>]*>(.+)</a>#Ui', $html, $matches);
        $links =  array_unique(array_merge($matches[2], $matches[3]));

        foreach($links as $link) {
            if($search === str_replace('&amp;', '&', $link)) return true;
        }
        return false;
    }

}

class Jlog_SendPingback {

    var $pageslinkedto = array();
    var $useragent = "";

    function Jlog_SendPingback($html, $pagelinkedfrom, $useragent) {
        // neet to prevent &amp; in url
        $this->pagelinkedfrom = htmlspecialchars_decode($pagelinkedfrom);
        $this->useragent = $useragent;

        preg_match_all('#<a[^>]+href\s*=\s*("([^"]+)"|\'([^\']+)\')[^>]*>(.+)</a>#Ui', $html, $matches);
        $pageslinkedto = array();
        $pageslinkedto = array_unique(array_merge($matches[2], $matches[3]));
        $count = count($pageslinkedto);
        for($i = 0; $count > $i; $i++) {
            if(substr($pageslinkedto[$i], 0, 4) !== "http") unset($pageslinkedto[$i]);
            // htmlspecialchars_decode is easier than str_replace
            else $pageslinkedto[$i] = htmlspecialchars_decode($pageslinkedto[$i]);
        }
        $this->pageslinkedto = $pageslinkedto;
    }

    function doPingbacks() {
        foreach($this->pageslinkedto as $pagelinkedto) {
            $feedback[] = $this->send($pagelinkedto);
        }
        return $feedback;
    }

    function send($pagelinkedto) {

        $s = new HTTP_Request($pagelinkedto);
        if(PEAR::isError($s->sendRequest())) return $pagelinkedto." &mdash; Error: The source URI does not exist.";
        else {
            $xmlrpcserver = $s->getResponseHeader("X-Pingback");
            if(!empty($xmlrpcserver));
            else {
                if(preg_match('<link rel="pingback" href="([^"]+)" ?/?>', $s->getResponseBody(), $matches)) {
                    $xmlrpcserver = $matches[1];
                }
                else return $pagelinkedto." &mdash; This is not a pingback-enabled resource.";
            }

            $client = new IXR_Client($xmlrpcserver);
            $client->timeout = 3;
            $client->useragent = $this->useragent;

            // when set to true, this outputs debug messages by itself
            $client->debug = false;

            if (! $client->query('pingback.ping', $this->pagelinkedfrom, $pagelinkedto ) )
                return $pagelinkedto." &mdash; Error: ".$client->getErrorMessage();

            else return $pagelinkedto." &mdash; ".$client->getResponse();
        }
    }

}
?>
