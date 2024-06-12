<?php

class CRM_Supportcase_Form_AddCase extends CRM_Core_Form {

  public function getTitle() {
    return ts('Create Support Case');
  }

  public function setDefaultValues() {
    $defaultValues = [];
    $prefillEmailId = CRM_Utils_Request::retrieve('prefill_email_id', 'String', $this);
    $dashboardSearchQfKey = CRM_Utils_Request::retrieve('dashboardSearchQfKey', 'String');

    if (!empty($dashboardSearchQfKey)) {
      $defaultValues['dashboard_search_qf_key'] = $dashboardSearchQfKey;
    }

    if (!empty($prefillEmailId) && CRM_Supportcase_Utils_Email::isEmailExist($prefillEmailId)) {
      $contactId = CRM_Supportcase_Utils_EmailSearch::getContactIdByEmailId($prefillEmailId);
      if (!empty($contactId)) {
        $defaultValues['client_contact_id'] = $contactId;
      }
    }

    return $defaultValues;
  }

  public function buildQuickForm() {
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/ang/element.css');
    $this->add('text', 'dashboard_search_qf_key');
    $this->add('text', 'prefill_email_id');
    $this->add('text', 'subject', 'Subject', ['class' => 'spc__input spc--width-100-percent'], TRUE);
    $this->add('select', 'category_id', ts('Category'), CRM_Supportcase_Utils_Category::getOptions(), TRUE, ['class' => 'spc__input spc--width-100-percent']);
    $this->addEntityRef(
      'client_contact_id',
      ts('Client'),
      [
        'entity' => 'Contact',
        'multiple' => FALSE,
        'placeholder' => ts('- select client -'),
        'class' => 'spc__input spc--single-select spc--width-100-percent',
        'data-create-links' => json_encode([
          [
            'label' => 'New support case contact',
            'url' => CRM_Utils_System::url('civicrm/supportcase/create-new-support-case-contact', "reset=1&context=dialog", NULL, NULL, FALSE, FALSE, TRUE),
            'type' => 'Individual',
            'icon' => 'fa-user',
          ],
        ]),
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
    $this->redirectToManageCase($case['id']);
  }

  /**
   * @param $caseId
   * @return void
   */
  public function redirectToManageCase($caseId) {
    $angularRoute = 'supportcase/manage-case/' . $caseId;
    $qfKey = CRM_Utils_Request::retrieve('dashboard_search_qf_key', 'String', $this);
    $prefillEmailId = CRM_Utils_Request::retrieve('prefill_email_id', 'String', $this);

    if (!empty($qfKey)) {
      $angularRoute .= '/' . $qfKey;
      if (!empty($prefillEmailId)) {
        $angularRoute .= '/' . $prefillEmailId;
      }
    } else if (!empty($prefillEmailId)) {
      $angularRoute .= '//' . $prefillEmailId;
    }

    $angularUrl = CRM_Utils_System::url('civicrm/a/', NULL, TRUE, $angularRoute);
    CRM_Utils_System::redirect($angularUrl);
  }

  public function cancelAction() {
    $urlParams = [];
    $qfKey = CRM_Utils_Request::retrieve('dashboard_search_qf_key', 'String', $this);
    if (!empty($qfKey)) {
      $urlParams['qfKey'] = $qfKey;
    }

    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/supportcase', $urlParams));
  }

}
