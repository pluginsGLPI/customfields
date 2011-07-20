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
// Purpose of file: Code for hooks, etc.
// ----------------------------------------------------------------------

//include_once ('inc/plugin_customfields.function.php');
//include_once ('inc/plugin_customfields.class.php');

// Define dropdown relations for use by GLPI
function plugin_customfields_getDatabaseRelations() {
//TODO: add in releations for multiselects?
   global $DB;
   $plugin = new Plugin();

   if ($plugin->isActivated("customfields")) {
      $relations=array();
      $query="SELECT * FROM glpi_plugin_customfields_fields WHERE entities!='' AND deleted=0 AND data_type='dropdown' ORDER BY device_type";
      $result=$DB->query($query);
      while ($data=$DB->fetch_assoc($result)) {
         $relations[$data['dropdown_table']]=array(plugin_customfields_table($data['device_type'])=>$data['system_name']);
      }

      $entities=array();
      $query="SELECT dropdown_table FROM glpi_plugin_customfields_dropdowns WHERE has_entities=1;";
      $result=$DB->query($query);
      while ($data=$DB->fetch_assoc($result)) {
         $entities[$data['dropdown_table']]='FK_entities'; 
      }
      if(!empty($entities)) {
         $relations['glpi_entities']=$entities;
      }

      return $relations;
   }
   else {
      return array();
   }
}

// Define dropdown tables to be managed in GLPI
function plugin_customfields_getDropdown() {
   global $DB;
   $plugin = new Plugin();

   if ($plugin->isActivated("customfields")) {
      $dropdowns = array();

      $query='SELECT * FROM glpi_plugin_customfields_dropdowns';
      $result=$DB->query($query);
      while ($data=$DB->fetch_assoc($result)) {
         $dropdowns[$data['dropdown_table']]=$data['label'];
      }
      return $dropdowns;
   }
   else {
      return array();
   }
}

/////////// SEARCH FUNCTIONS ////////////

// Define search options for each device type that has custom fields.
// 'Search options' are also used by GLPI for logging and mass updates.
function plugin_customfields_getSearchOption() {
   global $LANG,$DB,$LINK_ID_TABLE,$CFG_GLPI;
   $sopt=array();

   // Initialize values for mass update and extended searches
   $mpos=array();
   $xpos=array();
   foreach($LINK_ID_TABLE as $k=>$v) {
      $xpos[$k]=7100;
      $mpos[$k]=7200;
   }
   for($i=500;$i<=520;$i++) {
      // for components
      $mpos[$i]=7200;
   }

   $sopt[PLUGIN_CUSTOMFIELDS_TYPE]['common']=$LANG['plugin_customfields']['title'];

   $query="SELECT f.*, dd.is_tree, cf.enabled FROM glpi_plugin_customfields as cf, glpi_plugin_customfields_fields AS f ".
      " LEFT JOIN glpi_plugin_customfields_dropdowns AS dd ON dd.system_name=f.system_name ".
      " WHERE f.device_type=cf.device_type ".
      " ORDER BY f.device_type, f.location, f.sort_order, f.label";
   $result=$DB->query($query);

   $device_type='';
   while ($data=$DB->fetch_assoc($result)) {
      if($data['restricted'] && !plugin_customfields_fieldHaveRight($data['device_type'],$data['system_name'],'r')) {
         continue; // no access to this field
      }

      // Range 5200-7699 used by this plugin
      $lpos = $data['sopt_pos'] + 5200; // first 1000 used for logging
      $spos = $data['sopt_pos'] + 6200; // next 900 used for regular searches
      if($data['device_type']!=$device_type) {
         $device_type=$data['device_type'];
         $table = plugin_customfields_link_id_table($device_type);
         $table2 = plugin_customfields_table($device_type);
         // Put a default header
         $headingtext=plugin_customfields_device_type_label($device_type).'*';
         $extendedtype=false;
         $extends=array();
         if($device_type==NETWORKING_PORT_TYPE) {
            $extendedtype=true;
            $extends=array(COMPUTER_TYPE, NETWORKING_TYPE, PRINTER_TYPE, PERIPHERAL_TYPE, PHONE_TYPE);
         }
         if($device_type==INFOCOM_TYPE) {
            $extendedtype=true;
            $extends=$CFG_GLPI['infocom_types'];
         }
         if($extendedtype) {
            foreach($extends as $type)
               $sopt[$type]['customfields_typeheader_'.$device_type]=$headingtext;
         }
         else {
            $sopt[$device_type]['customfields_typeheader_'.$device_type]=$headingtext;
         }
      }

      if($data['deleted'] || $data['entities']=='' || !$data['enabled']) { // preserve names for log history
         if(CUSTOMFIELDS_GLPI_PATCH_APPLIED) {
            $sopt[$device_type][$lpos]['name']=$data['label'];
            $sopt[$device_type][$lpos]['field']='';
            $sopt[$device_type][$lpos]['linkfield']='';
            $sopt[$device_type][$lpos]['purpose']='log'; // an extra field used to clean search options
         }
      }
      elseif($data['data_type']=='sectionhead') {
         if($extendedtype) {
            foreach($extends as $type) {
               $sopt[$type]['customfields_'.$data['system_name']]=$data['label'];
            }
         }
         else {
            $sopt[$device_type]['customfields_'.$data['system_name']]=$data['label'];
         }
      }
      elseif($data['data_type']=='multiselect') {
         $sopt[$device_type][$lpos]['table']='glpi_plugin_customfields_multiselect_'.$data['ID'];
         list($tablename,$fieldname)=explode('.',$data['dropdown_table']);
         $sopt[$device_type][$lpos]['field']=$fieldname;
         $sopt[$device_type][$lpos]['linkfield']='';
         $sopt[$device_type][$lpos]['name']=$data['label'];
         $sopt[$device_type][$lpos]['forcegroupby']=true;
      }
      elseif($data['data_type']=='dropdown') {
         $sopt[$device_type][$lpos]['table']=$data['dropdown_table'];
         if($data['is_tree']==1) {
            $sopt[$device_type][$lpos]['field']='completename';
         }
         else {
            $sopt[$device_type][$lpos]['field']='name';
         }
         $sopt[$device_type][$lpos]['linkfield']=$data['system_name'];
         $sopt[$device_type][$lpos]['name']=$data['label'];
         // ALSO register the field under associated types
         foreach($extends as $type) {
            $xpos[$type]++;
            $xspos=$xpos[$type];
            $sopt[$type][$xspos]['table']=$data['dropdown_table'];
            if($data['is_tree']==1) {
               $sopt[$type][$xspos]['field']='completename';
            }
            else {
               $sopt[$type][$xspos]['field']='name';
            }
            $sopt[$type][$xspos]['linkfield']=$data['system_name'];
            $sopt[$type][$xspos]['name']=$data['label'];
            $sopt[$type][$xspos]['forcegroupby']=true;
            $sopt[$type][$xspos]['purpose']='search';
         }
      }
      else {
         // Note: Yes/No fields are included in search, logging, and mass update functionality. 
         // In the GLPI core they are not usually included.

         // For fields that aren't dropdowns, it is necessary to apply a patch 
         // to enable logging and mass update functionality
         if(CUSTOMFIELDS_GLPI_PATCH_APPLIED) {
            // for logging (these might need to be the first set of options)
            $sopt[$device_type][$lpos]['table']=$table;
            $sopt[$device_type][$lpos]['field']=$data['system_name'];
            $sopt[$device_type][$lpos]['linkfield']='';
            $sopt[$device_type][$lpos]['name']=$data['label'];
            $sopt[$device_type][$lpos]['purpose']='log'; // an extra field used to clean search options

            // for mass update
            $mpos[$device_type]++;
            $mupos=$mpos[$device_type];
            $sopt[$device_type][$mupos]['table']=$table2;
            $sopt[$device_type][$mupos]['field']=$data['system_name'];
            $sopt[$device_type][$mupos]['linkfield']=$data['system_name'];
            $sopt[$device_type][$mupos]['name']=$data['label'];
            $sopt[$device_type][$mupos]['purpose']='update'; // an extra field used to clean search options
         }
         // for search
         if($extendedtype) {
            $xstable=plugin_customfields_table($device_type);
            foreach($extends as $type) {
               $xpos[$type]++;
               $xspos=$xpos[$type];
               $sopt[$type][$xspos]['table']=$xstable;
               $sopt[$type][$xspos]['field']=$data['system_name'];
               $sopt[$type][$xspos]['linkfield']='ID';
               $sopt[$type][$xspos]['name']=$data['label'];
               $sopt[$type][$xspos]['forcegroupby']=true;
               $sopt[$type][$xspos]['purpose']='search';
/*
               // REMOVED. THis is handled separately for financial cf. No Mass Update for Nework port cf (does not make sense with the one-many rel.) 
               // for mass update
               $mpos[$type]++;
               $mupos=$mpos[$type];
               $sopt[$type][$mupos]['table']=$table2;
               $sopt[$type][$mupos]['field']=$data['system_name'];
               $sopt[$type][$mupos]['linkfield']=$data['system_name'];
               $sopt[$type][$mupos]['name']=$data['label'];
               $sopt[$type][$mupos]['purpose']='update'; // an extra field used to clean search options
*/
            }
         }
         else {
            $sopt[$device_type][$spos]['table']=$table2;
            $sopt[$device_type][$spos]['field']=$data['system_name'];
            $sopt[$device_type][$spos]['linkfield']='ID';
            $sopt[$device_type][$spos]['name']=$data['label'];
            $sopt[$device_type][$spos]['purpose']='search'; // an extra field used to clean search options
         }
      }
   }

   return $sopt;
}

// Clean Search Options: Necessary for search to work properly if GLPI patch applied. 
// Removes the search options that are used for different purposes.
// This function requires the glpi patch in order to be called. See the patch directory for instructions.
function plugin_customfields_cleanSearchOption($options, $action) {
   if(!empty($options)) {
      foreach($options as $ID => $value) {
         if(is_array($value) && isset($value['purpose'])) {
            // If action is 'r' we are cleaning before a search. 
            // If action is 'w', we are cleaning before an update.
            if ($value['purpose']=='log') {
               unset($options[$ID]);
            }
            elseif ($value['purpose']=='search' && $action=='w') {
               unset($options[$ID]);
            }
            elseif ($value['purpose']=='update' && $action=='r') {
               unset($options[$ID]);
            }
         }
      }
   }

   return $options;
}
// Define how to join the tables when doing a search
function plugin_customfields_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables) {
   global $DB;
   $type_table = plugin_customfields_table($type);
   if ($new_table==$type_table) {
      $out=addLeftJoin($type,$ref_table,$already_link_tables,$new_table,$linkfield);
      return $out;
   }
   elseif (substr($new_table,0,37)=='glpi_plugin_customfields_multiselect_') {
      $msnum=intval(substr($new_table,37));
      $query="SELECT `dropdown_table` FROM `glpi_plugin_customfields_fields` WHERE ID='$msnum';";
      $result=$DB->query($query);
      $row=$DB->fetch_assoc($result);
      $middletable='ms_link_'.$msnum;
      list($endtable)=explode('.',$row['dropdown_table']);
      $out=" LEFT JOIN glpi_plugin_customfields_multiselect AS $middletable ON $middletable.device = $ref_table.ID AND $middletable.field='$msnum'";
      $out.=" LEFT JOIN $endtable AS $new_table ON $new_table.ID = $middletable.item ";
      return $out;
   }
   elseif ($new_table=='glpi_plugin_customfields_networking_ports') {
      $out=addLeftJoin($type,$ref_table,$already_link_tables,"glpi_networking_ports",'');
//      $out.=addLeftJoin(NETWORKING_PORT_TYPE,'glpi_networking_ports',$already_link_tables,"glpi_plugin_customfields_networking_ports",'ID'); 
      // addLeftJoinThis doesn't work here for some reason, so we hard code the second join
      $out.=" LEFT JOIN glpi_plugin_customfields_networking_ports ON (glpi_networking_ports.ID = glpi_plugin_customfields_networking_ports.ID) ";
      return $out;
   }
   elseif ($new_table=='glpi_plugin_customfields_infocoms') {
      $out=addLeftJoin($type,$ref_table,$already_link_tables,"glpi_infocoms",'');
      $out.=" LEFT JOIN glpi_plugin_customfields_infocoms ON (glpi_infocoms.ID = glpi_plugin_customfields_infocoms.ID) ";
      return $out;
   }
   else { // it is a custom dropdown
      $query="SELECT * FROM `glpi_plugin_customfields_fields` WHERE `dropdown_table`='$new_table' AND `device_type`='$type' AND `deleted`=0 AND `entities`!='';";
      $result=$DB->query($query);
      if($DB->numrows($result)) { // A regular dropdown (this fails if the same dd is used in the device AND in networking ports)
         $out=addLeftJoin($type,$ref_table,$already_link_tables,$type_table,'ID');
         $out.= " LEFT JOIN $new_table ON ($new_table.ID = $type_table.$linkfield) ";
      }
      else { // a dropdown in network ports
         $query="SELECT * FROM `glpi_plugin_customfields_fields` WHERE `dropdown_table`='$new_table' AND `deleted`=0 AND `entities`!='' 
            AND `device_type` IN (".NETWORKING_PORT_TYPE.",".INFOCOM_TYPE.");";
         $result=$DB->query($query);
         if($DB->numrows($result)) { // (this fails if the same dd is used in networking ports and financial cf)
            $row=$DB->fetch_assoc($result);
            $dtype=$row['device_type'];
            $dtype_table=plugin_customfields_link_id_table($dtype);
            $cftype_table=plugin_customfields_table($dtype);
            // Link to intermediate table first
            $out=addLeftJoin($type,$ref_table,$already_link_tables,$dtype_table,'');
            $out.=addLeftJoin($dtype,$dtype_table,$already_link_tables,$cftype_table,'ID');
            $out.=" LEFT JOIN $new_table ON ($cftype_table.$linkfield = $new_table.ID) ";
         }
      }
      return $out;
   }
}

///////////// VARIOUS HOOKS /////////////////

// Hook to process Mass Update & transfer
function plugin_pre_item_update_customfields($data) {
   global $ACTIVE_CUSTOMFIELDS_TYPES;

   if (empty($ACTIVE_CUSTOMFIELDS_TYPES)) { 
      return $data;
   }

   // If update isn't set, then this is a mass update or transfer, not a regular update
   if(in_array($data['_item_type_'],$ACTIVE_CUSTOMFIELDS_TYPES) && !isset($data['_already_called_'])) {
      if(!isset($data['update'])) {
         // mass update or tranfer, possibly affecting one of our custom fields
         $updates=array();      
         if(isset($data['FK_entities'])) { // the item is being transfered to another entity
            $updates=plugin_customfields_transferAllDropdowns($data['ID'],$data['_item_type_'],$data['FK_entities']);
         }

         $plugin_customfields = new plugin_customfields($data['_item_type_']);
         $newdata=array_merge($updates,$data);
         $newdata['_already_called_']=true; // prevents recurrsion
         // The data may or may not be a custom field. At the moment we try an update regardless
         $plugin_customfields->update($newdata);
      }
      else {
         $plugin_customfields = new plugin_customfields($data['_item_type_']);
         if(plugin_customfields_HaveRight($data['_item_type_'],'w')) {
            $post=plugin_customfields_transformPost($data);
            $post['_already_called_']=true; // prevents recursion
            $plugin_customfields->update($post);
            if(isset($post['_multiselects'])) {
               plugin_customfields_updateMultiselects($data['_item_type_'],$data['ID'],$post['_multiselects'],$data['FK_entities']);
            }
         }
      }
   }

   return $data; // return the original data, not our additional data
}

// Hook done on add item case
// If in Auto Activate mode, add a record for the custom fields when a device is added
function plugin_item_add_customfields($parm) {
   global $DB,$ACTIVE_CUSTOMFIELDS_TYPES;

   if(isset($parm['input']['_already_called_'])) return false;

   if (CUSTOMFIELDS_AUTOACTIVATE && isset($parm['type']) && !empty($ACTIVE_CUSTOMFIELDS_TYPES)) {
      if (in_array($parm['type'], $ACTIVE_CUSTOMFIELDS_TYPES)) {
         if(plugin_customfields_HaveRight($parm['type'],'w')) {
//TODO: What if autoactivate is not set?? Just require it to be set?
            $plugin_customfields = new plugin_customfields($parm['type']);
            $post=plugin_customfields_transformPost($parm['input']);
            $post['ID']=$parm['ID'];
            $post['_already_called_']=true; // prevents recursion
            $plugin_customfields->add($post);
            if(isset($post['_multiselects'])) {
               $entity=isset($parm['input']['FK_entities']) ? $parm['input']['FK_entities'] : 0;
               plugin_customfields_updateMultiselects($parm['type'],$parm['ID'],$post['_multiselects'],$entity);
            }
            return true;
         }
      }
   }
   return false;
}

// Hook done on purge item case
function plugin_item_purge_customfields($parm) {
   global $DB,$ALL_CUSTOMFIELDS_TYPES;

   // Must delete custom fields when main item is purged, even if custom fields for this device are currently disabled
   if (in_array($parm['type'],$ALL_CUSTOMFIELDS_TYPES) && ($table=plugin_customfields_table($parm['type']))) {
      $sql="DELETE FROM `$table` WHERE ID = '".intval($parm['ID'])."' LIMIT 1;";
      $result=$DB->query($sql);
      return true;
   }
   else {
      return false;
   }
}

// This function requires the glpi patch in order to be called. See the patch directory for instructions
function plugin_customfields_MassiveActionsFieldsDisplay($type,$table,$field,$linkfield) {
   global $DB;
   $query="SELECT * FROM glpi_plugin_customfields_fields WHERE device_type='$type' AND system_name='$linkfield';";
   $result=$DB->query($query);
   if ($data=$DB->fetch_assoc($result)) {
      switch($data['data_type']) {
         case 'dropdown':
            dropdownValue($data['dropdown_table'], $linkfield, 1, $_SESSION['glpiactive_entity']);
            break;
         case 'yesno':
            dropdownYesNo($linkfield,0);
            break;
         case 'date':
            showDateFormItem($linkfield,'',true,true);
            break;
         case 'money':         
            echo '<input type="text" size="16" value="'.formatNumber(0,true).'" name="'.$linkfield.'"/>';
            break;
         default:
            autocompletionTextField($linkfield,$table,$field); 
            break;
      }
      return true;
   }
   else {
      return false;
   }
}

function plugin_customfields_MassiveActions($type) {
   global $LANG,$CFG_GLPI,$ACTIVE_CUSTOMFIELDS_TYPES;
   if(in_array(INFOCOM_TYPE,$ACTIVE_CUSTOMFIELDS_TYPES) && in_array($type,$CFG_GLPI['infocom_types'])) {
      return array('plugin_customfields_update_infocom'=>$LANG['plugin_customfields']['Update_Financial_CF']);
   }
   return array();
}

function plugin_customfields_MassiveActionsDisplay($type,$action) {
   global $LANG,$CFG_GLPI,$LINK_ID_TABLE,$SEARCH_OPTION;
   if($action=='plugin_customfields_update_infocom' && in_array($type,$CFG_GLPI['infocom_types'])) {
      $first_group=true;
      $newgroup="";
      $items_in_group=0;
      echo "<select name='id_field' id='massiveaction_field'>";
      echo "<option value='0' selected>------</option>";
      foreach($SEARCH_OPTION[INFOCOM_TYPE] as $key => $val) {
         if (!is_array($val)) {
            if (!empty($newgroup)&&$items_in_group>0) {
               echo $newgroup;
               $first_group=false;
            }
            $items_in_group=0;
            $newgroup="";
            if (!$first_group) $newgroup.="</optgroup>";
            $newgroup.="<optgroup label=\"$val\">";
         } 
         elseif(!isset($val['purpose']) || $val['purpose']=='update') {
            $newgroup.= "<option value='".$key."'>".$val['name']."</option>";
            $items_in_group++;
         }
      }
      if (!empty($newgroup)&&$items_in_group>0) echo $newgroup;
      if (!$first_group) {
         echo "</optgroup>";
      }

      echo "</select>";
   
      $paramsmassaction=array('id_field'=>'__VALUE__',
         'device_type'=>INFOCOM_TYPE,
         );
      ajaxUpdateItemOnSelectEvent("massiveaction_field","show_massiveaction_field",$CFG_GLPI["root_doc"]."/ajax/dropdownMassiveActionField.php",$paramsmassaction);
   
      echo "<span id='show_massiveaction_field'>&nbsp;</span>\n";
   }
   return "";
}
/*
function plugin_customfields_haveTypeRight($type,$right) {
   return haveTypeRight($type,$right); // necessary for mass update of financial info or not necessary at all?
}
*/

function plugin_customfields_MassiveActionsProcess($data) {
   // only used for infocoms
   global $LANG,$DB;
   if(isset($_POST['item']) && is_array($_POST['item'])) {
      $items=addslashes_deep($_POST['item']);
      $device_type=intval($_POST['device_type']);
      $field=mysql_real_escape_string($_POST['field']);
      $value=mysql_real_escape_string($_POST[$field]);

      foreach($items as $item_id => $checked) {
         if($checked==1) {
            $sql="SELECT `ID` FROM `glpi_infocoms` 
               WHERE `FK_device`='$item_id' AND `device_type`='$device_type';";
            $result=$DB->query($sql);
            if($DB->numrows($result)>0) {
               $row=$DB->fetch_assoc($result);
               $infocom_id=$row['ID'];
               $sql="UPDATE glpi_plugin_customfields_infocoms SET `$field`='$value' 
                  WHERE `ID`='$infocom_id';";
               $result=$DB->query($sql);
            }
         }
      }
   }
}

// Define headings added by the plugin -- determines if a tab should be shown or not
function plugin_get_headings_customfields($type,$ID,$withtemplate) {
   global $LANG, $ACTIVE_CUSTOMFIELDS_TYPES;

   // Show the tab if Custom fields have been activated for this device type
   if ($type==PROFILE_TYPE || !empty($ACTIVE_CUSTOMFIELDS_TYPES) && in_array($type,$ACTIVE_CUSTOMFIELDS_TYPES)) {
      // template case
      if ($withtemplate || $ID<0 || $ID=='') {
         return array();
      }
      // Non template case
      else {
         return array(1 => $LANG['plugin_customfields']['title']);
      }
   }
   else {
      return false;
   }
}
// Define headings actions added by the plugin -- what happens when you click on the tab
function plugin_headings_actions_customfields($type) {
   global $ACTIVE_CUSTOMFIELDS_TYPES;

   if ($type==PROFILE_TYPE || !empty($ACTIVE_CUSTOMFIELDS_TYPES) && in_array($type,$ACTIVE_CUSTOMFIELDS_TYPES)) {
      return array(1 => 'plugin_headings_customfields');
   }
   else {
      return false;
   }
}

// customfields of an action heading -- show the custom fields
function plugin_headings_customfields($type,$ID,$withtemplate=0) {
   if($type==PROFILE_TYPE) {
      global $CFG_GLPI;
      $prof=new plugin_customfields_Profile();
      if (!$prof->GetfromDB($ID)) {
         plugin_customfields_createaccess($ID);
      }
      $prof->showForm($CFG_GLPI["root_doc"]."/plugins/customfields/front/plugin_customfields.profile.php",$ID);
   } 
   elseif ($ID > -1) {
      echo '<div align="center">';
      echo plugin_customfields_showAssociated($type,$ID);
      echo '</div>';
   }
}

// Define fields that can be updated with the data_injection plugin
function plugin_customfields_datainjection_variables() {   
   global $IMPORT_PRIMARY_TYPES, $DATA_INJECTION_MAPPING, $LANG, $IMPORT_TYPES,$DATA_INJECTION_INFOS,$DB;
   $plugin = new Plugin();

   if ($plugin->isActivated("customfields")) {
      $query="SELECT * FROM glpi_plugin_customfields_fields WHERE data_type <> 'sectionhead' AND deleted=0;";
      $result=$DB->query($query);
      while ($data=$DB->fetch_assoc($result)) {
         $type=5200 + $data['device_type']; // this plugin uses the range 5200-7699
         $field = $data['system_name'];
         if($data['data_type']=='dropdown') {
            $DATA_INJECTION_MAPPING[$type][$field]['table'] = $data['dropdown_table'];
            $DATA_INJECTION_MAPPING[$type][$field]['field'] = 'name';
            $DATA_INJECTION_MAPPING[$type][$field]['linkfield'] = $field;
            $DATA_INJECTION_INFOS[$type][$field]['linkfield'] = $field;
            $DATA_INJECTION_MAPPING[$type][$field]['table_type'] = 'dropdown';
            $DATA_INJECTION_INFOS[$type][$field]['table_type'] = 'dropdown';
         }
         else {
            $DATA_INJECTION_MAPPING[$type][$field]['table'] = plugin_customfields_table($data['device_type']);
            $DATA_INJECTION_MAPPING[$type][$field]['field'] = $field;
         }
         $DATA_INJECTION_MAPPING[$type][$field]['name'] = $data['label'];
         switch($data['data_type']) {
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
