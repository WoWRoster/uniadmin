<?php
/**
 * WoWRoster.net UniAdmin
 *
 * Constants
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

// UniAdmin Version
define('UA_VER', '0.7.9');
define('NO_CACHE', true);

// Directories
define('UA_INCLUDEDIR', UA_BASEDIR . 'include' . DIR_SEP);
define('UA_LANGDIR',    UA_BASEDIR . 'language' . DIR_SEP);
define('UA_THEMEDIR',   UA_BASEDIR . 'styles' . DIR_SEP);
define('UA_MODULEDIR',  UA_BASEDIR . 'modules' . DIR_SEP);
define('UA_CACHEDIR',   UA_BASEDIR . 'cache' . DIR_SEP);

// User Levels
define('UA_ID_ANON',  0);
define('UA_ID_USER',  1);
define('UA_ID_POWER', 2);
define('UA_ID_ADMIN', 3);

// URI Parameters
define('UA_URI_OP',           'op');
define('UA_URI_ACTION',       'action');
define('UA_URI_ID',           'id');
define('UA_URI_ADD',          'add');
define('UA_URI_DELETE',       'delete');
define('UA_URI_DELETE_ALL',   'deleteall');
define('UA_URI_DISABLE',      'disable');
define('UA_URI_ENABLE',       'enable');
define('UA_URI_OPT',          'optional');
define('UA_URI_REQ',          'require');
define('UA_URI_PROCESS',      'process');
define('UA_URI_UPDATE_ALL',   'update_all');
define('UA_URI_UPDATE_ONE',   'update_one');
define('UA_URI_SVNAME',       'svname');
define('UA_URI_NAME',         'name');
define('UA_URI_LEVEL',        'level');
define('UA_URI_PASS',         'password');
define('UA_URI_NEW',          'new');
define('UA_URI_UPINI',        'upini');
define('UA_URI_GETINI',       'getini');
define('UA_URI_DETAIL',       'detail');
define('UA_URI_EDIT',         'edit');
define('UA_URI_ORPHAN',       'orphan');
define('UA_URI_ADDONDEL_ADD', 'addondel_add');
define('UA_URI_ADDONDEL_REM', 'addondel_remove');
define('UA_URI_ADDONDEL_NAME','addondel_name');
define('UA_URI_RELOAD',       'reload');

// URL parameters
define('UA_INDEX',        '');
define('UA_URI_PAGE',     'p');
define('UA_INDEXPAGE',    UA_INDEX . '?' . UA_URI_PAGE . '=');
define('UA_URI_THEME',    'theme');
define('UA_FORMACTION',   UA_INDEX . ( isset($_GET[UA_URI_PAGE]) && ($_GET[UA_URI_PAGE] != '') ? '?' . UA_URI_PAGE . '=' . $_GET[UA_URI_PAGE] : '') );

// Reject certain settings in UU since we don't need them, or want them displayed
define('UA_REJECT_INI'   , 'CHECKEDADDONS,CHECKEDSVLIST,EXELOC,FILELOCATION,SELECTEDACCT,EXE1,EXE1LOCATION,EXE2,EXE2LOCATION,EXE3,EXE3LOCATION,EXEUULAUNCH,EXEWOWLAUNCH');

// File types to ignore when scanning addons
define('UA_ADDON_BLACKLIST', 'ade,adp,bas,bat,chm,cmd,com,cpl,crt,doc,eml,emf,exe,hlp,hta,inf,ins,isp,jar,js,jse,lnk,mdb,mde,msc,msi,msp,mst,pcd,pif,ppt,py,rar,reg,scr,sct,shs,url,vbs,vbe,wsf,wsh,wsc,xsl');

// Allowed logo image types
define('UA_LOGO_TYPES'  , 'jpg,jpeg,png,ico,gif');

// Download UniAdmin location and update check
define('UA_DOWNLOAD','http://www.wowroster.net/Downloads/details/id=16.html');
define('UA_UPDATECHECK','http://www.wowroster.net/ua_version.txt');
