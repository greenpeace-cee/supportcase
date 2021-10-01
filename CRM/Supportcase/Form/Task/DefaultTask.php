<?php

/**
 * This class is default task. And it does nothing.
 */
class CRM_Supportcase_Form_Task_DefaultTask extends CRM_Supportcase_Form_SupportCaseTaskBase {

  public function getTitle() {
    return ts('Default task');
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->addDefaultButtons(ts('Ok'), 'done');
  }

}
