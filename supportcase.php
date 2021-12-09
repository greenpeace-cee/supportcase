<?php

require_once 'supportcase.civix.php';

use Civi\Api4\CiviCase;
use CRM_Supportcase_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function supportcase_civicrm_config(&$config) {
  _supportcase_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function supportcase_civicrm_xmlMenu(&$files) {
  _supportcase_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function supportcase_civicrm_install() {
  _supportcase_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function supportcase_civicrm_postInstall() {
  _supportcase_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function supportcase_civicrm_uninstall() {
  _supportcase_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function supportcase_civicrm_enable() {
  _supportcase_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function supportcase_civicrm_disable() {
  _supportcase_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function supportcase_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _supportcase_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function supportcase_civicrm_managed(&$entities) {
  _supportcase_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function supportcase_civicrm_caseTypes(&$caseTypes) {
  _supportcase_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function supportcase_civicrm_angularModules(&$angularModules) {
  _supportcase_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function supportcase_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _supportcase_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function supportcase_civicrm_entityTypes(&$entityTypes) {
  _supportcase_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function supportcase_civicrm_themes(&$themes) {
  _supportcase_civix_civicrm_themes($themes);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
 */
function supportcase_civicrm_navigationMenu(&$menu) {
  _supportcase_civix_insert_navigation_menu($menu, 'Cases', array(
    'label' => E::ts('Support Dashboard'),
    'name' => 'Support_Dashboard',
    'url' => 'civicrm/supportcase',
    'permission' => 'access support cases',
    'operator' => 'OR',
    'separator' => 2,
  ));
  _supportcase_civix_navigationMenu($menu);
}

function supportcase_civicrm_permission(&$permissions) {
  $permissions += [
    'access support cases' => [
      E::ts('CiviCRM: Access Support Cases'),
      E::ts('Access support cases'),
    ],
  ];
}

function supportcase_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if (in_array(strtolower($entity), ['case_lock', 'supportcase_manage_case', 'supportcase_quick_action'])) {
    $permissions[$entity][$action] = ['access support cases'];
  }
}

function supportcase_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values) {
  if ($objectName == 'Case' && $op == 'case.selector.actions') {
    if (empty($objectId) || empty($links[0]['url']) || $links[0]['url'] != 'civicrm/contact/view/case') {
      return;
    }
    $isSupportCase = CiviCase::get(FALSE)
      ->selectRowCount()
      ->addWhere('id', '=', $objectId)
      ->addWhere('case_type_id:name', '=', 'support_case')
      ->execute()
      ->count();
    if ($isSupportCase) {
      $links[0]['url'] = 'civicrm/supportcase/manage-case-angular-wrap';
      $links[0]['qs'] = 'reset=1&case_id=%%id%%';
      $links[0]['target'] = '_blank';
    }
  }
}
