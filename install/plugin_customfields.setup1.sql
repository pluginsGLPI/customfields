DROP TABLE IF EXISTS `glpi_plugin_customfields`;
CREATE TABLE `glpi_plugin_customfields` (
	`ID` int(11) NOT NULL auto_increment,
	`device_type` int(11) NOT NULL default '0',
	`enabled` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `glpi_plugin_customfields` (`device_type`,`enabled`) VALUES ('-1','118');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('1');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('41');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('4');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('6');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('39');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('20');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('2');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('42');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('5');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('3');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('11');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('17');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('23');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('16');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('7');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('8');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('10');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('13');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('15');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('27');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('28');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('9');
INSERT INTO `glpi_plugin_customfields` (`device_type`,`enabled`) VALUES ('24',1);
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('501');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('502');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('503');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('504');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('505');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('506');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('507');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('508');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('509');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('510');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('511');
INSERT INTO `glpi_plugin_customfields` (`device_type`) VALUES ('512');
