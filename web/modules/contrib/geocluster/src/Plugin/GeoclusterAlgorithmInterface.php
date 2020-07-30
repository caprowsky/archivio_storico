<?php

namespace Drupal\geocluster\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Geocluster Algorithm plugins.
 */
interface GeoclusterAlgorithmInterface extends PluginInspectionInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition);

  /**
   * Perform any clustering tasks before the views query will be executed.
   */
  public function preExecute();

  /**
   * Perform any clustering tasks after the views query has been executed.
   */
  public function postExecute();

  /**
   * Allows to skip the clustering process.
   *
   * @param null|bool $skip
   *   If the parameter is give it set's the state of the skipping flag.
   *
   * @return bool
   *   TRUE if the clustering is disabled, FALSE otherwise.
   */
  public function skipClustering($skip);

  /**
   * Actions after construct (eg: debug).
   */
  public function afterConstruct();

  /**
   * Actions to do before the view is built.
   */
  public function beforePreExecute();

  /**
   * First actions to do after the view is built.
   */
  public function beforePostExecute();

  /**
   * Last actions to do after the view is built.
   */
  public function afterPostExecute();

  /* GETTERS & SETTERS */

  /**
   * Get Cluster Distance.
   *
   * @return float
   *   Returns cluster distance.
   */
  public function getClusterDistance();

  /**
   * Get Field Handler.
   *
   * @return \views_handler_field
   *   The field Handler.
   */
  public function getFieldHandler();

  /**
   * Get Resolution.
   *
   * @return float
   *   The resolution.
   */
  public function getResolution();

  /**
   * Get Zoom Level.
   *
   * @return int
   *   The zoom level.
   */
  public function getZoomLevel();

  /**
   * Get Geohash length.
   *
   * @return int
   *   The length of the geohash.
   */
  public function getGeohashLength();

  /**
   * Set $values field.
   */
  public function setValues(&$values);

  /**
   * Get Values.
   *
   * @return object
   *   Values attribute.
   */
  public function getValues();

}
