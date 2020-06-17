<?php

class CRM_Supportcase_BAO_Query extends CRM_Case_BAO_Query {

  public static function getSearchFieldMetadata() {
    $metadata = parent::getSearchFieldMetadata();
    $metadata['case_agents'] = [
      'title' => ts('Involved Agent(s)'),
      'type' => CRM_Utils_Type::T_INT,
      'is_pseudofield' => TRUE,
      'html' => ['type' => 'Select2'],
    ];
    $metadata['case_agent'] = [
      'title' => ts('Client'),
      'type' => CRM_Utils_Type::T_INT,
      'is_pseudofield' => TRUE,
      'html' => ['type' => 'Select2'],
    ];
    $metadata['case_keyword'] = [
      'title' => ts('Keyword'),
      'type' => CRM_Utils_Type::T_STRING,
      'is_pseudofield' => TRUE,
      'html' => ['type' => 'text'],
    ];

    if (isset($metadata['case_id'])) {
      $metadata['case_id']['title'] = ts('Search by Case ID');
      //TODO: Make this filed only integer
    }

    return $metadata;
  }

  /**
   * Add all the elements shared between case search and advanced search.
   *
   * @param CRM_Case_Form_Search $form
   */
  public static function buildSearchForm(&$form) {
    $form->addSearchFieldMetadata(['Case' => self::getSearchFieldMetadata()]);
    $form->addFormFieldsFromMetadata();
    $caseTags = CRM_Core_BAO_Tag::getColorTags('civicrm_case');
    if ($caseTags) {
      $form->add('select2', 'case_tags', ts('Case Tag(s)'), $caseTags, FALSE, ['class' => 'big', 'placeholder' => ts('- select -'), 'multiple' => TRUE]);
    }
    // $form->add('select2', 'case_agents', ts('Involved Agent(s)'), $caseAgents, FALSE, ['class' => 'big', 'placeholder' => ts('- select -'), 'multiple' => TRUE]);
    $form->add('text', 'case_keyword', ts('Keyword'), ['class' => 'huge', 'placeholder' => 'Search within subject or message']);
    $form->addEntityRef('case_agents', ts('Involved Agent(s)'), ['multiple' => TRUE, 'api' => ['params' => ['group' => 'support_agent']]], FALSE, ['class' => 'big']);
    $form->addEntityRef('case_client', ts('Client(s)'), ['multiple' => TRUE], FALSE, ['class' => 'big']);
    $form->getElement('case_status_id')->setAttribute('class', 'huge crm-select2');

    $parentNames = CRM_Core_BAO_Tag::getTagSet('civicrm_case');
    CRM_Core_Form_Tag::buildQuickForm($form, $parentNames, 'civicrm_case', NULL, TRUE, FALSE);
  }

}
