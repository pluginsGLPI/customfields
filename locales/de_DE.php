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

$title = 'Benutzerdefinierte Felder';

//English

$LANG['plugin_customfields']['title'] = $title;

// General
$LANG['plugin_customfields']['Enabled'] = 'Aktiviert';
$LANG['plugin_customfields']['Disabled'] = 'Deaktiviert';
$LANG['plugin_customfields']['Enable'] = 'Aktiviere';
$LANG['plugin_customfields']['Disable'] = 'Deaktiviere';
$LANG['plugin_customfields']['Device_Type'] = 'Gerätetyp';
$LANG['plugin_customfields']['Status'] = 'Status';
$LANG['plugin_customfields']['Label'] = 'Bezeichnung';
$LANG['plugin_customfields']['System_Name'] = 'Interner Name';
$LANG['plugin_customfields']['Update_Custom_Fields'] = $title.' aktualisieren';
$LANG['plugin_customfields']['delete_warning'] = '(Warnung! Kann nicht rückgängig gemacht werden)';
$LANG['plugin_customfields']['No_Fields'] = 'There is no custom field available.';

// Manage Custom Fields
$LANG['plugin_customfields']['Manage_Custom_Fields'] = $title.' verwalten';
$LANG['plugin_customfields']['Type'] = 'Typ';
$LANG['plugin_customfields']['Location'] = 'Ort';
$LANG['plugin_customfields']['Sort'] = 'Sortieren';
$LANG['plugin_customfields']['Required'] = 'Benötigt';
$LANG['plugin_customfields']['Restricted'] = 'Beschränkt';
$LANG['plugin_customfields']['Unique'] = 'Eindeutig';
$LANG['plugin_customfields']['Entities'] = 'Einheiten';
$LANG['plugin_customfields']['no_cf_yet'] = 'Keine benutzerdefinierten Felder konfiguriert. Fügen Sie diese unten hinzu.';
$LANG['plugin_customfields']['Add_New_Field'] = 'Neues Feld hinzufügen';
$LANG['plugin_customfields']['Clone_Field'] = 'Feld klonen (von einem anderen Gerätetyp)';
$LANG['plugin_customfields']['Field'] = 'Feld';
$LANG['plugin_customfields']['Add_Custom_Dropdown'] = 'Benutzerdefinierte Auswahlliste hinzufügen';
$LANG['plugin_customfields']['Dropdown_Name'] = 'Name der Auswahlliste';
$LANG['plugin_customfields']['status_of_cf'] = $title.' - Status';
$LANG['plugin_customfields']['Activate_Custom_Fields'] = $title.' aktivieren';
$LANG['plugin_customfields']['add_fields_first'] = 'Bevor Sie die benutzerdefinierten Felder für diesen Gerätetyp aktivieren können, müssen Sie Felder hinzufügen.';
$LANG['plugin_customfields']['cf_enabled'] = 'Benutzerdefinierte Felder wurden für diesen Gerätetyp aktiviert.';
$LANG['plugin_customfields']['cf_disabled'] = 'Benutzerdefinierte Felder wurden für diesen Gerätetyp deaktiviert.';
$LANG['plugin_customfields']['Custom_Field'] = 'Benutzerdefiniertes Feld'; // the default name for a field if left blank
$LANG['plugin_customfields']['multiselect_note'] = 'Wenn Sie eine Auswahlliste mit Mehrfachauswahl erstellen, geben Sie tabellenname.feldname als interner Name ein.';

// Manage Custom Dropdowns
$LANG['plugin_customfields']['Manage_Custom_Dropdowns'] = 'Benutzerdefinierte Auswahllisten verwalten';
$LANG['plugin_customfields']['Back_to_Manage'] = 'Zurück zu '.$LANG['plugin_customfields']['Manage_Custom_Fields'];
$LANG['plugin_customfields']['Uses_Entities'] = 'Benutzt Einheit';
$LANG['plugin_customfields']['Tree_Structure'] = 'Baumstruktur';
$LANG['plugin_customfields']['Used_by_NNN_devices'] = 'Benutzt von NNN Gerät(en)'; // IMPORTANT: Use NNN where the number should go
$LANG['plugin_customfields']['no_dd_yet'] = 'Keine benutzerdefinierten Auswahllisten erstellt. Fügen Sie diese unten hinzu.';
$LANG['plugin_customfields']['Add_New_Dropdown'] = 'Neue Auswahlliste erstellen';
$LANG['plugin_customfields']['Custom_Dropdown'] = 'Benutzerdefinierte Auswahlliste'; //the default name of a custom dropdown

// Data Types
$LANG['plugin_customfields']['dropdown'] = 'Auswahlliste';
$LANG['plugin_customfields']['general'] = 'Allgemein';
$LANG['plugin_customfields']['text'] = 'Text';
$LANG['plugin_customfields']['text_explained'] = 'Text (Mehrzeilig)';
$LANG['plugin_customfields']['notes'] = 'Notizfeld';
$LANG['plugin_customfields']['notes_explained'] = 'Notizfeld (großer Eingabebereich)';
$LANG['plugin_customfields']['date'] = 'Datum';
$LANG['plugin_customfields']['number'] = 'Zahl';
$LANG['plugin_customfields']['money'] = 'Währung';
$LANG['plugin_customfields']['yesno'] = 'Ja/Nein';
$LANG['plugin_customfields']['sectionhead'] = 'Überschrift';
$LANG['plugin_customfields']['multiselect'] = 'Mehrfachauswahl';

// Setup
$LANG['plugin_customfields']['setup'][1] = 'There is no restricted field';
$LANG['plugin_customfields']['setup'][2] = 'Dieses plugin erfordert GLPI version 0.72 oder höher';
$LANG['plugin_customfields']['setup'][3] = 'Setup des '.$title.' Plugins';
$LANG['plugin_customfields']['setup'][4] = 'Vorhandene Daten wurden gefunden. Diese wurden wiederhergestellt.';
$LANG['plugin_customfields']['setup'][5] = 'Daten einer älteren Version gefunden. Diese Daten werden aktualisiert, wenn Sie das Plugin aktivieren.';
$LANG['plugin_customfields']['setup'][7] = 'Die Plugindateien sind nicht kompatibel mit den vorhandenen Daten. Bitte aktualisieren Sie zunächst das Plugin.';
$LANG['plugin_customfields']['setup'][8] = 'Das Plugin wurde deinstalliert, aber die Daten wurden nicht gelöscht.';
$LANG['plugin_customfields']['setup'][9] = 'Hier klicken, um alle Daten zu löschen.';
$LANG['plugin_customfields']['setup'][10] = 'Wollen Sie wirklich alle Daten löschen?';
$LANG['plugin_customfields']['setup'][11] = 'Anleitung';
//$LANG['plugin_customfields']['setup'][12] = 'FAQ';
//$LANG['plugin_customfields']['setup'][14] = 'Please change to "Root Entity (Show all)" before installing this plugin';

// Device Types
$LANG['plugin_customfields']['device_type']['SoftwareVersion'] = 'Softwareversion';
$LANG['plugin_customfields']['device_type']['SoftwareLicense'] = 'Softwarelizenz';
include('devices.php');

// Errors
$LANG['plugin_customfields']['error'][1]  = "Sie haben keine Auswahlliste ausgewählt";
$LANG['plugin_customfields']['No_Fields'] = "Es sind keine benutzerdefinierten Felder verfügbar.";
