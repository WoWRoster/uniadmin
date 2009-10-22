#
# MySQL UniAdmin DB Structure
#
# $Id$
#
# --------------------------------------------------------
### Table structure for addons

DROP TABLE IF EXISTS `uniadmin_addons`;
CREATE TABLE IF NOT EXISTS `uniadmin_addons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_uploaded` int(11) NOT NULL DEFAULT '0',
  `version` varchar(64) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL DEFAULT '',
  `file_name` varchar(250) NOT NULL DEFAULT '',
  `homepage` varchar(250) NOT NULL DEFAULT '',
  `notes` mediumtext,
  `toc` mediumint(9) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `requiredoff` tinyint(1) NOT NULL DEFAULT '0',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `full_path` tinyint(1) NOT NULL DEFAULT '0',
  `ace_title` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `addon_name` (`name`)
);

# --------------------------------------------------------
### Table structure for addondel


DROP TABLE IF EXISTS `uniadmin_addondel`;
CREATE TABLE `uniadmin_addondel` (
  `id` int(11) NOT NULL auto_increment,
  `dir_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `dir_name` (`dir_name`)
);


# --------------------------------------------------------
### Table structure for config

DROP TABLE IF EXISTS `uniadmin_config`;
CREATE TABLE `uniadmin_config` (
  `config_name` varchar(255) NOT NULL,
  `config_value` varchar(255) default NULL,
  `form_type` mediumtext,
  PRIMARY KEY  (`config_name`)
);


# --------------------------------------------------------
### Table structure for files

DROP TABLE IF EXISTS `uniadmin_files`;
CREATE TABLE `uniadmin_files` (
  `id` int(11) NOT NULL auto_increment,
  `addon_id` int(11) NOT NULL,
  `filename` varchar(250) NOT NULL default '',
  `md5sum` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `addon_id` (`addon_id`)
);


# --------------------------------------------------------
### Table structure for logos


DROP TABLE IF EXISTS `uniadmin_logos`;
CREATE TABLE `uniadmin_logos` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(250) NOT NULL default '',
  `updated` int(11) NOT NULL default '0',
  `logo_num` int(11) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '0',
  `md5` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
);


# --------------------------------------------------------
### Table structure for settings


DROP TABLE IF EXISTS `uniadmin_settings`;
CREATE TABLE `uniadmin_settings` (
  `id` int(11) NOT NULL auto_increment,
  `set_name` varchar(250) NOT NULL default '',
  `set_value` varchar(250) NOT NULL default '',
  `enabled` tinyint(1) NOT NULL default '0',
  `section` varchar(64) NOT NULL,
  `form_type` mediumtext,
  PRIMARY KEY  (`id`)
);


# --------------------------------------------------------
### Table structure for stats


DROP TABLE IF EXISTS `uniadmin_stats`;
CREATE TABLE `uniadmin_stats` (
  `id` int(11) NOT NULL auto_increment,
  `ip_addr` varchar(30) NOT NULL default '',
  `host_name` varchar(250) NOT NULL default '',
  `action` varchar(250) NOT NULL default '',
  `time` varchar(15) NOT NULL default '',
  `user_agent` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`id`)
);


# --------------------------------------------------------
### Table structure for svlist


DROP TABLE IF EXISTS `uniadmin_svlist`;
CREATE TABLE `uniadmin_svlist` (
  `id` int(11) NOT NULL auto_increment,
  `sv_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `sv_name` (`sv_name`)
);


# --------------------------------------------------------
### Table structure for users


DROP TABLE IF EXISTS `uniadmin_users`;
CREATE TABLE `uniadmin_users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `password` varchar(32) NOT NULL,
  `level` int(11) NOT NULL default '0',
  `language` varchar(32) NOT NULL,
  `user_style` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
);