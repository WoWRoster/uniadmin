#
# MySQL UniAdmin Upgrade File
#
# * $Id$
#
# --------------------------------------------------------
### Alter uniadmin_config

UPDATE `uniadmin_config` SET `config_value` = '0.7.7' WHERE `config_name` = 'UAVer' LIMIT 1;

ALTER TABLE `uniadmin_config` ORDER BY `config_name`;


# --------------------------------------------------------
### Alter uniadmin_settings

ALTER TABLE `uniadmin_settings` CHANGE `enabled` `enabled` tinyint(1) NOT NULL default '0';

INSERT INTO `uniadmin_settings` ( `id` , `set_name` , `set_value` , `enabled` , `section` , `form_type` ) VALUES
  (NULL , 'ALLOWADDONDEL', '1', '0', 'updater', 'radio{yes^1|no^0'),
  (NULL , 'HOMEURL', 'http://yourdomain.com', '0', 'options', 'text{250|50'),
  (NULL , 'FORUMURL', 'http://yourdomain.com/forum', '0', 'options', 'text{250|50');


# --------------------------------------------------------
### Table structure for addondel


DROP TABLE IF EXISTS `uniadmin_addondel`;
CREATE TABLE `uniadmin_addondel` (
  `id` int(11) NOT NULL auto_increment,
  `dir_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `dir_name` (`dir_name`)
);
