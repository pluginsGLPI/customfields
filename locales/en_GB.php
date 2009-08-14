<?php
/*

   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/

   ----------------------------------------------------------------------
   LICENSE

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License (GPL)
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   To read the license please visit http://www.gnu.org/copyleft/gpl.html
   ----------------------------------------------------------------------
// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Language file
----------------------------------------------------------------------
 */

$title = 'Custom Fields';
$version = '1.0.1';

//English

$LANGCUSTOMFIELDS['title'] = $title;

// General
$LANGCUSTOMFIELDS['Enabled'] = 'Enabled';
$LANGCUSTOMFIELDS['Disabled'] = 'Disabled';
$LANGCUSTOMFIELDS['Enable'] = 'Enable';
$LANGCUSTOMFIELDS['Disable'] = 'Disable';
$LANGCUSTOMFIELDS['Device_Type'] = 'Device Type';
$LANGCUSTOMFIELDS['Status'] = 'Status';
$LANGCUSTOMFIELDS['Label'] = 'Label';
$LANGCUSTOMFIELDS['System_Name'] = 'System Name';
$LANGCUSTOMFIELDS['Update_Custom_Fields'] = 'Update '.$title;
$LANGCUSTOMFIELDS['delete_warning'] = '(Warning: Can\'t undo!)';

// Manage Custom Fields
$LANGCUSTOMFIELDS['Manage_Custom_Fields'] = 'Manage '.$title;
$LANGCUSTOMFIELDS['Type'] = $LANG['common'][17];
$LANGCUSTOMFIELDS['Sort'] = 'Sort';
$LANGCUSTOMFIELDS['Hidden'] = 'Hidden';
$LANGCUSTOMFIELDS['no_cf_yet'] = 'No custom fields defined yet. Add them below.';
$LANGCUSTOMFIELDS['Add_New_Field'] = 'Add New Field';
$LANGCUSTOMFIELDS['Clone_Field'] = 'Clone Field (from another device type)';
$LANGCUSTOMFIELDS['Field'] = 'Field';
$LANGCUSTOMFIELDS['Add_Custom_Dropdown'] = 'Add Custom Dropdown';
$LANGCUSTOMFIELDS['Dropdown_Name'] = 'Dropdown Name';
$LANGCUSTOMFIELDS['status_of_cf'] = 'Status of '.$title;
$LANGCUSTOMFIELDS['Activate_Custom_Fields'] = 'Activate '.$title;
$LANGCUSTOMFIELDS['add_fields_first'] = 'You must add data fields before you can enable custom fields for this device type.';
$LANGCUSTOMFIELDS['cf_enabled'] = 'Custom fields have been enabled for this device type.';
$LANGCUSTOMFIELDS['cf_disabled'] = 'Custom fields have been disabled for this device type.';
$LANGCUSTOMFIELDS['Custom_Field'] = 'Custom Field'; // the default name for a field if left blank

// Manage Custom Dropdowns
$LANGCUSTOMFIELDS['Manage_Custom_Dropdowns'] = 'Manage Custom Dropdowns';
$LANGCUSTOMFIELDS['Back_to_Manage'] = 'Back to '.$LANGCUSTOMFIELDS['Manage_Custom_Fields'];
$LANGCUSTOMFIELDS['Uses_Entities'] = 'Uses Entities';
$LANGCUSTOMFIELDS['Tree_Structure'] = 'Tree Structure';
$LANGCUSTOMFIELDS['Used_by_NNN_devices'] = 'Used by NNN device(s)'; // IMPORTANT: Use NNN where the number should go
$LANGCUSTOMFIELDS['no_dd_yet'] = 'No custom dropdowns defined yet. Add them below.';
$LANGCUSTOMFIELDS['Add_New_Dropdown'] = 'Add New Dropdown';
$LANGCUSTOMFIELDS['Custom_Dropdown'] = 'Custom Dropdown'; //the default name of a custom dropdown

// Data Types
$LANGCUSTOMFIELDS['dropdown'] = 'Dropdown';
$LANGCUSTOMFIELDS['general'] = 'General';
$LANGCUSTOMFIELDS['text'] = 'Text';
$LANGCUSTOMFIELDS['text_explained'] = 'Text (multiple lines)';
$LANGCUSTOMFIELDS['notes'] = 'Notes';
$LANGCUSTOMFIELDS['notes_explained'] = 'Notes (large text area)';
$LANGCUSTOMFIELDS['date'] = 'Date';
$LANGCUSTOMFIELDS['number'] = 'Number';
$LANGCUSTOMFIELDS['money'] = 'Money';
$LANGCUSTOMFIELDS['yesno'] = 'Yes/No';
$LANGCUSTOMFIELDS['sectionhead'] = 'Section Header';

// Device Types
$LANGCUSTOMFIELDS['device_type'][COMPUTER_TYPE]  = $LANG['Menu'][0];
$LANGCUSTOMFIELDS['device_type'][NETWORKING_TYPE]= $LANG['Menu'][1];
$LANGCUSTOMFIELDS['device_type'][PRINTER_TYPE]   = $LANG['Menu'][2];
$LANGCUSTOMFIELDS['device_type'][MONITOR_TYPE]   = $LANG['Menu'][3];
$LANGCUSTOMFIELDS['device_type'][PERIPHERAL_TYPE]= $LANG['Menu'][16];
$LANGCUSTOMFIELDS['device_type'][SOFTWARE_TYPE]  = $LANG['Menu'][4];
$LANGCUSTOMFIELDS['device_type'][PHONE_TYPE]     = $LANG['Menu'][34];
$LANGCUSTOMFIELDS['device_type'][CARTRIDGE_TYPE] = $LANG['Menu'][21];
$LANGCUSTOMFIELDS['device_type'][CONSUMABLE_TYPE]= $LANG['Menu'][32];
$LANGCUSTOMFIELDS['device_type'][CONTACT_TYPE]   = $LANG['Menu'][22];
$LANGCUSTOMFIELDS['device_type'][ENTERPRISE_TYPE]= $LANG['Menu'][23];
$LANGCUSTOMFIELDS['device_type'][CONTRACT_TYPE]  = $LANG['Menu'][25];
$LANGCUSTOMFIELDS['device_type'][DOCUMENT_TYPE]  = $LANG['Menu'][27];
$LANGCUSTOMFIELDS['device_type'][TRACKING_TYPE]  = $LANG['Menu'][5];
$LANGCUSTOMFIELDS['device_type'][42] = $LANG['networking'][6]; // remove in 0.72
//$LANGCUSTOMFIELDS['device_type'][NETWORKING_PORT_TYPE] = $LANG['networking'][6]; //add in 0.72
$LANGCUSTOMFIELDS['device_type'][DEVICE_TYPE]    = $LANG['title'][30]; 

$LANGCUSTOMFIELDS['component_type'][MOBOARD_DEVICE]   = $LANG['devices'][5]; 
$LANGCUSTOMFIELDS['component_type'][PROCESSOR_DEVICE] = $LANG['devices'][4]; 
$LANGCUSTOMFIELDS['component_type'][RAM_DEVICE]       = $LANG['devices'][6]; 
$LANGCUSTOMFIELDS['component_type'][HDD_DEVICE]       = $LANG['devices'][1]; 
$LANGCUSTOMFIELDS['component_type'][NETWORK_DEVICE]   = $LANG['devices'][3]; 
$LANGCUSTOMFIELDS['component_type'][DRIVE_DEVICE]     = $LANG['devices'][19]; 
$LANGCUSTOMFIELDS['component_type'][CONTROL_DEVICE]   = $LANG['devices'][20]; 
$LANGCUSTOMFIELDS['component_type'][GFX_DEVICE]       = $LANG['devices'][2]; 
$LANGCUSTOMFIELDS['component_type'][SND_DEVICE]       = $LANG['devices'][7]; 
$LANGCUSTOMFIELDS['component_type'][PCI_DEVICE]       = $LANG['devices'][21];
$LANGCUSTOMFIELDS['component_type'][CASE_DEVICE]      = $LANG['devices'][22]; 
$LANGCUSTOMFIELDS['component_type'][POWER_DEVICE]     = $LANG['devices'][23]; 

// Setup
$LANGCUSTOMFIELDS['setup'][3] = 'Setup of '.$title.' Plugin';
$LANGCUSTOMFIELDS['setup'][4] = 'Install '.$title.' plugin '.$version;
$LANGCUSTOMFIELDS['setup'][5] = 'Upgrade database tables for '.$title.' plugin to version '.$version;
$LANGCUSTOMFIELDS['setup'][6] = 'Uninstall '.$title.' plugin '.$version;
$LANGCUSTOMFIELDS['setup'][7] = 'The plugin files are not compatable with the database. Please upgrade your plugin files to a newer version';
$LANGCUSTOMFIELDS['setup'][8] = 'Warning: If you uninstall this plugin, all of the data in your custom fields will be removed.';
$LANGCUSTOMFIELDS['setup'][9] = 'Are you sure you want to uninstall it?';
$LANGCUSTOMFIELDS['setup'][11] = 'Instructions';
$LANGCUSTOMFIELDS['setup'][12] = 'FAQ';
$LANGCUSTOMFIELDS['setup'][14] = 'Please change to "Root Entity (Show all)" before installing this plugin';
?>
