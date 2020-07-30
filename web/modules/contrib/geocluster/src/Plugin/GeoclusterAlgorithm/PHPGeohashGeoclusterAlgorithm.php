<?php

namespace Drupal\geocluster\Plugin\GeoclusterAlgorithm;

/**
 * Definition of the PHPGeohashGeocluster algorithm.
 *
 * @package Drupal\geocluster\Plugin
 *
 * @GeoclusterAlgorithm(
 *   id = "php_algorithm",
 *   adminLabel = @Translation("Php Geohash geocluster algorithm")
 * )
 */
class PHPGeohashGeoclusterAlgorithm extends GeohashGeoclusterAlgorithm {

  /**
   * No pre execution step needed for php based clustering.
   */
  public function preExecute() {
  }

  /**
   * Create clusters from geohash grid.
   */
  protected function preClusterByGeohash() {
    $this->totalItems = count($this->values);
    // Prepare input data & parameters.
    $entities_by_type = $this->entitiesByType();

    // Generate geohash-based pre-clusters.
    $entities_by_geohash = $this->loadEntityFields($entities_by_type, $this->geohashLength);

    // Loop over geohash-based pre-clusters to create real clusters.
    foreach ($entities_by_geohash as $current_hash => &$entities) {
      $cluster_id = NULL;
      // Add all points within the current geohash to a cluster.
      foreach ($entities as $key => &$entity) {
        // Prepare data.
        $value = &$this->values[$key];
        $value->geocluster_ids = $value->{$this->fieldHandler->field_alias};
        $value->geocluster_count = 1;
        $value->geocluster_lon = $value->geocluster_geometry->getX();
        $value->geocluster_lat = $value->geocluster_geometry->getY();
        // Either create a new cluster, or.
        if (!isset($cluster_id)) {
          $this->initCluster($value);
          $cluster_id = $key;
        }
        else {
          $this->addCluster($cluster_id, $key, $current_hash, $current_hash, $entities_by_geohash);
        }
      }
    }

    return $entities_by_geohash;
  }

  /* HELPERS */

  /**
   * Divide the entity ids by entity type, so they can be loaded in bulk.
   *
   * @return array
   *   An array containing all the entities grouped by type
   */
  public function entitiesByType() {
    $entities_by_type = [];
    foreach ($this->values as $key => $object) {
      if (isset($object->{$this->fieldHandler->field_alias}) && !isset($this->values[$key]->_field_data[$this->fieldHandler->field_alias])) {
        $entity_type = $object->{$this->fieldHandler->aliases['entity_type']};
        if (empty($this->fieldHandler->definition['is revision'])) {
          $entity_id = $object->{$this->fieldHandler->field_alias};
          $entities_by_type[$entity_type][$key] = $entity_id;
        }
        else {
          $revision_id = $object->{$this->fieldHandler->field_alias};
          $entity_id = $object->{$this->fieldHandler->aliases['entity_id']};
          $entities_by_type[$entity_type][$key] = [$entity_id, $revision_id];
        }
      }
    }
    return $entities_by_type;
  }

  /**
   * Load only the field data required for geoclustering.
   *
   * This saves us unnecessary entity loads.
   *
   * @return array
   *   An array containing all the entities grouped by geohash key
   */
  public function loadEntityFields($entities_by_type, $geohash_length) {
    foreach ($entities_by_type as $entity_type => $my_entities) {
      // Use EFQ for preparing entities to be used in field_attach_load().
      $query = new EntityFieldQuery();
      $query->entityCondition('entity_type', 'node');
      $query->entityCondition('entity_id', $my_entities, 'IN');
      $result = $query->execute();
      $entities = $result[$entity_type];
      field_attach_load(
        $entity_type,
        $entities,
        FIELD_LOAD_CURRENT,
        ['field_id' => $this->fieldHandler->field_info['id']]
      );

      /* @todo handle revisions? */

      $keys = $my_entities;
      $entities_by_geohash = [];

      foreach ($keys as $key => $entity_id) {
        // If this is a revision, load the revision instead.
        if (isset($entities[$entity_id])) {
          $this->values[$key]->_field_data[$this->fieldHandler->field_alias] = [
            'entity_type' => $entity_type,
            'entity' => $entities[$entity_id],
          ];

          $geofield = $this->getGeofieldWithGeometry($this->values[$key]);
          $geohash_key = substr($geofield['geohash'], 0, $geohash_length);
          if (!isset($entities_by_geohash[$geohash_key])) {
            $entities_by_geohash[$geohash_key] = [];
          }
          $entities_by_geohash[$geohash_key][$key] = $entities[$entity_id];
        }
      }
    }
    ksort($entities_by_geohash);
    return $entities_by_geohash;
  }

  /**
   * Helper function to get the geofield with its geometry for a given result.
   *
   * Geometry will only we loaded once and stored in the geofield.
   *
   * @param object $value
   *   The current result row value set.
   *
   * @return object
   *   Returns a geofield object.
   */
  public function &getGeofieldWithGeometry(&$value) {
    $entity = &$value->_field_data[$this->fieldHandler->field_alias]['entity'];

    $geofield = &$entity->{$this->fieldHandler->field_info['field_name']}[LANGUAGE_NONE][0];
    if (!isset($value->geocluster_geometry)) {
      $value->geocluster_geometry = geoPHP::load($geofield['geom']);
    }
    return $geofield;
  }

}
