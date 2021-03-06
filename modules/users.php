<?php
/**
 * WoWRoster.net UniAdmin
 *
 * User Module
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

if( $user->data['level'] < UA_ID_USER )
{
	ua_die($user->lang['access_denied']);
}

// Get Operation
$op = ( isset($_POST[UA_URI_OP]) ? $_POST[UA_URI_OP] : '' );

// Decide What To Do
switch( $op )
{
	case 'edit':
		modify_user();
		break;

	case 'finalize':
		finalize_user();
		main();
		break;

	case UA_URI_NEW:
		new_user();
		main();
		break;

	case 'delete':
		delete_user();
		main();
		break;

	default:
		main();
		break;
}







/**
 * Users Page Functions
 */


/**
 * Main Display
 */
function main( )
{
	global $db, $uniadmin, $user, $tpl;

	$tpl->assign_vars(array(
		'L_CURRENT_USERS'   => $user->lang['current_users'],
		'L_USERNAME'        => $user->lang['username'],
		'L_PASSWORD'        => $user->lang['password'],
		'L_RETYPE_PASSWORD' => $user->lang['retype_password'],
		'L_USERLEVEL'       => $user->lang['userlevel'],
		'L_LANGUAGE'        => $user->lang['language'],
		'L_MODIFY'          => $user->lang['modify'],
		'L_DELETE'          => $user->lang['delete'],
		'L_ADD_USER'        => $user->lang['add_user'],
		'L_USER_STYLE'      => $user->lang['user_style'],

		'S_POWER'           => ( $user->data['level'] >= UA_ID_POWER ) ? true : false,
		'S_ADMIN'           => ( $user->data['level'] == UA_ID_ADMIN ) ? true : false,

		'U_LEVEL_SELECTBOX' => level_select( ( isset($_POST['level']) ? $_POST['level'] : '') ),
		'U_LANG_SELECTBOX'  => lang_select( ( isset($_POST['language']) ? $_POST['language'] : '') ),
		'U_STYLE_SELECTBOX' => style_select( ( isset($_POST['style']) ? $_POST['style'] : '') ),
		'U_USER_ID'         => UA_ID_USER,

		'P_USERNAME'        => ( isset($_POST['name']) ) ? $_POST['name'] : '',
		)
	);

	$sql = "SELECT * FROM `" . $db->table('users') . "` ORDER BY `level` DESC, `name` ASC;";
	$result = $db->query($sql);


	while ($row = $db->fetch_record($result))
	{
		$userN = $row['name'];
		$userL = $row['level'];
		$userI = $row['id'];
		$userW = $row['language'];
		$userS = $row['user_style'];

		if( strtoupper($userN) == strtoupper($user->data['name']) || $user->data['level'] >= UA_ID_POWER )
		{
			if( strtoupper($userN) == strtoupper($user->data['name']) || $user->data['level'] == UA_ID_ADMIN || ($user->data['level'] == UA_ID_POWER && $userL == UA_ID_USER) )
			{
				$tpl->assign_block_vars('user_row', array(
					'ROW_CLASS'  => $uniadmin->switch_row_class(),
					'USER_ID'    => $userI,
					'NAME'       => $userN,
					'LEVEL'      => $userL,
					'LANG'       => $userW,
					'STYLE'      => $userS,
					'S_EDIT'     => true,
					'S_DELETE'   => true,
					)
				);
			}
			else
			{
				$tpl->assign_block_vars('user_row', array(
					'ROW_CLASS'  => $uniadmin->switch_row_class(),
					'USER_ID'    => $userI,
					'NAME'       => $userN,
					'LEVEL'      => $userL,
					'LANG'       => $userW,
					'STYLE'      => $userS,
					'S_EDIT'     => false,
					'S_DELETE'   => false,
					)
				);
			}
		}
		else
		{
			$tpl->assign_block_vars('user_row', array(
				'ROW_CLASS'  => $uniadmin->switch_row_class(),
				'USER_ID'    => $userI,
				'NAME'       => $userN,
				'LEVEL'      => $userL,
				'LANG'       => $userW,
				'STYLE'      => $userS,
				'S_EDIT'     => false,
				'S_DELETE'   => false,
				)
			);
		}
	}

	$db->free_result($result);

	$uniadmin->set_vars(array(
		'page_title'    => $user->lang['title_users'],
		'template_file' => 'user.html',
		'display'       => true
		)
	);
}

/**
 * Builds the edit user table
 */
function modify_user()
{
	global $db, $uniadmin, $user, $tpl;

	$uid = $_POST[UA_URI_ID];

	$sql = "SELECT * FROM `" . $db->table('users') . "` WHERE `id` = '$uid';";
	$result = $db->query($sql);

	$row = $db->fetch_record($result);
	$userN = $row['name'];
	$userL = $row['level'];
	$userS = $row['user_style'];
	$userW = $row['language'];

	$tpl->assign_vars(array(
		'L_MODIFY_USER' => $user->lang['modify_user'],
		'L_CHANGE_USERNAME'   => $user->lang['change_username'],
		'L_OLD_PASSWORD'      => $user->lang['old_password'],
		'L_NEW_PASSWORD'      => $user->lang['new_password'],
		'L_RETYPE_PASSWORD'   => $user->lang['retype_password'],
		'L_CHANGE_USERLEVEL'  => $user->lang['change_userlevel'],
		'L_CHANGE_LANGUAGE'   => $user->lang['change_language'],
		'L_CHANGE_STYLE'      => $user->lang['change_style'],

		'L_USERNAME'      => $user->lang['username'],
		'L_USERLEVEL'     => $user->lang['userlevel'],

		'S_POWER'         => ( $user->data['level'] >= UA_ID_POWER ) ? true : false,
		'S_ADMIN'         => ( $user->data['level'] == UA_ID_ADMIN ) ? true : false,
		'S_SELF'          => ( $user->data['level'] == $userL ) ? true : false,

		'U_USER_ID'         => $uid,
		'U_USERNAME'        => $userN,
		'U_USERLEVEL'       => $userL,
		'U_LANG_SELECTBOX'  => lang_select($userW),
		'U_LEVEL_SELECTBOX' => level_select($userL),
		'U_STYLE_SELECTBOX' => style_select($userS),
		)
	);

	$db->free_result($result);

	$uniadmin->set_vars(array(
		'page_title'    => $user->lang['modify_user'],
		'template_file' => 'user_edit.html',
		'display'       => true
		)
	);
}

/**
 * Finalizes editing of a user
 */
function finalize_user()
{
	global $db, $uniadmin, $user;

	$userI = $_POST[UA_URI_ID];
	$userP = $_POST['password'];
	$userP2 = $_POST['password2'];
	$userL = $_POST['level'];
	$userS = $_POST['style'];
	$userW = $_POST['language'];

	$sql = "SELECT * FROM `" . $db->table('users') . "` WHERE `id` = '" . $db->escape($userI) . "';";
	$result = $db->query($sql);

	$row = $db->fetch_record($result);
	$old_pass_hash = $row['password'];

	if( $user->data['id'] == $userI )
	{
		$userN = $row['name'];

		// user is level 1 and trying to change someone elses info
		if( $user->data['level'] == UA_ID_USER && $user->data['id'] != $userI )
		{
			ua_die($user->lang['access_denied']);
		}
		// user is level 1 and trying to change their name
		if( $user->data['level'] == UA_ID_USER && isset($_POST['name']) )
		{
			ua_die($user->lang['access_denied']);
		}

		// Check passwords
		$userPD = $_POST['old_password'];
		if( !empty($userP) )
		{
			if( (md5($userP) == md5($userP2)) && md5($userPD) == $old_pass_hash )
			{
				$userP = md5($userP);
			}
			else
			{
				$uniadmin->error($user->lang['error_pass_mismatch_edit']);
				$userP = $old_pass_hash;
			}
		}
		elseif( empty($userP) && !empty($userP2) )
		{
			$uniadmin->error($user->lang['error_pass_mismatch_edit']);
			$userP = $old_pass_hash;
		}
		else
		{
			$userP = $old_pass_hash;
		}

		$sql = "UPDATE `" . $db->table('users') . "` SET `password` = '" . $db->escape($userP) . "', `language` = '" . $db->escape($userW) . "', `user_style` = '" . $db->escape($userS) . "' WHERE `id` = '$userI' LIMIT 1 ;";
		$result = $db->query($sql);
	}
	elseif( $user->data['level'] > UA_ID_USER )
	{
		$userN = $_POST['name'];

		// Check passwords
		if( !empty($userP) )
		{
			if( md5($userP) == md5($userP2) )
			{
				$userP = md5($userP);
			}
			else
			{
				$uniadmin->error($user->lang['error_pass_mismatch_edit']);
				$userP = $old_pass_hash;
			}
		}
		elseif( empty($userP) && !empty($userP2) )
		{
			$uniadmin->error($user->lang['error_pass_mismatch_edit']);
			$userP = $old_pass_hash;
		}
		else
		{
			$userP = $old_pass_hash;
		}

		if ($user->data['id'] != $userI)
		{
			if ($user->data['level'] < UA_ID_ADMIN)
			{
				$userL = UA_ID_USER;
			}
			$sql = "UPDATE `" . $db->table('users') . "` SET `name` = '" . $db->escape($userN) . "', `level` = '" . $db->escape($userL) . "', `password` = '" . $db->escape($userP) . "', `language` = '" . $db->escape($userW) . "', `user_style` = '" . $db->escape($userS) . "' WHERE `id` = '$userI' LIMIT 1 ;";
		}
		else
		{
			$sql = "UPDATE `" . $db->table('users') . "` SET `name` = '" . $db->escape($userN) . "', `password` = '" . $db->escape($userP) . "', `language` = '" . $db->escape($userW) . "', `user_style` = '" . $db->escape($userS) . "' WHERE `id` = '$userI' LIMIT 1 ;";

		}
		$result = $db->query($sql);
	}
	$uniadmin->message(sprintf($user->lang['user_modified'],$userN));
}

/**
 * Finalizes creation of a new user
 */
function new_user()
{
	global $db, $uniadmin, $user;

	$userN = $_POST['name'];
	$userP = $_POST['password'];
	$userP2 = $_POST['password2'];
	$userL = $_POST['level'];
	$userW = $_POST['language'];
	$userS = $_POST['style'];

	// Form validation check
	$add_error = false;

	// Check name
	if( empty($userN) )
	{
		$uniadmin->error($user->lang['error_name_required']);
		$add_error = true;
	}

	// Check passwords
	if( !empty($userP) )
	{
		if( (md5($userP) == md5($userP2)) )
		{
			$userP = md5($userP);
		}
		else
		{
			$uniadmin->error($user->lang['error_pass_mismatch']);
			$add_error = true;
		}
	}
	elseif( empty($userP) && !empty($userP2) )
	{
		$uniadmin->error($user->lang['error_pass_mismatch']);
		$add_error = true;
	}
	else
	{
		$uniadmin->error($user->lang['error_pass_required']);
		$add_error = true;
	}

	if( $add_error )
	{
		return;
	}

	if ($user->data['level'] > UA_ID_USER)
	{
		if ($user->data['level'] > UA_ID_POWER)
		{
			$sql = "INSERT INTO `" . $db->table('users') . "` ( `name` , `password` , `level` , `language` , `user_style` ) VALUES ( '" . $db->escape($userN) . "' , '" . $userP . "' , '$userL' , '" . $db->escape($userW) . "' , '" . $db->escape($userS) . "' );";
			$db->query($sql);
			if( !$db->affected_rows() )
			{
				$uniadmin->error(sprintf($user->lang['sql_error_user_add'],$userN));
				return;
			}

			$uniadmin->message(sprintf($user->lang['user_added'],$userN));
		}
		else
		{
			$sql = "INSERT INTO `" . $db->table('users') . "` ( `name` , `password` , `level` , `language` , `user_style` ) VALUES ( '" . $db->escape($userN) . "' , '" . $userP . "' , '1' , '" . $db->escape($userW) . "' , '" . $db->escape($userS) . "' );";
			$db->query($sql);
			if( !$db->affected_rows() )
			{
				$uniadmin->error(sprintf($user->lang['sql_error_user_add'],$userN));
				return;
			}

			$uniadmin->message(sprintf($user->lang['user_added'],$userN));
		}
	}
	else
	{
		ua_die($user->lang['access_denied']);
	}
}

/**
 * Deletes a user
 */
function delete_user()
{
	global $db, $uniadmin, $user;

	$userI = $_POST[UA_URI_ID];

	$sql = "SELECT * FROM `" . $db->table('users') . "` WHERE `id` = '$userI';";
	$result = $db->query($sql);

	$row = $db->fetch_record($result);
	$userN = $row['name'];

	if ($user->data['level'] == UA_ID_ADMIN || $user->data['id'] == $userI)
	{
		$sql = "DELETE FROM `" . $db->table('users') . "` WHERE `id` = '$userI' LIMIT 1";
		$result = $db->query($sql);
		if( !$db->affected_rows() )
		{
			$uniadmin->error(sprintf($user->lang['sql_error_user_delete'],$userN));
			return;
		}

		$uniadmin->message(sprintf($user->lang['user_deleted'],$userN));
	}
	elseif ($user->data['level'] == UA_ID_POWER && $row['level'] == UA_ID_USER )
	{
		$sql = "DELETE FROM `" . $db->table('users') . "` WHERE `id` = '$userI' LIMIT 1";
		$result = $db->query($sql);
		if( !$db->affected_rows() )
		{
			$uniadmin->error(sprintf($user->lang['sql_error_user_delete'],$userN));
			return;
		}

		$uniadmin->message(sprintf($user->lang['user_deleted'],$userN));
	}
	else
	{
		ua_die($user->lang['access_denied']);
	}
}
