<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Interface Module
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @package    UniAdmin
 */

if( !defined('IN_UNIADMIN') )
{
    exit('Detected invalid access to this file!');
}

// Get Operation
$op = ( isset($_REQUEST['OPERATION']) ? $_REQUEST['OPERATION'] : 'VIEW' );

// Determine what version of UU is accessing this
$uu_patterns = $juu_patterns = array();

preg_match('|uniuploader(.+)\\(uu ([0-9].[0-9].[0-9])|i',$user->user_agent,$uu_patterns);
preg_match('|\(juu ([a-z]-[0-9]{2})|i',$user->user_agent,$juu_patterns);

if(
	( isset($uu_patterns[2]) && version_compare($uu_patterns[2],'2.5.0','<') ) ||
	( isset($juu_patterns[1]) && version_compare($juu_patterns[1],'11','<') )
	)
{
	define('UU_COMPAT', true);
}
else
{
	define('UU_COMPAT', false);
}

// Include xml builder
require(UA_INCLUDEDIR . 'minixml.inc.php');


// Decide What To Do
switch( $op )
{
	case 'GETSETTINGS':
		update_stats($op);
		echo output_settings();
		break;

	case 'GETSETTINGSXML':
		update_stats($op);
		echo output_settings_xml();
		break;

	case 'GETADDONLIST':
		update_stats($op);
		echo output_addon_xml();
		break;

	case 'GETDELETEADDONS':
		update_stats($op);
		echo output_addondel_xml();
		break;

	case 'GETADDON':
		update_stats($op);
		echo output_addon_url($_REQUEST['ADDON']);
		break;

	case 'GETUAVER':
		echo $uniadmin->config['UAVer'];
		break;

	case 'GETFILEMD5':
		echo output_logo_md5($_REQUEST['FILENAME']);
		break;

	default:
		update_stats($op);
		echo $user->lang['interface_ready'];
		break;
}






/**
 * Interface Page Functions
 */



/**
 * Adds viewer's stats for the UniAdmin stats page
 */
function update_stats( $op )
{
	global $db, $user;

	$action = ( isset($_REQUEST['ADDON']) ? $op . ' - ' . $_REQUEST['ADDON'] : $op );

	$sql = "INSERT INTO `" . $db->table('stats') . "` ( `ip_addr` , `host_name` , `action` , `time` , `user_agent` ) VALUES
		( '" . $db->escape($user->ip_address) . "', '" . $db->escape($user->remote_host) . "', '" . $db->escape($action) . "', '" . time() . "', '" . $db->escape($user->user_agent) . "' );";
	$db->query($sql);
}

/**
 * Echo's all of UniAdmin's settings for UniUploader
 */
function output_settings( )
{
	global $db, $uniadmin;

	// Set delimiters correctly if UU_COMPAT is true
	if( UU_COMPAT )
	{
		$eq_sep = '=';
		$pipe_sep = '|';
	}
	else
	{
		$eq_sep = '[=]';
		$pipe_sep = '[|]';
	}

	$output_string = '';

	// logos
	$sql = "SELECT * FROM `" . $db->table('logos') . "` WHERE `active` = '1' ORDER BY `logo_num` ASC;";
	$result = $db->query($sql);
	if( $db->num_rows($result) > 0 )
	{
		while( $row = $db->fetch_record($result) )
		{
			$output_string .= 'LOGO' . $row['logo_num'] . $eq_sep . $uniadmin->url_path . $uniadmin->config['logo_folder'] . '/' . $row['filename'] . $pipe_sep;
		}
	}
	$db->free_result($result);

	// settings
	$sql = "SELECT * FROM `" . $db->table('settings') . "` WHERE `enabled` = '1' ORDER BY `set_name` ASC;";
	$result = $db->query($sql);
	if( $db->num_rows($result) > 0 )
	{
		while( $row = $db->fetch_record($result) )
		{
			$output_string .= $row['set_name'] . $eq_sep . $row['set_value'] . $pipe_sep;
		}
	}
	$db->free_result($result);

	// sv list
	$sql = "SELECT * FROM `" . $db->table('svlist') . "` ORDER BY `sv_name` ASC;";
	$result = $db->query($sql);
	if( $db->num_rows($result) > 0 )
	{
		$output_string .= 'SVLIST' . $eq_sep;
		while( $row = $db->fetch_record($result) )
		{
			$output_string .= $row['sv_name'] . $pipe_sep;
		}
	}
	$db->free_result($result);

	return $output_string;
}

/**
 * Echo's all of UniAdmin's settings for UniUploader
 * This output the settings in xml format
 */
function output_settings_xml( )
{
	global $db, $uniadmin;

	$xmlDoc = new MiniXMLDoc();
	$xmlRoot =& $xmlDoc->getRoot();

	$uaElement =& $xmlRoot->createChild('uasettings');


	// logos
	$sql = "SELECT * FROM `" . $db->table('logos') . "` WHERE `active` = '1' ORDER BY `logo_num` ASC;";
	$result = $db->query($sql);
	if( $db->num_rows($result) > 0 )
	{
		$logosElement =& $uaElement->createChild('logos');

		while( $row = $db->fetch_record($result) )
		{
			$childElement =& $logosElement->createChild('logo');
			$childElement->attribute('id', $row['logo_num']);
			$childElement->text($uniadmin->url_path . $uniadmin->config['logo_folder'] . '/' . $row['filename']);
		}
	}
	$db->free_result($result);


	// settings
	$sql = "SELECT * FROM `" . $db->table('settings') . "` WHERE `enabled` = '1' ORDER BY `set_name` ASC;";
	$result = $db->query($sql);
	if( $db->num_rows($result) > 0 )
	{
		$settingsElement =& $uaElement->createChild('settings');

		while( $row = $db->fetch_record($result) )
		{
			$childElement =& $settingsElement->createChild($row['set_name']);
			$childElement->text($row['set_value']);
		}
	}
	$db->free_result($result);


	// sv list
	$sql = "SELECT * FROM `" . $db->table('svlist') . "` ORDER BY `sv_name` ASC;";
	$result = $db->query($sql);
	if( $db->num_rows($result) > 0 )
	{
		$svlistElement =& $uaElement->createChild('svlist');

		while( $row = $db->fetch_record($result) )
		{
			$childElement =& $svlistElement->createChild('savedvariable');
			$childElement->text($row['sv_name']);
		}
	}
	$db->free_result($result);

	$output = $xmlDoc->toString();

	header('Content-Type: text/xml');
	header('Content-Length: ' . strlen($output));
	return $output;
}

/**
 * Echos XML for the addons UniAdmin provides
 */
function output_addon_xml( )
{
	global $db, $uniadmin;

	// Don't get optional addons if UU_COMPAT is true
	if( UU_COMPAT )
	{
		$sql = "SELECT * FROM `" . $db->table('addons') . "` WHERE `enabled` = '1' AND `required` = '1' ORDER BY `name` ASC;";
	}
	else
	{
		$sql = "SELECT * FROM `" . $db->table('addons') . "` WHERE `enabled` = '1' ORDER BY `required` DESC, `name` ASC;";
	}
	$result = $db->query($sql);


	$xmlDoc = new MiniXMLDoc();
	$xmlRoot =& $xmlDoc->getRoot();
	$addonsElement =& $xmlRoot->createChild('addons');

	if( $db->num_rows($result) > 0 )
	{
		while( $row = $db->fetch_record($result) )
		{
			$addonElement =& $addonsElement->createChild('addon');

			$addonElement->attribute('name', htmlspecialchars($row['name']));
			$addonElement->attribute('version', htmlspecialchars($row['version']));
			$addonElement->attribute('required', $row['required']);
			$addonElement->attribute('requiredoff', $row['requiredoff']);
			$addonElement->attribute('homepage', htmlspecialchars($row['homepage']));
			$addonElement->attribute('filename', $uniadmin->url_path . $uniadmin->config['addon_folder'] . '/' . $row['file_name']);
			$addonElement->attribute('toc', $row['toc']);
			$addonElement->attribute('full_path', $row['full_path']);
			$addonElement->attribute('notes', htmlspecialchars($row['notes']));

			$sql = "SELECT * FROM `" . $db->table('files') . "` WHERE `addon_id` = '" . $row['id'] . "';";
			$result2 = $db->query($sql);

			if( $db->num_rows($result2) > 0 )
			{
				while( $row2 = $db->fetch_record($result2) )
				{
					$childElement =& $addonElement->createChild('file');

					$childElement->attribute('name', $row2['filename']);
					$childElement->attribute('md5sum', $row2['md5sum']);
				}
			}
			$db->free_result($result2);
		}
		$db->free_result($result);
	}

	$output = $xmlDoc->toString();
	header('Content-Type: text/xml');
	header('Content-Length: ' . strlen($output));
	return $output;
}

/**
 * Echos an addon's download URL
 */
function output_addon_url( $addonName )
{
	global $db, $uniadmin;

	$sql = "SELECT `name`, `file_name` FROM `" . $db->table('addons') . "` WHERE `name` LIKE '" . $db->escape($addonName) . "';";
	$result = $db->query($sql);

	if( $db->num_rows($result) > 0 )
	{
		$row = $db->fetch_record($result);

		$download = $uniadmin->url_path . $uniadmin->config['addon_folder'] . '/' . $row['file_name'];

		$db->free_result($result);
		return $download;
	}
	else
	{
		return '';
	}
}

/**
 * Echos a logo's md5 hash
 *
 * @param string $filename
 */
function output_logo_md5( $filename )
{
	global $db;

	$sql = "SELECT * FROM `" . $db->table('logos') . "` WHERE `filename` = '" . $db->escape($filename) . "';";
	$result = $db->query($sql);

	if( $db->num_rows($result) > 0 )
	{
		$row = $db->fetch_record($result);

		$db->free_result($result);
		return $row['md5'];
	}
	else
	{
		return '';
	}
}


/**
 * Echos XML UniAdmin delete addons list
 */
function output_addondel_xml( )
{
	global $db, $uniadmin;

	// Don't get optional addons if UU_COMPAT is true
	$sql = "SELECT * FROM `" . $db->table('addondel') . "`;";
	$result = $db->query($sql);

	$xmlDoc = new MiniXMLDoc();
	$xmlRoot =& $xmlDoc->getRoot();
	$addonsElement =& $xmlRoot->createChild('addons');

	if( $db->num_rows($result) > 0 )
	{
		while( $row = $db->fetch_record($result) )
		{
			$addonElement =& $addonsElement->createChild('addon');

			$addonElement->attribute('dirname', htmlspecialchars($row['dir_name']));
		}
		$db->free_result($result);
	}
	else
	{
		$db->free_result($result);
		return;
	}

	$output = $xmlDoc->toString();
	header('Content-Type: text/xml');
	header('Content-Length: ' . strlen($output) );
	return $output;
}
