<?php
 include_once('.'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require(JLOG_BASEPATH.'admin'.DIRECTORY_SEPARATOR.'blog.func.php');

  $get = strip($_GET);
  $c['main'] = output_admin_menu();

    if(empty($get['jplug'])) {

        $handle = "";
        $file = "";
        $plugindirectory = JLOG_BASEPATH.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR;

        if(is_dir($plugindirectory)) {
            $handle = opendir($plugindirectory);
            while( false !== ( $file = readdir ($handle) ) ) {
                if(substr($file, -10) === '.jplug.php') {
                    $pluginName = substr($file, 0, -10);
                    $availablePlugins .= "  <li><a href='".add_session_id_to_url("?jplug=".$pluginName)."'>".$pluginName."</a></li>\n";
                }
            }
            closedir($handle);

            if(!empty($availablePlugins)) {
                $availablePlugins = " <ul>\n".$availablePlugins." </ul>\n";
                $title = $l['admin']['plugins_headline'];
            }

            else {
                $availablePlugins = "<p>".$l['admin']['plugins_not_avaliable']."</p>";
                $title = $l['admin']['plugins_h_not_avaliable'];
            }

        }
    }
    else {
        $title = $get['jplug'];
        $availablePlugins = "<p>".$l['admin']['plugin_no_content']."</p>";
    }



  $c['meta']['title'] =  $title;
  $c['main'] .= "<h2>".$title."</h2>\n";

  $c['main'] .= $plugins->callHook('adminContent', $availablePlugins);


require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
