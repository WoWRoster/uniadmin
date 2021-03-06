<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Config Module
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

if( $user->data['level'] < UA_ID_ADMIN )
{
	ua_die($user->lang['access_denied']);
}

// Get Operation
$op = ( isset($_POST[UA_URI_OP]) ? $_POST[UA_URI_OP] : '' );

// Decide What To Do
switch( $op )
{
	case UA_URI_PROCESS:
		process_update();
		$uniadmin->config();
		break;

	default:
		break;
}
main();








/**
 * UA Preferences Page Functions
 */


/**
 * Main Display
 */
function main( )
{
	global $db, $uniadmin, $user, $tpl;

	$tpl->assign_vars(array(
		'L_CONF_SETTINGS'  => $user->lang['uniadmin_config_settings'],
		'L_SETTING_NAME'   => $user->lang['setting_name'],
		'L_VALUE'          => $user->lang['value'],
		'L_ENABLED'        => $user->lang['enabled'],
		'L_IMG_MISSING'    => $user->lang['image_missing'],
		'L_UPDATE_SET'     => $user->lang['update_settings'],
		'ONLOAD'           => " onload=\"initARC('ua_mainsettings','radioOn', 'radioOff','checkboxOn', 'checkboxOff');\""
		)
	);

	$sql = "SELECT * FROM `" . $db->table('config') . "` ORDER BY `config_name` ASC;";
	$result = $db->query($sql);

	while( $row = $db->fetch_record($result) )
	{
		$setname = $row['config_name'];
		$setvalue = $row['config_value'];

		// Figure out input type
		$input_field = '';
		$input_type = explode('{',$row['form_type']);

		// Special case for hidden, continue only breaks the switch...grrrr
		if( $input_type[0] == 'hidden')
		{
			continue;
		}

		switch( $input_type[0] )
		{
			case 'text':
				$length = explode('|',$input_type[1]);
				$input_field = '<input class="input" name="' . $setname . '" type="text" value="' . $setvalue . '" size="' . $length[1] . '" maxlength="' . $length[0] . '" />';
				break;

			case 'radio':
				$options = explode('|',$input_type[1]);
				$rad=0;
				foreach( $options as $value )
				{
					$vals = explode('^',$value);
					$input_field .= '<input type="radio" id="' . $setname . '_' . $rad . '" name="' . $setname . '" value="' . $vals[1] . '" ' . ( $setvalue == $vals[1] ? 'checked="checked"' : '' ) . ' /><label for="' . $setname . '_' . $rad . '">' . $user->lang[$vals[0]] . "</label>\n";
					$rad++;
				}
				break;

			case 'select':
				$options = explode('|',$input_type[1]);
				$input_field .= '<select class="select" name="' . $setname . '">' . "\n";
				$select_one = 1;
				foreach( $options as $value )
				{
					$vals = explode('^',$value);
					if( $setvalue == $vals[1] && $select_one )
					{
						$input_field .= '  <option value="' . $vals[1] . '" selected="selected">' . $vals[0] . '</option>' . "\n";
						$select_one = 0;
					}
					else
					{
						$input_field .= '  <option value="' . $vals[1] . '">' . $vals[0] . '</option>' . "\n";
					}
				}
				$input_field .= '</select>';
				break;

			case 'function':
				$input_field = $input_type[1]();
				break;

			case 'display':
				$input_field = $setvalue;
				break;

			default:
				$input_field = $setvalue;
				break;
		}

		list($name,$tip) = explode('|',$user->lang['admin'][$setname]);

		$tpl->assign_block_vars('config_row', array(
			'ROW_CLASS'   => $uniadmin->switch_row_class(),
			'SETNAME'     => $setname,
			'SETVALUE'    => $setvalue,
			'NAME'        => addslashes($name),
			'TOOLTIP'     => addslashes($tip),
			'INPUT_FIELD' => $input_field,
			)
		);
	}

	$uniadmin->set_vars(array(
		'page_title'    => $user->lang['title_config'],
		'template_file' => 'pref.html',
		'display'       => true
		)
	);
}

/**
 * Process Config Update
 */
function process_update( )
{
	global $uniadmin;

	foreach( $_POST as $settingName => $settingValue )
	{
		if( $settingName == 'language' )
		{
			$settingName = 'default_lang';
		}
		if( $settingName == 'style' )
		{
			$settingName = 'default_style';
		}
		if( $settingName != UA_URI_OP )
		{
			if( $settingValue != $uniadmin->config[$settingName] )
			{
				$set = $uniadmin->config_set($settingName,$settingValue);
			}
		}
	}
}
