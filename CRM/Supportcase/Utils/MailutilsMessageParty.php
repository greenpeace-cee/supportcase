<?php

class CRM_Supportcase_Utils_MailutilsMessageParty {

  /**
   * @param $mailutilsMessageId
   * @param $partyTypeId
   * @return array
   */
  public static function getMailutilsMessageParties($mailutilsMessageId, $partyTypeId = null) {
    if (empty($mailutilsMessageId)) {
      return [];
    }

    $messagePartyQuery = \Civi\Api4\MailutilsMessageParty::get(FALSE)
      ->addSelect('*')
      ->addWhere('mailutils_message_id', '=', $mailutilsMessageId);

    if (!empty($partyTypeId)) {
      $messagePartyQuery->addWhere('party_type_id', '=', $partyTypeId);
    }

    try {
      $messageParties = $messagePartyQuery->execute();
    } catch (Exception $e) {
      return [];
    }

    return $messageParties;
  }

}
