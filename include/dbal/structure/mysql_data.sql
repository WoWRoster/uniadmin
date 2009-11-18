#
# MySQL UniAdmin DB Data
#
# $Id$
#
# --------------------------------------------------------
### Configuration values
INSERT INTO `uniadmin_config` (`config_name`, `config_value`, `form_type`) VALUES
	('addon_folder', 'addon_zips', 'text{250|50'),
	('check_updates', '168', 'select{Do Not check^0|Once a Day^24|Once a Week^168|Once a Month^720'),
	('default_lang', 'english', 'function{lang_select'),
	('default_style', 'default', 'function{style_select'),
	('enable_gzip', '0', 'radio{yes^1|no^0'),
	('interface_url', '%url%interface.php', 'text{250|50'),
	('logo_folder', 'logos', 'text{250|50'),
	('remote_timeout', '24', 'text{10|10'),
	('temp_analyze_folder', 'addon_temp', 'text{250|50'),
	('UAVer', '0.8.0', 'display'),
	('ua_debug', '1', 'radio{no^0|half^1|full^2'),
	('versioncache', '', 'hidden');

### Settings
INSERT INTO `uniadmin_settings` (`set_name`, `set_value`, `enabled`, `section`, `form_type`) VALUES
	('LANGUAGE', 'English', '0', 'settings', 'select{English^English|Deutsch^Deutsch|French^French|Nederlands^Nederlands|Russian^Russian|Svenska^Svenska'),
	('PRIMARYURL', 'http://yourdomain.com/yourinterface.php', '0', 'settings', 'text{250|50'),
	('PROGRAMMODE', 'Basic', '0', 'settings', 'select{Basic^Basic|Advanced^Advanced'),
	('AUTODETECTWOW', '1', '0', 'settings', 'radio{yes^1|no^0'),
	('OPENGL', '0', '0', 'settings', 'radio{yes^1|no^0'),
	('WINDOWMODE', '0', '0', 'settings', 'radio{yes^1|no^0'),
	('UPLOADSCREENSHOTS','1','0', 'settings', 'radio{yes^1|no^0'),
	('UPLOADSVS','1','0', 'settings', 'radio{yes^1|no^0'),
	('UPLOADALLACCOUNTS','1','0', 'settings', 'radio{yes^1|no^0'),

	('UUUPDATERCHECK', '1', '0', 'updater', 'radio{yes^1|no^0'),
	('SYNCHROURL', 'http://yourdomain.com/UniAdmin/interface.php', '0', 'updater', 'text{250|50'),
	('ADDONAUTOUPDATE', '1', '0', 'updater', 'radio{yes^1|no^0'),
	('ALLOWADDONDEL', '1', '0', 'updater', 'radio{yes^1|no^0'),
	('UUSETTINGSUPDATER', '1', '0', 'updater', 'radio{yes^1|no^0'),
	('AUTOSYNCIN','0','0', 'updater', 'text{250|50'),
	('UPDATESURL', 'http://www.wowroster.net/uniuploader_updater2/update.php', '0', 'updater', 'text{250|50'),
	('UUAUTOUPDATERCHECK','0','0', 'updater', 'radio{yes^1|no^0'),
	('UUTIMEOUT','20000','0', 'updater', 'text{250|50'),
	('AUTOLAUNCHTIMER','30','0', 'updater', 'text{250|50'),

	('AUTOUPLOADONFILECHANGES', '1', '0', 'options', 'radio{yes^1|no^0'),
	('ALWAYSONTOP', '1', '0', 'options', 'radio{yes^1|no^0'),
	('SYSTRAY', '0', '0', 'options', 'radio{yes^1|no^0'),
	('ADDVAR1CH', '0', '0', 'options', 'radio{on^1|off^0'),
	('ADDVARNAME1', 'username', '0', 'options', 'text{250|50'),
	('ADDVARVAL1', '', '0', 'options', 'text{250|50'),
	('ADDVAR2CH', '0', '0', 'options', 'radio{on^1|off^0'),
	('ADDVARNAME2', 'password', '0', 'options', 'text{250|50'),
	('ADDVARVAL2', '', '0', 'options', 'password{250|50'),
	('ADDVAR3CH', '0', '0', 'options', 'radio{on^1|off^0'),
	('ADDVARNAME3', '', '0', 'options', 'text{250|50'),
	('ADDVARVAL3', '', '0', 'options', 'text{250|50'),
	('ADDVAR4CH', '0', '0', 'options', 'radio{on^1|off^0'),
	('ADDVARNAME4', '', '0', 'options', 'text{250|50'),
	('ADDVARVAL4', '', '0', 'options', 'text{250|50'),
	('ADDURL1CH', '0', '0', 'options', 'radio{on^1|off^0'),
	('ADDURL1', '', '0', 'options', 'text{250|50'),
	('ADDURL2CH', '0', '0', 'options', 'radio{on^1|off^0'),
	('ADDURL2', '', '0', 'options', 'text{250|50'),
	('ADDURL3CH', '0', '0', 'options', 'radio{on^1|off^0'),
	('ADDURL3', '', '0', 'options', 'text{250|50'),
	('ADDURL4CH', '0', '0', 'options', 'radio{on^1|off^0'),
	('ADDURL4', '', '0', 'options', 'text{250|50'),
	('HOMEURL', 'http://yourdomain.com', '0', 'options', 'text{250|50'),
	('FORUMURL', 'http://yourdomain.com/forum', '0', 'options', 'text{250|50'),
	('ROSTERURL', 'http://yourdomain.com/roster', '0', 'options', 'text{250|50'),
	('UNIADMINURL', 'http://yourdomain.com/uniadmin', '0', 'options', 'text{250|50'),
	('USERAGENT','','0', 'options', 'text{250|50'),
	('UPERRPOPUP','1','0', 'options', 'radio{yes^1|no^0'),
	('CLOSEAFUPD','0','0', 'options', 'radio{yes^1|no^0'),
	('CLOSEAFLAU','0','0', 'options', 'radio{yes^1|no^0'),

	('AUTOLAUNCHWOW', '0', '0', 'advanced', 'radio{yes^1|no^0'),
	('WOWARGS', '0', '0', 'advanced', 'text{250|50'),
	('STARTWITHWINDOWS', '0', '0', 'advanced', 'radio{yes^1|no^0'),
	('USELAUNCHER', '0', '0', 'advanced', 'radio{yes^1|no^0'),
	('STARTMINI', '1', '0', 'advanced', 'radio{yes^1|no^0'),
	('SENDPWSECURE', '1', '0', 'advanced', 'radio{yes^1|no^0'),
	('STOREPWSECURE','1','0', 'advanced', 'radio{yes^1|no^0'),
	('GZIP', '1', '0', 'advanced', 'radio{yes^1|no^0'),
	('DELAYUPLOAD', '0', '0', 'advanced', 'radio{yes^1|no^0'),
	('DELAYSECONDS', '5', '0', 'advanced', 'text{250|10'),
	('RETRDATAFROMSITE', '1', '0', 'advanced', 'radio{yes^1|no^0'),
	('RETRDATAURL', 'http://yourdomain.com/web_to_wow.php', '0', 'advanced', 'text{250|50'),
	('WEBWOWSVFILE', 'SavedVariables.lua', '0', 'advanced', 'text{250|50'),
	('DOWNLOADBEFOREWOWL', '0', '0', 'advanced', 'radio{on^1|off^0'),
	('DOWNLOADBEFOREUPLOAD', '0', '0', 'advanced', 'radio{on^1|off^0'),
	('DOWNLOADAFTERUPLOAD', '1', '0', 'advanced', 'radio{on^1|off^0'),
	('PURGEFIRST','0','0', 'advanced', 'radio{yes^1|no^0'),
	('USEAPPDATA','0','0', 'advanced', 'radio{yes^1|no^0'),
	('CLOSETOSYSTRAY','0','0', 'advanced', 'radio{yes^1|no^0'),

	('GUILDNAME', '', '0', 'custom', 'text{250|50'),
	('ENABLEOFFICERBUILD','0','0', 'custom', 'radio{yes^1|no^0'),
	('OFFICERSTR', '', '0', 'custom', 'text{250|50'),
	('MEMBERUUVAL', '', '0', 'custom', 'password{250|50'),
	('OFFICERUUVAL', '', '0', 'custom', 'password{250|50'),
	{'HIDEUAURL', '', '0', 'custom', 'radio{yes^1|no^0'};

### SV List
INSERT INTO `uniadmin_svlist` (`sv_name`) VALUES
	('CharacterProfiler'),
	('PvPLog');

### User List
INSERT INTO `uniadmin_users` (`name`, `password`, `level`, `language`, `user_style`) VALUES
	('Default', '4cb9c8a8048fd02294477fcb1a41191a', '3', 'english', 'default');
