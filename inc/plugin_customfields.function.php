<?php
/*
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
// Purpose of file: Collection of various functions used by the plugin.
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) die('Sorry. You can\'t access this file directly.');

///////////////// AUTHORIZATION FUNCTIONS ////////////////////////

function plugin_customfields_initSession() 
{
	global $DB;
	
	if(TableExists('glpi_plugin_customfields'))
	{
		// Check the compatability of the plugin files and database tables. 
		$query="SELECT enabled FROM glpi_plugin_customfields WHERE device_type='-1';";
		$result = $DB->query($query);
		$data=$DB->fetch_array($result);
		$dbversion=$data['enabled']; // Version of the last modification to the plugin tables' structure

		// If the files and database are compatible, use the plugin
		if($dbversion == plugin_customfields_dbversion_required())
			$_SESSION['glpi_plugin_customfields_installed']=1;

		// If the database tables need to be upgraded, disble plugin until the upgrade is performed
		if($dbversion < plugin_customfields_dbversion_required())
			$_SESSION['glpi_plugin_customfields_upgrade_required']=1;
	}
}

function plugin_customfields_haveRight($device_type,$right)
{
	// Rights for custom fields are the same as the rights for each device type
	switch($device_type) 
	{
		case COMPUTER_TYPE: return haveRight('computer',$right); break;
		case MONITOR_TYPE: return haveRight('monitor',$right); break;
		case SOFTWARE_TYPE: return haveRight('software',$right); break;
		case NETWORKING_TYPE: return haveRight('networking',$right); break;
		case PERIPHERAL_TYPE: return haveRight('peripheral',$right); break;
		case PRINTER_TYPE: return haveRight('printer',$right); break;
		case CARTRIDGE_TYPE: return haveRight('cartridge',$right); break;
		case CONSUMABLE_TYPE: return haveRight('consumable',$right); break;
		case PHONE_TYPE: return haveRight('phone',$right); break;
		case CONTACT_TYPE: return haveRight('contact_enterprise',$right); break;
		case ENTERPRISE_TYPE: return haveRight('contact_enterprise',$right); break;
		case CONTRACT_TYPE: return haveRight('contract_infocom',$right); break;
		case DOCUMENT_TYPE: return haveRight('document',$right); break;
		case TRACKING_TYPE: return haveRight('tracking',$right); break;
		case DEVICE_TYPE: return haveRight('device',$right); break;
		case NETWORKING_PORT_TYPE: return haveRight('networking',$right); break;
		default: return false;
	}
}

function plugin_customfields_checkRight($device_type, $right)
{
	global $CFG_GLPI;

	if (!plugin_customfields_haveRight($device_type, $right))
	{
		// Check for session timeout
		if (!isset ($_SESSION['glpiID']))
		{
			glpi_header($CFG_GLPI['root_doc'] . '/index.php');
			exit ();
		}
		displayRightError();
	}
}

////////////////// DATABASE FUNCTIONS /////////////////////////

// Removes most accents used in European languages
function plugin_customfields_remove_accents($str) 
{
	$str = htmlentities($str, ENT_COMPAT, 'UTF-8');
	$str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|ring);/','$1',$str);
	$from = explode(' ', '&#192; &#193; &#194; &#195; &#196; &#197; &#199; &#200; &#201; &#202; &#203; &#204; &#205; &#206; &#207; &#208; &#209; &#210; &#211; &#212; &#213; &#214; &#217; &#218; &#219; &#220; &#221; &#224; &#225; &#226; &#227; &#228; &#229; &#230; &#231; &#232; &#233; &#234; &#235; &#236; &#237; &#238; &#239; &#240; &#241; &#242; &#243; &#244; &#245; &#246; &#249; &#250; &#251; &#252; &#253; &#255; &#256; &#257; &#258; &#259; &#260; &#261; &#262; &#263; &#264; &#265; &#266; &#267; &#268; &#269; &#270; &#271; &#272; &#273; &#274; &#275; &#276; &#277; &#278; &#279; &#280; &#281; &#282; &#283; &#284; &#285; &#286; &#287; &#288; &#289; &#290; &#291; &#292; &#293; &#294; &#295; &#296; &#297; &#298; &#299; &#300; &#301; &#302; &#303; &#304; &#305; &#308; &#309; &#310; &#311; &#312; &#313; &#314; &#315; &#316; &#317; &#318; &#319; &#320; &#321; &#322; &#323; &#324; &#325; &#326; &#327; &#328; &#329; &#330; &#331; &#332; &#333; &#334; &#335; &#336; &#337; &#340; &#341; &#342; &#343; &#344; &#345; &#346; &#347; &#348; &#349; &#350; &#351; &#352; &#353; &#354; &#355; &#356; &#357; &#360; &#361; &#362; &#363; &#364; &#365; &#366; &#367; &#368; &#369; &#370; &#371; &#372; &#373; &#374; &#375; &#376; &#377; &#378; &#379; &#380; &#381; &#382;');
	$to = explode(' ', 'A A A A A A C E E E E I I I I D N O O O O O U U U U Y a a a a a a a c e e e e i i i i o n o o o o o u u u u y y A a A a A a C c C c C c C c D d D d E e E e E e E e E e G g G g G g G g G H H h I i I i I i I i I i J j K k k L l L l L l L l L l N n N n N n n N n O o O o O o R r R r R r S s S s S s S s T t T t U u U u U u U u U u U u W w Y y Y Z z Z z Z z');
	return str_replace($from, $to, html_entity_decode($str));
}

// Replace punctuation and spaces with underscore, letters to lowercase. 
// Removes most accents, but does not replace foreign scripts, chinese characters, etc.
function plugin_customfields_make_system_name($str)
{
	$str = plugin_customfields_remove_accents(trim($str));
	return strtr($str,
		' ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()+={}[]<>,.?/~`|:;"\'\\',
		'_abcdefghijklmnopqrstuvwxyz______________________________');
}

// Names of tables uses to store the custom field data
function plugin_customfields_table($device_type)
{
	switch($device_type) 
	{
		case COMPUTER_TYPE: return 'glpi_plugin_customfields_computers'; break;
		case MONITOR_TYPE: return 'glpi_plugin_customfields_monitors'; break;
		case SOFTWARE_TYPE: return 'glpi_plugin_customfields_software'; break;
		case NETWORKING_TYPE: return 'glpi_plugin_customfields_networking'; break;
		case PERIPHERAL_TYPE: return 'glpi_plugin_customfields_peripherals'; break;
		case PRINTER_TYPE: return 'glpi_plugin_customfields_printers'; break;
		case CARTRIDGE_TYPE: return 'glpi_plugin_customfields_cartridges'; break;
		case CONSUMABLE_TYPE: return 'glpi_plugin_customfields_consumables'; break;
		case PHONE_TYPE: return 'glpi_plugin_customfields_phones'; break;
		case CONTACT_TYPE: return 'glpi_plugin_customfields_contacts'; break;
		case ENTERPRISE_TYPE: return 'glpi_plugin_customfields_enterprises'; break;
		case CONTRACT_TYPE: return 'glpi_plugin_customfields_contracts'; break;
		case DOCUMENT_TYPE: return 'glpi_plugin_customfields_docs'; break;
		case TRACKING_TYPE: return 'glpi_plugin_customfields_tracking'; break;
		case DEVICE_TYPE: return 'glpi_plugin_customfields_device'; break;
		case NETWORKING_PORT_TYPE: return 'glpi_plugin_customfields_ports'; break;
		case PLUGIN_CUSTOMFIELDS_TYPE: return 'glpi_plugin_customfields'; break;
		default: return false;
	}
}
// Names of glpi tables
function plugin_customfields_link_id_table($device_type)
{
	switch($device_type) 
	{
		case NETWORKING_PORT_TYPE: return 'glpi_networking_ports'; break; // can remove in 0.72
//		case DEVICE_TYPE: return '???'; break;
		default:
			global $LINK_ID_TABLE;
			return $LINK_ID_TABLE[$device_type];
		break;
	}
}
// Active custom fields for a specific device (used if auto activate is turned off)
function plugin_customfields_activate($device_type,$ID)
{
	global $DB;
	if ($device_type>0 && $ID>0)
	{
		if($table=plugin_customfields_table($device_type))
		{
			$query="INSERT INTO `$table` (ID) VALUES ('".intval($ID)."');";
			$result = $DB->query($query);
		}
	}
}

// Activates custom fields for all devices of a specific type
function plugin_customfields_activate_all($device_type)
{
	global $DB;

	$query="SELECT ID FROM glpi_plugin_customfields_fields WHERE device_type = '".intval($device_type)."';";
	$result=$DB->query($query);
	if($DB->numrows($result) > 0)
	{
		plugin_customfields_create_data_table($device_type);

		$table1=plugin_customfields_link_id_table($device_type);
		$table2=plugin_customfields_table($device_type);

		$query="SELECT a.ID, b.ID AS skip FROM $table1 AS a LEFT JOIN $table2 AS b ON a.ID=b.ID;";
		$result=$DB->query($query);
		while ($data=$DB->fetch_assoc($result))
		{
			if(is_null($data['skip']))
			{
				$sql="INSERT INTO `$table2` (ID) VALUES ('".intval($data['ID'])."');";
				$result2 = $DB->query($sql); 
			}
		}
	}
}

// Create a table to store custom data for a device type if it doesn't already exist
function plugin_customfields_create_data_table($device_type)
{
	global $DB;
	$table=plugin_customfields_table($device_type);

	if(!TableExists($table))
	{
		$sql="CREATE TABLE `$table` (`ID` int(11) NOT NULL auto_increment, PRIMARY KEY  (`ID`))".
			" ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3;";
		$result = $DB->query($sql);
		return ($result ? true : false);
	}
	else
		return true;
}

function plugin_customfields_disable_device($device_type)
{
	global $DB, $ACTIVE_CUSTOMFIELDS_TYPES, $LANGCUSTOMFIELDS;
	unset($ACTIVE_CUSTOMFIELDS_TYPES[$device_type]);
	$query="UPDATE glpi_plugin_customfields SET enabled=0 WHERE device_type='$device_type';";
	$result=$DB->query($query);
	addMessageAfterRedirect($LANGCUSTOMFIELDS['cf_disabled']);
}

// Copies value in a drop down to a new entity if it does not alreay exist in the new entity. 
// If the menu has a tree stucture, it will also copy any parents that didn't exist in the new entity.
function plugin_customfields_transferDropdown($ddID,$dd_table,$newentity)
{
	global $DB;

	if ($ddID>0)
	{
		// Search init item
		$query="SELECT * FROM $dd_table WHERE ID='$ddID'";
		if ( ($result=$DB->query($query)) && ($DB->numrows($result)) )
		{
			$data=$DB->fetch_array($result);
			$data=addslashes_deep($data);
			// Search if the value already exists in the destination entity
			if(isset($data['completename'])) // it is a tree
				$query="SELECT ID FROM $dd_table WHERE FK_entities='$newentity' AND completename='".$data['completename']."'";
			else // it isn't a tree
				$query="SELECT ID FROM $dd_table WHERE FK_entities='$newentity' AND name='".$data['name']."'";
			if ($result_search=$DB->query($query))
			{
				// If a match is found, use it
				if ($DB->numrows($result_search)>0){
					$newID=$DB->result($result_search,0,'ID');
					return $newID;
				}
			}
			// No match was found, so copy the data to the new entity 
			$input=array();
			$input['tablename']=$dd_table;
			$input['FK_entities']=$newentity;
			$input['value']=$data['name'];
			$input['comments']=$data['comments'];
			$input['type']="under";
			$input['value2']=0; // parentID
			// if parentID > 0 need to recurrsively transfer the parent(s)
			if (isset($data['parentID']) && ($data['parentID']>0))
			{
				$input['value2']=plugin_customfields_transferDropdown($data['parentID'],$dd_table,$newentity);
			}
			// add the item
			$newID=addDropdown($input);
			return $newID;
		}
	}
	return 0;
}

// Transfer drop down items to a new entity
function plugin_customfields_transferAllDropdowns($ID,$device_type,$newentity)
{
	global $DB;
	$updates=array();

	if ($ID > 0)
	{
		$query="SELECT d.* FROM glpi_plugin_customfields_fields AS f, glpi_plugin_customfields_dropdowns AS d ".
			" WHERE f.device_type='$device_type' AND f.data_type='dropdown' AND d.system_name=f.system_name AND d.has_entities=1;";
		if ($result=$DB->query($query))
		{
			while($data=$DB->fetch_array($result))
			{
				$data_table=plugin_customfields_table($device_type);
				$system_name=$data['system_name'];
				$query="SELECT `$system_name` AS oldID FROM $data_table WHERE ID='$ID';";
				if ( ($dd_result=$DB->query($query)) && ($dd_data=$DB->fetch_array($dd_result)) )
				{
					$newID=plugin_customfields_transferDropdown($dd_data['oldID'],$data['dropdown_table'],$newentity);
					$updates[$data['system_name']]=$newID;
				}
			}
		}
	}
	return $updates;
}

////////////////////// DISPLAY FUNCTIONS /////////////////////////

// Show the custom fields form below the main device
function plugin_customfields_showAssociated($device_type,$ID,$withtemplate='')
{
	GLOBAL $DB,$CFG_GLPI,$LANG,$LANGCUSTOMFIELDS;

	$query="SELECT * FROM glpi_plugin_customfields WHERE device_type='$device_type'";
	$result = $DB->query($query);
	$info=$DB->fetch_array($result);
	if($info['enabled']!=1)
		return;
	
	$table=plugin_customfields_table($device_type);
	if ($table)
	{
		$query="SELECT * FROM `$table` WHERE ID='$ID'";
		$result = $DB->query($query);
		$number = $DB->numrows($result);
	}
	else
		return;

	if ($number!=1) // No data found, so make a link to activate custom fields for this device
	{
		if (plugin_customfields_haveRight($device_type,'w')&&$withtemplate!=2)
		{
			echo '<br><div class="center">';
			echo '<strong><a href="'.GLPI_ROOT.'/plugins/customfields/front/plugin_customfields.form.php?device_type='.$device_type.'&amp;ID='.$ID.'&amp;add=add">'.$LANGCUSTOMFIELDS['Activate_Custom_Fields'].'</a></strong>';
			echo '</div><br>';
		}
	} 
	else // Data was found, so display it
	{
		$data=$DB->fetch_array($result);
		$query="SELECT * FROM glpi_plugin_customfields_fields WHERE device_type='$device_type' AND hidden=0 ORDER BY sort_order;";
		$result = $DB->query($query);

		echo '<br><form action="'.GLPI_ROOT.'/plugins/customfields/front/plugin_customfields.form.php" method="post" name="form_cf">';
		echo '<table class="tab_cadre_fixe">';
		$count=0;
		while ($fields=$DB->fetch_array($result)){
			$field_name=$fields['system_name'];
			if($fields['data_type']!='sectionhead')
				$value=$data[$field_name];
			$count++;

			if($fields['data_type']=='sectionhead'
				 || $fields['data_type']=='notes' 
				 || $fields['data_type']=='text' // */ // comment out this line if you don't want 'Text' (comment) fields to span both columns
			) 
			{ // Display data that should span both columns
				if(!($count % 2))
				{
					echo '<td colspan="2"></td></tr>';
					$count++;
				}
				if($fields['data_type']=='sectionhead')
				{
					echo '<tr><th colspan="4">'.$fields['label'].'</th></tr>';
				}
				elseif($fields['data_type']=='notes')
				{
					echo '<tr class="tab_bg_1">';
					echo '<td colspan="4" valign="middle" align="center" class="tab_bg_1">';
					echo $fields['label'].':<br>';
					echo '<textarea name="'.$field_name.'" rows="20" cols="100">'.$value.'</textarea>';
					echo '</td></tr>';
				}
				else
				{
					echo '<tr class="tab_bg_1">';
					echo '<td valign="top">'.$fields['label'].': </td>';
					echo '<td colspan="3" align="center">';
					echo '<textarea name="'.$field_name.'" rows="4" cols="75">'.$value.'</textarea>';
					echo '</td></tr>';
				}
				$count++;
			}
			else // display data that only needs a single column
			{
				if($count % 2)
					echo '<tr class="tab_bg_1">';
				echo '<td>'.$fields['label'].': </td>';
				echo '<td>';
				switch($fields['data_type'])
				{
					case 'general':
						echo '<input type="text" size="20" value="'.$value.'" name="'.$field_name.'"/>';
						break;
					case 'dropdown':
						dropdownValue($fields['dropdown_table'], $field_name, $value);
						break;
					case 'date':
						$editcalendar=($withtemplate!=2);
						showCalendarForm('form_cf',$field_name,$value,$editcalendar);
						break;
					case 'money':
						echo '<input type="text" size="16" value="'.formatNumber($value,true).'" name="'.$field_name.'"/>';
						break;
					case 'yesno':
						echo dropdownYesNo($field_name,$value);
						break;
					case 'text': // only in effect if the condition about 40 lines above is removed
						echo '<textarea name="'.$field_name.'" rows="4" cols="35">'.$value.'</textarea>';
						break;
					case 'number':
						echo '<input type="text" size="10" value="'.$value.'" name="'.$field_name.'"/>';
						break;
				}
				echo '</td>';
				if(!($count % 2))
					echo '</tr>';
			}
		}
		if($count % 2)
			echo '<td colspan="2"></td></tr>';

		// Show buttons
		if(($count >= 1) && plugin_customfields_haveRight($device_type,'w'))
		{
			if(CUSTOMFIELDS_AUTOACTIVATE) 
				echo '<tr><td align="center" valign="top" colspan="4" class="tab_bg_2">';
			else
				echo '<tr><td align="center" valign="top" colspan="2" class="tab_bg_2">';

			echo '<input type="submit" class="submit" value="'.$LANGCUSTOMFIELDS['Update_Custom_Fields'].'" name="update"/>';
			echo '<input type="hidden" value="'.$ID.'" name="ID"/>';
			echo '<input type="hidden" value="'.$device_type.'" name="device_type"/></td>';

			if(!CUSTOMFIELDS_AUTOACTIVATE) // Must show the delete button if autoactivate is off
			{
				echo '<td align="center" colspan="2" class="tab_bg_2">';
				echo '<div class="center"><input type="submit" class="submit" value="'.$LANG['buttons'][6].
					'" name="delete"/> <b>'.$LANGCUSTOMFIELDS['delete_warning'].'</b></div></td>';
			}
			echo '</tr>';
		}

		echo '</table>';
		echo '</form>';
	}

}

///////////////////// INSTALLATION FUNCTIONS /////////////////////

function plugin_customfields_install() 
{
	global $DB;
	
	$DB_file = GLPI_ROOT.'/plugins/customfields/inc/plugin_customfields.setup1.sql';
	$DBf_handle = fopen($DB_file, 'rt');
	$sql_query = fread($DBf_handle, filesize($DB_file));
	fclose($DBf_handle);
	foreach ( explode(";\n", "$sql_query") as $sql_line) 
	{
		if (get_magic_quotes_runtime()) $sql_line=stripslashes_deep($sql_line);
		$DB->query($sql_line);
	}
	
	$DB_file = GLPI_ROOT.'/plugins/customfields/inc/plugin_customfields.setup2.sql';
	$DBf_handle = fopen($DB_file, 'rt');
	$sql_query = fread($DBf_handle, filesize($DB_file));
	fclose($DBf_handle);
	foreach ( explode(";\n", "$sql_query") as $sql_line) 
	{
		if (get_magic_quotes_runtime()) $sql_line=stripslashes_deep($sql_line);
		$DB->query($sql_line);
	}
}

function plugin_customfields_upgrade($oldversion)
{
	global $DB;

	// Save settings
	$sql="SELECT `device_type` FROM `glpi_plugin_customfields` WHERE `enabled`='1';";
	$result=$DB->query($sql);
	$enabled=array();
	while ($data=$DB->fetch_array($result))
	{
		$enabled[]=$data['device_type'];
	}
	
	// Upgrade
	$DB_file = GLPI_ROOT.'/plugins/customfields/inc/plugin_customfields.setup1.sql';
	$DBf_handle = fopen($DB_file, 'rt');
	$sql_query = fread($DBf_handle, filesize($DB_file));
	fclose($DBf_handle);
	foreach ( explode(";\n", "$sql_query") as $sql_line) 
	{
		if (get_magic_quotes_runtime()) $sql_line=stripslashes_deep($sql_line);
		$DB->query($sql_line);
	}

	// Restore settings
	foreach($enabled as $device_type)
	{
		$sql="UPDATE `glpi_plugin_customfields` SET `enabled`='1' WHERE `device_type`='$device_type';";
		$DB->query($sql);
	}
}

function plugin_customfields_uninstall() 
{
	global $DB, $ALL_CUSTOMFIELDS_TYPES;

	$query='SELECT dropdown_table FROM glpi_plugin_customfields_dropdowns';
	$result=$DB->query($query);
	while ($data=$DB->fetch_assoc($result))
	{
		$table=$data['dropdown_table'];
		if($table!='')
		{
			$query = "DROP TABLE IF EXISTS `$table`;";
			$DB->query($query) or die($DB->error());
		}
	}
	
	foreach($ALL_CUSTOMFIELDS_TYPES as $device_type)
	{
		$table=plugin_customfields_table($device_type);
		if($table)
		{
			$query ="DROP TABLE IF EXISTS `$table`;";
			$DB->query($query) or die($DB->error());
		}
	}
	$query = 'DROP TABLE `glpi_plugin_customfields`;';
	$DB->query($query) or die($DB->error());
	$query = 'DROP TABLE `glpi_plugin_customfields_fields`;';
	$DB->query($query) or die($DB->error());
	$query = 'DROP TABLE `glpi_plugin_customfields_dropdowns`;';
	$DB->query($query) or die($DB->error());
}
?>