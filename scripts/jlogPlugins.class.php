<?php
// FIXME Strict Standards

// hiervon werden die Plugins abgeleitet
class JlogPlugin {

 /* Hooks */
		function hook_body            ($t) { return $t; } // string body
		function hook_commentForm     ($t, $c) { return $t; } // string with comment form output + array with form data
		function hook_adminContent    ($t) { return $t; } // string content
		function hook_newComment      ($t) { return $t; } // array  form data
		function hook_updateComment   ($t) { return $t; } // array  form data
		function hook_deleteComment   ($t) { return $t; } // string comment id
	function hook_showComment($comment, $data, $nr) { return $comment; } // string comment output
		function hook_onUpdate        ($t) { return $t; } // array  with all rss feeds and sub
	function hook_doEntry($output /* entry */,
		$dbData		/* array with data from database */,
		$cc		/* string count comments */,
		$section	/* string section */) { return $output; }
		function hook_doTeaser        ($t) { return $t; } // string with entry + array with data from database + string count comments + string pre + string post
		function hook_bbcode          ($t) { return $t; } // bbcode object
		function hook_bbcomments      ($t) { return $t; } // bbcomments object
	function hook_adminForm($formHTML, $formData) { return $formHTML; } // admin formular
	function hook_insertEntry($id, $form /* bereits escaped */) {} // int id + array with form data
	function hook_updateEntry($id, $form) {} // int id + array with form data
		function hook_permalink       ($t) { return $t; } // string permalink + string date + string url + string section
		function hook_xmlrpcPermalink ($t) { return $t; } // string url
		function hook_countComments   ($t) { return $t; } // array with id and count
	function hook_adminMail($mail, $blogentry, $id) { return $mail; } // array with mail + array blogentry
		function hook_commentorMail   ($t) { return $t; } // array with mail + array blogentry
	function hook_commentAdminList($comment, $data) { return $comment; } // string with actual tr + array with comment data
		function hook_previewComment  ($t) { return $t; } // same as newComment
		function hook_dispatchLogin   ($t) { return $t; } //
		function hook_loginForm       ($t) { return $t; } //	
		function hook_adminMenu       ($t) { return $t; } // string with the admin menu html
		function hook_adminList       ($t) { return $t; } //
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
                if(isset($this->get['jplug']) && strtolower($this->get['jplug']) === strtolower(get_class($plugin)))
                    $hookresult = call_user_func_array(array($plugin, $hook), $parameters);
            }
            else $hookresult = call_user_func_array(array($plugin, $hook), $parameters);
        }
        return $hookresult;
    }
}
