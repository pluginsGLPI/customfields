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

function plugin_customfields_fieldHaveRight($device_type,$field,$right) {
   $field=$device_type.'_'.$field;
   $matches=array(
         "r" => array("r","w","q","1"),
         "w" => array("w","q"),
         "q" => array("q"),
            );
   if (isset($_SESSION["glpi_plugin_customfields_profile"][$field])&&in_array($_SESSION["glpi_plugin_customfields_profile"][$field],$matches[$right])) {
      return true;
   }
   else {
      return false;
   }
}

function plugin_customfields_haveRight($device_type,$right) {
   // Rights for custom fields are the same as the rights for each device type

   if($device_type>500 && $device_type<520) {
      // This is a work-around to get custom fields to work with components.
      return haveRight('device',$right);
   }
   if ($device_type >= 1000) {
      // This is a plugin. There is no standard way to check rights for plugins,
      // so if customfield are added on a plugin everyone who can see the plugin
      // will have write access to the custom fields.
      return true;
   }
   switch($device_type) {
      case COMPUTER_TYPE: return haveRight('computer',$right); break;
      case COMPUTERDISK_TYPE: return haveRight('computer',$right); break;
      case MONITOR_TYPE: return haveRight('monitor',$right); break;
      case SOFTWARE_TYPE: return haveRight('software',$right); break;
      case SOFTWAREVERSION_TYPE: return haveRight('software',$right); break;
      case SOFTWARELICENSE_TYPE: return haveRight('software',$right); break;
      case NETWORKING_TYPE: return haveRight('networking',$right); break;
      case NETWORKING_PORT_TYPE: return haveRight('networking',$right); break;
      case PERIPHERAL_TYPE: return haveRight('peripheral',$right); break;
      case PRINTER_TYPE: return haveRight('printer',$right); break;
      case CARTRIDGE_TYPE: return haveRight('cartridge',$right); break;
      case CONSUMABLE_TYPE: return haveRight('consumable',$right); break;
      case PHONE_TYPE: return haveRight('phone',$right); break;
      case CONTACT_TYPE: return haveRight('contact_enterprise',$right); break;
      case ENTERPRISE_TYPE: return haveRight('contact_enterprise',$right); break;
      case CONTRACT_TYPE: return haveRight('contract',$right); break;
      case DOCUMENT_TYPE: return haveRight('document',$right); break;
      case TRACKING_TYPE: return haveRight('update_ticket','1'); break; // Update consided 'write' access for tickets
      case USER_TYPE: return haveRight('user',$right); break;
      case GROUP_TYPE: return haveRight('group',$right); break;
      case ENTITY_TYPE: return haveRight('entity',$right); break;
      case INFOCOM_TYPE: return haveRight('infocom',$right); break;
      default: return false;
   }
}

function plugin_customfields_checkRight($device_type, $right) {
   global $CFG_GLPI;

   if (!plugin_customfields_haveRight($device_type, $right)) {
      // Check for session timeout
      if (!isset ($_SESSION['glpiID'])) {
         glpi_header($CFG_GLPI['root_doc'] . '/index.php');
         exit ();
      }
      displayRightError();
   }
}

////////////////// DATABASE FUNCTIONS /////////////////////////

// Removes most accents used in European languages
function plugin_customfields_remove_accents($str) {
   $str = htmlentities($str, ENT_COMPAT, 'UTF-8');
   $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|ring);/','$1',$str);
   $from = explode(' ', '&#192; &#193; &#194; &#195; &#196; &#197; &#199; &#200; &#201; &#202; &#203; &#204; &#205; &#206; &#207; &#208; &#209; &#210; &#211; &#212; &#213; &#214; &#217; &#218; &#219; &#220; &#221; &#224; &#225; &#226; &#227; &#228; &#229; &#230; &#231; &#232; &#233; &#234; &#235; &#236; &#237; &#238; &#239; &#240; &#241; &#242; &#243; &#244; &#245; &#246; &#249; &#250; &#251; &#252; &#253; &#255; &#256; &#257; &#258; &#259; &#260; &#261; &#262; &#263; &#264; &#265; &#266; &#267; &#268; &#269; &#270; &#271; &#272; &#273; &#274; &#275; &#276; &#277; &#278; &#279; &#280; &#281; &#282; &#283; &#284; &#285; &#286; &#287; &#288; &#289; &#290; &#291; &#292; &#293; &#294; &#295; &#296; &#297; &#298; &#299; &#300; &#301; &#302; &#303; &#304; &#305; &#308; &#309; &#310; &#311; &#312; &#313; &#314; &#315; &#316; &#317; &#318; &#319; &#320; &#321; &#322; &#323; &#324; &#325; &#326; &#327; &#328; &#329; &#330; &#331; &#332; &#333; &#334; &#335; &#336; &#337; &#340; &#341; &#342; &#343; &#344; &#345; &#346; &#347; &#348; &#349; &#350; &#351; &#352; &#353; &#354; &#355; &#356; &#357; &#360; &#361; &#362; &#363; &#364; &#365; &#366; &#367; &#368; &#369; &#370; &#371; &#372; &#373; &#374; &#375; &#376; &#377; &#378; &#379; &#380; &#381; &#382;');
   $to = explode(' ', 'A A A A A A C E E E E I I I I D N O O O O O U U U U Y a a a a a a a c e e e e i i i i o n o o o o o u u u u y y A a A a A a C c C c C c C c D d D d E e E e E e E e E e G g G g G g G g G H H h I i I i I i I i I i J j K k k L l L l L l L l L l N n N n N n n N n O o O o O o R r R r R r S s S s S s S s T t T t U u U u U u U u U u U u W w Y y Y Z z Z z Z z');
   return str_replace($from, $to, html_entity_decode($str));
}

// Replace punctuation and spaces with underscore, letters to lowercase.
// Removes most accents, but does not replace foreign scripts, chinese characters, etc.
function plugin_customfields_make_system_name($str, $allowdot=false) {
   $str = plugin_customfields_remove_accents(trim($str));
   $dot=$allowdot ? '.' : '_';
   return strtr($str,
      ' ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()+={}[]<>,?/~`|:;"\'\\.',
      '_abcdefghijklmnopqrstuvwxyz_____________________________'.$dot);
}

// Get the name of the device type OR the non-localized 'name' of the plugin
function plugin_customfields_device_type_label($device_type) {
   global $LANG, $PLUGIN_HOOKS;
   if($device_type >= 1000) {
      return 'Plugin ' . ucwords($PLUGIN_HOOKS['plugin_types'][$device_type]);
   }
   else {
      return $LANG['plugin_customfields']['device_type'][$device_type];
   }
}

// Names of tables uses to store the custom field data
function plugin_customfields_table($device_type) {
   $normal_table = plugin_customfields_link_id_table($device_type);
   $cf_table = 'glpi_plugin_customfields_'.substr($normal_table,5);
   return $cf_table;

}
// Names of glpi tables
function plugin_customfields_link_id_table($device_type) {
   switch($device_type) {
      case (MOBOARD_DEVICE+500): return 'glpi_device_moboard'; break;
      case (PROCESSOR_DEVICE+500): return 'glpi_device_processor'; break;
      case (RAM_DEVICE+500): return 'glpi_device_ram'; break;
      case (HDD_DEVICE+500): return 'glpi_device_hdd'; break;
      case (NETWORK_DEVICE+500): return 'glpi_device_iface'; break;
      case (DRIVE_DEVICE+500): return 'glpi_device_drive'; break;
      case (CONTROL_DEVICE+500): return 'glpi_device_control'; break;
      case (GFX_DEVICE+500): return 'glpi_device_gfxcard'; break;
      case (SND_DEVICE+500): return 'glpi_device_sndcard'; break;
      case (PCI_DEVICE+500): return 'glpi_device_pci'; break;
      case (CASE_DEVICE+500): return 'glpi_device_case'; break;
      case (POWER_DEVICE+500): return 'glpi_device_power'; break;
      default:
         global $LINK_ID_TABLE;
         return $LINK_ID_TABLE[$device_type];
      break;
   }
}
// Activates custom fields for a specific device (used if auto activate is turned off)
function plugin_customfields_activate($device_type,$ID)
{
   global $DB;
   if ($device_type>0 && $ID>=0) {
      if($table=plugin_customfields_table($device_type)) {
         $query="INSERT INTO `$table` (ID) VALUES ('".intval($ID)."');";
         $result = $DB->query($query);
      }
   }
}

// Activates custom fields for all devices of a specific type
function plugin_customfields_activate_all($device_type) {
   global $DB;

   if($device_type==DEVICE_TYPE) return;

   $query="SELECT ID FROM glpi_plugin_customfields_fields WHERE device_type = '".intval($device_type)."';";
   $result=$DB->query($query);
   if($DB->numrows($result) > 0) {
      plugin_customfields_create_data_table($device_type);

      $table1=plugin_customfields_link_id_table($device_type);
      $table2=plugin_customfields_table($device_type);
      if($device_type==ENTITY_TYPE) {
         $sql="INSERT INTO `$table2` (ID) VALUES ('0');"; // Add a row for the Root Entity
         $result2 = $DB->query($sql);
      }

      $query="SELECT a.ID, b.ID AS skip FROM $table1 AS a LEFT JOIN $table2 AS b ON a.ID=b.ID;";
      $result=$DB->query($query);
      while ($data=$DB->fetch_assoc($result)) {
         if(is_null($data['skip'])) {
            $sql="INSERT INTO `$table2` (ID) VALUES ('".intval($data['ID'])."');";
            $result2 = $DB->query($sql);
         }
      }
   }
}

function plugin_customfields_activate_all_types() {
   global $DB;

   $sql="SELECT `device_type` FROM `glpi_plugin_customfields` WHERE `enabled`='1';";
   $result=$DB->query($sql);
   while ($data=$DB->fetch_array($result)) {
      plugin_customfields_activate_all($data['device_type']);
   }
}

// Create a table to store custom data for a device type if it doesn't already exist
function plugin_customfields_create_data_table($device_type) {
   global $DB;
   $table=plugin_customfields_table($device_type);

   if(!TableExists($table)) {
      $sql="CREATE TABLE `$table` (`ID` int(11) NOT NULL default '0', PRIMARY KEY  (`ID`))".
         " ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3;";
      $result = $DB->query($sql);
      return ($result ? true : false);
   }
   else {
      return true;
   }
}

function plugin_customfields_disable_device($device_type) {
   global $DB, $ACTIVE_CUSTOMFIELDS_TYPES, $LANG;
   unset($ACTIVE_CUSTOMFIELDS_TYPES[$device_type]);
   $query="UPDATE glpi_plugin_customfields SET enabled=0 WHERE device_type='$device_type';";
   $result=$DB->query($query);
   addMessageAfterRedirect($LANG['plugin_customfields']['cf_disabled']);
}

// Copies value in a drop down to a new entity if it does not alreay exist in the new entity.
// If the menu has a tree stucture, it will also copy any parents that didn't exist in the new entity.
function plugin_customfields_transferDropdown($ddID,$dd_table,$newentity) {
   global $DB;

   if ($ddID>0) {
      // Search init item
      $query="SELECT * FROM $dd_table WHERE ID='$ddID'";
      if ( ($result=$DB->query($query)) && ($DB->numrows($result)) ) {
         $data=$DB->fetch_array($result);
         $data=addslashes_deep($data);
         // Search if the value already exists in the destination entity
         if(isset($data['completename'])) { // it is a tree
            $query="SELECT ID FROM $dd_table WHERE FK_entities='$newentity' AND completename='".$data['completename']."'";
         }
         else { // it isn't a tree
            $query="SELECT ID FROM $dd_table WHERE FK_entities='$newentity' AND name='".$data['name']."'";
         }
         if ($result_search=$DB->query($query)) {
            // If a match is found, use it
            if ($DB->numrows($result_search)>0) {
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
         if (isset($data['parentID']) && ($data['parentID']>0)) {
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
function plugin_customfields_transferAllDropdowns($ID,$device_type,$newentity) {
   global $DB;
   $updates=array();

   if ($ID > 0) {
      $query="SELECT d.* FROM glpi_plugin_customfields_fields AS f, glpi_plugin_customfields_dropdowns AS d ".
         " WHERE f.device_type='$device_type' AND f.data_type='dropdown' AND d.system_name=f.system_name AND d.has_entities=1;";
      if ($result=$DB->query($query)) {
         while($data=$DB->fetch_array($result)) {
            $data_table=plugin_customfields_table($device_type);
            $system_name=$data['system_name'];
            $query="SELECT `$system_name` AS oldID FROM $data_table WHERE ID='$ID';";
            if ( ($dd_result=$DB->query($query)) && ($dd_data=$DB->fetch_array($dd_result)) ) {
               $newID=plugin_customfields_transferDropdown($dd_data['oldID'],$data['dropdown_table'],$newentity);
               $updates[$data['system_name']]=$newID;
            }
         }
      }
   }
   return $updates;
}

function plugin_customfields_getMetaCache($device_type,$entity=0) {
   global $CFMETACACHE;
   if(!isset($CFMETACACHE[$device_type][$entity])) {
      global $DB;
      $query="SELECT * FROM glpi_plugin_customfields_fields WHERE device_type='$device_type' AND deleted='0' AND (entities='*' OR entities LIKE '%$entity%') ORDER BY sort_order;";
      $result = $DB->query($query);
      while ($fields=$DB->fetch_array($result)) {
         $CFMETACACHE[$device_type][$entity][$fields['system_name']]=$fields;
      }
   }
   return $CFMETACACHE[$device_type][$entity];
}

function plugin_customfields_getDataCache($device_type,$ID) {
   global $CFDATACACHE;
   if(!isset($CFDATACACHE[$device_type][$ID])) {
      $table=plugin_customfields_table($device_type);
      if ($table) {
         global $DB;
         $query="SELECT * FROM `$table` WHERE ID='$ID'";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         if($number==1) {
            $CFDATACACHE[$device_type][$ID]=$DB->fetch_array($result);
         }
      }
   }
   return $CFDATACACHE[$device_type][$ID];
}

function plugin_customfields_transformPost($post) {
   $multis=array();
   if(isset($post['ID'])) {
      $cfpost['ID']=$post['ID'];
   }
   foreach($post as $k=>$v) {
      if(substr($k,0,9)=='__multi__') {
         $k=substr($k,9);
         if(!isset($multis[$k])) {
            $multis[$k]=array(); // In case no option was selected, so we know to delete all options
         }
      }
      elseif(substr($k,0,3)=='cf_' && is_array($v)) {
         $multis[substr($k,3)]=$v;
      }
      elseif(substr($k,0,3)=='cf_') {
         $cfpost[substr($k,3)]=$v;
      }
   }
   if(!empty($multis)) {
      $cfpost['_multiselects']=$multis;
   }
   return $cfpost;
}

function plugin_customfields_updateMultiselects($type,$ID,$multis,$entity) {
   $meta=plugin_customfields_getMetaCache($type,$entity);
   global $DB;
   foreach($multis as $sys_name=>$data) {
      foreach($data as $v) {
         $keydata[$v]=$v;
      }
      $oldvals=array();
      $newvals=array();
      $changed=false;
      $fieldnum=$meta[$sys_name]['ID'];
      list($valuetable,$valuefield)=explode('.',$meta[$sys_name]['dropdown_table']);
      $table="glpi_plugin_customfields_multiselect";
      $sql="SELECT `$table`.*, `$valuetable`.`$valuefield` as `valuefield`
         FROM `$table`
         LEFT JOIN `$valuetable` ON `$table`.`item`=`$valuetable`.`ID`
         WHERE `field`='$fieldnum' AND `device`='$ID'
         ORDER BY `$table`.`ID`";
      $result=$DB->query($sql);
      while ($row=$DB->fetch_assoc($result)) {
         $oldvals[$row['item']]=$row['valuefield'];
         if(isset($keydata[$row['item']])) {
            unset($keydata[$row['item']]);
         }
         else {
            $sql2="DELETE FROM `$table` WHERE `ID`='{$row['ID']}';";
            $result2=$DB->query($sql2);
            $changed=true;
         }
      }
      foreach($keydata as $k) {
         $sql2="INSERT INTO `$table` (`field`,`device`,`item`) VALUES ('$fieldnum','$ID','$k');";
         $result2=$DB->query($sql2);
         $changed=true;
      }
      if($changed) {
         if(!empty($data)) {
            $sql="SELECT `ID`,`$valuefield` as `valuefield` FROM `$valuetable` WHERE `ID` IN ('".implode("','",$data)."') ORDER BY `$valuefield`";
            $result=$DB->query($sql);
            while ($row=$DB->fetch_assoc($result)) {
               $newvals[$row['ID']]=$row['valuefield'];
            }
         }
         $changes=array(
            0=>$meta[$sys_name]['sopt_pos']+5200,
            1=>addslashes(implode("<br>\n",$oldvals)),
            2=>addslashes(implode("<br>\n",$newvals)),
         );
         historyLog($ID, $type, $changes, 0); //, HISTORY_LOG_SIMPLE_MESSAGE);
      }
   }
}

////////////////////// DISPLAY FUNCTIONS /////////////////////////

function plugin_customfields_showMultiselect($field_name,$ID,$fields,$readonly,$fieldnum) {
   global $DB;
   list($ddtable,$ddfield)=explode('.',$fields['dropdown_table']);
   if(substr($ddtable,0,13)=='glpi_dropdown') {
      $titlefield='comments';
   }
   else {
      $titlefield='name'; // maybe should allow user to customize the colum displayed instead of using a default
   }
   $sql="SELECT `$ddtable`.ID, `$ddtable`.`$ddfield`, `$ddtable`.`$titlefield` AS `titlefield`, linktable.device
      FROM `$ddtable`
      LEFT JOIN glpi_plugin_customfields_multiselect linktable ON linktable.item=`$ddtable`.ID
         AND linktable.device='$ID'
         AND linktable.field='$fieldnum'
      ORDER BY `$ddtable`.`$ddfield`;";
   $result=$DB->query($sql);
   $out1='';
   $out2='';
   $items=array();
   $size=0;
   while ($data=$DB->fetch_assoc($result)) {
      $size++;
      $title=$data['titlefield'];
      if($data['device']) {
         $items[]=$data[$ddfield];
         $out1.='<option value="'.$data['ID'].'" title="'.$title.'" selected="selected">'.$data[$ddfield].'</option>'."\n";
      }
      else {
         $out2.='<option value="'.$data['ID'].'" title="'.$title.'">'.$data[$ddfield].'</option>'."\n";
      }
   }
   if($readonly) {
      echo implode('<br />',$items);
   }
   else {
      if($size<2) $size=2;
      if($size>$fields['default_value'] && $fields['default_value']>1) $size=$fields['default_value'];
      if($size>40) $size=40;
      echo '<input type="hidden" name="__multi__'.$fields['system_name'].'" value="" />';
      echo '<select name="'.$field_name.'[]" size="'.$size.'" multiple="multiple">';
      echo $out1.$out2;
      echo '</select>';
   }
}

function plugin_customfields_showValue($value,$size='') {
   if($size!='') {
      echo '<div style="text-align:left;overflow:auto;border:1px solid #999;'.$size.'">';
   }
   if($value!='' && $value!='&nbsp;') {
      echo $value;
   }
   else {
      echo '-';
   }
   if($size!='') {
      echo '</div>';
   }
}

// Show the custom fields form below the main device
function plugin_customfields_showAssociated($device_type,$ID,$withtemplate='') {
   GLOBAL $DB,$CFG_GLPI,$LANG;

   $query="SELECT * FROM glpi_plugin_customfields WHERE device_type='$device_type'";
   $result = $DB->query($query);
   $info=$DB->fetch_array($result);
   if($info['enabled']!=1) {
      return;
   }

   $entity=0;
   if($device_type <= 500 && !in_array($device_type,array(COMPUTERDISK_TYPE,NETWORKING_PORT_TYPE,ENTITY_TYPE,
               SOFTWAREVERSION_TYPE,SOFTWARELICENSE_TYPE,INFOCOM_TYPE))) {
      $table = plugin_customfields_link_id_table($device_type);
      $query="SELECT FK_entities FROM `$table` WHERE ID='$ID'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number==1) {
         $data=$DB->fetch_array($result);
         $entity=$data['FK_entities'];
      }
   }

   $data=plugin_customfields_getDataCache($device_type,$ID);
   if (!$data) { // No data found, so make a link to activate custom fields for this device
      if (plugin_customfields_haveRight($device_type,'w')&&$withtemplate!=2) {
         echo '<div class="center">';
         echo '<strong><a href="'.GLPI_ROOT.'/plugins/customfields/front/plugin_customfields.form.php?device_type='.$device_type.'&amp;ID='.$ID.'&amp;add=add">'.$LANG['plugin_customfields']['Activate_Custom_Fields'].'</a></strong>';
         echo '</div><br>';
      }
   }
   else { // Data was found, so display it
      $meta=plugin_customfields_getMetaCache($device_type,$entity);

      echo '<form action="'.GLPI_ROOT.'/plugins/customfields/front/plugin_customfields.form.php" method="post" name="form_cf">';
      echo '<table class="tab_cadre_fixe">';
      $count=0;
      foreach($meta as $fields) {
         if($fields['location']!=0) continue;

         $output=plugin_customfields_displayField($device_type,$ID,$fields['system_name'],$entity,$withtemplate);
         if($output===false) continue;

         if(in_array($fields['data_type'],array('notes','text','sectionhead'))) {
            if($count % 2) {
               echo '<td colspan="2"></td></tr>';
               $count++;
            }
            echo '<tr';
            if($fields['data_type']!='sectionhead') {
               echo ' class="tab_bg_1"';
            }
            echo '>'.$output.'</tr>';
         }
         else {
            if($count % 2) {
               echo $output.'</tr>';
            }
            else {
               echo '<tr class="tab_bg_1">'.$output;
            }
            $count++;
         }
      } // end of foreach loop
      if($count % 2)
         echo '<td colspan="2"></td></tr>';

      // Show buttons
      if(($count >= 1) && plugin_customfields_haveRight($device_type,'w')) {
         if(CUSTOMFIELDS_AUTOACTIVATE) {
            echo '<tr><td align="center" valign="top" colspan="4" class="tab_bg_2">';
         }
         else {
            echo '<tr><td align="center" valign="top" colspan="2" class="tab_bg_2">';
         }

         echo '<input type="submit" class="submit" value="'.$LANG['plugin_customfields']['Update_Custom_Fields'].'" name="update"/>';
         echo '<input type="hidden" value="'.$ID.'" name="ID"/>';
         echo '<input type="hidden" value="'.$device_type.'" name="device_type"/></td>';

         if(!CUSTOMFIELDS_AUTOACTIVATE) { // Must show the delete button if autoactivate is off
            echo '<td align="center" colspan="2" class="tab_bg_2">';
            echo '<div class="center"><input type="submit" class="submit" value="'.$LANG['buttons'][6].
               '" name="delete"/> <b>'.$LANG['plugin_customfields']['delete_warning'].'</b></div></td>';
         }
         echo '</tr>';
      }

      echo '</table>';
      echo '</form>';
   }

}

function plugin_customfields_displayField($device_type,$ID,$system_name,$entity=0,$withtemplate=0) {
   $data=plugin_customfields_getDataCache($device_type,$ID);
   $meta=plugin_customfields_getMetaCache($device_type,$entity);
   $fields=$meta[$system_name];
   if($fields['entities']!='*') {
      $entities=explode(',',$fields['entities']);
      // don't process the field if it shouldn't be shown for this entity
      if(!in_array($entity,$entities)) {
         return false;
      }
   }
   $required = false;
   $readonly = false;

   if($fields['restricted']) {
      if(!plugin_customfields_fieldHaveRight($device_type,$fields['system_name'],'w')) {
         $readonly=true;
         if(!plugin_customfields_fieldHaveRight($device_type,$fields['system_name'],'r')) {
            return false; // no access to this field, so don't show it
         }
      }
      elseif(plugin_customfields_fieldHaveRight($device_type,$fields['system_name'],'q')) {
         $required=true;
      }
   }
   else {
      $required=$fields['required'];
   }

   if($fields['data_type']=='sectionhead') {
      return '<th colspan="4">'.$fields['label'].'</th>';
   }

   if($fields['data_type']!='multiselect') {
      $value=$data[$fields['system_name']];
   }
   $field_name='cf_'.$fields['system_name'];

   $classnames= array();
   if($required && !$readonly) { // Requiring a readonly field would cause a problem
      $classnames[] = 'required';
   }
   if($fields['unique']) {
      $classnames[]='unique';
   }
   $title = '';
   $classes = '';
   $classnames=implode(' ',$classnames);
   if($classnames!=='') {
      $classes= ' class="'.$classnames.'"';
      $title = ' title="'.$fields['label'].'"';
   }
   ob_start();
   if($fields['data_type']=='notes') {
      echo '<td colspan="4" valign="middle" align="center" class="tab_bg_1 '.$classnames.'"'.$title.'>';
      echo $fields['label'].':<br>';
   }
   elseif($fields['data_type']=='text') {
      echo '<td valign="top"'.$classes.'>'.$fields['label'].': </td>';
      echo '<td colspan="3" align="center"'.$classes.$title.'>';
   }
   else {
      echo '<td'.$classes.'>'.$fields['label'].': </td>';
      echo '<td'.$classes.$title.'>';
   }

   switch($fields['data_type']) {
      case 'general':
         if(!$readonly) {
            echo '<input type="text" size="20" value="'.$value.'" name="'.$field_name.'"/>';
         }
         else {
            plugin_customfields_showValue($value);
         }
         break;
      case 'notes':
         if(!$readonly) {
            echo '<textarea name="'.$field_name.'" rows="20" cols="100">'.$value.'</textarea>';
         }
         else {
            plugin_customfields_showValue($value,'height:30em;width:50em;');
         }
         break;
      case 'text':
         if(!$readonly) {
            echo '<textarea name="'.$field_name.'" rows="4" cols="75">'.$value.'</textarea>';
         }
         else {
            plugin_customfields_showValue($value,'height:6em;width:50em;');
         }
         break;
      case 'dropdown':
         if(!$readonly) {
            dropdownValue($fields['dropdown_table'], $field_name, $value, 1, $entity);
         }
         else {
            plugin_customfields_showValue(getDropdownName($fields['dropdown_table'], $value));
         }
         break;
      case 'multiselect':
         plugin_customfields_showMultiselect($field_name,$ID,$fields,$readonly,$fields['ID']);
         break;
      case 'date':
         //$editcalendar=($withtemplate!=2) && (!$readonly); // may need to implement this later?
         $editcalendar=(!$readonly);
         showDateFormItem($field_name,$value,true,$editcalendar);
         break;
      case 'money':
         if(!$readonly) {
            echo '<input type="text" size="16" value="'.formatNumber($value,true).'" name="'.$field_name.'"/>';
         }
         else {
            plugin_customfields_showValue(formatNumber($value,true));
         }
         break;
      case 'yesno':
         if(!$readonly) {
            echo dropdownYesNo($field_name,$value);
         }
         else {
            plugin_customfields_showValue(getYesNo($field_name,$value));
         }
         break;
      case 'text': // only in effect if the condition about 40 lines above is removed
         if(!$readonly) {
            echo '<textarea name="'.$field_name.'" rows="4" cols="35">'.$value.'</textarea>';
         }
         else {
            plugin_customfields_showValue($value,'height:6em;width:23em;');
         }
         break;
      case 'number':
         if(!$readonly) {
            echo '<input type="text" size="10" value="'.$value.'" name="'.$field_name.'"/>';
         }
         else {
            plugin_customfields_showValue($value);
         }
         break;
   }
   echo '</td>';
   $out=ob_get_contents();
   ob_end_clean();
   return $out;
}

///////////////////// INSTALLATION FUNCTIONS /////////////////////

function plugin_customfields_exec_sql_file($DB_file = '') {
   global $DB;
   $DBf_handle = fopen(GLPI_ROOT.$DB_file, 'rt');
   $sql_query = fread($DBf_handle, filesize(GLPI_ROOT.$DB_file));
   fclose($DBf_handle);
   foreach ( explode(";\n", "$sql_query") as $sql_line) {
      if (get_magic_quotes_runtime()) $sql_line=stripslashes_deep($sql_line);
      $result = $DB->query($sql_line);
   }
}
function plugin_customfields_install() {
   global $DB;

   if(!TableExists('glpi_plugin_customfields')) {
      plugin_customfields_exec_sql_file('/plugins/customfields/install/plugin_customfields.setup1.sql');
      plugin_customfields_exec_sql_file('/plugins/customfields/install/plugin_customfields.setup2.sql');
      plugin_customfields_exec_sql_file('/plugins/customfields/install/plugin_customfields.setup3.sql');
      plugin_customfields_exec_sql_file('/plugins/customfields/install/plugin_customfields.v118.sql');
      return true;
   }
   else {
      global $LANG;

      // Check the compatability of the plugin files and database tables.
      $query="SELECT enabled FROM glpi_plugin_customfields WHERE device_type='-1';";
      $result = $DB->query($query);
      $data=$DB->fetch_array($result);
      $dbversion=$data['enabled']; // Version of the last modification to the plugin tables' structure

      // If the files and database are compatible, use the plugin
      if($dbversion == CUSTOMFIELDS_DB_VERSION_REQUIRED) {
         addMessageAfterRedirect($LANG['plugin_customfields']['setup'][4]);
         return true;
      }

      // If the database tables are from different version, notify the user
      if($dbversion < CUSTOMFIELDS_DB_VERSION_REQUIRED) {
         addMessageAfterRedirect($LANG['plugin_customfields']['setup'][5]);
         if(CUSTOMFIELDS_DB_VERSION_REQUIRED==116) {
            addMessageAfterRedirect('<b style="color:red">WE RECOMMEND BACKING UP YOUR DATA BEFORE ACTIVATING CUSTOM FIELDS VERSION 1.1.6!</b>');
         }
         return true;
      }
      else {
         addMessageAfterRedirect($LANG['plugin_customfields']['setup'][7]);
         return false;
      }
   }
}

function plugin_customfields_upgrade($oldversion) {
   global $DB;

   // Upgrade logging feature
   if($oldversion < 101) {
      $sql = "ALTER TABLE `glpi_plugin_customfields_fields` ADD `deleted` smallint(6) NOT NULL DEFAULT '0', ".
         " ADD `sopt_pos` int(11) NOT NULL DEFAULT '0';";
      $result=$DB->query($sql);
      $sql = "UPDATE `glpi_plugin_customfields_fields` SET `sopt_pos`=`ID`;"; // initialize sopt_pos to something unique
      $result=$DB->query($sql);
   }

   // Save settings
   $sql="SELECT `device_type` FROM `glpi_plugin_customfields` WHERE `enabled`='1';";
   $result=$DB->query($sql);
   $enabled=array();
   while ($data=$DB->fetch_array($result)) {
      $enabled[]=$data['device_type'];
   }

   // Upgrade date fields to be compatible with GLPI 0.72+
   if($oldversion < 110) {
      $sql="SELECT `device_type`,`system_name` FROM `glpi_plugin_customfields_fields` WHERE `data_type`='date';";
      $result=$DB->query($sql);
      while ($data=$DB->fetch_array($result)) {
         $table =plugin_customfields_table($data['device_type']);
         $field = $data['system_name'];
         $sql = "ALTER TABLE `$table` CHANGE `$field` `$field` DATE NULL DEFAULT NULL;";
         $DB->query($sql);
         $sql = "UPDATE `$table` SET `$field`=NULL WHERE `$field`='0000-00-00';";
         $DB->query($sql);
      }
   }

   // Add a column to indicate if a field is required
   if($oldversion < 112) {
      $sql = "ALTER TABLE `glpi_plugin_customfields_fields` ADD `required` smallint(6) NOT NULL DEFAULT '0';";
      $result=$DB->query($sql);
      $sql = "ALTER TABLE `glpi_plugin_customfields_fields` CHANGE `device_type` `device_type` INT(11) NOT NULL DEFAULT '0';";
      $result=$DB->query($sql);
   }

   // Add a column to indicate which entities to show the field with
   // Remove column for hidden field, use blank in entites field to replace this functionality
   // Add restricted field to allow field-based permissions
   if($oldversion < 113) {
      $sql = "ALTER TABLE `glpi_plugin_customfields_fields` ADD `entities` VARCHAR(255) NOT NULL DEFAULT '*';";
      $result=$DB->query($sql);
      $sql = "UPDATE `glpi_plugin_customfields_fields` SET `entities`='' WHERE `hidden`=1;";
      $result=$DB->query($sql);
      $sql = "ALTER TABLE `glpi_plugin_customfields_fields` DROP `hidden`;";
      $result=$DB->query($sql);
      $sql = "ALTER TABLE `glpi_plugin_customfields_fields` ADD `restricted` smallint(6) NOT NULL DEFAULT '0';";
      $result=$DB->query($sql);
      plugin_customfields_exec_sql_file('/plugins/customfields/install/plugin_customfields.setup3.sql');
   }

   // Upgrade fields to be compatable with mysql strict mode
   if($oldversion < 116) {
      $transform=array();
      $transform['general'] = 'VARCHAR(255) collate utf8_unicode_ci default NULL';
      $transform['dropdown']= 'INT(11) NOT NULL default \'0\'';
      $transform['yesno']   = 'SMALLINT(6) NOT NULL default \'0\'';
      $transform['text']    = 'TEXT collate utf8_unicode_ci';
      $transform['notes']   = 'LONGTEXT collate utf8_unicode_ci';
      $transform['number']  = 'INT(11) NOT NULL default \'0\'';
      $transform['money']   = 'DECIMAL(20,4) NOT NULL default \'0.0000\'';

      $sql="SELECT `device_type`,`system_name`,`data_type` FROM `glpi_plugin_customfields_fields`
         WHERE `deleted`='0' AND data_type!='sectionhead' AND data_type!='date'
         ORDER BY `device_type`, `sort_order`, `ID`;";
      $result=$DB->query($sql);
      set_time_limit(300);
      echo 'Updating Custom Fields...';
      while ($data=$DB->fetch_array($result)) {
         echo '.';
         glpi_flush();
         $table =plugin_customfields_table($data['device_type']);
         $field = $data['system_name'];
         $newtype = $transform[$data['data_type']];
         $sql = "ALTER TABLE `$table` CHANGE `$field` `$field` $newtype;";
         $DB->query($sql);
         if(in_array($data['data_type'],array('general','text','notes'))) {
            $sql = "UPDATE `$table` SET `$field`=NULL WHERE `$field`='';";
            $DB->query($sql);
         }
      }
      plugin_customfields_exec_sql_file('/plugins/customfields/install/plugin_customfields.changes-116.sql');
      echo 'finished.';
      glpi_flush();
   }

   if($oldversion < 117) {
      $sql = "ALTER TABLE `glpi_plugin_customfields_fields` ADD `unique` smallint(6) NOT NULL DEFAULT '0';";
      $result=$DB->query($sql);
      // General Upgrade (from now on add new types in the code, not the sql file)
      plugin_customfields_exec_sql_file('/plugins/customfields/install/plugin_customfields.setup1.sql');
   }
   if($oldversion < 118) {
      plugin_customfields_exec_sql_file('/plugins/customfields/install/plugin_customfields.v118.sql');
   }

   // Restore settings
   foreach($enabled as $device_type) {
      $sql="UPDATE `glpi_plugin_customfields` SET `enabled`='1' WHERE `device_type`='$device_type';";
      $DB->query($sql);
      plugin_customfields_activate_all($device_type);
   }
}

function plugin_customfields_uninstall() {
   global $LANG;

   if(TableExists('glpi_plugin_customfields')) {
      $message = $LANG['plugin_customfields']['setup'][8] . '<br>'.
         '<a href="../plugins/customfields/front/plugin_customfields.removedata.php" '.
         'onclick="return confirm(\''.$LANG['plugin_customfields']['setup'][10].'\');">'.
         $LANG['plugin_customfields']['setup'][9].'</a>';
      addMessageAfterRedirect($message);
   }
   return true;
}

function plugin_customfields_remove_data() {
   global $DB, $ALL_CUSTOMFIELDS_TYPES;

   $query='SELECT dropdown_table FROM glpi_plugin_customfields_dropdowns';
   if($result=$DB->query($query)) {
      while ($data=$DB->fetch_assoc($result)) {
         $table=$data['dropdown_table'];
         if($table!='') {
            $query = "DROP TABLE IF EXISTS `$table`;";
            $DB->query($query) or die($DB->error());
         }
      }
   }

   $query='SELECT device_type, enabled FROM glpi_plugin_customfields WHERE device_type > 0;';
   if($result=$DB->query($query)) {
      while ($data=$DB->fetch_assoc($result)) {
         $table=plugin_customfields_table($data['device_type']);
         if($table) {
            $query ="DROP TABLE IF EXISTS `$table`;";
            $DB->query($query) or die($DB->error());
         }
      }
   }

   $query = 'DROP TABLE IF EXISTS `glpi_plugin_customfields`;';
   $DB->query($query) or die($DB->error());
   $query = 'DROP TABLE IF EXISTS `glpi_plugin_customfields_fields`;';
   $DB->query($query) or die($DB->error());
   $query = 'DROP TABLE IF EXISTS `glpi_plugin_customfields_dropdowns`;';
   $DB->query($query) or die($DB->error());

   return true;
}

///////////////////// PROFILE FUNCTIONS /////////////////////

function plugin_customfields_createaccess($ID) {
   GLOBAL $DB;
   include_once (GLPI_ROOT."/inc/profile.class.php");

   $Profile=new Profile();
   $Profile->GetfromDB($ID);
   $name=$Profile->fields["name"];

   $query ="INSERT INTO `glpi_plugin_customfields_profiledata` (`ID`, `name`) VALUES ('$ID', '$name');";

   $DB->query($query);
}

function plugin_customfields_changeprofile() {
   $plugin = new Plugin();
   if ($plugin->isActivated("customfields")) {
      $prof=new plugin_customfields_Profile();
      if($prof->getFromDB($_SESSION['glpiactiveprofile']['ID'])) {
         $_SESSION["glpi_plugin_customfields_profile"]=$prof->fields;
      }
      else {
         unset($_SESSION["glpi_plugin_customfields_profile"]);
      }
   }
}

?>
