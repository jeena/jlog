<?php

/**
 * This is an example file, how update classes have to look like.
 * 
 * Since we want to support PHP 4, unfortunately we can't use
 * all the new OOP functions PHP 5 offers. In future this file may
 * be a abstract class that all update classes have to extend.
 */
class JlogUpdate_Example 
{
    /**
     * You can do anything you want here
     */
    function __construct()
    {
        
    }
    
    /**
     * This function should prepare parts of the form for the update
     * 
     * It gets the language array as the first parameter and
     * must return html for the update form.
     */
    function getForm($l) 
    {
        return '<p>Do not need anything to configure for this update.</p>';
    }
    
    /**
     * This function has to perform the update
     *
     * Must return true, if everything went well, of an array of error
     * messages, if something went wrong.
     * The first parameter again in the language array.
     */
    function performUpdate($l) 
    {
        return true;
    }
}