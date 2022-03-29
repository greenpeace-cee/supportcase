<?php

/**
 * Handles all actions of entities
 */
class CRM_Supportcase_Install_Install {

  /**
   * Creates all entities
   */
  public static function createEntities() {
    (new CRM_Supportcase_Install_Entity_Group())->createAll();
    (new CRM_Supportcase_Install_Entity_OptionGroup())->createAll();
    (new CRM_Supportcase_Install_Entity_OptionValue())->createAll();
    (new CRM_Supportcase_Install_Entity_CaseType())->createAll();
    (new CRM_Supportcase_Install_Entity_CustomGroup())->createAll();
    (new CRM_Supportcase_Install_Entity_CustomField())->createAll();
    (new CRM_Supportcase_Install_Entity_TagSet())->createAll();
    (new CRM_Supportcase_Install_Entity_Tag())->createAll();
    (new CRM_Supportcase_Install_Entity_Job())->createAll();
    (new CRM_Supportcase_Install_Entity_LocationType())->createAll();
    (new CRM_Supportcase_Install_Entity_RelationshipType())->createAll();
    (new CRM_Supportcase_Install_Entity_WysiwygPreset())->createAll();
  }

  /**
   * Disables all entities
   */
  public static function disableEntities() {
    (new CRM_Supportcase_Install_Entity_Group())->disableAll();
    (new CRM_Supportcase_Install_Entity_OptionGroup())->disableAll();
    (new CRM_Supportcase_Install_Entity_OptionValue())->disableAll();
    (new CRM_Supportcase_Install_Entity_CaseType())->disableAll();
    (new CRM_Supportcase_Install_Entity_CustomGroup())->disableAll();
    (new CRM_Supportcase_Install_Entity_CustomField())->disableAll();
    (new CRM_Supportcase_Install_Entity_Job())->disableAll();
    (new CRM_Supportcase_Install_Entity_LocationType())->disableAll();
    (new CRM_Supportcase_Install_Entity_RelationshipType())->disableAll();
  }

  /**
   * Enables all entities
   */
  public static function enableEntities() {
    (new CRM_Supportcase_Install_Entity_Group())->enableAll();
    (new CRM_Supportcase_Install_Entity_OptionGroup())->enableAll();
    (new CRM_Supportcase_Install_Entity_OptionValue())->enableAll();
    (new CRM_Supportcase_Install_Entity_CaseType())->enableAll();
    (new CRM_Supportcase_Install_Entity_CustomGroup())->enableAll();
    (new CRM_Supportcase_Install_Entity_CustomField())->enableAll();
    (new CRM_Supportcase_Install_Entity_Job())->enableAll();
    (new CRM_Supportcase_Install_Entity_LocationType())->enableAll();
    (new CRM_Supportcase_Install_Entity_RelationshipType())->enableAll();
  }

  /**
   * Deletes all entities
   */
  public static function deleteEntities() {
    //TODO: Do we need to delete any entities? Maybe will be enough disable all entities.
  }

}
