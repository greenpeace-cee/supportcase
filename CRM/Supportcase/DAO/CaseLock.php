<?php

class CRM_Supportcase_DAO_CaseLock extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  static $_tableName = 'civicrm_supportcase_case_lock';

  /**
   * Static entity name.
   *
   * @var string
   */
  static $entityName = 'CaseLock';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log
   * table.
   *
   * @var boolean
   */
  static $_log = FALSE;

  /**
   * Unique id of current row
   *
   * @var int
   */
  public $id;

  /**
   * Contact id who is locked the case
   *
   * @var int
   */
  public $contact_id;

  /**
   * Case id which is locked
   *
   * @var int
   */
  public $case_id;

  /**
   * Lock of Case will be expired at
   * After this time case will be "open"
   *
   * @var int
   */
  public $lock_expire_at;

  /**
   * Message for users about this licking
   *
   * @var string
   */
  public $lock_message;

  /**
   * Returns the names of the table
   *
   * @return string
   */
  static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns entity name
   *
   * @return string
   */
  static function getEntityName() {
    return self::$entityName;
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('id'),
          'description' => 'id',
          'required' => TRUE,
          'import' => TRUE,
          'where' => self::getTableName() . '.id',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => TRUE,
          'table_name' => self::getTableName(),
          'entity' => self::getEntityName(),
          'bao' => 'CRM_Supportcase_BAO_CaseLock',
        ],
        'contact_id' => [
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Contact id'),
          'description' => 'Contact who is locked the case',
          'required' => TRUE,
          'import' => TRUE,
          'where' => self::getTableName() . '.contact_id',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => TRUE,
          'table_name' => self::getTableName(),
          'entity' => self::getEntityName(),
          'bao' => 'CRM_Supportcase_BAO_CaseLock',
        ],
        'case_id' => [
          'name' => 'case_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Case id'),
          'description' => 'The locked case',
          'required' => TRUE,
          'import' => TRUE,
          'where' => self::getTableName() . '.case_id',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => TRUE,
          'table_name' => self::getTableName(),
          'entity' => self::getEntityName(),
          'bao' => 'CRM_Supportcase_BAO_CaseLock',
        ],
        'lock_expire_at' => [
          'name' => 'lock_expire_at',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Expire at'),
          'description' => 'After this time case will be "open"',
          'required' => TRUE,
          'import' => TRUE,
          'where' => self::getTableName() . '.lock_expire_at',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => TRUE,
          'table_name' => self::getTableName(),
          'entity' => self::getEntityName(),
          'bao' => 'CRM_Supportcase_BAO_CaseLock',
        ],
        'lock_message' => [
          'name' => 'lock_message',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Expire at'),
          'description' => 'Lock is started in that time',
          'required' => TRUE,
          'import' => TRUE,
          'where' => self::getTableName() . '.lock_message',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => TRUE,
          'table_name' => self::getTableName(),
          'entity' => self::getEntityName(),
          'bao' => 'CRM_Supportcase_BAO_CaseLock',
        ],
      ];

      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }

    return Civi::$statics[__CLASS__]['fields'];
  }

}
