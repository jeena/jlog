<?php

/**
 * Jlog
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * $HeadURL: http://jeenaparadies.net/svn/jlog/trunk/scripts/settings.class.php $
 * $Rev: 1768 $
 * $Author: driehle $
 * $Date: 2008-09-30 21:43:16 +0200 (Di, 30. Sep 2008) $
 */

/**
 * Settings class
 * 
 * This class represents the current settings for Jlog and
 * offers the possibility to modify these settings based on
 * user's input. The configuration can be saved to disk,
 * if wanted.
 * 
 * @category  Jlog
 * @package   Jlog_Settings
 * @license   GNU General Public License
 *
 */
class Settings {

    /**
    * Assoziative array holding configuration options
    *
    * @access private
    * @var array
    */
    var $d = array();

    /**
    * Assoziative array holding translations according
    * to the current language.
    *
    * @access private
    * @var array
    */
    var $l = array();

    /**
    * Settings() - class constructor
    *
    * @access public
    * @param array $l
    * @return void
    */
    function __construct($l) {
        // get the language array $l and put it into the class
        $this->l = $l;
    }

    /**
    * getValue() - reads configuration data
    * 
    * This procedure returns the value for then configuration option
    * specified by $key or an array of all options if $key is not
    * specified or false
    *
    * @access public
    * @param string|boolean $key
    * @return mixed
    */
    function getValue($key = false) {
        if($key === false) return $this->d;
        else return $this->d[strtolower($key)];
    }
  
    /**
    * setValue() - sets configuration data
    *
    * @access public
    * @param string|boolean $key
    * @param mixed $value      
    * @return mixed
    */
    function setValue($key, $value) {
        $this->d[strtolower($key)] = $value;
    }

    /**
    * importDataByConstants()
    * 
    * imports data from global constats starting with JLOG_ prefix      
    *
    * @access public
    * @return void
    */
    function importDataByConstants() {
        # no return
        // this is a blacklist of constats which are not to be written in settings.inc.php
        $search = array(
            'JLOG_ADMIN',
            'JLOG_DB_CONTENT',
            'JLOG_DB_COMMENTS',
            'JLOG_DB_CATASSIGN',
            'JLOG_DB_CATEGORIES',
            'JLOG_DB_ATTRIBUTES',
            'JLOG_UPDATE',
            'JLOG_LOGIN',
            'JLOG_SOFTWARE_VERSION',
            'JLOG_SOFTWARE_URL',
            'JLOG_SOFTWARE_PHPV',
            'JLOG_SOFTWARE_MYSQLV',
            'JLOG_ADMIN_PASSWORD_AGAIN'
        );
        
        // get all needed constants and put it into the class
        $constants = get_defined_constants();
        foreach($constants as $key => $value) {
            if(!in_array($key, $search) AND strpos($key, "JLOG_") !== false) {
                $this->setValue($key, $value);
            }
        }
    }

    /**
    * importDataByArray() - sets configuration data
    * 
    * Sets configuration data according to $d. If working in
    * non-exclusive mode (the default), $d is merged into the current 
    * configuration, otherwise the current configuration is discared
    * and $d is set as the new configuration.
    *
    * @access public
    * @param array $d
    * @param boolean $exclusiv
    * @return void
    */
    function importDataByArray($d = false, $exclusiv = false) {

        // get the data from users $d array and put it into the class
        if($d !== false) {
            if($exclusiv) $this->d = $d;
            else $this->d = array_merge($this->d, $d);
        }
        
        if(JLOG_ADMIN === true) {
            $this->d['jlog_db'] = JLOG_DB;
            $this->d['jlog_db_url'] = JLOG_DB_URL;
            $this->d['jlog_db_user'] = JLOG_DB_USER;
            $this->d['jlog_db_pwd'] = JLOG_DB_PWD;
            $this->d['jlog_db_prefix'] = JLOG_DB_PREFIX;
            $this->d['jlog_start_year'] = JLOG_START_YEAR;
            $this->d['jlog_path'] = JLOG_PATH;
            $this->d['jlog_basepath'] = JLOG_BASEPATH;
            if($this->d['jlog_admin_password'] == '') {
                $this->jlog_admin_password = JLOG_ADMIN_PASSWORD;
            }
            else {
                $this->d['jlog_admin_password'] = hashPassword($this->d['jlog_admin_password']);
                $this->d['jlog_admin_password_again'] = hashPassword($this->d['jlog_admin_password_again']);
            }
            $this->d['jlog_installed_version'] = JLOG_INSTALLED_VERSION;
            $this->d['jlog_installed_url'] = JLOG_INSTALLED_URL;
            $this->d['jlog_installed_phpv'] = JLOG_INSTALLED_PHPV;
            $this->d['jlog_installed_mysqlv'] = JLOG_INSTALLED_MYSQLV;
        }
        else {
            $this->d['jlog_admin_password'] = hashPassword($this->d['jlog_admin_password']);
            $this->d['jlog_admin_password_again'] = hashPassword($this->d['jlog_admin_password_again']);
        }
        
        if((defined('JLOG_SETUP') AND JLOG_SETUP === true)) 
        {
            $this->d['jlog_installed_version'] = JLOG_SOFTWARE_VERSION;
            $this->d['jlog_installed_url'] = JLOG_SOFTWARE_URL;
            $this->d['jlog_installed_phpv'] = JLOG_SOFTWARE_PHPV;
            $this->d['jlog_installed_mysqlv'] = JLOG_SOFTWARE_MYSQLV;
        }
    }

    /**
    * importSuggestedData() - preallocates configuration data
    * 
    * Initialises the configuration with useful settings during
    * the installation process.
    * 
    * @access public
    * @return void
    */
    function importSuggestedData() {
        // suggest some data for setup
        $this->setValue('jlog_path', $this->getSuggestPath());
        $this->setValue('jlog_basepath', dirname(dirname( __FILE__ )).DIRECTORY_SEPARATOR);
        $date = getdate();
        $this->setValue('jlog_start_year', $date['year']);
        $this->setValue('jlog_max_blog_orginal', 1);
        $this->setValue('jlog_max_blog_big', 4);
        $this->setValue('jlog_max_blog_small', 15);
        $this->setValue('jlog_sub_current', 6);
        $this->setValue('jlog_date', $this->l['date_format']);
        $this->setValue('jlog_date_comment', $this->l['date_format_comment']);
        $this->setValue('jlog_date_subcurrent', $this->l['date_format_subcurrent']);
        $this->setValue('jlog_info_by_comment', true);
        $this->setValue('jlog_db_url', 'localhost');
        $this->setValue('jlog_db_prefix', 'jlog_');
        $this->setValue('jlog_blogservices', 'http://rpc.pingomatic.com/');
        $this->setValue('jlog_language', (defined('JLOG_LANGUAGE') ? JLOG_LANGUAGE : 'de'));
    }


    /**
    * getSuggestPath() - generate a suggestion for JLOG_PATH
    *
    * @access private
    * @return string
    */
    function getSuggestPath() {
        $host  = empty($_SERVER['HTTP_HOST'])
                  ? (empty($_SERVER['SERVER_NAME'])
                      ? $_SERVER['SERVER_ADDR']
                      : $_SERVER['SERVER_NAME'])
                  : $_SERVER['HTTP_HOST'];
        $proto = (empty($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] == 'off')
                  ? 'http'
                  : 'https';
        $port  = $_SERVER['SERVER_PORT'];
    
        $uri   = $proto . '://' . $host;
        if ((('http' == $proto) and (80 != $port))
        or (('https' == $proto) and (443 != $port))) 
        {
            $uri .= ':' . $port;
        }
        $uri  .= dirname($_SERVER['SCRIPT_NAME']);
    
        return $uri;
    }

    /**
    * defaultValue() - gets a value of an array
    * 
    * Look for index $key in the array $array and return
    * the corresponding value if it exists or the default
    * value $default if it doesn't.
    *
    * @access public
    * @param array $array
    * @param mixed $key
    * @param mixed $default
    * @return mixed
    */
    function defaultValue($array, $key, $default = '') {
        if(isset($array[$key])) {
          return $array[$key];
        }
        else {
          return $default;
        }
    }

    /**
    * form_output() - generates HTML output for formular
    *
    * @access public
    * @return string
    */
    function form_output() {
        # returns the filled form
    
        $data = array_htmlspecialchars($this->d);
    
        if (isset($data['jlog_clean_url']) AND
		($data['jlog_clean_url'] === 'true' OR $data['jlog_clean_url'] === '1'))
	{
        	$d['clean_url_yes'] = " checked='checked'";
		$d['clean_url_no'] = '';
	}
        else {
        	$d['clean_url_yes'] = '';
		$d['clean_url_no'] = " checked='checked'";
	}
    
        if(isset($data['jlog_info_by_comment'])) $d['info_by_comment'] = " checked='checked'";
        else $d['info_by_comment'] = "";
    
        if(isset($data['jlog_bs_weblogs_com']) AND ($data['jlog_bs_weblogs_com'] === 'true' OR $data['jlog_bs_weblogs_com'] === '1'))
        $d['bs_weblogs_com'] = " checked='checked' ";
    
        if(defined("JLOG_ADMIN") AND JLOG_ADMIN === true) $admincenter_password = " ".$this->l['admin']['m_admin_password_admin'];
        else $admincenter_password = '';
    
        // get available languages
        $dir = opendir(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lang');
        $languages = array();
        while(($file = readdir($dir)) !== false) {
          if($file == '.' OR $file == '..') continue;
          if(!preg_match('/lang\.([a-zA-z0-9]+)\.inc\.php/', $file, $matches)) continue;
          $languages[] = $matches[1];
        }
    
        // do the form
        $form = "
         <form action='".$_SERVER['SCRIPT_NAME']."' method='post'>
          <fieldset><legend>".$this->l['admin']['m_metadata']."</legend>
           <p><label for='language'>".$this->l['admin']['m_language']."</label><br />";
        
        if(defined("JLOG_ADMIN") AND JLOG_ADMIN === true) $form .= add_session_id_input_tag();
        
        $form .= "<select class='userdata' id='language' name='jlog_language'>";
        foreach($languages as $lang) {
          $form .= "<option";
          if((isset($_POST['jlog_language']) AND $lang = $_POST['jlog_language']) OR $lang == JLOG_LANGUAGE)
          $form .= " selected='selected'";
          $form .= ">$lang</option>";
        }
    
        $form .= "</select>
           </p>
           
           <p><label for='website'>".$this->l['admin']['m_website']."</label><br />
              <input class='userdata' id='website' name='jlog_website' type='text' size='20' maxlength='255' value='".$this->defaultValue($data, 'jlog_website')."' /></p>
           <p><label for='publisher'>".$this->l['admin']['m_publisher']."</label><br />
              <input class='userdata' id='publisher' name='jlog_publisher' type='text' size='20' maxlength='255' value='".$this->defaultValue($data, 'jlog_publisher')."' /></p>
           <p><label for='admin_password'>".$this->l['admin']['m_admin_password'].$admincenter_password."</label><br />
              <input class='userdata' id='admin_password' name='jlog_admin_password' type='password' size='20' maxlength='255' /></p>
           <p><label for='admin_password_again'>".$this->l['admin']['m_admin_password_again'].$admincenter_password."</label><br />
              <input class='userdata' id='admin_password_again' name='jlog_admin_password_again' type='password' size='20' maxlength='255' /></p>
           <p><label for='email'>".$this->l['admin']['m_email']."</label><br />
              <input class='userdata' id='email' name='jlog_email' type='text' size='20' maxlength='255' value='".$this->defaultValue($data, 'jlog_email')."' /></p>
           <p><label for='description'>".$this->l['admin']['m_description']."</label><br />
              <textarea class='small' id='description' name='jlog_description' rows='2' cols='60'>".$this->defaultValue($data, 'jlog_description')."</textarea></p>
          </fieldset>
    
          <fieldset><legend>".$this->l['admin']['m_behavior']."</legend>
           <p><label>".$this->l['admin']['m_clean_url']."</label><br />
              <input id='clean_url_yes' name='jlog_clean_url' type='radio' value='true'".$d['clean_url_yes']." /><label class='nobreak' for='clean_url_yes'>".$this->l['admin']['yes']."</label>
              <input id='clean_url_no' name='jlog_clean_url' type='radio' value='false'".$d['clean_url_no']." /><label class='nobreak' for='clean_url_no'>".$this->l['admin']['no']."</label></p>
           <p><label for='max_blog_orginal'>".$this->l['admin']['m_max_blog_orginal']."</label><br />
              <input class='short' id='max_blog_orginal' name='jlog_max_blog_orginal' type='text' maxlength='3' size='3' value='".$this->defaultValue($data, 'jlog_max_blog_orginal')."' /></p>
           <p><label for='max_blog_big'>".$this->l['admin']['m_max_blog_big']."</label><br />
              <input class='short' id='max_blog_big' name='jlog_max_blog_big' type='text' size='3' maxlength='3' value='".$this->defaultValue($data, 'jlog_max_blog_big')."' /></p>
           <p><label for='max_blog_small'>".$this->l['admin']['m_max_blog_small']."</label><br />
              <input class='short' id='max_blog_small' name='jlog_max_blog_small' type='text' size='3' maxlength='3' value='".$this->defaultValue($data, 'jlog_max_blog_small')."' /></p>
           <p><label for='sub_current'>".$this->l['admin']['m_sub_current']."</label><br />
              <input class='short' id='sub_current' name='jlog_sub_current' type='text' size='3' maxlength='3' value='".$this->defaultValue($data, 'jlog_sub_current')."' /></p>
           <p><input id='info_by_comment' name='jlog_info_by_comment' type='checkbox' value='true'".$d['info_by_comment']."/> <label for='info_by_comment' class='nobreak'>".$this->l['admin']['m_info_by_comment']."</label></p>
           <p><label for='date'>".$this->l['admin']['m_date']."</label></p>
           <p><input class='userdata' id='date' name='jlog_date' type='text' value='".$this->defaultValue($data, 'jlog_date')."' size='20' /> <label for='date' class='nobreak'>".$this->l['admin']['m_date_posting']."</label></p>
           <p><input class='userdata' id='date_comment' name='jlog_date_comment' type='text' value='".$this->defaultValue($data, 'jlog_date_comment')."' size='20' /> <label for='date_comment' class='nobreak'>".$this->l['admin']['m_date_comment']."</label></p>
           <p><input class='userdata' id='date_subcurrent' name='jlog_date_subcurrent' type='text' value='".$this->defaultValue($data, 'jlog_date_subcurrent')."' size='20' /> <label for='date_subcurrent' class='nobreak'>".$this->l['admin']['m_date_subcurrent']."</label></p>
           <p><label for='blogservices'>".$this->l['admin']['m_bs']."</label></p>
           <p><textarea class='small' id='blogservices' name='jlog_blogservices' rows='2' cols='60'>".$this->defaultValue($data, 'jlog_blogservices')."</textarea></p>
          </fieldset>
         ";
    
        if(defined('JLOG_SETUP') AND JLOG_SETUP === true) {
          $form .=
          "
          <fieldset><legend>".$this->l['admin']['m_database']."</legend>
           <p><label for='db'>".$this->l['admin']['m_db']."</label><br />
              <input class='userdata' id='db' name='jlog_db' type='text' size='20' maxlength='255' value='".$this->defaultValue($data, 'jlog_db')."' /></p>
           <p><label for='db_url'>".$this->l['admin']['m_db_url']."</label><br />
              <input class='userdata' id='db_url' name='jlog_db_url' type='text' size='20' maxlength='255' value='".$this->defaultValue($data, 'jlog_db_url')."' /></p>
           <p><label for='db_user'>".$this->l['admin']['m_db_user']."</label><br />
              <input class='userdata' id='db_user' name='jlog_db_user' type='text' size='20' maxlength='255' value='".$this->defaultValue($data, 'jlog_db_user')."' /></p>
           <p><label for='db_pwd'>".$this->l['admin']['m_db_pwd']."</label><br />
              <input class='userdata' id='db_pwd' name='jlog_db_pwd' type='password' size='20' maxlength='255' value='".$this->defaultValue($data, 'jlog_db_pwd')."' /></p>
           <p><label for='db_prefix'>".$this->l['admin']['m_db_prefix']."</label><br />
              <input class='userdata' id='db_prefix' name='jlog_db_prefix' type='text' size='20' maxlength='255' value='".$this->defaultValue($data, 'jlog_db_prefix')."' />
              <input name='jlog_start_year' type='hidden' value='".$this->defaultValue($data, 'jlog_start_year', date("Y"))."' /></p>
              <input name='jlog_path' type='hidden' value='".$this->defaultValue($data, 'jlog_path')."' />
              <input name='jlog_basepath' type='hidden' value='".$this->defaultValue($data, 'jlog_basepath')."' />
          </fieldset>
         ";
        }
    
        $form .= "
          <p><input type='submit' class='button' value='".$this->l['admin']['submit']."' /></p>
         </form>
         ";
    
        return $form;
    }

    /**
    * validate() - validates the current configuration
    * 
    * If the current configuration is valid, an empty array is returned.
    * Otherwise the returned array containes all errors, described in the
    * current language.
    *
    * @access public
    * @return array
    */
    function validate() {
        # if everything validate then return true
        # otherwise return the $errors array
    
        $errors = array();
    
        // paths
        if(empty($this->d['jlog_path']) OR (check_url($this->d['jlog_path'], array ('http')) === false)) $errors[] = $this->l['admin']['e_path'];
        if(empty($this->d['jlog_basepath']) OR !is_dir($this->d['jlog_basepath'])) $errors[] = $this->l['admin']['e_basepath'];
        if($this->d['jlog_clean_url'] != 'true') $this->d['jlog_clean_url'] = 'false';
        // metadata
        if(empty($this->d['jlog_website'])) $errors[] = $this->l['admin']['e_website'];
        if(empty($this->d['jlog_publisher'])) $errors[] = $this->l['admin']['e_publisher'];
        if(defined('JLOG_SETUP') AND JLOG_SETUP) {
          if($this->d['jlog_admin_password'] == hashPassword(""))
          $errors[] = $this->l['admin']['e_admin_password'];
          elseif($this->d['jlog_admin_password'] !== $this->d['jlog_admin_password_again'])
          $errors[] = $this->l['admin']['e_admin_password_again'];
        }
        elseif(!empty($this->d['jlog_admin_password']) AND $this->d['jlog_admin_password'] !== $this->d['jlog_admin_password_again']) {
          $errors[] = $this->l['admin']['e_admin_password_again'];
        }
        // Fix of bug #148
        if(isset($this->d['jlog_admin_password_again']))
        unset($this->d['jlog_admin_password_again']);
    
        if(empty($this->d['jlog_email']) OR !strpos($this->d['jlog_email'], '@')) $errors[] = $this->l['admin']['e_email'];
        if(empty($this->d['jlog_description'])) $errors[] = $this->l['admin']['e_description'];
        // behavour
        if(!is_numeric($this->d['jlog_max_blog_orginal']) OR intval($this->d['jlog_max_blog_orginal']) < 0) $errors[] = $this->l['admin']['e_max_blog_orginal'];
        if(!is_numeric($this->d['jlog_max_blog_big']) OR intval($this->d['jlog_max_blog_big']) < 0) $errors[] = $this->l['admin']['e_max_blog_big'];
        if(!is_numeric($this->d['jlog_max_blog_small']) OR intval($this->d['jlog_max_blog_small']) < 0) $errors[] = $this->l['admin']['e_max_blog_small'];
        if(!is_numeric($this->d['jlog_sub_current']) OR intval($this->d['jlog_sub_current']) < 0) $errors[] = $this->l['admin']['e_sub_current'];
        if(!is_numeric($this->d['jlog_start_year'])) $errors[] = $this->l['admin']['e_start_year'];
        if($this->d['jlog_info_by_comment'] != 'true') $this->d['jlog_info_by_comment'] = 'false';
        // database
        if(empty($this->d['jlog_db'])) $errors[] = $this->l['admin']['e_db'];
        if(empty($this->d['jlog_db_url'])) $errors[] = $this->l['admin']['e_db_url'];
        // Fix of bug #196, prefix should only contain alphanumeric values, can be empty!
        if(!preg_match('/^[a-zA-Z0-9_]*$/', $this->d['jlog_db_prefix'])) $errors[] = $this->l['admin']['e_db_prefix'];
    
        return $errors;
    }

    /**
    * do_settings() - save configuration
    * 
    * Saves the current configuration to the settings.inc.php file 
    * in the personal folder. Return an empty array if configuration
    * was saved successfully, or an array containing descriptions of
    * the errors that occured otherwise.
    *
    * @access public
    * @return array
    */
    function do_settings() {
        # if it's all done return true
        # otherwise return the $errors array
    
        $errors = array();
    
        // if there is no new password set the old
        if(JLOG_ADMIN AND empty($this->d['jlog_admin_password'])) $this->d['jlog_admin_password'] = JLOG_ADMIN_PASSWORD;
    
        // remove slashes at the end of JLOG_PATH if present
        $this->d['jlog_path'] = rtrim($this->d['jlog_path'], '/');
        // make shure JLOG_BASEPATH ends with a slash!!
        $this->d['jlog_basepath'] = rtrim($this->d['jlog_basepath'], '/\\') . DIRECTORY_SEPARATOR;
    
        // no quotes for bolean and numbers
        $no_quotes = array (
            'jlog_clean_url' => 'bool',
            'jlog_max_blog_orginal' => 'int',
            'jlog_max_blog_big' => 'int',
            'jlog_max_blog_small' => 'int',
            'jlog_sub_current' => 'int',
            'jlog_start_year' => 'int',
            'jlog_info_by_comment' => 'bool'
        );
    
        // serialize data to file format
        $file_content = '<?php' . PHP_EOL . '// generated at ' . date('Y-m-d, h:i:s') . PHP_EOL;
    
        foreach($this->d as $key => $value) {
            $output = '';
            if(isset($no_quotes[$key])) {
                // boolean values
                if($no_quotes[$key] == 'bool') {
                    if($value == 'true' OR $value === true) $output = 'true';
                    else $output = 'false';
                }
                // numeric values
                else {
                    $output = (int) $value;
                }
            }
            // string values
            else {
                $output = '\'' . $this->escapeForPhp($value) . '\'';
            }
            $key = '\'' . $this->escapeForPhp(strtoupper($key)) . '\'';
            $file_content .= 'define(' . $key . ', ' . $output . ');' . PHP_EOL;
        }
    
        $file_content .= '// eof';
    
        // write to settings.inc.php
        if(!$handle = fopen(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."settings.inc.php", "w")) $errors[] = $this->l['admin']['can_not_open']." /personal/settings.inc.php";
        if(!fwrite($handle, $file_content)) $errors[] = $this->l['admin']['can_not_write']." /personal/settings.inc.php";
        fclose($handle);
    
        return $errors;
    }
  
    /**
    * escapeForPhp()
    * 
    * escapes $value so that it can be used between single quotes in a
    * PHP script, single quotes are better than double qoutes, as therein no
    * further substituions are performed   
    *
    * @access public
    * @param string $value   
    * @return string
    */
    function escapeForPhp($value) {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace("'",  "\'", $value);
        $value = str_replace("\0", '', $value);
        $value = str_replace("\r\n", "'.chr(13).chr(10).'", $value);
        $value = str_replace("\r", "'.chr(13).'", $value);
        $value = str_replace("\n", "'.chr(10).'", $value);
        return $value;
    }
}

// eof
