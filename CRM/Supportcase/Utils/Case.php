<?php

class CRM_Supportcase_Utils_Case {

  /**
   * Finds in activities phone number
   *
   * @param $caseId
   * @return string
   */
  public static function findPhoneNumberInActivities($caseId) {
    try {
      $activities = civicrm_api3('Activity', 'get', [
        'sequential' => 1,
        'return' => ["phone_number"],
        'case_id' => $caseId,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return '';
    }

    $phoneNumber = '';
    if (!empty($activities['values'])) {
      foreach ($activities['values'] as $activity) {
        if (!empty($activity["phone_number"])) {
          $phoneNumber = $activity["phone_number"];
          break;
        }
      }
    }

    return $phoneNumber;
  }

  /**
   * @todo HACK
   * @param $caseId
   */
  public static function findEmailInActivities($caseId) {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $caseId,
    ]);
    $firstClient = reset($case['client_id']);
    return civicrm_api3('Contact', 'getvalue', [
      'return' => 'email',
      'id' => $firstClient,
    ]);
  }

  /**
   * Gets first client id by case data from api
   *
   * @return int|string
   */
  public static function getFirstClient($apiCaseResult) {
    if (!empty($apiCaseResult['contacts'])) {
      foreach ($apiCaseResult['contacts'] as $contact) {
        if ($contact['role'] == 'Client') {
          return $contact['contact_id'];
        }
      }
    }

    return '';
  }

  /**
   * Get most recent communication by case id
   * (it is activity)
   *
   * @param $caseId
   * @return array
   */
  public static function getRecentCommunication($caseId) {
    $recentCommunication = [
      'activity_id' => '',
      'activity_date_time' => '',
      'activity_details' => '',
      'activity_type_label' => '',
    ];

    try {
      $recentActivity = civicrm_api3('Activity', 'get', [
        'case_id' => $caseId,
        'activity_type_id' => ['IN' => CRM_Supportcase_Utils_Setting::get('supportcase_available_activity_type_names')],
        'is_deleted' => "0",
        'sequential' => 1,
        'return' => [
          'id',
          'subject',
          'details',
          'activity_date_time',
          'activity_type_id',
          'activity_type_id.label'
        ],
        'options' => [
          'sort' => "activity_date_time DESC",
          'limit' => 1
        ],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $recentCommunication;
    }

    if (!empty($recentActivity['values'][0])) {
      $recentCommunication['activity_id'] = $recentActivity['values'][0]['id'];
      $recentCommunication['activity_date_time'] = $recentActivity['values'][0]['activity_date_time'];
      $recentCommunication['activity_details'] = trim(CRM_Utils_String::stripAlternatives($recentActivity['values'][0]['details'] ?? NULL));
      $recentCommunication['activity_type_label'] = $recentActivity['values'][0]['activity_type_id.label'];
    }

    return $recentCommunication;
  }

  /**
   * Finds case manager ids in case data from api 3
   *
   * @param $caseDataFromApi3
   * @return array
   */
  public static function findManagersIds($caseDataFromApi3) {
    $managerIds = [];

    if (empty($caseDataFromApi3['contacts'])) {
      return $managerIds;
    }

    foreach ($caseDataFromApi3['contacts'] as $contact) {
      if ($contact['role'] == 'Case Coordinator is') {
        $managerIds[] = $contact['contact_id'];
      }
    }

    return $managerIds;
  }

  /**
   * Finds case clients ids in case data from api 3
   *
   * @param $caseDataFromApi3
   * @return array
   */
  public static function findClientsIds($caseDataFromApi3) {
    $clientIds = [];

    if (empty($caseDataFromApi3['contacts'])) {
      return $clientIds;
    }

    foreach ($caseDataFromApi3['contacts'] as $contact) {
      if ($contact['role'] == 'Client') {
        $clientIds[] = $contact['contact_id'];
      }
    }

    return $clientIds;
  }

}
