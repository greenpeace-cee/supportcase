<?php

class CRM_Supportcase_Task extends CRM_Core_Task {

  const RESOLVE_CASE = 1;
  const MARK_SPAM = 2;
  const TASK_DELETE = 3;
  const CASE_CHANGE_CATEGORY = 4;

  /**
   * @var string
   */
  public static $objectType = 'case';

  /**
   * Returns list of available task for cases
   *
   * @return array
   */
  public static function tasks() {
    if (!self::$_tasks) {
      self::$_tasks = [
        self::RESOLVE_CASE => [
          'title' => ts('Resolve Case'),
          'class' => 'CRM_Supportcase_Form_Task_Resolve',
          'result' => FALSE,
        ],
        self::MARK_SPAM => [
          'title' => ts('Report Spam'),
          'class' => 'CRM_Supportcase_Form_Task_Spam',
          'result' => FALSE,
        ],
        self::TASK_DELETE => [
          'title' => ts('Delete cases'),
          'class' => 'CRM_Supportcase_Form_Task_Delete',
          'result' => FALSE,
        ],
        self::CASE_CHANGE_CATEGORY => [
          'title' => ts('Change Category'),
          'class' => 'CRM_Supportcase_Form_Task_ChangeCategory',
          'result' => FALSE,
        ],
      ];

      if (!CRM_Core_Permission::check('delete in CiviCase')) {
        unset(self::$_tasks[self::TASK_DELETE]);
      }

      parent::tasks();
    }

    return self::$_tasks;
  }

  /**
   * @param int $permission
   * @param array $params
   * @return array
   */
  public static function permissionedTaskTitles($permission, $params = []) {
    $tasks = self::taskTitles();
    $tasks = parent::corePermissionedTaskTitles($tasks, $permission, $params);
    ksort($tasks);
    return $tasks;
  }

  /**
   * These tasks are the core set of tasks.
   *
   * @param int $value
   *
   * @return array
   *   the set of tasks for a group of contacts
   */
  public static function getTask($value) {
    self::tasks();
    if (!$value || empty(self::$_tasks[$value])) {
      // make the print task by default
      $value = self::MARK_SPAM;
    }

    return [
      self::$_tasks[$value]['class'],
      self::$_tasks[$value]['result'],
    ];
  }

}
