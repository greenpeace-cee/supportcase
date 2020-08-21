<?php

class CRM_Supportcase_Install_Entity_OptionGroup extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'OptionGroup';

  /**
   * Option Group name
   *
   * @var string
   */
  const CASE_CATEGORY = 'support_case_category';

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
        'name' => self::CASE_CATEGORY,
        'title' => ts("Support Case Category"),
      ],
    ];
  }

}
