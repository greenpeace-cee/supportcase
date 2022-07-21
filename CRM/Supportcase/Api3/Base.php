<?php

/**
 * Uses on api 3 to make easier using civi api3
 */
class CRM_Supportcase_Api3_Base {

  /**
   * Validated params
   */
  protected $params;

  /**
   * @param $params
   */
  public function __construct($params) {
    $this->params = $this->prepareParams($params);
  }

  /**
   * Returns results to api
   *
   * @return array
   */
  public function getResult() {
    return [];
  }

  /**
   * Prepares and validates params
   *
   * @param $params
   *
   * @return array
   */
  protected function prepareParams($params) {
    return [];
  }

  /**
   * @param $params
   * @return string[]
   */
  protected function getReturnFields($params): array {
    $returnFields = ['id'];

    if (empty($params['return'])) {
      return $returnFields;
    }

    if (is_array($params['return'])) {
      foreach ($params['return'] as $fieldName) {
        $returnFields[] = $fieldName;
      }
    }

    return $returnFields;
  }

}
