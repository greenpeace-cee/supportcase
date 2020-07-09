<?php
return [
  [
    'name'    => 'supportcase_status_forwarded',
    'entity'  => 'OptionValue',
    'cleanup' => 'unused',
    'params'  => [
      'version'         => 3,
      'option_group_id' => 'case_status',
      'name'            => 'forwarded',
      'label'           => 'Forwarded',
      'grouping'        => 'Opened',
    ],
  ],
  [
    'name'    => 'supportcase_status_spam',
    'entity'  => 'OptionValue',
    'cleanup' => 'unused',
    'params'  => [
      'version'         => 3,
      'option_group_id' => 'case_status',
      'name'            => 'spam',
      'label'           => 'Spam',
      'grouping'        => 'Closed',
    ],
  ],
];
