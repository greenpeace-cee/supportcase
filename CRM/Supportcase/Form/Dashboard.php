<?php

/**
 * Support Case Dashboard, based on CRM_Case_Form_Search
 */
class CRM_Supportcase_Form_Dashboard extends CRM_Core_Form_Search {

  /**
   * The params that are sent to the query
   *
   * @var array
   */
  protected $_queryParams;

  /**
   * Row limit
   *
   * @var int
   */
  protected $_limit = NULL;

  /**
   * Case rows - result of selector
   *
   * @var array
   */
  protected $caseRows = NULL;

  /**
   * @return string
   */
  public function getDefaultEntity() {
    return 'Case';
  }

  /**
   * @return string
   */
  public function getTitle() {
    return ts('Support Dashboard');
  }

  /**
   * @return array
   * @throws CRM_Core_Exception
   * @throws CiviCRM_API3_Exception
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $defaultValues['case_type_id'] = CRM_Supportcase_Utils_Setting::getMainCaseTypeId();
    $defaultValues['case_status_id'] = [
      CRM_Core_PseudoConstant::getKey('CRM_Case_BAO_Case', 'case_status_id', 'Open'),
      CRM_Core_PseudoConstant::getKey('CRM_Case_BAO_Case', 'case_status_id', 'Urgent'),
    ];

    // to prevent accidentally selecting cases on not active tabs - clears cases checkboxes
    // clears if current task is default task
    $isDefaultTask = $this->controller->getStateMachine()->getTaskFormName() == 'DefaultTask';
    if ($isDefaultTask) {
      foreach ($defaultValues as $key => $field) {
        if(preg_match('/^' . self::CB_PREFIX . '/', $key)) {
          unset($defaultValues[$key]);
        }
      }
    }

    return $defaultValues;
  }

  /**
   * Processing needed for buildForm and later.
   */
  public function preProcess() {
    //validate case configuration.
    $isCaseComponentEnabled = CRM_Case_BAO_Case::enabled();
    $configured = CRM_Case_BAO_Case::isCaseConfigured();
    $this->assign('notConfigured', !$configured['configured']);
    $this->assign('isCaseComponentEnabled', $isCaseComponentEnabled);
    if (!$isCaseComponentEnabled || !$configured['configured']) {
      return;
    }

    $this->_actionButtonName = $this->getButtonName('next', 'action');
    parent::preProcess();

    $this->_queryParams = $this->getQueryParams();
    $this->set('queryParams', $this->_queryParams);
    $selector = new CRM_Supportcase_Selector_Dashboard($this->_queryParams, $this->_action);
    $controller = new CRM_Core_Selector_Controller($selector,
      $this->get(CRM_Utils_Pager::PAGE_ID),
      $this->getSortID(),
      CRM_Core_Action::VIEW,
      $this,
      CRM_Core_Selector_Controller::SESSION
    );

    $controller->run();
    $this->caseRows = $this->get('rows');
    $controller->setEmbedded(TRUE);
    $controller->moveFromSessionToTemplate();

    $this->set('searchFormName', 'Dashboard');
    $this->assign('cases', (new CRM_Supportcase_Utils_CasesHandler($this->caseRows))->run());
    $this->assign('summary', $this->get('summary'));
    $this->assign('limit', $this->_limit);
    $this->assign('currentContactId', CRM_Core_Session::getLoggedInContactID());
    $this->assign('isTagsFilterEmpty', $this->isTagsFilterEmpty());
    $this->assign('lockReloadTimeInSek', CRM_Supportcase_Utils_Setting::getDashboardLockReloadTime());
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    $this->addSortNameField();
    CRM_Supportcase_BAO_Query::buildSearchForm($this);
    $this->addRowSelectors($this->caseRows);
    $tasks = CRM_Supportcase_Task::permissionedTaskTitles(CRM_Core_Permission::getPermission(), []);
    $this->addTaskMenu($tasks);

    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/general-dashboard.css');
    if (function_exists('_shoreditch_isActive') && _shoreditch_isActive()) {
      CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/with-shoreditch-dashboard.css');
    } else {
      CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/without-shoreditch-dashboard.css');
    }
  }

  public function postProcess() {
    $this->setFormValues();
    $buttonName = $this->controller->getButtonName();

    // check actionName and if next, then do not repeat a search, since we are going to the next page
    if ($buttonName != $this->_actionButtonName) {
      return;
    }

    // hack, make sure we reset the task values
    $stateMachine = $this->controller->getStateMachine();
    $formName = $stateMachine->getTaskFormName();
    $this->controller->resetPage($formName);
  }

  /**
   * Set the metadata for the form.
   *
   */
  protected function setSearchMetadata() {
    $this->addSearchFieldMetadata(['Case' => CRM_Supportcase_BAO_Query::getSearchFieldMetadata()]);
  }

  /**
   * Is tags filter empty?
   *
   * @return bool
   */
  protected function isTagsFilterEmpty() {
    if (empty($this->_formValues['case_taglist'])) {
      return TRUE;
    }

    foreach ($this->_formValues['case_taglist'] as $tag) {
      if (!empty($tag)) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Gets query params for selector
   *
   * @return array
   */
  private function getQueryParams() {
    $values = [];
    $isInitialDisplay = empty($_GET['qfKey']);
    if ($isInitialDisplay) {
      foreach ($this->setDefaultValues() as $field => $value) {
        $values[$field] = $value;
      }
    }

    $values = array_merge($values, $this->_submitValues);
    $values['case_type_id'] = CRM_Supportcase_Utils_Setting::getMainCaseTypeId();

    return CRM_Contact_BAO_Query::convertFormValues($values);
  }

}
