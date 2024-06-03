<?php

require_once 'supportcase.civix.php';

use Civi\Api4\CiviCase;
use CRM_Supportcase_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function supportcase_civicrm_config(&$config) {
  _supportcase_civix_civicrm_config($config);
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
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function supportcase_civicrm_enable() {
  _supportcase_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
 */
function supportcase_civicrm_navigationMenu(&$menu) {
  _supportcase_civix_insert_navigation_menu($menu, NULL, array(
    'label' => E::ts('Support Dashboard'),
    'icon' => 'crm-i fa-medkit',
    'name' => 'Support_Dashboard',
    'url' => 'civicrm/supportcase',
    'permission' => 'access support cases',
    'operator' => 'OR',
    'separator' => 0,
    'weight' => 70,
  ));
  _supportcase_civix_navigationMenu($menu);

  _supportcase_civix_insert_navigation_menu($menu, 'Cases', array(
    'label' => E::ts('Add Support Case'),
    'name' => 'Support_Dashboard',
    'url' => 'civicrm/supportcase/add-case',
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

/**
 * Add token services to the container.
 *
 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
 */
function  supportcase_civicrm_container(ContainerBuilder $container) {
  $container->addResource(new FileResource(__FILE__));
  $container->findDefinition('dispatcher')->addMethodCall('addListener',
    ['civi.token.list', ['\Civi\Supportcase\Hook\RegisterTokens', 'run'], -100]
  )->setPublic(TRUE);
  $container->findDefinition('dispatcher')->addMethodCall('addListener',
    ['civi.token.eval', ['\Civi\Supportcase\Hook\EvaluateTokens', 'run'], -100]
  )->setPublic(TRUE);
}

function supportcase_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if (in_array(strtolower($entity), ['case_lock', 'supportcase_manage_case', 'supportcase_quick_action', 'supportcase_email', 'supportcase_comment', 'supportcase_fast_task', 'supportcase_draft_email'])) {
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
      ->addWhere('case_type_id:name', '=', CRM_Supportcase_Install_Entity_CaseType::SUPPORT_CASE)
      ->execute()
      ->count();
    if ($isSupportCase) {
      $links[0]['url'] = 'civicrm/a/#/supportcase/manage-case/%%id%%';
      $links[0]['qs'] = 'reset=1';
      $links[0]['target'] = '_blank';
    }
  }
}

function supportcase_civicrm_preProcess($formName, &$form) {
  if ($formName == CRM_Contact_Form_Search_Builder::class) {
    $spcIsUsePrefillVal = CRM_Utils_Request::retrieve('spc_is_use_prefill_val', 'Integer');
    $contactIds = CRM_Utils_Request::retrieve('c_ids', 'String');
    $isReset = CRM_Utils_Request::retrieve('reset', 'Integer');

    if (empty($spcIsUsePrefillVal) || empty($contactIds) || empty($isReset)) {
      return;
    }

    $rowNumber = 0;
    $contactWhereIndex = 1;

    $form->_submitValues = [
      'mapper' => [$contactWhereIndex => [
        $rowNumber => [
          0 => 'Contact',
          1 => 'id',
        ]
      ]
      ],
      'operator' => [
        $contactWhereIndex => [
          $rowNumber => 'IN'
        ]
      ],
      'value' => [
        $contactWhereIndex => [
          $rowNumber => $contactIds
        ]
      ],
    ];

    // hack to submit form after loading:
    $script = 'CRM.$(function ($) { $("select#mapper_1_1_0").closest(".form-item").empty(); $("form.CRM_Contact_Form_Search_Builder").submit();});';
    CRM_Core_Resources::singleton()->addScript($script, 10000, 'page-footer');
  }
}
