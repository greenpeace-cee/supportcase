<?php

/**
 * Returns prepared template for select2
 */
function civicrm_api3_supportcase_manage_case_get_rendered_template($params) {
  try {
    $mailutilsTemplateApi = \Civi\Api4\MailutilsTemplate::get();
    $mailutilsTemplateApi->setLimit(0);
    $mailutilsTemplateApi->setCheckPermissions(FALSE);

    if (!empty($params['id'])) {
      $mailutilsTemplateApi->addWhere('id', '=', $params['id']);
    }

    $mailutilsTemplates = $mailutilsTemplateApi->execute();
  } catch (API_Exception $e) {
    return civicrm_api3_create_success([]);
  }

  if (!empty($mailutilsTemplates['rowCount']) && $mailutilsTemplates['rowCount'] == 0) {
    return civicrm_api3_create_success([]);
  }

  foreach ($mailutilsTemplates as $template) {
    $message = CRM_Supportcase_Utils_MailutilsTemplate::removeSmartyEscapeWords($template['message']);

    return civicrm_api3_create_success([
      'rendered_text' => CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens($message, $params['token_contact_id']),
      'mailutils_template_id' => $template['id'],
    ]);
  }

  return civicrm_api3_create_success([]);
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
