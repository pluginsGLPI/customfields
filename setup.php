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
// Purpose of file: Used to initialize the plugin and define its actions.
// ----------------------------------------------------------------------

// If auto activate set to true, custom fields will be automatically
// added when a new record is inserted. If set to false, users must
// click 'Activate custom fields' to add additional information.
define('CUSTOMFIELDS_AUTOACTIVATE', true);

$ACTIVE_CUSTOMFIELDS_TYPES = array();
$ALL_CUSTOMFIELDS_TYPES = array();

include_once ('inc/function.php');
include_once ('inc/itemtype.class.php');
include_once ('inc/profile.class.php');

// Initialize the plugin's hooks (this function is required)
function plugin_init_customfields() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $DB, $ACTIVE_CUSTOMFIELDS_TYPES, $ALL_CUSTOMFIELDS_TYPES;

   $PLUGIN_HOOKS['change_profile']['customfields'] = array('PluginCustomfieldsProfile','changeprofile');

   Plugin::registerClass('PluginCustomfieldsDropdowns');
   Plugin::registerClass('PluginCustomfieldsFields');

   if (isset($_SESSION['glpiID'])){
      $plugin = new Plugin();
 //     if ($plugin->isActivated("customfields")) {
         $query = "SELECT `itemtype`, `enabled`
                   FROM `glpi_plugin_customfields_itemtypes`
                   WHERE `itemtype` <> 'Version'";
         $result = $DB->query($query);

         while ($data=$DB->fetch_assoc($result)) {
            $ALL_CUSTOMFIELDS_TYPES[] = $data['itemtype'];
            if ($data['enabled']) {
               $ACTIVE_CUSTOMFIELDS_TYPES[] = $data['itemtype'];
            }
         }

         $query = "SELECT *
                   FROM `glpi_plugin_customfields_dropdowns`
                   WHERE `has_entities` = 1
                         OR `is_tree` = 1";
         $result=$DB->query($query);

         while ($data=$DB->fetch_assoc($result)) {
            if ($data['has_entities']==1) {
               array_push($CFG_GLPI['specif_entities_tables'], $data['dropdown_table']);
            }
            if ($data['is_tree']==1) {
               array_push($CFG_GLPI['dropdowntree_tables'], $data['dropdown_table']);
            }
         }

         // Display a menu entry in the main menu if the user has configuration rights
         if (haveRight('config','w')) {
            $PLUGIN_HOOKS['menu_entry']['customfields'] = true;
         }

         // Menus for each device type
         $PLUGIN_HOOKS['headings']['customfields'] = 'plugin_get_headings_customfields';
         $PLUGIN_HOOKS['headings_action']['customfields'] = 'plugin_headings_actions_customfields';

         // Functions to run when data changes
         foreach($ACTIVE_CUSTOMFIELDS_TYPES as $type) {
            $PLUGIN_HOOKS['item_add']['customfields'][$type]='plugin_item_add_customfields';
            $PLUGIN_HOOKS['pre_item_update']['customfields'][$type] = 'plugin_pre_item_update_customfields';
         }
         foreach($ALL_CUSTOMFIELDS_TYPES as $type) {
            $PLUGIN_HOOKS['item_purge']['customfields'][$type] = 'plugin_item_purge_customfields';
         }

         // Define how to import data into custom fields with the Data_Injection plugin
         $PLUGIN_HOOKS['data_injection']['customfields'] = 'plugin_customfields_data_injection_variables';
 //     }

      // Indicate where the configuration page can be found
      if (haveRight('config','w')) {
         $PLUGIN_HOOKS['config_page']['customfields'] = 'front/config.form.php';
      }
   }
}


// Get the name and the version of the plugin (required function)
function plugin_version_customfields() {
   global $LANG;

   return array('name'           => $LANG['plugin_customfields']['title'],
                'author'         => 'Oregon State Data Center, Nelly Mahu Lasson',
                'homepage'       => 'https://forge.indepnet.net/projects/show/customfields',
                'minGlpiVersion' => '0.78',
                'version'        => '1.2');
}

// Checks prerequisites before install. May print errors or add message after redirect
function plugin_customfields_check_prerequisites() {

   if (GLPI_VERSION < 0.78) {
      echo "GLPI version not compatible need 0.78";
   } else {
      return true;
   }
/*
                $plugin = new Plugin();

		// Automatically upgrade db (if necessary) when plugin is activated
		if (haveRight('config','w') && $plugin->isActivated("customfields"))
		{
			global $DB;
			// Check the version of the database tables.
			$query="SELECT enabled FROM glpi_plugin_customfields WHERE device_type='-1';";
			$result = $DB->query($query);
			$data=$DB->fetch_array($result);
			$dbversion=$data['enabled']; // Version of the last modification to the plugin tables' structure

                	if(CUSTOMFIELDS_AUTOACTIVATE)
				plugin_customfields_activate_all_types();
		}
		return true;
	}
*/
}

// Check configuration process for plugin : need to return true if succeeded
function plugin_customfields_check_config() {
   return true;
}



?>
