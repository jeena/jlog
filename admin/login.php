<?php
### Loginscript taken form <http://aktuell.de.selfhtml.org/tippstricks/php/loginsystem/>
### autor: Benjamin Wilfing
### email: benjamin.wilfing@selfhtml.org
### homepage: <http://wilfing-home.de>
###
### adapted for Jlog by Jeena Paradies

ini_set('session.use_trans_sid', false);

define('JLOG_ADMIN', true);
define('JLOG_LOGIN', true);
require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'proto.inc.php');
require(JLOG_BASEPATH.'admin'.DIRECTORY_SEPARATOR.'blog.func.php');

$false_password = "";
$get = strip($_GET);
$post = strip($_POST);

### Plugin Hook
$dispatch_login = $plugins->callHook('dispatchLogin', true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' AND $dispatch_login) {
	session_start();
	$passwort = $post['password'];
	$url      = !empty($post['url']) ? $post['url'] : '';
	$hostname = $_SERVER['HTTP_HOST'];
	$path     = dirname($_SERVER['SCRIPT_NAME']) . '/';
	
	if (strpos($url, "\n") !== false or strpos($url, "\r") !== false) {
	    die('Somebody tried to hack Jlog with Response-Splitting.');
	}
	
	if (hashPassword($passwort) == JLOG_ADMIN_PASSWORD) {
        $_SESSION['logged_in'] = true;
		session_regenerate_id();	// neue SID
    	
    	if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
      		if (php_sapi_name() == 'cgi') header('Status: 303 See Other');
      		else header('HTTP/1.1 303 See Other');
    	}
    	
		if ($path == $url) $url = $path . 'new.php';		
		if (!empty($url)) $path = $url;
		
		header('Location: ' . add_session_id_to_url(proto().'://'.$hostname.$path));
		exit;
	}
	else {
		$false_password = "  <p class='error'>".$l['admin']['login_false_pw']."</p>\n";
	}
}
else {
    setcookie("cookieallowed", "true", time() + 180);
}

$c['meta']['title'] = $l['admin']['login_headline'];
$btnValue = htmlspecialchars($l['admin']['login_send']);
$c['main'] = '
  <h2>'.$l['admin']['login_headline'].'</h2>
  ' . $false_password . '
  <form action="login.php" method="post" accept-charset="UTF-8">
   <p><label for="password">' . $l['admin']['login_password'] . '</label>
      <input class="userdata" id="password" type="password" name="password" autocomplete="off" spellcheck="false" writingsuggestions="false"/>
      <input style="display: none;" name="username" type="text" value="do-not-change" /></p>
   <p><input type="hidden" name="url" value="' . htmlspecialchars(!empty($get['url']) ? $get['url'] : '') . '" />
      <button value="' . $btnValue . '">' . $btnValue . '</button></p>
  </form>
';

### Plugin Hook
$c["main"] = $plugins->callHook('loginForm', $c["main"]);


require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
