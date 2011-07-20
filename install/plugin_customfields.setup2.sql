DROP TABLE IF EXISTS `glpi_plugin_customfields_fields`;
CREATE TABLE `glpi_plugin_customfields_fields` (
        `ID` int(11) NOT NULL auto_increment,
	`device_type` int(11) NOT NULL default '0',
        `system_name` varchar(40) collate utf8_unicode_ci default NULL,
        `label` varchar(70) collate utf8_unicode_ci default NULL,
        `data_type` varchar(30) collate utf8_unicode_ci NOT NULL default 'int(11)',
        `sort_order` smallint(6) NOT NULL default '0',
        `default_value` varchar(255) collate utf8_unicode_ci default NULL,
        `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL,
        `deleted` smallint(6) NOT NULL DEFAULT '0',
        `sopt_pos` int(11) NOT NULL DEFAULT '0',
        `required` smallint(6) NOT NULL DEFAULT '0',
        `entities` VARCHAR(255) NOT NULL DEFAULT '*',
        `restricted` smallint(6) NOT NULL DEFAULT '0',
        `unique` smallint(6) NOT NULL DEFAULT '0',
        PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `glpi_plugin_customfields_dropdowns`;
CREATE TABLE `glpi_plugin_customfields_dropdowns` (
        `ID` int(11) NOT NULL auto_increment,
        `system_name` varchar(40) collate utf8_unicode_ci default NULL,
        `label` varchar(70) collate utf8_unicode_ci default NULL,
        `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL,
        `has_entities` smallint(6) NOT NULL default '0',
        `is_tree` smallint(6) NOT NULL default '0',
        PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

