<?php

class CRM_Supportcase_Install_Entity_CustomField extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'CustomField';

  /**
   * Params to check entity existence
   *
   * @var array
   */
  protected $entitySearchParams = ['name', 'custom_group_id'];

  /**
   * Gets list of entities params
   *
   * @return array
   */
  protected function getEntityParam() {
    return [
      [
        'custom_group_id' => CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS,
        'label' => ts('Category'),
        'name' => "category",
        'html_type' => "Select",
        'data_type' => "Int",
        'is_searchable' => 1,
        'is_required' => 1,
        'option_group_id' => CRM_Supportcase_Utils_OptionGroup::getId(CRM_Supportcase_Install_Entity_OptionGroup::CASE_CATEGORY)
      ],
    ];
  }

}
