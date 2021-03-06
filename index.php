<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Main access file
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @package    UniAdmin
 */

// Include the initialization file
include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'set_env.php');

// Determine the module request
$page = ( isset($_GET[UA_URI_PAGE]) && !empty($_GET[UA_URI_PAGE]) ) ? $_GET[UA_URI_PAGE] : 'help';

define('UA_CURRENT_PAGE', $page);
unset($page);

if( preg_match('/[^a-z0-9_]/i', UA_CURRENT_PAGE) )
{
	ua_die($user->lang['error_invalid_module_name']);
}

// Include the module
if( is_file($var = UA_MODULEDIR . UA_CURRENT_PAGE . '.php') )
{
	require($var);
}
else
{
	ua_die($user->lang['error_invalid_module']);
}

$db->close_db();

?>