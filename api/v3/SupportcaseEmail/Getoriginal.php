<?php

use Civi\Api4\MailutilsMessage;

function _civicrm_api3_supportcase_email_getoriginal_spec(&$params) {
  $params['activity_id'] = [
    'name' => 'activity_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Activity ID',
  ];
}

/**
 * Get unfiltered HTML version of email (if available)
 */
function civicrm_api3_supportcase_email_getoriginal($params) {
  $message = MailutilsMessage::get(FALSE)
    ->addWhere('activity_id', '=', $params['activity_id'])
    ->execute()
    ->first();

  // the original implementation in ezcMailCharsetConverter does not use
  // //translit//ignore with iconv and seems to fail sometimes
  ezcMailCharsetConverter::setConvertMethod(function($text, $originalCharset) {
    if ( $originalCharset === 'unknown-8bit' || $originalCharset === 'x-user-defined' )
    {
      $originalCharset = 'latin1';
    }
    return iconv($originalCharset, 'utf-8//TRANSLIT//IGNORE', $text);
  });

  $html = NULL;
  foreach (json_decode($message['body'], TRUE) as $partJson) {
    if (empty($partJson['headers'])) {
      continue;
    }
    $headers = new ezcMailHeadersHolder($partJson['headers']);
    $matches = [];
    // split a content-type like text/html into $mainType (text) and $subtype (html)
    preg_match_all( '/^(\S+)\/([^;]+)/', $headers['Content-Type'], $matches, PREG_SET_ORDER );
    if (count($matches) == 0) {
      continue;
    }
    $mainType = strtolower($matches[0][1]);
    $subType = strtolower($matches[0][2]);

    // we only care about text parts
    if ($mainType != 'text') {
      continue;
    }

    $parser = new ezcMailTextParser($subType, $headers);
    $parser->parseBody($partJson['text']);
    $part = $parser->finish();
    if ($part->subType == 'html') {
      // we found an HTML part, stop looking for more
      $html = $part->text;
      break;
    }
    if ($part->subType == 'plain') {
      // we can use text/plain as a fallback if no HTML parts are present
      $html = nl2br($part->text);
    }
  }

  // this is a rather ugly hack to force (most) links to open in a new tab
  $html = str_replace('<a ', '<a target="_blank" ', $html);

  return civicrm_api3_create_success($html, $params);
}
