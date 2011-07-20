CREATE TABLE `glpi_plugin_customfields_multiselect` (`ID` INT(11) NOT NULL AUTO_INCREMENT, `field` INT(11) NOT NULL, `device` INT(11) NOT NULL, `item` INT(11) NOT NULL, PRIMARY KEY (`ID`)) ENGINE = MYISAM;
ALTER TABLE `glpi_plugin_customfields_fields` ADD `location` smallint(6) NOT NULL DEFAULT '0';
