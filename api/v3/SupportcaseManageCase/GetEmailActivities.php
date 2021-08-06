<?php

/**
 * Get activities(type = email) related to the case
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_get_email_activities($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  try {
    $activities = civicrm_api3('Activity', 'get', [
      'is_deleted' => '0',
      'options' => ['limit' => 0],//TODO: check if needs limit 0
      'case_id' => $params['case_id'],
      'activity_type_id' => ['IN' => ['Email', 'Inbound Email']],
      'api.Attachment.get' => [],
      'return' => ['target_contact_id', 'source_contact_id', 'activity_type_id', 'activity_date_time', 'subject', 'details'],
      'options' => ['sort' => 'activity_date_time DESC'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    $activities = [];
  }

  $preparedActivities = [];
  if (!empty($activities['values'])) {
    foreach ($activities['values'] as $activity) {
      $from = civicrm_api3('Contact', 'getsingle', [
        'id' => $activity['source_contact_id'],
        'return' => ['id', 'email', 'display_name'],
      ]);

      try {
        $to = civicrm_api3('Contact', 'getsingle', [
          'id' => $activity['target_contact_id'][0],
          'return' => ['id', 'email', 'display_name'],
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        $to = [
          'id' => null,
          'display_name' => null,
          'email' => null,
        ];
      }

      $preparedActivity = [
        'id' => $activity['id'],
        'activity_type_id' => $activity['activity_type_id'],
        'from_contact_id' => $from['id'],
        'from_name' => $from['display_name'],
        'from_email' => $from['email'],
        'to_contact_id' => $to['id'],
        'to_name' => $to['display_name'],
        'to_email' => $to['email'],
        'subject' => $activity['subject'],
        'activity_date_time' => $activity['activity_date_time'],
        'details' => trim(CRM_Utils_String::stripAlternatives($activity['details'])),
        'reply' => _civicrm_api3_supportcase_manage_case_get_email_activities_format_quote($activity, $from),
        'attachments' => [],
      ];
      foreach ($activity['api.Attachment.get']['values'] as $attachment) {
        $preparedActivity['attachments'][] = [
          'name' => $attachment['name'],
          'icon' => $attachment['icon'],
          'url' => $attachment['url'],
        ];
      }
      $preparedActivities[] = $preparedActivity;
    }
  }

  return civicrm_api3_create_success($preparedActivities);
}

function _civicrm_api3_supportcase_manage_case_get_email_activities_format_quote($activity, $from) {
  $date = CRM_Utils_Date::customFormat($activity['activity_date_time']);
  $signature = "\n\nMit freundlichen Grüßen<hr>Greenpeace in Zentral- und Osteuropa\nWiedner Hauptstraße 120-124, 1050 Wien\nTelefon: +43 (0)1 545 45 80\nSpendenkonto: IBAN AT24 20111 82221219800";
  $message = "{$signature}\n\nOn {$date} {$from['display_name']} wrote:";
  $quote = trim(CRM_Utils_String::stripAlternatives($activity['details']));
  $quote = str_replace("\r", "", $quote);
  $quote = str_replace("\n", "\n> ", $quote);
  $quote = str_replace('> >', '>>', $quote);
  $message = $message . "\n> " . $quote;
  return nl2br($message);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_get_email_activities_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
