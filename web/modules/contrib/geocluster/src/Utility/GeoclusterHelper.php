<?php

namespace Drupal\geocluster\Utility;

/**
 * Provides module internal helper methods.
 *
 * @ingroup utility
 */
class GeoclusterHelper {

  /**
   * Resolutions indexed by zoom levels.
   *
   * The resolutions are in meters / pixel, so the most common use is to divide
   * the distance between points by the resolution in order to determine the
   * number of pixels between the features.
   *
   * @return array
   *   An array of resolutions indexed by zoom levels.
   */
  public static function resolutions() {
    // @todo: static, cache?
    $r = [];

    // Meters per pixel.
    // https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames#Resolution_and_Scale
    $maxResolution = 156543;
    for ($zoom = 0; $zoom <= 30; ++$zoom) {
      $r[$zoom] = $maxResolution / pow(2, $zoom);
    }
    return $r;
  }

  /**
   * Mercator projection correction.
   *
   * Dumb implementation to incorporate pixel variation with latitude
   * on the mercator projection.
   *
   * @param float $lat
   *   The latitude.
   *
   * @return float
   *   The correction factor.
   */
  public static function pixelCorrection($lat) {
    /* Todo: use a valid implementation instead of the current guessing.

    Current implementation is based on the observation:
    lat = 0 => output is correct
    lat = 48 => output is 223 pixels distance instead of 335 in reality.
     */
    return 1 + (335.0 / 223.271875276 - 1) * ((float) (abs($lat)) / 47.9899);
  }

  /**
   * Calculate the distance between two given points in pixels.
   *
   * This depends on the resolution (zoom level) they are viewed in.
   *
   * @param object $geometry
   *   First point geometry object.
   * @param object $otherGeometry
   *   Second point geometry object.
   * @param float $resolution
   *   The current resolution.
   *
   * @return float
   *   Returns the distance in pixel.
   */
  public static function distancePixels($geometry, $otherGeometry, $resolution) {
    $distance = GeoclusterHelper::distanceHaversine($geometry, $otherGeometry);
    $distancePixels = $distance / $resolution * GeoclusterHelper::pixelCorrection($geometry->getY());
    return $distancePixels;
  }

  /**
   * Calculate the distance between two given points in meters.
   *
   * Based on http://www.codecodex.com/wiki/Calculate_Distance_Between_Two_Points_on_a_Globe#PHP.
   *
   * @param object $geometry
   *   First point geometry object.
   * @param object $otherGeometry
   *   Second point geometry object.
   *
   * @return int
   *   Returns the distance in meters.
   */
  public static function distanceHaversine($geometry, $otherGeometry) {
    $long_1 = (float) $geometry->getX();
    $lat_1 = (float) $geometry->getY();
    $long_2 = (float) $otherGeometry->getX();
    $lat_2 = (float) $otherGeometry->getY();

    // In meters.
    $earth_radius = GEOFIELD_KILOMETERS * 1000;

    $dLat = deg2rad($lat_2 - $lat_1);
    $dLon = deg2rad($long_2 - $long_1);

    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat_1)) * cos(deg2rad($lat_2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;

    return $d;
  }

  /**
   * Convert from degrees to meters.
   *
   * Based on
   * http://dev.openlayers.org/docs/files/OpenLayers/Layer/SphericalMercator-js.html
   * https://github.com/openlayers/openlayers/blob/master/lib/OpenLayers/Projection.js#L278
   *
   * @param float $lon
   *   Longitude.
   * @param float $lat
   *   Latitude.
   *
   * @return array
   *   Returns an array representing the point's position in meters.
   */
  public static function forwardMercator($lon, $lat) {
    $pole = 20037508.34;
    $x = $lon * $pole / 180;
    $y = log(tan((90 + $lat) * pi() / 360)) / pi() * $pole;
    return ['x' => $x, 'y' => $y];
  }

  /**
   * Convert from meters (Spherical Mercator) to degrees (EPSG:4326).
   *
   *  Based on https://github.com/mapbox/clustr/blob/gh-pages/src/clustr.js.
   *
   *  also see
   *  http://dev.openlayers.org/docs/files/OpenLayers/Layer/SphericalMercator-js.html
   *  https://github.com/openlayers/openlayers/blob/master/lib/OpenLayers/Projection.js#L278
   *
   * @param float $x
   *   Position of the point on the X axis.
   * @param float $y
   *   Position of the point on the X axis.
   *
   * @return array
   *   Returns array(lon,lat) in degrees.
   */
  public static function backwardMercator($x, $y) {
    $r2d = 180 / pi();
    $a = 6378137;
    return [
      $x * $r2d / $a,
      ((pi() * 0.5) - 2.0 * atan(exp(-$y / $a))) * $r2d,
    ];
  }

  /**
   * Get the barycenter of all geometries.
   *
   * @param array $geometries
   *   An array of geometries.
   * @param array $itemFactor
   *   An array of weight of each geometry.
   *
   * @return object
   *   Returns a point object representing the center.
   */
  public static function getCenter(array $geometries, array $itemFactor = []) {
    $lat = 0;
    $lon = 0;
    $len = count($geometries);
    $totalFactor = 0;
    for ($i = 0; $i < $len; $i++) {
      $geometry = $geometries[$i];
      $factor = $itemFactor[$i] ?? 1;
      $lat += $geometry->getY() * $factor;
      $lon += $geometry->getX() * $factor;
      $totalFactor += $factor;
    }
    $lat = $lat / $totalFactor;
    $lon = $lon / $totalFactor;
    $point = \Drupal::service('geofield.wkt_generator')->wktBuildPoint([$lon, $lat]);
    $center = \Drupal::service('geofield.geophp')->load($point);
    return $center;
  }

}
