<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Addon Module
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

include(UA_INCLUDEDIR . 'addon_lib.php');

// Get Operation
$op = ( isset($_POST[UA_URI_OP]) ? $_POST[UA_URI_OP] : '' );

$id = ( isset($_POST[UA_URI_ID]) ? $_POST[UA_URI_ID] : '' );

$detail = ( isset($_POST[UA_URI_DETAIL]) ? $_POST[UA_URI_DETAIL] : ( isset($_GET[UA_URI_DETAIL]) ? $_GET[UA_URI_DETAIL] : '' ) );

// Decide What To Do
switch( $op )
{
	case UA_URI_PROCESS:
		if( $user->data['level'] >= UA_ID_ADMIN )
			process_addon($_FILES['file']);
		break;

	case UA_URI_DELETE:
		if( $user->data['level'] >= UA_ID_ADMIN )
			delete_addon($id);
		break;

	case UA_URI_DELETE_ALL:
		if( $user->data['level'] >= UA_ID_ADMIN )
			delete_all_addons();
		break;

	case UA_URI_REQ:
	case UA_URI_OPT:
		if( $user->data['level'] >= UA_ID_POWER )
			toggle_addon($op,$id);
		break;

	case UA_URI_REQOFF:
	case UA_URI_OPTOFF:
		if( $user->data['level'] >= UA_ID_POWER )
			toggle_addon($op,$id);
		break;

	case UA_URI_DISABLE:
	case UA_URI_ENABLE:
		if( $user->data['level'] >= UA_ID_POWER )
			toggle_addon($op,$id);
		break;

	case UA_URI_EDIT:
		if( $user->data['level'] >= UA_ID_POWER )
			edit_addon($id);
		break;

	case UA_URI_ORPHAN:
		if( $user->data['level'] >= UA_ID_ADMIN )
			process_orphan_addons();
		break;

	case UA_URI_ADDONDEL_ADD:
		if( $user->data['level'] >= UA_ID_ADMIN )
			add_addon_del($_POST[UA_URI_ADDONDEL_NAME]);
		break;

	case UA_URI_ADDONDEL_REM:
		if( $user->data['level'] >= UA_ID_ADMIN )
			remove_addon_del($id);
		break;

	default:
		break;
}

if( $detail != '' )
{
	addon_detail($detail);
}
else
{
	main();
}








/**
 * Addon Page Functions
 */


/**
 * Main Display
 */
function main( )
{
	global $db, $uniadmin, $user, $tpl;

	// Assign template vars
	$tpl->assign_vars(array(
		'L_ADDON_MANAGE'   => $user->lang['addon_management'],
		'L_NAME'           => $user->lang['name'],
		'L_TOC'            => $user->lang['toc'],
		'L_REQUIRED'       => $user->lang['required'],
		'L_OPTIONAL'       => $user->lang['optional'],
		'L_REQUIREDOFF'    => $user->lang['requiredoff'],
		'L_OPTIONALOFF'    => $user->lang['optionaloff'],
		'L_VERSION'        => $user->lang['version'],
		'L_UPLOADED'       => $user->lang['uploaded'],
		'L_ENABLED'        => $user->lang['enabled'],
		'L_DISABLED'       => $user->lang['disabled'],
		'L_DELETE'         => $user->lang['delete'],
		'L_DELETE_ALL'     => $user->lang['delete_all_addons'],
		'L_DISABLE_ENABLE' => $user->lang['disable_enable'],
		'L_SELECT_FILE'    => $user->lang['select_file'],
		'L_DOWNLOAD'       => $user->lang['download'],
		'L_UPDATE_ALL'     => $user->lang['update_all'],
		'L_ADD_UPDATE'     => $user->lang['add_update_addon'],
		'L_ADD'            => $user->lang['add'],
		'L_REQUIRED_ADDON' => $user->lang['required_addon'],
		'L_REQUIREDOFF_ADDON' => $user->lang['requiredoff_addon'],
		'L_SELECT_FILE'    => $user->lang['select_file'],
		'L_HOMEPAGE'       => $user->lang['homepage'],
		'L_GO'             => $user->lang['go'],
		'L_FULLPATH'       => $user->lang['fullpath_addon'],
		'L_AUTOMATIC'      => $user->lang['automatic'],
		'L_ADDON_DETAILS'  => $user->lang['addon_details'],
		'L_MANAGE'         => $user->lang['manage'],
		'L_YES'            => $user->lang['yes'],
		'L_NO'             => $user->lang['no'],
		'L_NOTES'          => $user->lang['notes'],
		'L_UNSCANNED'      => $user->lang['unscanned_addons'],

		'L_NO_ADDONS'      => $user->lang['error_no_addon_in_db'],
		'L_CONFIRM_DELETE' => $user->lang['confirm_addons_delete'],

		'L_REQUIRED_TIP'   => $user->lang['addon_required_tip'],
		'L_REQUIREDOFF_TIP'   => $user->lang['addon_requiredoff_tip'],
		'L_FULLPATH_TIP'   => $user->lang['addon_fullpath_tip'],
		'L_SELECTFILE_TIP' => $user->lang['addon_selectfile_tip'],

		'L_REMOVE'         => $user->lang['remove'],
		'L_ADDON'          => $user->lang['addon'],
		'L_ADDONDEL_CONT'  => $user->lang['addon_delete'],
		'L_ADD_ADDONDEL'   => $user->lang['addon_delete_add'],
		'L_NO_DEL_ADDONS'  => $user->lang['addon_delete_none'],

		'S_ADDONS'         => true,
		'SHOWOFF'          => false,
		'S_ADDON_ADD_DEL'  => false
		)
	);

	$resultoff = false;
	// Check admin
	if( $user->data['level'] == UA_ID_ADMIN )
	{
		$tpl->assign_var('S_ADDON_ADD_DEL',true);
		// Check if ENABLEOFFICERBUILD is actually enabled or not, if not we hide the officer stuff
		$sqloff = 'SELECT `set_value` FROM `' . $db->table('settings') . "` WHERE `set_name` = 'ENABLEOFFICERBUILD'";
		$resultoff = $db->query_first($sqloff);
		$tpl->assign_var('SHOWOFF',$resultoff);
		$tpl->assign_var('ONLOAD'," onload=\"initARC('ua_updateaddon','radioOn', 'radioOff','checkboxOn', 'checkboxOff'); initARC('ua_orphan_addon','radioOn', 'radioOff','checkboxOn', 'checkboxOff');\"");
	}

	// Set string to "View Addons" if user is anonymous
	if( $user->data['level'] == UA_ID_ANON )
	{
		$tpl->assign_var('L_ADDON_MANAGE',$user->lang['view_addons']);
	}

	$sql = 'SELECT * FROM `' . $db->table('addons') . '` ORDER BY `name` ASC;';
	$result = $db->query($sql);

	// Set not scanned addons array
	$addon_in_db = array();

	// Loop for every addon in database
	if( $db->num_rows($result) > 0 )
	{
		while( $row = $db->fetch_record($result) )
		{
			if( substr($row['file_name'], 0, 7) == 'http://' )
			{
				$download = $row['file_name'];
			}
			else
			{
				$download = $uniadmin->url_path . $uniadmin->config['addon_folder'] . '/' . $row['file_name'];
				// Add to not scanned addons array
				$addon_in_db[] = $row['file_name'];
			}

			if ($resultoff == true)
			{
				// Assign template vars (show everything)
				$tpl->assign_block_vars('addons_row', array(
					'ROW_CLASS'   => $uniadmin->switch_row_class(),
					'ID'          => $row['id'],
					'HOMEPAGE'    => $row['homepage'],
					'ADDONNAME'   => $row['name'],
					'TOC'         => $row['toc'],
					'REQUIRED'    => $row['required'],
					'REQUIREDOFF' => $row['requiredoff'],
					'VERSION'     => $row['version'],
					'TIME'        => date($user->lang['time_format'],$row['time_uploaded']),
					'ENABLED'     => $row['enabled'],
					'DOWNLOAD'    => $download,
					'FILESIZE'    => $uniadmin->filesize_readable($row['filesize']),
					'NOTE'        => addslashes(htmlentities($row['notes']))
					)
				);
			}
			else
			{
				// Assign template vars (don't show officer stuff)
				$tpl->assign_block_vars('addons_row', array(
					'ROW_CLASS'   => $uniadmin->switch_row_class(),
					'ID'          => $row['id'],
					'HOMEPAGE'    => $row['homepage'],
					'ADDONNAME'   => $row['name'],
					'TOC'         => $row['toc'],
					'REQUIRED'    => $row['required'],
					'VERSION'     => $row['version'],
					'TIME'        => date($user->lang['time_format'],$row['time_uploaded']),
					'ENABLED'     => $row['enabled'],
					'DOWNLOAD'    => $download,
					'FILESIZE'    => $uniadmin->filesize_readable($row['filesize']),
					'NOTE'        => addslashes(htmlentities($row['notes']))
					)
				);
			}
		}


	}
	else // Set var to display "No Addons"
	{
		$tpl->assign_var('S_ADDONS',false);
	}

	$tpl->assign_var('S_ADDON_DEL',false);

	// Build the addon delete list table
	$sql = "SELECT * FROM `" . $db->table('addondel') . "`;";
	$result = $db->query($sql);

	if( $db->num_rows($result) > 0 )
	{
		$tpl->assign_var('S_ADDON_DEL',true);

		while( $row = $db->fetch_record($result) )
		{
			$tpl->assign_block_vars('addondel_list', array(
				'ROW_CLASS' => $uniadmin->switch_row_class(),
				'ID'        => $row['id'],
				'NAME'      => $row['dir_name']
				)
			);
		}
	}

	$db->free_result($result);


	// Get a list of currently uploaded addons
	$uploaded_addons = $uniadmin->ls(UA_BASEDIR . $uniadmin->config['addon_folder'],array(),false);

	$addon_not_db = false;
	if( is_array($uploaded_addons) && count($uploaded_addons) > 0 )
	{
		foreach( $uploaded_addons as $addon_index => $addon )
		{
			$addon = basename($addon);
			if ( !in_array($addon,$addon_in_db) )
			{
				$addon_not_db = true;

				$tpl->assign_block_vars('orphan_addons_row', array(
					'ROW_CLASS'   => $uniadmin->switch_row_class(),
					'ID'          => "id_$addon_index",
					'NAME'        => $addon
					)
				);
				$_SESSION['id_' . $addon_index] = $addon;
			}
		}
	}
	$tpl->assign_var('S_ORPHAN_ADDONS',$addon_not_db);


	$uniadmin->set_vars(array(
		'page_title'    => $user->lang['title_addons'],
		'template_file' => 'addons.html',
		'display'       => true
		)
	);
}

function addon_detail( $addon_id )
{
	global $db, $uniadmin, $user, $tpl;

	// Assign template vars
	$tpl->assign_vars(array(
		'L_ADDON_DETAILS'  => $user->lang['addon_details'],
		'L_NAME'           => $user->lang['name'],
		'L_TOC'            => $user->lang['toc'],
		'L_REQUIRED'       => $user->lang['required'],
		'L_OPTIONAL'       => $user->lang['optional'],
		'L_REQUIREDOFF'    => $user->lang['requiredoff'],
		'L_OPTIONALOFF'    => $user->lang['optionaloff'],
		'L_VERSION'        => $user->lang['version'],
		'L_UPLOADED'       => $user->lang['uploaded'],
		'L_ENABLED'        => $user->lang['enabled'],
		'L_DISABLED'       => $user->lang['disabled'],
		'L_FILES'          => $user->lang['files'],
		'L_DELETE'         => $user->lang['delete'],
		'L_DISABLE_ENABLE' => $user->lang['disable_enable'],
		'L_SELECT_FILE'    => $user->lang['select_file'],
		'L_DOWNLOAD'       => $user->lang['download'],
		'L_ADD_UPDATE'     => $user->lang['add_update_addon'],
		'L_UPDATE'         => $user->lang['update_addon'],
		'L_REQUIRED_ADDON' => $user->lang['required_addon'],
		'L_REQUIREDOFF_ADDON' => $user->lang['requiredoff_addon'],
		'L_SELECT_FILE'    => $user->lang['select_file'],
		'L_HOMEPAGE'       => $user->lang['homepage'],
		'L_GO'             => $user->lang['go'],
		'L_MANAGE'         => $user->lang['manage'],
		'L_YES'            => $user->lang['yes'],
		'L_NO'             => $user->lang['no'],
		'L_NOTES'          => $user->lang['notes'],
		'L_EDIT'           => $user->lang['edit'],
		'L_CANCEL'         => $user->lang['cancel'],

		'S_ADDON_ADD_DEL'  => false,
		'SHOWOFF'          => false,
		'S_FILES'          => false,
		)
	);

	$resultoff = false;
	// Check admin
	if( $user->data['level'] == UA_ID_ADMIN )
	{
		$tpl->assign_var('S_ADDON_ADD_DEL',true);
		// Check if ENABLEOFFICERBUILD is actually enabled or not, if not we hide the officer stuff
		$sqloff = 'SELECT `set_value` FROM `' . $db->table('settings') . "` WHERE `set_name` = 'ENABLEOFFICERBUILD'";
		$resultoff = $db->query_first($sqloff);
		$tpl->assign_var('SHOWOFF',$resultoff);
	}

	// If anonymous, change to "View Addons"
	if( $user->data['level'] == UA_ID_ANON )
	{
		$tpl->assign_var('L_ADDON_MANAGE',$user->lang['view_addons']);
	}

	$sql = 'SELECT * FROM `' . $db->table('addons') . "` WHERE `id` = '$addon_id';";

	$result = $db->query($sql);

	$row = $db->fetch_record($result);

	// Get all files
	if( $db->num_rows($result) > 0 )
	{
		$sql = 'SELECT * FROM `' . $db->table('files') . "` WHERE `addon_id` = '$addon_id' ORDER BY `filename` ASC;";

		$result2 = $db->query($sql);

		$num_files = $db->num_rows($result2);

		// Loop and assign to tpl vars
		// Also generate a HTML list
		// Themes can decide whether to display file list, or a html <li> list
		if( $num_files > 0 )
		{
			$tpl->assign_var('S_FILES', true);

			$addonsArray = array();
			while( $row2 = $db->fetch_record($result2) )
			{
				$tpl->assign_block_vars('files_row',array(
					'ROW_CLASS' => $uniadmin->switch_row_class(),
					'FILE'      => $row2['filename'],
					'FILEPATH'  => dirname($row2['filename']),
					'FILENAME'  => basename($row2['filename']),
					'MD5'       => $row2['md5sum']
					)
				);

				// Add to list for dir tree parsing
				addToList($row2['filename'],$row2['md5sum'],$addonsArray);
			}

//			$uniadmin->error('<pre>' . print_r($addonsArray,true) . '</pre>');

			// Parse the dir tree array into an html list
			$htmllist = '';
			arrayToLi($addonsArray,$htmllist);
			$tpl->assign_var('FILE_HTML_LIST',$htmllist);
		}

		if( substr($row['file_name'], 0, 7) == 'http://' )
		{
			$download = $row['file_name'];
		}
		else
		{
			$download = $uniadmin->url_path . $uniadmin->config['addon_folder'] . '/' . $row['file_name'];
		}

		if ($resultoff == true)
		{
			// Assign template vars (show everything)
			$tpl->assign_vars(array(
				'ID'          => $row['id'],
				'HOMEPAGE'    => $row['homepage'],
				'ADDONNAME'   => $row['name'],
				'TOC'         => $row['toc'],
				'REQUIRED'    => $row['required'],
				'REQUIREDOFF'    => $row['requiredoff'],
				'VERSION'     => $row['version'],
				'TIME'        => date($user->lang['time_format'],$row['time_uploaded']),
				'ENABLED'     => $row['enabled'],
				'NUMFILES'    => $num_files,
				'DOWNLOAD'    => $download,
				'FILESIZE'    => $uniadmin->filesize_readable($row['filesize']),
				'NOTES'       => htmlentities($row['notes'])
				)
			);
		}
		else
		{
			// Assign template vars (don't show officer stuff)
			$tpl->assign_vars(array(
				'ID'          => $row['id'],
				'HOMEPAGE'    => $row['homepage'],
				'ADDONNAME'   => $row['name'],
				'TOC'         => $row['toc'],
				'REQUIRED'    => $row['required'],
				'VERSION'     => $row['version'],
				'TIME'        => date($user->lang['time_format'],$row['time_uploaded']),
				'ENABLED'     => $row['enabled'],
				'NUMFILES'    => $num_files,
				'DOWNLOAD'    => $download,
				'FILESIZE'    => $uniadmin->filesize_readable($row['filesize']),
				'NOTES'       => htmlentities($row['notes'])
				)
			);
		}
	}
	else
	{
		ua_die(sprintf($user->lang['error_addon_not_exist'],$addon_id));
	}
	$db->free_result($result);

	$uniadmin->set_vars(array(
		'page_title'    => $user->lang['title_addons'],
		'template_file' => 'addon_detail.html',
		'display'       => true
		)
	);
}

/**
 * Toggle Addon
 *
 * @param string $op
 * @param string $addon_id
 */
function toggle_addon( $op , $addon_id )
{
	global $db, $user, $uniadmin;

	if( !empty($op) && !empty($addon_id) )
	{
		switch( $op )
		{
			case UA_URI_DISABLE:
				$sql = "UPDATE `" . $db->table('addons') . "` SET `enabled` = '0' WHERE `id` = '$addon_id';";
				$error = 'disable';
				break;

			case UA_URI_ENABLE:
				$sql = "UPDATE `" . $db->table('addons') . "` SET `enabled` = '1' WHERE `id` = '$addon_id';";
				$error = 'enable';
				break;

			case UA_URI_OPT:
				$sql = "UPDATE `" . $db->table('addons') . "` SET `required` = '0' WHERE `id` = '$addon_id';";
				$error = 'optional';
				break;

			case UA_URI_REQ:
				$sql = "UPDATE `" . $db->table('addons') . "` SET `required` = '1' WHERE `id` = '$addon_id';";
				$error = 'require';
				break;

			case UA_URI_OPTOFF:
				$sql = "UPDATE `" . $db->table('addons') . "` SET `requiredoff` = '0' WHERE `id` = '$addon_id';";
				$error = 'optionaloff';
				break;

			case UA_URI_REQOFF:
				$sql = "UPDATE `" . $db->table('addons') . "` SET `requiredoff` = '1' WHERE `id` = '$addon_id';";
				$error = 'requireoff';
				break;

			default:
				break;
		}
		$db->query($sql);
		if( !$db->affected_rows() )
		{
		    $uniadmin->error($user->lang['error_' . $error . '_addon']);
			$uniadmin->error(sprintf($user->lang['sql_error_addons_' . $error],$addon_id));
		}
	}
}

/**
 * Deletes an addon from the addon_zip directory and the database
 *
 * @param int $addon_id
 */
function delete_addon( $addon_id )
{
	global $db, $user, $uniadmin;

	$sql = "SELECT * FROM `" . $db->table('addons') . "` WHERE `id` = '$addon_id';";
	$result = $db->query($sql);

	if( $db->num_rows($result) > 0 )
	{
		$row = $db->fetch_record($result);

		if( substr($row['file_name'], 0, 7) != 'http://' )
		{
			$local_path = UA_BASEDIR . $uniadmin->config['addon_folder'] . DIR_SEP . $row['file_name'];
			$try_unlink = unlink($local_path);
			if( !$try_unlink )
			{
				$uniadmin->error($user->lang['error_delete_addon']);
				$uniadmin->error(sprintf($user->lang['error_unlink'],$local_path));
			}
		}

		$sql = "DELETE FROM `" . $db->table('addons') . "` WHERE `id` = '$addon_id';";
		$db->query($sql);
		if( !$db->affected_rows() )
		{
		    $uniadmin->error(sprintf($user->lang['sql_error_addons_delete'],$addon_id));
		}

		$sql = "DELETE FROM `" . $db->table('files') . "` WHERE `addon_id` = '$addon_id';";
		$db->query($sql);
		if( !$db->affected_rows() )
		{
		    $uniadmin->error(sprintf($user->lang['sql_error_addons_delete'],$addon_id));
		}

		$uniadmin->message(sprintf($user->lang['addon_deleted'],$row['name']));
	}
}

/**
 * Deletes all addons from the addon_zip directory and the database
 */
function delete_all_addons( )
{
	global $db, $user, $uniadmin;

	$sql = "TRUNCATE TABLE `" . $db->table('addons') . "`;";
	$result = $db->query($sql);

	$sql = "TRUNCATE TABLE `" . $db->table('files') . "`;";
	$result = $db->query($sql);

	$uniadmin->cleardir(UA_BASEDIR . $uniadmin->config['addon_folder']);

	$uniadmin->message($user->lang['all_addons_delete']);
}

function edit_addon( $addon_id )
{
	global $db, $user, $uniadmin;

	$addon_name = strip_tags(stripslashes($_POST['name']));
	$addon_toc = strip_tags(stripslashes($_POST['toc']));
	$addon_url = strip_tags(stripslashes($_POST['homepage']));
	$addon_version = strip_tags(stripslashes($_POST['version']));
	$addon_notes = str_replace(array("\r","\n"),array('',' '),strip_tags(stripslashes($_POST['notes'])));

	// Insert Main Addon data
	$sql = "UPDATE `" . $db->table('addons') . "` SET
		`version` = '" . $db->escape($addon_version) . "',
		`name` = '" . $db->escape($addon_name) . "',
		`homepage` = '" . $db->escape($addon_url) . "',
		`notes` = '" . $db->escape($addon_notes) . "',
		`toc` = '" . $db->escape($addon_toc) . "'
		WHERE `id` = '$addon_id';";

	$db->query($sql);

	$uniadmin->message(sprintf($user->lang['addon_edited'],$addon_name));
}

function process_orphan_addons()
{
	global $uniadmin, $user;

	foreach( $_POST as $addon => $dl )
	{
		if( $dl == 'on' )
		{
			$download[] = $addon;
		}
	}

	if( $_POST[UA_URI_ACTION] == UA_URI_ADD )
	{
		foreach( $download as $key => $addon )
		{
			$addon = $_SESSION[$addon];

			$filename = UA_BASEDIR . $uniadmin->config['addon_folder'] . DIR_SEP . "$addon";

			$toPass = array();
			$toPass['name'] = $addon;
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
	elseif( $_POST[UA_URI_ACTION] == UA_URI_DELETE )
	{
		foreach( $download as $key => $addon )
		{
			$addon = $_SESSION[$addon];

			$filename = UA_BASEDIR . $uniadmin->config['addon_folder'] . DIR_SEP . "$addon";
			if( file_exists($filename) )
			{
				$try_unlink = unlink($filename);
				if( !$try_unlink )
				{
					$uniadmin->error(sprintf($user->lang['error_unlink'],$filename));
				}
			}
			$uniadmin->message(sprintf($user->lang['addon_deleted'],$addon));
		}
	}
}

/**
 * Adds an addon delete dirname
 *
 * @param string $svname
 */
function add_addon_del( $name )
{
	global $db, $user, $uniadmin;

	if( !empty($name) )
	{
		$sql = "INSERT INTO `" . $db->table('addondel') . "` ( `dir_name` ) VALUES ( '" . $db->escape($name) . "' );";
		$db->query($sql);
		if( !$db->affected_rows() )
		{
			$uniadmin->error(sprintf($user->lang['sql_error_settings_addondel_insert'],$name));
		}
	}
}

/**
 * Removes an addon delete dirname
 *
 * @param int $id
 */
function remove_addon_del( $id )
{
	global $db, $user, $uniadmin;

	$sql = "DELETE FROM `" . $db->table('addondel') . "` WHERE `id` = " . $db->escape($id) . " LIMIT 1;";
	$db->query($sql);
	if( !$db->affected_rows() )
	{
		$uniadmin->error(sprintf($user->lang['sql_error_settings_addondel_remove'],$id));
	}
}
