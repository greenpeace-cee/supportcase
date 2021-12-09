<?php

class CRM_Supportcase_Install_Entity_WysiwygPreset extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Add CKEditor config file
   */
  public function createAll() {
    $template = dirname(__DIR__) . '/../../../resources/crm-ckeditor-supportcase.js';
    $file = Civi::paths()->getPath(CRM_Admin_Form_CKEditorConfig::CONFIG_FILEPATH . 'supportcase' . '.js');
    file_put_contents($file, file_get_contents($template));
  }

  public function disableAll() {
    throw new BadMethodCallException('disableAll is not supported for CRM_Supportcase_Install_Entity_WysiwygPreset');
  }

  public function enableAll() {
    throw new BadMethodCallException('enableAll is not supported for CRM_Supportcase_Install_Entity_WysiwygPreset');
  }

}
