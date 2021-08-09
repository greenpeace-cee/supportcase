<?php

/**
 * Search mailing group/email/contact by input
 *
 * @param array $params
 *
 * @return array
 */
function  civicrm_api3_supportcase_email_get($params) {
  $searchResult = [];

  if (!empty($params['search_string']) && !empty(trim($params['search_string']))) {
    $searchResult = CRM_Supportcase_Utils_EmailSearch::searchByString(trim($params['search_string']));
  } elseif (!empty($params['search_pseudo_id'])) {
    foreach ($params['search_pseudo_id'] as $pseudoId) {
      $searchItems = CRM_Supportcase_Utils_EmailSearch::searchByPseudoId($pseudoId);
      foreach ($searchItems as $item) {
        $searchResult[] = $item;
      }
    }
  }

  return civicrm_api3_create_success($searchResult, $params);
}
