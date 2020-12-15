<?php

/**
 * Removes old case locks
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_case_lock_clean_old($params) {
  CRM_Supportcase_BAO_CaseLock::cleanOld();

  return civicrm_api3_create_success(['message' => 'All old case locks is cleaned.']);
}

