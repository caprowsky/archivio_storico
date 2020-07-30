<?php

namespace Drupal\geocluster\Plugin\GeoclusterAlgorithm;

/**
 * Definition of the MySQLGeohashGeocluster algorithm.
 *
 * @package Drupal\geocluster\Plugin
 *
 * @GeoclusterAlgorithm(
 *   id = "mysql_algorithm",
 *   adminLabel = @Translation("MySQL Geohash geocluster algorithm")
 * )
 */
class MySQLGeohashGeoclusterAlgorithm extends GeohashGeoclusterAlgorithm {

  /**
   * Add the required geohash index to the query.
   *
   * This must be done before the view is built.
   */
  public function beforePreExecute() {
    if ($this->config->getOption('group_by')) {
      $view = $this->config->getView();
      foreach ($view->field as $field) {
        if (isset($field->options) && isset($field->options['type']) && $field->options['type'] == 'geofield_default') {
          // Set the group column to the appropriate geocluster index level.
          $group_column = 'geocluster_index_' . $this->getGeohashLength();
          $field->options['group_column'] = $group_column;
        }
      }
    }
  }

  /**
   * Add geocluster-based aggregation settings before executing the view.
   */
  public function preExecute() {
    if ($this->config->getOption('group_by')) {
      $view = $this->config->getView();
      foreach ($view->field as $field) {
        if (isset($field->options) && isset($field->options['type']) && $field->options['type'] == 'geofield_default') {
          $this->addGeoclusterGroupBySettings($field);
        }
      }
    };
  }

  /**
   * Transform geohash-based, aggregated result into initial clustering.
   */
  protected function preClusterByGeohash() {
    $total_count = 0;
    $results_by_geohash = [];
    $group_field = $this->getClusterFieldAlias();
    foreach ($this->values as $row => &$value) {
      if (!isset($value->$group_field)) {
        continue;
      }
      $hash_prefix = $value->$group_field;
      $total_count += $this->initCluster($value);
      $results_by_geohash[$hash_prefix] = [
        $row => $value,
      ];
    }

    $this->totalItems = $total_count;
    return $results_by_geohash;
  }

  /* HELPERS */

  /**
   * Adds geocluster-specific group_by settings to the query.
   *
   * @param object $field
   *   Either a Standard field object or a GeoclusterHandlerField.
   */
  protected function addGeoclusterGroupBySettings(&$field) {
    // Add additional fields.
    // Add concatenated list of grouped entity ids.
    $this->addFields(['geocluster_ids' => 'entity_id'], 'group_concat', $field);

    // Add count(entity_id).
    $this->addFields(['geocluster_count' => 'entity_id'], 'count', $field);

    // Add center point: avg(lat), avg(lng).
    $avg_fields = [
      'geocluster_lat' => $field->field . '_lat',
      'geocluster_lon' => $field->field . '_lon',
    ];
    $this->addFields($avg_fields, 'avg', $field);
  }

  /**
   * Adds a new field to the view.
   *
   * @param array $additional_fields
   *   An array of additional fields to handle.
   * @param string $function
   *   The name of the aggregate function.
   * @param object $field
   *   Either a Standard field object or a GeoclusterHandlerField.
   */
  protected function addFields(array $additional_fields, $function, &$field) {
    foreach ($additional_fields as $field_key => $additional_field) {
      $params = [];
      if (!empty($function)) {
        $params['function'] = $function;
      }
      $view = $this->config->getView();
      $view->query->addField($field->tableAlias, $additional_field, $field_key, $params);
    }
  }

  /**
   * Returns the alias for the cluster field.
   *
   * @return string
   *   The alias of the clustered field
   */
  protected function getClusterFieldAlias() {
    $name = $this->getClusterFieldName();
    return $this->fieldHandler->aliases[$name];
  }

  /**
   * Returns the name of the cluster field.
   *
   * @return string
   *   The name of the clustered field
   */
  protected function getClusterFieldName() {
    return current($this->fieldHandler->group_fields);
  }

}
