#
# MySQL UniAdmin Upgrade File
#
# * $Id$
#
# --------------------------------------------------------
### Alter uniadmin_config

UPDATE `uniadmin_config` SET `config_value` = '0.7.8' WHERE `config_name` = 'UAVer' LIMIT 1;
