<?php

/**
 * Separates cases(which returns from selector) into tabs
 */
class CRM_Supportcase_Utils_CaseTabs {

  /**
   * Cases which returns from selector
   *
   * @var array
   */
  public $caseRows = [];

  /**
   * Prepared case tabs to display
   *
   * @var array
   */
  public $tabs = [];

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
  public function separateToTabs() {
    $this->tabs[] = $this->getPrepareAllCaseTab();
    $this->tabs[] = $this->getPrepareMyCaseTab();
    foreach ($this->getCaseCategoryTabs() as $categoryTab) {
      $this->tabs[] = $categoryTab;
    }

    return $this->tabs;
  }

  /**
   * Get Prepared 'all' tab
   *
   * @return array
   */
  private function getPrepareAllCaseTab() {
    $allTab = [
      'title' => 'All',
      'html_id' => 'all',
      'count_cases' => count($this->caseRows),
      'extra_counters' => [],
      'cases' => $this->caseRows
    ];
    $countOfUrgentCases = $this->getCountOfUrgentCases($this->caseRows);
    if ($countOfUrgentCases > 0) {
      $allTab['extra_counters'][] = $this->prepareUrgentCounterParams($countOfUrgentCases);
    }

    return $allTab;
  }

  /**
   * Gets prepared 'My case' tab
   *
   * @return array
   */
  private function getPrepareMyCaseTab() {
    //TODO: fill “My Cases” tab
    return [
      'title' => 'My Cases',
      'html_id' => 'my_cases',
      'count_cases' => 0,
      'extra_counters' => [],
      'cases' => []
    ];
  }

  /**
   * Gets prepared tabs based on 'category' custom filed
   *
   * @return array
   */
  private function getCaseCategoryTabs() {
    $tabs = [];

    $categories = CRM_Supportcase_Utils_Category::get();
    foreach ($categories as $category) {
      $cases = $this->findCasesByCategory($category, $this->caseRows);
      $countOfUrgentCases = $this->getCountOfUrgentCases($cases);
      $tab = [
        'title' => $category['label'],
        'html_id' => 'category_' . $category['value'],
        'count_cases' => count($cases),
        'extra_counters' => [],
        'cases' => $cases
      ];
      if ($countOfUrgentCases > 0) {
        $tab['extra_counters'][] = $this->prepareUrgentCounterParams($countOfUrgentCases);
      }
      $tabs[] = $tab;
    }

    return $tabs;
  }

  /**
   * Prepares params to display 'urgent' counter in tabs
   *
   * @param $count
   * @return array
   */
  private function prepareUrgentCounterParams($count) {
    return  [
      'color' => 'red',
      'count' => $count,
      'title' => 'Urgent',
    ];
  }

  /**
   * Gets count of urgent task
   *
   * @param $cases
   * @return mixed
   */
  private function getCountOfUrgentCases($cases) {
    $counter = 0;

    foreach ($cases as $case) {
      if (isset($case['case_status']) && $case['case_status'] == 'Urgent') {
        $counter++;
      }
    }

    return $counter;
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
      if ($category['value'] == $case['category']) {
        $filteredCases[] = $case;
      }
    }

    return $filteredCases;
  }

}
