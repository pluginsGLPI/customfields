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
$LANG['plugin_customfields']['delete_warning'] = '(Attention: annulation impossible!)';

// Manage Custom Fields
$LANG['plugin_customfields']['Manage_Custom_Fields'] = 'Gérer les '.$title;
$LANG['plugin_customfields']['Type'] = $LANG['common'][17];
$LANG['plugin_customfields']['Sort'] = 'Tri';
$LANG['plugin_customfields']['Required'] = 'Required';
$LANG['plugin_customfields']['Restricted'] = 'Restricted';
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

// Device Types
$LANG['plugin_customfields']['device_type']['Computer']        = $LANG['Menu'][0];
$LANG['plugin_customfields']['device_type']['NetworkEquipment']= $LANG['Menu'][1];
$LANG['plugin_customfields']['device_type']['Printer']         = $LANG['Menu'][2];
$LANG['plugin_customfields']['device_type']['Monitor']         = $LANG['Menu'][3];
$LANG['plugin_customfields']['device_type']['Peripheral']      = $LANG['Menu'][16];
$LANG['plugin_customfields']['device_type']['Software']        = $LANG['Menu'][4];
$LANG['plugin_customfields']['device_type']['Phone']           = $LANG['Menu'][34];
$LANG['plugin_customfields']['device_type']['CartridgeItem']       = $LANG['Menu'][21];
$LANG['plugin_customfields']['device_type']['ConsumableItem']      = $LANG['Menu'][32];
$LANG['plugin_customfields']['device_type']['Contact']         = $LANG['Menu'][22];
$LANG['plugin_customfields']['device_type']['Supplier']        = $LANG['Menu'][23];
$LANG['plugin_customfields']['device_type']['Contract']        = $LANG['Menu'][25];
$LANG['plugin_customfields']['device_type']['Document']        = $LANG['Menu'][27];
$LANG['plugin_customfields']['device_type']['Ticket']          = $LANG['Menu'][5];
$LANG['plugin_customfields']['device_type']['User']            = $LANG['Menu'][14];
$LANG['plugin_customfields']['device_type']['Group']           = $LANG['Menu'][36];
$LANG['plugin_customfields']['device_type']['Entity']          = $LANG['Menu'][37];
$LANG['plugin_customfields']['device_type']['NetworkPort']     = $LANG['networking'][6];
$LANG['plugin_customfields']['device_type']['ComputerDisk']    = $LANG['computers'][8];
$LANG['plugin_customfields']['device_type']['SoftwareVersion'] = 'Software Versions';
$LANG['plugin_customfields']['device_type']['SoftwareLicense'] = 'Software License';
$LANG['plugin_customfields']['device_type']['Device']          = $LANG['title'][30];
$LANG['plugin_customfields']['device_type']['Infocom']          = 'Information financières';
$LANG['plugin_customfields']['device_type']['DeviceMotherboard']  = $LANG['devices'][5];
$LANG['plugin_customfields']['device_type']['DeviceProcessor']  = $LANG['devices'][4];
$LANG['plugin_customfields']['device_type']['DeviceMemory']  = $LANG['devices'][6];
$LANG['plugin_customfields']['device_type']['DeviceHardDrive']  = $LANG['devices'][1];
$LANG['plugin_customfields']['device_type']['DeviceNetworkCard']  = $LANG['devices'][3];
$LANG['plugin_customfields']['device_type']['DeviceDrive']  = $LANG['devices'][19];
$LANG['plugin_customfields']['device_type']['DeviceControl']  = $LANG['devices'][20];
$LANG['plugin_customfields']['device_type']['DeviceGraphicCard']  = $LANG['devices'][2];
$LANG['plugin_customfields']['device_type']['DeviceSoundCard']  = $LANG['devices'][7];
$LANG['plugin_customfields']['device_type']['DevicePci']  = $LANG['devices'][21];
$LANG['plugin_customfields']['device_type']['DeviceCase']  = $LANG['devices'][22];
$LANG['plugin_customfields']['device_type']['DevicePowerSupply']  = $LANG['devices'][23];


// Setup
$LANG['plugin_customfields']['setup'][1] = 'Aucun champ restreint';
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
