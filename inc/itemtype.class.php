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



/*
   function getSearchOptions() {
      global $LANG,$DB;

      $tab = array();

      $tab['common'] = $LANG['plugin_customfields']['title'];

   $query="SELECT f.*, dd.is_tree, cf.enabled FROM glpi_plugin_customfieldsitemtypes as cf, glpi_plugin_customfields_fields AS f ".
      " LEFT JOIN glpi_plugin_customfields_dropdowns AS dd ON dd.system_name=f.system_name ".
      " WHERE f.device_type=cf.device_type ".
      " ORDER BY f.device_type, f.sort_order, f.label";
   $result=$DB->query($query);

   $device_type='';
   while ($data=$DB->fetch_assoc($result))
   {
      // Range 5200-7699 used by this plugin
      $lpos = $data['sopt_pos'] + 5200; // first 1000 used for logging
      $spos = $data['sopt_pos'] + 6200; // next 900 used for regular searches
      $xspos = $data['sopt_pos'] + 7100; // next 100 used for extended searches
      if($data['device_type']!=$device_type)
      {
         $mupos = 7200; // last 500 used for mass update
         $table = getTableForItemType($data['itemtype']);
         $table2 = plugin_customfields_table($data['itemtype']);
      }

      $device_type=$data['device_type'];
      if($data['deleted'] || $data['entities']=='' || !$data['enabled']) // preserve names for log history
      {
         if(CUSTOMFIELDS_GLPI_PATCH_APPLIED)
         {
            $sopt[$device_type][$lpos]['name']=$data['label'];
            $sopt[$device_type][$lpos]['field']='';
            $sopt[$device_type][$lpos]['linkfield']='';
            $sopt[$device_type][$lpos]['purpose']='log'; // an extra field used to clean search options
         }
      }
      elseif($data['data_type']=='sectionhead')
      {
         $sopt[$device_type]['customfields_'.$data['system_name']]=$data['label'];
         if($device_type==NETWORKING_PORT_TYPE)
         {
            foreach(array(COMPUTER_TYPE, NETWORKING_TYPE, PRINTER_TYPE, PERIPHERAL_TYPE, PHONE_TYPE) as $type)
               $sopt[$type]['customfields_'.$data['system_name']]=$data['label'];
         }
      }
      elseif($data['data_type']=='dropdown')
      {
         // search, logging, and mass update all work for dropdowns
         $sopt[$device_type][$lpos]['table']=$data['dropdown_table'];
         if($data['is_tree']==1)
            $sopt[$device_type][$lpos]['field']='completename';
         else
            $sopt[$device_type][$lpos]['field']='name';
         $sopt[$device_type][$lpos]['linkfield']=$data['system_name'];
         $sopt[$device_type][$lpos]['name']=$data['label'];

         if($device_type==NETWORKING_PORT_TYPE)
         {
            foreach(array(COMPUTER_TYPE, NETWORKING_TYPE, PRINTER_TYPE, PERIPHERAL_TYPE, PHONE_TYPE) as $type)
            {
               $sopt[$type][$xspos]['table']=$data['dropdown_table'];
               if($data['is_tree']==1)
                  $sopt[$type][$xspos]['field']='completename';
               else
                  $sopt[$type][$xspos]['field']='name';
               $sopt[$type][$xspos]['linkfield']=$data['system_name'];
               $sopt[$type][$xspos]['name']=$data['label'];
               $sopt[$type][$xspos]['forcegroupby']=true;
               $sopt[$type][$xspos]['purpose']='search';
            }
         }
      }
      else
      {
         // Note: Yes/No fields are included in search, logging, and mass update functionality.
         // In the GLPI core they are not usually included.

         // For fields that aren't dropdowns, it is necessary to apply a patch
         // to enable logging and mass update functionality
         if(CUSTOMFIELDS_GLPI_PATCH_APPLIED)
         {
            // for logging (these might need to be the first set of options)
            $sopt[$device_type][$lpos]['table']=$table;
            $sopt[$device_type][$lpos]['field']=$data['system_name'];
            $sopt[$device_type][$lpos]['linkfield']='';
            $sopt[$device_type][$lpos]['name']=$data['label'];
            $sopt[$device_type][$lpos]['purpose']='log'; // an extra field used to clean search options

            // for mass update
            $mupos++;
            $sopt[$device_type][$mupos]['table']=$table2;
            $sopt[$device_type][$mupos]['field']=$data['system_name'];
            $sopt[$device_type][$mupos]['linkfield']=$data['system_name'];
            $sopt[$device_type][$mupos]['name']=$data['label'];
            $sopt[$device_type][$mupos]['purpose']='update'; // an extra field used to clean search options
         }
         // for search
         if($device_type==NETWORKING_PORT_TYPE)
         {
            foreach(array(COMPUTER_TYPE, NETWORKING_TYPE, PRINTER_TYPE, PERIPHERAL_TYPE, PHONE_TYPE) as $type)
            {
               $sopt[$type][$xspos]['table']='glpi_plugin_customfields_networking_ports';
               $sopt[$type][$xspos]['field']=$data['system_name'];
               $sopt[$type][$xspos]['linkfield']='ID';
               $sopt[$type][$xspos]['name']=$data['label'];
               $sopt[$type][$xspos]['forcegroupby']=true;
               $sopt[$type][$xspos]['purpose']='search';
            }
         }
         else
         {
            $sopt[$device_type][$spos]['table']=$table2;
            $sopt[$device_type][$spos]['field']=$data['system_name'];
            $sopt[$device_type][$spos]['linkfield']='ID';
            $sopt[$device_type][$spos]['name']=$data['label'];
            $sopt[$device_type][$spos]['purpose']='search'; // an extra field used to clean search options
         }
      }
   }

   return $sopt;
}*/
}

?>
