<?php

class CRM_Supportcase_Install_Entity_TagSet extends CRM_Supportcase_Install_Entity_Base {

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
  const ACTIONS_TAGS = 'Support case action tags';

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
        'name' => self::ACTIONS_TAGS,
        "description" => "Tag set for support case actions",
        "is_tagset" => "1",
        "is_reserved" => 1,
        "used_for" => "Cases",
      ],
    ];
  }

}
