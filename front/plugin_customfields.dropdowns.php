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
// Purpose of file: Page used to configue custom dropdown menus
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..');


include (GLPI_ROOT.'/inc/includes.php');
checkRight('config','r'); 

commonHeader($LANG['plugin_customfields']['Manage_Custom_Dropdowns'],$_SERVER['PHP_SELF'],'plugins','customfields');

$haveright = haveRight('config','w');

///////// First process any actions //////////

if(isset($_POST['delete']) && $haveright) {
   foreach($_POST['delete'] as $ID => $garbage) {
      $sql="SELECT * FROM glpi_plugin_customfields_dropdowns WHERE ID='".intval($ID)."';";
      $result = $DB->query($sql);
      $data=$DB->fetch_assoc($result);
      $system_name=$data['system_name'];
      $table=$data['dropdown_table'];

      $sql="DELETE FROM glpi_plugin_customfields_dropdowns WHERE ID='".intval($ID)."' AND system_name='$system_name';";
      $result = $DB->query($sql);

      $sql="DROP TABLE IF EXISTS `$table`;";
      $result = $DB->query($sql);
   }
   glpi_header($_SERVER['HTTP_REFERER']); // Reload so clicking refresh on browser will not re-post old data
}
elseif(isset($_POST['add']) && $haveright) {
   $has_entities=isset($_POST['has_entities']) ? 1 : 0;
   $is_tree=isset($_POST['is_tree']) ? 1 : 0;

   $label=($_POST['label'] !='') ? $_POST['label'] : $LANG['plugin_customfields']['Custom_Dropdown'];
   if ($_POST['system_name']=='') // use label for system name if no system name was provided
      $system_name=plugin_customfields_make_system_name($label);
   else
      $system_name=plugin_customfields_make_system_name($_POST['system_name']);

   $extra='';
   do {
      $sql="SELECT system_name FROM glpi_plugin_customfields_fields WHERE system_name='$system_name$extra' 
         UNION SELECT system_name FROM glpi_plugin_customfields_dropdowns WHERE system_name='$system_name$extra';";
      $result = $DB->query($sql);
      $extra=$extra+1;
      // keep looping until a name for the field is found that isn't already used
   } while(($DB->numrows($result)>0) && ($extra<51)); // if failed to find a name after 50 times, stop trying

   if($extra > 1)
      $system_name=$system_name.($extra - 1); // If the field name wasn't unique, append a number to make it unique

   if ($extra<51) {
      $table="glpi_dropdown_plugin_customfields_$system_name";

      // Save the meta data
      $sql="INSERT INTO glpi_plugin_customfields_dropdowns (system_name,label,has_entities,is_tree,dropdown_table)".
         " VALUES ('$system_name','$label','$has_entities','$is_tree','$table');";
      $result = $DB->query($sql);

      if($has_entities) {
         $entities='`FK_entities` int(11) NOT NULL default \'0\',';
      }
      else {
         $entities='';
      }

      // Create a table for the new dropdown menu
      if(!TableExists($table)) {
         if($is_tree) {
            $sql="CREATE TABLE `$table` (`ID` int(11) NOT NULL auto_increment, ".$entities.
               " `parentID` int(11) NOT NULL default '0',".
               " `name` varchar(255) collate utf8_unicode_ci default NULL,".
               " `completename` varchar(255) collate utf8_unicode_ci default NULL,".
               " `comments` text collate utf8_unicode_ci,".
               " `level` int(11) NOT NULL default '0',".
               " PRIMARY KEY (`ID`))".
               " ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3;";
         }
         else {
            $sql="CREATE TABLE `$table` (`ID` int(11) NOT NULL auto_increment, ".$entities.
               " `name` varchar(255) collate utf8_unicode_ci default NULL,".
               " `comments` text collate utf8_unicode_ci,".
               " PRIMARY KEY (`ID`))".
               " ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3;";
         }
         $result = $DB->query($sql);
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}
elseif(isset($_POST['update']) && $haveright) {
   // Change the default label for the dropdown
   $query="SELECT * FROM glpi_plugin_customfields_dropdowns";
   $result=$DB->query($query);
   while ($data=$DB->fetch_assoc($result)) {
      $ID=$data['ID'];
      $label=$_POST['label'][$ID];
      $sql="UPDATE glpi_plugin_customfields_dropdowns SET label='$label' WHERE ID='$ID';";
      $DB->query($sql);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}

//////////// Show the form /////////////

echo '<div align="center">';

echo '<a href="./plugin_customfields.config.php">'.$LANG['plugin_customfields']['Back_to_Manage'].'</a><br><br>';

echo '<form action="#" method="post">';
echo '<table class="tab_cadre" cellpadding="5">';
echo '<tr><th colspan="5">'.$LANG['plugin_customfields']['Manage_Custom_Dropdowns'].'</th></tr>';
echo '<tr>';
echo '<th>'.$LANG['plugin_customfields']['Label'].'</th>';
echo '<th>'.$LANG['plugin_customfields']['System_Name'].'</th>';
echo '<th>'.$LANG['plugin_customfields']['Uses_Entities'].'</th>';
echo '<th>'.$LANG['plugin_customfields']['Tree_Structure'].'</th>';
echo '<th></th>';
echo '</tr>';

$query="SELECT dd.*, COUNT(linked.ID) AS num_links FROM glpi_plugin_customfields_dropdowns AS dd ".
   " LEFT JOIN glpi_plugin_customfields_fields AS linked ON (linked.dropdown_table = dd.dropdown_table) ".
   " GROUP BY dd.ID ORDER BY label;";
$result=$DB->query($query);

while ($data=$DB->fetch_assoc($result)) {
   $ID = $data['ID'];
   echo '<tr class="tab_bg_1">';
   echo '<td><input name="label['.$ID.']" value="'.htmlspecialchars($data['label']).'" size="20"></td>';
   echo '<td>'.$data['system_name'].'</td>';
   echo '<td align="center">';
   if($data['has_entities']) { // Yes or No
      echo $LANG['choice'][1]; 
   }
   else {
      echo $LANG['choice'][0];
   }
   echo '</td>';
   echo '<td align="center">';
   if($data['is_tree']) {
      echo $LANG['choice'][1]; 
   }
   else {
      echo $LANG['choice'][0];
   }
   echo '</td><td>';
   if($data['num_links']==0 && $haveright) {
      echo '<input name="delete['.$ID.']" class="submit" type="submit" value="'.$LANG['buttons'][6].'">';
   }
   else {
      echo str_replace('NNN',$data['num_links'],$LANG['plugin_customfields']['Used_by_NNN_devices']);
   }
   echo '</td></tr>';
}
if($haveright) {
   echo '<tr><td align="center" valign="top" class="tab_bg_2" colspan="6">';
   if($DB->numrows($result)>0) {
      echo '<input type="submit" name="update" value="'.$LANG['buttons'][7].'" class="submit"/>';
   }
   else {
      echo $LANG['plugin_customfields']['no_dd_yet'];
   }
   echo '</td></tr>';
}
echo '</table>';
echo '</form>';
   
if($haveright) {
   echo '<br><form action="#" method="post">';
   echo '<table class="tab_cadre" cellpadding="4">';
   echo '<tr><th colspan="5">'.$LANG['plugin_customfields']['Add_New_Dropdown'].'</th></tr>';
   echo '<tr>';
   echo '<th>'.$LANG['plugin_customfields']['Label'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['System_Name'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Uses_Entities'].'</th>';
   echo '<th>'.$LANG['plugin_customfields']['Tree_Structure'].'</th>';
   echo '<th></th>';
   echo '</tr>';
   echo '<tr class="tab_bg_1">';
   echo '<td><input name="label" size="20"></td>';
   echo '<td><input name="system_name"></td>';
   echo '<td align="center"><input name="has_entities" type="checkbox"></td>';
   echo '<td align="center"><input name="is_tree" type="checkbox"></td>';
   echo '<td><input name="add" class="submit" type="submit" value="'.$LANG['buttons'][8].'"></td>';
   echo '</tr>';
   echo '</table>';
   echo '</form>';
}
echo '</div>';


commonFooter();

?>
