<?php

namespace Drupal\geocluster\Utility;

/**
 * Provides module internal helper methods.
 *
 * @ingroup utility
 */
class GeohashHelper {

  /**
   * Number of characters in the geohash.
   */
  const GEOHASH_PRECISION = 12;

  /**
   * Return only top-right neighbors according to the structure of geohash.
   *
   * @param string $geohash
   *   A geohash string.
   *
   * @return array
   *   Returns an array of top-right neighbors.
   */
  public static function getTopRightNeighbors($geohash) {
    $neighbors = [];
    $top = GeohashUtils::calcNeighbors($geohash, 'top');
    $neighbors[0] = GeohashUtils::calcNeighbors($top, 'left');
    $neighbors[1] = $top;
    $neighbors[2] = GeohashUtils::calcNeighbors($top, 'right');
    $neighbors[3] = GeohashUtils::calcNeighbors($geohash, 'right');
    return $neighbors;
  }

  /**
   * Calculate geohash length for clustering by a specified distance in pixels.
   *
   * @param float $cluster_distance
   *   The cluster distance in pixels.
   * @param int $resolution
   *   The zoom level.
   *
   * @return int
   *   Return the length sought.
   */
  public static function lengthFromDistance($cluster_distance, $resolution) {
    $cluster_distance_meters = $cluster_distance * $resolution;
    $x = $y = $cluster_distance_meters;
    list($width, $height) = GeoclusterHelper::backwardMercator($x, $y);

    $hashLen = GeohashHelper::lookupHashLenForWidthHeight($width, $height);
    if ($hashLen == self::GEOHASH_PRECISION) {
      return $hashLen;
    }
    return $hashLen + 1;
  }

  /**
   * Return a geohash length that has width & height >= specified arguments.
   *
   * Based on solr2155.lucene.spatial.geohash.GeoHashUtils.
   *
   * @param float $width
   *   The width in degrees.
   * @param float $height
   *   The height in degrees.
   *
   * @return int
   *   Return the geohash length.
   */
  public static function lookupHashLenForWidthHeight($width, $height) {
    list ($hashLenToLatHeight, $hashLenToLonWidth) = GeohashHelper::getHashLenConversions();
    // Loop through hash length arrays from beginning till we find one.
    for ($len = 1; $len <= self::GEOHASH_PRECISION; $len++) {
      $latHeight = $hashLenToLatHeight[$len];
      $lonWidth = $hashLenToLonWidth[$len];
      if ($latHeight < $height || $lonWidth < $width) {
        // Previous length is big enough to encompass specified width & height.
        return $len - 1;
      }
    }
    return self::GEOHASH_PRECISION;
  }

  /**
   * Based on solr2155.lucene.spatial.geohash.GeoHashUtils.
   *
   * See the table at http://en.wikipedia.org/wiki/Geohash.
   *
   * @return array
   *   Return an array of degrees for each geohash length.
   */
  public static function getHashLenConversions() {
    // @todo: static, cache?
    $hashLenToLatHeight = [90 * 2];
    $hashLenToLonWidth = [180 * 2];
    $even = FALSE;
    for ($i = 1; $i <= self::GEOHASH_PRECISION; $i++) {
      $hashLenToLatHeight[$i] = $hashLenToLatHeight[$i - 1] / ($even ? 8 : 4);
      $hashLenToLonWidth[$i] = $hashLenToLonWidth[$i - 1] / ($even ? 4 : 8);
      $even = !$even;
    }
    return [
      $hashLenToLatHeight,
      $hashLenToLonWidth,
    ];
  }

}
