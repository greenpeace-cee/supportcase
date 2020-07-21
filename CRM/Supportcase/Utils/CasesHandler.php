<?php

/**
 * Handles cases(which returns from selector) and creates tabs
 */
class CRM_Supportcase_Utils_CasesHandler {

  /**
   * Cases which returns from selector
   *
   * @var array
   */
  private $caseRows = [];

  /**
   * Cases tabs
   *
   * @var array
   */
  private $caseTabs = [];

  /**
   * CRM_Supportcase_Utils_Category constructor.
   * @param $caseRows
   */
  public function __construct($caseRows) {
    $this->caseRows = (is_array($caseRows)) ? $caseRows: [];
  }

  /**
   * Gets case tabs prepared to display
   *
   * @return array
   */
  public function run() {
    $currentContactId = CRM_Core_Session::getLoggedInContactID();
    $categories = CRM_Supportcase_Utils_Category::get();
    $preparedCaseRows = [];
    $categoryLabels = [];
    $allCasesTabName = 'all_cases_tab';
    $myCaseTabName = 'my_cases_tab';

    $this->addTab($allCasesTabName, 'All');
    $this->addTab($myCaseTabName, 'My Cases');

    foreach ($categories as $category) {
      $categoryLabels[$category['value']] = $category['label'];
      $this->addTab($this->getCategoryClassName($category['value']), $category['label']);
    }

    foreach ($this->caseRows as $caseRow) {
      $case = $caseRow;
      $isUrgentCase = (isset($case['case_status']) && $case['case_status'] == 'Urgent');

      //my cases tab
      if ($currentContactId == $case['case_manager_contact_id']) {
        $myCaseTabName = 'my_cases_tab';
        $case['classes'][] = $myCaseTabName;
        $this->updateTabCaseCounter($myCaseTabName);
        if ($isUrgentCase) {
          $this->updateTabUrgentCounter($myCaseTabName);
        }
      }

      //all case tab
      $case['classes'][] = $allCasesTabName;
      $this->updateTabCaseCounter($allCasesTabName);
      if ($isUrgentCase) {
        $this->updateTabUrgentCounter($allCasesTabName);
      }

      //category tabs
      if (!empty($case['category'])) {
        $categoryClassName = $this->getCategoryClassName($case['category']);
        $case['classes'][] = $categoryClassName;
        $this->updateTabCaseCounter($categoryClassName);
        if ($isUrgentCase) {
          $this->updateTabUrgentCounter($categoryClassName);
        }
      }

      $preparedCaseRows[] = $case;
    }

    return [
      'tabs' => $this->caseTabs,
      'rows' => $preparedCaseRows
    ];
  }

  private function getCategoryClassName($categoryValue) {
    return 'tab_case_category_' . $categoryValue;
  }

  /**
   * Add tab
   *
   * @param $tabName
   * @param $tabLabel
   * @return void
   */
  private function addTab($tabName, $tabLabel) {
    if (empty($tabName)) {
      return;
    }

    if (empty($this->caseTabs[$tabName])) {
      $this->caseTabs[$tabName] = [
        'title' => $tabLabel,
        'case_class_selector' => $tabName,
        'name' => $tabName,
        'count_cases' => 0,
        'extra_counters' => [],
      ];
    }
  }

  /**
   * Update count of cases in tab
   *
   * @param $tabName
   * @return void
   */
  private function updateTabCaseCounter($tabName) {
    if (empty($tabName) || empty($this->caseTabs[$tabName])) {
      return;
    }

    $this->caseTabs[$tabName]['count_cases'] = $this->caseTabs[$tabName]['count_cases'] + 1;
  }

  /**
   * Update urgent counter in tab
   *
   * @param $tabName
   * @param $tabLabel
   * @return void
   */
  private function updateTabUrgentCounter($tabName) {
    if (empty($tabName) || empty($this->caseTabs[$tabName])) {
      return;
    }

    if (!isset($this->caseTabs[$tabName]['extra_counters']['urgent_counter'])) {
      $this->caseTabs[$tabName]['extra_counters']['urgent_counter'] = [
        'color' => '#d54040',
        'count' =>  0,
        'title' => 'Urgent',
      ];
    }

    $this->caseTabs[$tabName]['extra_counters']['urgent_counter']['count'] = $this->caseTabs[$tabName]['extra_counters']['urgent_counter']['count'] + 1;
  }

}
