<?php
use CRM_Supportcase_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Supportcase_Upgrader extends CRM_Supportcase_Upgrader_Base {

  public function install() {
    // for some reason using civicrm_managed for CaseType causes an error because
    // Civi attempts to add it twice, so we're adding it here instead
    $caseType = civicrm_api3('CaseType', 'get', [
      'return' => ['id'],
      'name'   => 'support_case',
    ]);
    $caseTypeDefinition = [
      'title' => 'Support Case',
      'name' => 'support_case',
      'definition' => [
        'restrictActivityAsgmtToCmsUser' => '0',
        'activityAsgmtGrps' => [
          'support_agent',
        ],
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
              'support_agent',
            ],
          ],
        ],
      ],
    ];
    if (!empty($caseType['id'])) {
      $caseTypeDefinition['id'] = $caseType['id'];
    }
    civicrm_api3('CaseType', 'create', $caseTypeDefinition);

    //TODO: move it in another place?
    // install custom group 'Support Case Details' and custom field 'category':
    $caseTypeId = civicrm_api3('CaseType', 'getvalue', [
      'return' => "id",
      'name' => "support_case",
    ]);

    civicrm_api3('CustomGroup', 'create', [
      'name' => 'support_case_details',
      'title' => ts('Support Case Details'),
      'extends' => "Case",
      "extends_entity_column_value" => [$caseTypeId],
    ]);

    $categoryOptionGroup = civicrm_api3('OptionGroup', 'create', [
      'name' => "support_case_category",
    ]);

    civicrm_api3('CustomField', 'create', [
      'custom_group_id' => 'support_case_details',
      'label' => ts('Category'),
      'name' => "category",
      'html_type' => "Select",
      'data_type' => "Int",
      'is_searchable' => 1,
      'is_required' => 1,
      'option_group_id' => $categoryOptionGroup['id']
    ]);

    //TODO: add real data (now its dummy data)
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => "support_case_category",
      'label' => ts("Td do"),
    ]);
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => "support_case_category",
      'label' => ts("In progress"),
    ]);
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => "support_case_category",
      'label' => ts("Done"),
    ]);

  }

}
