<?php

class CRM_Supportcase_Install_Entity_WysiwygPreset extends CRM_Supportcase_Install_Entity_Base {

  /**
   * Add CKEditor config file
   */
  public function createAll() {
    $template = dirname(__DIR__) . '/../../../resources/crm-ckeditor-supportcase.js';
    $config_filepath = NULL;
    if (class_exists('CRM_Admin_Form_CKEditorConfig')) {
      $config_filepath = CRM_Admin_Form_CKEditorConfig::CONFIG_FILEPATH;
    }
    elseif (class_exists('CRM_Ckeditor4_Form_CKEditorConfig')) {
      $config_filepath = CRM_Ckeditor4_Form_CKEditorConfig::CONFIG_FILEPATH;
    }
    else {
      throw new Exception('Supportcase requires the ckeditor4 extension starting with CiviCRM 5.40');
    }
    $file = Civi::paths()->getPath($config_filepath . 'supportcase' . '.js');
    file_put_contents($file, file_get_contents($template));
  }

  public function disableAll() {
    throw new BadMethodCallException('disableAll is not supported for CRM_Supportcase_Install_Entity_WysiwygPreset');
  }

  public function enableAll() {
    throw new BadMethodCallException('enableAll is not supported for CRM_Supportcase_Install_Entity_WysiwygPreset');
  }

}
