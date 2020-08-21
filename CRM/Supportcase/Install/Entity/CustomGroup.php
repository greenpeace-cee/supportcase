<?php

class CRM_Supportcase_Install_Entity_CustomGroup extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'CustomGroup';

  /**
   * Custom Group name
   *
   * @var string
   */
  const CASE_DETAILS = 'support_case_details';

  /**
   * Params to check entity existence
   *
   * @var array
   */
  protected $entitySearchParams = ['name'];

  /**
   * Gets list of entities params
   *
   * @return array
   */
  protected function getEntityParam() {
    return [
      [
        'name' => self::CASE_DETAILS,
        'title' => ts('Support Case Details'),
        'extends' => "Case",
        "extends_entity_column_value" => [CRM_Supportcase_Utils_Setting::getMainCaseTypeId()],
      ],
    ];
  }

}
