<?php

// hiervon werden die Plugins abgeleitet
class JlogPlugin {

 /* Hooks */
		function hook_body            ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string body
		function hook_commentForm     ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string with comment form output + array with form data
		function hook_adminContent    ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string content
		function hook_newComment      ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // array  form data
		function hook_updateComment   ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // array  form data
		function hook_deleteComment   ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string comment id
		function hook_showComment     ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string comment output
		function hook_onUpdate        ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // array  with all rss feeds and sub
		function hook_doEntry         ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string with entry + array with data from database + string count comments + string section
		function hook_doTeaser        ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string with entry + array with data from database + string count comments + string pre + string post
		function hook_bbcode          ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // bbcode object
		function hook_bbcomments      ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // bbcomments object
		function hook_adminForm       ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // admin formular
		function hook_insertEntry     ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // int id + array with form data
		function hook_updateEntry     ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // int id + array with form data
		function hook_permalink       ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string permalink + string date + string url + string section
		function hook_xmlrpcPermalink ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string url
		function hook_countComments   ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // array with id and count
		function hook_adminMail       ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // array with mail + array blogentry
		function hook_commentorMail   ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // array with mail + array blogentry
		function hook_commentAdminList($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string with actual tr + array with comment data
		function hook_previewComment  ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // same as newComment
		function hook_dispatchLogin   ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } //
		function hook_loginForm       ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } //	
		function hook_adminMenu       ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } // string with the admin menu html
		function hook_adminList       ($t, $a = null, $b = null, $c = null, $d = null, $e = null, $f = null) { return $t; } //
}

class JlogPluginManager {
    var $plugins = array();

    function __construct($plugindirectory) {
        $handle = "";
        $file = "";
        $this->get = strip($_GET);

        if(is_dir($plugindirectory)) {
            $handle = opendir($plugindirectory);
            while( false !== ( $file = readdir ($handle) ) ) {
                if(substr($file, -10) === '.jplug.php') {
                    include_once $plugindirectory.$file;
                    $this->register( substr($file, 0, -10) );
                }
            }
            closedir($handle);

        }
    }

    function register($plugin) {
        $this->plugins[] = new $plugin;
    }

    // Aufruf $JLogPluginManagerInstanz->callHook('eins', $param1[, $param2, ...]);
    // $param1 = Pflicht-Parameter, alle anderen optional
    function callHook($hook) {
        $hook = 'hook_' . $hook;

        $parameters = func_get_args();
        array_shift($parameters); // $hook entfernen
        if (!isset($parameters[0]))
            die('fatal error - no parameters');

        $hookresult = $parameters[0];

        foreach ($this->plugins as $plugin) {
            $parameters[0] = $hookresult;
            if($hook == 'hook_adminTitle' OR $hook == 'hook_adminContent') {
                if(strtolower($this->get['jplug']) === strtolower(get_class($plugin)))
                    $hookresult = call_user_func_array(array($plugin, $hook), $parameters);
            }
            else $hookresult = call_user_func_array(array($plugin, $hook), $parameters);
        }
        return $hookresult;
    }
}
?>
