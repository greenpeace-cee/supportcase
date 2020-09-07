CREATE TABLE IF NOT EXISTS `civicrm_supportcase_case_lock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT ,
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Contact who is locked the case',
  `case_id` int(10) unsigned NOT NULL COMMENT 'The locked case',
  `lock_expire_at` int(10) unsigned NOT NULL COMMENT 'After this time case will be "open"',
  `lock_message` VARCHAR(255) NOT NULL COMMENT 'Message for users about this licking',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
