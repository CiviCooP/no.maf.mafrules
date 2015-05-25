<?php

require_once 'mafrules.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mafrules_civicrm_config(&$config) {
  _mafrules_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mafrules_civicrm_xmlMenu(&$files) {
  _mafrules_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mafrules_civicrm_install() {
  if (_mafrules_civirules_installed() == FALSE) {
    throw new Exception('Extension MAF Norge CiviRules requires extension CiviRules to be installed and enabled.
    Could not install MAF Norge CiviRules because missing CiviRules');
  }
  /*
   * create custom field to check if civirules has processed for contributions. Security measure
   * at Steinar's request, can be removed if he is happy
   */
  _mafrules_create_civirulescheck();
  _mafrules_civix_civicrm_install();

}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mafrules_civicrm_uninstall() {
  _mafrules_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mafrules_civicrm_enable() {
  if (_mafrules_civirules_installed() == FALSE) {
    throw new Exception('Extension MAF Norge CiviRules requires extension CiviRules to be installed and enabled.
    Could not enable MAF Norge CiviRules because missing CiviRules');
  }
  _mafrules_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mafrules_civicrm_disable() {
  _mafrules_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mafrules_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mafrules_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mafrules_civicrm_managed(&$entities) {
  _mafrules_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mafrules_civicrm_caseTypes(&$caseTypes) {
  _mafrules_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mafrules_civicrm_angularModules(&$angularModules) {
_mafrules_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mafrules_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mafrules_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Function to check if CiviRules extension (org.civicoop.civirules) is installed as this
 * is required
 */
function _mafrules_civirules_installed() {
  $installed = FALSE;
  try {
    $extensions = civicrm_api3('Extension', 'get');
    foreach($extensions['values'] as $ext) {
      if ($ext['key'] == 'org.civicoop.civirules' &&$ext['status'] == 'installed') {
        $installed = TRUE;
      }
    }
  } catch (Exception $e) {
    $installed = FALSE;
  }
  return $installed;
}

/**
 * Function to check if custom group for civirules check exists and if it does, return id
 *
 * @param string $customGroupName
 * @return bool|int
 */
function _mafrule_create_civirulescheck($customGroupName) {
  $customGroupName = 'maf_norge_civirules_check';
  $customFieldName = 'send_thank_you_processed';
  try {
    $customGroup = civicrm_api3('CustomGroup', 'Getsingle', array('name' => $customGroupName));
  } catch (CiviCRM_API3_Exception $ex) {
    $createCustomGroupParams = array(
      'name' => $customGroupName,
      'title' => 'MAF Norge CiviRules Checks',
      'extends' => 'Contribution',
      'collapse_display' => 0,
      'table_name' => 'civicrm_value_civirules_check',
      'is_reserved' => 1,
      'is_active' => 1);
    $customGroup = civicrm_api3('CustomGroup', 'Create', $createCustomGroupParams);
  }
  $customFieldParams = array(
    'name' => $customFieldName,
    'custom_group_id' => $customGroup['id']);
  try {
    civicrm_api3('CustomField', 'Getsingle', $customFieldParams);
  } catch (CiviCRM_API3_Exception $ex) {
    $createCustomFieldParams = array(
      'name' => $customFieldName,
      'custom_Group_id' => $customGroup['id'],
      'label' => 'Send Thank You action processed',
      'default_value' => 0,
      'data_type' => 'Boolean',
      'html_type' => 'Radio',
      'is_active' => 1,
      'is_view' => 1,
      'is_searchable' => 1,
      'column_name' => 'send_thank_you_processed');
    civicrm_api3('CustomField', 'Create', $createCustomFieldParams);
  }
  return $customGroup['id'];
}