<?php

class CRM_Supportcase_Utils_EmailDefaultValues_Manager {

  public static function getPreparedEmailDefaultValues($mode, $caseId, $fromActivityId = null) {
    if ($mode === CRM_Supportcase_Utils_Email::NEW_EMAIL_MODE) {
      $email = new CRM_Supportcase_Utils_EmailDefaultValues_Modes_New($mode, $caseId, null);
    } elseif ($mode === CRM_Supportcase_Utils_Email::REPLY_ALL_MODE) {
      $email = new CRM_Supportcase_Utils_EmailDefaultValues_Modes_ReplyAll($mode, $caseId, $fromActivityId);
    } elseif ($mode === CRM_Supportcase_Utils_Email::REPLY_MODE) {
      $email = new CRM_Supportcase_Utils_EmailDefaultValues_Modes_Reply($mode, $caseId, $fromActivityId);
    } elseif ($mode === CRM_Supportcase_Utils_Email::FORWARD_MODE) {
      $email = new CRM_Supportcase_Utils_EmailDefaultValues_Modes_Forward($mode, $caseId, $fromActivityId);
    } else {
      $email = new CRM_Supportcase_Utils_EmailDefaultValues_Modes_None($mode, $caseId, $fromActivityId);
    }

    $defaultValues = $email->getValues();

    return $defaultValues;
  }

}
