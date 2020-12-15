<?php

class CRM_Supportcase_Install_Entity_Job extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'Job';

  /**
   * Params for checking Entity existence
   *
   * @var array
   */
  protected $entitySearchParams = ['api_action', 'api_entity', 'domain_id'];

  /**
   * Gets list of entities params
   *
   * @return array
   */
  protected function getEntityParam() {
    return [
      [
        'name' => 'Supportcase clean old case lock',
        'description' => 'Clean old rows on "civicrm_supportcase_case_lock" table',
        'api_entity' => 'CaseLock',
        'api_action' => 'clean_old',
        'run_frequency' => 'Daily',
        'domain_id' => CRM_Core_Config::domainID(),
        'is_active' => '1',
      ],
    ];
  }

}
