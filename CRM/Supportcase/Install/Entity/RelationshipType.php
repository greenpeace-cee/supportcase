<?php

class CRM_Supportcase_Install_Entity_RelationshipType extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'RelationshipType';

  /**
   * Params to check entity existence
   *
   * @var array
   */
  protected $entitySearchParams = ['name_a_b'];

  /**
   * Gets list of entities params
   *
   * @return array
   */
  protected function getEntityParam() {
    return [
      [
        'name_a_b' => 'duplicates',
        'name_b_a' => 'is leading clone of',
        'is_active' => '1',
      ],
    ];
  }

}
