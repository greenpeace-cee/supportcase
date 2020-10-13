<?php

class CRM_Supportcase_Utils_Tags {

  /**
   * Gets tags ids connected to the entity
   *
   * @param $entityId
   * @param $entityTableName
   * @return array
   */
  public static function getTagsIds($entityId, $entityTableName) {
    if (empty($entityId) || empty($entityTableName)) {
      return [];
    }

    $entityTagIds = CRM_Core_BAO_EntityTag::getTag($entityId, $entityTableName);
    $preparedEntityTagIds = [];

    if (!empty($entityTagIds)) {
      foreach ($entityTagIds as $entityTagId) {
        $preparedEntityTagIds[] = $entityTagId;
      }
    }

    return $preparedEntityTagIds;
  }

  /**
   * Gets available tags for entity
   * @param $entityTableName
   * @return array
   */
  public static function getAvailableTags($entityTableName) {
    if (empty($entityTableName)) {
      return [];
    }

    $preparedAvailableTags = [];
    $availableTags = CRM_Core_BAO_Tag::getTagsUsedFor($entityTableName, FALSE);

    if (!empty($availableTags)) {
      foreach ($availableTags as $tagId => $availableTag) {
        $preparedAvailableTags[$tagId] = [
          'id' => $tagId,
          'name' => $availableTag['name'],
          'description' => $availableTag['description'],
          'color' => $availableTag['color'],
        ];
      }
    }

    return $preparedAvailableTags;
  }

  /**
   * Gets tags data connected to the entity
   *
   * @param $entityId
   * @param $entityTableName
   * @return array
   */
  public static function getTags($entityId, $entityTableName) {
    if (empty($entityId) || empty($entityTableName)) {
      return [];
    }

    $availableTags = self::getAvailableTags($entityTableName);
    $tagsIds = self::getTagsIds($entityId, $entityTableName);
    $tags = [];

    foreach ($tagsIds as $tagId) {
      if (!empty($availableTags[$tagId])) {
        $tags[] = $availableTags[$tagId];
      }
    }

    return $tags;
  }

}
