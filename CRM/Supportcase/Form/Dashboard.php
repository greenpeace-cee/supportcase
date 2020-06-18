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
   * @return string
   */
  public function getDefaultEntity() {
    return 'Case';
  }

  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $support_case_type_id = civicrm_api3('CaseType', 'getvalue', [
      'return' => 'id',
      'name'   => 'support_case',
    ]);
    // TODO: replace hardcoded values
    $defaultValues['case_type_id'] = $support_case_type_id;
    $defaultValues['case_status_id'] = [1, 3];
    return $defaultValues;
  }

  /**
   * Processing needed for buildForm and later.
   */
  public function preProcess() {
    //validate case configuration.
    $configured = CRM_Case_BAO_Case::isCaseConfigured();
    $this->assign('notConfigured', !$configured['configured']);
    if (!$configured['configured']) {
      return;
    }

    /**
     * set the button names
     */
    $this->_actionButtonName = $this->getButtonName('next', 'action');

    $this->_done = FALSE;

    parent::preProcess();

    $this->_queryParams = CRM_Contact_BAO_Query::convertFormValues($this->_formValues);
    $selector = new CRM_Supportcase_Selector_Dashboard($this->_queryParams,
      $this->_action,
      NULL,
      FALSE,
      $this->_limit,
      $this->_context
    );

    $this->assign('limit', $this->_limit);

    $controller = new CRM_Core_Selector_Controller($selector,
      $this->get(CRM_Utils_Pager::PAGE_ID),
      $this->getSortID(),
      CRM_Core_Action::VIEW,
      $this,
      CRM_Core_Selector_Controller::TRANSFER
    );
    $controller->setEmbedded(TRUE);
    if ($this->isInitialDisplay()) {
      // TODO: hack - perform search w/o parameters on first load
      $this->postProcess();
    }
    $controller->moveFromSessionToTemplate();

    $this->assign('caseTabs', $this->getPrepareTabs());
    $this->assign('summary', $this->get('summary'));
  }

  /**
   * Prepare case tabs
   *
   * @return array
   */
  private function getPrepareTabs() {
    $rows = $this->get('rows');
    $tabs = [];

    $tabs[] = [
      'title' => 'All',
      'html_id' => 'all',
      'count_cases' => count($rows),
      'extra_counters' => [//TODO: Add needed counters, its dummy
        [
          'color' => 'blue',
          'count' => 9,
          'title' => 'title',
        ]
      ],
      'cases' => $rows
    ];

    //TODO: fill “My Cases” tab
    $tabs[] = [
      'title' => 'My Cases',
      'html_id' => 'my_cases',
      'count_cases' => 0,
      'extra_counters' => [],
      'cases' => []
    ];

    $categories = CRM_Supportcase_Utils_Category::get();
    foreach ($categories as $category) {
      $cases = $this->findCasesByCategory($category, $rows);
      $tabs[] = [
        'title' => $category['label'],
        'html_id' => 'category_' . $category['id'],
        'count_cases' => count($cases),
        'extra_counters' => [],
        'cases' => $cases
      ];
    }

    return $tabs;
  }

  /**
   * Filters cases by category
   *
   * @param $category
   * @param $cases
   * @return mixed
   */
  private function findCasesByCategory($category, $cases) {
    $filteredCases = [];

    foreach ($cases as $case) {
      if ($category['id'] == $case['category']) {
        $filteredCases[] = $case;
      }
    }

    return $filteredCases;
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->addSortNameField();

    CRM_Supportcase_BAO_Query::buildSearchForm($this);

    $rows = $this->get('rows');
    if (is_array($rows)) {
      $this->addRowSelectors($rows);

      $tasks = CRM_Supportcase_Task::permissionedTaskTitles(CRM_Core_Permission::getPermission(), []);

      $this->addTaskMenu($tasks);
    }

    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/dashboard.css');
  }

  public function postProcess() {
    if ($this->_done) {
      return;
    }

    $this->_done = TRUE;
    $this->setFormValues();
    // TODO: hack - find some other way to signal "initial page load, search with default filters"
    foreach ($this->setDefaultValues() as $field => $value) {
      // TODO: move fixed case_type_id filter to CRM_Supportcase_BAO_Query?
      if ($this->isInitialDisplay() || $field == 'case_type_id') {
        $this->_formValues[$field] = $value;
      }
    }

    // @todo - stop changing formValues - respect submitted form values, change a working array.
    $this->_queryParams = CRM_Contact_BAO_Query::convertFormValues($this->_formValues);

    $this->set('queryParams', $this->_queryParams);

    $buttonName = $this->controller->getButtonName();
    if ($buttonName == $this->_actionButtonName) {
      // check actionName and if next, then do not repeat a search, since we are going to the next page

      // hack, make sure we reset the task values
      $stateMachine = $this->controller->getStateMachine();
      $formName = $stateMachine->getTaskFormName();
      $this->controller->resetPage($formName);
      return;
    }

    $this->_queryParams = CRM_Contact_BAO_Query::convertFormValues($this->_formValues);

    $selector = new CRM_Supportcase_Selector_Dashboard($this->_queryParams,
      $this->_action,
      NULL,
      FALSE,
      $this->_limit,
      $this->_context
    );
    $selector->setKey($this->controller->_key);

    $this->assign('limit', $this->_limit);

    $controller = new CRM_Core_Selector_Controller($selector,
      $this->get(CRM_Utils_Pager::PAGE_ID),
      $this->getSortID(),
      CRM_Core_Action::VIEW,
      $this,
      CRM_Core_Selector_Controller::SESSION
    );
    $controller->setEmbedded(TRUE);

    $query = &$selector->getQuery();
    if ($this->_context == 'user') {
      $query->setSkipPermission(TRUE);
    }
    $controller->run();
  }

  public function getTitle() {
    return ts('Support Dashboard');
  }

  /**
   * Set the metadata for the form.
   *
   */
  protected function setSearchMetadata() {
    $this->addSearchFieldMetadata(['Case' => CRM_Supportcase_BAO_Query::getSearchFieldMetadata()]);
  }

  /**
   * Is this the initial page load with no results and search parameters?
   *
   * @todo change this so it doesn't make me wanna throw up
   *
   * @return bool
   */
  protected function isInitialDisplay() {
    return empty($this->get('rows')) && empty($_POST);
  }

}
