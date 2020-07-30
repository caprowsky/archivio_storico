<?php

namespace Drupal\geocluster;

use Drupal\geofield\Plugin\Field\FieldType\GeofieldItem;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\geocluster\Utility\GeohashUtils;

/**
 * Extend the 'geofield' field type with geocluster functionality.
 */
class GeoclusterItem extends GeofieldItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
    $field_schema = parent::schema($field);

    // Adds separate columns for the geohash indices, from length 1 to max.
    for ($i = 1; $i <= GeohashUtils::GEOCLUSTER_GEOHASH_LENGTH; $i++) {
      $name = 'geocluster_index_' . $i;
      $field_schema['columns'][$name] = [
        'description' => 'Geocluster index level ' . $i,
        'type' => 'varchar',
        'length' => $i,
        'not null' => FALSE,
      ];

      $field_schema['indexes'][$name] = [$name];
    }

    return $field_schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    // Adds propety definitions for the geohash indexes, from length 1 to max.
    for ($i = 1; $i <= GeohashUtils::GEOCLUSTER_GEOHASH_LENGTH; $i++) {
      $name = 'geocluster_index_' . $i;
      $label = 'Geocluster index lvl ' . $i;
      $properties[$name] = DataDefinition::create('string')->setLabel($label);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    parent::setValue($values, $notify);

    for ($i = GeohashUtils::GEOCLUSTER_GEOHASH_LENGTH; $i > 0; $i--) {
      $name = 'geocluster_index_' . $i;
      $this->$name = $this->geoclusterGetGeohashPrefix($this->geohash, $i);
    }
  }

  /**
   * Get a geohash prefix of a specified, maximum length.
   *
   * @param string $geohash
   *   The geohash string.
   * @param int $length
   *   The length until which we want to have our prefix.
   *
   * @return string
   *   The geohash prefix
   */
  private function geoclusterGetGeohashPrefix($geohash, $length) {
    return substr($geohash, 0, min($length, strlen($geohash)));
  }

  /**
   * {@inheritdoc}
   */
  public function prepareCache() {
  }

}
