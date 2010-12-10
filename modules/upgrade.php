<?php
/**
 * WoWRoster.net UniAdmin
 *
 * UniAdmin Upgrader
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @package    UniAdmin
 * @subpackage Upgrader
 */

if( !defined('IN_UNIADMIN') )
{
	exit('Detected invalid access to this file!');
}

if( version_compare($uniadmin->config['UAVer'], UA_VER,'>=') )
{
	ua_die($user->lang['no_upgrade']);
}


/**
 * UniAdmin Upgrader
 *
 * @package    UniAdmin
 * @subpackage Upgrader
 */
class Upgrade
{
	var $db = null;
	var $versions = array('0.7.5','0.7.6','0.7.7','0.7.8','0.7.9');
	var $index = null;

	function upgrade()
	{
		global $db;

		$db->error_die(false);

		if( isset($_POST['upgrade']) )
		{
			// Find out what version we're upgrading from
			$version_from = $_POST['version'];
			foreach( $this->versions as $index => $version )
			{
				$this->index = $index;
				if( str_replace('.', '', $version) == $version_from )
				{
					$method = 'upgrade_' . $version_from;
					$this->$method();
				}
			}
		}
		else
		{
			$this->display_form();
		}
	}

	function finalize()
	{
		global $user, $uniadmin;

		$this->index++;

		if( isset($this->versions[$this->index]) )
		{
			$method = 'upgrade_' . str_replace('.', '', $this->versions[$this->index]);
			$this->$method();
		}
		else
		{
			$uniadmin->message($user->lang['upgrade_complete']);

			$uniadmin->set_vars(array(
				'page_title'    => $user->lang['ua_upgrade'],
				'template_file' => 'index.html',
				'display'       => true
				)
			);
		}
	}

	//--------------------------------------------------------------
	// Upgrade methods
	//--------------------------------------------------------------

	function upgrade_079()
	{
		$this->standard_upgrader();
		$this->finalize();
	}

	function upgrade_078()
	{
		$this->standard_upgrader();
		$this->finalize();
	}

	function upgrade_077()
	{
		$this->standard_upgrader();
		$this->finalize();
	}

	function upgrade_076()
	{
		$this->standard_upgrader();
		$this->finalize();
	}

	function upgrade_075()
	{
		$this->standard_upgrader();
		$this->finalize();
	}

	/**
	 * The standard upgrader
	 * This parses the requested sql file for database upgrade
	 * Most upgrades will use this function
	 */
	function standard_upgrader()
	{
		global $db, $config, $user;

		$ver = str_replace('.', '', $this->versions[$this->index]);

		$db_structure_file = UA_INCLUDEDIR . 'dbal' . DIR_SEP . 'structure' . DIR_SEP . 'upgrade_' . $ver . '.sql';

		if( file_exists($db_structure_file) )
		{
			// Parse structure file and create database tables
			$sql = @fread(@fopen($db_structure_file, 'r'), @filesize($db_structure_file));
			$sql = preg_replace('#uniadmin\_(\S+?)([\s\.,]|$)#', $config['table_prefix'] . '\\1\\2', $sql);

			$sql = remove_remarks($sql);
			$sql = parse_sql($sql, ';');

			$sql_count = count($sql);
			for ( $i = 0; $i < $sql_count; $i++ )
			{
				$db->query($sql[$i]);
			}
			unset($sql);
		}

		// Nifty, we update the stats table to include our UA upgrades!
		$sql = "INSERT INTO `" . $db->table('stats') . "` ( `ip_addr` , `host_name` , `action` , `time` , `user_agent` ) VALUES
			( '" . $db->escape($user->ip_address) . "', '" . $db->escape($user->remote_host) . "', 'UPGRADE-" . UA_VER . "', '" . time() . "', '" . $db->escape($user->user_agent) . "' );";
		$db->query($sql);

		// Get the default locale for the USERAGENT update
		$sql = 'SELECT `config_value` FROM ' . CONFIG_TABLE . " WHERE `config_name` = 'default_lang';";
		$default_lang = $db->query_first($sql);

		$db->query('UPDATE `' . $db->table('settings') . "` SET `set_value` = 'UniUploader 2.0 (UU " . UU_VER . "; {$default_lang})' WHERE `set_name` = 'USERAGENT';");

		return;
	}

	function display_form()
	{
		global $db, $uniadmin, $user, $tpl;

		foreach ( $this->versions as $version )
		{
			$selected = ( $version == $uniadmin->config['UAVer'] ) ? ' selected="selected"' : '';

			$tpl->assign_block_vars('version_row', array(
				'VALUE'    => str_replace('.', '', $version),
				'SELECTED' => $selected,
				'OPTION'   => 'UniAdmin ' . $version
				)
			);
		}

		$tpl->assign_vars(array(
			'L_UA_UPGRADE'     => $user->lang['ua_upgrade'],
			'L_SELECT_VERSION' => $user->lang['select_version'],
			'L_UPGRADE'        => $user->lang['upgrade']
			)
		);

		$uniadmin->set_vars(array(
			'page_title'    => $user->lang['ua_upgrade'],
			'template_file' => 'upgrade.html',
			'display'       => true
			)
		);
	}
}



/**
 * Removes comments from a SQL data file
 *
 * @param    string  $sql    SQL file contents
 * @return   string
 */
function remove_remarks($sql)
{
	if ( $sql == '' )
	{
		die('Could not obtain SQL structure/data');
	}

	$retval = '';
	$lines  = explode("\n", $sql);
	unset($sql);

	foreach ( $lines as $line )
	{
		// Only parse this line if there's something on it, and we're not on the last line
		if ( strlen($line) > 0 )
		{
			// If '#' is the first character, strip the line
			$retval .= ( substr($line, 0, 1) != '#' ) ? $line . "\n" : "\n";
		}
	}
	unset($lines, $line);

	return $retval;
}

/**
 * Parse multi-line SQL statements into a single line
 *
 * @param    string  $sql    SQL file contents
 * @param    char    $delim  End-of-statement SQL delimiter
 * @return   array
 */
function parse_sql($sql, $delim)
{
	if ( $sql == '' )
	{
		die('Could not obtain SQL structure/data');
	}

	$retval     = array();
	$statements = explode($delim, $sql);
	unset($sql);

	$linecount = count($statements);
	for ( $i = 0; $i < $linecount; $i++ )
	{
		if ( ($i != $linecount - 1) || (strlen($statements[$i]) > 0) )
		{
			$statements[$i] = trim($statements[$i]);
			$statements[$i] = str_replace("\r\n", '', $statements[$i]) . "\n";

			// Remove 2 or more spaces
			$statements[$i] = preg_replace('#\s{2,}#', ' ', $statements[$i]);

			$retval[] = trim($statements[$i]);
		}
	}
	unset($statements);

	return $retval;
}

$upgrade = new Upgrade();

// And the upgrade-o-matic 5000 takes care of the rest.
