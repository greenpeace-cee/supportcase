<?php

/*
* Settings metadata file
*/
return [
  'supportcase_subscription_group_ids' => [
    'group_name' => 'SupportcaseConfig',
    'group' => 'SupportcaseConfig',
    'name' => 'supportcase_subscription_group_ids',
    'type' => 'Array',
    'default' => [],
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'List of subscription groups ids',
  ],
  'supportcase_available_activity_type_names' => [
    'group_name' => 'SupportcaseConfig',
    'group' => 'SupportcaseConfig',
    'name' => 'supportcase_available_activity_type_names',
    'type' => 'Array',
    'default' => [
      'Email',
      'Inbound Email',
      'SMS',
      'Inbound SMS',
      'Phone Call',
      'Outgoing Call',
    ],
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'List of available activity type names',
  ],
  'supportcase_available_case_status_names' => [
    'group_name' => 'SupportcaseConfig',
    'group' => 'SupportcaseConfig',
    'name' => 'supportcase_available_case_status_names',
    'type' => 'Array',
    'default' => [
      'Closed',
      'Open',
      'Urgent',
      'forwarded',
      'spam',
    ],
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'List of available activity type names',
  ],
  'supportcase_discard_mail_aliases' => [
    'group_name' => 'SupportcaseConfig',
    'group' => 'SupportcaseConfig',
    'name' => 'supportcase_discard_mail_aliases',
    'type' => 'Array',
    'default' => [],
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'List of email addresses that are known aliases of an inbox and should be discarded for To/CC prefills',
  ],
];
