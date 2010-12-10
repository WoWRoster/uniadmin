<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Database layer
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @package    UADataBase
 */

if( !defined('IN_UNIADMIN') )
{
	exit('Detected invalid access to this file!');
}

switch( $config['dbtype'] )
{
	case 'mysql':
		include_once(UA_INCLUDEDIR . 'dbal' . DIR_SEP . 'mysql.php');
		break;

	default:
		include_once(UA_INCLUDEDIR . 'dbal' . DIR_SEP . 'mysql.php');
		break;
}

$db = new SQL_DB($config['host'], $config['database'], $config['username'], $config['password'], $config['table_prefix']);
if( !$db->link_id )
{
	print("Cannot connect to the database<br />\n");
	print('MySQL Said: ' . mysql_error() );
	die();
}
