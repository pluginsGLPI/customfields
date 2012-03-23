<?php

//Generate classes for each itemtype managed by the plugin
$query = "SELECT *
             FROM `glpi_plugin_customfields_itemtypes`
             WHERE `itemtype` <> 'Version'
             ORDER BY `id`";
$result = $DB->query($query);
while ($data=$DB->fetch_assoc($result)) {
   if (!class_exists("PluginCustomfields".$data['itemtype'], false)) {
      eval("class PluginCustomfields".$data['itemtype']." extends CommonDBTM {}");
   }
}

?>
