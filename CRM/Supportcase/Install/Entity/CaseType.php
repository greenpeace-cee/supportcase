<?php

class CRM_Supportcase_Install_Entity_CaseType extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Entity name
   *
   * @var string
   */
  protected $entityName = 'CaseType';

  /**
   * Params to check entity existence
   *
   * @var array
   */
  protected $entitySearchParams = ['name'];

  /**
   * Case type name
   *
   * @var string
   */
  const SUPPORT_CASE = 'support_case';

  /**
   * Gets list of entities params
   *
   * @return array
   */
  protected function getEntityParam() {
    return [
      [
        'title' => 'Support Case',
        'name' => self::SUPPORT_CASE,
        'definition' => [
          'restrictActivityAsgmtToCmsUser' => '0',
          'activityAsgmtGrps' => [
            CRM_Supportcase_Install_Entity_Group::SUPPORT_AGENT,
          ],
          'caseStatuses' => CRM_Supportcase_Utils_Setting::get('supportcase_available_case_status_names'),
          'activityTypes' => [
            [
              'name' => 'Open Case',
              'max_instances' => '1',
            ],
            [
              'name' => 'Email',
            ],
            [
              'name' => 'Inbound Email',
            ],
            [
              'name' => 'Phone Call',
            ],
            [
              'name' => 'Outgoing Call',
            ],
            [
              'name' => 'Inbound SMS',
            ],
            [
              'name' => 'SMS',
            ],
            [
              'name' => 'Follow up',
            ],
            [
              'name' => 'Meeting',
            ],
          ],
          'activitySets' => [
            [
              'name' => 'standard_timeline',
              'label' => 'Standard Timeline',
              'timeline' => '1',
              'activityTypes' => [
                [
                  'name' => 'Open Case',
                  'status' => 'Completed',
                  'label' => 'Open Case',
                  'default_assignee_type' => '1',
                  'default_assignee_relationship' => [],
                  'default_assignee_contact' => [],
                ],
              ],
            ],
          ],
          'timelineActivityTypes' => [
            [
              'name' => 'Open Case',
              'status' => 'Completed',
              'label' => 'Open Case',
              'default_assignee_type' => '1',
              'default_assignee_relationship' => [],
              'default_assignee_contact' => [],
            ],
          ],
          'caseRoles' => [
            [
              'name' => 'Case Coordinator',
              'creator' => '1',
              'manager' => '1',
              'groups' => [
                CRM_Supportcase_Install_Entity_Group::SUPPORT_AGENT,
              ],
            ],
          ],
        ],
      ],
    ];
  }

}
