<?php

/**
 * Returns prepared template for select2
 */
function civicrm_api3_supportcase_manage_case_get_rendered_template($params) {
  try {
    $mailutilsTemplate = \Civi\Api4\MailutilsTemplate::get()
      ->setLimit(0)
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $params['id'])
      ->execute()
      ->first();
  } catch (API_Exception $e) {
    return civicrm_api3_create_success([]);
  }

  if (empty($mailutilsTemplate)) {
    return civicrm_api3_create_success([]);
  }

  $message = CRM_Supportcase_Utils_MailutilsTemplate::prepareToExecuteMessage($mailutilsTemplate['message']);

  return civicrm_api3_create_success([
    'rendered_text' => CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens($message, $params['token_contact_id']),
    'mailutils_template_id' => $mailutilsTemplate['id'],
  ]);
}

function _civicrm_api3_supportcase_manage_case_get_rendered_template_spec(&$params) {
  $params['token_contact_id'] = [
    'name' => 'token_contact_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'token contact id',
  ];
  $params['id'] = [
    'name' => 'id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Mailutils template id',
  ];
}
