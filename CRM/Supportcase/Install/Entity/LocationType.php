<?php

class CRM_Supportcase_Install_Entity_LocationType extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'LocationType';

  /**
   * Params to check entity existence
   *
   * @var array
   */
  protected $entitySearchParams = ['name'];

  /**
   * Location type name
   *
   * @var string
   */
  const SUPPORT = 'support';

  /**
   * Gets list of entities params
   *
   * @return array
   */
  protected function getEntityParam() {
    return [
      [
        'name' => self::SUPPORT,
        'display_name' => 'Support',
        'is_default' => '0',
        'is_active' => '1',
        'is_reserved' => '1',
        'description' => 'Support case',
      ],
    ];
  }

}
