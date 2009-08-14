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
// Purpose of file: Used to initialize the plugin and define its actions.
// ----------------------------------------------------------------------

// If auto activate set to true, custom fields will be automatically added when a new record is inserted.
// If set to false, users must click 'Activate custom fields' to add additional information.
define('CUSTOMFIELDS_AUTOACTIVATE', true); 

// This will be set to true when applying the glpi patch. 
// See instructions in the patch directory
define('CUSTOMFIELDS_GLPI_PATCH_APPLIED', false);

$ACTIVE_CUSTOMFIELDS_TYPES = array();
$ALL_CUSTOMFIELDS_TYPES = array();
define('NETWORKING_PORT_TYPE',42); // Remove in 0.72

include_once ('inc/plugin_customfields.function.php');
include_once ('inc/plugin_customfields.class.php');

// Initialize the plugin's hooks (this function is required)
function plugin_init_customfields()
{
	global $PLUGIN_HOOKS,$CFG_GLPI,$LANGCUSTOMFIELDS,$DB,$ACTIVE_CUSTOMFIELDS_TYPES,$ALL_CUSTOMFIELDS_TYPES;

	// customfields uses the range 5200-7699
	// Params : plugin name - string defining type - number - class name - table name - form page
	pluginNewType('customfields','PLUGIN_CUSTOMFIELDS_TYPE',5200,'plugin_customfields','glpi_plugin_customfields','front/plugin_customfields.form.php');

	$PLUGIN_HOOKS['init_session']['customfields'] = 'plugin_customfields_initSession';
	
	if (isset($_SESSION['glpiID']))
	{
		if (isset($_SESSION['glpi_plugin_customfields_installed']) && $_SESSION['glpi_plugin_customfields_installed']==1)
		{
			$query='SELECT device_type, enabled FROM glpi_plugin_customfields WHERE device_type > 0;';
			$result=$DB->query($query);
			while ($data=$DB->fetch_assoc($result))
			{
				$ALL_CUSTOMFIELDS_TYPES[]=$data['device_type'];
				if($data['enabled'])
					$ACTIVE_CUSTOMFIELDS_TYPES[]=$data['device_type'];
			}

			$query='SELECT * FROM glpi_plugin_customfields_dropdowns WHERE has_entities=1 OR is_tree=1';
			$result=$DB->query($query);
			while ($data=$DB->fetch_assoc($result))
			{
				if($data['has_entities']==1)
					array_push($CFG_GLPI['specif_entities_tables'],$data['dropdown_table']);
				if($data['is_tree']==1)
					array_push($CFG_GLPI['dropdowntree_tables'],$data['dropdown_table']);
			}

			// Display a menu entry in the main menu if the user has configuration rights 
			if(haveRight('config','w'))
				$PLUGIN_HOOKS['menu_entry']['customfields'] = true;

			// Menus for each device type
			$PLUGIN_HOOKS['headings']['customfields'] = 'plugin_get_headings_customfields';
			$PLUGIN_HOOKS['headings_action']['customfields'] = 'plugin_headings_actions_customfields';

			// Functions to run when data changes
			$PLUGIN_HOOKS['item_add']['customfields'] = 'plugin_item_add_customfields';
			$PLUGIN_HOOKS['item_purge']['customfields'] = 'plugin_item_purge_customfields';
			$PLUGIN_HOOKS['pre_item_update']['customfields'] = 'plugin_pre_item_update_customfields';
		
			// Define how to import data into custom fields with the Data_Injection plugin
			$PLUGIN_HOOKS['data_injection']['customfields'] = 'plugin_customfields_data_injection_variables';
		}

		// Indicate where the configuration page can be found
		if (haveRight('config','w'))
			$PLUGIN_HOOKS['config_page']['customfields'] = 'front/plugin_customfields.config.php';			
	}
}

/////////////// KEY FUNCTIONS /////////////////

// Get the name and the version of the plugin (required function)
function plugin_version_customfields()
{
	global $LANGCUSTOMFIELDS;

	return array( 'name' => $LANGCUSTOMFIELDS['title'],
			'minGlpiVersion' => '0.71',
			'version' => '1.0.1');
}

function plugin_customfields_dbversion_required()
{
	// This is the last version that the database tables changed.
	// This version of the database will be older than the plugin if there were
	// no changes to the database.
	return 101; // 1.0.1
}

// Define dropdown relations for use by GLPI
function plugin_customfields_getDatabaseRelations()
{
	global $DB;

	if (isset($_SESSION['glpi_plugin_customfields_installed']) && $_SESSION['glpi_plugin_customfields_installed']==1)
	{
		$relations=array();
		$query="SELECT * FROM glpi_plugin_customfields_fields WHERE hidden=0 AND deleted=0 AND data_type='dropdown' ORDER BY device_type";
		$result=$DB->query($query);
		while ($data=$DB->fetch_assoc($result))
		{
			$relations[$data['dropdown_table']]=array(plugin_customfields_table($data['device_type'])=>$data['system_name']);
		}

		$entities=array();
		$query="SELECT dropdown_table FROM glpi_plugin_customfields_dropdowns WHERE has_entities=1;";
		$result=$DB->query($query);
		while ($data=$DB->fetch_assoc($result))
		{
			$entities[$data['dropdown_table']]='FK_entities'; 
		}
		if(!empty($entities))
			$relations['glpi_entities']=$entities;

		return $relations;
	}
	else
		return array();
}

// Define dropdown tables to be managed in GLPI
function plugin_customfields_getDropdown()
{
	global $DB;

	if (isset($_SESSION['glpi_plugin_customfields_installed']) && $_SESSION['glpi_plugin_customfields_installed']==1)
	{
		$dropdowns = array();

		$query='SELECT * FROM glpi_plugin_customfields_dropdowns';
		$result=$DB->query($query);
		while ($data=$DB->fetch_assoc($result))
		{
			$dropdowns[$data['dropdown_table']]=$data['label'];
		}
		return $dropdowns;
	}
	else
		return array();
}

/////////// SEARCH FUNCTIONS ////////////

// Define search options for each device type that has custom fields.
// 'Search options' are also used by GLPI for logging and mass updates.
function plugin_customfields_getSearchOption()
{
	global $LANGCUSTOMFIELDS,$LANG,$DB;
	$sopt=array();

	$sopt[PLUGIN_CUSTOMFIELDS_TYPE]['common']=$LANGCUSTOMFIELDS['title'];

	$query="SELECT f.*, dd.is_tree, cf.enabled FROM glpi_plugin_customfields as cf, glpi_plugin_customfields_fields AS f ".
		" LEFT JOIN glpi_plugin_customfields_dropdowns AS dd ON dd.system_name=f.system_name ".
		" WHERE f.device_type=cf.device_type ".
		" ORDER BY f.device_type, f.sort_order, f.label";
	$result=$DB->query($query);

	$device_type='';
	while ($data=$DB->fetch_assoc($result))
	{
		// Range 5200-7699 used by this plugin
		$lpos = $data['sopt_pos'] + 5200; // first 1000 used for logging
		$spos = $data['sopt_pos'] + 6200; // next 1000 used for searches
		if($data['device_type']!=$device_type)
		{
			$mupos = 7200; // last 500 used for mass update 
			$table = plugin_customfields_link_id_table($data['device_type']);
			$table2 = plugin_customfields_table($data['device_type']);
		}

		$device_type=$data['device_type'];

		if($data['deleted'] || $data['hidden'] || !$data['enabled']) // preserve names for log history
		{
			if(CUSTOMFIELDS_GLPI_PATCH_APPLIED)
			{
				$sopt[$device_type][$lpos]['name']=$data['label'];
				$sopt[$device_type][$lpos]['field']='';
				$sopt[$device_type][$lpos]['linkfield']='';
				$sopt[$device_type][$lpos]['purpose']='log'; // an extra field used to clean search options
			}
		}
		elseif($data['data_type']=='sectionhead')
		{
			$sopt[$device_type]['customfields_'.$data['system_name']]=$data['label'];
		}
		elseif($data['data_type']=='dropdown')
		{
			// search, logging, and mass update all work for dropdowns
			$sopt[$device_type][$lpos]['table']=$data['dropdown_table'];
			if($data['is_tree']==1)
				$sopt[$device_type][$lpos]['field']='completename';
			else
				$sopt[$device_type][$lpos]['field']='name';
			$sopt[$device_type][$lpos]['linkfield']=$data['system_name'];
			$sopt[$device_type][$lpos]['name']=$data['label'];
		}
		else
		{
			// Note: Yes/No fields are included in search, logging, and mass update functionality. 
			// In the GLPI core they are not usually included.

			// For fields that aren't dropdowns, it is necessary to apply a patch 
			// to enable logging and mass update functionality
			if(CUSTOMFIELDS_GLPI_PATCH_APPLIED)
			{
				// for logging (these might need to be the first set of options)
				$sopt[$device_type][$lpos]['table']=$table;
				$sopt[$device_type][$lpos]['field']=$data['system_name'];
				$sopt[$device_type][$lpos]['linkfield']='';
				$sopt[$device_type][$lpos]['name']=$data['label'];
				$sopt[$device_type][$lpos]['purpose']='log'; // an extra field used to clean search options

				// for mass update
				$mupos++;
				$sopt[$device_type][$mupos]['table']=$table2;
				$sopt[$device_type][$mupos]['field']=$data['system_name'];
				$sopt[$device_type][$mupos]['linkfield']=$data['system_name'];
				$sopt[$device_type][$mupos]['name']=$data['label'];
				$sopt[$device_type][$mupos]['purpose']='update'; // an extra field used to clean search options
			}
			// for search
			$sopt[$device_type][$spos]['table']=$table2;
			$sopt[$device_type][$spos]['field']=$data['system_name'];
			$sopt[$device_type][$spos]['linkfield']='ID';
			$sopt[$device_type][$spos]['name']=$data['label'];
			$sopt[$device_type][$spos]['purpose']='search'; // an extra field used to clean search options
		}
	}

	return $sopt;
}

// Clean Search Options: Necessary for search to work properly if GLPI patch applied. 
// Removes the search options that are used for different purposes.
// This function requires the glpi patch in order to be called. See the patch directory for instructions.
function plugin_customfields_cleanSearchOption($options, $action)
{
	if(!empty($options))
	{
		foreach($options as $ID => $value) 
		{
			if(is_array($value) && isset($value['purpose']))
			{
				// If action is 'r' we are cleaning before a search. 
				// If action is 'w', we are cleaning before an update.
				if ($value['purpose']=='log')
					unset($options[$ID]);
				elseif ($value['purpose']=='search' && $action=='w')
					unset($options[$ID]);
				elseif ($value['purpose']=='update' && $action=='r')
					unset($options[$ID]);
			}
		}
	}

	return $options;
}
// Define how to join the tables when doing a search
function plugin_customfields_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables)
{
	$type_table = plugin_customfields_table($type);
	if ($new_table==$type_table)
	{
		$out=addLeftJoin($type,$ref_table,$already_link_tables,$new_table,$linkfield);
		return $out;
	}
	else // it is a custom dropdown
	{
		$out=addLeftJoin($type,$ref_table,$already_link_tables,$type_table,'ID');
		$out.= " LEFT JOIN $new_table ON ($new_table.ID = $type_table.$linkfield) ";
		return $out;
	}
}

///////////// VARIOUS HOOKS /////////////////

// Hook to process Mass Update & transfer
function plugin_pre_item_update_customfields($data)
{
	global $ACTIVE_CUSTOMFIELDS_TYPES;

	// If update isn't set, then this is a mass update or transfer, not a regular update
	if(!isset($data['update']) && !isset($data['_already_called_']) && in_array($data['_item_type_'],$ACTIVE_CUSTOMFIELDS_TYPES))
	{
		// mass update or tranfer, possibly affecting one of our custom fields
		$updates=array();		
		if(isset($data['FK_entities'])) // the item is being transfered to another entity
		{
			$updates=plugin_customfields_transferAllDropdowns($data['ID'],$data['_item_type_'],$data['FK_entities']);
		}

		$plugin_customfields = new plugin_customfields($data['_item_type_']);
		$newdata=array_merge($updates,$data);
		$newdata['_already_called_']=true; // prevents recurrsion
		// The data may or may not be a custom field. At the moment we try an update regardless
		$plugin_customfields->update($newdata);
	}

	return $data; // return the original data, not our additional data
}

// Hook done on add item case
// If in Auto Activate mode, add a record for the custom fields when a device is added
function plugin_item_add_customfields($parm)
{
	global $DB,$ACTIVE_CUSTOMFIELDS_TYPES;

	if (CUSTOMFIELDS_AUTOACTIVATE && isset($parm['type']))
	{
		if (in_array($parm['type'], $ACTIVE_CUSTOMFIELDS_TYPES))
		{
			$table=plugin_customfields_table($parm['type']);
			$sql="INSERT INTO `$table` (ID) VALUES ('".intval($parm['ID'])."');";
			$result = $DB->query($sql); 
			return ($result ? true : false);
		}
		elseif ($parm['type'] == -1)
		{
			if(isset($_POST['logical_number']) && isset($_POST['iface'])) // must be a port
			{
				$parm['type']=NETWORKING_PORT_TYPE;
				$table=plugin_customfields_table($parm['type']);
				$sql="INSERT INTO `$table` (ID) VALUES ('".intval($parm['ID'])."');";
				$result = $DB->query($sql); 
				return ($result ? true : false);
			}
		}
	}
	return false;
}

// Hook done on purge item case
function plugin_item_purge_customfields($parm)
{
	global $DB,$ALL_CUSTOMFIELDS_TYPES;

	// Must delete custom fields when main item is purged, even if custom fields for this device are currently disabled
	if (in_array($type,$ALL_CUSTOMFIELDS_TYPES) && ($table=plugin_customfields_table($parm['type'])))
	{
		$sql="DELETE FROM `$table` WHERE ID = '".intval($parm['ID'])."' LIMIT 1;";
		$result=$DB->query($sql);
		return true;
	}
	else
		return false;

}

// This function requires the glpi patch in order to be called. See the patch directory for instructions
function plugin_customfields_MassiveActionsFieldsDisplay($type,$table,$field,$linkfield)
{
	global $DB;

	$query="SELECT * FROM glpi_plugin_customfields_fields WHERE device_type='$type' AND system_name='$field';";
	$result=$DB->query($query);
	if ($data=$DB->fetch_assoc($result))
	{
		switch($data['data_type'])
		{
			case 'dropdown':
				dropdownValue($data['dropdown_table'], $field, 1, $_SESSION['glpiactive_entity']);
				break;
			case 'yesno':
				dropdownYesNo($field,0);
				break;
			case 'date':
				showCalendarForm('massiveaction_form',$field,'',true);
				break;
			case 'money':			
				echo '<input type="text" size="16" value="'.formatNumber(0,true).'" name="'.$field.'"/>';
				break;
			default:
				autocompletionTextField($linkfield,$table,$field); 
				break;
		}
		return true;
	}
	else
		return false;
}

// Define headings added by the plugin -- determines if a tab should be shown or not
function plugin_get_headings_customfields($type,$withtemplate)
{
	global $LANGCUSTOMFIELDS, $ACTIVE_CUSTOMFIELDS_TYPES;
	
	// Show the tab if Custom fields have been activated for this device type
	if (in_array($type,$ACTIVE_CUSTOMFIELDS_TYPES)) 
	{
		// template case
		if ($withtemplate)
			return array();
		// Non template case
		else 
			return array(1 => $LANGCUSTOMFIELDS['title']);
	}
	else
		return false;
}
// Define headings actions added by the plugin -- what happens when you click on the tab
function plugin_headings_actions_customfields($type)
{
	global $ACTIVE_CUSTOMFIELDS_TYPES;

	if (in_array($type,$ACTIVE_CUSTOMFIELDS_TYPES))
		return array(1 => 'plugin_headings_customfields');
	else
		return false;
}

// customfields of an action heading -- show the custom fields
function plugin_headings_customfields($type,$ID,$withtemplate=0)
{
	if ($ID > -1)
	{
		echo '<div align="center">';
		echo plugin_customfields_showAssociated($type,$ID);
		echo '</div>';
	}
}

// Define fields that can be updated with the data_injection plugin
function plugin_customfields_data_injection_variables()
{	
	global $IMPORT_PRIMARY_TYPES, $DATA_INJECTION_MAPPING, $LANG, $IMPORT_TYPES, $LANGCUSTOMFIELDS,$DATA_INJECTION_INFOS,$DB;
	
	if (isset($_SESSION['glpi_plugin_customfields_installed']) && $_SESSION['glpi_plugin_customfields_installed']==1)
	{
		$query="SELECT * FROM glpi_plugin_customfields_fields WHERE data_type <> 'sectionhead' AND deleted=0;";
		$result=$DB->query($query);
		while ($data=$DB->fetch_assoc($result))
		{
			$type=5200 + $data['device_type']; // this plugin uses the range 5200-7699
			$field = $data['system_name'];
			if($data['data_type']=='dropdown')
			{
				$DATA_INJECTION_MAPPING[$type][$field]['table'] = $data['dropdown_table'];
				$DATA_INJECTION_MAPPING[$type][$field]['field'] = 'name';
				$DATA_INJECTION_MAPPING[$type][$field]['linkfield'] = $field;
				$DATA_INJECTION_INFOS[$type][$field]['linkfield'] = $field;
				$DATA_INJECTION_MAPPING[$type][$field]['table_type'] = 'dropdown';
				$DATA_INJECTION_INFOS[$type][$field]['table_type'] = 'dropdown';
			}
			else
			{
				$DATA_INJECTION_MAPPING[$type][$field]['table'] = plugin_customfields_table($type);
				$DATA_INJECTION_MAPPING[$type][$field]['field'] = $field;
			}
			$DATA_INJECTION_MAPPING[$type][$field]['name'] = $data['label'];
			switch($data['device_type'])
			{
				case 'number': 
				case 'yesno': $DATA_INJECTION_MAPPING[$type][$field]['type'] = 'integer'; break;
				case 'date': $DATA_INJECTION_MAPPING[$type][$field]['type'] = 'date'; break;
				case 'money': $DATA_INJECTION_MAPPING[$type][$field]['type'] = 'float'; break;
				case 'text':
				case 'notes':
					$DATA_INJECTION_MAPPING[$type][$field]['table_type'] = 'multitext';
					$DATA_INJECTION_INFOS[$type][$field]['table_type'] = 'multitext';
					$DATA_INJECTION_MAPPING[$type][$field]['type'] = 'text'; 
					break;
				default: $DATA_INJECTION_MAPPING[$type][$field]['type'] = 'text'; 
			}
			$DATA_INJECTION_INFOS[$type][$field]['table'] = $DATA_INJECTION_MAPPING[$type][$field]['table']; 
			$DATA_INJECTION_INFOS[$type][$field]['field'] = $DATA_INJECTION_MAPPING[$type][$field]['field'];
			$DATA_INJECTION_INFOS[$type][$field]['name'] = $DATA_INJECTION_MAPPING[$type][$field]['name'];
			$DATA_INJECTION_INFOS[$type][$field]['type'] = $DATA_INJECTION_MAPPING[$type][$field]['type'];
		}
	}
}

?>
