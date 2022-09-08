<?php

class CRM_Supportcase_Utils_MailutilsTemplate {

  /**
   * Removes smarty escape words
   *
   * @param $message
   * @return string
   */
  public static function prepareToExecuteMessage($message) {
    if (empty($message)) {
      return '';
    }

    try {
      $mailutilsTemplate = \Civi\Api4\MailutilsTemplate::prepareToExecuteMessage()
        ->setMessage($message)
        ->execute()
        ->first();
    } catch (Exception $e) {
      return $message;
    }

    return $mailutilsTemplate['preparedToExecuteMessage'];
  }

}
