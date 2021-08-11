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
    1 => 'css/ang/button.css',
    2 => 'css/ang/manageCase.css',
    3 => 'css/ang/directives/caseInfo.css',
    4 => 'css/ang/directives/communication.css',
    5 => 'css/ang/directives/recentCases.css',
    6 => 'css/ang/directives/activities.css',
    7 => 'css/ang/directives/managePanel.css',
    8 => 'css/ang/directives/communication.css',
    9 => 'css/ang/directives/quickAction.css',
    10 => 'css/ang/directives/actions/exampleAction.css',
    11 => 'css/ang/directives/contactInfo.css',
    12 => 'css/ang/directives/showMoreText.css',
    //actions styles:
    13 => 'css/ang/directives/actions/actionGeneral.css',
    14 => 'css/ang/directives/actions/doNotSms.css',
    15 => 'css/ang/directives/actions/manageEmailSubscriptions.css',
  ],
  'partials' => [
    0 => 'ang/manageCase',
  ],
  'settings' => [],
];
