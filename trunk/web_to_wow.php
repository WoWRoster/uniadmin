<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Web_to_WoW data retrieval file
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @date       $Date$
 * @revision   $Rev$
 * @url        $URL$
 * @author     $Author$
*/

// Enter the data you wish to output to WoW's SavedVariables.lua file here
$svlua = "";

// Now lets send the data to UniUploader for output to WoW's SavedVariables.lua file
switch ($_REQUEST["OPERATION"])
{
	// Option to retrieve the data requested, so lets output it
	case "GETDATA":
		echo $svlua;
		break;
	// Invalid or no request option made
	default:
		break;
}
?>
