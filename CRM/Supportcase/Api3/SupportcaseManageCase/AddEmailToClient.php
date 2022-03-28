<?php

/**
 * Uses on 'SupportcaseManageCase->add_email_to_client' api
 */
class CRM_Supportcase_Api3_SupportcaseManageCase_AddEmailToClient extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    try {
      $createdEmail = civicrm_api3('Email', 'create', [
        'contact_id' => $this->params['client_id'],
        'email' => $this->params['email'],
        'location_type_id' => CRM_Supportcase_Install_Entity_LocationType::SUPPORT,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Creating email error: ' . $e->getMessage(), 'creating_email_error');
    }

    $emails = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($createdEmail['id']);

    return [
      'message' => 'Successfully added email to client.',
      'data' => !empty($emails[0]) ? $emails[0] : [],
    ];
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

    if (!CRM_Supportcase_Utils_Email::isValidEmail($params['email'])) {
      throw new api_Exception('Please enter valid email', 'email_is_not_valid');
    }

    $firstClientId = CRM_Supportcase_Utils_Case::getFirstClient($case);
    if (empty($firstClientId)) {
      throw new api_Exception('Cannot find any client', 'cannot_find_any_client');
    }

    if (CRM_Supportcase_Utils_Email::isContactHasEmail($params['email'], $firstClientId)) {
      throw new api_Exception('This email already added to client', 'cannot_find_any_client');
    }

    return [
      'case' => $case,
      'client_id' => $firstClientId,
      'case_id' => $params['case_id'],
      'email' => $params['email'],
    ];
  }

}
