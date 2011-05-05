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
// Translator to Spanish: Jorge López Díaz
----------------------------------------------------------------------
 */

$title = 'Campos Personalizados';

//Français

$LANG['plugin_customfields']['title'] = $title;

// General
$LANG['plugin_customfields']['Enabled']               = 'Habilitado';
$LANG['plugin_customfields']['Disabled']              = 'Deshabilitado';
$LANG['plugin_customfields']['Enable']                = 'Habilitar';
$LANG['plugin_customfields']['Disable']               = 'Deshabilitar';
$LANG['plugin_customfields']['Device_Type']           = 'Tipo de dispositivo';
$LANG['plugin_customfields']['Status']                = 'Estado';
$LANG['plugin_customfields']['Label']                 = 'Etiqueta';
$LANG['plugin_customfields']['System_Name']           = 'Nombre en el sistema';
$LANG['plugin_customfields']['Update_Custom_Fields']  = 'Actualizar '.$title;
$LANG['plugin_customfields']['delete_warning']        = '(¡Atención: no se puede deshacer!)';

// Manage Custom Fields
$LANG['plugin_customfields']['Manage_Custom_Fields']   = 'Gestionar '.$title;
$LANG['plugin_customfields']['Type']                   = $LANG['common'][17];
$LANG['plugin_customfields']['Sort']                   = 'Ordenar';
$LANG['plugin_customfields']['Required']               = 'Requerido';
$LANG['plugin_customfields']['Restricted']             = 'Restringido';
$LANG['plugin_customfields']['Entities']               = 'Entidades';
$LANG['plugin_customfields']['no_cf_yet']              = 'No hay campos personalizado todavía. Añádalos abajo';
$LANG['plugin_customfields']['Add_New_Field']          = 'Añadir nuevo';
$LANG['plugin_customfields']['Clone_Field']            = 'Clonar campo (desde otro tipo de dispoitivo)';
$LANG['plugin_customfields']['Field']                  = 'Campo';
$LANG['plugin_customfields']['Add_Custom_Dropdown']    = 'Añadir campo desplegable';
$LANG['plugin_customfields']['Dropdown_Name']          = 'Nombre del campo desplegable';
$LANG['plugin_customfields']['status_of_cf']           = 'Estado de '.$title;
$LANG['plugin_customfields']['Activate_Custom_Fields'] = 'Activar '.$title;
$LANG['plugin_customfields']['add_fields_first']       = 'Hay que añadir datos antes de que se puedan habilitar campos personalizados para este tipo de dispositivo.';
$LANG['plugin_customfields']['cf_enabled']             = 'Campos personalizados habilitados para este tipo de dispositivo.';
$LANG['plugin_customfields']['cf_disabled']            = 'Campos personalizado deshabilitados para este tipo de dispositivo.';
$LANG['plugin_customfields']['Custom_Field']           = 'Campo personalizado'; // the default name for a field if left blank

// Manage Custom Dropdowns
$LANG['plugin_customfields']['Manage_Custom_Dropdowns']  = 'Gestionar desplegables personalizados';
$LANG['plugin_customfields']['Back_to_Manage']           = 'Volver a '.$LANG['plugin_customfields']['Manage_Custom_Fields'];
$LANG['plugin_customfields']['Uses_Entities']            = 'Usa Entidades';
$LANG['plugin_customfields']['Tree_Structure']           = 'Estructura en árbol';
$LANG['plugin_customfields']['Used_by_NNN_devices']      = 'Usado por NNN dispositvo(s)'; // IMPORTANT: Use NNN where the number should go
$LANG['plugin_customfields']['no_dd_yet']                = 'No hay campos desplegables definidos todavía. Añádalos abajo.';
$LANG['plugin_customfields']['Add_New_Dropdown']         = 'Añadir nuevo desplegable';
$LANG['plugin_customfields']['Custom_Dropdown']          = 'Desplegable personalizado'; //the default name of a custom dropdown

// Data Types
$LANG['plugin_customfields']['dropdown']        = 'Desplegable';
$LANG['plugin_customfields']['general']         = 'General';
$LANG['plugin_customfields']['text']            = 'Texto';
$LANG['plugin_customfields']['text_explained']  = 'Texto (múltiples líneas)';
$LANG['plugin_customfields']['notes']           = 'Notas';
$LANG['plugin_customfields']['notes_explained'] = 'Notas (área grande de texto)';
$LANG['plugin_customfields']['date']            = 'Fecha';
$LANG['plugin_customfields']['number']          = 'Número';
$LANG['plugin_customfields']['money']           = 'Dinero';
$LANG['plugin_customfields']['yesno']           = 'Sí/No';
$LANG['plugin_customfields']['sectionhead']     = 'Cabecera de sección';

// Device Types
// Device Types
$LANG['plugin_customfields']['device_type'][COMPUTER_TYPE]        = $LANG['Menu'][0];
$LANG['plugin_customfields']['device_type'][NETWORKING_TYPE]      = $LANG['Menu'][1];
$LANG['plugin_customfields']['device_type'][PRINTER_TYPE]         = $LANG['Menu'][2];
$LANG['plugin_customfields']['device_type'][MONITOR_TYPE]         = $LANG['Menu'][3];
$LANG['plugin_customfields']['device_type'][PERIPHERAL_TYPE]      = $LANG['Menu'][16];
$LANG['plugin_customfields']['device_type'][SOFTWARE_TYPE]        = $LANG['Menu'][4];
$LANG['plugin_customfields']['device_type'][PHONE_TYPE]           = $LANG['Menu'][34];
$LANG['plugin_customfields']['device_type'][CARTRIDGE_TYPE]       = $LANG['Menu'][21];
$LANG['plugin_customfields']['device_type'][CONSUMABLE_TYPE]      = $LANG['Menu'][32];
$LANG['plugin_customfields']['device_type'][CONTACT_TYPE]         = $LANG['Menu'][22];
$LANG['plugin_customfields']['device_type'][ENTERPRISE_TYPE]      = $LANG['Menu'][23];
$LANG['plugin_customfields']['device_type'][CONTRACT_TYPE]        = $LANG['Menu'][25];
$LANG['plugin_customfields']['device_type'][DOCUMENT_TYPE]        = $LANG['Menu'][27];
$LANG['plugin_customfields']['device_type'][TRACKING_TYPE]        = $LANG['Menu'][5];
$LANG['plugin_customfields']['device_type'][USER_TYPE]            = $LANG['Menu'][14];
$LANG['plugin_customfields']['device_type'][GROUP_TYPE]           = $LANG['Menu'][36];
$LANG['plugin_customfields']['device_type'][ENTITY_TYPE]          = $LANG['Menu'][37];
$LANG['plugin_customfields']['device_type'][NETWORKING_PORT_TYPE] = $LANG['networking'][6];
$LANG['plugin_customfields']['device_type'][COMPUTERDISK_TYPE]    = $LANG['computers'][8];
$LANG['plugin_customfields']['device_type'][SOFTWAREVERSION_TYPE] = 'Software Versions';
$LANG['plugin_customfields']['device_type'][SOFTWARELICENSE_TYPE] = 'Software License';
$LANG['plugin_customfields']['device_type'][DEVICE_TYPE]           = $LANG['title'][30];

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
$LANG['plugin_customfields']['setup'][1] = 'Any restricted field';
$LANG['plugin_customfields']['setup'][2] = 'Este plugin requiere la version 0.72  o mayor de GLPI.';
$LANG['plugin_customfields']['setup'][3] = 'Configuración de '.$title.' Plugin';
$LANG['plugin_customfields']['setup'][4] = 'Se encontraron datos de este plugin anteriores que han sido restaurados.';
$LANG['plugin_customfields']['setup'][5] = 'Se encontraron datos de una versión anterior de '.$title.'. Se actualizarán cuando active este plugin.';
$LANG['plugin_customfields']['setup'][7] = 'Los archivos del plugin no son compatibles con los datos existentes. Por favor, actualícelos.';
$LANG['plugin_customfields']['setup'][8] = $title.' desinstalado pero sin borrar los datos existentes.';
$LANG['plugin_customfields']['setup'][9] = 'Pulse aquí para borrar los datos de los campos personalizados.';
$LANG['plugin_customfields']['setup'][10] = '¿Está seguro de que quiere borrar todos los datos de los campos personalizados?';
$LANG['plugin_customfields']['setup'][11] = 'Instrucciones';
//$LANG['plugin_customfields']['setup'][12] = 'FAQ';
//$LANG['plugin_customfields']['setup'][14] = 'Please change to "Root Entity (Show all)" before installing this plugin';
?>
