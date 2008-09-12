#
# MySQL UniAdmin Upgrade File
#
# * $Id$
#
# --------------------------------------------------------
### Alter uniadmin_config

UPDATE `uniadmin_config` SET `config_value` = '0.7.9' WHERE `config_name` = 'UAVer' LIMIT 1;

INSERT INTO `uniadmin_settings` ( `id` , `set_name` , `set_value` , `enabled` , `section` , `form_type` ) VALUES
  (NULL , 'PURGEFIRST','0','0', 'advanced', 'radio{yes^1|no^0');
