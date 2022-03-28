<?php

/**
 * Search mailing emails/contacts by input
 */
function  civicrm_api3_supportcase_email_get($params) {
  $searchResult = [];

  if (!empty($params['search_string']) && !empty(trim($params['search_string']))) {
    $searchResult = CRM_Supportcase_Utils_EmailSearch::searchByString(trim($params['search_string']));
  } elseif (!empty($params['email_id'])) {
    if (is_array($params['email_id'])) {
      foreach ($params['email_id'] as $emailId) {
        $searchItems = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($emailId);
        foreach ($searchItems as $item) {
          $searchResult[] = $item;
        }
      }
    } else {
      $searchResult = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($params['email_id']);;
    }
  }

  return civicrm_api3_create_success($searchResult, $params);
}
