<?php

class CRM_Supportcase_Task extends CRM_Core_Task {

  const RESOLVE_CASE = 1;
  const MARK_SPAM = 2;
  const TASK_DELETE = 3;
  const BATCH_UPDATE = 4;

  /**
   * @var string
   */
  public static $objectType = 'case';

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
          'class' => 'CRM_Case_Form_Task_Delete',
          'result' => FALSE,
        ],
        self::BATCH_UPDATE => [
          'title' => ts('Update multiple cases'),
          'class' => [
            'CRM_Case_Form_Task_PickProfile',
            'CRM_Case_Form_Task_Batch',
          ],
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

  public static function permissionedTaskTitles($permission, $params = []) {
    $tasks = self::taskTitles();
    $tasks = parent::corePermissionedTaskTitles($tasks, $permission, $params);
    ksort($tasks);
    return $tasks;
  }

}
