<?php

class CRM_Supportcase_Utils_EmailDefaultValues_Modes_New extends CRM_Supportcase_Utils_EmailDefaultValues_Modes_Base {

  public function getValues() {
    $defaultValues = $this->getDefaultFields();

    $case = $this->getCase();
    $firstClientId = CRM_Supportcase_Utils_Case::getFirstClient($case);

    $defaultValues['case_category_id'] = $case['case_category_id'];
    $defaultValues['token_contact_id'] = $firstClientId;
    $defaultValues['subject'] = $case['subject'];

    $mailUtilsSetting = $this->getFirstRelatedMailutilsSetting($case['case_category_id']);
    if (!empty($mailUtilsSetting)) {
      $emailBody = $this->getBodyForNewEmail($mailUtilsSetting, $firstClientId);

      $mainEmailId = CRM_Supportcase_Utils_Activity::getMainEmailIdByFromEmailAddressId($mailUtilsSetting['from_email_address_id']);
      if (!empty($mainEmailId)) {
        $defaultValues['fromEmails'] = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($mainEmailId);
        $emailBody = CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens($emailBody, $firstClientId);
      }

      $defaultValues['body'] = json_encode(['html' => $emailBody, 'text' => CRM_Utils_String::htmlToText($emailBody)]);
    }

    if (!empty($firstClientId)) {
      $toEmailsData = CRM_Supportcase_Utils_EmailSearch::getEmailsDataByContactId($firstClientId);
      if (!empty($toEmailsData[0])) {
        $defaultValues['toEmails'] = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($toEmailsData[0]['id']);
      }
    }

    // fix when cannot find from emails
    if (empty($defaultValues['fromEmails'])) {
      $currentContactId = CRM_Core_Session::getLoggedInContactID();
      $emailsData = CRM_Supportcase_Utils_EmailSearch::getEmailsDataByContactId($currentContactId);
      if (!empty($emailsData[0])) {
        $defaultValues['fromEmails'] = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($emailsData[0]['id']);
      }
    }

    return $defaultValues;
  }

  /**
   * @return string
   */
  protected function getBodyForNewEmail($mailUtilsSetting, $toContactId) {
    $renderedTemplate = CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens(
      $this->getRenderedTemplate($mailUtilsSetting),
      $toContactId
    );
    $message = "{$renderedTemplate}\n\n";

    return nl2br($message);
  }

  /**
   * @param $mailUtilsSetting
   * @return string
   */
  protected function getRenderedTemplate($mailUtilsSetting) {
    if (empty($mailUtilsSetting['mailutils_template_id'])) {
      return '';
    }

    $mailutilsTemplate = \Civi\Api4\MailutilsTemplate::get(FALSE)
      ->addWhere('id', '=', $mailUtilsSetting['mailutils_template_id'])
      ->setLimit(1)
      ->execute()
      ->first();

    if (empty($mailutilsTemplate)) {
      return '';
    }

    $message = str_replace(["\r", "\n"], '', $mailutilsTemplate['message']);

    return CRM_Supportcase_Utils_MailutilsTemplate::prepareToExecuteMessage($message);
  }

}
