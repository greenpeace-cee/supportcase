/**
 * CKEditor config file for supportcase extension
 */
CKEDITOR.editorConfig = function( config ) {
  config.toolbarGroups = [
    { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
    { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
    { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
    { name: 'forms', groups: [ 'forms' ] },
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
    { name: 'links', groups: [ 'links' ] },
    { name: 'insert', groups: [ 'insert' ] },
    '/',
    { name: 'styles', groups: [ 'styles' ] },
    { name: 'colors', groups: [ 'colors' ] },
    { name: 'tools', groups: [ 'tools' ] },
    { name: 'others', groups: [ 'others' ] },
    { name: 'about', groups: [ 'about' ] }
  ];

  config.removeButtons = 'Subscript,Superscript,Outdent,Blockquote,Link,Unlink,Anchor,Image,Table,HorizontalRule,SpecialChar,Styles,Format,Maximize,About,Indent,Scayt,PasteText,PasteFromWord,Paste';

  config.skin = 'moono';

  config.allowedContent = true;

  config.forcePasteAsPlainText = true;

  config.disableNativeSpellChecker = false;
};
