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

  const MADE_SUPPORT_REQUEST_RELATED_TO = 'made_support_request_related_to';

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
      [
        'name_a_b' => self::MADE_SUPPORT_REQUEST_RELATED_TO,
        'name_b_a' => 'has_support_request_made_by',
        'label_a_b' => "Made support request related to",
        'label_b_a' => "Has support request made by",
        'description' => '',
      ],
    ];
  }

}
