<?php
/*
 * @version $Id$
 ---------------------------------------------------------------------- 
 GLPI - Gestionnaire Libre de Parc Informatique 
 Copyright (C) 2003-2009 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi-project.org
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
// Purpose of file: Page to add and manage custom fields.
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..');


include (GLPI_ROOT.'/inc/includes.php');
checkRight('config','r'); 

commonHeader($LANGCUSTOMFIELDS['Manage_Custom_Fields'],$_SERVER['PHP_SELF'],'plugins','customfields');

if(isset($_GET['device_type']))
{
	$device_type=intval($_GET['device_type']);

	////////// First process any actions ///////////

	if(isset($_POST['enable'])) // Enable custom fields for this device type
	{
		$sql="SELECT COUNT(ID) AS num_cf FROM glpi_plugin_customfields_fields WHERE device_type='$device_type' AND data_type<>'sectionhead';";
		$result = $DB->query($sql);
		$data=$DB->fetch_assoc($result);
		if($data['num_cf']>0) // Need at least one custom field (not including section headings) before enabling
		{
			global $ACTIVE_CUSTOMFIELDS_TYPES;
			$ACTIVE_CUSTOMFIELDS_TYPES[]=$device_type;
			$query="UPDATE glpi_plugin_customfields SET enabled=1 WHERE device_type='$device_type';";
			$result=$DB->query($query);

			if (CUSTOMFIELDS_AUTOACTIVATE)
				plugin_customfields_activate_all($device_type);

			addMessageAfterRedirect($LANGCUSTOMFIELDS['cf_enabled']);
		}
		glpi_header($_SERVER['HTTP_REFERER']); // So clicking refresh on browser will not send post data again
	}
	if(isset($_POST['disable'])) // Disable custom fields for this device type
	{
		plugin_customfields_disable_device($device_type);
		glpi_header($_SERVER['HTTP_REFERER']); // So clicking refresh on browser will send post data again
	}
	elseif(isset($_POST['delete'])) // Delete a field
	{
		foreach($_POST['delete'] as $ID => $garbage)
		{
			$sql="SELECT * FROM glpi_plugin_customfields_fields WHERE device_type='$device_type' AND ID='".intval($ID)."';";
			$result = $DB->query($sql);
			$data=$DB->fetch_assoc($result);
			$system_name=$data['system_name'];

			$sql="DELETE FROM glpi_plugin_customfields_fields WHERE device_type='$device_type' AND ID='".intval($ID)."' AND system_name='$system_name';";
			$result = $DB->query($sql);
			$table=plugin_customfields_table($device_type);
			
			$sql="SELECT COUNT(ID) AS num_left FROM glpi_plugin_customfields_fields WHERE device_type='$device_type';";
			$result = $DB->query($sql);
			$data=$DB->fetch_assoc($result);
			if($data['num_left']==0) // If no more fields, drop the data table
			{
				$sql="DROP TABLE IF EXISTS `$table`;";
				plugin_customfields_disable_device($device_type);
			}
			else
			{
				// Check if there are any dat fields remaining, or only section headers
				$sql="SELECT COUNT(ID) AS num_left FROM glpi_plugin_customfields_fields ".
					" WHERE device_type='$device_type' AND data_type<>'sectionhead';";
				$result = $DB->query($sql);
				$data=$DB->fetch_assoc($result);
				if($data['num_left']==0) // If no more data fields, disable custom fields
					plugin_customfields_disable_device($device_type);

				// Remove the column from the data table
				$sql="ALTER TABLE `$table` DROP `$system_name`;";
			}
			$result = $DB->query($sql);
		}
		glpi_header($_SERVER['HTTP_REFERER']); // So clicking refresh on browser will not send post data again
	}
	elseif(isset($_POST['add'])) // Add a field
	{
		$data_ok=false;
		$sort=intval($_POST['sort']);
		$hidden=isset($_POST['hidden']) ? 1 : 0;

		if(isset($_POST['dropdown_id'])) // Add a drop down menu
		{
			$sql="SELECT * FROM glpi_plugin_customfields_dropdowns WHERE ID='".intval($_POST['dropdown_id'])."';";
			if($result = $DB->query($sql))
			{
				$data=$DB->fetch_assoc($result);
				$system_name=$data['system_name'];
				$label=$data['label'];
				$dd_table=$data['dropdown_table'];
				$data_type='dropdown';
				$data_ok=true;
			}
		}
		else // Add a normal field
		{
			if(isset($_POST['clonedata']))
			{
 				list($system_name,$data_type,$label)=explode(',',$_POST['clonedata'],3);
				$system_name=plugin_customfields_make_system_name($system_name); // clean up in case of tampering
			}
			else
			{
				$system_name=plugin_customfields_make_system_name($_POST['system_name']);
				$data_type=$_POST['data_type'];
				$label=($_POST['label'] !='') ? $_POST['label'] : $LANGCUSTOMFIELDS['Custom_Field'];
			}
			$dd_table='';

			if ($system_name=='') // If the system name was left blank, generate one 
			{
				$system_name='field_';
				$extra=1;
			}
			else
				$extra='';

			$maintable=plugin_customfields_link_id_table($device_type);

			do { // Make sure the field name is not already used
				$sql="SELECT system_name FROM glpi_plugin_customfields_fields WHERE device_type='$device_type' AND system_name='$system_name$extra' ".
					" UNION SELECT system_name FROM glpi_plugin_customfields_dropdowns WHERE system_name='$system_name$extra';";
				$result = $DB->query($sql);
				if($DB->numrows($result)==0)
				{
					$sql="SHOW COLUMNS FROM $maintable WHERE Field='$system_name$extra';";
					$result = $DB->query($sql);
				}
				$extra=$extra+1;
			} while(($DB->numrows($result)>0) && ($extra<51)); // Don't try more than 50 times

			if($extra > 1) // We need to append a number to make it unique
				$system_name=$system_name.($extra - 1);

			if($extra<51)
				$data_ok=true;
		}

		if ($data_ok)
		{			
			$sql="INSERT INTO glpi_plugin_customfields_fields (device_type,system_name,label,data_type,sort_order,hidden,dropdown_table)".
				" VALUES ('$device_type','$system_name','$label','$data_type','$sort','$hidden','$dd_table');";
			$result = $DB->query($sql);
			if($data_type!='sectionhead') // add the field to the data table if it isn't a section header
			{
				$table=plugin_customfields_table($device_type);

				if (CUSTOMFIELDS_AUTOACTIVATE)
					plugin_customfields_activate_all($device_type); // creates table and activates IF necessary
				else
					plugin_customfields_create_data_table($device_type); // creates table if it doesn't alreay exist

				switch($data_type) 
				{
					case 'dropdown': $db_data_type='INT(11)'; break;
					case 'yesno': $db_data_type='SMALLINT(6)'; break;
					case 'general': $db_data_type='VARCHAR(255)'; break;
					case 'text': $db_data_type='TEXT'; break;
					case 'notes': $db_data_type='LONGTEXT'; break;
					case 'date': $db_data_type='DATE'; break;
					case 'number': $db_data_type='INT(11)'; break;
					case 'money': $db_data_type='DECIMAL(20,4)'; break;
					default: $db_data_type='INT(11)';
				}

				$sql="ALTER TABLE `$table` ADD `$system_name` $db_data_type NOT NULL;";
				$result = $DB->query($sql);
			}
		}

		glpi_header($_SERVER['HTTP_REFERER']);
	}
	elseif(isset($_POST['update'])) // Update labels, sort order, etc.
	{
		$query="SELECT * FROM glpi_plugin_customfields_fields WHERE device_type='$device_type' ORDER BY sort_order";
		$result=$DB->query($query);
		while ($data=$DB->fetch_assoc($result))
		{
			$ID=$data['ID'];
			$label=$_POST['label'][$ID];
			$sort=intval($_POST['sort'][$ID]);
			$hidden=isset($_POST['hidden'][$ID]) ? 1 : 0;
			$sql="UPDATE glpi_plugin_customfields_fields SET label='$label', sort_order='$sort', hidden='$hidden' ".
				" WHERE device_type='$device_type' AND ID='$ID';";
			$DB->query($sql);
		}
		glpi_header($_SERVER['HTTP_REFERER']);
	}


	//////// Display the page //////////

	$query="SELECT * FROM glpi_plugin_customfields WHERE device_type='$device_type';";
	$result=$DB->query($query);
	$data=$DB->fetch_assoc($result);

	echo '<div align="center">';

	echo '<form action="?device_type='.$device_type.'" method="post">';
	echo '<table class="tab_cadre" cellpadding="5">';
	echo '<tr><th colspan="6">'.$LANGCUSTOMFIELDS['title'].' ('.$LANGCUSTOMFIELDS['device_type'][$device_type].')</th></tr>';
	echo '<tr>';
	echo '<th>'.$LANGCUSTOMFIELDS['Label'].'</th>';
	echo '<th>'.$LANGCUSTOMFIELDS['System_Name'].'</th>';
	echo '<th>'.$LANGCUSTOMFIELDS['Type'].'</th>';
	echo '<th>'.$LANGCUSTOMFIELDS['Sort'].'</th>';
	echo '<th>'.$LANGCUSTOMFIELDS['Hidden'].'</th>';
	echo '<th></th>';
	echo '</tr>';

	$query="SELECT * FROM glpi_plugin_customfields_fields WHERE device_type='$device_type' ORDER BY sort_order";
	$result=$DB->query($query);
	$numdatafields=0;

	while ($data=$DB->fetch_assoc($result))
	{
		$ID = $data['ID'];
		echo '<tr class="tab_bg_1">';
		echo '<td><input name="label['.$ID.']" value="'.htmlspecialchars($data['label']).'" size="20"></td>';
		echo '<td>'.$data['system_name'].'</td>';
		echo '<td>'.$LANGCUSTOMFIELDS[$data['data_type']].'</td>';
		echo '<td><input name="sort['.$ID.']" value="'.$data['sort_order'].'" size="2"></td>';
		echo '<td align="center"><input name="hidden['.$ID.']" type="checkbox"';
		if($data['hidden']) echo ' checked="checked"';
		echo '></td>';
		echo '<td><input name="delete['.$ID.']" class="submit" type="submit" value="'.$LANG['buttons'][6].'"></td>';
		echo '</tr>';
		if ($data['data_type']!='sectionhead') 
			$numdatafields++;
	}
	echo '<tr><td align="center" valign="top" class="tab_bg_2" colspan="6">';
	if($DB->numrows($result)>0)
		echo '<input type="submit" name="update" value="'.$LANG['buttons'][7].'" class="submit"/>';
	else
		echo $LANGCUSTOMFIELDS['no_cf_yet'];
	echo '</td></tr>';
	echo '</table>';
	echo '</form>';

	// Form to add fields
	echo '<br><form action="?device_type='.$device_type.'" method="post">';
	echo '<table class="tab_cadre" cellpadding="5">';
	echo '<tr><th colspan="6">'.$LANGCUSTOMFIELDS['Add_New_Field'].'</th></tr>';
	echo '<tr>';
	echo '<th>'.$LANGCUSTOMFIELDS['Label'].'</th>';
	echo '<th>'.$LANGCUSTOMFIELDS['System_Name'].'</th>';
	echo '<th>'.$LANGCUSTOMFIELDS['Type'].'</th>';
	echo '<th>'.$LANGCUSTOMFIELDS['Sort'].'</th>';
	echo '<th>'.$LANGCUSTOMFIELDS['Hidden'].'</th>';
	echo '<th></th>';
	echo '</tr>';
	echo '<tr class="tab_bg_1">';
	echo '<td><input name="label" size="20"></td>';
	echo '<td><input name="system_name"></td>';
	echo '<td><select name="data_type">';
	echo '<option value="general">'.$LANGCUSTOMFIELDS['general'].'</option>';
	echo '<option value="text">'.$LANGCUSTOMFIELDS['text_explained'].'</option>';
	echo '<option value="notes">'.$LANGCUSTOMFIELDS['notes_explained'].'</option>';
	echo '<option value="date">'.$LANGCUSTOMFIELDS['date'].'</option>';
	echo '<option value="number">'.$LANGCUSTOMFIELDS['number'].'</option>';
	echo '<option value="money">'.$LANGCUSTOMFIELDS['money'].'</option>';
	echo '<option value="yesno">'.$LANGCUSTOMFIELDS['yesno'].'</option>';
	echo '<option value="sectionhead">'.$LANGCUSTOMFIELDS['sectionhead'].'</option>';
	echo '</select></td>';
	echo '<td><input name="sort" size="2"></td>';
	echo '<td align="center"><input name="hidden" type="checkbox"></td>';
	echo '<td><input name="add" class="submit" type="submit" value="'.$LANG['buttons'][8].'"></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';

	// Show clone field form if there are any fields that can be cloned
	$query="SELECT DISTINCT system_name, data_type, label FROM glpi_plugin_customfields_fields ".
		" WHERE data_type<>'dropdown' AND device_type<>$device_type ".
		" AND system_name NOT IN (SELECT system_name FROM glpi_plugin_customfields_fields WHERE device_type=$device_type) ".
		" ORDER BY label;";
	$result=$DB->query($query);

	if($DB->numrows($result) > 0)
	{
		echo '<br><form action="?device_type='.$device_type.'" method="post">';
		echo '<table class="tab_cadre" cellpadding="5">';
		echo '<tr><th colspan="6">'.$LANGCUSTOMFIELDS['Clone_Field'].'</th></tr>';
		echo '<tr>';
		echo '<th>'.$LANGCUSTOMFIELDS['Field'].'</th>';
		echo '<th>'.$LANGCUSTOMFIELDS['Sort'].'</th>';
		echo '<th>'.$LANGCUSTOMFIELDS['Hidden'].'</th>';
		echo '<th></th>';
		echo '</tr>';
		echo '<tr class="tab_bg_1">';
		echo '<td><select name="clonedata">';
		while ($data=$DB->fetch_assoc($result)){
			echo '<option value="'.$data['system_name'].','.$data['data_type'].','.htmlspecialchars($data['label']).'">'.
			$data['label'].' ('.$data['system_name'].') - '.$LANGCUSTOMFIELDS[$data['data_type']].'</option>';
		}
		echo '</select></td>';
		echo '<td><input name="sort" size="2"></td>';
		echo '<td align="center"><input name="hidden" type="checkbox"></td>';
		echo '<td><input name="add" class="submit" type="submit" value="'.$LANG['buttons'][8].'"></td>';
		echo '</tr>';
		echo '</table>';
		echo '</form>';
	}

	// Form to add drop down menus
	$query="SELECT dd.* FROM glpi_plugin_customfields_dropdowns AS dd ".
		" LEFT JOIN glpi_plugin_customfields_fields AS more ".
		" ON (more.dropdown_table=dd.dropdown_table AND more.device_type='$device_type')  ".
		" WHERE more.ID IS NULL ORDER BY dd.label;";
	$result=$DB->query($query);

	if($DB->numrows($result) > 0)
	{
		echo '<br><form action="?device_type='.$device_type.'" method="post">';
		echo '<table class="tab_cadre" cellpadding="5">';
		echo '<tr><th colspan="4"><a href="./plugin_customfields.dropdowns.php">'.$LANGCUSTOMFIELDS['Add_Custom_Dropdown'].'</a></th></tr>';
		echo '<tr>';
		echo '<th>'.$LANGCUSTOMFIELDS['Dropdown_Name'].'</th>';
		echo '<th>'.$LANGCUSTOMFIELDS['Sort'].'</th>';
		echo '<th>'.$LANGCUSTOMFIELDS['Hidden'].'</th>';
		echo '<th></th>';
		echo '</tr>';
		echo '<tr class="tab_bg_1">';
		echo '<td><select name="dropdown_id">';
		while ($data=$DB->fetch_assoc($result)){
			echo '<option value="'.$data['ID'].'">'.$data['label'].'</option>';
		}
		echo '</select></td>';
		echo '<td><input name="sort" value="'.$data['sort_order'].'" size="2"></td>';
		echo '<td align="center"><input name="hidden" type="checkbox"></td>';
		echo '<td><input name="add" class="submit" type="submit" value="'.$LANG['buttons'][8].'"></td>';
		echo '</tr>';
		echo '</table>';
		echo '</form>';
	}
	else
		echo '<br><a href="./plugin_customfields.dropdowns.php">'.$LANGCUSTOMFIELDS['Add_Custom_Dropdown'].'</a><br>';

	// Form to enable or disable custom fields for this device type
	$query="SELECT * FROM glpi_plugin_customfields WHERE device_type='$device_type';";
	$result=$DB->query($query);
	$data=$DB->fetch_assoc($result);

	echo '<br><form action="?device_type='.$device_type.'" method="post">';
	echo '<table class="tab_cadre" cellpadding="5">';
	echo '<tr class="tab_bg_1"><th>'.$LANGCUSTOMFIELDS['status_of_cf'].': </th><td>';
	if ($data['enabled']==1)
		echo $LANGCUSTOMFIELDS['Enabled'].'</td><td><input class="submit" type="submit" name="disable" value="'.$LANGCUSTOMFIELDS['Disable'].'">';
	else
	{
		echo '<span style="color:#f00;font-weight:bold;">'.$LANGCUSTOMFIELDS['Disabled'].'</span></td>';
		if ($numdatafields > 0)
			echo '<td><input class="submit" type="submit" name="enable" value="'.$LANGCUSTOMFIELDS['Enable'].'">';
		else
		{
			echo '</tr><tr><td class="tab_bg_2" colspan="2">'.$LANGCUSTOMFIELDS['add_fields_first'];
		}
	}

	echo '</td></tr>';
	echo '</table>';
	echo '</form>';

	echo '</div>';
}

commonFooter();

?>
