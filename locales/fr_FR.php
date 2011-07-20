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
// Translated by: Alain Dubrunfaut
----------------------------------------------------------------------
 */

$title = 'Champs Personnalisés';

//Français

$LANG['plugin_customfields']['title'] = $title;

// General
$LANG['plugin_customfields']['Enabled'] = 'Activé';
$LANG['plugin_customfields']['Disabled'] = 'Désactivé';
$LANG['plugin_customfields']['Enable'] = 'Activer';
$LANG['plugin_customfields']['Disable'] = 'Désactiver';
$LANG['plugin_customfields']['Device_Type'] = 'Type de composant';
$LANG['plugin_customfields']['Status'] = 'Statut';
$LANG['plugin_customfields']['Label'] = 'Libellé';
$LANG['plugin_customfields']['System_Name'] = 'Nom interne DB';
$LANG['plugin_customfields']['Update_Custom_Fields'] = 'Actualiser '.$title;
$LANG['plugin_customfields']['Update_Financial_CF'] = 'Update Financial CF';
$LANG['plugin_customfields']['delete_warning'] = '(Attention: annulation impossible!)';

// Manage Custom Fields
$LANG['plugin_customfields']['Manage_Custom_Fields'] = 'Gérer les '.$title;
$LANG['plugin_customfields']['Type'] = $LANG['common'][17];
$LANG['plugin_customfields']['Location'] = 'Location';
$LANG['plugin_customfields']['Sort'] = 'Tri';
$LANG['plugin_customfields']['Required'] = 'Required';
$LANG['plugin_customfields']['Restricted'] = 'Restricted';
$LANG['plugin_customfields']['Unique'] = 'Unique';
$LANG['plugin_customfields']['Entities'] = 'Entities';
$LANG['plugin_customfields']['no_cf_yet'] = 'Aucun champ personnalisé n\'existe. Ajoutez-en ci-dessous.';
$LANG['plugin_customfields']['Add_New_Field'] = 'Ajouter un nouveau champ';
$LANG['plugin_customfields']['Clone_Field'] = 'Cloner un champ (depuis un autre type de composant)';
$LANG['plugin_customfields']['Field'] = 'Champ';
$LANG['plugin_customfields']['Add_Custom_Dropdown'] = 'Ajouter une liste déroulante';
$LANG['plugin_customfields']['Dropdown_Name'] = 'Nom de la liste';
$LANG['plugin_customfields']['status_of_cf'] = 'Statut du plugin '.$title.' pour ce composant';
$LANG['plugin_customfields']['Activate_Custom_Fields'] = 'Activer '.$title;
$LANG['plugin_customfields']['add_fields_first'] = 'Vous devez ajouter des champs avant de pouvoir activer le plugin pour ce type de composant.';
$LANG['plugin_customfields']['cf_enabled'] = 'Les Champs personnalisés sont activés pour ce type de composant.';
$LANG['plugin_customfields']['cf_disabled'] = 'Les Champs personnalisés sont désactivés pour ce type de composant.';
$LANG['plugin_customfields']['Custom_Field'] = 'Champ personnalisé'; // the default name for a field if left blank
$LANG['plugin_customfields']['multiselect_note'] = 'If creating a multiselect field, put table_name.field_name in the System Name field.';

// Manage Custom Dropdowns
$LANG['plugin_customfields']['Manage_Custom_Dropdowns'] = 'Gérer les listes déroulantes';
$LANG['plugin_customfields']['Back_to_Manage'] = 'Retour vers '.$LANG['plugin_customfields']['Manage_Custom_Fields'];
$LANG['plugin_customfields']['Uses_Entities'] = 'Utiliser les Entités';
$LANG['plugin_customfields']['Tree_Structure'] = 'Structure arborescente';
$LANG['plugin_customfields']['Used_by_NNN_devices'] = 'Champ utilisé par NNN composant(s)'; // IMPORTANT: Use NNN where the number should go
$LANG['plugin_customfields']['no_dd_yet'] = 'Aucune liste déroulante définie pour l\'instant. Ajoutez-en ci-dessous.';
$LANG['plugin_customfields']['Add_New_Dropdown'] = 'Ajouter une liste déroulante';
$LANG['plugin_customfields']['Custom_Dropdown'] = 'Liste déroulante'; //the default name of a custom dropdown

// Data Types
$LANG['plugin_customfields']['dropdown'] = 'Liste déroulante';
$LANG['plugin_customfields']['general'] = 'General';
$LANG['plugin_customfields']['text'] = 'Texte';
$LANG['plugin_customfields']['text_explained'] = 'Texte (lignes multiples)';
$LANG['plugin_customfields']['notes'] = 'Notes';
$LANG['plugin_customfields']['notes_explained'] = 'Notes (zone de texte)';
$LANG['plugin_customfields']['date'] = 'Date';
$LANG['plugin_customfields']['number'] = 'Nombre';
$LANG['plugin_customfields']['money'] = 'Monétaire';
$LANG['plugin_customfields']['yesno'] = 'Oui/Non';
$LANG['plugin_customfields']['sectionhead'] = 'Entête de section';
$LANG['plugin_customfields']['multiselect'] = 'Multiselect';

// Device Types
$LANG['plugin_customfields']['device_type'][COMPUTER_TYPE]  = $LANG['Menu'][0];
$LANG['plugin_customfields']['device_type'][NETWORKING_TYPE]= $LANG['Menu'][1];
$LANG['plugin_customfields']['device_type'][PRINTER_TYPE]   = $LANG['Menu'][2];
$LANG['plugin_customfields']['device_type'][MONITOR_TYPE]   = $LANG['Menu'][3];
$LANG['plugin_customfields']['device_type'][PERIPHERAL_TYPE]= $LANG['Menu'][16];
$LANG['plugin_customfields']['device_type'][SOFTWARE_TYPE]  = $LANG['Menu'][4];
$LANG['plugin_customfields']['device_type'][PHONE_TYPE]     = $LANG['Menu'][34];
$LANG['plugin_customfields']['device_type'][CARTRIDGE_TYPE] = $LANG['Menu'][21];
$LANG['plugin_customfields']['device_type'][CONSUMABLE_TYPE]= $LANG['Menu'][32];
$LANG['plugin_customfields']['device_type'][CONTACT_TYPE]   = $LANG['Menu'][22];
$LANG['plugin_customfields']['device_type'][ENTERPRISE_TYPE]= $LANG['Menu'][23];
$LANG['plugin_customfields']['device_type'][CONTRACT_TYPE]  = $LANG['Menu'][25];
$LANG['plugin_customfields']['device_type'][DOCUMENT_TYPE]  = $LANG['Menu'][27];
$LANG['plugin_customfields']['device_type'][TRACKING_TYPE]  = $LANG['Menu'][5];
$LANG['plugin_customfields']['device_type'][USER_TYPE]      = $LANG['Menu'][14];
$LANG['plugin_customfields']['device_type'][GROUP_TYPE]     = $LANG['Menu'][36];
$LANG['plugin_customfields']['device_type'][ENTITY_TYPE]    = $LANG['Menu'][37];
$LANG['plugin_customfields']['device_type'][NETWORKING_PORT_TYPE] = $LANG['networking'][6];
$LANG['plugin_customfields']['device_type'][COMPUTERDISK_TYPE] = $LANG['computers'][8]; 
$LANG['plugin_customfields']['device_type'][SOFTWAREVERSION_TYPE] = 'Software Versions'; 
$LANG['plugin_customfields']['device_type'][SOFTWARELICENSE_TYPE] = 'Software License'; 
$LANG['plugin_customfields']['device_type'][DEVICE_TYPE]    = $LANG['title'][30]; 

$LANG['plugin_customfields']['component_type'][MOBOARD_DEVICE]   = $LANG['devices'][5]; 
$LANG['plugin_customfields']['component_type'][PROCESSOR_DEVICE] = $LANG['devices'][4]; 
$LANG['plugin_customfields']['component_type'][RAM_DEVICE]       = $LANG['devices'][6]; 
$LANG['plugin_customfields']['component_type'][HDD_DEVICE]       = $LANG['devices'][1]; 
$LANG['plugin_customfields']['component_type'][NETWORK_DEVICE]   = $LANG['devices'][3]; 
$LANG['plugin_customfields']['component_type'][DRIVE_DEVICE]     = $LANG['devices'][19]; 
$LANG['plugin_customfields']['component_type'][CONTROL_DEVICE]   = $LANG['devices'][20]; 
$LANG['plugin_customfields']['component_type'][GFX_DEVICE]       = $LANG['devices'][2]; 
$LANG['plugin_customfields']['component_type'][SND_DEVICE]       = $LANG['devices'][7]; 
$LANG['plugin_customfields']['component_type'][PCI_DEVICE]       = $LANG['devices'][21];
$LANG['plugin_customfields']['component_type'][CASE_DEVICE]      = $LANG['devices'][22]; 
$LANG['plugin_customfields']['component_type'][POWER_DEVICE]     = $LANG['devices'][23]; 

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
?>
