<?php
function pluginCustomfieldsInstall() {
   global $DB, $LANG;

   //Upgrade process if needed
   if (TableExists("glpi_plugin_customfields")) {

      if (TableExists("glpi_plugin_customfields_fields")) {
         if (!FieldExists('glpi_plugin_customfields_fields','deleted')) { // <1.0.1
            plugin_customfields_upgradeto101();
         }
      }

      //plugin_customfields_upgradeto110();

      if (TableExists("glpi_plugin_customfields_fields")) {
         if (!FieldExists('glpi_plugin_customfields_fields','required')) { // <1.1.2
            plugin_customfields_upgradeto112();
         }
      }

      if (TableExists("glpi_plugin_customfields_fields")) {
         if (!FieldExists('glpi_plugin_customfields_fields','entities')) { // <1.1.3
            plugin_customfields_upgradeto113();
         }
      }

      plugin_customfields_upgradeto116();

      if (TableExists("glpi_plugin_customfields_fields")) {
         if (!FieldExists('glpi_plugin_customfields_fields','unique')) { // <1.1.7
            plugin_customfields_upgradeto117();
         }
      }

      if (!TableExists("glpi_plugin_customfields_itemtypes")) { // <1.2
         plugin_customfields_upgradeto12();
      }

      plugin_customfields_upgradeto110(); // must be at the end : itemtype

      return true;

   } else { //not installed

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_customfields_itemtypes` (
                  `id` int(11) NOT NULL auto_increment,
                  `itemtype` VARCHAR(100) NOT NULL default '',
                  `enabled` smallint(6) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->query($query) or die($DB->error());

      $query = "INSERT INTO `glpi_plugin_customfields_itemtypes`
                      (`itemtype`,`enabled`)
               VALUES ('Version', '12')";
      $DB->query($query) or die($DB->error());

      $query = "INSERT INTO `glpi_plugin_customfields_itemtypes`
                       (`itemtype`)
                VALUES ('Computer'), ('ComputerDisk'), ('Monitor'), ('Software'), ('SoftwareVersion'),
                       ('SoftwareLicense'), ('NetworkEquipment'), ('NetworkPort'), ('Peripheral'),
                       ('Printer'), ('CartridgeItem'), ('ConsumableItem'), ('Phone'), ('Ticket'), ('Contact'),
                       ('Supplier'), ('Contract'), ('Document'), ('User'), ('Group'), ('Entity')";
      $DB->query($query) or die($DB->error());


      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_customfields_fields` (
                  `id` int(11) NOT NULL auto_increment,
                  `itemtype` VARCHAR(100) NOT NULL default '',
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
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3";
      $DB->query($query) or die($DB->error());

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_customfields_dropdowns` (
                  `id` int(11) NOT NULL auto_increment,
                  `system_name` varchar(40) collate utf8_unicode_ci default NULL,
                  `name` varchar(70) collate utf8_unicode_ci default NULL,
                  `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL,
                  `has_entities` smallint(6) NOT NULL default '0',
                  `is_tree` smallint(6) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->query($query) or die($DB->error());

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_customfields_dropdownsitems` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `entities_id` int(11) NOT NULL DEFAULT '0',
                  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                  `completename` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                  `level` INTEGER NOT NULL DEFAULT '0',
                  `plugin_customfields_dropdowns_id` int(11) NOT NULL DEFAULT '0',
                  `plugin_customfields_dropdownsitems_id` int(11) NOT NULL DEFAULT '0',
                  `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                  PRIMARY KEY (`id`)
               ) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1";
      $DB->query($query) or die($DB->error());

      $query = "INSERT INTO `glpi_displaypreferences`
                  VALUES (NULL,'PluginCustomfieldsDropdownsItem','3','1','0');";
      $DB->query($query) or die($DB->error());

      $query = "INSERT INTO `glpi_displaypreferences`
                  VALUES (NULL,'PluginCustomfieldsDropdownsItem','4','2','0');";
      $DB->query($query) or die($DB->error());

      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_customfields_profiles` (
                  `id` int(11) NOT NULL ,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->query($query) or die($DB->error());

      return true;
   }
}




function pluginCustomfieldsUninstall() {
   global $LANG, $DB;

   $query = "SELECT `itemtype`
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` <> 'Version'";

   $itemtypes = array();
   if ($result=$DB->query($query)) {
      while ($data=$DB->fetch_assoc($result)) {
         $itemtypes[] = $data['itemtype'];
         $table = plugin_customfields_table($data['itemtype']);
         if ($table) {
            $query ="DROP TABLE IF EXISTS `$table`";
            $DB->query($query) or die($DB->error());
         }
      }
   }

   //delete dropdown search option
   $query = "DELETE FROM glpi_displaypreferences WHERE itemtype = 'PluginCustomfieldsDropdownsItem'";
   $DB->query($query) or die($DB->error());


   //delete object searchoption for itemptype existing links
   $searchopts_keys = array();
   foreach ($itemtypes as $itemtype) {
      $searchoptions = plugin_customfields_getAddSearchOptions($itemtype);
      $searchopts_keys = array_merge(array_keys($searchoptions), $searchopts_keys);
   }
   $searchopts_keys_str = "'".implode("',' ", $searchopts_keys)."'";
   $query = "DELETE FROM glpi_displaypreferences WHERE num IN ($searchopts_keys_str)";
   $DB->query($query) or die($DB->error());



   $query = "SELECT `dropdown_table`
             FROM `glpi_plugin_customfields_dropdowns`";

   if ($result=$DB->query($query)) {
      while ($data=$DB->fetch_assoc($result)) {
         $table = $data['dropdown_table'];
         if ($table != '') {
            $query = "DROP TABLE IF EXISTS `$table`";
            $DB->query($query) or die($DB->error());
         }
      }
   }

   $tables = array(
               'glpi_plugin_customfields_dropdowns',
               'glpi_plugin_customfields_dropdownsitems',
               'glpi_plugin_customfields_fields',
               'glpi_plugin_customfields_itemtypes',
               'glpi_plugin_customfields_profiles'
            );

   foreach ($tables as $table) {
      $DB->query ("DROP TABLE `$table`");
   }

   return true;
}




function plugin_customfields_upgrade($oldversion) {
   global $DB;

   // Upgrade logging feature
   if ($oldversion < 101) {
      plugin_customfields_upgradeto101();
   }

   // Save settings
   $sql = "SELECT `device_type`
           FROM `glpi_plugin_customfields`
           WHERE `enabled` = '1';";
   $result  = $DB->query($sql);
   $enabled = array();
   while ($data=$DB->fetch_array($result)) {
      $enabled[] = $data['device_type'];
   }

   // Upgrade date fields to be compatible with GLPI 0.72+
   if ($oldversion < 110) {
      plugin_customfields_upgradeto110();
   }

   // Add a column to indicate if a field is required
   if ($oldversion < 112) {
      plugin_customfields_upgradeto112();
   }

   // Add a column to indicate which entities to show the field with
   // Remove column for hidden field, use blank in entites field to replace this functionality
   // Add restricted field to allow field-based permissions
   if ($oldversion < 113) {
      plugin_customfields_upgradeto113();
   }

   // Upgrade fields to be compatable with mysql strict mode
   if ($oldversion < 116) {
      plugin_customfields_upgradeto116();

      echo 'finished.';
      glpi_flush();
   }

   if ($oldversion < 117) {
      plugin_customfields_upgradeto117();
   }

   if ($oldversion < 12) {
      plugin_customfields_upgradeto12();
   }

            if (CUSTOMFIELDS_AUTOACTIVATE) {
            plugin_customfields_activate_all_types();
         }

}


function plugin_customfields_upgradeto101() {
   global $DB;

   $sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `deleted` smallint(6) NOT NULL DEFAULT '0',
           ADD `sopt_pos` int(11) NOT NULL DEFAULT '0'";
   $DB->query($sql) or die($DB->error());

   $sql = "UPDATE `glpi_plugin_customfields_fields`
           SET `sopt_pos` = `ID`"; // initialize sopt_pos to something unique
   $DB->query($sql) or die($DB->error());
}


function plugin_customfields_upgradeto110() {
   global $DB;

   $sql = "SELECT `itemtype`, `system_name`
           FROM `glpi_plugin_customfields_fields`
           WHERE `data_type` = 'date'";
   $result = $DB->query($sql) or die($DB->error());

   while ($data=$DB->fetch_array($result)) {
      $table = plugin_customfields_table($data['itemtype']);
      $field = $data['system_name'];
      $sql = "ALTER TABLE `$table`
              CHANGE `$field` `$field` DATE NULL DEFAULT NULL";
      $DB->query($sql) or die($DB->error());

      $sql = "UPDATE `$table`
              SET `$field`= NULL
              WHERE `$field` = '0000-00-00'";
      $DB->query($sql) or die($DB->error());
   }
}


function plugin_customfields_upgradeto112() {
   global $DB;

   $sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `required` smallint(6) NOT NULL DEFAULT '0'";
   $DB->query($sql) or die($DB->error());

   $sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           CHANGE `device_type` `device_type` INT(11) NOT NULL DEFAULT '0'";
   $DB->query($sql) or die($DB->error());
}


function plugin_customfields_upgradeto113() {
   global $DB;

   $sql = "ALTER TABLE `glpi_plugin_customfields_fields`
          ADD `entities` VARCHAR(255) NOT NULL DEFAULT '*'";
   $DB->query($sql) or die($DB->error());

   $sql = "UPDATE `glpi_plugin_customfields_fields`
           SET `entities` = ''
           WHERE `hidden` = 1";
   $DB->query($sql) or die($DB->error());

   $sql = "ALTER TABLE `glpi_plugin_customfields_fields`
          DROP `hidden`";
   $DB->query($sql) or die($DB->error());

   $sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `restricted` smallint(6) NOT NULL DEFAULT '0'";
   $DB->query($sql) or die($DB->error());

   if (!TableExists("glpi_plugin_customfields_profiledata")) {
      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_customfields_profiledata` (
                  `ID` int(11) NOT NULL auto_increment,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  PRIMARY KEY (`ID`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3";
      $DB->query($query) or die($DB->error());
   }
}


function plugin_customfields_upgradeto116() {
   global $DB;

   // Upgrade
   $query= "DROP TABLE IF EXISTS `glpi_plugin_customfields`";
   $DB->query($query) or die($DB->error());

   $query= "CREATE TABLE `glpi_plugin_customfields` (
               `ID` int(11) NOT NULL auto_increment,
               `device_type` int(11) NOT NULL default '0',
               `enabled` smallint(6) NOT NULL default '0',
               PRIMARY KEY (`ID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3";
   $DB->query($query) or die($DB->error());

   $query= "INSERT INTO `glpi_plugin_customfields`
               (`device_type`,`enabled`)
            VALUES ('-1', '116')";
   $DB->query($query) or die($DB->error());

   $query= "INSERT INTO `glpi_plugin_customfields`
                   (`device_type`)
            VALUES ('1'), ('41'), ('4'), ('6'), ('39'), ('20'), ('2'), ('42'), ('5'), ('3'), ('11'),
                   ('17'), ('23'), ('16'), ('7'), ('8'), ('10'), ('13'), ('15'), ('27'), ('28')";
   $DB->query($query) or die($DB->error());

   $transform = array();
   $transform['general']  = 'VARCHAR(255) collate utf8_unicode_ci default NULL';
   $transform['dropdown'] = 'INT(11) NOT NULL default \'0\'';
   $transform['yesno']    = 'SMALLINT(6) NOT NULL default \'0\'';
   $transform['text']     = 'TEXT collate utf8_unicode_ci';
   $transform['notes']    = 'LONGTEXT collate utf8_unicode_ci';
   $transform['number']   = 'INT(11) NOT NULL default \'0\'';
   $transform['money']    = 'DECIMAL(20,4) NOT NULL default \'0.0000\'';

   $sql = "SELECT `device_type`, `system_name`, `data_type`
           FROM `glpi_plugin_customfields_fields`
           WHERE `deleted` = 0
                 AND `data_type` != 'sectionhead'
                 AND `data_type` != 'date'
           ORDER BY `device_type`, `sort_order`, `ID`";
   $result = $DB->query($query) or die($DB->error());
   set_time_limit(300);
   echo 'Updating Custom Fields...';

   while ($data=$DB->fetch_array($result)) {
      echo '.';
      glpi_flush();
      $table   = plugin_customfields_table($data['itemtype']);
      $field   = $data['system_name'];
      $newtype = $transform[$data['data_type']];
      $sql = "ALTER TABLE `$table`
              CHANGE `$field` `$field` $newtype";
      $DB->query($query) or die($DB->error());

      if (in_array($data['data_type'], array('general', 'text', 'notes'))) {
         $sql = "UPDATE `$table`
                 SET `$field` = NULL
                 WHERE `$field` = ''";
         $DB->query($sql) or die($DB->error());
      }
   }

   $query = "ALTER TABLE `glpi_plugin_customfields_fields`
             CHANGE `system_name` `system_name` varchar(40) collate utf8_unicode_ci default NULL,
             CHANGE `label` `label` varchar(70) collate utf8_unicode_ci default NULL,
             CHANGE `default_value` `default_value` varchar(255) collate utf8_unicode_ci default NULL,
             CHANGE `dropdown_table` `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL";
   $DB->query($query) or die($DB->error());

   $query = "ALTER TABLE `glpi_plugin_customfields_dropdowns`
             CHANGE `system_name` `system_name` varchar(40) collate utf8_unicode_ci default NULL,
             CHANGE `label` `label` varchar(70) collate utf8_unicode_ci default NULL,
             CHANGE `dropdown_table` `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL";
   $DB->query($query) or die($DB->error());

   $query = "UPDATE `glpi_plugin_customfields_fields`
             SET `default_value` = NULL
             WHERE `default_value` = ''";
   $DB->query($query) or die($DB->error());

   echo 'finished.';
   glpi_flush();
}


function plugin_customfields_upgradeto117() {
   global $DB;

   $sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           ADD `unique` smallint(6) NOT NULL DEFAULT '0'";
   $DB->query($sql) or die($DB->error());

   // Upgrade
   $query= "DROP TABLE IF EXISTS `glpi_plugin_customfields`
            CREATE TABLE `glpi_plugin_customfields` (
               `ID` int(11) NOT NULL auto_increment,
               `device_type` int(11) NOT NULL default '0',
               `enabled` smallint(6) NOT NULL default '0',
               PRIMARY KEY  (`ID`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3";
      $DB->query($query) or die($DB->error());

   $query= "INSERT INTO `glpi_plugin_customfields`
               (`device_type`,`enabled`)
            VALUES ('-1', '117'),
                   ('24', '1')";
   $DB->query($query) or die($DB->error());

   $query= "INSERT INTO `glpi_plugin_customfields`
                   (`device_type`)
            VALUES ('1', '41', '4', '6', '39', '20', '2', '42', '5', '3', '11', '17', '23', '16',
                    '7', '8', '10', '13', '15', '27', '28' , '501', '502', '503', '504', '505',
                    '506', '507', '508', '509', '510', '511', '512')";
   $DB->query($query) or die($DB->error());

   // Save settings
   $sql = "SELECT `device_type`
           FROM `glpi_plugin_customfields`
           WHERE `enabled` = '1';";
   $result  = $DB->query($sql);
   $enabled = array();
   while ($data=$DB->fetch_array($result)) {
      $enabled[] = $data['device_type'];
   }

   foreach($enabled as $device_type) {
      $sql = "UPDATE `glpi_plugin_customfields`
              SET `enabled` = 1
              WHERE `device_type` = '$device_type';";
      $DB->query($sql) or die($DB->error());
   }
}


function plugin_customfields_upgradeto12() {
   global $DB;

   $glpi_tables = array('glpi_plugin_customfields_software'          => 'glpi_plugin_customfields_softwares',
                        'glpi_plugin_customfields_networking'        => 'glpi_plugin_customfields_networkequipments',
                        'glpi_plugin_customfields_enterprises'       => 'glpi_plugin_customfields_suppliers',
                        'glpi_plugin_customfields_docs'              => 'glpi_plugin_customfields_documents',
                        'glpi_plugin_customfields_tracking'          => 'glpi_plugin_customfields_tickets',
                        'glpi_plugin_customfields_user'              => 'glpi_plugin_customfields_users',
                        'glpi_plugin_customfields_networking_ports'  => 'glpi_plugin_customfields_networkports');

   foreach ($glpi_tables as $oldtable => $newtable) {
      if (!TableExists("$newtable") && TableExists("$oldtable")) {
         $query = "RENAME TABLE `$oldtable` TO `$newtable`";
         $DB->query($query) or die($DB->error());
      }
   }

   if (TableExists("glpi_plugin_customfields")) {
      $query = "RENAME TABLE `glpi_plugin_customfields` TO `glpi_plugin_customfields_itemtypes`";
      $DB->query($query) or die($DB->error());

      $query = "ALTER TABLE `glpi_plugin_customfields_itemtypes`
                CHANGE `ID` `id` int(11) NOT NULL auto_increment,
                CHANGE `device_type` `itemtype` VARCHAR(100) NOT NULL default ''";
      $DB->query($query) or die($DB->error());

      $tables = array('glpi_plugin_customfields_itemtypes');
      Plugin::migrateItemType(array(-1 => 'Version'), array(), $tables);

      $query = "SELECT `itemtype`
                FROM `glpi_plugin_customfields_itemtypes`
                WHERE `itemtype` <> 'Version' ";
      $result  = $DB->query($query);

      $enabled = array();
      while ($data=$DB->fetch_array($result)) {
         $enabled[] = $data['itemtype'];
         $table = plugin_customfields_table($data['itemtype']);
         if (TableExists($table)) {
             $query = "ALTER TABLE `$table`
                       CHANGE `ID` `id` int(11) NOT NULL auto_increment";
             $DB->query($query) or die($DB->error());
         }
      }

       foreach($enabled as $itemtype) {
          $sql = "UPDATE `glpi_plugin_customfields_itemtypes`
                  SET `enabled` = 1
                  WHERE `itemtype` = '$itemtype';";
          $DB->query($sql) or die($DB->error());
       }
   }

   if (TableExists("glpi_plugin_customfields_dropdowns")) {
      $query = "ALTER TABLE `glpi_plugin_customfields_dropdowns`
                CHANGE `ID` `id` int(11) NOT NULL auto_increment";
      $DB->query($query) or die($DB->error());
   }

   if (TableExists("glpi_plugin_customfields_fields")) {
      $query = "ALTER TABLE `glpi_plugin_customfields_fields`
                CHANGE `ID` `id` int(11) NOT NULL auto_increment,
                CHANGE `device_type` `itemtype` VARCHAR(100) NOT NULL default ''";
      $DB->query($query) or die($DB->error());

      $tables = array('glpi_plugin_customfields_fields');
      Plugin::migrateItemType(array(-1 => 'Version'), array(), $tables);
   }

   if (TableExists("glpi_plugin_customfields_profiledata")) {
      $query = "RENAME TABLE `glpi_plugin_customfields_profiledata` TO `glpi_plugin_customfields_profiles`";
      $DB->query($query) or die($DB->error());
      $query = "ALTER TABLE `glpi_plugin_customfields_profiles`
                CHANGE `ID` `id` int(11) NOT NULL auto_increment";
      $DB->query($query) or die($DB->error());
   }
}

?>
