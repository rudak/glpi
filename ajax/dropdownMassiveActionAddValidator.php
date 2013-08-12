<?php
/*
 * @version $Id: dropdownMassiveActionAddValidator.php 21251 2013-07-24 09:21:49Z ludo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

// TODO : check its validy regarding new MassiveAction system

if (isset($_POST["validatortype"])) {
   switch($_POST["validatortype"]){
      case 'user': 
         echo "<input type='hidden' name='groups_id' value=0 />";
         User::dropdown(array('name'   => 'users_id_validate',
                              'entity' => $_SESSION["glpiactive_entity"],
                              'right'  => array('validate_request', 'validate_incident')));
         
         echo "<br><br>".__('Comments')." ";
         echo "<textarea name='comment_submission' cols='50' rows='6'></textarea>&nbsp;";

         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         break;
      
      case 'group':
         echo "<input type='hidden' name='users_id_validate' value=0 />";
         $condition = "(SELECT count(`users_id`) FROM `glpi_groups_users` WHERE `groups_id` = `glpi_groups`.`id`)";
         $rand = Group::dropdown(array('name'      => 'groups_id',
                                       'condition' => $condition,
                                       'entity'    => $_SESSION["glpiactive_entity"],
                                       'right'     => array('validate_request', 'validate_incident')));
         
         $param = array('validatortype'      => 'group_user', 
                        'groups_id' =>'__VALUE__');
         
         Ajax::updateItemOnSelectEvent("dropdown_groups_id$rand", "show_groups_users",
                              $CFG_GLPI["root_doc"]."/ajax/dropdownMassiveActionAddValidator.php",
                              $param);
         
         echo "<br><span id='show_groups_users'>&nbsp;</span>\n";
         break;
      
      case 'group_user':
         $groups_users = Group_user::getGroupUsers($_POST['groups_id']);
         $users = array();
         $param['values'] =  array();
         foreach($groups_users as $data){
            $users[$data['id']] = formatUserName($data['id'], 
                                                 $data['name'], 
                                                 $data['realname'], 
                                                 $data['firstname']);
         }
         
         if(isset($_POST['all_users']) && $_POST['all_users']) {
            $param['values'] =  array_keys($users);
         }

         $param['multiple']= true;
         $param['display'] = true;
         $param['size']    = count($users);

         Dropdown::showFromArray("users_id_validate", $users, $param);
         
          // Display all/none buttons to select all or no users in group
         if(!empty($_POST['groups_id'])){
            echo "<a id='all_users' class='vsubmit'>".__('All')."</a>";
            $param_button['validatortype']     = 'group_user';
            $param_button['users_id_validate'] = '';
            $param_button['all_users']         = 1;
            $param_button['groups_id']         = $_POST['groups_id'];
            Ajax::updateItemOnEvent('all_users', 'show_groups_users', $CFG_GLPI["root_doc"]."/ajax/dropdownMassiveActionAddValidator.php", $param_button, array('click'));
            
            echo "&nbsp;<a id='no_users' class='vsubmit'>".__('None')."</a>";;
            $param_button['all_users'] = 0;
            Ajax::updateItemOnEvent('no_users', 'show_groups_users', $CFG_GLPI["root_doc"]."/ajax/dropdownMassiveActionAddValidator.php", $param_button, array('click'));
         }
         
         echo "<br><br>&nbsp;".__('Minimum validation required')."&nbsp;";
         TicketValidation_User::dropdownValidationRequired(array('value' => '__VALUE__',
                                                                  'name' => 'validation_percent'));
         
         echo "<br><br>".__('Comments')." ";
         echo "<textarea name='comment_submission' cols='50' rows='6'></textarea>&nbsp;";

         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         break;
   }
   


}
?>