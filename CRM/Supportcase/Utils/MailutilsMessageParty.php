<?php

use Civi\Api4\MailutilsMessageParty;

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

    $messagePartyQuery = MailutilsMessageParty::get(FALSE)
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

  /**
   * Update all MailutilsMessageParty contact_id values with contact
   * $oldContactId to $newContactId for all MailutilsMessages with the provided
   * $activityIds values.
   *
   * @param array $activityIds
   * @param int $oldContactId
   * @param int $newContactId
   *
   * @throws \API_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public static function updateMessagePartyContactByActivitiesAndContact(
    array $activityIds,
    int $oldContactId,
    int $newContactId
  ) {
    MailutilsMessageParty::update(FALSE)
      ->addWhere('mailutils_message_id.activity_id', 'IN', $activityIds)
      ->addWhere('contact_id', '=', $oldContactId)
      ->addValue('contact_id', $newContactId)
      ->execute();
  }

  /**
   * Get all email addresses used in MailutilsMessageParty given a contact ID
   * and an array of activity IDs
   *
   * @param array $activityIds
   * @param int $oldContactId
   *
   * @return array
   * @throws \API_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public static function getMessagePartyEmailByActivitiesAndContact(
    array $activityIds,
    int $oldContactId
  ): array {
    $mailutilsMessageParties = MailutilsMessageParty::get(FALSE)
      ->addSelect('email')
      ->setGroupBy(['email',])
      ->addWhere('mailutils_message_id.activity_id', 'IN', $activityIds)
      ->addWhere('contact_id', '=', $oldContactId)
      ->execute()
      ->getArrayCopy();
    return array_column($mailutilsMessageParties, 'email');
  }

}
