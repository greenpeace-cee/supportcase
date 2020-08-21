<?php
use CRM_Supportcase_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Supportcase_Upgrader extends CRM_Supportcase_Upgrader_Base {

  /**
   * Runs while extension is installing
   */
  public function install() {}

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

}
