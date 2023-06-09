
<?php

/**
 * Uses on 'SupportcaseManageCase->get_case_status_warning_window_data' api
 */
class CRM_Supportcase_Api3_SupportcaseManageCase_GetCaseStatusWarningWindowData extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    $returnData = [
      'isAllowToChangeCaseStatus' => true,
      'warningWindow' => [
        'title' => 'Changing case status',
        'type' => 'modal', // modal, inline
        'message' => '',
        'yesButtonText' => 'Continue WITHOUT sending the message',
        'yesButtonClasses' => 'spc__button spc--cancel spc--height-medium',
        'yesButtonIcon' => 'fa-times',
        'noButtonText' => 'Cancel',
        'noButtonClasses' => 'ci__case-info-edit-confirm spc__button spc--height-medium',
        'noButtonIcon' => 'fa-check',
      ],
    ];

    $closedStatuses = CRM_Supportcase_Utils_Case::getCaseClosedStatusesIds();
    $spamStatusId = CRM_Core_PseudoConstant::getKey('CRM_Case_BAO_Case', 'case_status_id', 'spam');
    $newStatusName = CRM_Core_PseudoConstant::getLabel('CRM_Case_BAO_Case', 'status_id', $this->params['new_case_status_id']);

    if (in_array($this->params['new_case_status_id'], $closedStatuses) && $this->params['new_case_status_id'] != $spamStatusId) {
      if (CRM_Supportcase_Utils_Case::isCaseHasDraftEmails($this->params['case_id'])) {
        $returnData['isAllowToChangeCaseStatus'] = false;
        $returnData['warningWindow']['message'] = 'There is an unsent draft in this case.';
      } elseif (CRM_Supportcase_Utils_Case::isCaseHasNotAnsveredEmail($this->params['case_id'])) {
        $returnData['isAllowToChangeCaseStatus'] = false;
        $returnData['warningWindow']['message'] = 'You have not replied to the most recent message.';
        $returnData['warningWindow']['type'] = 'inline';

        if ($this->params['context'] == 'caseStatusDirective') {
          // There not enough space, make text more short:
          $returnData['warningWindow']['yesButtonText'] = 'Change';
        }
      }

      if (!empty($returnData['warningWindow']['message'])) {
        $returnData['warningWindow']['message'] .= ' <br> Are you sure you want to change the case status to "' . $newStatusName . '"?';
      }
    }

    return $returnData;
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
      civicrm_api3('Case', 'getsingle', [
        'id' => $params['case_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

    $context = (!empty($params['context']) && in_array($params['context'], ['managePanelDirective', 'caseStatusDirective'])) ? $params['context'] : '';

    return [
      'context' => $context,
      'new_case_status_id' => $params['new_case_status_id'],
      'case_id' => $params['case_id'],
    ];
  }

}
