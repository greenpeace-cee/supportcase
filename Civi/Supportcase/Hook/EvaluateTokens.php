<?php

namespace Civi\Supportcase\Hook;

use API_Exception;
use Civi\Token\Event\TokenValueEvent;
use Civi\Token\TokenRow;
use CRM_Core_Session;
class EvaluateTokens {

  /**
   * @param TokenValueEvent $e
   * @return void
   */
  public static function run(\Civi\Token\Event\TokenValueEvent $e) {
    $currentContactId = CRM_Core_Session::getLoggedInContactID();
    $addresseeDisplay = '';
    $displayName = '';

    try {
      $contact = \Civi\Api4\Contact::get(FALSE)
        ->addSelect('display_name', 'addressee_display')
        ->addWhere('id', '=', $currentContactId)
        ->execute()
        ->first();

      $displayName = $contact['display_name'];
      $addresseeDisplay = $contact['addressee_display'];
    } catch (API_Exception $e) {}

    foreach ($e->getRows() as $row) {
      /** @var TokenRow $row */
      $row->format('text/html');
      $row->tokens('supportcase_user', 'addressee', $addresseeDisplay);
      $row->tokens('supportcase_user', 'display_name', $displayName);
    }
  }

}
