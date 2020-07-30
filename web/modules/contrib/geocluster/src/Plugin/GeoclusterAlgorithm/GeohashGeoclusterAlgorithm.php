<?php

namespace Drupal\geocluster\Plugin\GeoclusterAlgorithm;

use Drupal\geocluster\Plugin\GeoclusterAlgorithmBase;
use Drupal\geocluster\Utility\GeohashHelper;
use Drupal\geocluster\Utility\GeoclusterHelper;

/**
 * Abstract definition of a Geohash algorithm.
 */
abstract class GeohashGeoclusterAlgorithm extends GeoclusterAlgorithmBase {

  /**
   * No default implementation, to be implemented by algorithm, if needed.
   */
  public function preExecute() {
  }

  /**
   * Perform clustering on the aggregated views result set.
   */
  public function postExecute() {
    module_load_include('inc', 'geofield', 'vendor/phayes/geophp/geoPHP');
    $results_by_geohash = $this->preClusterByGeohash();

    $this->clusterByNeighborCheck($results_by_geohash);

    $this->finalizeClusters();
  }

  /**
   * Create initial clustering from geohash grid.
   *
   * No default implementation, to be implemented by algorithm, if needed.
   */
  protected function preClusterByGeohash() {
  }

  /**
   * Create final clusters by checking for overlapping neighbors.
   *
   * @param array $results_by_geohash
   *   The array of geohashes containing count & centroid.
   */
  protected function clusterByNeighborCheck(array &$results_by_geohash) {
    ksort($results_by_geohash);
    foreach ($results_by_geohash as $current_hash => &$results) {
      if (empty($current_hash)) {
        continue;
      }
      $item_key = current(array_keys($results));
      // Check top right neighbor hashes for overlapping points.
      // Top-right is enough because by the way geohash is structured,
      // future geohashes are always top, topright or right.
      $hash_stack = GeohashHelper::getTopRightNeighbors($current_hash);
      foreach ($hash_stack as $hash) {
        if (isset($results_by_geohash[$hash])) {
          $other_item_key = current(array_keys($results_by_geohash[$hash]));
          $geometry = $this->getGeometry($this->values[$item_key]);
          $other_geometry = $this->getGeometry($this->values[$other_item_key]);
          if ($this->shouldCluster($geometry, $other_geometry)) {
            $this->addCluster($item_key, $other_item_key, $current_hash, $hash, $results_by_geohash);
            if (!isset($results_by_geohash[$current_hash])) {
              continue 2;
            }
          }
        }
      }
    }
  }

  /**
   * Finalize clusters.
   */
  protected function finalizeClusters() {
    foreach ($this->values as &$value) {
      if ($value->geocluster_count > 1) {
        $value->clustered = TRUE;
      }
    }
  }

  /* ALGORITHM HELPERS */

  /**
   * Initialize the cluster from a coordinate.
   *
   * @param object $value
   *   An object containing a geocluster latitude & longitude.
   *
   * @return int
   *   Returns the number of points in a cluster.
   */
  protected function initCluster(&$value) {
    $point = \Drupal::service('geofield.wkt_generator')->wktBuildPoint([$value->geocluster_lon, $value->geocluster_lat]);
    $value->geocluster_geometry = \Drupal::service('geofield.geophp')->load($point);
    $value->clustered = TRUE;
    return $value->geocluster_count;
  }

  /**
   * Determine if two geofields should be clustered as of their distance.
   *
   * @param object $geometry
   *   One geometry point.
   * @param object $otherGeometry
   *   Another geometry point.
   *
   * @return bool
   *   Returns true if we need to cluster the 2 geofields, false otherwise.
   */
  protected function shouldCluster($geometry, $otherGeometry) {
    // Calculate distance.
    $distance = GeoclusterHelper::distancePixels($geometry, $otherGeometry, $this->resolution);
    return $distance <= $this->clusterDistance;
  }

  /**
   * Cluster two given rows.
   *
   * @param int $row_id
   *   The first row to be clustered.
   * @param int $row_id2
   *   The second row to be clustered.
   * @param string $hash
   *   The hash associated to the first row.
   * @param string $hash2
   *   The hash associated to the second row.
   * @param array $entities_by_geohash
   *   The array of result.
   */
  protected function addCluster($row_id, $row_id2, $hash, $hash2, array &$entities_by_geohash) {
    $result1 = &$this->values[$row_id]; $result2 = &$this->values[$row_id2];

    // Calculate new center from all points.
    $center = GeoclusterHelper::getCenter([$result1->geocluster_geometry, $result2->geocluster_geometry], [$result1->geocluster_count, $result2->geocluster_count]);
    $result1->geocluster_geometry = $center;
    $result1->geocluster_lat = $center->getY();
    $result1->geocluster_lon = $center->getX();

    // Merge cluster data.
    $result1->geocluster_ids .= ',' . $result2->geocluster_ids;
    $result1->geocluster_count += $result2->geocluster_count;

    // Remove other result data that has been merged into the cluster.
    unset($this->values[$row_id2]);
    unset($entities_by_geohash[$hash2][$row_id2]);
    if (count($entities_by_geohash[$hash2]) == 0) {
      unset($entities_by_geohash[$hash2]);
    }
  }

  /**
   * Return the geometry associated to a result.
   *
   * @param object $result
   *   A geocluster object.
   *
   * @return object
   *   Returns the geometry associated to a result.
   */
  protected function getGeometry($result) {
    return $result->geocluster_geometry;
  }

}
