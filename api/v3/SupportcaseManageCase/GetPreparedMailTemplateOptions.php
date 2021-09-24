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
    $mailutilsTemplates = civicrm_api4('MailutilsTemplate', 'get', []);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('MailutilsTemplate returns error: ' . $e->getMessage(), 'mailutils_template_error');
  }

  if (!empty($mailutilsTemplates['rowCount']) && $mailutilsTemplates['rowCount'] == 0) {
    return [];
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

    $preparedTemplates[$categoryId]['children'][] = [
      'id' => $template['message'],
      'text' => $template['name'],
    ];
  }

  foreach ($preparedTemplates as $preparedTemplate) {
    $result[] = $preparedTemplate;
  }

  return civicrm_api3_create_success($result);
}
