<?php

/**
 * Returns prepared templates for select2
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_get_prepared_mail_template_options($params) {
  try {
    $mailutilsTemplateApi = \Civi\Api4\MailutilsTemplate::get();
    $mailutilsTemplateApi->setLimit(0);
    $mailutilsTemplateApi->setCheckPermissions(FALSE);
    if (!empty($params['support_case_category_id'])) {
      $mailutilsTemplateApi->addClause('OR', ['support_case_category_id', '=', $params['support_case_category_id']], ['support_case_category_id', 'IS NULL']);
    }

    $mailutilsTemplates = $mailutilsTemplateApi->execute();
  } catch (API_Exception $e) {
    return civicrm_api3_create_success([]);
  }

  if (!empty($mailutilsTemplates['rowCount']) && $mailutilsTemplates['rowCount'] == 0) {
    return civicrm_api3_create_success([]);
  }

  $result = [];
  $preparedTemplates = [];

  foreach ($mailutilsTemplates as $template) {
    $categoryId = $template['template_category_id'];
    if (empty($preparedTemplates[$categoryId])) {
      $preparedTemplates[$categoryId] = [
        'text' => CRM_Supportcase_Utils_OptionValue::getLabelByValue($categoryId, 'mailutils_template_category'),
        'children' => [],
      ];
    }

    $message = $template['message'];
    if (!empty($params['is_need_to_handle_tokens']) && $params['is_need_to_handle_tokens'] == '1' && !empty($params['token_contact_id'])) {
      $message = CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens($message, $params['token_contact_id']);
    }
    
    $preparedTemplates[$categoryId]['children'][] = [
      'id' => $message,
      'text' => $template['name'],
      'mailutils_template_id' => $template['id'],
    ];
  }

  foreach ($preparedTemplates as $preparedTemplate) {
    $result[] = $preparedTemplate;
  }

  return civicrm_api3_create_success($result);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_get_prepared_mail_template_options_spec(&$params) {
  $params['support_case_category_id'] = [
    'name' => 'support_case_category_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Support case category id',
  ];
  $params['token_contact_id'] = [
    'name' => 'token_contact_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'token contact id',
  ];
  $params['is_need_to_handle_tokens'] = [
    'name' => 'is_need_to_handle_tokens',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Is need to handle tokens',
  ];
}
