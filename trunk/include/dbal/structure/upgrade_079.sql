#
# MySQL UniAdmin Upgrade File
#
# * $Id$
#
# --------------------------------------------------------
### Alter uniadmin_config

UPDATE `uniadmin_config` SET `config_value` = '0.8.0' WHERE `config_name` = 'UAVer' LIMIT 1;

UPDATE `uniadmin_config` SET `form_type` = 'select{Do Not check^0|Once a Day^24|Once a Week^168|Once a Month^720', `config_value` = '168'
	WHERE `config_name` = 'check_updates' LIMIT 1;

INSERT INTO `uniadmin_config` (`config_name`, `config_value`, `form_type`) VALUES
	('versioncache', '', 'hidden');

# --------------------------------------------------------
### Alter uniadmin_settings

INSERT INTO `uniadmin_settings` (`set_name` , `set_value` , `enabled` , `section` , `form_type` ) VALUES
	('ROSTERURL', 'http://yourdomain.com/roster', '0', 'options', 'text{250|50'),
	('UNIADMINURL', 'http://yourdomain.com/uniadmin', '0', 'options', 'text{250|50'),
	('USERAGENT','UniUploader 2.0 (UU 2.6.9; English)','0', 'options', 'text{250|50'),
	('UPERRPOPUP','1','0', 'options', 'radio{yes^1|no^0'),
	('CLOSEAFUPD','0','0', 'options', 'radio{yes^1|no^0'),
	('CLOSEAFLAU','0','0', 'options', 'radio{yes^1|no^0'),

	('AUTOSYNCIN','0','0', 'updater', 'text{250|50'),
	('UPDATESURL', 'http://www.wowroster.net/uniuploader_updater2/update.php', '0', 'updater', 'text{250|50'),
	('UUAUTOUPDATERCHECK','0','0', 'updater', 'radio{yes^1|no^0'),

	('STOREPWSECURE','1','0', 'advanced', 'radio{yes^1|no^0'),
	('USEAPPDATA','0','0', 'advanced', 'radio{yes^1|no^0'),
	('CLOSETOSYSTRAY','0','0', 'advanced', 'radio{yes^1|no^0'),

	('UPLOADSCREENSHOTS','1','0', 'settings', 'radio{yes^1|no^0'),
	('UPLOADSVS','1','0', 'settings', 'radio{yes^1|no^0'),
	('UPLOADALLACCOUNTS','1','0', 'settings', 'radio{yes^1|no^0');

# --------------------------------------------------------
### Alter uniadmin_addons

ALTER TABLE `uniadmin_addons`
  ADD `ace_title` VARCHAR(64) NOT NULL DEFAULT '';
