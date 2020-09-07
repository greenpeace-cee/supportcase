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
      $query->select('id, contact_id, case_id, expire_at');
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
      $isLockedBySelf = CRM_Core_Session::getLoggedInContactID() == $result->contact_id;
      $lockStatuses[] = [
        'is_locked_by_self' => $isLockedBySelf,
        'is_case_locked' => $result->is_case_locked == 1,
        'case_id' => $result->case_id,
        'contact_id' => $result->contact_id,
        'lock_message' => $isLockedBySelf ? CRM_Supportcase_Utils_Setting::getLockedCaseBySelfMessage() : $result->lock_message,
        'lock_expire_at' => $result->lock_expire_at,
      ];
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
      1 => [implode(',', $caseId) , 'Integer'],
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
   * @throws CRM_Core_Exception
   */
  public static function lockCase($caseId, $contactId) {
    $dateTime = new DateTime();
    $timestamp = $dateTime->getTimestamp();
    $contactDisplayName = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $contactId, 'display_name');

    $caseLock = new self();
    $caseLock->case_id = $caseId;
    $caseLock->contact_id = $contactId;
    $caseLockExistence = $caseLock->find(TRUE);

    $newCaseLock = new self();
    if (!empty($caseLockExistence)) {
      $newCaseLock->id = $caseLock->id;
    }
    $newCaseLock->case_id = $caseId;
    $newCaseLock->contact_id = $contactId;
    $newCaseLock->lock_expire_at = $timestamp + CRM_Supportcase_Utils_Setting::getCaseLocTime();
    $newCaseLock->lock_message = ts('Case is locked by %1.', [1 => $contactDisplayName]);
    $newCaseLock->save();

    $isLockedBySelf = CRM_Core_Session::getLoggedInContactID() == $newCaseLock->contact_id;

    return [
      [
        'is_locked_by_self' => $isLockedBySelf,
        'is_case_locked' => true,
        'case_id' => $newCaseLock->case_id,
        'contact_id' => $newCaseLock->contact_id,
        'lock_message' => $isLockedBySelf ? CRM_Supportcase_Utils_Setting::getLockedCaseBySelfMessage() : $newCaseLock->lock_message,
        'lock_expire_at' => $newCaseLock->lock_expire_at,
      ]
    ];
  }

}
