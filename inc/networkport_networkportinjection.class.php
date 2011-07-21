<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginDatainjectionNetworkport_NetworkPortInjection extends NetworkPort_NetworkPort
                                              implements PluginDatainjectionInjectionInterface {


   function __construct() {
      $this->table = getTableForItemType(get_parent_class($this));
   }

   static function getTypeName() {
      global $LANG;

      return $LANG['datainjection']['port'][1];
   }


   function isPrimaryType() {
      return false;
   }


   function connectedTo() {
      return array('Computer', 'NetworkEquipment', 'Peripheral', 'Phone', 'Printer');
   }


   function getOptions($primary_type = '') {
      global $LANG;

      //To manage vlans : relies on a CommonDBRelation object !
      $tab[1]['name']          = $LANG['setup'][90];
      $tab[1]['field']         = 'itemtype';
      $tab[1]['table']         = getTableForItemType('Vlan');
      $tab[1]['linkfield']     = getForeignKeyFieldForTable($tab[100]['table']);
      $tab[100]['displaytype']   = 'relation';
      $tab[100]['relationclass'] = 'NetworkPort_Vlan';
      $tab[100]['storevaluein']  = $tab[100]['linkfield'];

      $tab[4]['checktype'] = 'mac';
      $tab[5]['checktype'] = 'ip';
      $tab[6]['checktype'] = 'ip';
      $tab[7]['checktype'] = 'ip';
      $tab[8]['checktype'] = 'ip';

      return $tab;
   }


   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param values fields to add into glpi
    * @param options options used during creation
    *
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
   **/
   function addOrUpdateObject($values=array(), $options=array()) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }


   function checkPresent($fields_toinject=array(), $options=array()) {
      return $this->getUnicityRequest($fields_toinject['NetworkPort'], $options['checks']);
   }


   function checkParameters ($fields_toinject, $options) {

      $fields_tocheck = array();
      switch ($options['checks']['port_unicity']) {
         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_LOGICAL_NUMBER :
            $fields_tocheck = array('logical_number');
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_LOGICAL_NUMBER_MAC :
            $fields_tocheck = array('logical_number', 'mac');
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_LOGICAL_NUMBER_NAME :
            $fields_tocheck = array('logical_number', 'name');
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_LOGICAL_NUMBER_NAME_MAC :
            $fields_tocheck = array('logical_number', 'mac', 'name');
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_MACADDRESS :
            $fields_tocheck = array('mac');
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_NAME :
            $fields_tocheck = array('name');
            break;
      }

      $check_status = true;
      foreach ($fields_tocheck as $field) {
         if (!isset($fields_toinject[$field])
             || $fields_toinject[$field] == PluginDatainjectionCommonInjectionLib::EMPTY_VALUE) {
            $check_status = false;
         }
      }

      return $check_status;
   }


      /**
    * Build where sql request to look for a network port
    *
    * @param model the model
    * @param fields the fields to insert into DB
    *
    * @return the sql where clause
   **/
   function getUnicityRequest($fields_toinject=array(), $options=array()) {

      $where = "";

      switch ($options['port_unicity']) {
         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_LOGICAL_NUMBER :
            $where .= " AND `logical_number` = '".(isset ($fields_toinject["logical_number"])
                                                   ? $fields_toinject["logical_number"] : '')."'";
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_LOGICAL_NUMBER_MAC :
            $where .= " AND `logical_number` = '".(isset ($fields_toinject["logical_number"])
                                                   ? $fields_toinject["logical_number"] : '')."'
                        AND `name` = '".(isset ($fields_toinject["name"])
                                         ? $fields_toinject["name"] : '')."'
                        AND `mac` = '".(isset ($fields_toinject["mac"])
                                        ? $fields_toinject["mac"] : '')."'";
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_LOGICAL_NUMBER_NAME :
            $where .= " AND `logical_number` = '".(isset ($fields_toinject["logical_number"])
                                                   ? $fields_toinject["logical_number"] : '')."'
                        AND `name` = '".(isset ($fields_toinject["name"])
                                         ? $fields_toinject["name"] : '')."'";
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_LOGICAL_NUMBER_NAME_MAC :
            $where .= " AND `logical_number` = '".(isset ($fields_toinject["logical_number"])
                                                   ? $fields_toinject["logical_number"] : '')."'
                        AND `name` = '".(isset ($fields_toinject["name"])
                                         ? $fields_toinject["name"] : '')."'
                        AND `mac` = '".(isset ($fields_toinject["mac"])
                                        ? $fields_toinject["mac"] : '')."'";
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_MACADDRESS :
            $where .= " AND `mac` = '".(isset ($fields_toinject["mac"])
                                        ? $fields_toinject["mac"] : '')."'";
            break;

         case PluginDatainjectionCommonInjectionLib::UNICITY_NETPORT_NAME :
            $where .= " AND `name` = '".(isset ($fields_toinject["name"])
                                         ? $fields_toinject["name"] : '')."'";
            break;
      }

      return $where;
   }

   /**
    * Check if at least mac or ip is defined otherwise block import
    * @param values the values to inject
    * @return true if check ok, false if not ok
    */
   function lastCheck($values = array()) {

      if ((!isset($values['NetworkPort']['name']) || empty($values['NetworkPort']['name']))
          && (!isset($values['NetworkPort']['mac']) || empty($values['NetworkPort']['mac']))
          && (!isset($values['NetworkPort']['ip']) || empty($values['NetworkPort']['ip']))) {
         return false;
      }
      return true;
   }
}

?>