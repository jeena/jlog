<?php

class JlogUpdate_102To110
{
    var $languages = array();
    
    function JlogUpdate_102To110()
    {
        $dir = opendir(JLOG_BASEPATH.'lang');
        while(($file = readdir($dir)) !== false) {
          if($file == '.' OR $file == '..') continue;
          if(!preg_match('/lang\.([a-zA-z0-9]+)\.inc\.php/', $file, $matches)) continue;
          $this->languages[] = $matches[1];
        }
    }
    
    function getForm($l) 
    {
        $html = "<p><label for='language'>Bitte wählen Sie die gewünschte Sprache für Ihr Weblog:</label><br />
                 <select class='userdata' id='language' name='j110_language'>"; 
        foreach($this->languages as $lang) {
            $html .= "<option>$lang</option>";
        }
        $html .= "</select>
                </p>
                <p>Die Zeichenkodierung ihrer Template-Datei <code>personal/template.tpl</code> muss nach UTF-8 umgewandelt werden. Wenn diese Datei
                  beschreibbar ist (z.B.: chmod 777), wird dies vom Updatescript automatisch für sie erledigt.
                  Andernfalls müssen Sie die Konvertierung nachträglich manuell vornehmen.</p>
                ";
        return $html;
    }
    
    function performUpdate($l, $settings)
    {
        // convert all settings to utf8
        foreach($settings->d as $key => $value) {
          $settings->d[$key] = utf8_encode($value);
        }
   
        // reset hash of the administrator password
        $settings->d['jlog_admin_password'] = md5($_POST['password']);
   
        // store chosen language
        $lang = in_array($_POST['j110_language'], $this->languages) ? $_POST['j110_language'] : 'de';
        $settings->d['jlog_language'] = $lang;
   
        $update_errors = array();
         
        /**
         * On a correct Jlog 1.0.2 installation, the template is saved with an ISO
         * encoding, so we're going to try to convert this to UTF-8
         */
        $template = JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."template.tpl";
        if(@file_put_contents($template, utf8_encode(@file_get_contents($template))) == false) {
            $update_errors[] = 'Die Datei <code>personal/template.tpl</code> konnte nicht in UTF-8 Kodierung konvertiert werden.';
        }

   
        if(empty($update_errors)) {
            return true;
        }
        else {
            return $update_errors;
        }
    }
}