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
    $tabs = [];
    $tabs[] = $this->getPrepareAllCaseTab();
    $tabs[] = $this->getPrepareMyCaseTab();
    foreach ($this->getCaseCategoryTabs() as $categoryTab) {
      $tabs[] = $categoryTab;
    }

    return $tabs;
  }

  /**
   * Get Prepared 'all' tab
   *
   * @return array
   */
  private function getPrepareAllCaseTab() {
    return [
      'title' => 'All',
      'html_id' => 'all',
      'count_cases' => count($this->caseRows),
      'extra_counters' => $this->prepareCaseExtraCounters($this->caseRows),
      'cases' => $this->caseRows
    ];
  }

  /**
   * Gets prepared 'My case' tab
   *
   * @return array
   */
  private function getPrepareMyCaseTab() {
    $cases = [];
    $currentContactId = CRM_Core_Session::getLoggedInContactID();
    if (empty($currentContactId)) {
      return [];
    }

    foreach ($this->caseRows as $case) {
      if ($currentContactId == $case['case_manager_contact_id']) {
        $cases[] = $case;
      }
    }

    return [
      'title' => 'My Cases',
      'html_id' => 'my_cases',
      'count_cases' => count($cases),
      'extra_counters' => $this->prepareCaseExtraCounters($cases),
      'cases' => $cases
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
      $tabs[] = [
        'title' => $category['label'],
        'html_id' => 'category_' . $category['value'],
        'count_cases' => count($cases),
        'extra_counters' => $this->prepareCaseExtraCounters($cases),
        'cases' => $cases
      ];
    }

    return $tabs;
  }

  /**
   * Prepares extra counters params for case rows
   *
   * @param $cases
   * @return array
   */
  private function prepareCaseExtraCounters($cases) {
    $extraCounters = [];
    $countOfUrgentCases = $this->getCountOfUrgentCases($cases);
    if ($countOfUrgentCases > 0) {
      $extraCounters[] = $this->prepareUrgentCounterParams($countOfUrgentCases);
    }

    return $extraCounters;
  }

  /**
   * Prepares params to display 'urgent' counter in tabs
   *
   * @param $count
   * @return array
   */
  private function prepareUrgentCounterParams($count) {
    return  [
      'color' => '#d54040',
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
