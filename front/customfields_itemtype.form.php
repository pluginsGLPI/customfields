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

define('GLPI_ROOT', '../../..');

include (GLPI_ROOT.'/inc/includes.php');

logDebug("form ",$_POST);
if (!isset($_GET['ID'])) {
   $_GET['ID'] = '';
}
if (!isset($_GET['withtemplate'])) {
   $_GET['withtemplate'] = '';
}

$PluginItem = new PluginCustomfields_Itemtype($_POST['itemtype']);
if (isset($_POST['delete'])) {
   $customfields->check($_POST['id'],'w');
   $customfields->delete($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST['update'])) {
      $PluginItem->update($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_GET['add']) && isset($_GET['itemtype']) && isset($_GET['ID'])) {
   if ($right->HaveRight($_GET['itemtype'],'w')) {
      plugin_customfields_activate($_GET['itemtype'], $_GET['ID']);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}
?>