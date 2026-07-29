<?php

class CRM_Supportcase_Form_CreateSupportcaseContact extends CRM_Core_Form {

  public function getTitle(): string {
    return ts('Create Support Case Contact');
  }

  public function buildQuickForm(): void {
    Civi::resources()->addStyleFile('supportcase', 'css/ang/element.css');
    $this->add('text', 'first_name', 'First name', ['class' => 'spc__input spc--width-100-percent'], TRUE);
    $this->add('text', 'last_name', 'Last name', ['class' => 'spc__input spc--width-100-percent'], TRUE);
    $this->add('email', 'email', 'Email', ['class' => 'spc__input spc--width-100-percent'], TRUE);

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

    if (strlen($values['first_name']) > 64) {
      $errors["first_name"] = 'To long. Type less than 64.';
    }

    if (strlen($values['last_name']) > 64) {
      $errors["last_name"] = 'To long. Type less than 64.';
    }

    // table civicrm_email.email	varchar(254)
    if (strlen($values['email']) > 254) {
      $errors["email"] = 'To long. Type less than 254.';
    }

    return empty($errors) ? TRUE : $errors;
  }

  public function postProcess() {
    $values = $this->exportValues();
    $contact = $this->createContact($values['first_name'], $values['last_name'], $values['email']);
    $isRunInModalWindow = (!empty($this->controller->_print) && $this->controller->_print === 'json');

    if ($isRunInModalWindow) {
      http_response_code(200);
      CRM_Utils_JSON::output([
        'extra' => [
          'display_name' => $contact['display_name'],
          'sort_name' => $contact['sort_name'],
          'id' => $contact['id'],
        ],
        'action' => CRM_Core_Action::ADD,
        'id' => $contact['id'],
        "buttonName" => "next",
        'userContext' => CRM_Utils_System::url('civicrm/supportcase/create-new-support-case-contact', "reset=1&context=dialog", NULL, NULL, FALSE, FALSE, TRUE),
        'status' => 'success',
        'title' => "New support case contact"
      ]);
      return;
    }

    CRM_Core_Session::setStatus('Contact is created! Id = ' . $contact['id'] , 'Success', 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/supportcase'));
  }

  /**
   * @param $firstName
   * @param $lastName
   * @param $email
   * @return array
   */
  private function createContact($firstName, $lastName, $email): array {
    $contact = civicrm_api3('Contact', 'create', [
      'first_name' => $firstName,
      'last_name' => $lastName,
      'contact_type' => "Individual",
    ]);

    civicrm_api3('Email', 'create', [
      'contact_id' => $contact['id'],
      "location_type_id" => CRM_Supportcase_Install_Entity_LocationType::SUPPORT,
      'is_primary' => 1,
      'email' => $email,
    ]);

    foreach ($contact['values'] as $contact) {
      return $contact;
    }

    return [];
  }

}
