<?php

class CRM_Supportcase_Utils_Group {

  /**
   * Is contact in group
   *
   * @param $contactId
   * @param $groupId
   * @param string $status
   * @return null|string
   */
  public static function isContactInGroup($contactId, $groupId, $status) {
    return !empty(self::findContactGroupId($contactId, $groupId, $status));
  }

  /**
   * Find ContactGroup id entity by params
   *
   * @param $contactId
   * @param $groupId
   * @param string $status
   * @return null|string
   */
  public static function findContactGroupId($contactId, $groupId, $status = null) {
    if (empty($contactId) || empty($groupId)) {
      return false;
    }

    $params = [
      'sequential' => 1,
      'options' => ['limit' => 0],
      'contact_id' => $contactId,
      'group_id' => $groupId,
    ];

    if (!empty($status)) {
      $params['status'] = $status;
    }

    try {
      $groupContact = civicrm_api3('GroupContact', 'get', $params);
    } catch (CiviCRM_API3_Exception $e) {
      return false;
    }

    if (!empty($groupContact['values'])) {
      foreach ($groupContact['values'] as $groupContact) {
        return $groupContact['id'];
      }
    }

    return false;
  }

  /**
   * Adds/removes contact form group
   *
   * @param $contactId
   * @param $groupId
   * @param $isNeedToAddContactToGroup
   */
  public static function updateContactGroup($contactId, $groupId, $isNeedToAddContactToGroup) {
    if (empty($contactId) || empty($groupId) || !isset($isNeedToAddContactToGroup)) {
      return;
    }

    if ($isNeedToAddContactToGroup) {
      if (self::isContactInGroup($contactId, $groupId, 'Added')) {
        return;
      }

      $contactGroupId = self::findContactGroupId($contactId, $groupId);
      if (!empty($contactGroupId)) {
        civicrm_api3('GroupContact', 'create', [
          'id' => $contactGroupId,
          'status' => "Added",
        ]);
      } else {
        civicrm_api3('GroupContact', 'create', [
          'contact_id' => $contactId,
          'group_id' => $groupId,
        ]);
      }
    } else {
      if (self::findContactGroupId($contactId, $groupId, 'Removed')) {
        return;
      }

      $contactGroupId = self::findContactGroupId($contactId, $groupId);
      if (!empty($contactGroupId)) {
        civicrm_api3('GroupContact', 'create', [
          'id' => $contactGroupId,
          'status' => "Removed",
        ]);
      }
    }
  }

}
