<?php

class CRM_Supportcase_Utils_CaseManager {

  /**
   * Get case manger contact ids
   *
   * @param int $caseId
   *
   * @return array
   */
  public static function getCaseManagerContactIds($caseId) {
    $managersIds = [];

    if (CRM_Supportcase_Utils_Setting::isCaseToolsExtensionEnable()) {
      try {
        $caseManagers = civicrm_api3('CaseTools', 'get_case_managers', [
          'case_id' => $caseId,
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        return [];
      }

      if (!empty($caseManagers['values']['manager_ids'])) {
        $managersIds = $caseManagers['values']['manager_ids'];
      }
    }

    return $managersIds;
  }



}
