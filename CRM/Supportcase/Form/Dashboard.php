<?php

/**
 * Support Case Dashboard
 */
class CRM_Supportcase_Form_Dashboard extends CRM_Core_Form_Search {

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
  public function getTitle() {
    return ts('Support Dashboard');
  }

  /**
   * @return array
   * @throws CRM_Core_Exception
   * @throws CiviCRM_API3_Exception
   */
  public function setDefaultValues() {
    return array_merge(parent::setDefaultValues(), $this->getSupportcaseDefaultValues());
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
    $isCaseComponentEnabled = CRM_Case_BAO_Case::isComponentEnabled();
    $configured = CRM_Case_BAO_Case::isCaseConfigured();
    $this->assign('notConfigured', !$configured['configured']);
    $this->assign('isCaseComponentEnabled', $isCaseComponentEnabled);
    if (!$isCaseComponentEnabled || !$configured['configured']) {
      return;
    }

    $this->_actionButtonName = $this->getButtonName('next', 'action');
    parent::preProcess();

    $queryParams = $this->getQueryParams();
    $config = CRM_Core_Config::singleton();
    $this->set('queryParams', $queryParams);
    $selector = new CRM_Supportcase_Selector_Dashboard($queryParams, $this->_action);
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
    $this->addSearchFormElements();
    $this->addRowSelectors($this->caseRows);

    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/ang/element.css');
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/actionPanel.css');
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/dashboard.css');
  }

  public function postProcess() {
    $this->setFormValues();
  }

  /**
   * @return void
   */
  private function addSearchFormElements() {
    $this->addSearchFieldMetadata(['Case' => CRM_Supportcase_Form_Dashboard::getCustomSearchFieldMetadata()]);
    $this->addFormFieldsFromMetadata();
    $caseTags = CRM_Core_BAO_Tag::getColorTags('civicrm_case');
    if ($caseTags) {
      $this->add('select2', 'case_tags', ts('Case Tag(s)'), $caseTags, FALSE, ['class' => 'big', 'placeholder' => ts('- select -'), 'multiple' => TRUE]);
    }

    $this->add('text', 'case_keyword', ts('Keyword'), ['class' => 'huge', 'placeholder' => 'Search within subject or message']);
    $this->addEntityRef('case_agents', ts('Involved Agent(s)'), ['multiple' => TRUE, 'api' => ['params' => ['group' => CRM_Supportcase_Install_Entity_Group::SUPPORT_AGENT]]], FALSE, ['class' => 'big']);
    $this->addEntityRef('case_client', ts('Client(s)'), ['multiple' => TRUE], FALSE, ['class' => 'big']);
    $this->add('checkbox', 'is_show_deleted_cases', ts('Show deleted cases?'));

    $caseStatusIdElement = $this->getElement('case_status_id');
    $caseStatusIdElement->setAttribute('class', 'huge crm-select2');
    $caseStatusIdElement->_options = [];
    foreach (CRM_Supportcase_Utils_Setting::getCaseStatusOptions() as $option) {
      $caseStatusIdElement->addOption($option['label'], $option['value']);
    }

    $parentNames = CRM_Core_BAO_Tag::getTagSet('civicrm_case');
    CRM_Core_Form_Tag::buildQuickForm($this, $parentNames, 'civicrm_case', NULL, TRUE, FALSE);

    $additionalClasses = [
      'case_keyword' => ['spc__input'],
      'case_agents' => ['spc__input', 'spc--multiple-select'],
      'case_client' => ['spc__input', 'spc--multiple-select'],
      'case_tags' => ['spc__input', 'spc--multiple-select'],
      'case_status_id' => ['spc__input', 'spc--multiple-select'],
      'case_start_date_relative' => ['spc__input', 'spc--single-select'],
      'case_start_date_low' => ['spc__input'],
      'case_start_date_high' => ['spc__input'],
      'case_end_date_relative' => ['spc__input', 'spc--single-select'],
      'case_end_date_low' => ['spc__input'],
      'case_end_date_high' => ['spc__input'],
      'case_id' => ['spc__input', 'spc--width-100-percent'],
    ];

    foreach ($additionalClasses as $elementName => $classes) {
      if ($this->elementExists($elementName)) {
        $this->addClassToElement($this->getElement($elementName), $classes);
      }
    }
  }

  /**
   * Add new classes to element
   *
   * @param $element
   * @param $classes
   */
  private function addClassToElement($element, $classes) {
    $elementClasses = $element->getAttribute('class');
    $newClasses = $elementClasses . ' ' . implode(' ', $classes) . ' ';
    $element->setAttribute('class', $newClasses);
  }

  /**
   * @return array
   */
  public static function getCustomSearchFieldMetadata() {
    $metadata = CRM_Case_BAO_Query::getSearchFieldMetadata();
    $metadata['case_agents'] = [
      'title' => ts('Involved Agent(s)'),
      'type' => CRM_Utils_Type::T_INT,
      'is_pseudofield' => TRUE,
      'html' => ['type' => 'Select2'],
    ];
    $metadata['is_show_deleted_cases'] = [
      'title' => ts('Show deleted cases?'),
      'type' => CRM_Utils_Type::T_BOOLEAN,
      'is_pseudofield' => TRUE,
      'html' => ['type' => 'CheckBox'],
    ];
    $metadata['case_agent'] = [
      'title' => ts('Client'),
      'type' => CRM_Utils_Type::T_INT,
      'is_pseudofield' => TRUE,
      'html' => ['type' => 'Select2'],
    ];
    $metadata['case_keyword'] = [
      'title' => ts('Keyword'),
      'type' => CRM_Utils_Type::T_STRING,
      'is_pseudofield' => TRUE,
      'html' => ['type' => 'text'],
    ];

    return $metadata;
  }

  /**
   * Set the metadata for the form.
   *
   */
  protected function setSearchMetadata() {
    $this->addSearchFieldMetadata(['Case' => CRM_Supportcase_Form_Dashboard::getCustomSearchFieldMetadata()]);
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
