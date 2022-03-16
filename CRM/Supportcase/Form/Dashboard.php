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
    $defaultValues = array_merge(parent::setDefaultValues(), $this->getSupportcaseDefaultValues());

    // to prevent accidentally selecting cases on not active tabs - clears cases checkboxes
    // clears if current task is default task
    $isDefaultTask = $this->controller->getStateMachine()->getTaskFormName() == 'DefaultTask';
    if ($isDefaultTask) {
      foreach ($defaultValues as $key => $field) {
        if (preg_match('/^' . CRM_Core_Form::CB_PREFIX . '/', $key)) {
          unset($defaultValues[$key]);
        }
      }
    }

    return $defaultValues;
  }

  /**
   * @return array
   */
  public function getSupportcaseDefaultValues() {
    return [
      'case_type_id' => CRM_Supportcase_Utils_Setting::getMainCaseTypeId(),
      'case_status_id' => [
        CRM_Core_PseudoConstant::getKey('CRM_Case_BAO_Case', 'case_status_id', 'Open'),
        CRM_Core_PseudoConstant::getKey('CRM_Case_BAO_Case', 'case_status_id', 'Urgent'),
      ],
      "case_id" => '',
      "case_keyword" => '',
      "case_agents" => '',
      "case_start_date_relative" => '',
      "case_start_date_low" => '',
      "case_start_date_high" => '',
      "case_end_date_relative" => '',
      "case_end_date_low" => '',
      "case_end_date_high" => '',
      "case_client" => '',
      "case_taglist" => '',
    ];
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
    $config = CRM_Core_Config::singleton();
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

    $pager = (CRM_Core_Smarty::singleton())->get_template_vars('pager');
    $isShowPagination = !empty($pager) && $pager->numItems() > $pager->_perPage;
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String');
    $dashboardSearchQfKey = CRM_Utils_Rule::qfKey($qfKey) ? $qfKey : false;

    $this->set('searchFormName', 'Dashboard');
    $this->assign('cases', (new CRM_Supportcase_Utils_CasesHandler($this->caseRows))->run());
    $this->assign('summary', $this->get('summary'));
    $this->assign('limit', $this->_limit);
    $this->assign('currentContactId', CRM_Core_Session::getLoggedInContactID());
    $this->assign('isTagsFilterEmpty', $this->isTagsFilterEmpty());
    $this->assign('lockReloadTimeInSek', CRM_Supportcase_Utils_Setting::getDashboardLockReloadTime());
    $this->assign('isShowPagination', $isShowPagination);
    $this->assign('civiBaseUrl', rtrim($config->userFrameworkBaseURL, "/"));
    $this->assign('isCollapseFilter', $this->isCollapseFilter());
    $this->assign('addNewCaseUrl', $this->getCreateNewCaseUrl());
    $this->assign('dashboardSearchQfKey', $dashboardSearchQfKey);
    $this->assign('categories', CRM_Supportcase_Utils_Category::get());
    $this->assign('itemsPerPage', CRM_Supportcase_Utils_Setting::getDefaultCountOfRows());

    // to clean old values from previous tasks when user click on 'cancel' button
    $buttonName = $this->controller->getButtonName();
    if ($buttonName == '_qf_Dashboard_display') {
      $this->cleanSelectedCasesAtFormParams();
    }
  }

  /**
   * @return string
   */
  private function getCreateNewCaseUrl() {
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String');

    if (CRM_Utils_Rule::qfKey($qfKey)) {
      return CRM_Utils_System::url('civicrm/supportcase/add-case', "dashboardSearchQfKey=$qfKey");
    }

    return CRM_Utils_System::url('civicrm/supportcase/add-case');
  }

  /**
   * true - search filter closed
   * false - search filter open
   *
   * @return bool
   */
  public function isCollapseFilter() {
    $submitValues = $this->getSubmitValues();
    $defaultValues = $this->getSupportcaseDefaultValues();
    if (empty($submitValues)) {
      return true;
    }

    foreach ($defaultValues as $name => $value) {
      if ($name === 'case_type_id') {
        continue;
      }

      if (empty($submitValues[$name]) && !empty($value)) {
        return false;
      }

      // case_taglist field is complicated and need to advance compare. It is list of tag sets with values
      if ($name === 'case_taglist') {
        if ($submitValues[$name] === $value) {
          continue;
        }

        if (!is_array($submitValues[$name])) {
          return false;
        }

        foreach ($submitValues[$name] as $tagSetId => $tagSetValues) {
          if (!empty($tagSetValues)) {
            return false;
          }
        }

        continue;
      }

      if (is_array($value)) {
        if (!empty(array_diff($value, $submitValues[$name])) || !empty(array_diff($submitValues[$name], $value))) {
          return false;
        }
      } else {
        if ($submitValues[$name] != $value) {
          return false;
        }
      }
    }

    return true;
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

    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/ang/element.css');
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/actionPanel.css');
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/dashboard.css');
  }

  public function postProcess() {
    $this->setFormValues();
    $buttonName = $this->controller->getButtonName();

    // to clean old values from previous tasks
    $isGoToTheDashboardAfterTask = is_null($buttonName);
    if ($isGoToTheDashboardAfterTask) {
        $this->cleanSelectedCasesAtFormParams();
    }

    // check actionName and if next, then do not repeat a search, since we are going to the next page
    if ($buttonName != $this->_actionButtonName) {
      return;
    }

    // hack, make sure we reset the task values
    // TODO: check if it still needed
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

  /**
   * Cleans old values from previous tasks
   */
  protected function cleanSelectedCasesAtFormParams() {
    $formValues = $this->controller->get('formValues');
    $cleanedQueryParams = [];

    if (!empty($formValues))  {
        foreach ($formValues as $key => $value) {
            $isSelectCaseParam = substr($key, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX;
            if (!$isSelectCaseParam) {
                $cleanedQueryParams[$key] = $value;
            }
        }
    }

    $this->controller->set('formValues', $cleanedQueryParams);
  }

}
