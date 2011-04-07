<?php
/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org/
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
// Purpose of file: Code for hooks, etc.
// ----------------------------------------------------------------------

//include_once ('inc/plugin_customfields.function.php');
//include_once ('inc/plugin_customfields.class.php');

// Define dropdown relations for use by GLPI
function plugin_customfields_getDatabaseRelations() {
   global $DB;

   $plugin = new Plugin();

   $relations = array();
   $query = "SELECT *
             FROM `glpi_plugin_customfields_fields`
             WHERE `entities` != ''
                   AND `deleted` = 0
                   AND `data_type` = 'dropdown'
             ORDER BY `itemtype`";
   $result = $DB->query($query);

   while ($data=$DB->fetch_assoc($result)) {
      $relations[$data['dropdown_table']] = array(plugin_customfields_table($data['itemtype'])
                                                                           => $data['system_name']);
   }

   $entities = array();
   $query = "SELECT `dropdown_table`
             FROM `glpi_plugin_customfields_dropdowns`
             WHERE `has_entities` = 1";
   $result = $DB->query($query);

   while ($data=$DB->fetch_assoc($result)) {
      $entities[$data['dropdown_table']] = 'entities_id';
   }
   if (!empty($entities)) {
      $relations['glpi_entities'] = $entities;
   }
   return $relations;
}


// Define dropdown tables to be managed in GLPI
function plugin_customfields_getDropdown() {
   global $DB;

   $plugin = new Plugin();

   if ($plugin->isActivated("customfields")) {
      $dropdowns = array();

      $query = "SELECT *
                FROM `glpi_plugin_customfields_dropdowns`";
      $result = $DB->query($query);

      while ($data=$DB->fetch_assoc($result)) {
         $dropdowns[$data['dropdown_table']] = $data['label'];
      }
      return $dropdowns;
   }
   return array();
}


/////////// SEARCH FUNCTIONS ////////////

// Define search options for each device type that has custom fields.
// 'Search options' are also used by GLPI for logging and mass updates.

function plugin_customfields_getAddSearchOptions($itemtype) {
   global $LANG, $ACTIVE_CUSTOMFIELDS_TYPES, $DB;

   $sopt = array();
   if (in_array($itemtype, $ACTIVE_CUSTOMFIELDS_TYPES)) {
      $query = "SELECT `glpi_plugin_customfields_fields`.*,
                       `glpi_plugin_customfields_dropdowns`.`is_tree`
                FROM `glpi_plugin_customfields_fields`
                LEFT JOIN `glpi_plugin_customfields_dropdowns`
                  ON `glpi_plugin_customfields_dropdowns`.`system_name`
                        = `glpi_plugin_customfields_fields`.`system_name`
                WHERE `glpi_plugin_customfields_fields`.`itemtype` = '$itemtype'
                ORDER BY `glpi_plugin_customfields_fields`.`sort_order`,
                         `glpi_plugin_customfields_fields`.`label`";

      $i = 5200;
      foreach($DB->request($query) as $search) {
         $sopt[$i]['table']         = plugin_customfields_table($itemtype);
         $sopt[$i]['field']         = $search['system_name'];
         $sopt[$i]['linkfield']     = $search['system_name'];
         $sopt[$i]['name']          = $LANG['plugin_customfields']['title']." - ".$search['label'];
      $i++;
      }
   }
   return $sopt;
}


// Define how to join the tables when doing a search
function plugin_customfields_addLeftJoin($type, $ref_table, $new_table, $linkfield,
                                         &$already_link_tables) {
   global $DB;

   $type_table = plugin_customfields_table($type);
   if ($new_table == $type_table) {
      return " LEFT JOIN `$new_table`
                  ON (`$ref_table`.`id` = `$new_table`.`id`) ";
   }

   if ($new_table == 'glpi_plugin_customfields_networkports') {
      $out  = addLeftJoin($type, $ref_table, $already_link_tables, "glpi_networkports", '');
      $out .= " LEFT JOIN `glpi_plugin_customfields_networkports`
                  ON (`glpi_networkports`.`id` = `glpi_plugin_customfields_networkports`.`id`) ";
      return $out;
   }

   // it is a custom dropdown
   $query = "SELECT *
             FROM `glpi_plugin_customfields_fields`
             WHERE `dropdown_table` = '$new_table'
                   AND `itemtype` = '$type'
                   AND `deleted` = 0
                   AND `entities` != ''";
   $result = $DB->query($query);

   if ($DB->numrows($result)) {// A regular dropdown (this fails if the same dd is used in the device AND in networking ports)
      $out  = addLeftJoin($type, $ref_table, $already_link_tables, $type_table, 'id');
      $out .= " LEFT JOIN `$new_table` ON (`$new_table`.`id` = `$type_table`.`$linkfield`) ";

   } else {// a dropdown in network ports
      // Link to glpi_networking_ports first
      $out  = addLeftJoin($type, $ref_table, $already_link_tables, "glpi_networkports", '');
      $out .= addLeftJoin('NetworkPort', 'glpi_networkports', $already_link_tables,
                          "glpi_plugin_customfields_networkports", 'id');
      $out .= " LEFT JOIN `$new_table`
                  ON (`glpi_plugin_customfields_networkports`.`$linkfield` = `$new_table`.`id`) ";
   }
   return $out;
}


///////////// VARIOUS HOOKS /////////////////

// Hook to process Mass Update & transfer
function plugin_pre_item_update_customfields($item) {
   global $ACTIVE_CUSTOMFIELDS_TYPES;

   if (empty($ACTIVE_CUSTOMFIELDS_TYPES)) {
      return '';
   }

   // If update isn't set, then this is a mass update or transfer, not a regular update
   if (!isset($item->input['_already_called_'])
       && in_array($item->getType(), $ACTIVE_CUSTOMFIELDS_TYPES)) {

      // mass update or tranfer, possibly affecting one of our custom fields
      $updates = array();
      $plugdropdown = new PluginCustomfieldsDropdown();
      if (isset($item->input['entities_id'])) {// the item is being transfered to another entity
         $updates = $plugdropdown->transferAllDropdowns($item->input['id'], $item->getType(),
                                                        $item->input['entities_id']);
      }

      $type    = new PluginCustomfieldsItemtype($item->getType());
      $newdata = array();
      foreach ($item->input as $key=>$val) {
         if (substr($key,0,3) == 'cf_') {
            $newdata[$key] = $item->input[$key];
            unset($item->input[$key]);
         }
      }
      $newdata['id'] = $item->input['id'];
      $newdata['_already_called_'] = true;
      $plugin_custfield->update($newdata);
   }

//   return $item; // return the original data, not our additional data
}


// Hook done on add item case
// If in Auto Activate mode, add a record for the custom fields when a device is added
function plugin_item_add_customfields($parm) {
   global $DB,$ACTIVE_CUSTOMFIELDS_TYPES;

   if (CUSTOMFIELDS_AUTOACTIVATE
       && isset($parm['type'])
       && !empty($ACTIVE_CUSTOMFIELDS_TYPES)) {

      if (in_array($parm['type'], $ACTIVE_CUSTOMFIELDS_TYPES)) {
         $table = plugin_customfields_table($parm['type']);
         $sql = "INSERT INTO `$table`
                        (`id`)
                 VALUES ('".intval($parm['id'])."')";
         $result = $DB->query($sql);
         return ($result ? true : false);
      }
   }
   return false;
}


// Hook done on purge item case
function plugin_item_purge_customfields($parm) {
   global $DB,$ALL_CUSTOMFIELDS_TYPES;

   // Must delete custom fields when main item is purged, even if custom fields for this device are currently disabled
   if (in_array($parm['type'], $ALL_CUSTOMFIELDS_TYPES)
       && ($table=plugin_customfields_table($parm['type']))) {

      $sql = "DELETE
              FROM `$table`
              WHERE `id` = '".intval($parm['id'])."'
              LIMIT 1";
      $result = $DB->query($sql);
      return true;
   }
   return false;
}


function plugin_customfields_MassiveActionsFieldsDisplay($options=array()) {
   global $DB;

   $table     = $options['options']['table'];
   $field     = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];
   $type      = getItemTypeForTable($table);
   $plug      = isPluginItemType($type);

   if ($plug['plugin'] == 'Customfields') {
      $item = new $type;

       $query = "SELECT *
                 FROM `glpi_plugin_customfields_fields`
                 WHERE `itemtype` = '$type'
                       AND `system_name` = '$field'";
      $result=$DB->query($query);

      if ($data=$DB->fetch_assoc($result)) {
         switch($data['data_type']) {
            case 'dropdown' :
               Dropdown::dropdownValue($data['dropdown_table'], $field, 1,
                                       $_SESSION['glpiactive_entity']);
            break;

            case 'yesno' :
               dropdownYesNo($field, 0);
               break;

             case 'date' :
                showDateFormItem($field, '', true, true);
                break;

             case 'money' :
                echo '<input type="text" size="16" value="'.formatNumber(0,true).'" name="'.$field.'"/>';
                break;

             default :
                autocompletionTextField($item, $field);
                break;
         }
      }
      return true;
   }
   return false;
}


// Define headings added by the plugin -- determines if a tab should be shown or not
function plugin_get_headings_customfields($item, $withtemplate) {
   global $LANG, $ACTIVE_CUSTOMFIELDS_TYPES;

   $type = get_Class($item);

   if (($type == 'Profile' && $item->fields['interface'] != 'helpdesk')
        || !empty($ACTIVE_CUSTOMFIELDS_TYPES)
        && in_array($type, $ACTIVE_CUSTOMFIELDS_TYPES)) {

      $ID = $item->getField('id');
      if (!$withtemplate || !$item->isNewID($ID) ) {
         return array(1 => $LANG['plugin_customfields']['title']);
      }
   }
   return false;
}


// Define headings actions added by the plugin -- what happens when you click on the tab
function plugin_headings_actions_customfields($item) {
   global $ACTIVE_CUSTOMFIELDS_TYPES;

   $type = get_class($item);

   if (($type == 'Profile' && $item->getField('interface')=='central')
        || !empty($ACTIVE_CUSTOMFIELDS_TYPES)
        && in_array($type,$ACTIVE_CUSTOMFIELDS_TYPES)) {

      return array(1 => 'plugin_headings_customfields');
   }
   return false;
}


// customfields of an action heading -- show the custom fields
function plugin_headings_customfields($item) {
   global $CFG_GLPI;

   $ID = $item->getField('id');
   $type = get_class($item);

   if ($type == 'Profile') {
      $prof = new PluginCustomfieldsProfile();
      if ($prof->GetfromDB($ID) || $prof->createUserAccess($item)) {
         $prof->showForm($ID,
                         array('target' => $CFG_GLPI["root_doc"]."/plugins/customfields/front/profile.form.php"));
      }

   } else {
      if ($ID > 0) {
         echo '<div class="center">';
         echo plugin_customfields_showAssociated($item);
         echo '</div>';
      }
   }
}

/*
// Define fields that can be updated with the data_injection plugin
function plugin_customfields_data_injection_variables()
{
	global $IMPORT_PRIMARY_TYPES, $DATA_INJECTION_MAPPING, $LANG, $IMPORT_TYPES,$DATA_INJECTION_INFOS,$DB;
	$plugin = new Plugin();

	if ($plugin->isActivated("customfields"))
	{
		$query="SELECT * FROM glpi_plugin_customfields_fields WHERE data_type <> 'sectionhead' AND deleted=0;";
		$result=$DB->query($query);
		while ($data=$DB->fetch_assoc($result))
		{
			$type=5200 + $data['device_type']; // this plugin uses the range 5200-7699
			$field = $data['system_name'];
			if($data['data_type']=='dropdown')
			{
				$DATA_INJECTION_MAPPING[$type][$field]['table'] = $data['dropdown_table'];
				$DATA_INJECTION_MAPPING[$type][$field]['field'] = 'name';
				$DATA_INJECTION_MAPPING[$type][$field]['linkfield'] = $field;
				$DATA_INJECTION_INFOS[$type][$field]['linkfield'] = $field;
				$DATA_INJECTION_MAPPING[$type][$field]['table_type'] = 'dropdown';
				$DATA_INJECTION_INFOS[$type][$field]['table_type'] = 'dropdown';
			}
			else
			{
				$DATA_INJECTION_MAPPING[$type][$field]['table'] = plugin_customfields_table($type);
				$DATA_INJECTION_MAPPING[$type][$field]['field'] = $field;
			}
			$DATA_INJECTION_MAPPING[$type][$field]['name'] = $data['label'];
			switch($data['data_type'])
			{
				case 'number':
				case 'yesno': $DATA_INJECTION_MAPPING[$type][$field]['type'] = 'integer'; break;
				case 'date': $DATA_INJECTION_MAPPING[$type][$field]['type'] = 'date'; break;
				case 'money': $DATA_INJECTION_MAPPING[$type][$field]['type'] = 'float'; break;
				case 'text':
				case 'notes':
					$DATA_INJECTION_MAPPING[$type][$field]['table_type'] = 'multitext';
					$DATA_INJECTION_INFOS[$type][$field]['table_type'] = 'multitext';
					$DATA_INJECTION_MAPPING[$type][$field]['type'] = 'text';
					break;
				default: $DATA_INJECTION_MAPPING[$type][$field]['type'] = 'text';
			}
			$DATA_INJECTION_INFOS[$type][$field]['table'] = $DATA_INJECTION_MAPPING[$type][$field]['table'];
			$DATA_INJECTION_INFOS[$type][$field]['field'] = $DATA_INJECTION_MAPPING[$type][$field]['field'];
			$DATA_INJECTION_INFOS[$type][$field]['name'] = $DATA_INJECTION_MAPPING[$type][$field]['name'];
			$DATA_INJECTION_INFOS[$type][$field]['type'] = $DATA_INJECTION_MAPPING[$type][$field]['type'];
		}
	}
}
*/



function plugin_customfields_install() {
   global $DB, $LANG;

   //Upgrade process if needed
   if (TableExists("glpi_plugin_customfields")) {

      if (TableExists("glpi_plugin_customfields_fields")) {
         if (!FieldExists('glpi_plugin_customfields_fields','deleted')) { // <1.0.1
            plugin_customfields_upgradeto101();
         }
      }

//      plugin_customfields_upgradeto110();

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

//      plugin_customfields_upgradeto116();

      if (TableExists("glpi_plugin_customfields_fields")) {
         if (!FieldExists('glpi_plugin_customfields_fields','unique')) { // <1.1.7
            plugin_customfields_upgradeto117();
         }
      }

      if (!TableExists("glpi_plugin_customfields_itemtypes")) { // <1.2
         plugin_customfields_upgradeto12();
      }

plugin_customfields_upgradeto110(); // must be at the end : itemtype
plugin_customfields_upgradeto116();
/*
      if (haveRight('config', 'w')) {
         // Check the version of the database tables.
         $query = "SELECT `version`
                   FROM `glpi_plugins`
                   WHERE `directory` = 'customfields'";
         $result    = $DB->query($query);
         $data      = $DB->fetch_array($result);
         $dbversion = $data['version']; // Version of the last modification to the plugin tables' structure
logdebug("vsersion", $dbversion);
         if ($dbversion < CUSTOMFIELDS_DB_VERSION_REQUIRED) {
            plugin_customfields_upgrade($dbversion);
         }
         if (CUSTOMFIELDS_AUTOACTIVATE) {
            plugin_customfields_activate_all_types();
         }
      }*/
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
                       ('Printer'), ('Cartridge'), ('Consumable'), ('Phone'), ('Ticket'), ('Contact'),
                       ('Supplier'), ('Contract'), ('Document'), ('User'), ('Group'), ('Entity'),
                       ('DeviceMotherboard'), ('DeviceProcessor'), ('DeviceMemory'),
                       ('DeviceHardDrive'), ('DeviceNetworkCard'), ('DeviceDrive'),
                       ('DeviceControl'), ('DeviceGraphicCard'), ('DeviceSoundCard'), ('DevicePci'),
                       ('DeviceCase'), ('DevicePowerSupply')";
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
                  `label` varchar(70) collate utf8_unicode_ci default NULL,
                  `dropdown_table` varchar(255) collate utf8_unicode_ci default NULL,
                  `has_entities` smallint(6) NOT NULL default '0',
                  `is_tree` smallint(6) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
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
           ADD `entities` VARCHAR(255) NOT NULL DEFAULT '*',
           ADD `restricted` smallint(6) NOT NULL DEFAULT '0'";
   $DB->query($sql) or die($DB->error());

   $sql = "UPDATE `glpi_plugin_customfields_fields`
           SET `entities` = ''
           WHERE `hidden` = 1";
   $DB->query($sql) or die($DB->error());

   $sql = "ALTER TABLE `glpi_plugin_customfields_fields`
           DROP `hidden`";
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

   $transform = array();
   $transform['general']  = 'VARCHAR(255) collate utf8_unicode_ci default NULL';
   $transform['dropdown'] = 'INT(11) NOT NULL default \'0\'';
   $transform['yesno']    = 'SMALLINT(6) NOT NULL default \'0\'';
   $transform['text']     = 'TEXT collate utf8_unicode_ci';
   $transform['notes']    = 'LONGTEXT collate utf8_unicode_ci';
   $transform['number']   = 'INT(11) NOT NULL default \'0\'';
   $transform['money']    = 'DECIMAL(20,4) NOT NULL default \'0.0000\'';

   $sql = "SELECT `itemtype`, `system_name`, `data_type`
           FROM `glpi_plugin_customfields_fields`
           WHERE `deleted` = 0
                 AND `data_type` != 'sectionhead'
                 AND `data_type` != 'date'
           ORDER BY `itemtype`, `sort_order`, `id`";
   $result = $DB->query($sql) or die($DB->error());
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
      $DB->query($sql) or die($DB->error());

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

/*
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

   foreach ($enabled as $device_type) {
      $sql = "UPDATE `glpi_plugin_customfields`
              SET `enabled` = 1
              WHERE `device_type` = '$device_type';";
      $DB->query($sql) or die($DB->error());
   }
   */
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

      $query = "DELETE
                FROM `glpi_plugin_customfields_itemtypes`
                WHERE `device_type` IN ('501', '502', '503', '504', '505', '506', '507', '508',
                                        '509', '510', '511', '512')";
      $DB->query($query) or die($DB->error());

      $query = "DELETE
                FROM `glpi_plugin_customfields_fields`
                WHERE `device_type` IN ('501', '502', '503', '504', '505', '506', '507', '508',
                                        '509', '510', '511', '512')";
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

      $query = "DELETE
                FROM `glpi_plugin_customfields_itemtypes`
                WHERE `itemtype` = 'Device'";
      $DB->query($query) or die($DB->error());

      $query = "INSERT INTO `glpi_plugin_customfields_itemtypes`
                       (`itemtype`)
                VALUES ('DeviceMotherboard'), ('DeviceProcessor'), ('DeviceMemory'),
                       ('DeviceHardDrive'), ('DeviceNetworkCard'), ('DeviceDrive'),
                       ('DeviceControl'), ('DeviceGraphicCard'), ('DeviceSoundCard'),
                       ('DevicePci'), ('DeviceCase'), ('DevicePowerSupply')";
      $DB->query($query) or die($DB->error());

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


function plugin_customfields_uninstall() {
   global $LANG;

   $query = "SELECT `itemtype`
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` <> 'Version'";

   if ($result=$DB->query($query)) {
      while ($data=$DB->fetch_assoc($result)) {
         $table = plugin_customfields_table($data['itemtype']);
         if ($table) {
            $query ="DROP TABLE IF EXISTS `$table`";
            $DB->query($query) or die($DB->error());
         }
      }
   }

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

   $tables = array('glpi_plugin_customfields',
                   'glpi_plugin_customfields_dropdowns',
                   'glpi_plugin_customfields_fields',
                   'glpi_plugin_customfields_profiledate',
                   'glpi_plugin_customfields_itemtypes',
                   'glpi_plugin_customfields_dropdowns',
                   'glpi_plugin_customfields_fields',
                   'glpi_plugin_customfields_profiles');

   foreach ($tables as $table) {
      $DB->query ("DROP TABLE `$table`");
   }

   return true;
}

?>
