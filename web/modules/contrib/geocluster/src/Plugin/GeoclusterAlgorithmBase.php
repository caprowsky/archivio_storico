<?php

namespace Drupal\geocluster\Plugin;

use Drupal\geocluster\Utility\GeohashHelper;
use Drupal\geocluster\Utility\GeoclusterHelper;
use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for GeoclusterAlgorithm plugins.
 */
abstract class GeoclusterAlgorithmBase extends PluginBase implements GeoclusterAlgorithmInterface {

  /**
   * Stores the reference of geocluster configuration.
   *
   * @var GeoclusterConfigBackendInterface
   */
  public $config;

  /**
   * Reference to Geofield field handler on which to perform clustering.
   *
   * @var views_handler_field
   */
  public $fieldHandler;

  /**
   * Minimum cluster distance.
   *
   * As defined by GeoclusterConfigViewsDisplayExtender.
   *
   * @var float
   */
  public $clusterDistance;

  /**
   * Current zoom level for clustering.
   *
   * @var int
   */
  public $zoomLevel;

  /**
   * Resolution in meters / pixel based on zoomLevel.
   *
   * @var float
   *
   * @see GeoclusterHelper::resolutions().
   */
  public $resolution;

  /**
   * Geohash length for clustering by a specified distance in pixels.
   *
   * @var int
   */
  public $geohashLength;

  /**
   * Items being clustered.
   *
   * @var object
   */
  public $values;

  /**
   * Total number of items within clusters.
   *
   * @var int
   */
  protected $totalItems;

  /**
   * Flag to skip whole clustering process.
   *
   * @var bool
   */
  protected $skipClustering;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Check if cluster distance is negative, if so disable clustering.
    $this->skipClustering($configuration['cluster_distance'] < 0);
    $this->config = $configuration['config'];
    $this->fieldHandler = $configuration['cluster_field'];
    $this->clusterDistance = $configuration['cluster_distance'];
    $this->zoomLevel = $configuration['zoom'];
    $resolutions = GeoclusterHelper::resolutions();
    $this->resolution = $resolutions[$configuration['zoom']];
    $this->geohashLength = GeohashHelper::lengthFromDistance($this->clusterDistance, $this->resolution);
    $this->afterConstruct();
  }

  /**
   * Perform any clustering tasks before the views query will be executed.
   */
  abstract public function preExecute();

  /**
   * Perform any clustering tasks after the views query has been executed.
   */
  abstract public function postExecute();

  /**
   * Allows to skip the clustering process.
   *
   * @param null|bool $skip
   *   If the parameter is give it set's the state of the skipping flag.
   *
   * @return bool
   *   TRUE if the clustering is disabled, FALSE otherwise.
   */
  public function skipClustering($skip = NULL) {
    if (isset($skip)) {
      $this->skipClustering = (bool) $skip;
    }
    return $this->skipClustering;
  }

  /**
   * {@inheritdoc}
   */
  public function afterConstruct() {
  }

  /**
   * {@inheritdoc}
   */
  public function beforePreExecute() {
  }

  /**
   * {@inheritdoc}
   */
  public function beforePostExecute() {
    $view = $this->config->getView();
    $this->values = &$view->result;
  }

  /**
   * {@inheritdoc}
   */
  public function afterPostExecute() {
  }

  /* GETTERS & SETTERS */

  /**
   * {@inheritdoc}
   */
  public function getClusterDistance() {
    return $this->clusterDistance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldHandler() {
    return $this->fieldHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function getResolution() {
    return $this->resolution;
  }

  /**
   * {@inheritdoc}
   */
  public function getZoomLevel() {
    return $this->zoomLevel;
  }

  /**
   * {@inheritdoc}
   */
  public function getGeohashLength() {
    return $this->geohashLength;
  }

  /**
   * {@inheritdoc}
   */
  public function setValues(&$values) {
    $this->values = &$values;
  }

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    return $this->values;
  }

}
