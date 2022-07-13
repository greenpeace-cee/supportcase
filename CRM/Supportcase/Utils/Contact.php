<?php

class CRM_Supportcase_Utils_Contact {

  /**
   * Is contact exist?
   *
   * @return bool
   */
  public static function isExist($contactId) {
    if (empty($contactId)) {
      return false;
    }

    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contactId;
    $contactExistence = $contact->find(TRUE);

    return (bool) $contactExistence;
  }

}
