<?php

class CRM_Supportcase_Utils_MailutilsMessageParty {

  /**
   * @param $mailutilsMessageId
   * @param $partyTypeId
   * @return array
   */
  public static function getMailutilsMessageParties($mailutilsMessageId, $partyTypeId) {
    if (empty($mailutilsMessageId)) {
      return [];
    }

    try {
      $messageParties = \Civi\Api4\MailutilsMessageParty::get()
        ->addSelect('*')
        ->addWhere('mailutils_message_id', '=', $mailutilsMessageId)
        ->addWhere('party_type_id', '=', $partyTypeId)
        ->execute();
    } catch (Exception $e) {
      return [];
    }

    return $messageParties;
  }

  /**
   * @param $mailutilsMessageId
   * @return array
   */
  public static function getCcEmailIds($mailutilsMessageId) {
    if (empty($mailutilsMessageId)) {
      return [];
    }

    $ccMessageParties = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessageParties($mailutilsMessageId, CRM_Supportcase_Utils_PartyType::getCcPartyTypeId());
    $ccEmails = [];

    foreach ($ccMessageParties as $ccMessageParty) {
      $emailId = CRM_Supportcase_Utils_Email::getEmailId($ccMessageParty['email'], $ccMessageParty['contact_id']);
      if (!empty($emailId)) {
        $ccEmails[] = $emailId;
      }
    }

    return $ccEmails;
  }

}
