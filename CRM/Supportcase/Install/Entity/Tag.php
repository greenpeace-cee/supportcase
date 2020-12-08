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
  const DO_NOT_SMS = 'do not sms';

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
        "description" => "Case is used 'Do not SMS' action",
        "is_reserved" => 1,
      ],
    ];
  }

}
