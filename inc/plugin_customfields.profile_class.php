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
// Purpose of file: Create a class to take advantage of core features
// such as update and logging.
// ----------------------------------------------------------------------


if (!defined('GLPI_ROOT')) die('Sorry. You can\'t access this file directly.');

class plugin_customfields_Profile extends CommonDBTM {

   function __construct () {
      $this->table="glpi_plugin_customfields_profiledata";
      $this->type=-1;
   }
   
   //if profile deleted
   function cleanProfiles($ID) {
   
      global $DB;
      $query = "DELETE 
               FROM glpi_plugin_customfields_profiledata 
               WHERE ID='$ID' ";
      $DB->query($query);
   }

   function dropdownNoneReadWriteRequired($name,$value){
      global $LANG;
      echo "<select name='$name'>\n";
      echo "<option value='' ".(empty($value)?" selected ":"").">".$LANG['profiles'][12]."</option>\n";
      echo "<option value='r' ".($value=='r'?" selected ":"").">".$LANG['profiles'][10]."</option>\n";
      echo "<option value='w' ".($value=='w'?" selected ":"").">".$LANG['profiles'][11]."</option>\n";
      echo "<option value='q' ".($value=='q'?" selected ":"").">".$LANG['plugin_customfields']['Required']."</option>\n";
      echo "</select>\n";   
   }   
   
   //profiles modification
   function showForm($target,$ID){
      global $LANG, $DB;

      if (!haveRight("profile","r")) return false;
      $canedit=haveRight("profile","w");
      $prof = new Profile();
      if ($ID){
         $this->getFromDB($ID);
         $prof->getFromDB($ID);
      }
      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";

      $device_type = 0;
   
      $query="SELECT * FROM `glpi_plugin_customfields_fields` WHERE `restricted`=1 ORDER BY `device_type`, `sort_order`;";
      if ($result=$DB->query($query))
      {
         while($data=$DB->fetch_array($result))
         {
            if ($data['device_type'] != $device_type) {
               $device_type = $data['device_type'];
               echo "<tr><th colspan='2' align='center'><strong>".
                  plugin_customfields_device_type_label($device_type)."</strong></th></tr>";
            }
            $profile_field = $data['device_type'] . '_' . $data['system_name'];
            echo "<tr class='tab_bg_2'><td>".$data['label'].
               " (".$data['system_name'].', '.$LANG['plugin_customfields'][$data['data_type']]."):</td><td>";
            if ($prof->fields['interface']!='helpdesk') {
               if ($data['data_type']=='sectionhead') 
                  dropdownYesNo($profile_field,$this->fields[$profile_field],1,1,1);
               else
                  $this->dropdownNoneReadWriteRequired($profile_field,$this->fields[$profile_field]);
            } else {
               echo $LANG['choice'][0]; // No
            }
            echo "</td></tr>";
         }
      }

      if ($canedit){
         echo "<tr class='tab_bg_1'>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='hidden' name='ID' value=$ID>";
         echo "<input type='submit' name='update_user_profile' value=\"".$LANG['buttons'][7]."\" class='submit'>";
         echo "</td></tr>";
      }
      echo "</table></form>";

   }
}
?>
