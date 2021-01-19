<?php
use CRM_Supportcase_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Supportcase_Upgrader extends CRM_Supportcase_Upgrader_Base {

  /**
   * Runs while extension is installing
   */
  public function install() {
    $this->validateConfiguration();
  }

  /**
   * Runs after extension is installed
   */
  public function onPostInstall() {
    CRM_Supportcase_Install_Install::createEntities();
  }

  /**
   * Runs while extension is uninstalling
   */
  public function uninstall() {
    CRM_Supportcase_Install_Install::deleteEntities();
  }

  /**
   * Runs while extension is enabling
   */
  public function enable() {
    CRM_Supportcase_Install_Install::enableEntities();
  }

  /**
   * Runs while extension is disabling
   */
  public function disable() {
    CRM_Supportcase_Install_Install::disableEntities();
  }

  /**
   * Validates configuration requires to use the extension
   */
  private function validateConfiguration() {
    $isCaseComponentEnabled = CRM_Case_BAO_Case::enabled();
    $title = ts('Support case installation');
    if (!$isCaseComponentEnabled) {
      $message = ts('Case component is disabled. To correctly work with extension please enable "Case" component. See "Administer->Configuration Checklist->Enable Components".');
      CRM_Core_Session::setStatus($message, $title, 'warning');
    }

    $configured = CRM_Case_BAO_Case::isCaseConfigured();
    if (!$configured['configured']) {
      $message = ts('Case is  not configured. To correctly work with extension please configure "Case" component.');
      CRM_Core_Session::setStatus($message, $title, 'warning');
    }

    if (CRM_Supportcase_Utils_Setting::isPopupFormsEnabled()) {
      $message = ts('CiviCRM "Enable Popup Forms" setting is disabled. To correctly work with extension please enable the setting. See "Administer->Configuration Checklist->Display Preferences".');
      CRM_Core_Session::setStatus($message, $title, 'warning');
    }
  }

  public function upgrade_0001() {
    $this->ctx->log->info('Applying update 0001. Install new tag and tag set.');
    (new CRM_Supportcase_Install_Entity_TagSet())->createAll();
    (new CRM_Supportcase_Install_Entity_Tag())->createAll();

    return TRUE;
  }

  public function upgrade_0002() {
    $this->ctx->log->info('Applying update 0002. Install new Job.');
    (new CRM_Supportcase_Install_Entity_Job())->createAll();

    return TRUE;
  }

  public function upgrade_0003() {
    $this->ctx->log->info('Applying update 0003. Install new tag.');
    (new CRM_Supportcase_Install_Entity_Tag())->createAll();

    return TRUE;
  }

}
