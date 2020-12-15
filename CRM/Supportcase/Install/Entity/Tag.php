<?php

class CRM_Supportcase_Install_Entity_Tag extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'Tag';

  /**
   * Tag name
   *
   * @var string
   */
  const DO_NOT_SMS = 'SMS Opt-Out';

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
        'name' => self::DO_NOT_SMS,
        'parent_id' => CRM_Supportcase_Install_Entity_TagSet::ACTIONS_TAGS,
        "description" => "Contact has opted out from receiving SMS",
        "is_reserved" => 1,
      ],
    ];
  }

}
