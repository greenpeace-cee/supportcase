<?php

/**
 * Controller for Dashboard(Search Cases and Case actions)
 */
class CRM_Supportcase_Controller_Dashboard extends CRM_Core_Controller {

  /**
   * Class constructor.
   *
   * @param string $title
   * @param bool|int $action
   * @param bool $modal
   * @throws CRM_Core_Exception
   */
  public function __construct($title = NULL, $action = CRM_Core_Action::NONE, $modal = TRUE) {
    parent::__construct($title, $modal);
    $this->_stateMachine = new CRM_Supportcase_StateMachine_Dashboard($this, $action);

    // create and instantiate the pages
    $this->addPages($this->_stateMachine, $action);

    // add all the actions
    $config = CRM_Core_Config::singleton();
    $this->addActions();
  }

}
