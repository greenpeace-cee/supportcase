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
   * Gets prepared emails data by mailutils message id
   *
   * @param $mailutilsMessageId
   * @param $partyTypeId
   * @return array
   */
  public static function getMailutilsMessagePartiesEmailsData($mailutilsMessageId, $partyTypeId = null) {
    return self::prepareEmailsData(self::getMailutilsMessageParties($mailutilsMessageId, $partyTypeId));
  }

  /**
   * Prepares emails data by list of MailutilsMessageParty entity
   *
   * @param $messageParties
   * @return array
   */
  public static function prepareEmailsData($messageParties) {
    $emailLabels = [];
    $emailIds = [];
    $emailsData = [];
    foreach ($messageParties as $messageParty) {
      $emailData = CRM_Supportcase_Utils_Email::getEmailContactData($messageParty['email'], $messageParty['contact_id']);
      if (empty($emailData)) {
        continue;
      }

      $icon = '';
      if ($emailData['contact_type'] == 'Individual') {
        $icon = 'com--individual-icon';
      } elseif ($emailData['contact_type'] == 'Organization') {
        $icon = 'com--organization-icon';
      } elseif ($emailData['contact_type'] == 'Household') {
        $icon = 'com--household-icon';
      }

      $emailLabel = CRM_Supportcase_Utils_EmailSearch::prepareEmailLabel($emailData['contact_display_name'], $emailData['email']);
      $emailLabels[] = $emailLabel;
      $emailIds[] = $emailData['id'];
      $emailsData[] = [
        'id' => $emailData['id'],
        'label' => $emailLabel,
        'contact_id' => $emailData['contact_id'],
        'contact_type' => $emailData['contact_type'],
        'contact_display_name' => $emailData['contact_display_name'],
        'email' => $emailData['email'],
        'contact_link' => CRM_Utils_System::url('civicrm/contact/view/', [
          'reset' => '1',
          'cid' => $emailData['contact_id'],
        ]),
        'icon' => $icon,
      ];
    }

    return [
      'email_labels' => $emailLabels,
      'coma_separated_email_labels' => implode(', ', $emailLabels),
      'email_ids' => $emailIds,
      'coma_separated_email_ids' => implode(',', $emailIds),
      'emails_data' => $emailsData,
    ];
  }

  /**
   * Get all emails data by mailUtilsMessageId -> MailutilsMessageParties
   *
   * @param $mailUtilsMessageId
   * @return array
   */
  public static function getEmailsData($mailUtilsMessageId): array {
    $ccPartyTypeId = CRM_Supportcase_Utils_PartyType::getCcPartyTypeId();
    $toPartyTypeId = CRM_Supportcase_Utils_PartyType::getToPartyTypeId();
    $fromPartyTypeId = CRM_Supportcase_Utils_PartyType::getFromPartyTypeId();

    return [
      'to' => CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessageId, $toPartyTypeId),
      'from' => CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessageId, $fromPartyTypeId),
      'cc' => CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessageId, $ccPartyTypeId),
    ];
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
