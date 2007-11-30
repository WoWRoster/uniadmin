<?php
/**
 * WoWRoster.net UniAdmin
 *
 * WoWAce Module
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @package    UniAdmin
*/

if( !defined('IN_UNIADMIN') )
{
    exit('Detected invalid access to this file!');
}

if( $user->data['level'] == UA_ID_ANON )
{
	ua_die($user->lang['access_denied']);
}

include(UA_INCLUDEDIR . 'addon_lib.php');
require_once(UA_INCLUDEDIR . 'minixml.inc.php');
require_once(UA_INCLUDEDIR . 'xmlparser.class.php');


$ace_error = false;
$ace_file = UA_CACHEDIR . 'latest.xml';
$ace_url = 'http://files.wowace.com/latest.xml';



//ace_update_all();die();


/**
 * WoWAce Addon Page Functions
 */

if( isset($_POST[UA_URI_OP]) )
{
	switch( $_POST[UA_URI_OP] )
	{
		case UA_URI_PROCESS:
			process_wowace_addons();
			break;

		case UA_URI_RELOAD:
			if( file_exists($ace_file) )
			{
				$try_unlink = unlink($ace_file);
				if( !$try_unlink )
				{
					$uniadmin->error(sprintf($user->lang['error_unlink'],$ace_file));
				}
			}
			break;
	}
}

// Assign template vars
$tpl->assign_vars(array(
	'L_WOWACE_ADDONS'   => $user->lang['get_wowace_addons'],
	'L_NOLIST'          => $user->lang['error_no_wowace_addons'],
	'L_NAME'            => $user->lang['name'],
	'L_DOWNLOAD'        => $user->lang['download'],
	'L_GO'              => $user->lang['go'],
	'L_NOTES'           => $user->lang['notes'],
	'L_VERSION'         => $user->lang['version'],
	'L_DATETIME'        => $user->lang['date_time'],
	'L_LASTUPDATED'     => $user->lang['last_updated'],
	'L_FORCERELOAD'     => $user->lang['force_reload'],
	'L_FORCERELOAD_TIP' => $user->lang['wowace_reload']
	)
);

$filelist = '';
ace_get_filelist($filelist);

if( !empty($filelist) )
{
	$tpl->assign_var('S_ACELIST', true);
	$tpl->assign_var('ONLOAD'," onload=\"initARC('ua_wowace','radioOn', 'radioOff','checkboxOn', 'checkboxOff');\"");

	$waaddons = array();
	ace_parselist($waaddons, $filelist);
	
	$id = 0;
	foreach( $waaddons as $addon => $data )
	{
		$data['description'] = preg_replace('/\|c[a-f0-9]{2}([a-f0-9]{6})(.+?)\|r/i','<span style="color:#$1;">$2</span>',htmlentities($data['description']));
		// Assign template vars
		$tpl->assign_block_vars('addons_row', array(
			'ROW_CLASS'   => $uniadmin->switch_row_class(),
			'ID'          => 'addon_' . $id,
			'NAME'        => $addon,
			'DESC'        => $data['description'],
			'VERSION'     => $data['version'],
			'TIMESTAMP'   => date($user->lang['time_format'],$data['datetime'])
			)
		);
		$_SESSION['addon_' . $id] = $addon;
		$_SESSION['addon_' . $id . '_url'] = $data['url'];
		$id++;
	}
}
else
{
	$tpl->assign_var('S_ACELIST', false);
}

$uniadmin->set_vars(array(
	'page_title'    => $user->lang['title_wowace'],
	'template_file' => 'wowace.html',
	'display'       => true
	)
);


function ace_parselist(&$waaddons, &$waaddons_unparsed){
	global $ace_file;
	$waaddons = array();
	if( function_exists('xml_parse') )
	{
		$xmlParser =& new XmlParser();
		
		if (!empty($waaddons_unparsed))
			$xmlParser->Parse($waaddons_unparsed);
		else
			$xmlParser->Parse(file_get_contents($ace_file));

		$items = $xmlParser->data['rss'][0]['child']['channel'][0]['child']['item'];

		foreach( $items as $item )
		{
			$title = $item['child']['title'][0]['data'];
			$description = ( isset($item['child']['description'][0]['data']) ? $item['child']['description'][0]['data'] : $title );
			$version = $item['child']['wowaddon:version'][0]['data'];
			$datetime = $item['child']['pubDate'][0]['data'];

			$waaddons[$title]['description'] = $description;
			$waaddons[$title]['version'] = $version;
			$waaddons[$title]['datetime'] = strtotime($datetime);

			$url = $item['child']['enclosure'][0]['attribs']['url'];
			$waaddons[$title]['url'] = ( !empty($url) ? str_replace('http://www.wowace.com/files', 'http://files.wowace.com', $url) : 'http://files.wowace.com/' . $title . '/' . $title . '.zip' );
		}
	}
	else
	{
		ua_die('php XML parsing functions are required for the WoWAce module');
	}
	uksort($waaddons, 'strnatcasecmp');
	//should really move all keys starting with a non alpha to the BOTTOM mwahahahah
	//print_r($waaddons);die();
}


function ace_update_all(){
	// could build a proxy tpl where user can see which are outdated and manually update single ones
	// dont know how fast this chain of functions is/could be
	// ace_checkforold_all forces a new xml download, so watch out
	$db_ace_addons = ace_checkforold_all();
	foreach($db_ace_addons as $key => $value)
	{
		if ($value['old'])
			ace_update_single($key, $value['url']);
	}
}
function ace_update_single($ace_name,$url){
	//download/process it
}

function ace_checkforold_all(){
	global $db,$ace_file;
	$waaddons = array();
	$waaddons_unparsed = '';
	ace_get_filelist($waaddons_unparsed,true);
	ace_parselist($waaddons, $waaddons_unparsed);
	$sql = 'SELECT * FROM `' . UA_TABLE_ADDONS . '`  WHERE not (`ace_title` = \'\') ORDER BY `name` ASC;';
	$result = $db->query($sql);
	$addons = array();
	if( $db->num_rows($result) > 0 )
	{
		while( $row = $db->fetch_record($result) )
		{
			$addons[$row['ace_title']]=$row;
			if (ace_title_in_list($row['ace_title'],$waaddons)){
				$addons[$row['ace_title']]['url'] = ace_geturl($row['ace_title'],$waaddons);
				if (ace_checkforold_single($row,$waaddons)){
					$addons[$row['ace_title']]['old'] = true;
				}
				else
				{
					$addons[$row['ace_title']]['old'] = false;
				}
			}
		}
	}
	return $addons;
}

function ace_title_in_list($ace_title, &$waaddons)
{
	return array_key_exists($ace_title, $waaddons);
}
function ace_checkforold_single($ace_dbrow, &$waaddons)
{
	//server time may affect this comparison , perhaps another database.ua.addons field is in order
	//to be honest maybe all of the ace stuff should be in the addons table to make it easier
	if ((int)$ace_dbrow['time_uploaded'] > (int)$waaddons[$ace_dbrow['ace_title']]['datetime'])
		return false;
	else 
		return true;
}
function ace_geturl($ace_title, &$waaddons)
{
	return $waaddons[$ace_title]['url'];
}
function ace_get_filelist(&$filelist,$force = false){
	global $tpl, $uniadmin, $user, $ace_url, $ace_file;
	
	if ($force){
		$try_unlink = unlink($ace_file);
		if( !$try_unlink )
		{
			$uniadmin->error($user->lang['error_delete_ace_list']);
			$uniadmin->error(sprintf($user->lang['error_unlink'],$ace_file));
		}
	}
	if( !file_exists($ace_file) )
	{
		$filelist = $uniadmin->get_remote_contents($ace_url);
		$uniadmin->message($user->lang['new_wowace_list']);
		$uniadmin->write_file($ace_file,$filelist);
		clearstatcache();
		$file_info = stat($ace_file);
		$tpl->assign_var('WOWACE_UPDATED',date($user->lang['time_format'],$file_info['9']) );
		return true;
	}
	else
	{
		clearstatcache();
		$file_info = stat($ace_file);
		if( ($file_info['9'] + (60 * 60 * $uniadmin->config['remote_timeout'])) <= time() )
		{
			// Download List
			$filelist = $uniadmin->get_remote_contents($ace_url);
			$uniadmin->message($user->lang['new_wowace_list']);

			$uniadmin->write_file($ace_file,$filelist);
			clearstatcache();
			$file_info = stat($ace_file);
			$tpl->assign_var('WOWACE_UPDATED',date($user->lang['time_format'],$file_info['9']) );
			return true;
		}
		else
		{
			// Keep the old file
			$filelist = file_get_contents($ace_file);
			$tpl->assign_var('WOWACE_UPDATED',date($user->lang['time_format'],$file_info['9']) );
			return true;
		}
	}
	return false;
}
function process_wowace_addons( )
{
	global $uniadmin, $user;

	foreach( $_POST as $addon => $dl )
	{
		if( $dl == 'on' )
		{
			$download[] = $addon;
		}
	}

	foreach( $download as $key => $addon )
	{
		$url = $_SESSION[$addon . '_url'];
		$addon = $_SESSION[$addon];

		$addoncon = $uniadmin->get_remote_contents($url);
		$filename = UA_BASEDIR . $uniadmin->config['addon_folder'] . DIR_SEP . "$addon.zip";

		$write_temp_file = $uniadmin->write_file($filename,$addoncon,'w+');

		if( $write_temp_file === false )
		{
			$uniadmin->error(sprintf($user->lang['error_write_file'],str_replace('\\','/',$filename)));
		}
		else
		{
			$toPass = array();
			$toPass['name'] = $addon . '.zip';
			$toPass['type'] = 'application/zip';
			$toPass['tmp_name'] = $filename;

			if( is_readable($toPass['tmp_name']) )
			{
				$toPass['error'] = 0;
			}
			else
			{
				$toPass['error'] = 1;
			}
			$toPass['size'] = filesize($toPass['tmp_name']);
			process_addon($toPass);
		}
	}
}
