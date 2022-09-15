<?php

/**
 * Uses on 'SupportcaseManageCase->is_need_to_show_confirmation_message' api
 */
class CRM_Supportcase_Api3_SupportcaseManageCase_IsNeedToShowConfirmationMessage extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    $result = [
      'isNeedToShowConfirmationMessage' => true,
      'confirmationMessage' => 'Are you sure to change case client?',
    ];

    $isCurrentClientHasLastAndFirstName = !empty($this->params['current_case_client']['last_name']) && !empty($this->params['current_case_client']['first_name']);
    $isNewClientHasLastAndFirstName = !empty($this->params['new_case_client']['last_name']) && !empty($this->params['new_case_client']['first_name']);

    if ($isCurrentClientHasLastAndFirstName && $isNewClientHasLastAndFirstName) {
      if ($this->params['new_case_client']['last_name'] == $this->params['current_case_client']['last_name']
        && $this->params['new_case_client']['first_name'] == $this->params['current_case_client']['first_name']) {
        $result['isNeedToShowConfirmationMessage'] = false;
        $result['confirmationMessage'] = '';
      }
    }

    return $result;
  }


  /**
   * Returns validated params
   *
   * @param $params
   *
   * @return array
   */
  protected function prepareParams($params) {
    try {
      $case = civicrm_api3('Case', 'getsingle', [
        'id' => $params['case_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

    try {
      $newClient = civicrm_api3('Contact', 'getsingle', [
        'id' => $params['new_case_client_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('New client id does not exist', 'client_id_does_not_exist');
    }

    $currentClientId = '';
    foreach ($case['client_id'] as $clientId) {
      $currentClientId = $clientId;
    }

    if (empty($currentClientId)) {
      throw new api_Exception('Current client id does not exist', 'current_client_id_does_not_exist');
    }

    try {
      $currentClient = civicrm_api3('Contact', 'getsingle', [
        'id' => $currentClientId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Current client does not exist', 'client_does_not_exist');
    }

    return [
      'case' => $case,
      'new_case_client' => $newClient,
      'current_case_client' => $currentClient,
    ];
  }

}
