<?php

/**
 * @file
 * Contains \Drupal\migrando\Plugin\migrate\process\Encode.
 */
namespace Drupal\migrando\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Process latitude and longitude and return the value for the D8 geofield.
 *
 * @MigrateProcessPlugin(
 *   id = "encode"
 * )
 */
class Encode extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return html_entity_decode(htmlspecialchars_decode($value), ENT_QUOTES);
  }

}
