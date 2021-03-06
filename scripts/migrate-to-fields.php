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

// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Main configuration page
// ----------------------------------------------------------------------


/**
 * 
 * The script does the following assumptioins
 * 
 *  1 Fields does not contains any data
 * 
 * Limitations of the script
 * 
 *  1 cannot generate containers with recursive entities flag 
 *  2 cannot reuse already existing containers (see assumption 1)
 * 
 */

chdir(dirname($_SERVER["SCRIPT_FILENAME"]));

include ('../../../inc/includes.php');

if (isCommandLine()) {
   $EndOfLine = "\n";
} else {
   $EndOfLine = "<br>";
}

// Sanitize console output
echo $EndOfLine . $EndOfLine;

// ---- Check prerequisites ----
echo "Searching for Fields plugin$EndOfLine";
if (! class_exists("PluginFieldsContainer")) {
   die ("It appears you did not install or activate Fields plugin $EndOfLinePlease download it here : http://github.com/pluginsglpi/fields$EndOfLine");
} else {
   echo "Fields plugin found and activated$EndOfLine";
}

echo "Checking for Fields version$EndOfLine";
$query = "SELECT `version` FROM `glpi_plugins`  WHERE `directory`='fields' ";
if (! $result = $DB->query($query)) {
   die ("Could search plugins table for Fields version$EndOfLine");
}
if (!($data = $DB->fetch_assoc($result))) {
   die("Could not find Fields in the plugins table$EndOfLine");
}
if ($data['version'] != '0.90-1.1') {
   die("Fields version does not match 0.90-1.1$EndOfLine");
}


echo "Searching for Custom Fields plugin$EndOfLine";
if (! class_exists("PluginCustomfieldsConfig")) {
   die ("It appears you did not install or activate Custom Fields plugin$EndOfLine");
} else {
   echo "Custom Fields plugin found and activated$EndOfLine";
}

echo "Disabling Custom Fields before migration$EndOfLine";
$query = "UPDATE `glpi_plugins` SET `state`='4' WHERE `directory`='customfields' ";
// if (! $result = $DB->query($query)) {
//    die ("Could not disable Custom Fields$EndOfLine");
// }

$query = "SELECT `enabled` FROM `glpi_plugin_customfields_itemtypes` WHERE `itemtype`='Version' LIMIT 1";
if ($result = $DB->query($query)) {
   if (($row = $DB->fetch_assoc($result)) === null) {
      die ("Custom Fields tables are damaged : unable to check version of the plugin$EndOfLine");
   } else {
      if ($row['enabled'] < '160') {
         die("Please upgrade Custom Fields and carefully check data before running this migration tool again. $EndOfLine This script supports the latest DB model only.$EndOfLine");
      }
   }
}

// ---- OK for migration ----
$query = "SELECT * FROM `glpi_plugin_customfields_itemtypes` WHERE NOT `itemtype`='Version'";
if (!$containerResult = $DB->query($query)) {
   die("Could not find activated custom fields$EndOfLine");
} else {
   while ($containerData = $DB->fetch_assoc($containerResult)) {
      $itemtype = $containerData['itemtype'];
      if (is_numeric($itemtype)) {
         echo "Warning : ignoring itemtype $itemtype . This is mostly due to a very old Cutom Fields intallation; maybe some custom fields have been lost while upgrading to GLPI 0.84 or later.$EndOfLine";
      } else {
         $container = new PluginFieldsContainer();
         $container->fields["label"] = __('Custom Field', 'customfields');
         $container->fields["name"] = "customfields"; 
         $container->fields["itemtype"] = $itemtype;
         $container->fields["type"] = "tab";
         $container->fields["subtype"] = null;
         $container->fields["entities_id"] = 0;
         $container->fields["is_recursive"] = 0;
         $container->fields["is_active"] = $containerData["enabled"];
         
         if (!$fieldResult = $DB->query("SELECT * FROM `glpi_plugin_customfields_fields` WHERE `itemtype`='$itemtype' ORDER BY `sort_order`")) {
            die("Could not read customfields fields for itemtype $itemtype$EndOfLine");
         }
         $customFieldsColumns = array("id");
         $fieldsColumns = array("items_id");
         while ($fieldData = $DB->fetch_assoc($fieldResult)) {
            $entities = explode(',', $fieldData['entities']);
            
            // Override entities list as there is no way to convert arbitrary entities to a subtree of them 
            // without making duplate and inconsistent data
            $entities = array('*');
            $entity = '*';
            
            $entity = trim($entity);
            if ($entity == '*') {
               $entity = 0;
               $container->fields["is_recursive"] = 1;
            } else {
               $container->fields["is_recursive"] = 0;
            }
            $container->fields["entities_id"] = $entity;
            
            // Check if a container already exists for the current entity
            $checkContainer = new PluginFieldsContainer();
            $foundContainers = $checkContainer->find(" `itemtype`='$itemtype' AND `entities_id`='$entity' AND `type`='tab' AND `is_recursive`='1' ORDER BY `id` DESC");
            
            if (count($foundContainers) == 0) {
               // No eligible container, create it 
               if (!$container->addToDB()) {
                  die("Could not add in DB the container for $itemtype$EndOfLine");
               }
               //$targetContainer = $container;
            } else {
               // shift should get the latest created container (first one because ordered descending by ID 
               //$targetContainer = array_shift($foundContainers);
               $foundContainer = array_pop($foundContainers);
               $container->getFromDB($foundContainer['id']);
            }
            
            $field = new PluginFieldsField();
            $field->fields["name"] = str_replace('_', '', $fieldData["system_name"]);
            $field->fields["label"] = $fieldData["label"];
            $field->fields["type"] = convertType($fieldData["data_type"]);
            $field->fields["plugin_fields_containers_id"] = $container->fields["id"];
            $field->fields["ranking"] = $fieldData["sort_order"];
            $field->fields["default_value"] = $fieldData["default_value"];
            $field->fields["is_active"] = $container->fields["is_active"];
            $field->fields["is_readonly"] = 0;
            $field->fields["mandatory"] = 0;
            
            $customFieldsColumns[] = $fieldData["system_name"];
            $fieldsColumns[] = $field->fields["name"];
            
            if (!$field->addToDB()) {
               die("Could not create field " . $field->fields["name"] . " for $itemtype, entity $entity $EndOfLine");
            }
            if ($field->fields["type"] != 'dropdown' && $field->fields["type"] != 'header') {
               $targetItemtype = "PluginFields".ucfirst($itemtype.
                                       preg_replace('/s$/', '', $container->fields['name']));
               PluginFieldsContainer::generateTemplate($container->fields);
               $targetTable = $targetItemtype::getTable();
               $sourceItemtype = "PluginCustomfields" . $itemtype;
               $sourceTable = $sourceItemtype::getTable();
               $targetItemtype::addField($field->fields["name"], $field->fields["type"]);
            } else {
               
            }
            
         }
         
         // Migrate all data for the container
         $customFieldsColumns = '`' . implode('`, `', $customFieldsColumns) . '`';
         $fieldsColumns = '`' . implode('`, `', $fieldsColumns) . '`';
         $query = "INSERT INTO $targetTable ($fieldsColumns)
            SELECT $customFieldsColumns FROM $sourceTable";
         if (! $result = $DB->query($query)) {
            die("Could not move data from $sourceTable to $targetTable$EndOfLine");
         }
      }
      
   }
}

function convertType($type) {
     switch ($type) {
        case 'sectionhead':
           $convertedType = 'header';
           break;
           
        case 'general':
           $convertedType = 'text';
           break;
           
        case 'dropdown':
           $convertedType = 'dropdown';
           break;
           
        case 'date':
           $convertedType = 'datetime';
           break;
           
        case 'money':
        case 'number':
           $convertedType = 'number';
           break;
           
        case 'yesno':
           $convertedType = 'yesno';
           break;
           
        case 'notes':
        case 'text':
           $convertedType = 'textarea';
           break;
           
        default:
           $convertedType = 'text';
           break;
     }
     
     return $convertedType;
}
