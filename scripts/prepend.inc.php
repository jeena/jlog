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
 * $HeadURL: http://jeenaparadies.net/svn/jlog/trunk/scripts/prepend.inc.php $
 * $Rev: 1739 $
 * $Author: driehle $
 * $Date: 2008-09-03 15:53:30 +0200 (Mi, 03. Sep 2008) $
 */

// load settings and version information
error_reporting(E_ALL ^ E_NOTICE);
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."personal".DIRECTORY_SEPARATOR."settings.inc.php");
require_once(JLOG_BASEPATH."scripts".DIRECTORY_SEPARATOR."version.inc.php");
 
// these two constants did not exist in Jlog 1.0.2
if (!defined('JLOG_INSTALLED_VERSION')) {
    define('JLOG_INSTALLED_VERSION', '1.0.2');
}
if (!defined('JLOG_LANGUAGE')) {
    define('JLOG_LANGUAGE', 'de');
}

// redirect to update-script if new jlog version was installed
if(version_compare(JLOG_INSTALLED_VERSION, JLOG_SOFTWARE_VERSION, '<')
	AND substr($_SERVER['SCRIPT_FILENAME'], -17) !== '/admin/update.php')
{
	header('Location: ' . JLOG_PATH . '/admin/update.php');
	exit;
}

// define constants for names of tables in database
define("JLOG_DB_CONTENT", JLOG_DB_PREFIX."content");
define("JLOG_DB_COMMENTS", JLOG_DB_PREFIX."comments");
define("JLOG_DB_CATASSIGN", JLOG_DB_PREFIX."catassign");
define("JLOG_DB_CATEGORIES", JLOG_DB_PREFIX."categories");
define("JLOG_DB_ATTRIBUTES", JLOG_DB_PREFIX."attributes");

if (!function_exists('get_magic_quotes_gpc')) {
	function get_magic_quotes_gpc() { return false; }
}

// we need these files on every page
require_once(JLOG_BASEPATH.'lang'.DIRECTORY_SEPARATOR.'lang.'.JLOG_LANGUAGE.'.inc.php');
require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'database.class.php');
require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'bbcode.php');
require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'general.func.php'); 
require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'comments.php');

// additionaly, when in admin-mode, we need this file
if(defined('JLOG_ADMIN')) require_once(JLOG_BASEPATH.'lang'.DIRECTORY_SEPARATOR.'lang-admin.'.JLOG_LANGUAGE.'.inc.php');

// connect to database
$connect = new mysqli(JLOG_DB_URL, JLOG_DB_USER, JLOG_DB_PWD, JLOG_DB);
if ($connect == FALSE) {
  mail(JLOG_EMAIL, $l['admin']['e_db'], $l['admin']['e_db_is']."\n".mysql_error());
  die("<strong>".$l['db_error']."</strong><br />".$l['plz_try_again'].".");
}
// select our database
#$select = @mysql_select_db(JLOG_DB);
if ($connect == FALSE) {
  mail(JLOG_EMAIL, $l['admin']['e_db'], $l['admin']['e_db_is']."\n".mysql_error());
  die("<strong>".$l['db_error']."</strong><br />".$l['plz_try_again'].".");
}
// do some settings
$connect->set_charset('utf8');
$connect->query("SET NAMES utf8");
$connect->query("SET sql_mode=''");

// some more code that needs to run for every page - however, this
// code requires an established connection to the database
setlocale(LC_TIME, $l['locale']);
require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'categories.class.php');
require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'jlogPlugins.class.php');
$plugins = new JlogPluginManager(JLOG_BASEPATH.'plugins'.DIRECTORY_SEPARATOR);

// call hooks for bbcode plugins
$bbcode = $plugins->callHook('bbcode', $bbcode);
$bbcomments = $plugins->callHook('bbcomments', $bbcomments);
   
// eof
