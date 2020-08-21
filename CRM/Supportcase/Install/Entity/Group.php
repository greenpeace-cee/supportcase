<?php

class CRM_Supportcase_Install_Entity_Group extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'Group';

  /**
   * Group name
   *
   * @var string
   */
  const SUPPORT_AGENT = 'support_agent';

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
        'title' => ts('Support Agent'),
        'name' => self::SUPPORT_AGENT,
        'description' => 'Contacts that act as support agents',
      ],
    ];
  }

}
