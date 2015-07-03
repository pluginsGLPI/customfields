<?php

/*
----------------------------------------------------------------------
GLPI - Gestionnaire Libre de Parc Informatique
Copyright (C) 2003-2009 by the INDEPNET Development Team.

http://indepnet.net/   http://glpi-project.org
----------------------------------------------------------------------

LICENSE

This file is part of GLPI.

GLPI is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
------------------------------------------------------------------------
*/

// ----------------------------------------------------------------------
// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Installation scripts.
// ----------------------------------------------------------------------
if (! defined ( 'GLPI_ROOT' )) {
	die ( 'Sorry. You can\'t access this file directly.' );
}

/**
 * Install custom fields and do database work
 *
 * @return bool
 */
function pluginCustomfieldsInstall() {
	global $DB, $LANG;
	
	// Upgrade process if needed
	
	if (TableExists ( "glpi_plugin_customfields" )) {
		
		// <1.0.1
		
		if (TableExists ( "glpi_plugin_customfields_fields" )) {
			if (! FieldExists ( 'glpi_plugin_customfields_fields', 'deleted' )) {
				$dbversion = 100; // the version needs to be less than 101
			}
		}
		
		// <1.1.2
		
		if (TableExists ( "glpi_plugin_customfields_fields" )) {
			if (! FieldExists ( 'glpi_plugin_customfields_fields', 'required' )) {
				$dbversion = 101;
			}
		}
		
		// <1.1.3
		
		if (TableExists ( "glpi_plugin_customfields_fields" )) {
			if (! FieldExists ( 'glpi_plugin_customfields_fields', 'entities' )) {
				$dbversion = 112;
			}
		}
		
		// <1.1.6
		if (TableExists ( "glpi_plugin_customfields_fields" )) {
			if (! FieldExists ( 'glpi_plugin_customfields_fields', 'itemtype' )) {
				$dbversion = 113;
			}
		}
		
		// <1.1.7
		if (TableExists ( "glpi_plugin_customfields_fields" )) {
			if (! FieldExists ( 'glpi_plugin_customfields_fields', 'unique' )) {
				$dbversion = 116;
			}
		}

		// <1.2
		
		if (TableExists ( "glpi_plugin_customfields" )) {
			$query = "SELECT `enabled`
	                    FROM `glpi_plugin_customfields`
	                    WHERE `device_type`='-1'";
			$result = $DB->query ( $query );
			$data = $DB->fetch_array ( $result );
			if (isset($data['enabled']) && $data['enabled'] == 117) {
				$dbversion = 117;
			}
		}
		
		// >= 1.2
		if (TableExists ( "glpi_plugin_customfields_itemtypes" )) {
			// Check the version of the database tables.
			$query = "SELECT `enabled`
	                    FROM `glpi_plugin_customfields_itemtypes`
	                    WHERE itemtype='Version'
	                    ORDER BY `enabled` DESC
	                    LIMIT 1;";
			$result = $DB->query ( $query );
			$data = $DB->fetch_array ( $result );
			// Version of the last modification to the plugin tables' structure
			$dbversion = $data ['enabled'];
			
			if ($dbversion == 12) {
				
				$dbversion = 120;
	
	         }
		}

		return pluginCustomfieldsUpgrade ($dbversion);
	} else {
		
		// Customfields type configuration table
		
		$query = "CREATE TABLE
                  IF NOT EXISTS `glpi_plugin_customfields_itemtypes` (
                  `id` int(11) NOT NULL auto_increment,
                  `itemtype` VARCHAR(100) NOT NULL default '',
                  `enabled` smallint(6) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$DB->query ( $query ) or die ( $DB->error () );
		
		// Add version string
		
		$query = "INSERT INTO `glpi_plugin_customfields_itemtypes`
                      (`itemtype`,`enabled`)
               VALUES ('Version', '160')";
		$DB->query ( $query ) or die ( $DB->error () );
		
		// Add supported types
		
		$query = "INSERT INTO `glpi_plugin_customfields_itemtypes`
                       (`itemtype`)
                VALUES ('Computer'), ('ComputerDisk'), ('Monitor'),
                       ('Software'), ('SoftwareVersion'),
                       ('SoftwareLicense'), ('NetworkEquipment'),
                       ('NetworkPort'), ('Peripheral'), ('Printer'),
                       ('CartridgeItem'), ('ConsumableItem'), ('Phone'),
                       ('Ticket'), ('Contact'), ('Supplier'), ('Contract'),
                       ('Document'), ('User'), ('Group'), ('Entity'),
                       ('DeviceProcessor'), ('DeviceMemory'),
                       ('DeviceMotherboard'), ('DeviceNetworkCard'),
                       ('DeviceHardDrive'),
                       ('DeviceDrive'), ('DeviceControl'),
                       ('DeviceGraphicCard'),
                       ('DeviceSoundCard'), ('DeviceCase'),
                       ('DevicePowerSupply'),
                       ('DevicePci'), ('Budget'), ('ComputerVirtualMachine')";
		$DB->query ( $query ) or die ( $DB->error () );
		
		// Customfields field configuration table
		
		$query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_customfields_fields` (
                  `id` int(11) NOT NULL auto_increment,
                  `itemtype` VARCHAR(100) NOT NULL default '',
                  `system_name` varchar(40) collate utf8_unicode_ci
                    default NULL,
                  `label` varchar(70) collate utf8_unicode_ci default NULL,
                  `data_type` varchar(30) collate utf8_unicode_ci NOT NULL
                    default 'int(11)',
                  `sort_order` smallint(6) NOT NULL default '0',
                  `default_value` varchar(255) collate utf8_unicode_ci
                    default NULL,
                  `dropdown_table` varchar(255) collate utf8_unicode_ci
                    default NULL,
                  `deleted` smallint(6) NOT NULL DEFAULT '0',
                  `sopt_pos` int(11) NOT NULL DEFAULT '0',
                  `required` smallint(6) NOT NULL DEFAULT '0',
                  `entities` VARCHAR(255) NOT NULL DEFAULT '*',
                  `restricted` smallint(6) NOT NULL DEFAULT '0',
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM
                DEFAULT
                  CHARSET=utf8
                  COLLATE=utf8_unicode_ci
                  AUTO_INCREMENT=3";
		
		$DB->query ( $query ) or die ( $DB->error () );
		
		// Custom drop downs
		
		$query = "CREATE TABLE
                  IF NOT EXISTS `glpi_plugin_customfields_dropdowns` (
                  `id` int(11) NOT NULL auto_increment,
                  `system_name` varchar(40) collate utf8_unicode_ci default NULL,
                  `name` varchar(70) collate utf8_unicode_ci default NULL,
                  `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL,
                  `has_entities` smallint(6) NOT NULL default '0',
                  `is_tree` smallint(6) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$DB->query ( $query ) or die ( $DB->error () );
		
		// Custom drop down items
		
		// This query is also needed (and duplicated) in plugin_customfields_upgradeto150
		// we MUST update it too if this query changes !!
		$query = "CREATE TABLE
                  IF NOT EXISTS `glpi_plugin_customfields_dropdownsitems` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `entities_id` int(11) NOT NULL DEFAULT '0',
                  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                  `name` varchar(255) CHARACTER SET utf8
                    COLLATE utf8_unicode_ci DEFAULT NULL,
                  `completename` TEXT CHARACTER SET utf8
                    COLLATE utf8_unicode_ci DEFAULT NULL,
                  `level` INTEGER NOT NULL DEFAULT '0',
                  `plugin_customfields_dropdowns_id` int(11) NOT NULL
                    DEFAULT '0',
                  `plugin_customfields_dropdownsitems_id` int(11) NOT NULL
                    DEFAULT '0',
                  `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                  PRIMARY KEY (`id`)
               ) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1";
		$DB->query ( $query ) or die ( $DB->error () );
		
		// Dropdown display preferences
		
		$query = "INSERT IGNORE `glpi_displaypreferences`
                  VALUES (NULL,'PluginCustomfieldsDropdownsItem','3','1','0');";
		$DB->query ( $query ) or die ( $DB->error () );
		
		$query = "INSERT IGNORE `glpi_displaypreferences`
                  VALUES (NULL,'PluginCustomfieldsDropdownsItem','4','2','0');";
		$DB->query ( $query ) or die ( $DB->error () );
		
		// Profiles
		
		$query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_customfields_profiles` (
                  `id` int(11) NOT NULL ,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$DB->query ( $query ) or die ( $DB->error () );
		
		return true;
	}
}

/**
 * Upgrade custom fields plugin
 * @return bool
 */
function plugin_CustomFields_Upgrade() {
	global $DB, $LANG;
	
	// <1.0.1
	
	if (TableExists ( "glpi_plugin_customfields_fields" )) {
		if (! FieldExists ( 'glpi_plugin_customfields_fields', 'deleted' )) {
			plugin_customfields_upgradeto101 ();
		}
	}
	
	// <1.1.2
	
	if (TableExists ( "glpi_plugin_customfields_fields" )) {
		if (! FieldExists ( 'glpi_plugin_customfields_fields', 'required' )) {
			plugin_customfields_upgradeto112 ();
		}
	}
	
	// <1.1.3
	
	if (TableExists ( "glpi_plugin_customfields_fields" )) {
		if (! FieldExists ( 'glpi_plugin_customfields_fields', 'entities' )) {
			plugin_customfields_upgradeto113 ();
		}
	}
	
	// <1.1.6
	if (TableExists ( "glpi_plugin_customfields_fields" )) {
		if (! FieldExists ( 'glpi_plugin_customfields_fields', 'itemtype' )) {
			plugin_customfields_upgradeto116 ();
		}
	}
	
	// <1.1.7
	
	if (TableExists ( "glpi_plugin_customfields_fields" )) {
		if (! FieldExists ( 'glpi_plugin_customfields_fields', 'unique' )) {
			plugin_customfields_upgradeto117 ();
		}
	}
	
	// <1.2
	
	if (! TableExists ( "glpi_plugin_customfields_itemtypes" )) {
		plugin_customfields_upgradeto12 ();
	}
	
	plugin_customfields_upgradeto110 (); // must be at the end : itemtype
	
	return true;
}

/**
 * Uninstall custom fields plugin
 * @return bool
 */
function pluginCustomfieldsUninstall() {
	global $LANG, $DB;
	
	// Cancel search in session (if search on customfields is in progress
	// before uninstall)
	Search::resetSaveSearch ();
	
	// Get customfields itemtypes
	$query = "SELECT `itemtype`
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` <> 'Version'";
	
	// Remove data tables
	
	$itemtypes = array ();
	
	if ($result = $DB->query ( $query )) {
		while ( $data = $DB->fetch_assoc ( $result ) ) {
			$itemtypes [] = $data ['itemtype'];
			$table = plugin_customfields_table ( $data ['itemtype'] );
			if ($table) {
				$query = "DROP TABLE IF EXISTS `$table`";
				$DB->query ( $query ) or die ( $DB->error () );
			}
		}
	}
	
	// Delete dropdown search option
	
	$query = "DELETE FROM glpi_displaypreferences 
      WHERE itemtype = 'PluginCustomfieldsDropdownsItem'";
	$DB->query ( $query ) or die ( $DB->error () );
	
	// Delete object searchoption for itemptype existing links
	
	$searchopts_keys = array ();
	
	foreach ( $itemtypes as $itemtype ) {
		$searchoptions = plugin_customfields_getAddSearchOptions ( $itemtype );
		$searchopts_keys = array_merge ( array_keys ( $searchoptions ), $searchopts_keys );
	}
	
	$searchopts_keys_str = "'" . implode ( "', '", $searchopts_keys ) . "'";
	$query = "DELETE FROM glpi_displaypreferences
                           WHERE num IN ($searchopts_keys_str)";
	$DB->query ( $query ) or die ( $DB->error () );
	
	// TODO : The following query seems never used, but there is code using it !
	// Needs testing
	
	$query = "SELECT `dropdown_table`
             FROM `glpi_plugin_customfields_dropdowns`";
	
	// Delete custom dropdown tables
	
	if ($result = $DB->query ( $query )) {
		while ( $data = $DB->fetch_assoc ( $result ) ) {
			$table = $data ['dropdown_table'];
			if ($table != '') {
				$query = "DROP TABLE IF EXISTS `$table`";
				$DB->query ( $query ) or die ( $DB->error () );
			}
		}
	}
	
	// Drop additional tables
	
	$tables = array (
			'glpi_plugin_customfields_dropdowns',
			'glpi_plugin_customfields_dropdownsitems',
			'glpi_plugin_customfields_fields',
			'glpi_plugin_customfields_itemtypes',
			'glpi_plugin_customfields_profiles',
			'glpi_plugin_customfields' 
	);
	
	foreach ( $tables as $table ) {
		$DB->query ( "DROP TABLE `$table`" );
	}
	
	return true;
}

/**
 * Upgrade custom fields plugin
 *
 * @param $oldversion Version to upgrade from
 */
function pluginCustomfieldsUpgrade($oldversion) {
	global $DB;
	
	set_time_limit ( 300 );
	
	// Upgrade logging feature
	if ($oldversion < 101) {
		plugin_customfields_upgradeto101 ();
	}
	
	// Upgrade date fields to be compatible with GLPI 0.72+
	if ($oldversion < 110) {
		plugin_customfields_upgradeto110 ();
	}
	
	// Add a column to indicate if a field is required
	if ($oldversion < 112) {
		plugin_customfields_upgradeto112 ();
	}
	
	// Add a column to indicate which entities to show the field with
	// Remove column for hidden field, use blank in entites
	// field to replace this functionality
	// Add restricted field to allow field-based permissions
	if ($oldversion < 113) {
		plugin_customfields_upgradeto113 ();
	}
	
	// Upgrade fields to be compatable with mysql strict mode
	if ($oldversion < 116) {
		plugin_customfields_upgradeto116 ();
		
	}
	
	if ($oldversion < 117) {
		plugin_customfields_upgradeto117 ();
	}
	
	if ($oldversion < 120) {
		plugin_customfields_upgradeto12 ();
	}
	
	if ($oldversion < 150) {
		plugin_customfields_upgradeto150 ();
	}
	
	if ($oldversion < 160) {
		plugin_customfields_upgradeto160 ();
	}
	
	if ($oldversion < 161) {
		plugin_customfields_upgradeto161 ();
	}
	
	echo 'finished.<br />';
	Html::glpi_flush ();
	
	if (CUSTOMFIELDS_AUTOACTIVATE) {
		//plugin_customfields_activate_all_types ();
	}
	
	return true;
}

/**
 * Upgrade => 1.01
 */
function plugin_customfields_upgradeto101() {
	global $DB;
	echo 'Updating Custom Fields to 1.0.1 ... ';
	Html::glpi_flush ();
	
	$sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `deleted` smallint(6) NOT NULL DEFAULT '0',
           ADD `sopt_pos` int(11) NOT NULL DEFAULT '0'";
	$DB->query ( $sql ) or die ( $DB->error () );
	
	$sql = "UPDATE `glpi_plugin_customfields_fields`
           SET `sopt_pos` = `ID`"; // initialize sopt_pos to something unique
	$DB->query ( $sql ) or die ( $DB->error () );
	
	echo 'finished.';
	Html::glpi_flush ();
	
}

/**
 * Upgrade => 1.1
 */
function plugin_customfields_upgradeto110() {
	global $DB;
	echo 'Updating Custom Fields to 1.1.0 ... ';
	Html::glpi_flush ();
	
	$sql = "SELECT `itemtype`, `system_name`
           FROM `glpi_plugin_customfields_fields`
           WHERE `data_type` = 'date'";
	$result = $DB->query ( $sql ) or die ( $DB->error () );
	
	while ( $data = $DB->fetch_array ( $result ) ) {
		$table = plugin_customfields_table ( $data ['itemtype'] );
		$field = $data ['system_name'];
		$sql = "ALTER TABLE `$table`
              CHANGE `$field` `$field` DATE NULL DEFAULT NULL";
		$DB->query ( $sql ) or die ( $DB->error () );
		
		$sql = "UPDATE `$table`
              SET `$field`= NULL
              WHERE `$field` = '0000-00-00'";
		$DB->query ( $sql ) or die ( $DB->error () );
	}
	
	echo 'finished.<br />';
	Html::glpi_flush ();
}

/**
 * Upgrade => 1.12
 */
function plugin_customfields_upgradeto112() {
	global $DB;
	echo 'Updating Custom Fields to 1.1.2 ... ';
	Html::glpi_flush ();
	
	$sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `required` smallint(6) NOT NULL DEFAULT '0'";
	$DB->query ( $sql ) or die ( $DB->error () );
	
	$sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           CHANGE `device_type` `device_type` INT(11) NOT NULL DEFAULT '0'";
	$DB->query ( $sql ) or die ( $DB->error () );
	echo 'finished.<br />';
	Html::glpi_flush ();
}

/**
 * Upgrade => 1.13
 */
function plugin_customfields_upgradeto113() {
	global $DB;
	echo 'Updating Custom Fields to 1.1.3 ... ';
	Html::glpi_flush ();
	
	$sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `entities` VARCHAR(255) NOT NULL DEFAULT '*'";
	$DB->query ( $sql ) or die ( $DB->error () );
	
	$sql = "UPDATE `glpi_plugin_customfields_fields`
           SET `entities` = ''
           WHERE `hidden` = 1";
	$DB->query ( $sql ) or die ( $DB->error () );
	
	$sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           DROP `hidden`";
	$DB->query ( $sql ) or die ( $DB->error () );
	
	$sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `restricted` smallint(6) NOT NULL DEFAULT '0'";
	$DB->query ( $sql ) or die ( $DB->error () );
	
	if (! TableExists ( "glpi_plugin_customfields_profiledata" )) {
		
		$query = "CREATE TABLE
                  IF NOT EXISTS `glpi_plugin_customfields_profiledata` (
                  `ID` int(11) NOT NULL auto_increment,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  PRIMARY KEY (`ID`)
                ) ENGINE=MyISAM
                DEFAULT
                  CHARSET=utf8
                  COLLATE=utf8_unicode_ci
                  AUTO_INCREMENT=3";
		$DB->query ( $query ) or die ( $DB->error () );
	}
	echo 'finished.<br />';
	Html::glpi_flush ();
}

/**
 * Upgrade => 1.16
 */
function plugin_customfields_upgradeto116() {
	global $DB;
	echo 'Updating Custom Fields to 1.1.6 ... ';
	Html::glpi_flush ();
	
	// Save settings
	$sql = "SELECT `device_type`
           FROM `glpi_plugin_customfields`
           WHERE `enabled` = '1';";
	$result = $DB->query ( $sql );
	$enabled = array ();
	while ( $data = $DB->fetch_array ( $result ) ) {
		$enabled [] = $data ['device_type'];
	}
	
	// Upgrade
	$query = "DROP TABLE IF EXISTS `glpi_plugin_customfields`";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "CREATE TABLE `glpi_plugin_customfields` (
               `ID` int(11) NOT NULL auto_increment,
               `device_type` int(11) NOT NULL default '0',
               `enabled` smallint(6) NOT NULL default '0',
               PRIMARY KEY (`ID`)
             ) ENGINE=MyISAM
             DEFAULT
              CHARSET=utf8
              COLLATE=utf8_unicode_ci
              AUTO_INCREMENT=3";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "INSERT INTO `glpi_plugin_customfields`
                    (`device_type`,`enabled`)
             VALUES ('-1', '116')";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "INSERT INTO `glpi_plugin_customfields`
                    (`device_type`)
             VALUES ('1'), ('41'), ('4'), ('6'), ('39'), ('20'), ('2'),
             ('42'), ('5'), ('3'), ('11'),
                    ('17'), ('23'), ('16'), ('7'), ('8'), ('10'), ('13'),
                    ('15'), ('27'), ('28')";
	$DB->query ( $query ) or die ( $DB->error () );
	
	// Restore settings
	foreach ( $enabled as $device_type ) {
		$sql = "UPDATE `glpi_plugin_customfields`
              SET `enabled` = 1
              WHERE `device_type` = '$device_type';";
		$DB->query ( $sql ) or die ( $DB->error () );
	}
	
	$transform = array ();
	$transform ['general'] = 'VARCHAR(255) collate utf8_unicode_ci default NULL';
	$transform ['dropdown'] = 'INT(11) NOT NULL default \'0\'';
	$transform ['yesno'] = 'SMALLINT(6) NOT NULL default \'0\'';
	$transform ['text'] = 'TEXT collate utf8_unicode_ci';
	$transform ['notes'] = 'LONGTEXT collate utf8_unicode_ci';
	$transform ['number'] = 'INT(11) NOT NULL default \'0\'';
	$transform ['money'] = 'DECIMAL(20,4) NOT NULL default \'0.0000\'';
	
	$sql = "SELECT `device_type`, `system_name`, `data_type`
           FROM `glpi_plugin_customfields_fields`
           WHERE `deleted` = 0
                 AND `data_type` != 'sectionhead'
                 AND `data_type` != 'date'
           ORDER BY `device_type`, `sort_order`, `ID`";
	$result = $DB->query ( $sql ) or die ( $DB->error () );
	set_time_limit ( 300 );
	echo 'Updating Custom Fields...';
	
	while ( $data = $DB->fetch_array ( $result ) ) {
		echo '.';
		Html::glpi_flush ();
		$table = plugin_customfields_table ( $data ['device_type'] );
		$field = $data ['system_name'];
		$newtype = $transform [$data ['data_type']];
		$sql = "ALTER TABLE `$table`
              CHANGE `$field` `$field` $newtype";
		$DB->query ( $query ) or die ( $DB->error () );
		
		if (in_array ( $data ['data_type'], array (
				'general',
				'text',
				'notes' 
		) )) {
			$sql = "UPDATE `$table`
                 SET `$field` = NULL
                 WHERE `$field` = ''";
			$DB->query ( $sql ) or die ( $DB->error () );
		}
	}
	
	$query = "ALTER TABLE `glpi_plugin_customfields_fields`
             CHANGE `system_name` `system_name` varchar(40)
              collate utf8_unicode_ci default NULL,
             CHANGE `label` `label` varchar(70)
              collate utf8_unicode_ci default NULL,
             CHANGE `default_value` `default_value` varchar(255)
              collate utf8_unicode_ci default NULL,
             CHANGE `dropdown_table` `dropdown_table` varchar(255)
              collate utf8_unicode_ci default NULL";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "ALTER TABLE `glpi_plugin_customfields_dropdowns`
             CHANGE `system_name` `system_name` varchar(40)
              collate utf8_unicode_ci default NULL,
             CHANGE `label` `label` varchar(70)
              collate utf8_unicode_ci default NULL,
             CHANGE `dropdown_table` `dropdown_table` varchar(255)
              collate utf8_unicode_ci default NULL";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "UPDATE `glpi_plugin_customfields_fields`
             SET `default_value` = NULL
             WHERE `default_value` = ''";
	$DB->query ( $query ) or die ( $DB->error () );
	
	echo 'finished.<br />';
	Html::glpi_flush ();
}

/**
 * Upgrade => 1.17
 */
function plugin_customfields_upgradeto117() {
	global $DB;
	echo 'Updating Custom Fields to 1.1.7 ... ';
	Html::glpi_flush ();
	
	$sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `unique` smallint(6) NOT NULL DEFAULT '0'";
	$DB->query ( $sql ) or die ( $DB->error () );
	
	// Save settings
	$sql = "SELECT `device_type`
           FROM `glpi_plugin_customfields`
           WHERE `enabled` = '1';";
	$result = $DB->query ( $sql );
	$enabled = array ();
	while ( $data = $DB->fetch_array ( $result ) ) {
		$enabled [] = $data ['device_type'];
	}
	
	// Upgrade
	$query = "DROP TABLE IF EXISTS `glpi_plugin_customfields`";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "CREATE TABLE `glpi_plugin_customfields` (
               `ID` int(11) NOT NULL auto_increment,
               `device_type` int(11) NOT NULL default '0',
               `enabled` smallint(6) NOT NULL default '0',
               PRIMARY KEY  (`ID`)
            ) ENGINE=MyISAM
            DEFAULT
              CHARSET=utf8
              COLLATE=utf8_unicode_ci
              AUTO_INCREMENT=3";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "INSERT INTO `glpi_plugin_customfields`
               (`device_type`,`enabled`)
            VALUES ('-1', '117'),
                   ('24', '1')";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "INSERT INTO `glpi_plugin_customfields`
                   (`device_type`)
            VALUES ('1'), ('41'), ('4'), ('6'), ('39'), ('20'), ('2'), ('42'), ('5'), ('3'),
            ('11'), ('17'), ('23'), ('16'),
                    ('7'), ('8'), ('10'), ('13'), ('15'), ('27'), ('28') , ('501'), ('502'),
                    ('503'), ('504'), ('505'),
                    ('506'), ('507'), ('508'), ('509'), ('510'), ('511'), ('512')";
	$DB->query ( $query ) or die ( $DB->error () );
	
	// Restore settings
	foreach ( $enabled as $device_type ) {
		$sql = "UPDATE `glpi_plugin_customfields`
              SET `enabled` = 1
              WHERE `device_type` = '$device_type';";
		$DB->query ( $sql ) or die ( $DB->error () );
	}
	echo 'finished.<br />';
	Html::glpi_flush ();
}

/**
 * Upgrade => 1.2
 */
function plugin_customfields_upgradeto12() {
	global $DB;
	echo 'Updating Custom Fields to 1.2.0 ... ';
	Html::glpi_flush ();
	
	$glpi_tables = array (
			'glpi_plugin_customfields_software' => 'glpi_plugin_customfields_softwares',
			'glpi_plugin_customfields_networking' => 'glpi_plugin_customfields_networkequipments',
			'glpi_plugin_customfields_enterprises' => 'glpi_plugin_customfields_suppliers',
			'glpi_plugin_customfields_docs' => 'glpi_plugin_customfields_documents',
			'glpi_plugin_customfields_tracking' => 'glpi_plugin_customfields_tickets',
			'glpi_plugin_customfields_user' => 'glpi_plugin_customfields_users',
			'glpi_plugin_customfields_networking_ports' => 'glpi_plugin_customfields_networkports' 
	);
	
	foreach ( $glpi_tables as $oldtable => $newtable ) {
		if (! TableExists ( "$newtable" ) && TableExists ( "$oldtable" )) {
			$query = "RENAME TABLE `$oldtable` TO `$newtable`";
			$DB->query ( $query ) or die ( $DB->error () );
		}
	}
	
	if (TableExists ( "glpi_plugin_customfields" )) {
		$query = "RENAME TABLE `glpi_plugin_customfields`
         TO `glpi_plugin_customfields_itemtypes`";
		$DB->query ( $query ) or die ( $DB->error () );
		
		$query = "ALTER TABLE `glpi_plugin_customfields_itemtypes`
                CHANGE `ID` `id` int(11) NOT NULL auto_increment,
                CHANGE `device_type` `itemtype` VARCHAR(100) NOT NULL
                  default ''";
		$DB->query ( $query ) or die ( $DB->error () );
		
		$tables = array (
				'glpi_plugin_customfields_itemtypes' 
		);
		Plugin::migrateItemType ( array (
				- 1 => 'Version' 
		), array (), $tables );
		
		$query = "SELECT `itemtype`
                FROM `glpi_plugin_customfields_itemtypes`
                WHERE `itemtype` <> 'Version' 
				AND `enabled` = 1";
		$result = $DB->query ( $query );
		
		$enabled = array ();
		while ( $data = $DB->fetch_array ( $result ) ) {
			$enabled [] = $data ['itemtype'];
			$table = plugin_customfields_table ( $data ['itemtype'] );
			if (TableExists ( $table )) {
				$query = "ALTER TABLE `$table`
                       CHANGE `ID` `id` int(11) NOT NULL auto_increment";
				$DB->query ( $query ) or die ( $DB->error () );
			}
		}
		
		foreach ( $enabled as $itemtype ) {
			$sql = "UPDATE `glpi_plugin_customfields_itemtypes`
                  SET `enabled` = 1
                  WHERE `itemtype` = '$itemtype';";
			$DB->query ( $sql ) or die ( $DB->error () );
		}
	}
	
	if (TableExists ( "glpi_plugin_customfields_dropdowns" )) {
		$query = "ALTER TABLE `glpi_plugin_customfields_dropdowns`
                CHANGE `ID` `id` int(11) NOT NULL auto_increment";
		$DB->query ( $query ) or die ( $DB->error () );
	}
	
	if (TableExists ( "glpi_plugin_customfields_fields" )) {
		$query = "ALTER TABLE `glpi_plugin_customfields_fields`
                CHANGE `ID` `id` int(11) NOT NULL auto_increment,
                CHANGE `device_type` `itemtype` VARCHAR(100) NOT NULL
                  default ''";
		$DB->query ( $query ) or die ( $DB->error () );
		
		$tables = array (
				'glpi_plugin_customfields_fields' 
		);
		Plugin::migrateItemType ( array (
				- 1 => 'Version' 
		), array (), $tables );
	}
	
	if (TableExists ( "glpi_plugin_customfields_profiledata" )) {
		$query = "RENAME TABLE `glpi_plugin_customfields_profiledata` 
         TO `glpi_plugin_customfields_profiles`";
		$DB->query ( $query ) or die ( $DB->error () );
		$query = "ALTER TABLE `glpi_plugin_customfields_profiles`
                CHANGE `ID` `id` int(11) NOT NULL auto_increment";
		$DB->query ( $query ) or die ( $DB->error () );
	}
	echo 'finished.<br />';
	Html::glpi_flush ();
}

/**
 * update to 1.50 
 */
function plugin_customfields_upgradeto150() {
	global $DB;
	echo 'Updating Custom Fields to 1.5.0 ... ';
	Html::glpi_flush ();
	
	if (TableExists ( "glpi_plugin_customfields_dropdowns" )) {
		
		// This query is a duplicate of the one found in install process
		$query = "CREATE TABLE
                  IF NOT EXISTS `glpi_plugin_customfields_dropdownsitems` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `entities_id` int(11) NOT NULL DEFAULT '0',
                  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                  `name` varchar(255) CHARACTER SET utf8
                    COLLATE utf8_unicode_ci DEFAULT NULL,
                  `completename` TEXT CHARACTER SET utf8
                    COLLATE utf8_unicode_ci DEFAULT NULL,
                  `level` INTEGER NOT NULL DEFAULT '0',
                  `plugin_customfields_dropdowns_id` int(11) NOT NULL
                    DEFAULT '0',
                  `plugin_customfields_dropdownsitems_id` int(11) NOT NULL
                    DEFAULT '0',
                  `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
				  `old_id`  int(11) NOT NULL,
                  PRIMARY KEY (`id`)
               ) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1";
		$DB->query ( $query ) or die ( $DB->error () );
		
		$query = "ALTER TABLE `glpi_plugin_customfields_dropdowns`
                CHANGE `label` `name` varchar(70) default NULL";
		$DB->query ( $query ) or die ( $DB->error () );
		
		$query = "SELECT `id`, `dropdown_table`, `has_entities`, `is_tree`
                FROM `glpi_plugin_customfields_dropdowns`";
		$result = $DB->query ( $query ) or die ( $DB->error () );
		while ( $data = $DB->fetch_array ( $result ) ) {
			switch (true) {
				case $data ['has_entities'] == '1' && $data ['is_tree'] == '1' :
					$query = "INSERT INTO `glpi_plugin_customfields_dropdownsitems`
                  (`entities_id`,
                   `is_recursive`,
                   `name`, 
                   `completename`,
                   `level`,
                   `plugin_customfields_dropdowns_id`,
                   `plugin_customfields_dropdownsitems_id`,
                   `comment`,
				   `old_id`
                   )
                  SELECT
                     `FK_entities`,
                     '0', 
                     `name`,
                     `completename`,
                     `level`,
                     '" . $data ['id'] . "',
                     `parentID`,
                     `comments`,
                     `id`
                  FROM `" . $data ['dropdown_table'] . "`";
					$DB->query ( $query ) or die ( $DB->error () );
					break;
				case $data ['has_entities'] == '1' && $data ['is_tree'] == '0' :
					$query = "INSERT INTO `glpi_plugin_customfields_dropdownsitems` 
                  (`entities_id`, 
                   `is_recursive`,
                   `name`,
				   `completename`,
                   `plugin_customfields_dropdowns_id`,
                   `comment`,
				   `old_id`
                   )
                  SELECT 
                     `FK_entities`,
                     '0', 
                     `name`,
					 `name`,
                     '" . $data ['id'] . "',
                     `comments`,
                     `id`
                  FROM `" . $data ['dropdown_table'] . "`";
					$DB->query ( $query ) or die ( $DB->error () );
					break;
				case $data ['has_entities'] == '0' && $data ['is_tree'] == '1' :
					$query = "INSERT INTO `glpi_plugin_customfields_dropdownsitems`
                  (`is_recursive`,
				   `name`, 
                   `completename`,
                   `level`,
                   `plugin_customfields_dropdowns_id`,
                   `plugin_customfields_dropdownsitems_id`,
                   `comment`,
				   `old_id`
                   )
                  SELECT 
					 '1',
                     `name`,
                     `completename`,
                     `level`,
                     '" . $data ['id'] . "',
                     `parentID`,
                     `comments`,
                     `id`
                  FROM `" . $data ['dropdown_table'] . "`";
					$DB->query ( $query ) or die ( $DB->error () );
					break;
				case $data ['has_entities'] == '0' && $data ['is_tree'] == '0' :
					$query = "INSERT INTO `glpi_plugin_customfields_dropdownsitems` 
                  (`is_recursive`,
				   `name`, 
				   `completename`,
                   `plugin_customfields_dropdowns_id`,
                   `comment`,
				   `old_id`
                   )
                  SELECT 
					 '1',
                     `name`,
					 `name`,
                     '" . $data ['id'] . "',
                     `comments`,
                     `id`
                  FROM `" . $data ['dropdown_table'] . "`";
					$DB->query ( $query ) or die ( $DB->error () );
					break;
			}
			
			
			// Drop old dropdown tables
			$query = "DROP TABLE " . $data ['dropdown_table'];
			$DB->query ( $query ) or die ( $DB->error () );
		}
		
		//Translate dropdown IDs used by assets from old to new ones
		$query = "SELECT `itemtype` 
			FROM `glpi_plugin_customfields_itemtypes`
			WHERE `itemtype` <> 'Version'";
		$result = $DB->query ( $query ) or die ( $DB->error () );
		include ('../plugins/customfields/inc/field.class.php');
		while ( $data = $DB->fetch_array ( $result ) ) {
			require ('../plugins/customfields/inc/virtual_classes.php'); 
			$customFieldsAsset = "PluginCustomfields" . $data['itemtype'];
			$customFieldsAssetItem = new $customFieldsAsset;
			$table = $customFieldsAssetItem->getTable ();
			if ( TableExists ($table) ) {
				$query2 = "SELECT `system_name` 
					FROM `glpi_plugin_customfields_dropdowns`";
				$result2 = $DB->query ( $query2 ) or die ( $DB->error () );
				while ( $data2 = $DB->fetch_array ( $result2 ) ) {
					if (FieldExists($table, $data2 ['system_name'])) {
						$updateQuery = "UPDATE `" . $table . "` as `cf`
								LEFT JOIN `glpi_plugin_customfields_dropdowns` as `dd`
								ON (`dd`.`system_name` = '" . $data2 ['system_name'] . "')
								LEFT JOIN `glpi_plugin_customfields_dropdownsitems` as `ddi`
								ON (`dd`.`id` = `ddi`.`plugin_customfields_dropdowns_id` AND `cf`.`" . $data2 ['system_name'] . "`=`ddi`.`old_id`)
								SET `cf`.`" . $data2 ['system_name'] . "` = `ddi`.`id`";
						$DB->query ( $updateQuery ) or die ( $DB->error () );
					}
				}
			}
		}
		
		// Rebuild parent relationship for tree dropdowns
		$query = "UPDATE `glpi_plugin_customfields_dropdownsitems` AS `dst`
              LEFT JOIN `glpi_plugin_customfields_dropdownsitems` AS `src`
              ON (`src`.`plugin_customfields_dropdowns_id` = `dst`.`plugin_customfields_dropdowns_id` 
              AND `src`.`entities_id` = `dst`.`entities_id` 
              AND CONCAT(`src`.`completename`, ' > ', `dst`.`name`) = `dst`.`completename`
              AND `src`.`level` + 1 = `dst`.`level`
              )
              SET `dst`.`plugin_customfields_dropdownsitems_id` = `src`.`id`";
		$DB->query ( $query ) or die ( $DB->error () );
	}
	
	// Drop temporary column used to translate dropdowns IDs in assets
	$query = "ALTER TABLE `glpi_plugin_customfields_dropdownsitems`
          DROP COLUMN `old_id`";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "UPDATE `glpi_plugin_customfields_itemtypes`
             SET enabled=150
             WHERE itemtype='Version'";
	$DB->query ( $query ) or die ( $DB->error () );
	
	echo 'finished.<br />'; 
	Html::glpi_flush ();
	
}

/**
 * Update to version 1.60
 */
function plugin_customfields_upgradeto160() {
	global $DB;
	echo 'Updating Custom Fields to 1.6.0 ... ';
	Html::glpi_flush ();
	
	$query = "INSERT INTO `glpi_plugin_customfields_itemtypes`
                      (`itemtype`,`enabled`)
               VALUES ('Budget', '0'), ('ComputerVirtualMachine', '0')";
	$DB->query ( $query ) or die ( $DB->error () );
	
	$query = "UPDATE `glpi_plugin_customfields_itemtypes`
             SET enabled=160
             WHERE itemtype='Version'";
	$DB->query ( $query ) or die ( $DB->error () );
	echo 'finished.<br />';
	Html::glpi_flush ();
}

/**
 * Update to version 1.6.1
 * 
 * This version implements a working upgrade from releases < 1.5.0 
 * if custom dropdowns exist. The fix is located in the upgrade process to 1.5.0 for
 * code consistency reasons. As this is a big fix, it is better to increment the version 
 * of the plugin
 * 
 */
function plugin_customfields_upgradeto161() {
	global $DB;
	echo 'Updating Custom Fields to 1.6.1 ... ';
	Html::glpi_flush ();	
	
	// Devices have been renamed to Peripherals in GLPI 0.78
	// Support for devices has been added in CF 1.1.7 (for GLPI 0.78 ?)
	// What to do with these data ?
	
	//$query = "DELETE FROM `glpi_plugin_customfields_itemtypes`
    //         WHERE `itemtype` = 'Device'";
	//$DB->query ( $query ) or die ( $DB->error () );

	$query = "UPDATE `glpi_plugin_customfields_itemtypes`
             SET enabled=161
             WHERE itemtype='Version'";
	$DB->query ( $query ) or die ( $DB->error () );
	echo 'finished.<br />';
	Html::glpi_flush ();
}