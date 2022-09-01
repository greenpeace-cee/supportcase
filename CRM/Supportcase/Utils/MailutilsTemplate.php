<?php

class CRM_Supportcase_Utils_MailutilsTemplate {

  /**
   * Removes smarty escape words
   *
   * @param $message
   * @return string
   */
  public static function removeSmartyEscapeWords($message) {
    if (empty($message)) {
      return '';
    }

    try {
      $smartyEscapeWords = \Civi\Api4\MailutilsTemplate::getSmartyEscapeWord()->execute()->first();
    } catch (Exception $e) {
      return $message;
    }

    return str_replace([$smartyEscapeWords['start'], $smartyEscapeWords['end']], '', $message);;
  }

}
