<?php

class JlogUpdate_112To113
{
    function getForm($l) 
    {
        return '<p>Bitte beachten Sie, dass nach Durchführung dieses Updates eventuell einzelne Plugins nicht mehr funktionieren.<br />'
             . 'Kontaktieren Sie in einem solchen Fall den Plugin-Autor bzzgl. eines Updates für das Plugin.</p>';
    }
    
    function performUpdate($l, $settings)
    {
        return true;
    }
}