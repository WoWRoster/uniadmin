<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Addon parsing functions
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

/**
 * Addon Parsing Functions
 */

/**
 * Processess an uploaded addon for insertion into the database
 *
 * @param array $fileArray	Array of info about a file
 * Standard array that the global $_FILES would contain
 *
 * 		[name] => The original name of the file
 * 		[type] => The mime type of the file
 * 		[tmp_name] => Full Server Path to real file location
 * 		[error] => The error code associated with this file
 * 		[size] => The size, in bytes, of the file
 */
function process_addon( $fileArray )
{
	global $db, $user, $uniadmin;

//	$uniadmin->error('<pre>' . print_r($fileArray,true) . '</pre>');

	$temp_file_name = ( isset($fileArray['tmp_name']) ? $fileArray['tmp_name'] : '' );

	// Check if nothing was uploaded
	if( empty($temp_file_name) )
	{
		$uniadmin->message($user->lang['error_no_addon_uploaded']);
		return false;
	}

	$addon_file_name = str_replace(' ','_',$fileArray['name']);

	if( $uniadmin->get_file_ext($addon_file_name) != 'zip' )
	{
		$uniadmin->message($user->lang['error_zip_file']);
		return false;
	}

	$addon_zip_folder = UA_BASEDIR . $uniadmin->config['addon_folder'] . DIR_SEP;
	$temp_folder = UA_BASEDIR . $uniadmin->config['temp_analyze_folder'];

	// Check if this addon is required
	$required = ( isset($_FILES['file']) ? ( isset($_POST['required']) ? 1 : 0 ) : NULL );

	// See if we are auto detecting the path or are we overriding it
	$full_path = false;
	$auto_path = true;
	if( isset($_POST['fullpath_addon']) && $_POST['fullpath_addon'] != '' )
	{
		switch( $_POST['fullpath_addon'] )
		{
			case '0': // Force false
				$full_path = false;
				$auto_path = false;
				break;

			case '1': // Force true
				$full_path = true;
				$auto_path = false;
				break;

			case '2': // Auto-detect mode
				$full_path = false;
				$auto_path = true;
				break;

			default: // Default is false and auto-detect
				$full_path = false;
				$auto_path = true;
				break;
		}
	}

	// Name and location of the zip file
	$zip_file = $addon_zip_folder . $addon_file_name;

	// Do the following actions only if we are not processing an existing addon
	if( is_uploaded_file($temp_file_name) )
	{
		// Delete Addon if it exists
		if( file_exists($zip_file) )
		{
			unlink($zip_file);
		}

		// Try to move to the addon_temp directory
		$try_move = move_uploaded_file($temp_file_name,$zip_file);
		if( !$try_move )
		{
			$uniadmin->error(sprintf($user->lang['error_move_uploaded_file'],$temp_file_name,$zip_file));
			return false;
		}
	}


	// Try to set write access on the uploaded file
	if( !is_writeable($zip_file) || !is_readable($zip_file) )
	{
		$try_chmod = chmod($zip_file,0777);
		if( !$try_chmod )
		{
			$uniadmin->error(sprintf($user->lang['error_chmod'],$zip_file));
			return false;
		}
	}

	// Get the file size
	$file_size = filesize($zip_file);

	// Unzip the file
	$unziped = $uniadmin->unzip($zip_file,$temp_folder . DIR_SEP);
	if( $unziped == 0 )
	{
		$try_unlink = unlink($zip_file);
		if( !$try_unlink )
		{
			$uniadmin->error(sprintf($user->lang['error_unlink'],$zip_file));
		}
		$uniadmin->cleardir($temp_folder);
		return false;
	}

	// Get the files in the directory
	$files = $uniadmin->ls($temp_folder);

	if( is_array($files) )
	{
		// Initialize LoD actions
		$lodAddOns = false;
		$lodAddOnZipFiles = array();

		// Initialize the TOC data
		$toc_file_name = '';
		$toc_files = array();
		$revision_files = array();

		// Search all *lod.bat and all *lod.sh and analyze them
		foreach( $files as $index => $file )
		{
			if( (substr( $file, -6 ) == 'lod.sh') || (substr($file, -7) == 'lod.bat') )
			{
				$lodAddOns = true;
				$localPath = dirname( $file );
//				$uniadmin->error('Local Path: ' . $localPath);

				chdir($localPath);
				$file_contents = explode("\n", file_get_contents($file));
				foreach( $file_contents as $line )
				{
					$line = trim($line);
					$arguments = explode(' ', $line);
					$command = $arguments[0];
//					$uniadmin->error('Command detected: ' . $command);

					switch( $command )
					{
						case 'cd':
							for( $i=1; $i < count($arguments); $i++ )
							{
								if( strlen($arguments[$i]) )
								{
									$localPath = realpath($arguments[$i]);
									if( substr($localPath, 0, strlen($temp_folder)) == $temp_folder )
									{
										chdir($localPath);
//										$uniadmin->error('Change Local Path: ' . $localPath);
									}
									else
									{
										$uniadmin->error(sprintf($user->lang['error_unsafe_file'],basename($file)));
									}
									break;
								}
							}
							break;

						case 'del':
						case 'rm':
							$options = '';
							$delete = array();
							for( $i = 1; $i < count($arguments); $i++ )
							{
								if( substr($arguments[$i], 0, 1) == '-' )
								{
									$options .= substr($arguments[$i], 1);
								}
								elseif(
									strlen($arguments[$i])
									&& ( $deleteFile = realpath($arguments[$i]) )
									&& ( is_dir($deleteFile) || is_file($deleteFile) )
									&& ( substr($deleteFile, 0, strlen($temp_folder)) == $temp_folder )
								) {
//									$uniadmin->error('Deleting: ' . $localPath . DIR_SEP . $arguments[$i]);
									$delete[] = $deleteFile;
								}
							}
							if( count($delete) )
							{
							}
							break;

						case 'mv':
							$options = '';
							$from = null;
							$to = null;
							for( $i = 1; $i < count( $arguments ); $i++ )
							{
								if( substr( $arguments[$i], 0,1 ) == '-' )
								{
									$options .= substr( $arguments[$i], 1 );
								}
								elseif (
									strlen($arguments[$i])
									&& ( !$from )
									&& ( $moveFile = realpath( $arguments[$i] ) )
									&& ( is_dir( $moveFile ) || is_file( $moveFile ) )
									&& ( substr( $moveFile , 0, strlen( $temp_folder ) ) == $temp_folder )
								) {
									$from = $moveFile;
								}
								elseif (
									strlen($arguments[$i])
									&& ( $from )
									&& ( $moveFile = realpath( substr( $arguments[$i], 0, strrpos( $arguments[$i], '/' ) ) ) )
									&& ( is_dir( $moveFile ) || is_file( $moveFile ) )
									&& ( substr( $moveFile , 0, strlen( $temp_folder ) ) == $temp_folder )
								) {
									$lastfile = substr( $arguments[$i], strrpos( $arguments[$i], '/' ) + 1 );
									$to = $moveFile.DIR_SEP.$lastfile;
								}
							}
							if( $from && $to )
							{
								rename( $from, $to );
//								$uniadmin->error('Renaming [' . $from . '] to [' . $to . ']');
							}
							break;

						default:
							$uniadmin->error('Line not interpretated: ' . $line);
							break;
					}
				}
				unlink($file);
			}

			// Auto Path detection
			if( $auto_path && !$full_path )
			{
				// Check if the file has 'Interface/AddOns/', if so set $full_path to true
				if( stristr($file, 'Interface' . DIR_SEP . 'AddOns') )
				{
					$full_path = true;
				}
			}
		}

		// We have some LoD Addons to move
		if( $lodAddOns )
		{
			// What we had before the LoD scan
			$oldFiles = $files;

			// New scan of the directory
			$newFiles = $uniadmin->ls($temp_folder);

			// Remove files that do not currently exist
			foreach( $oldFiles as $index => $file )
			{
				if( !file_exists($file) )
				{
					unset($oldFiles[$index]);
				}
			}

			// Remove the old files from the new files array
			$newFiles = array_diff($newFiles, $oldFiles);

			// Zip old files
			$zip_result = $uniadmin->zip($zip_file, $oldFiles, $temp_folder);
//			$uniadmin->error('$uniadmin->zip( ' . $zip_file . ', ' . $oldFiles . ', ' . $temp_folder . ');');

			if( $zip_result == 0 )
			{
				$uniadmin->error($user->lang['error_unzip']);
				$try_unlink = @unlink($zip_file);
				if( !$try_unlink )
				{
					$uniadmin->error(sprintf($user->lang['error_unlink'],$zip_file));
				}
				$uniadmin->cleardir($temp_folder);
				return false;
			}

			// New files packaging
			$filesbyLodAddOn = array();
			$lodAddOnDirPath = $temp_folder . ( $full_path ? DIR_SEP . 'Interface' . DIR_SEP . 'AddOns' : '' );

			foreach( $newFiles as $index => $file )
			{
				$loadAddOnName = substr($file, strlen($lodAddOnDirPath)+1, strpos($file, '/', strlen($lodAddOnDirPath) + 1) - strlen($lodAddOnDirPath)-1);
				if( !isset($filesbyLodAddOn[$loadAddOnName]) || ! is_array($filesbyLodAddOn[$loadAddOnName]) )
				{
					$filesbyLodAddOn[$loadAddOnName] = array();
				}
				$filesbyLodAddOn[$loadAddOnName][] = $file;
			}

			foreach( $filesbyLodAddOn as $loadAddOnName => $files )
			{
				$lodAddOnZipFile = $addon_zip_folder . $loadAddOnName . '.zip';

				// Zip our LoD file since we clear the addon temp dir when each addon is processed
				$zip_result = $uniadmin->zip($lodAddOnZipFile, $files, $temp_folder);
//				$uniadmin->error('$uniadmin->zip( ' . $lodAddOnZipFile . ', ' . $files . ', ' . $temp_folder . ');');

				if( $zip_result == 0 )
				{
					$uniadmin->error($user->lang['error_unzip']);
					$try_unlink = @unlink($zip_file);
					if( !$try_unlink )
					{
						$uniadmin->error(sprintf($user->lang['error_unlink'],$zip_file));
					}
					$uniadmin->cleardir($temp_folder);
					return false;
				}

				$lodAddOnZipFiles[$loadAddOnName] = $lodAddOnZipFile;

				// Delete the temp LoD files
				foreach( $files as $index => $file )
				{
					unlink( $file );
				}
			}
//			$uniadmin->error('<pre>' . print_r($filesbyLodAddOn, true) . '<pre>');
			$files = $oldFiles;
		}

		// Scan for *.toc and changelog-r*.txt files
		foreach( $files as $index => $file )
		{
			if( $uniadmin->get_file_ext($file) == 'toc' )
			{
				$toc_files[] = $file;
				continue;
			}
			elseif( strpos($file, 'changelog-r') !== false && $uniadmin->get_file_ext($file) == 'txt' )
			{
				$revision_files[] = $file;
				continue;
			}
		}

		// Scan the toc files if we have any
		if( count($toc_files) > 0 )
		{
			foreach( $toc_files as $file )
			{
				$toc_number = get_toc_val($file, 'Interface', '00000');

				$k = explode(DIR_SEP,$file);
				$toc_file_name = $k[count($k) - 1];
				$toc_file_name = substr($toc_file_name,0,count($toc_file_name) -5);

				$real_addon_name = get_toc_val($file, 'Title', $toc_file_name);

				if( strpos($real_addon_name, 'Ace') && strpos($real_addon_name, '|r'))
				{
					$real_addon_name = trim(substr($real_addon_name, 0, strpos(substr($real_addon_name, 0, strpos($real_addon_name, '|r')), '|')));
				}

				// Get version
				$revision = '';
				$version = get_toc_val($file, 'Version', '');
				$rev_matches = null;

				if( count($revision_files) > 0 )
				{
					asort($revision_files);

					foreach( $revision_files as $revision_file_name )
					{
						preg_match('|changelog\-r(.+?).txt|',$revision_file_name,$rev_matches);

						$revision = $rev_matches[1];
					}

					if( !empty($version) && !empty($revision) && ($version != $revision) )
					{
						$version .= " | r$revision";
					}
					elseif( empty($version) && !empty($revision) )
					{
						$version = "r$revision";
					}
				}

				// Set notes and homepage
				$homepage = get_toc_val($file, 'X-Website', get_toc_val($file, 'URL', ''));
				$notes = get_toc_val($file, 'Notes', '');

				$addon_file_check = strtolower(str_replace('.zip','',$addon_file_name));
				$toc_file_check = strtolower( str_replace( array(' ','.toc'), array('_',''), basename($toc_file_name) ) );

				if( strpos($addon_file_check, $toc_file_check) !== false )
				{
					break;
				}
				else
				{
					unset($k, $toc_file_name);
				}
			}
		}
		else  // We stop processing the addon because all addons should have a toc file
		{
			$try_unlink = unlink($zip_file);
			if( !$try_unlink )
			{
				$uniadmin->error(sprintf($user->lang['error_unlink'],$zip_file));
			}
			$uniadmin->cleardir($temp_folder);
			$uniadmin->error($user->lang['error_no_toc_file']);
			return false;
		}
	}
	else  // No files, we stop processing
	{
		$try_unlink = unlink($zip_file);
		if( !$try_unlink )
		{
			$uniadmin->error(sprintf($user->lang['error_unlink'],$zip_file));
		}
		$uniadmin->cleardir($temp_folder);
		$uniadmin->message($user->lang['error_no_files_addon']);
		return false;
	}

	// See if AddOn exists in the database and do stuff to it
	$sql = "SELECT * FROM `" . $db->table('addons') . "` WHERE `name` = '" . $db->escape($real_addon_name) . "';";
	$result = $db->query($sql);

	if( $db->num_rows($result) > 0 )
	{
		$row = $db->fetch_record($result);

		$db->free_result($result);

		$addon_id = $row['id'];

		// Check if we have new data and update, use old if not
		if( $homepage == '' )
		{
			$homepage = $row['homepage'];
		}
		if( $notes == '' )
		{
			$notes = $row['notes'];
		}
		if( $version == '' )
		{
			$version = $row['version'];
		}

		$enabled = $row['enabled'];

		// Remove files from database since we'll be updating them all
		$sql = "DELETE FROM `" . $db->table('files') . "` WHERE `addon_id` = '" . $addon_id . "';";
		$db->query($sql);

		if( is_null($required) )
		{
			$required = $row['required'] ? 1 : 0;
		}

		// Update Main Addon data
		$sql = "UPDATE `" . $db->table('addons') . "` SET `time_uploaded` = '" . time() . "', `version` = '" . $db->escape($version) . "', `enabled` = '$enabled', `name` = '" . $db->escape($real_addon_name) . "', `file_name` = '" . $db->escape($addon_file_name) . "', `homepage` = '" . $db->escape($homepage) . "', `notes` = '" . $db->escape($notes) . "', `toc` = '$toc_number', `required` = '$required', `filesize` = '$file_size', `full_path` = '" . intval($full_path) . "'"
			 . " WHERE `id` = '" . $addon_id . "';";
		$db->query($sql);
	}
	else  // New addon
	{
		if( is_null($required) )
		{
			$required = 0;
		}
		// Insert Main Addon data
		$sql = "INSERT INTO `" . $db->table('addons') . "` ( `time_uploaded` , `version` , `enabled` , `name`, `file_name`, `homepage`, `notes`, `toc`, `required`, `filesize`, `full_path` )"
			 . " VALUES ( '" . time() . "', '" . $db->escape($version) . "', '1', '" . $db->escape($real_addon_name) . "', '" . $db->escape($addon_file_name) . "', '" . $db->escape($homepage) . "', '" . $db->escape($notes) . "', '$toc_number', '$required', '$file_size', '" . intval($full_path) . "' );";
		$db->query($sql);

		// Get the insert id of the addon just inserted
		$addon_id = $db->insert_id();
	}

	if( !$db->affected_rows() )
	{
		// Clear up the addons table
		$sql = "DELETE FROM `" . $db->table('addons') . "` WHERE `id` = '$addon_id'";
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

	    $uniadmin->error($user->lang['sql_error_addons_insert']);
	    $uniadmin->cleardir($temp_folder);
	    return false;
	}

	// Insert Addon Files' Data
	foreach( $files as $file )
	{
		$md5 = md5_file($file);
		$k = explode(DIR_SEP,$file);
		$pos_t = strpos($file,'addon_temp');
		$file_name = str_replace('/','\\',substr($file,$pos_t + 10));

		if( $file_name != 'index.htm' && $file_name != 'index.html' && $file_name != '.svn' )
		{
			if( $full_path == false )
			{
				$file_name = '\Interface\AddOns' . $file_name;
			}

			$sql = "INSERT INTO `" . $db->table('files') . "` ( `addon_id` , `filename` , `md5sum` ) "
				 . " VALUES ( '" . $addon_id . "', '" . $db->escape($file_name) . "', '" . $db->escape($md5) . "' );";
			$db->query($sql);
			if( !$db->affected_rows() )
			{
				// Clear up the addons table
				$sql = "DELETE FROM `" . $db->table('addons') . "` WHERE `id` = '$addon_id'";
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

			    $uniadmin->error($user->lang['sql_error_addons_files_insert']);
			    $uniadmin->cleardir($temp_folder);
			    return false;
			}
		}

		// We have obtained the md5 and inserted the row into the database, now delete the file from the temp dir
		$try_unlink = unlink($file);
		if( !$try_unlink )
		{
			$uniadmin->error(sprintf($user->lang['error_unlink'],$file));
		}
	}

	// Now clear the temp folder
	$uniadmin->cleardir($temp_folder);

	$uniadmin->message(sprintf($user->lang['addon_uploaded'],$real_addon_name));

	// Now process our LoD addons, if any
	if( count($lodAddOnZipFiles) )
	{
		foreach( $lodAddOnZipFiles as $lodAddOnName => $lodAddOnZipFile )
		{
			$toPass = array();
			$toPass['name'] = $lodAddOnName . '.zip';
			$toPass['type'] = 'application/zip';
			$toPass['tmp_name'] = $lodAddOnZipFile;

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

	return true;
}

/**
 * Gets the value from the .toc file
 *
 * @param string $file		File to parse
 * @param string $var		Variable to search for
 * @param string $def_val	Default Value
 * @return string
 */
function get_toc_val( $file , $var , $def_val )
{
	$lines = file($file);

	$matches = $str_matches = null;

	$val = $def_val;
	foreach( $lines as $line )
	{
		$found = preg_match('/## \\b' . $var . '\\b: (.+)/',$line,$matches);

		if( $found )
		{
			$val = $matches[1];

			// Catch some special case lines here
			switch( $var )
			{
				case 'Version':
					$str_found = preg_match('/\$Revision:(.+?)\$/',$val,$str_matches);
					if( $str_found )
					{
						$val = $str_matches[1];
					}
					break;
			}

			$val = preg_replace('/(.+?)\\|c[a-f0-9]{8}(.+?)\\|r/i','\\1\\2',$val);
			$val = preg_replace('/\\|c[a-f0-9]{8}/i','',$val);
			$val = str_replace('|r','',$val);

			break;
		}
	}
	return trim($val);
}


/**
 * Convert a PHP array to a HTML list
 *
 * @param array $array		Array to convert
 * @param string $baseName	Top
 * @param string $string
 * @param bool $call
 */
function arrayToLi( $array , &$string , $baseName='' , $call=false )
{
	// Write out the initial definition
	if( $call )
	{
		$open = array('interface','addons');
		if( in_array(strtolower($baseName),$open) )
		{
			$string .= ("<li>$baseName\n<ul rel=\"open\">\n");
		}
		else
		{
			$string .= ("<li>$baseName\n<ul>\n");
		}
	}

	//Reset the array loop pointer
	reset($array);

	//Use list() and each() to loop over each key/value pair of the array
	while( list($key, $value) = each($array) )
	{
		if( is_array($value) )
		{
			// The value is another array, so simply call another instance of this function to handle it
			arrayToLi($value, $string, $key, true);
			if( $call )
			{
				$string .= "</ul></li>\n";
			}
		}
		else
		{
			// Output the value directly otherwise
			$string .= ("<li onmouseover=\"overlib('$value',LEFT,CAPTION,'MD5 - [$key]');\" onmouseout=\"return nd();\">$key</li>\n");
		}
	}
	if( !$call )
	{
		$string .= ("</ul></li>\n");
	}
}

/**
 * Adds a string to a directory tree array
 *
 * @param string $str	String to parse
 * @param string $md5	MD5 hash (optional)
 * @param array $array	Array to add elements to
 */
function addToList( $str , $md5 , &$array )
{
	$things = explode('\\', $str);

	if($things['0'] == '')
	{
		array_shift($things);
	}
	addToArray($things, $md5, $array);
}

/**
 * Part two of addToList()
 * A very, very dirty way to make an array
 * Only goes 11 levels deep
 * If we come across an addon that is more than 11 levels deep
 *   the addon developer must be one sick individual...
 *
 * @param array $things		Array to convert
 * @param string $md5		MD5 hash
 * @param array $array		Array to add elements to
 */
function addToArray( $things , $md5 ,  &$array )
{
	$count = count($things);

	switch( $count )
	{
		case 1:
			$array[$things['0']] = $md5;
			break;

		case 2:
			$array[$things['0']][$things['1']] = $md5;
			break;

		case 3:
			$array[$things['0']][$things['1']][$things['2']] = $md5;
			break;

		case 4:
			$array[$things['0']][$things['1']][$things['2']][$things['3']] = $md5;
			break;

		case 5:
			$array[$things['0']][$things['1']][$things['2']][$things['3']][$things['4']] = $md5;
			break;

		case 6:
			$array[$things['0']][$things['1']][$things['2']][$things['3']][$things['4']][$things['5']] = $md5;
			break;

		case 7:
			$array[$things['0']][$things['1']][$things['2']][$things['3']][$things['4']][$things['5']][$things['6']] = $md5;
			break;

		case 8:
			$array[$things['0']][$things['1']][$things['2']][$things['3']][$things['4']][$things['5']][$things['6']][$things['7']] = $md5;
			break;

		case 9:
			$array[$things['0']][$things['1']][$things['2']][$things['3']][$things['4']][$things['5']][$things['6']][$things['7']][$things['8']] = $md5;
			break;

		case 10:
			$array[$things['0']][$things['1']][$things['2']][$things['3']][$things['4']][$things['5']][$things['6']][$things['7']][$things['8']][$things['9']] = $md5;
			break;

		case 11:
			$array[$things['0']][$things['1']][$things['2']][$things['3']][$things['4']][$things['5']][$things['6']][$things['7']][$things['8']][$things['9']][$things['10']] = $md5;
			break;

		default:
			break;
	}
}
