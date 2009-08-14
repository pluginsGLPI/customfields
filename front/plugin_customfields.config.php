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

$NEEDED_ITEMS=array('setup');
if(!defined('GLPI_ROOT')){
	define('GLPI_ROOT', '../../..'); 
}
include (GLPI_ROOT.'/inc/includes.php');
checkRight('config','w');

// Check if plugin is installed and enabled
if(!isset($_SESSION['glpi_plugin_customfields_installed']) || $_SESSION['glpi_plugin_customfields_installed']!=1) 
{
	commonHeader($LANG['common'][12],$_SERVER['PHP_SELF'],'config','plugins');
	
	if ($_SESSION['glpiactive_entity']==0)
	{
		if(!TableExists('glpi_plugin_customfields'))
		{
			// The plugin has not been installed yet.
			echo '<div align="center">';
			echo '<table class="tab_cadre" cellpadding="5">';
			echo '<tr><th>'.$LANGCUSTOMFIELDS['setup'][3]; // Setup of Custom Fields Plugin
			echo '</th></tr>';
			echo '<tr class="tab_bg_1"><td>';
			echo '<a href="plugin_customfields.install.php">'.$LANGCUSTOMFIELDS['setup'][4].'</a></td></tr>'; // Install plugin
			echo '</table></div>';
		}
		else
		{
			if(isset($_SESSION['glpi_plugin_customfields_upgrade_required']))
			{
				echo '<div align="center">';
				echo '<table class="tab_cadre" cellpadding="5">';
				echo '<tr><th>'.$LANGCUSTOMFIELDS['setup'][3]; // Setup Custom Fields plugin
				echo '</th></tr>';
				echo '<tr class="tab_bg_1"><td>';
				echo '<a href="plugin_customfields.upgrade.php">'.
					$LANGCUSTOMFIELDS['setup'][5].'</a></td></tr>'; // Upgrade database tables
				echo '</table></div>';
			}
			else
			{
				// Files are older than database...need to upgrade files
				echo '<div align="center"><br><br><img src="'.$CFG_GLPI['root_doc'].'/pics/warning.png" alt="warning"><br><br>'; 
				echo '<b>'.$LANGCUSTOMFIELDS['setup'][7].'</b></div>';
			}
		}
	}
	else
	{ 
		// Need to switch to the root entity (show all) before installing the plugin
		echo '<div align="center"><br><br><img src="'.$CFG_GLPI['root_doc'].'/pics/warning.png" alt="warning"><br><br>'; 
		echo '<b>'.$LANGCUSTOMFIELDS['setup'][14].'</b></div>'; 
	}
}
else // plugin is installed, so show the configuration options
{
	commonHeader($LANG['common'][12],$_SERVER['PHP_SELF'],'plugins','customfields');
	
	echo '<div align="center">';

	echo '<table class="tab_cadre" cellpadding="5"><tr><th colspan="4">';
	echo $LANGCUSTOMFIELDS['Manage_Custom_Fields'].'</th></tr>';
	echo '<tr><th>'.$LANGCUSTOMFIELDS['Device_Type'].'</th><th>'.$LANGCUSTOMFIELDS['Status'].'</th></tr>';

	$query='SELECT * FROM glpi_plugin_customfields WHERE device_type > 0 ORDER BY ID';
	$result=$DB->query($query);

	while ($data=$DB->fetch_assoc($result)){
		echo '<tr class="tab_bg_1">';
		echo '<td><a href="./plugin_customfields.manage.php?device_type='.$data['device_type'].'">'.
			$LANGCUSTOMFIELDS['device_type'][$data['device_type']].'</a></td>';
		if ($data['enabled']==1)
			echo '<td><b>'.$LANGCUSTOMFIELDS['Enabled'].'</b></td>';
		else
			echo '<td><i>'.$LANGCUSTOMFIELDS['Disabled'].'</i></td>';
		echo '</tr>';
	}
	echo '</table><br>';


	echo '<table class="tab_cadre" cellpadding="5">';
	echo '<tr><th>'.$LANGCUSTOMFIELDS['setup'][3]; // Setup of Custom Fields Plugin
	echo '</th></tr>';
	echo '<tr class="tab_bg_1"><td align="center">';
	echo '<a href="./plugin_customfields.dropdowns.php">'.$LANGCUSTOMFIELDS['Manage_Custom_Dropdowns'].'</a>';
	echo '</td></tr>';

	echo '<tr class="tab_bg_1"><td align="center">';
	echo '<a href="http://www.opensourcegov.net/projects/glpi-cf/wiki/Installation_Instructions" target="_blank">'.$LANGCUSTOMFIELDS['setup'][11].'</a>&nbsp;'; // Instructions
//	echo '/&nbsp;<a href="" target="_blank">'.$LANGCUSTOMFIELDS['setup'][12].' </a>'; // We might add a FAQ later
	echo '</td></tr>';
	if ($_SESSION['glpiactive_entity']==0)
	{
		// Uninstall
		echo '<tr class="tab_bg_1"><td><a href="plugin_customfields.uninstall.php" '.
			'onclick="return confirm(\''.$LANGCUSTOMFIELDS['setup'][8].' '.$LANGCUSTOMFIELDS['setup'][9].'\');">'.
			$LANGCUSTOMFIELDS['setup'][6].'</a>';
		echo ' <img src="'.$CFG_GLPI['root_doc'].'/pics/aide.png" alt="" onmouseout="setdisplay(getElementById(\'commentsup\'),\'none\')" onmouseover="setdisplay(getElementById(\'commentsup\'),\'block\')">';
		// Warning that data will be lost if plugin is uninstalled
		echo '<span class="over_link" id="commentsup">'.$LANGCUSTOMFIELDS['setup'][8].'</span>';
		echo '</td></tr>';
	}
	echo '</table></div>';
}

commonFooter();

?>
