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

if (!defined('GLPI_ROOT')) {
   die('Sorry. You can\'t access this file directly.');
}

// CLASS customfields
class PluginCustomfieldsDropdown extends CommonDBTM  {


    // Copies value in a dropdown to a new entity if it does not alreay exist in the new entity.
    // If the menu has a tree stucture, it will also copy any parents that didn't exist in the new entity.
    function transferDropdown($ddID, $dd_table, $newentity) {
       global $DB;

       if ($ddID>0) {
          // Search init item
          $query = "SELECT *
                    FROM `$dd_table`
                    WHERE `id` = '$ddID'";
          if (($result=$DB->query($query)) && ($DB->numrows($result))) {
             $data = $DB->fetch_array($result);
             $data = addslashes_deep($data);

             // Search if the value already exists in the destination entity
             $query = "SELECT `id`
                       FROM `$dd_table`
                       WHERE `entities_id` = '$newentity' ";

             if (isset($data['completename'])) {// it is a tree
                $query .= "AND `completename` = '".$data['completename']."'";
             } else { // it isn't a tree
                $query .= "AND `name` = '".$data['name']."'";
             }

             if ($result_search=$DB->query($query)) {
                // If a match is found, use it
                if ($DB->numrows($result_search)>0) {
                   $newID = $DB->result($result_search,0,'id');
                   return $newID;
                }
             }

             // No match was found, so copy the data to the new entity
             $input = array();
             $input['tablename']     = $dd_table;
             $input['entities_id']   = $newentity;
             $input['value']         = $data['name'];
             $input['comments']      = $data['comments'];
             $input['type']          = "under";
             $input['value2']        = 0; // parentID

             // if parentID > 0 need to recurrsively transfer the parent(s)
             if (isset($data['parentID']) && ($data['parentID']>0)) {
                $input['value2'] = $this->transferDropdown($data['parentID'], $dd_table, $newentity);
             }
             // add the item
             $newID = Dropdown::import($data['itemtype'],$input);
             return $newID;
          }
       }
       return 0;
   }


   // Transfer drop down items to a new entity
   function transferAllDropdowns($itemtype, $newentity) {
      global $DB;

      $updates = array();
      $ID = $item->getField('id');

      if ($ID > 0) {
         $query = "SELECT d.*
                   FROM `glpi_plugin_customfields_fields` AS f,
                        `glpi_plugin_customfields_dropdowns` AS d
                   WHERE f.`itemtype` = '$itemtype'
                         AND f.`data_type` = 'dropdown'
                         AND d.`system_name` = f.`system_name`
                         AND d.`has_entities` = 1";

         if ($result=$DB->query($query)) {
            while($data=$DB->fetch_array($result)) {
               $data_table  = plugin_customfields_table($itemtype);
               $system_name = $data['system_name'];
               $query = "SELECT `$system_name` AS oldID
                         FROM `$data_table`
                         WHERE `id` = '$ID'";

               if (($dd_result=$DB->query($query)) && ($dd_data=$DB->fetch_array($dd_result))) {
                  $newID = $this->transferDropdown($dd_data['oldID'], $data['dropdown_table'],
                                                   $newentity);
                  $updates[$data['system_name']] = $newID;
                }
             }
          }
       }
       return $updates;
    }

}

?>
