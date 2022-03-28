<?php

/**
 * Returns prepared template for select2
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_get_prepared_mail_template_option($params) {
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
    return civicrm_api3_create_success([
      'rendered_text' => CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens($template['message'], $params['token_contact_id']),
      'mailutils_template_id' => $template['id'],
    ]);
  }

  return civicrm_api3_create_success([]);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_get_prepared_mail_template_option_spec(&$params) {
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
