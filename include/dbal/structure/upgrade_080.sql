#
# MySQL UniAdmin Upgrade File
#
# * $Id$
#
# --------------------------------------------------------
### Alter uniadmin_config

UPDATE `uniadmin_config` SET `config_value` = '0.8.0' WHERE `config_name` = 'UAVer' LIMIT 1;

ALTER TABLE `uniadmin_addons` ADD `ace_title` VARCHAR( 64 ) NOT NULL DEFAULT '';