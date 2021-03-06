<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Web_to_WoW data retrieval file
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @date       $Date$
 * @revision   $Rev$
 * @url        $URL$
 * @author     $Author$
*/

// Enter the data you wish to output to WoW's SavedVariables.lua file here
if (file_exists('SavedVariables.lua')) {
	// This allows you to create and use a local file on your website that is pure LUA code (rather than a PHP files code)
	$svlua = include 'SavedVariables.lua';
	}
else {  // File doesn't exist, use internal system instead
	// If you use this option and have it all internal to the php file make sure to change the
	// LUA code to php (eg instead of ' you put \') for correct parsing
	$svlua = '';
	}

// Now lets send the data to UniUploader for output to WoW's SavedVariables.lua file
switch ($_REQUEST['OPERATION'])
{
	// Option to retrieve the data requested, so lets output it
	case 'GETDATA':
		echo $svlua;
		break;
	// Invalid or no request option made
	default:
		break;
}
?>