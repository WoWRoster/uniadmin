<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Help Module
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id$
 * @link       http://www.wowroster.net
 * @package    UniAdmin
*/

if( !defined('IN_UNIADMIN') )
{
    exit('Detected invalid access to this file!');
}

foreach( $user->lang['help'] as $help_text )
{
	$tpl->assign_block_vars('help_row', array(
	    'HELP_HEADER' => $help_text['header'],
	    'HELP_TEXT'   => $help_text['text'])
	);
}

$uniadmin->set_vars(array(
    'page_title'    => $user->lang['title_help'],
    'template_file' => 'help.html',
    'display'       => true)
);
