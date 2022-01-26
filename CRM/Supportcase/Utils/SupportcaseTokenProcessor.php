<?php

class CRM_Supportcase_Utils_SupportcaseTokenProcessor {

  /**
   * @param $rawText
   * @param $contactId
   * @return string
   */
  public static function handleTokens($rawText, $contactId) {
    $errorMessage = 'Error while handling tokens :(';
    if (empty($rawText)) {
      return $errorMessage;
    }

    if (empty($contactId)) {
      return 'Empty related contact.' . $errorMessage;
    }

    try {
      $tokenProcessor = new \Civi\Token\TokenProcessor(\Civi::dispatcher(), [
        'controller' => __CLASS__,
        'smarty' => TRUE,
      ]);

      $tokenProcessor->addMessage('body_text', $rawText, 'text/plain');
      $tokenProcessor->addRow()->context('contactId', $contactId);
      $tokenProcessor->evaluate();
      $rows = $tokenProcessor->getRows();
    } catch (Exception $e) {
      return $errorMessage;
    }


    if (empty($rows)) {
      return $errorMessage;
    }

    foreach ($rows as $row) {
      try {
        $renderedText = $row->render('body_text');
      } catch (Exception $e) {
        return $errorMessage;
      }

      return $renderedText;
    }

    return $errorMessage;
  }

}
