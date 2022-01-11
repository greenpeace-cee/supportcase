<?php

class CRM_Supportcase_Form_AddCase extends CRM_Core_Form {

  public function getTitle() {
    return ts('Create Support Case');
  }

  public function buildQuickForm() {
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/ang/element.css');
    $this->add('text', 'subject', 'Subject', ['class' => 'spc__input spc--width-100-percent'], TRUE);
    $this->add('select', 'category_id', ts('Category'), CRM_Supportcase_Utils_Category::getOptions(), TRUE, ['class' => 'spc__input spc--width-100-percent']);
    $this->addEntityRef(
      'client_contact_id',
      ts('Client'),
      [
        'entity' => 'Contact',
        'multiple' => FALSE,
        'create' => TRUE,
        'placeholder' => ts('- select client -'),
        'class' => 'spc__input spc--single-select spc--width-100-percent'
      ],
      TRUE
    );

    $this->addButtons(
      [
        [
          'type' => 'done',
          'name' => 'Done',
          'isDefault' => true,
        ],
        [
          'type' => 'cancel',
          'name' => 'Cancel',
          'isDefault' => true,
        ],
      ]
    );
  }

  public function addRules() {
    $this->addFormRule([self::class, 'validateForm']);
  }

  public static function validateForm($values) {
    $errors = [];

    $categories = CRM_Supportcase_Utils_Category::getOptions();
    if (empty($categories[$values['category_id']])) {
      $errors["category_id"] = 'Case category does not exist.';
    }

    if (strlen($values['subject']) > 255) {
      $errors["subject"] = 'To long subject. Type less than 255.';
    }

    try {
      civicrm_api3('Contact', 'getsingle', [
        'id' => $values['client_contact_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      $errors["client_contact_id"] = 'Contact does not exist.';
    }

    return empty($errors) ? TRUE : $errors;
  }

  public function postProcess() {
    $values = $this->exportValues();

    try {
      $case = civicrm_api3('SupportcaseManageCase', 'create', [
        'client_contact_id' => $values['client_contact_id'],
        'subject' => $values['subject'],
        'category_id' => $values['category_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), 'Error', 'error');
      throw new CiviCRM_API3_Exception($e->getMessage());
    }

    CRM_Core_Session::setStatus('Supportcase is created!', 'Success', 'success');
    $angularUrl = CRM_Utils_System::url('civicrm/supportcase/manage-case-angular-wrap', ['case_id' => $case['id']]);
    CRM_Utils_System::redirect($angularUrl);
  }

}
