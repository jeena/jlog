<?php
 include_once('.'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require(JLOG_BASEPATH.'admin'.DIRECTORY_SEPARATOR.'blog.func.php');

if (!isset($c))
	$c = array('meta' => array('title' => ''));

 $c['meta']['title'] .= $l['admin']['new_post'];
 $c['main'] = output_admin_menu();
 $c['main'] .= "<h2>".$l['admin']['new_post']."</h2>";
 $form_input = strip($_POST);
 $form_input['date'] = strftime("%Y-%m-%d %H:%M:%s");

if (!isset($_POST['form_submitted'])) {
    // show form
     $c['main'] .= form_output($form_input);
}
elseif($_POST['form_submitted'] == $l['admin']['preview']) {
     $c['main'] .= error_output(check_input($form_input));
     $form_input['date'] = time();
     $c['main'] .= preview_output($form_input);
     $c['main'] .= form_output($form_input);
}
elseif($_POST['form_submitted'] == $l['admin']['publish']) {
    // Put data to database
    if(!check_input($form_input)) {
        if($id = insert_blog($form_input)) {
            $c['main'] .= "<p>".$l['admin']['entry_saved']."</p>";
            include_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'update.php');

           // ping blog services and pingback
          if($form_input['section'] == 'weblog') {
              $blogservices = explode("\n", str_replace("\r", "\n", str_replace("\r\n", "\n", JLOG_BLOGSERVICES)));
              foreach($blogservices as $blogservice) {
                  if(strlen($blogservice) > 0) $pingresult[] = doPing(trim($blogservice));
              }
              // if(is_array($pingresult)) $c['main'] .= "\n<ul>".join($pingresult)."\n</ul>";

              if(isset($form_input['allowpingback']) && $form_input['allowpingback'] != '0') {
                  $blogentryForURL = get_blog($id);
                  require_once(JLOG_BASEPATH.'xmlrpc.php');
                  $pingback = new Jlog_SendPingback($bbcode->parse($form_input['content']), blog($blogentryForURL['date'], $blogentryForURL['url']), " -- Jlog v".JLOG_SOFTWARE_VERSION);

                  $responces = array();
                  $responces = $pingback->doPingbacks();

/*                Die Ergebnisse der Pings verwirren den User nur habe ich mittlerweile festgestellt.

                  if(count($responces) > 0) {
                      $c['main'] .= " <ul>";
                      foreach($responces as $responce) {
                          $c['main'] .= "\n  <li>".$responce."</li>";
                      }
                      $c['main'] .= "\n </ul>";
                  }
*/

              }
          }
        }
    }
    else {
     // show preview and form
     $c['main'] .= error_output(check_input($form_input));
     $c['main'] .= form_output($form_input);
    }
}
else {
    // show form
     $c['main'] .= form_output($form_input);
}

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;

// verschiedene Dienste anpingen bei neuem Eintrag

function doPing($url) {
  $blog_title = JLOG_WEBSITE;
  $blog_url = JLOG_PATH;
  $timeout = 30;         //Sekunden
  $url = parse_url($url);
  
  $fp = @fsockopen($url['host'], 80, $errno, $errstr, $timeout);
    if(!$fp || preg_match('/\\s/', $url['host'])) {
      $response = 'Fehler: '.$errstr.' ('.$errno.')<br />Es konnte keine Verbindung hergestellt werden';
    } else {
      $data_string = '<?xml version="1.0" encoding="iso-8859-1"?'.'>
      <methodCall>
       <methodName>weblogUpdates.ping</methodName>
        <params>
         <param><value>'.$blog_title.'</value></param>
         <param><value>'.$blog_url.'</value></param>
       </params>
      </methodCall>';
      $data_header = "POST ".$url['path']." HTTP/1.0\r\n".
      "Host: {$url['host']}\r\n".
      "Content-Type: text/xml\r\n".
      "User-Agent: qxm XML-RPC Client\r\n".
      "Content-Length: ".strlen($data_string)."\r\n\r\n";
      fputs($fp, $data_header);
      fputs($fp, $data_string);
      unset($response);
      fclose($fp);
    }
  if(isset($response)) return '<li>'.$url['host'].' '.$response.'</li>';
}

// eof
