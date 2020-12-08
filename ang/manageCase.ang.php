<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
return [
  'requires' => ['ngRoute'],
  'js' =>
    [
      0 => 'ang/manageCase.js',
    ],
  'css' => [
    0 => 'css/ang/general.css',
    1 => 'css/ang/manageCase.css',
    2 => 'css/ang/directives/caseInfo.css',
    3 => 'css/ang/directives/communication.css',
    4 => 'css/ang/directives/recentCases.css',
    5 => 'css/ang/directives/activities.css',
    6 => 'css/ang/directives/managePanel.css',
    7 => 'css/ang/directives/communication.css',
    8 => 'css/ang/directives/quickAction.css',
    9 => 'css/ang/directives/actions/exampleAction.css',
    10 => 'css/ang/directives/actions/doNotSms.css',
    11 => 'css/ang/directives/contactInfo.css',
  ],
  'partials' => [
    0 => 'ang/manageCase',
  ],
  'settings' => [],
];
