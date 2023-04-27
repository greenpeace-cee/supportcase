<?php

namespace Civi\Supportcase\Hook;

use Civi\Token\Event\TokenRegisterEvent;

class RegisterTokens {

  /**
   * @param TokenRegisterEvent $e
   * @return void
   */
  public static function run(\Civi\Token\Event\TokenRegisterEvent $e) {
    $e->entity('supportcase_user')
      ->register('addressee', ts('Addressee of logged in contact'))
      ->register('display_name', ts('Display name of logged in contact'));
  }

}
