<?php
/*
 * @version $Id$
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
// Purpose of file: Page to add and manage custom fields.
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..');

include (GLPI_ROOT.'/inc/includes.php');
checkRight('config', 'r');

commonHeader($LANG['plugin_customfields']['Manage_Custom_Fields'], $_SERVER['PHP_SELF'], 'plugins',
             'customfields');

if (isset($_GET['itemtype'])) {
   $itemtype = $_GET['itemtype'];

   ////////// First process any actions ///////////

   if (isset($_POST['enable'])) {// Enable custom fields for this device type
      $sql = "SELECT COUNT(`id`) AS num_cf
              FROM `glpi_plugin_customfields_fields`
              WHERE `itemtype` = '$itemtype'
                    AND `data_type` <> 'sectionhead'";
      $result = $DB->query($sql);
      $data   = $DB->fetch_assoc($result);
      if ($data['num_cf']>0) {// Need at least one custom field (not including section headings) before enabling
         global $ACTIVE_CUSTOMFIELDS_TYPES;
         $ACTIVE_CUSTOMFIELDS_TYPES[] = $itemtype;
         $query = "UPDATE `glpi_plugin_customfields_itemtypes`
                   SET `enabled` = 1
                   WHERE `itemtype` = '$itemtype'";
         $DB->query($query);

         if (CUSTOMFIELDS_AUTOACTIVATE) {
            plugin_customfields_activate_all($itemtype);
         }
         addMessageAfterRedirect($LANG['plugin_customfields']['cf_enabled']);
      }
      glpi_header($_SERVER['HTTP_REFERER']); // So clicking refresh on browser will not send post data again
   }

   if (isset($_POST['disable'])) {// Disable custom fields for this device type
      plugin_customfields_disable_device($itemtype);
      glpi_header($_SERVER['HTTP_REFERER']); // So clicking refresh on browser will send post data again

   } else if(isset($_POST['delete'])) {// Delete a field
      foreach($_POST['delete'] as $id => $garbage) {
         $sql = "SELECT *
                 FROM `glpi_plugin_customfields_fields`
                 WHERE `itemtype` = '$itemtype'
                       AND `id` = '".intval($id)."'";
         $result = $DB->query($sql);
         $data   = $DB->fetch_assoc($result);
         $system_name = $data['system_name'];
         $sopt_pos    = $data['sopt_pos']+5200; // 5200 is the beginning of the range reserved for customfields

         // Check if the field is in the history log
         $sql = "SELECT COUNT(`id`) AS history_found
                 FROM `glpi_logs`
                 WHERE `itemtype` = '$itemtype'
                       AND `id_search_option` = '$sopt_pos'";
         $result = $DB->query($sql);
         $data   = $DB->fetch_assoc($result);

         if ($data['history_found']) {
            // Keep a record of the deleted field for the log
            $sql = "UPDATE `glpi_plugin_customfields_fields`
                    SET `deleted` = 1,
                        `system_name` = 'DELETED',
                        `sort_order` = 0,
                        `dropdown_table` = ''
                   WHERE `itemtype` = '$itemtype'
                         AND `id` = '".intval($id)."'
                         AND `system_name` = '$system_name'";
         } else {
            // Nothing in the history log, so delete the field completely
            $sql = "DELETE
                    FROM `glpi_plugin_customfields_fields`
                    WHERE `itemtype` = '$itemtype'
                          AND `id` = '".intval($id)."'
                          AND `system_name` = '$system_name'";
         }
         $result = $DB->query($sql);
         $table = plugin_customfields_table($itemtype);

         $sql = "SELECT COUNT(`id`) AS num_left
                 FROM `glpi_plugin_customfields_fields`
                 WHERE `itemtype` = '$itemtype'
                       AND `data_type` <> 'sectionhead'
                       AND `deleted` = 0";
         $result = $DB->query($sql);
         $data   = $DB->fetch_assoc($result);
         if ($data['num_left']==0) {// If no more fields, drop the data table
            $sql = "DROP TABLE IF EXISTS `$table`";
            plugin_customfields_disable_device($itemtype);
         } else {
            // Remove the column from the data table
            $sql = "ALTER TABLE `$table`
                    DROP `$system_name`";
         }
         $result = $DB->query($sql);
      }
      glpi_header($_SERVER['HTTP_REFERER']); // So clicking refresh on browser will not send post data again

   } else if(isset($_POST['add'])) {// Add a field
      $data_ok = false;
      $sort    = intval($_POST['sort']);

      if (isset($_POST['dropdown_id'])) {// Add a drop down menu
         $sql = "SELECT *
                 FROM `glpi_plugin_customfields_dropdowns`
                 WHERE `id` = '".intval($_POST['dropdown_id'])."'";

         if ($result = $DB->query($sql)) {
            $data        = $DB->fetch_assoc($result);
            $system_name = $data['system_name'];
            $label       = addslashes_deep($data['name']);
            $dd_table    = $data['dropdown_table'];
            $data_type   = 'dropdown';
            $data_ok     = true;
         }

      } else {// Add a normal field
         if (isset($_POST['clonedata'])) {
            list($system_name, $data_type, $label) = explode(',', $_POST['clonedata'], 3);
            $system_name = plugin_customfields_make_system_name($system_name); // clean up in case of tampering
         } else {
            $label = ($_POST['label'] !='') ? $_POST['label']
                                            : $LANG['plugin_customfields']['Custom_Field'];
            if ($_POST['system_name']=='') {// If the system name was left blank, use the label
               $system_name = plugin_customfields_make_system_name($label);
            } else {
               $system_name = plugin_customfields_make_system_name($_POST['system_name']);
            }
            $data_type = $_POST['data_type'];
         }
         $dd_table = '';
         $extra    = '';

         $maintable = getTableForItemType($itemtype);

         do {
            // Make sure the field name is not already used
            $sql = "SELECT `system_name`
                    FROM `glpi_plugin_customfields_fields`
                    WHERE `itemtype` = '$itemtype'
                          AND `deleted` = 0
                          AND `system_name` = '$system_name$extra'
                    UNION
                    SELECT `system_name`
                    FROM `glpi_plugin_customfields_dropdowns`
                    WHERE `system_name` = '$system_name$extra'";
            $result = $DB->query($sql);

            if ($DB->numrows($result)==0) {
               $sql = "SHOW COLUMNS
                       FROM `$maintable`
                       WHERE Field='$system_name$extra'";
               $result = $DB->query($sql);
            }
            $extra = $extra+1;
         } while (($DB->numrows($result)>0) && ($extra<101)); // Don't try more than 100 times

         if ($extra > 1) {// We need to append a number to make it unique
            $system_name = $system_name.($extra - 1);
         }

         if ($extra<101) {
            $data_ok = true;
         }
      }

      if ($data_ok) {
         // Get next search option position
         $sql = "SELECT MAX(`sopt_pos`)+1 AS next_sopt_pos
                 FROM `glpi_plugin_customfields_fields`
                 WHERE `itemtype` = '$itemtype'";

         $result   = $DB->query($sql);
         $data     = $DB->fetch_assoc($result);
         $sopt_pos = $data['next_sopt_pos'];
         if (!$sopt_pos) {
            $sopt_pos = 1;
         }

         $sql = "INSERT INTO `glpi_plugin_customfields_fields`
                        (`itemtype`, `system_name`, `label`, `data_type`, `sort_order`,
                         `dropdown_table`, `deleted`, `sopt_pos`, `restricted`)
                 VALUES ('$itemtype', '$system_name', '$label', '$data_type', '$sort',
                         '$dd_table', 0, '$sopt_pos', 0)";
         $result = $DB->query($sql);

         if ($data_type!='sectionhead') {// add the field to the data table if it isn't a section header
            $table = plugin_customfields_table($itemtype);

            if (CUSTOMFIELDS_AUTOACTIVATE) {
               plugin_customfields_activate_all($itemtype); // creates table and activates IF necessary
            } else {
               plugin_customfields_create_data_table($itemtype); // creates table if it doesn't alreay exist
            }

            switch ($data_type) {
               case 'general' :
                  $db_data_type = 'VARCHAR(255) collate utf8_unicode_ci default NULL';
                  break;

               case 'yesno' :
                  $db_data_type = 'SMALLINT(6) NOT NULL default \'0\'';
                  break;

               case 'text' :
                  $db_data_type = 'TEXT collate utf8_unicode_ci';
                  break;

               case 'notes' :
                  $db_data_type = 'LONGTEXT collate utf8_unicode_ci';
                  break;

               case 'date' :
                  $db_data_type = 'DATE default NULL';
                  break;

               case 'money' :
                  $db_data_type = 'DECIMAL(20,4) NOT NULL default \'0.0000\'';
                  break;

               case 'dropdown' :
               case 'number' :
               default :
                  $db_data_type = 'INT(11) NOT NULL default \'0\'';
                  break;
            }

            $sql = "ALTER TABLE `$table`
                    ADD `$system_name` $db_data_type;";
            $result = $DB->query($sql);
         }
      }
      glpi_header($_SERVER['HTTP_REFERER']);

   } else if(isset($_POST['update'])) {// Update labels, sort order, etc.
      $query = "SELECT *
                FROM `glpi_plugin_customfields_fields`
                WHERE `itemtype` = '$itemtype'
                      AND `deleted` = 0
                ORDER BY `sort_order`";
      $result = $DB->query($query);

      while ($data=$DB->fetch_assoc($result)) {
         $ID         = $data['id'];
         $label      = $_POST['label'][$ID];
         $sort       = intval($_POST['sort'][$ID]);
         $required   = isset($_POST['required'][$ID]) ? 1 : 0;
         $entities   = trim($_POST['entities'][$ID]);
         $restricted = isset($_POST['restricted'][$ID]) ? 1 : 0;

         $sql = "UPDATE `glpi_plugin_customfields_fields`
                 SET `label` = '$label',
                     `sort_order` = '$sort',
                     `required` = '$required',
                     `entities` = '$entities',
                     `restricted` = '$restricted'
                 WHERE `itemtype` = '$itemtype'
                       AND `id` = '$ID'";
         $DB->query($sql);

         if ($restricted==1 && $data['restricted']==0) {
            $sql = "ALTER TABLE `glpi_plugin_customfields_profiles`
                    ADD `{$itemtype}_{$data['system_name']}` char(1) default NULL";
            $DB->query($sql);
         } else if ($restricted==0 && $data['restricted']==1) {
            $sql = "ALTER TABLE `glpi_plugin_customfields_profiles`
                    DROP `{$itemtype}_{$data['system_name']}`";
            $DB->query($sql);
         }
      }
      glpi_header($_SERVER['HTTP_REFERER']);
   }


   //////// Display the page //////////

   $query = "SELECT *
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` = '$itemtype'";
   $result = $DB->query($query);
   $data   = $DB->fetch_assoc($result);

   echo '<div class="center">';

   echo '<form action="?itemtype='.$itemtype.'" method="post">';
   echo '<table class="tab_cadre" cellpadding="5">';
   echo '<tr><th colspan="8">'.$LANG['plugin_customfields']['title'].
         ' ('.$LANG['plugin_customfields']['device_type'][$itemtype].')</th></tr>';
   echo '<tr>';
   echo '<th>'.$LANG['plugin_customfields']['Label'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['System_Name'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Type'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Sort'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Required'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Restricted'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Entities'].'</th>';
   echo '<th></th>';
   echo '</tr>';

   $query = "SELECT *
             FROM `glpi_plugin_customfields_fields`
             WHERE `itemtype` = '$itemtype'
                   AND `deleted` = 0
             ORDER BY `sort_order`";
   $result = $DB->query($query);

   $numdatafields = 0;
   while ($data=$DB->fetch_assoc($result)) {
      $ID = $data['id'];
      echo '<tr class="tab_bg_1">';
      echo '<td><input name="label['.$ID.']" value="'.htmlspecialchars($data['label']).'" size="20"></td>';
      echo '<td>'.$data['system_name'].'</td>';
      echo '<td>'.$LANG['plugin_customfields'][$data['data_type']].'</td>';
      echo '<td><input name="sort['.$ID.']" value="'.$data['sort_order'].'" size="2"></td>';
      if ($data['data_type']!='sectionhead') {
         echo '<td class="center"><input name="required['.$ID.']" type="checkbox"';
         if ($data['required']) {
            echo ' checked="checked"';
         }
         echo '></td>';
      } else {
         echo '<td></td>';
      }
      echo '<td class="center"><input name="restricted['.$ID.']" type="checkbox"';
      if ($data['restricted']) {
         echo ' checked="checked"';
      }
      echo '></td>';
      echo '<td><input name="entities['.$ID.']" value="'.$data['entities'].'" size="7"></td>';
      echo '<td><input name="delete['.$ID.']" class="submit" type="submit" value=\''.$LANG['buttons'][6].'\'></td>';
      echo '</tr>';
      if ($data['data_type'] != 'sectionhead') {
         $numdatafields++;
      }
   }
   echo '<tr><td class="center top tab_bg_2" colspan="8">';
   if ($DB->numrows($result)>0) {
      echo '<input type="submit" name="update" value=\''.$LANG['buttons'][7].'\' class="submit"/>';
   } else {
      echo $LANG['plugin_customfields']['no_cf_yet'];
   }
   echo '</td></tr>';
   echo '</table>';
   echo '</form>';

   // Form to add fields
   echo '<br><form action="?itemtype='.$itemtype.'" method="post">';
   echo '<table class="tab_cadre" cellpadding="5">';
   echo '<tr><th colspan="5">'.$LANG['plugin_customfields']['Add_New_Field'].'</th></tr>';
   echo '<tr>';
   echo '<th>'.$LANG['plugin_customfields']['Label'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['System_Name'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Type'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Sort'].'</th>';
   echo '<th></th>';
   echo '</tr>';
   echo '<tr class="tab_bg_1">';
   echo '<td><input name="label" size="20"></td>';
   echo '<td><input name="system_name"></td>';
   echo '<td><select name="data_type">';
   echo '<option value="general">'.$LANG['plugin_customfields']['general'].'</option>';
   echo '<option value="text">'.$LANG['plugin_customfields']['text_explained'].'</option>';
   echo '<option value="notes">'.$LANG['plugin_customfields']['notes_explained'].'</option>';
   echo '<option value="date">'.$LANG['plugin_customfields']['date'].'</option>';
   echo '<option value="number">'.$LANG['plugin_customfields']['number'].'</option>';
   echo '<option value="money">'.$LANG['plugin_customfields']['money'].'</option>';
   echo '<option value="yesno">'.$LANG['plugin_customfields']['yesno'].'</option>';
   echo '<option value="sectionhead">'.$LANG['plugin_customfields']['sectionhead'].'</option>';
   echo '</select></td>';
   echo '<td><input name="sort" size="2"></td>';
   echo '<td><input name="add" class="submit" type="submit" value=\''.$LANG['buttons'][8].'\'></td>';
   echo '</tr>';
   echo '</table>';
   echo '</form>';

   // Show clone field form if there are any fields that can be cloned
   $query = "SELECT DISTINCT `system_name`, `data_type`, `label`
             FROM `glpi_plugin_customfields_fields`
             WHERE `data_type` <> 'dropdown'
                   AND `itemtype` <> '$itemtype'
                   AND `deleted` = 0
                   AND `system_name` NOT IN (SELECT `system_name`
                                             FROM `glpi_plugin_customfields_fields`
                                             WHERE `itemtype` = '$itemtype'
                                                   AND `deleted` = 0)
             ORDER BY `label`";
   $result = $DB->query($query);

   if ($DB->numrows($result) > 0) {
      echo '<br><form action="?itemtype='.$itemtype.'" method="post">';
      echo '<table class="tab_cadre" cellpadding="5">';
      echo '<tr><th colspan="4">'.$LANG['plugin_customfields']['Clone_Field'].'</th></tr>';
      echo '<tr>';
      echo '<th>'.$LANG['plugin_customfields']['Field'].'</th>';
      echo '<th>'.$LANG['plugin_customfields']['Sort'].'</th>';
      echo '<th></th>';
      echo '</tr>';
      echo '<tr class="tab_bg_1">';
      echo '<td><select name="clonedata">';
      while ($data=$DB->fetch_assoc($result)) {
         echo '<option value="'.$data['system_name'].','.$data['data_type'].','.htmlspecialchars($data['label']).'">'.
         $data['label'].' ('.$data['system_name'].') - '.$LANG['plugin_customfields'][$data['data_type']].'</option>';
      }
      echo '</select></td>';
      echo '<td><input name="sort" size="2"></td>';
      echo '<td><input name="add" class="submit" type="submit" value=\''.$LANG['buttons'][8].'\'></td>';
      echo '</tr>';
      echo '</table>';
      echo '</form>';
   }

   // Form to add drop down menus
   $query = "SELECT dd.*
             FROM `glpi_plugin_customfields_dropdowns` AS dd
             LEFT JOIN `glpi_plugin_customfields_fields` AS more
               ON (more.`dropdown_table` = dd.`dropdown_table`
                   AND more.`itemtype` = '$itemtype'
                   AND more.`deleted` = 0)
             WHERE more.`id` IS NULL
             ORDER BY dd.`name`";
   $result = $DB->query($query);

   if ($DB->numrows($result) > 0) {
      echo '<br><form action="?itemtype='.$itemtype.'" method="post">';
      echo '<table class="tab_cadre" cellpadding="5">';
      echo '<tr><th colspan="3"><a href="./plugin_customfields.dropdowns.php">'.
                              $LANG['plugin_customfields']['Add_Custom_Dropdown'].'</a></th></tr>';
      echo '<tr>';
      echo '<th>'.$LANG['plugin_customfields']['Dropdown_Name'].'</th>';
      echo '<th>'.$LANG['plugin_customfields']['Sort'].'</th>';
      echo '<th></th>';
      echo '</tr>';
      echo '<tr class="tab_bg_1">';
      echo '<td><select name="dropdown_id">';
      while ($data=$DB->fetch_assoc($result)) {
         echo '<option value="'.$data['id'].'">'.$data['name'].'</option>';
      }
      echo '</select></td>';
      echo '<td><input name="sort" value="'.$data['sort_order'].'" size="2"></td>';
      echo '<td><input name="add" class="submit" type="submit" value=\''.$LANG['buttons'][8].'\'></td>';
      echo '</tr>';
      echo '</table>';
      echo '</form>';

   } else {
      echo '<br><a href="./dropdown.php">'.$LANG['plugin_customfields']['Add_Custom_Dropdown'].'</a><br>';
   }

   // Form to enable or disable custom fields for this device type
   $query = "SELECT *
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` = '$itemtype'";
   $result = $DB->query($query);
   $data   = $DB->fetch_assoc($result);

   echo '<br><form action="?itemtype='.$itemtype.'" method="post">';
   echo '<table class="tab_cadre" cellpadding="5">';
   echo '<tr class="tab_bg_1"><th>'.$LANG['plugin_customfields']['status_of_cf'].': </th><td>';
   if ($data['enabled']==1) {
      echo $LANG['plugin_customfields']['Enabled'].'</td>'.
           '<td><input class="submit" type="submit" name="disable" value=\''.
                 $LANG['plugin_customfields']['Disable'].'\'>';
   } else {
      echo '<span style="color:#f00;font-weight:bold;">'.$LANG['plugin_customfields']['Disabled'].
           '</span></td>';
      if ($numdatafields > 0) {
         echo '<td><input class="submit" type="submit" name="enable" value=\''.
                    $LANG['plugin_customfields']['Enable'].'\'>';
      } else {
         echo '</tr><tr><td class="tab_bg_2" colspan="2">'.$LANG['plugin_customfields']['add_fields_first'];
      }
   }
   echo '</td></tr>';
   echo '</table>';
   echo '</form>';
   echo '</div>';
}

commonFooter();

?>
