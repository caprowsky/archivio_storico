<?php

namespace Drupal\geocluster\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a GeoclusterAlgorithm annotation object.
 *
 * @ingroup geofield_api
 *
 * @Annotation
 */
class GeoclusterAlgorithm extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The administrative label of the geofield backend.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $adminLabel = '';

}
