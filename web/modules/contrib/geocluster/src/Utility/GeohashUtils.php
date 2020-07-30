<?php

namespace Drupal\geocluster\Utility;

/**
 * Provides module internal helper methods.
 *
 * @ingroup utility
 */

/**
 * Implementation of geohasing functions.
 *
 * Based on http://github.com/davetroy/geohash-js/blob/master/geohash.js
 * see https://github.com/islam-dev/waqt.org/blob/master/geo.php.
 */
class GeohashUtils {
  /**
   * Geohash length.
   *
   * @var int
   */
  const GEOCLUSTER_GEOHASH_LENGTH = 12;

  /**
   * Base 32 definition.
   *
   * @var string
   */
  private static $base32 = '0123456789bcdefghjkmnpqrstuvwxyz';

  /**
   * Neighbors initialisation.
   *
   * @var array
   */
  private static $neighbors = [
    'odd' => [
      'bottom' => '238967debc01fg45kmstqrwxuvhjyznp',
      'top' => 'bc01fg45238967deuvhjyznpkmstqrwx',
      'left' => '14365h7k9dcfesgujnmqp0r2twvyx8zb',
      'right' => 'p0r21436x8zb9dcf5h7kjnmqesgutwvy',
    ],
    'even' => [
      'right' => 'bc01fg45238967deuvhjyznpkmstqrwx',
      'left' => '238967debc01fg45kmstqrwxuvhjyznp',
      'top' => 'p0r21436x8zb9dcf5h7kjnmqesgutwvy',
      'bottom' => '14365h7k9dcfesgujnmqp0r2twvyx8zb',
    ],
  ];

  /**
   * Borders initialization.
   *
   * @var array
   */
  private static $borders = [
    'odd' => [
      'bottom' => '0145hjnp',
      'top' => 'bcfguvyz',
      'left' => '028b',
      'right' => 'prxz',
    ],
    'even' => [
      'right' => 'bcfguvyz',
      'left' => '0145hjnp',
      'top' => 'prxz',
      'bottom' => '028b',
    ],
  ];

  /**
   * Bits simplification.
   *
   * @var array
   */
  private static $bits = [16, 8, 4, 2, 1];

  /**
   * Latitude range.
   *
   * @var array
   */
  private static $latRange = [-90.0, 90.0];

  /**
   * Lonngitude range.
   *
   * @var array
   */
  private static $lngRange = [-180.0, 180.0];

  /**
   * Calculate the geohash of a neighbor.
   *
   * @param string $geohash
   *   A geohash string.
   * @param string $direction
   *   Either top, bottom, right or left.
   *
   * @return string
   *   Returns the geohash of a neighbor.
   */
  public static function calcNeighbors($geohash, $direction) {
    $geohash = strtolower($geohash);
    $last = $geohash[strlen($geohash) - 1];
    $type = (strlen($geohash) % 2) ? 'odd' : 'even';
    $base = substr($geohash, 0, strlen($geohash) - 1);

    $b = GeohashUtils::$borders[$type];
    $n = GeohashUtils::$neighbors[$type];
    $val = strpos($b[$direction], $last);
    if (($val !== FALSE) && ($val != -1) && strlen($base) > 0) {
      $base = GeohashUtils::calcNeighbors($base, $direction);
    }

    $ni = strpos($n[$direction], $last);
    return $base . GeohashUtils::$base32[$ni];
  }

}
