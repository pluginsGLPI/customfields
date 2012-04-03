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

// Define dropdown relations for use by GLPI
function plugin_customfields_getDatabaseRelations() {
   //TODO: add in relations for multiselects?
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
   global $DB, $LANG;

   $plugin = new Plugin();

   if ($plugin->isActivated("customfields")) {
      /*$dropdowns = array();

      $query = "SELECT *
                FROM `glpi_plugin_customfields_dropdowns`";
      $result = $DB->query($query);

      while ($data=$DB->fetch_assoc($result)) {
         $dropdowns[getItemTypeForTable($data['dropdown_table'])] = $data['label'];
      }
      return $dropdowns;*/
      return array(
         'PluginCustomfieldsDropdownsItem' => $LANG['plugin_customfields']['Custom_Dropdown']
      );
   }
   return array();
}

/////////// SEARCH FUNCTIONS ////////////

// Define search options for each device type that has custom fields.
// 'Search options' are also used by GLPI for logging and mass updates.

function plugin_customfields_getAddSearchOptions($itemtype) {
   global $LANG, $ACTIVE_CUSTOMFIELDS_TYPES, $DB;

   //TODO: Rewrite this function, based on old code--but note that logging appears to work w/o separate item
   $sopt = array();
   if (in_array($itemtype, $ACTIVE_CUSTOMFIELDS_TYPES)) {
      $query = "SELECT `glpi_plugin_customfields_fields`.*,
                       `glpi_plugin_customfields_dropdowns`.`is_tree`
                FROM `glpi_plugin_customfields_fields`
                LEFT JOIN `glpi_plugin_customfields_dropdowns`
                  ON `glpi_plugin_customfields_dropdowns`.`system_name`
                        = `glpi_plugin_customfields_fields`.`system_name`
                WHERE `glpi_plugin_customfields_fields`.`itemtype` = '$itemtype'
                        AND data_type != 'sectionhead'
                ORDER BY
                  `glpi_plugin_customfields_fields`.`sort_order`,
                  `glpi_plugin_customfields_fields`.`id`,
                  `glpi_plugin_customfields_fields`.`label`";

      $i = 5200;
      foreach($DB->request($query) as $search) {
         /** 2 options created :
          * - one for search and displaypreference
          * - second for massive action
          **/

         $sopt[$i]['table']            = plugin_customfields_table($itemtype);
         $sopt[$i]['field']            = $search['system_name'];
         $sopt[$i]['linkfield']        = '';
         $sopt[$i]['name']             = $LANG['plugin_customfields']['title']." - ".$search['label'];
         $sopt[$i]['massiveaction']    = false;

         //no option for disable displaypreferences, check page executed
         if (strpos($_SERVER['SCRIPT_NAME'], "displaypreference.tabs.php") === false) {
            $sopt[$i+2000]['table']       = plugin_customfields_table($itemtype);
            $sopt[$i+2000]['field']       = $search['system_name'];
            $sopt[$i+2000]['linkfield']   = $search['system_name'];
            $sopt[$i+2000]['name']        = $LANG['plugin_customfields']['title']." - ".$search['label'];
            $sopt[$i+2000]['nosearch']    = true;
            $sopt[$i+2000]['nosort']      = true;
         }

         $i++;
      }
   }
   return $sopt;
}

// Clean Search Options: Necessary for search to work properly if GLPI patch applied.
// Removes the search options that are used for different purposes.
// This function requires the glpi patch in order to be called. See the patch directory for instructions.
function plugin_customfields_cleanSearchOption($options, $action) {
   //TODO: update this after finishing getAddSearchOptions
   if(!empty($options)) {
      foreach($options as $ID => $value) {
         if(is_array($value) && isset($value['purpose'])) {
            // If action is 'r' we are cleaning before a search.
            // If action is 'w', we are cleaning before an update.
            if ($value['purpose']=='log') {
               unset($options[$ID]);
            }
            elseif ($value['purpose']=='search' && $action=='w') {
               unset($options[$ID]);
            }
            elseif ($value['purpose']=='update' && $action=='r') {
               unset($options[$ID]);
            }
         }
      }
   }

   return $options;
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
   $out = "";
   if ($DB->numrows($result)) {// A regular dropdown (this fails if the same dd is used in the device AND in networking ports)
      $out  = addLeftJoin($type, $ref_table, $already_link_tables, $type_table, 'id');
      $out .= " LEFT JOIN `$new_table` ON (`$new_table`.`id` = `$type_table`.`$linkfield`) ";

   } else {// a dropdown in network ports
      // Link to glpi_networking_ports first
      /*$out  = addLeftJoin($type, $ref_table, $already_link_tables, "glpi_networkports", '');
      $out .= addLeftJoin('NetworkPort', 'glpi_networkports', $already_link_tables,
                          "glpi_plugin_customfields_networkports", 'id');
      $out .= " LEFT JOIN `$new_table`
                  ON (`glpi_plugin_customfields_networkports`.`$linkfield` = `$new_table`.`id`) ";*/
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

      $cf_itemtype = getItemTypeForTable(plugin_customfields_table($item->getType()));
      //spl_autoload_register("cf_autoload");
      $plugin_custfield = new $cf_itemtype;

      // mass update or tranfer, possibly affecting one of our custom fields
      /*$updates = array();
      $plugdropdown = new PluginCustomfieldsDropdown();
      if (isset($item->input['entities_id'])) {// the item is being transfered to another entity
         $updates = $plugdropdown->transferAllDropdowns($item->input['id'], $item->getType(),
                                                        $item->input['entities_id']);
      }*/

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
      $plugin_custfield->update($item->input);
   }

   return $item; // return the original data, not our additional data
}



// Hook done on add item case
// If in Auto Activate mode, add a record for the custom fields when a device is added
function plugin_item_add_customfields($obj) {
   global $DB,$ACTIVE_CUSTOMFIELDS_TYPES;
   $type=get_class($obj);
   $id=$obj->fields['id'];

   if (CUSTOMFIELDS_AUTOACTIVATE
       && !empty($ACTIVE_CUSTOMFIELDS_TYPES)) {

      if (in_array($type, $ACTIVE_CUSTOMFIELDS_TYPES)) {
         $table = plugin_customfields_table($type);
         $sql = "INSERT INTO `$table`
                        (`id`)
                 VALUES ('".intval($id)."')";
         $result = $DB->query($sql);
         return ($result ? true : false);
      }
   }
   return false;
}


// Hook done on purge item case
function plugin_item_purge_customfields($parm) {
   global $ALL_CUSTOMFIELDS_TYPES;

   // Must delete custom fields when main item is purged, even if custom fields for this device are currently disabled
   if (in_array($parm->getType(), $ALL_CUSTOMFIELDS_TYPES)
       && ($table=plugin_customfields_table($parm->getType()))) {

      $parm->delete(array('id' =>$parm->getID()));
      return true;
   }
   return false;
}

// This function requires the glpi patch in order to be called. See the patch directory for instructions
function plugin_customfields_MassiveActionsFieldsDisplay($options=array()) {
   global $DB;

   $type      = $options['itemtype'];
   $table     = $options['options']['table'];
   $field     = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];


   $query = "SELECT *
             FROM `glpi_plugin_customfields_fields`
             WHERE `itemtype` = '$type'
                   AND `system_name` = '$field'";
   $result=$DB->query($query);


   if ($data=$DB->fetch_assoc($result)) {
      switch($data['data_type']) {
         case 'dropdown' :
            $dropdown_obj = new PluginCustomfieldsDropdown;
            $tmp = $dropdown_obj->find("system_name = '".$data['system_name']."'");
            $dropdown = array_shift($tmp);
            Dropdown::show('PluginCustomfieldsDropdownsItem', array(
                              'condition' => $dropdown['id']." = plugin_customfields_dropdowns_id",
                              'name'      => $data['system_name'],
                              'entity'    => $_SESSION['glpiactive_entity']
                              ));
         break;

         case 'yesno' :
            dropdown::showYesNo($field, 0);
            break;

         case 'date' :
             showDateFormItem($field, '', true, true);
             break;

          case 'money' :
             echo '<input type="text" size="16" value="'.formatNumber(0,true).'" name="'.$field.'"/>';
             break;

          default :
             $item = new $type;
             autocompletionTextField($item, $field);
             break;
      }
      return true;
   } else {
      return false;
   }
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

function plugin_customfields_giveItem ($itemtype,$ID,$data,$num,$meta=0) {
   global $DB,$LANG;

   $searchopt=&Search::getOptions($itemtype);

   $NAME="ITEM_";
   if ($meta) {
      $NAME="META_";
   }

   if (!isset($data[$NAME.$num])) return;

   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];
   $linkfield=$searchopt[$ID]["linkfield"];

   if (strpos($table, "glpi_plugin_customfields_dropdownsitems") !== false) {
      switch ($field) {
         case "plugin_customfields_dropdowns_id":
            return Dropdown::getDropdownName(
                     "glpi_plugin_customfields_dropdowns",
                     $data[$NAME.$num]
                  );
            break;
         case "plugin_customfields_dropdownsitems_id":
            return Dropdown::getDropdownName(
                     "glpi_plugin_customfields_dropdownsitems",
                     $data[$NAME.$num]
                  );
            break;
      }
   } elseif (strpos($table, "glpi_plugin_customfields_") !== false) {
      $query = "SELECT *
                FROM `glpi_plugin_customfields_fields`
                WHERE `itemtype` = '$itemtype'
                      AND `system_name` = '$field'";
      $result=$DB->query($query);

      if ($data_db=$DB->fetch_assoc($result)) {
         switch($data_db['data_type']) {
            case 'dropdown' :
               return Dropdown::getDropdownName(
                                    "glpi_plugin_customfields_dropdownsitems",
                                    $data[$NAME.$num]
                                 );
               break;

            case 'yesno' :
               return Dropdown::getYesNo($data[$NAME.$num]);
               break;

            case 'date' :
               echo convDate($data[$NAME.$num]);
               return convDate($data[$NAME.$num]);
               break;

            case 'money' :
               return formatNumber($data[$NAME.$num]);
               break;

            default :
               return $data[$NAME.$num];
               break;
         }
      }
   }
}



function plugin_customfields_install() {
   include_once (GLPI_ROOT . "/plugins/customfields/inc/install.function.php");
   return pluginCustomfieldsInstall();
}


function plugin_customfields_uninstall() {
   include_once (GLPI_ROOT . "/plugins/customfields/inc/install.function.php");
   return pluginCustomfieldsUninstall();
}

?>
