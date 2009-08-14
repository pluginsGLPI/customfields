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
$version = '1.0.0';

//Français

$LANGCUSTOMFIELDS['title'] = $title;

// General
$LANGCUSTOMFIELDS['Enabled'] = 'Activé';
$LANGCUSTOMFIELDS['Disabled'] = 'Désactivé';
$LANGCUSTOMFIELDS['Enable'] = 'Activer';
$LANGCUSTOMFIELDS['Disable'] = 'Désactiver';
$LANGCUSTOMFIELDS['Device_Type'] = 'Type de composant';
$LANGCUSTOMFIELDS['Status'] = 'Statut';
$LANGCUSTOMFIELDS['Label'] = 'Libellé';
$LANGCUSTOMFIELDS['System_Name'] = 'Nom interne DB';
$LANGCUSTOMFIELDS['Update_Custom_Fields'] = 'Actualiser '.$title;
$LANGCUSTOMFIELDS['delete_warning'] = '(Attention: annulation impossible!)';

// Manage Custom Fields
$LANGCUSTOMFIELDS['Manage_Custom_Fields'] = 'Gérer les '.$title;
$LANGCUSTOMFIELDS['Type'] = $LANG['common'][17];
$LANGCUSTOMFIELDS['Sort'] = 'Tri';
$LANGCUSTOMFIELDS['Hidden'] = 'Caché';
$LANGCUSTOMFIELDS['no_cf_yet'] = 'Aucun champ personnalisé n\'existe. Ajoutez-en ci-dessous.';
$LANGCUSTOMFIELDS['Add_New_Field'] = 'Ajouter un nouveau champ';
$LANGCUSTOMFIELDS['Clone_Field'] = 'Cloner un champ (depuis un autre type de composant)';
$LANGCUSTOMFIELDS['Field'] = 'Champ';
$LANGCUSTOMFIELDS['Add_Custom_Dropdown'] = 'Ajouter une liste déroulante';
$LANGCUSTOMFIELDS['Dropdown_Name'] = 'Nom de la liste';
$LANGCUSTOMFIELDS['status_of_cf'] = 'Statut du plugin '.$title.' pour ce composant';
$LANGCUSTOMFIELDS['Activate_Custom_Fields'] = 'Activer '.$title;
$LANGCUSTOMFIELDS['add_fields_first'] = 'Vous devez ajouter des champs avant de pouvoir activer le plugin pour ce type de composant.';
$LANGCUSTOMFIELDS['cf_enabled'] = 'Les Champs personnalisés sont activés pour ce type de composant.';
$LANGCUSTOMFIELDS['cf_disabled'] = 'Les Champs personnalisés sont désactivés pour ce type de composant.';
$LANGCUSTOMFIELDS['Custom_Field'] = 'Champ personnalisé'; // the default name for a field if left blank

// Manage Custom Dropdowns
$LANGCUSTOMFIELDS['Manage_Custom_Dropdowns'] = 'Gérer les listes déroulantes';
$LANGCUSTOMFIELDS['Back_to_Manage'] = 'Retour vers '.$LANGCUSTOMFIELDS['Manage_Custom_Fields'];
$LANGCUSTOMFIELDS['Uses_Entities'] = 'Utiliser les Entités';
$LANGCUSTOMFIELDS['Tree_Structure'] = 'Structure arborescente';
$LANGCUSTOMFIELDS['Used_by_NNN_devices'] = 'Champ utilisé par NNN composant(s)'; // IMPORTANT: Use NNN where the number should go
$LANGCUSTOMFIELDS['no_dd_yet'] = 'Aucune liste déroulante définie pour l\'instant. Ajoutez-en ci-dessous.';
$LANGCUSTOMFIELDS['Add_New_Dropdown'] = 'Ajouter une liste déroulante';
$LANGCUSTOMFIELDS['Custom_Dropdown'] = 'Liste déroulante'; //the default name of a custom dropdown

// Data Types
$LANGCUSTOMFIELDS['dropdown'] = 'Liste déroulante';
$LANGCUSTOMFIELDS['general'] = 'General';
$LANGCUSTOMFIELDS['text'] = 'Texte';
$LANGCUSTOMFIELDS['text_explained'] = 'Texte (lignes multiples)';
$LANGCUSTOMFIELDS['notes'] = 'Notes';
$LANGCUSTOMFIELDS['notes_explained'] = 'Notes (zone de texte)';
$LANGCUSTOMFIELDS['date'] = 'Date';
$LANGCUSTOMFIELDS['number'] = 'Nombre';
$LANGCUSTOMFIELDS['money'] = 'Monétaire';
$LANGCUSTOMFIELDS['yesno'] = 'Oui/Non';
$LANGCUSTOMFIELDS['sectionhead'] = 'Entête de section';

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
$LANGCUSTOMFIELDS['setup'][3] = 'Configuration du plugin '.$title;
$LANGCUSTOMFIELDS['setup'][4] = 'Installer le plugin '.$title.' '.$version;
$LANGCUSTOMFIELDS['setup'][5] = 'Mettre à jour le plugin '.$title.' vers la version '.$version;
$LANGCUSTOMFIELDS['setup'][6] = 'Désinstaller le plugin '.$title.' '.$version;
$LANGCUSTOMFIELDS['setup'][7] = 'Le plugin n\'est pas compatible avec la version de GLPI. Merci de mettre à jour le plugin avec une version plus récente';
$LANGCUSTOMFIELDS['setup'][8] = 'Attention: Si vous désinstallez ce plugin, toutes les données personnalisées seront supprimées.';
$LANGCUSTOMFIELDS['setup'][9] = 'Ãtes-vous sÃ»r de vouloir dÃ©sinstaller?';
$LANGCUSTOMFIELDS['setup'][11] = 'Mode d\'emploi';
$LANGCUSTOMFIELDS['setup'][12] = 'FAQ';
$LANGCUSTOMFIELDS['setup'][14] = 'Merci de vous placer sur l\'entité racine  (voir tous)';
?>
