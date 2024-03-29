<?php

class CRM_Supportcase_Utils_MailutilsMessage {

  /**
   * @param $mailSettingId
   * @return array|null
   */
  public static function getRelatedMailUtilsSetting($mailSettingId) {
    if (empty($mailSettingId)) {
      return null;
    }

    $mailutilsSettings = \Civi\Api4\MailutilsSetting::get(FALSE)
      ->addWhere('mail_setting_id', '=', $mailSettingId)
      ->setLimit(1)
      ->execute();
    foreach ($mailutilsSettings as $mailutilsSetting) {
      return $mailutilsSetting;
    }

    return null;
  }

  /**
   * @param $mailutilsMessageId
   * @return array|null
   */
  public static function getMailutilsMessageById($mailutilsMessageId) {
    if (empty($mailutilsMessageId)) {
      return null;
    }

    $mailutilsMessages = \Civi\Api4\MailutilsMessage::get(FALSE)
      ->addWhere('id', '=', $mailutilsMessageId)
      ->setLimit(1)
      ->execute();

    return $mailutilsMessages->first();
  }

  /**
   * @param $mailutilsMessageId
   * @param $partyTypeId
   * @return void
   */
  public static function removeMessageParties($mailutilsMessageId, $partyTypeId) {
    if (empty($mailutilsMessageId) || empty($partyTypeId)) {
      return;
    }

    $results = \Civi\Api4\MailutilsMessageParty::delete(FALSE)
      ->addWhere('mailutils_message_id', '=', $mailutilsMessageId)
      ->addWhere('party_type_id', '=', $partyTypeId)
      ->execute();
  }

  /**
   * @param $mailutilsMessageId
   * @param $commaSeparatedEmailIds
   * @param $partyType
   * @return void
   */
  public static function updateMessagePartyContactIds($mailutilsMessageId, $commaSeparatedEmailIds, $partyType) {
    if (empty($mailutilsMessageId) || empty($partyType)) {
      return;
    }

    if (!CRM_Supportcase_Utils_PartyType::isExistPartyType($partyType)) {
      return;
    }

    $partyTypeId = CRM_Supportcase_Utils_PartyType::getPartyTypeIdByName($partyType);
    if (!$partyTypeId) {
      return;
    }

    $emails = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($commaSeparatedEmailIds);

    CRM_Supportcase_Utils_MailutilsMessage::removeMessageParties($mailutilsMessageId, $partyTypeId);

    foreach ($emails as $email) {
      CRM_Supportcase_Utils_MailutilsMessage::createMailutilsMessageParty($email, $mailutilsMessageId, $partyTypeId);
    }
  }

  /**
   * Creates MailutilsMessageParty
   *
   * @return int|false
   */
  public static function createMailutilsMessageParty($emailData, $mailutilsMessageId, $partyTypeId) {
    try {
      $messageParty = \Civi\Api4\MailutilsMessageParty::create(FALSE)
        ->addValue('mailutils_message_id', $mailutilsMessageId)
        ->addValue('contact_id', $emailData['contact_id'])
        ->addValue('party_type_id', $partyTypeId)
        ->addValue('name', $emailData['contact_custom_display_name'])
        ->addValue('email', $emailData['email'])
        ->execute()
        ->first();
    } catch (Exception $e) {
      return false;
    }

    return $messageParty;
  }

  /**
   * @param $mailutilsThreadId
   * @param $emailMessageId
   * @return string
   */
  public static function generateHeaders($mailutilsThreadId, $emailMessageId) {
    $referenceList = [$emailMessageId];
    if (empty($mailutilsThreadId)) {
      return json_encode([
        "References" => '<' . implode('> <', $referenceList) . '>',
      ]);
    }

    $mailutilsMessages = \Civi\Api4\MailutilsMessage::get(FALSE)
      ->addSelect('*')
      ->addWhere('mailutils_thread_id', '=', $mailutilsThreadId)
      ->execute();
    foreach ($mailutilsMessages as $item) {
      $referenceList[] = $item['message_id'];
    }

    return json_encode([
      "References" => '<' . implode('> <', $referenceList) . '>',
    ]);
  }

  /**
   * @param $email
   * @return string
   */
  public static function generateMessageId($email) {
    return \ezcMailTools::generateMessageId($email);
  }

}
