<?php


class JlogUpdater 
{
    /**
     * Existing versions of Jlog as array
     * version -> next version in history
     * 
     * @var array
     */
    var $versions = array(
        '1.0.2' => '1.1.0',
        '1.1.0' => '1.1.1',
        '1.1.1' => '1.1.2',
        '1.1.2' => '1.1.3'
    );
    
    function JlogUpdater() 
    {
        require_once(JLOG_BASEPATH."scripts".DIRECTORY_SEPARATOR."settings.class.php");
    }
    
    function getOldVersion() 
    {
        return JLOG_INSTALLED_VERSION;
    }
    
    function getNewVersion() 
    {
        return JLOG_SOFTWARE_VERSION;
    }
    
    function isUp2Date() 
    {
        if (version_compare($this->getOldVersion(), $this->getNewVersion(), '<')) {
            return false;
        }
        return true;
    }
    
    function prepareForm($l) 
    {
        $html = '<form action="' . $_SERVER['SCRIPT_NAME'] . '" method="post">'
              . '<p>' . $l['admin']['e_admin_password'] . ': '
              . '<input type="password" name="jlog_password" value="" />'
              . '</p>';
        $version = $this->getOldVersion();
        while (isset($this->versions[$version])) {
            $class = $this->_loadUpdateClass($version, $this->versions[$version]);
            $html .= sprintf("<h2>Update <var>%s</var> &#x2192; <var>%s</var></h2>\n", $version, $this->versions[$version]);
            $html .= $class->getForm($l);
            $version = $this->versions[$version];
        }
        $html .= '<p><input type="submit" name="update" value="' . $l['admin']['update_start'] . '" /></p>';
        $html .= '</form>';
        return $html;
    }
    
    function performUpdate($l) 
    {
        if (JLOG_AMDIN_PASSWORD !== hashPassword($_POST['jlog_password']) and JLOG_ADMIN_PASSWORD !== hashPassword(utf8_decode($_POST['jlog_password']))) {
            return '<p>' . $l['admin']['login_false_pw'] . '</p>';
        }
        
        require_once(JLOG_BASEPATH."scripts".DIRECTORY_SEPARATOR."settings.class.php");
        // read current settings from environment
        $settings = new Settings($l);
        $settings->importDataByConstants();
        
        $error = false;
        $html = '';
        $version = $this->getOldVersion();
        while (isset($this->versions[$version])) {
            $class = $this->_loadUpdateClass($version, $this->versions[$version]);
            $html .= sprintf("<h2>Update <var>%s</var> &#x2192; <var>%s</var></h2>\n", $version, $this->versions[$version]);
            $result = $class->performUpdate($l, $settings);
            if ($result === true) {
                // we know that update class ran successfully
                $result = $this->_updateVersionNumber($settings, $this->versions[$version]);
                // check if errors occured
                if (!empty($result)) {
                    $this->_renderErrors($result);
                    break;
                }
                else {
                    $html .= '<p>' . $l['admin']['update_successfull_part'] . '</p>';
                }
            }
            else {
                $html .= $this->_renderErrors($result);
                break;
            }
            $version = $this->versions[$version];
        }
        if ($error) {
            $html .= '<p>' . $l['admin']['update_failure'] . '</p>';
        }
        else {
            $html .= '<p>' . $l['admin']['update_successfull'] . '</p>';
        }
        return $html;
    }
    
    function _getUpdateFile($oldver, $newver) 
    {
        $oldver = str_replace('.', '', $oldver);
        $newver = str_replace('.', '', $newver);
        return "{$oldver}To{$newver}.php";
    }
    
    function _getUpdateClass($oldver, $newver) 
    {
        $oldver = str_replace('.', '', $oldver);
        $newver = str_replace('.', '', $newver);
        return "JlogUpdate_{$oldver}To{$newver}";
    }
    
    function _loadUpdateClass($oldver, $newver) 
    {
        $file = $this->_getUpdateFile($oldver, $newver);
        $class = $this->_getUpdateClass($oldver, $newver);
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . $file);
        return new $class(); 
    }
    
    function _renderErrors($errors)
    {
        $html = '<ul class="error">';
        foreach ($errors as $error) {
            $html .= '<li>' . $error . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
    
    function _updateVersionNumber($settings, $newver) 
    {   
        $settings->setValue('jlog_installed_version', $newver);
        $settings->setValue('jlog_installed_url', JLOG_SOFTWARE_URL);
        $settings->setValue('jlog_installed_phpv', JLOG_SOFTWARE_PHPV);
        $settings->setValue('jlog_installed_mysqlv', JLOG_SOFTWARE_MYSQLV);
   
        // rewrite settings.inc.php
        return $settings->do_settings();
    }
}

// eof
