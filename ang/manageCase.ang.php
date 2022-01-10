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
    0 => 'css/ang/hideCiviUi.css',
    1 => 'css/ang/general.css',
    2 => 'css/ang/element.css',
    3 => 'css/ang/manageCase.css',
    4 => 'css/ang/directives/caseInfo.css',
    5 => 'css/ang/directives/communication.css',
    6 => 'css/ang/directives/recentCases.css',
    7 => 'css/ang/directives/activities.css',
    8 => 'css/ang/directives/managePanel.css',
    9 => 'css/ang/directives/communication.css',
    10 => 'css/ang/directives/quickAction.css',
    11 => 'css/ang/directives/actions/exampleAction.css',
    12 => 'css/ang/directives/contactInfo.css',
    13 => 'css/ang/directives/showMoreText.css',
    14 => 'css/ang/directives/selectEmail.css',
    15 => 'css/ang/directives/comments.css',

    //actions styles:
    16 => 'css/ang/directives/actions/actionGeneral.css',
    17 => 'css/ang/directives/actions/doNotSms.css',
    18 => 'css/ang/directives/actions/manageEmailSubscriptions.css',
  ],
  'partials' => [
    0 => 'ang/manageCase',
  ],
  'settings' => [],
];
