<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Initialization file
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @package    UniAdmin
*/

if( eregi(basename(__FILE__),$_SERVER['PHP_SELF']) )
{
	die("You can't access this file directly!");
}

clearstatcache();

// Be paranoid with passed vars
// Destroy GET/POST/Cookie variables from the global scope
if( intval(ini_get('register_globals')) != 0 )
{
	foreach( $_REQUEST AS $key => $val )
	{
		if( isset($$key) )
		{
			unset($$key);
		}
	}
}
// Unset old style global vars, we use only php 4.3+ style globals
unset($HTTP_GET_VARS,$HTTP_POST_VARS,$HTTP_COOKIE_VARS);


// Disable magic quotes and add slashes to global arrays
// Checking for function existance for php6
if( function_exists('set_magic_quotes_runtime') )
{
	set_magic_quotes_runtime(0);
}

if( function_exists('get_magic_quotes_gpc') )
{
	if( !get_magic_quotes_gpc() )
	{
		$_GET = slash_global_data($_GET);
		$_POST = slash_global_data($_POST);
		$_COOKIE = slash_global_data($_COOKIE);
		$_REQUEST = slash_global_data($_REQUEST);
	}
}
else
{
	$_GET = slash_global_data($_GET);
	$_POST = slash_global_data($_POST);
	$_COOKIE = slash_global_data($_COOKIE);
	$_REQUEST = slash_global_data($_REQUEST);
}



define('CAN_INI_SET',!ereg('ini_set', ini_get('disable_functions')));

$phpver = explode('.', phpversion());
$phpver = "$phpver[0]$phpver[1]";
define('PHPVERSION', $phpver);
unset($phpver);

if( PHPVERSION < 43 )
{
	die('You must have at least PHP version 4.3 and higher to run UniAdmin');
}

if( !defined('DIR_SEP') )
{
	define('DIR_SEP',DIRECTORY_SEPARATOR);
}

define('UA_BASEDIR',dirname(__FILE__) . DIR_SEP);


if( file_exists(UA_BASEDIR . 'config.php') )
{
	include( UA_BASEDIR . 'config.php' );
}


if( !defined('UA_INSTALLED') )
{
	define( 'IN_UNIADMIN',true );
	include(UA_BASEDIR . 'include' . DIR_SEP . 'constants.php');
    require(UA_MODULEDIR . 'install.php');
    die();
}

define('IN_UNIADMIN',true);

// Start our session
if( session_id() == '' )
{
	session_start();
}


include(UA_BASEDIR . 'include' . DIR_SEP . 'constants.php');

include(UA_INCLUDEDIR . 'uadebug.php');

include(UA_INCLUDEDIR . 'dbal.php');
include(UA_INCLUDEDIR . 'uniadmin.php');
include(UA_INCLUDEDIR . 'user.php');
include(UA_INCLUDEDIR . 'template.php');


$tpl = new Template;
$uniadmin = new UniAdmin();
$user = new User();


include(UA_INCLUDEDIR . 'login.php');


// Check to run upgrader
if( version_compare($uniadmin->config['UAVer'], UA_VER,'<') )
{
	if( $user->data['level'] == UA_ID_ADMIN )
	{
		require(UA_MODULEDIR . 'upgrade.php');
		die();
	}
	else
	{
		ua_die($user->lang['error_upgrade_needed']);
	}
}


// ----[ Check for latest UniAdmin Version ]------------------
if( $user->data['level'] == UA_ID_ADMIN && $uniadmin->config['check_updates'] && isset($uniadmin->config['versioncache']) )
{
	$cache = unserialize($uniadmin->config['versioncache']);

	if( $uniadmin->config['versioncache'] == '' )
	{
		$cache['timestamp'] = 0;
		$cache['ver_latest'] = '';
		$cache['ver_info'] = '';
		$cache['ver_date'] = '';
	}

	if( ($cache['timestamp'] + (60 * 60 * $uniadmin->config['check_updates'])) <= time() )
	{
		$cache['timestamp'] = time();

		$content = $uniadmin->get_remote_contents(UA_UPDATECHECK);

		if( preg_match('#<version>(.+)</version>#i',$content,$version) )
		{
			$cache['ver_latest'] = $version[1];
		}

		if( preg_match('#<info>(.+)</info>#i',$content,$info) )
		{
			$cache['ver_info'] = $info[1];
		}

		if( preg_match('#<updated>(.+)</updated>#i',$content,$info) )
		{
			$cache['ver_date'] = $info[1];
		}

		$db->query ( "UPDATE `" . $db->table('config') . "` SET `config_value` = '" . serialize($cache) . "' WHERE `config_name` = 'versioncache' LIMIT 1;");

	}

	if( version_compare($cache['ver_latest'],UA_VER,'>') )
	{
		$cache['ver_date'] = date($user->lang['time_format'], $cache['ver_date']);
		$uniadmin->error(sprintf($user->lang['new_version_available'],$cache['ver_latest'],$cache['ver_date'],UA_DOWNLOAD) . '<br /><br />' . $cache['ver_info']);
	}
}



/**
* Applies addslashes() to the provided data
*
* @param $data Array of data or a single string
* @return mixed Array or string of data
*/
function slash_global_data( $data )
{
	if( is_array($data) )
	{
		foreach( $data as $k => $v )
		{
			$data[$k] = ( is_array($v) ) ? slash_global_data($v) : addslashes($v);
		}
	}
	return $data;
}

function ua_microtime( )
{
	list($usec, $sec) = explode(' ', microtime());
	return ($usec + $sec);
}
