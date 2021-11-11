<?php

class CRM_Supportcase_Install_Entity_OptionValue extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'OptionValue';

  /**
   * Params to check entity existence
   *
   * @var array
   */
  protected $entitySearchParams = ['name', 'option_group_id'];

  /**
   * Gets list of entities params
   *
   * @return array
   */
  protected function getEntityParam() {
    //TODO: Need to add real data,'without_category' - its dummy data. Used by 'category' custom filed.
    //TODO: The field has type - select and is required.
    //TODO: That's why need to create minimum one item of OptionValue for this select.
    return [
      [
        'option_group_id' => CRM_Supportcase_Install_Entity_OptionGroup::CASE_CATEGORY,
        'name' => 'without_category',
        'label' => ts("Without category"),
        'is_default' => 1,
      ],
      [
        'option_group_id' => 'case_status',
        'name' => 'forwarded',
        'label' => 'Forwarded',
        'grouping' => 'Opened',
      ],
      [
        'option_group_id' => 'case_status',
        'name' => 'spam',
        'label' => 'Spam',
        'grouping' => 'Closed',
      ],
      [
        'option_group_id' => 'activity_status',
        'name' => CRM_Supportcase_Utils_ActivityStatus::DRAFT_EMAIL,
        'label' => 'Draft email',
        'grouping' => 'Closed',
      ],
    ];
  }

}
