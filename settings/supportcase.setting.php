<?php

/*
* Settings metadata file
*/
return [
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
  ]
];
