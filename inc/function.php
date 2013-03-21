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
// Purpose of file: Collection of various functions used by the plugin.
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die('Sorry. You can\'t access this file directly.');
}


////////////////// DATABASE FUNCTIONS /////////////////////////

// Removes most accents used in European languages
function plugin_customfields_remove_accents($str) {

   $str  = htmlentities($str, ENT_COMPAT, 'UTF-8');
   $str  = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|ring);/','$1',$str);
   $from = explode(' ', '&#192; &#193; &#194; &#195; &#196; &#197; &#199; &#200; &#201; &#202; &#203; &#204; &#205; &#206; &#207; &#208; &#209; &#210; &#211; &#212; &#213; &#214; &#217; &#218; &#219; &#220; &#221; &#224; &#225; &#226; &#227; &#228; &#229; &#230; &#231; &#232; &#233; &#234; &#235; &#236; &#237; &#238; &#239; &#240; &#241; &#242; &#243; &#244; &#245; &#246; &#249; &#250; &#251; &#252; &#253; &#255; &#256; &#257; &#258; &#259; &#260; &#261; &#262; &#263; &#264; &#265; &#266; &#267; &#268; &#269; &#270; &#271; &#272; &#273; &#274; &#275; &#276; &#277; &#278; &#279; &#280; &#281; &#282; &#283; &#284; &#285; &#286; &#287; &#288; &#289; &#290; &#291; &#292; &#293; &#294; &#295; &#296; &#297; &#298; &#299; &#300; &#301; &#302; &#303; &#304; &#305; &#308; &#309; &#310; &#311; &#312; &#313; &#314; &#315; &#316; &#317; &#318; &#319; &#320; &#321; &#322; &#323; &#324; &#325; &#326; &#327; &#328; &#329; &#330; &#331; &#332; &#333; &#334; &#335; &#336; &#337; &#340; &#341; &#342; &#343; &#344; &#345; &#346; &#347; &#348; &#349; &#350; &#351; &#352; &#353; &#354; &#355; &#356; &#357; &#360; &#361; &#362; &#363; &#364; &#365; &#366; &#367; &#368; &#369; &#370; &#371; &#372; &#373; &#374; &#375; &#376; &#377; &#378; &#379; &#380; &#381; &#382;');
   $to   = explode(' ', 'A A A A A A C E E E E I I I I D N O O O O O U U U U Y a a a a a a a c e e e e i i i i o n o o o o o u u u u y y A a A a A a C c C c C c C c D d D d E e E e E e E e E e G g G g G g G g G H H h I i I i I i I i I i J j K k k L l L l L l L l L l N n N n N n n N n O o O o O o R r R r R r S s S s S s S s T t T t U u U u U u U u U u U u W w Y y Y Z z Z z Z z');
   return str_replace($from, $to, html_entity_decode($str));
}


// Replace punctuation and spaces with underscore, letters to lowercase.
// Removes most accents, but does not replace foreign scripts, chinese characters, etc.
function plugin_customfields_make_system_name($str) {

   $str = plugin_customfields_remove_accents(trim($str));
   return strtr($str,
                ' ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()+={}[]<>,.?/~`|:;"\'\\',
                '_abcdefghijklmnopqrstuvwxyz______________________________');
}


// Names of tables uses to store the custom field data
function plugin_customfields_table($itemtype) {
   return 'glpi_plugin_customfields_'.strtolower(getPlural($itemtype));
}

function plugin_customfields_itemtype($table) {
   global $CFG_GLPI;

   if (isset($CFG_GLPI['glpiitemtypetables'][$table])) {
      return $CFG_GLPI['glpiitemtypetables'][$table];

   } else {
      $inittable = $table;
      $table     = str_replace("glpi_", "", $table);
      $prefix    = "";

      if (preg_match('/^plugin_([a-z0-9]+)_/',$table,$matches)) {
         $table  = preg_replace('/^plugin_[a-z0-9]+_/','',$table);
         $prefix = "Plugin".ucfirst($matches[1]);
      }

      if (strstr($table,'_')) {
         $split = explode('_', $table);

         foreach ($split as $key => $part) {
            $split[$key] = ucfirst(getSingular($part));
         }
         $table = implode('_',$split);

      } else {
         $table = ucfirst(getSingular($table));
      }

      return $itemtype=$prefix.$table;
   }
}


// Active custom fields for a specific device (used if auto activate is turned off)
function plugin_customfields_activate($itemtype, $ID) {
   global $DB;

   if (isset($itemtype) && $ID>=0) {
      if ($table=plugin_customfields_table($itemtype)) {
         $query = "INSERT INTO `$table`
                   (`id`) VALUES ('".intval($ID)."')";
         $DB->query($query);
      }
   }
}


// Activates custom fields for all devices of a specific type
function plugin_customfields_activate_all($itemtype) {
   global $DB;

   $query = "SELECT `id`
             FROM `glpi_plugin_customfields_fields`
             WHERE `itemtype` = '$itemtype'";
   $result = $DB->query($query);

   if ($DB->numrows($result) > 0) {
      plugin_customfields_create_data_table($itemtype);

      $table1 = getTableForItemType($itemtype);
      $table2 = plugin_customfields_table($itemtype);
      if ($itemtype == 'Entity') {
         $sql = "INSERT INTO `$table2`
                        (`id`)
                 VALUES ('0')"; // Add a row for the Root Entity
         $result2 = $DB->query($sql);
      }

      $query = "SELECT a.`id`, b.`id` AS skip
                FROM $table1 AS a
                LEFT JOIN $table2 AS b
                     ON a.`id` = b.`id`";
      $result = $DB->query($query);

      while ($data=$DB->fetch_assoc($result)) {
         if (is_null($data['skip'])) {
            $sql = "INSERT INTO `$table2`
                           (`id`)
                    VALUES ('".intval($data['id'])."')";
            $result2 = $DB->query($sql);
         }
      }
   }
}


function plugin_customfields_activate_all_types() {
   global $DB;

   $sql = "SELECT `itemtype`
           FROM `glpi_plugin_customfields_itemtypes`
           WHERE `enabled` = 1";
   $result = $DB->query($sql);

   while ($data=$DB->fetch_array($result)) {
      plugin_customfields_activate_all($data['itemtype']);
   }
}


// Create a table to store custom data for a device type if it doesn't already exist
function plugin_customfields_create_data_table($itemtype) {
   global $DB;

   $table = plugin_customfields_table($itemtype);

   if (!TableExists($table)) {
      $sql = "CREATE TABLE `$table` (
               `id` int(11) NOT NULL default '0',
               PRIMARY KEY (`id`)
              )ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3;";
      $result = $DB->query($sql);
      return ($result ? true : false);
   }
   return true;
}


function plugin_customfields_disable_device($itemtype) {
   global $DB, $ACTIVE_CUSTOMFIELDS_TYPES, $LANG;

   unset($ACTIVE_CUSTOMFIELDS_TYPES[$itemtype]);
   $query = "UPDATE `glpi_plugin_customfields_itemtypes`
             SET `enabled` = 0
             WHERE `itemtype` = '$itemtype'";
   $result = $DB->query($query);
   Session::addMessageAfterRedirect($LANG['plugin_customfields']['cf_disabled']);
}


////////////////////// DISPLAY FUNCTIONS /////////////////////////

function plugin_customfields_showValue($value, $size='') {
    if ($size!='') {
      echo '<div style="text-align:left;overflow:auto;border:1px solid #999;'.$size.'">';
   }

   if ($value != '' && $value != '&nbsp;') {
      echo $value;
   } else {
      echo '-';
   }

   if ($size != '') {
      echo '</div>';
   }
}


// Show the custom fields form below the main device
function plugin_customfields_showAssociated($item, $withtemplate='') {
   global $DB,$CFG_GLPI,$LANG;

   $ID = $item->getField('id');
   $type = get_class($item);

   $query = "SELECT *
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` = '$type'";
   $result = $DB->query($query);
   $info   = $DB->fetch_array($result);
   if ($info['enabled'] != 1) {
      return;
   }

   $entity = 0;
   if (!in_array($type, array('ComputerDisk', 'NetworkPort', 'Entity', 'SoftwareVersion',
                              'SoftwareLicense'))) {
      $table = getTableForItemType($type);
      $query = "SELECT `entities_id`
                FROM `$table`
                WHERE `id`= '$ID'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number==1) {
         $data   = $DB->fetch_array($result);
         $entity = $data['entities_id'];
      }
   }

   $table = plugin_customfields_table($type);
   if ($table) {
      $query = "SELECT *
                FROM `$table`
                WHERE `id`= '$ID'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
   } else {
      return;
   }

   $item = new $type();
   if ($number != 1) {// No data found, so make a link to activate custom fields for this device
      if ($item->canCreate() && $withtemplate != 2) {
         echo '<div class="center b">';
         echo '<a href="'.GLPI_ROOT.'/plugins/customfields/front/itemtype.form.php?itemtype='.
                $type.'&amp;id='.$ID.'&amp;add=add">'.
                $LANG['plugin_customfields']['Activate_Custom_Fields'].'</a>';
         echo '</div><br>';
      }
   } else {// Data was found, so display it
      $data = $DB->fetch_array($result);
      $query = "SELECT *
                FROM `glpi_plugin_customfields_fields`
                WHERE `itemtype` = '$type'
                      AND `deleted` = 0
                      AND (`entities` = '*'
                           OR `entities` LIKE '%$entity%')
                ORDER BY sort_order";
      $result = $DB->query($query);

      echo '<form action="'.GLPI_ROOT.'/plugins/customfields/front/itemtype.form.php" '.
            'method="post" name="form_cf">';
      echo '<table class="tab_cadre_fixe">';
      $count = 0;
      while ($fields=$DB->fetch_array($result)) {
         if ($fields['entities'] != '*') {
            $entities = explode(',',$fields['entities']);
            // don't process the field if it shouldn't be shown for this entity
            if (!in_array($entity, $entities)) {
               continue;
            }
         }

         $readonly = false;
         if ($fields['restricted']) {
            $checkfield = $fields['itemtype'].'_'.$fields['system_name'];
            $prof = new pluginCustomfieldsProfile();
            if (!$prof->fieldHaveRight($checkfield, 'w')) {
               $readonly = true;
            }
         }

         $field_name = $fields['system_name'];
         if ($fields['data_type'] != 'sectionhead') {
            $value = $data[$field_name];
         }
         $count++;

         $classnames = '';
         $classes    = '';
         if ($fields['required'] && !$readonly) { // Requiring a readonly field would cause a problem
            $classnames = ' required';
            $classes    = ' class="required"';
         }

         if ($fields['data_type'] == 'sectionhead'
             || $fields['data_type'] == 'notes'
             || $fields['data_type'] == 'text' ) {// */ // comment out this line if you don't want 'Text' (comment) fields to span both columns

            // Display data that should span both columns
            if (!($count % 2)) {
               echo '<td colspan="2"></td></tr>';
               $count++;
            }

            if ($fields['data_type'] == 'sectionhead') {
               echo '<tr><th colspan="4">'.$fields['label'].'</th></tr>';
            } else if ($fields['data_type'] == 'notes') {
               echo '<tr class="tab_bg_1">';
               echo '<td colspan="4" class="middle center tab_bg_1'.$classnames.'">';
               echo $fields['label'].':<br>';
               if (!$readonly) {
                  echo '<textarea name="'.$field_name.'" rows="20" cols="100">'.$value.'</textarea>';
               } else {
                  plugin_customfields_showValue($value, 'height:30em;width:50em;');
               }
               echo '</td></tr>';

            } else {
               echo '<tr class="tab_bg_1">';
               echo '<td class="top">'.$fields['label'].': </td>';
               echo '<td colspan="3" class="center"'.$classes.'>';
               if (!$readonly) {
                  echo '<textarea name="'.$field_name.'" rows="4" cols="75">'.$value.'</textarea>';
               } else {
                  plugin_customfields_showValue($value,'height:6em;width:50em;');
               }
               echo '</td></tr>';
            }
            $count++;

         } else {// display data that only needs a single column
            if ($count % 2) {
               echo '<tr class="tab_bg_1">';
            }
            echo '<td'.$classes.'>'.$fields['label'].': </td>';
            echo '<td'.$classes.'>';

            switch ($fields['data_type']) {
               case 'general' :
                  if (!$readonly) {
                     echo '<input type="text" size="20" value="'.$value.'" name="'.$field_name.'"/>';
                  } else {
                     plugin_customfields_showValue($value);
                  }
                  break;

               case 'dropdown' :
                  if (!$readonly) {
//                     dropdownValue($fields['dropdown_table'], $field_name, $value);
                     //Dropdown::show('Location', array('value'  => $value));
                     $dropdown_obj = new PluginCustomfieldsDropdown;
                     $tmp = $dropdown_obj->find("system_name = '".$fields['system_name']."'");
                     $dropdown = array_shift($tmp);

                     Dropdown::show('PluginCustomfieldsDropdownsItem', array(
                              'condition' => $dropdown['id']." = plugin_customfields_dropdowns_id",
                              'name'      => $field_name,
                              'value'     => $value,
                              'entity'    => $_SESSION['glpiactive_entity']
                              ));
                  } else {
//                     plugin_customfields_showValue(Dropdown::getDropdownName($fields['dropdown_table'],
//                                                                             $value));
                  }
                  break;

               case 'date' :
                  $editcalendar = ($withtemplate!=2) && (!$readonly);
                  Html::showDateFormItem($field_name, $value, true, $editcalendar);
                  break;

               case 'money' :
                  if (!$readonly) {
                     echo '<input type="text" size="16" value="'.Html::formatNumber($value, true).
                           '" name="'.$field_name.'"/>';
                  } else {
                     plugin_customfields_showValue(formatNumber($value, true));
                  }
                  break;

               case 'yesno' :
                  if (!$readonly) {
                     Dropdown::showYesNo($field_name, $value);
                  } else {
                     plugin_customfields_showValue(Dropdown::getYesNo($field_name, $value));
                  }
                  break;

               case 'text' : // only in effect if the condition about 40 lines above is removed
                  if (!$readonly) {
                     echo '<textarea name="'.$field_name.'" rows="4" cols="35">'.$value.'</textarea>';
                  } else {
                     plugin_customfields_showValue($value, 'height:6em;width:23em;');
                  }
                  break;

               case 'number' :
                  if (!$readonly) {
                     echo '<input type="text" size="10" value="'.$value.'" name="'.$field_name.'"/>';
                  } else {
                     plugin_customfields_showValue($value);
                  }
                  break;
            }
            echo '</td>';
            if (!($count % 2)) {
               echo '</tr>';
            }
         }
      }

      if ($count % 2) {
         echo '<td colspan="2"></td></tr>';
      }

      $item = new $type();
      // Show buttons
      if (($count >= 1) && $item->canCreate()) {
         if (CUSTOMFIELDS_AUTOACTIVATE) {
            echo '<tr><td class="center top tab_bg_2" colspan="4">';
         } else {
            echo '<tr><td class="center top tab_bg_2" colspan="2">';
         }
         echo '<input type="submit" class="submit" value="'.
               $LANG['plugin_customfields']['Update_Custom_Fields'].'" name="update"/>';
         echo '<input type="hidden" value="'.$ID.'" name="id"/>';
         echo '<input type="hidden" value="'.$type.'" name="itemtype"/></td>';

         if (!CUSTOMFIELDS_AUTOACTIVATE) {// Must show the delete button if autoactivate is off
            echo '<td class="center tab_bg_2" colspan="2">';
            echo '<div class="center">'.
                 '<input type="submit" class="submit" value="'.$LANG['buttons'][6].
                 '" name="delete"/> <b>'.$LANG['plugin_customfields']['delete_warning'].'</b>'.
                 '</div></td>';
         }
         echo '</tr>';
      }

      echo '</table>';
      echo '</form>';
   }

}


?>
