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
class PluginCustomfieldsItemtype extends CommonDBTM {

   function __construct($itemtype = PLUGIN_CUSTOMFIELDS_TYPE) {

      $this->type       = $itemtype;
      $this->dohistory  = true;
      $this->forceTable(plugin_customfields_table($itemtype));
   }


   static function getTypes () {

      static $types = array('Cartridge', 'Computer', 'ComputerDisk', 'Consumable', 'Contact',
                            'Contract', 'Document', 'Entity', 'Group', 'Monitor', 'NetworkEquipment',
                            'NetworkPort', 'Peripheral', 'Phone', 'Printer', 'Software',
                            'SoftwareLicense', 'SoftwareVersion', 'Supplier', 'Ticket', 'User');

      foreach ($types as $key=>$type) {
         if (!class_exists($type)) {
            continue;
         }
         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }


   function getRestricted($itemtype) {
      global $DB;

      $query = "SELECT *
                FROM `glpi_plugin_customfields_fields`
                WHERE `itemtype` = '$itemtype'
                      AND `restricted` = 1";

      if ($result = $DB->query($query)) {
         if ($data = $DB->fetch_assoc($result)) {
            $right = $itemtype."_". $data['system_name'];
         }
      }
      $query = "SELECT *
                FROM `glpi_plugin_customfields_profiles`
                WHERE `$right` = 'w'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)>0){
            return haveRight($itemtype, 'w');
         }
      }

   }


}

?>
