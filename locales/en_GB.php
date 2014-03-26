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

//English

$LANG['plugin_customfields']['title'] = $title;

// General
$LANG['plugin_customfields']['Enabled'] = 'Enabled';
$LANG['plugin_customfields']['Disabled'] = 'Disabled';
$LANG['plugin_customfields']['Enable'] = 'Enable';
$LANG['plugin_customfields']['Disable'] = 'Disable';
$LANG['plugin_customfields']['Device_Type'] = 'Device Type';
$LANG['plugin_customfields']['Status'] = 'Status';
$LANG['plugin_customfields']['Label'] = 'Label';
$LANG['plugin_customfields']['System_Name'] = 'System Name';
$LANG['plugin_customfields']['Update_Custom_Fields'] = 'Update '.$title;
$LANG['plugin_customfields']['Update_Financial_CF'] = 'Update Financial CF';
$LANG['plugin_customfields']['delete_warning'] = '(Warning: Can\'t undo!)';
$LANG['plugin_customfields']['No_Fields'] = 'There is no custom field available.';

// Manage Custom Fields
$LANG['plugin_customfields']['Manage_Custom_Fields'] = 'Manage '.$title;
$LANG['plugin_customfields']['Type'] = 'Type';
$LANG['plugin_customfields']['Location'] = 'Location';
$LANG['plugin_customfields']['Sort'] = 'Sort';
$LANG['plugin_customfields']['Required'] = 'Required';
$LANG['plugin_customfields']['Restricted'] = 'Restricted';
$LANG['plugin_customfields']['Unique'] = 'Unique';
$LANG['plugin_customfields']['Entities'] = 'Entities';
$LANG['plugin_customfields']['no_cf_yet'] = 'No custom fields defined yet. Add them below.';
$LANG['plugin_customfields']['Add_New_Field'] = 'Add New Field';
$LANG['plugin_customfields']['Clone_Field'] = 'Clone Field (from another device type)';
$LANG['plugin_customfields']['Field'] = 'Field';
$LANG['plugin_customfields']['Add_Custom_Dropdown'] = 'Add Custom Dropdown';
$LANG['plugin_customfields']['Dropdown_Name'] = 'Dropdown Name';
$LANG['plugin_customfields']['status_of_cf'] = 'Status of '.$title;
$LANG['plugin_customfields']['Activate_Custom_Fields'] = 'Activate '.$title;
$LANG['plugin_customfields']['add_fields_first'] = 'You must add data fields before you can enable custom fields for this device type.';
$LANG['plugin_customfields']['cf_enabled'] = 'Custom fields have been enabled for this device type.';
$LANG['plugin_customfields']['cf_disabled'] = 'Custom fields have been disabled for this device type.';
$LANG['plugin_customfields']['Custom_Field'] = 'Custom Field'; // the default name for a field if left blank
$LANG['plugin_customfields']['multiselect_note'] = 'If creating a multiselect field, put table_name.field_name in the System Name field.';

// Manage Custom Dropdowns
$LANG['plugin_customfields']['Manage_Custom_Dropdowns'] = 'Manage Custom Dropdowns';
$LANG['plugin_customfields']['Back_to_Manage'] = 'Back to '.$LANG['plugin_customfields']['Manage_Custom_Fields'];
$LANG['plugin_customfields']['Uses_Entities'] = 'Uses Entities';
$LANG['plugin_customfields']['Tree_Structure'] = 'Tree Structure';
$LANG['plugin_customfields']['Used_by_NNN_devices'] = 'Used by NNN device(s)'; // IMPORTANT: Use NNN where the number should go
$LANG['plugin_customfields']['no_dd_yet'] = 'No custom dropdowns defined yet. Add them below.';
$LANG['plugin_customfields']['Add_New_Dropdown'] = 'Add New Dropdown';
$LANG['plugin_customfields']['Custom_Dropdown'] = 'Custom Dropdown'; //the default name of a custom dropdown

// Data Types
$LANG['plugin_customfields']['dropdown'] = 'Dropdown';
$LANG['plugin_customfields']['general'] = 'General';
$LANG['plugin_customfields']['text'] = 'Text';
$LANG['plugin_customfields']['text_explained'] = 'Text (multiple lines)';
$LANG['plugin_customfields']['notes'] = 'Notes';
$LANG['plugin_customfields']['notes_explained'] = 'Notes (large text area)';
$LANG['plugin_customfields']['date'] = 'Date';
$LANG['plugin_customfields']['number'] = 'Number';
$LANG['plugin_customfields']['money'] = 'Money';
$LANG['plugin_customfields']['yesno'] = 'Yes/No';
$LANG['plugin_customfields']['sectionhead'] = 'Section Header';
$LANG['plugin_customfields']['multiselect'] = 'Multiselect';

// Setup
$LANG['plugin_customfields']['setup'][2] = 'This plugin requires GLPI version 0.72 or higher';
$LANG['plugin_customfields']['setup'][3] = 'Setup of '.$title.' Plugin';
$LANG['plugin_customfields']['setup'][4] = 'Existing custom field data was found on your system. This data has been restored.';
$LANG['plugin_customfields']['setup'][5] = 'Data from an older version of '.$title.' was found. This data will be upgraded when you activate this plugin.';
$LANG['plugin_customfields']['setup'][7] = 'The plugin files are not compatable with the existing data. Please upgrade your plugin files.';
$LANG['plugin_customfields']['setup'][8] = 'Custom Fields has been uninstalled, but your custom field data has not been removed.';
$LANG['plugin_customfields']['setup'][9] = 'Click here to delete all custom field data.';
$LANG['plugin_customfields']['setup'][10] = 'Are you sure you want to delete all custom field data?';
$LANG['plugin_customfields']['setup'][11] = 'Instructions';
//$LANG['plugin_customfields']['setup'][12] = 'FAQ';
//$LANG['plugin_customfields']['setup'][14] = 'Please change to "Root Entity (Show all)" before installing this plugin';

// Device Types
$LANG['plugin_customfields']['device_type']['SoftwareVersion'] = 'Software Versions';
$LANG['plugin_customfields']['device_type']['SoftwareLicense'] = 'Software License';
include('devices.php');

// Errors
$LANG['plugin_customfields']['error'][1]  = "You have not selected Dropdown";
?>
