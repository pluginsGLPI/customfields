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

// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Main configuration page
// ----------------------------------------------------------------------

include ('../../../inc/includes.php');

$plugin = new Plugin();

// Check if plugin is installed and enabled

if ($plugin->isActivated("customfields")) {

   // Check ACL

   Session::checkRight("config", 'w');

   // Header

   Html::header(
      __('Configuration'),
      $_SERVER['PHP_SELF'],
      'plugins',
      'customfields'
   );

   echo "<div class='center'>";
   
   echo "<table class='tab_cadre' cellpadding='5'>";
   echo "<tr><th colspan='4'>"
      . $LANG['plugin_customfields']['Manage_Custom_Fields']
      . "</th></tr>";
   echo "<tr><th>" . $LANG['plugin_customfields']['Device_Type'] . "</th>";
   echo "<th>" . $LANG['plugin_customfields']['Status'] . "</th></tr>";

   // List supported item types
   
   $query = "SELECT *
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` <> 'Version'
             ORDER BY `id`";
   
   $result = $DB->query($query);

   while ($data = $DB->fetch_assoc($result)) {

      if (class_exists($data['itemtype'])) {

         $item = new $data['itemtype']();

         if ($item->canCreate()) {

            // List only, if the user can create an object of the type

            echo "<tr class='tab_bg_1'>";
            echo "<td><a href='./manage.php?itemtype="
               . $data['itemtype']
               . "'>"
               . call_user_func(
                  array(
                     $data['itemtype'],
                     'getTypeName'
                  )
               )
               . "</a></td>";

            // Show enabled or disabled?

            if ($data['enabled'] == 1) {

               echo "<td class='b'>"
                  . $LANG['plugin_customfields']['Enabled']
                  . "</td>";

            } else {

               echo "<td><i>"
                  . $LANG['plugin_customfields']['Disabled']
                  . "</i></td>";

            }

            echo "</tr>";

         }

      }

   }

   echo "</table><br>";

   // Custom dropdowns
   
   echo "<table class='tab_cadre' cellpadding='5'>";
   echo "<tr><th>" . $LANG['plugin_customfields']['setup'][3] . "</th></tr>";
   echo "<tr class='tab_bg_1'><td class='center'>";
   echo "<a href='./dropdown.php'>"
      . $LANG['plugin_customfields']['Manage_Custom_Dropdowns']
      . "</a>";
   echo "</td></tr>";
   echo "</table></div>";
   
} else {

   // Custom fields plugin not activated

   Html::header($LANG['common'][12], $_SERVER['PHP_SELF'], "config", "plugins");
   echo "<div class='center'><br><br>"
      . "<img src=\""
      . $CFG_GLPI["root_doc"]
      . "/pics/warning.png\" alt='warning'><br><br>";

   // text is hard coded because language setting are not accessible

   echo "<b>Please activate the plugin</b></div>";

}

// Footer

if (strstr($_SERVER['PHP_SELF'], "popup")) {
   Html::popFooter();
} else {
   Html::footer();
}