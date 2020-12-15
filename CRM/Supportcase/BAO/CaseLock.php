<?php

class CRM_Supportcase_BAO_CaseLock extends CRM_Supportcase_DAO_CaseLock {

  /**
   * Creates new row
   *
   * @param $params
   *
   * @return \CRM_Core_DAO
   */
  public static function &create(&$params) {
    $transaction = new self();

    if (!empty($params['id'])) {
      CRM_Utils_Hook::pre('edit', self::getEntityName(), $params['id'], $params);
    }
    else {
      CRM_Utils_Hook::pre('create', self::getEntityName(), NULL, $params);
    }

    $entityData = self::add($params);

    if (is_a($entityData, 'CRM_Core_Error')) {
      $transaction->rollback();
      return $entityData;
    }

    $transaction->commit();

    if (!empty($params['id'])) {
      CRM_Utils_Hook::post('edit', self::getEntityName(), $entityData->id, $entityData);
    } else {
      CRM_Utils_Hook::post('create', self::getEntityName(), $entityData->id, $entityData);
    }

    return $entityData;
  }

  /**
   * Deletes row
   *
   * @param $id
   */
  public static function del($id) {
    $entity = new self();
    $entity->id = $id;
    $params = [
      'id' => $id
    ];

    if ($entity->find(TRUE)) {
      CRM_Utils_Hook::pre('delete', self::getEntityName(), $entity->id, $params);
      $entity->delete();
      CRM_Utils_Hook::post('delete', self::getEntityName(), $entity->id, $entity);
    }
  }

  /**
   * Builds query for receiving data
   *
   * @param string $returnValue
   *
   * @return \CRM_Utils_SQL_Select
   */
  private static function buildSelectQuery($returnValue = 'rows') {
    $query = CRM_Utils_SQL_Select::from(CRM_Supportcase_DAO_CaseLock::getTableName());

    if ($returnValue == 'rows') {
      $query->select('id, contact_id, case_id, lock_expire_at, lock_message');
    } else if ($returnValue == 'count') {
      $query->select('COUNT(id)');
    }

    return $query;
  }

  /**
   * Builds 'where' condition for query
   *
   * @param $query
   * @param array $params
   *
   * @return mixed
   */
  private static function buildWhereQuery($query, $params = []) {
    if (!empty($params['id'])) {
      $query->where('id = #id', ['id' => $params['id']]);
    }
    if (!empty($params['contact_id'])) {
      $query->where('contact_id = #contact_id', ['contact_id' => $params['contact_id']]);
    }
    if (!empty($params['case_id'])) {
      $query->where('case_id = #case_id', ['case_id' => $params['case_id']]);
    }
    if (!empty($params['lock_expire_at'])) {
      $query->where('lock_expire_at = #lock_expire_at', ['lock_expire_at' => $params['lock_expire_at']]);
    }
    if (!empty($params['lock_message'])) {
      $query->where('lock_message = #lock_message', ['lock_message' => $params['lock_message']]);
    }

    return $query;
  }

  /**
   * Gets all data
   *
   * @param array $params
   *
   * @return array
   */
  public static function getAll($params = []) {
    $query = self::buildWhereQuery(self::buildSelectQuery(), $params);
    return CRM_Core_DAO::executeQuery($query->toSQL())->fetchAll();
  }

  /**
   * Gets lock status info to each case id
   *
   * @param $caseIds
   * @return array
   * @throws Exception
   */
  public static function getCasesLockStatus($caseIds) {
    if (empty($caseIds)) {
      return [];
    }

    $lockStatuses = [];
    $sql = '
        SELECT *, ' . self::getIsCaseLockedSelectSql() . ' 
        FROM civicrm_supportcase_case_lock
        WHERE case_id IN(%1) AND lock_expire_at > %2
    ';

    $result = CRM_Core_DAO::executeQuery($sql, [
      1 => [implode(',', $caseIds) , 'CommaSeparatedIntegers'],
      2 => [(new DateTime())->getTimestamp() , 'Integer']
    ]);

    while ($result->fetch()) {
      $lockStatuses[] = self::formatCaseLock($result);
    }

    return $lockStatuses;
  }

  /**
   * SQL query for select 'is_case_locked' field
   *
   * @return string
   * @throws CRM_Core_Exception
   */
  public static function getIsCaseLockedSelectSql() {
    $selectSql = ' 
      CASE
        WHEN civicrm_supportcase_case_lock.lock_expire_at > %1 THEN 1
        ELSE 0
      END AS is_case_locked
    ';

    $preparedSelectSql = CRM_Core_DAO::composeQuery($selectSql, [
      1 => [(new DateTime())->getTimestamp() , 'Integer']
    ]);

    return $preparedSelectSql;
  }

  /**
   * Is lock case exist
   * Checks by id
   *
   * @param $caseLockId
   * @return string
   * @throws Exception
   */
  public static function isCaseLockExist($caseLockId) {
    if (empty($caseLockId)) {
      return false;
    }

    $caseLock = new self();
    $caseLock->id = $caseLockId;
    $caseLockExistence = $caseLock->find(TRUE);

    return !empty($caseLockExistence);
  }

  /**
   * Renews lock case
   *
   * @param $caseLockId
   * @return array
   * @throws Exception
   */
  public static function renewLockCase($caseLockId) {
    $dateTime = new DateTime();
    $timestamp = $dateTime->getTimestamp();

    $newCaseLock = new self();
    $newCaseLock->id = $caseLockId;
    $newCaseLock->lock_expire_at = $timestamp + CRM_Supportcase_Utils_Setting::getCaseLocTime();
    $newCaseLock->save();

    return [self::formatCaseLock($newCaseLock)];
  }

  /**
   * Format case lock object
   *
   * @param $caseLockObject
   * @return array
   */
  public static function formatCaseLock($caseLockObject) {
    $isLockedBySelf = CRM_Core_Session::getLoggedInContactID() == $caseLockObject->contact_id;
    return [
      'id' => $caseLockObject->id,
      'is_locked_by_self' => $isLockedBySelf,
      'is_case_locked' => true,
      'case_id' => $caseLockObject->case_id,
      'contact_id' => $caseLockObject->contact_id,
      'lock_message' => $isLockedBySelf ? CRM_Supportcase_Utils_Setting::getLockedCaseBySelfMessage() : $caseLockObject->lock_message,
      'lock_expire_at' => $caseLockObject->lock_expire_at,
    ];
  }

  /**
   * Checks if contact has access to case
   *
   * @param $caseId
   * @param $contactId
   * @return bool
   * @throws Exception
   */
  public static function isCaseLockedForContact($caseId, $contactId) {
    if (empty($caseId) || empty($contactId)) {
      return FALSE;
    }

    $sql = 'SELECT * FROM civicrm_supportcase_case_lock WHERE case_id = %1 AND lock_expire_at > %2 ';
    $result = CRM_Core_DAO::executeQuery($sql, [
      1 => [$caseId , 'Integer'],
      2 => [(new DateTime())->getTimestamp() , 'Integer']
    ]);

    while ($result->fetch()) {
      if ($result->contact_id != $contactId) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Lock case by contact
   *
   * @param $caseId
   * @param $contactId
   * @return array
   * @throws Exception
   */
  public static function lockCase($caseId, $contactId) {
    $dateTime = new DateTime();
    $timestamp = $dateTime->getTimestamp();
    $contactDisplayName = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $contactId, 'display_name');

    $newCaseLock = new self();
    $newCaseLock->case_id = $caseId;
    $newCaseLock->contact_id = $contactId;
    $newCaseLock->lock_expire_at = $timestamp + CRM_Supportcase_Utils_Setting::getCaseLocTime();
    $newCaseLock->lock_message = ts('Case is locked by %1.', [1 => $contactDisplayName]);
    $newCaseLock->save();

    return [self::formatCaseLock($newCaseLock)];
  }

  /**
   * Removes all case locks by case id and contact id
   *
   * @param $caseId
   * @param $contactId
   */
  public static function removeCaseLocks($caseId, $contactId) {
    $caseLocks = self::getAll([
      'contact_id' => $contactId,
      'case_id' => $caseId,
    ]);

    foreach ($caseLocks as $caseLock) {
      self::del($caseLock['id']);
    }
  }

  /**
   * Removes old case locks rows in table
   */
  public static function cleanOld() {
    $dateTime = new DateTime();
    $timestamp = $dateTime->getTimestamp();

    $query = '
      DELETE FROM civicrm_supportcase_case_lock 
      WHERE lock_expire_at < %1;
    ';

    CRM_Core_DAO::singleValueQuery($query, [
      1 => [$timestamp - CRM_Supportcase_Utils_Setting::getCaseLockRowLiveTime(), 'Integer']
    ]);
  }

  /**
   * Unlocks case
   * Removes all case lock related to the case id
   *
   * @param $caseId
   */
  public static function unlockCase($caseId) {
    CRM_Core_DAO::singleValueQuery('DELETE FROM civicrm_supportcase_case_lock WHERE case_id = %1;', [
      1 => [$caseId, 'Integer']
    ]);
  }

}
