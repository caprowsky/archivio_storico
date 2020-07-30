<?php

namespace Drupal\geocluster;

/**
 * Provides access to geocluster configuration.
 */
interface GeoclusterConfigBackendInterface {

  /**
   * Returns a configuration option value.
   *
   * @return mixed
   *   An option value as configured in the Admin section
   */
  public function getOption($option);

  /**
   * Sets an option value.
   */
  public function setOption($option, $value);

  /**
   * Returns the view that the configuration is attached to.
   *
   * @return View
   *   The view the configuration is attached to.
   */
  public function getView();

  /**
   * Returns the display of the configuration.
   *
   * @return views_plugin_display
   *   The display of the configuration.
   */
  public function getDisplay();

}
