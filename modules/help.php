<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Help Module
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
