<?php

class JlogUpdate_111To112
{
    function getForm($l) 
    {
        return '<p>Dieses Script behebt ein paar fehlerhafte Einstellungen.'
               .' Es ist keine Konfiguration notwendig.</p>';
    }
    
    function performUpdate($l, $settings)
    {
        // in jlog versions prior to jlog 1.1.2 we had escaping problems that caused
        // a lot of backslashes in front of a double quote
        // so we have to replace \" or \\" or \\\" and so on by ".
        $data = array(
            'jlog_description' => $settings->getValue('jlog_description'),
            'jlog_website' => $settings->getValue('jlog_website'),
            'jlog_publisher' => $settings->getValue('jlog_publisher')
        );
        foreach ($data as $key => $value) {
            $value = preg_replace('=\\\\+"=', '"', $value);
            $settings->setValue($key, $value);
        }
        
        return true;
    }
}