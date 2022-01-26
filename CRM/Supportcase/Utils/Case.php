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

}
