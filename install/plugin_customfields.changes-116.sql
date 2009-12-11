ALTER TABLE `glpi_plugin_customfields_fields` CHANGE `system_name` `system_name` varchar(40) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_customfields_fields` CHANGE `label` `label` varchar(70) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_customfields_fields` CHANGE `default_value` `default_value` varchar(255) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_customfields_fields` CHANGE `dropdown_table` `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_customfields_dropdowns` CHANGE `system_name` `system_name` varchar(40) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_customfields_dropdowns` CHANGE `label` `label` varchar(70) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_customfields_dropdowns` CHANGE `dropdown_table` `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL;
UPDATE `glpi_plugin_customfields_fields` SET `default_value`=NULL WHERE `default_value`='';

