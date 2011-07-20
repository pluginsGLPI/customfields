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
// Purpose of file: Perform update, activate, and delete actions
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array('computer','printer','networking','monitor','software','peripheral','phone','user','enterprise','contract','infocom','group','entity');
define('GLPI_ROOT', '../../..');

include (GLPI_ROOT.'/inc/includes.php');
if(!isset($_GET['ID'])) $_GET['ID'] = '';
if(!isset($_GET['withtemplate'])) $_GET['withtemplate'] = '';

if (isset($_POST['delete'])) {
   $plugin_customfields = new plugin_customfields($_POST['device_type']);
   if(plugin_customfields_HaveRight($_POST['device_type'],'w')) {
      unset($_POST['device_type']);
      $plugin_customfields->delete($_POST);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_POST['update'])) {
   $plugin_customfields = new plugin_customfields($_POST['device_type']);
   if(plugin_customfields_HaveRight($_POST['device_type'],'w')) {
      $device_type=$_POST['device_type'];
      unset($_POST['device_type']);
      $post=plugin_customfields_transformPost($_POST);
      $plugin_customfields->update($post);
      if(isset($post['_multiselects'])) {
         plugin_customfields_updateMultiselects($device_type,$_POST['ID'],$post['_multiselects']);
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_GET['add']) && isset($_GET['device_type']) && isset($_GET['ID'])) {
   if(plugin_customfields_HaveRight($_GET['device_type'],'w')) {
      plugin_customfields_activate($_GET['device_type'],$_GET['ID']);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}
?>
