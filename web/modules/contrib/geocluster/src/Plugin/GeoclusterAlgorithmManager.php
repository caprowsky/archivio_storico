<?php

namespace Drupal\geocluster\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Geocluster plugin manager.
 *
 * @see plugin_api
 */
class GeoclusterAlgorithmManager extends DefaultPluginManager {

  /**
   * Constructs a GeoclusterAlgorithmManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/GeoclusterAlgorithm',
      $namespaces,
      $module_handler,
      'Drupal\geocluster\Plugin\GeoclusterAlgorithmInterface',
      'Drupal\geocluster\Annotation\GeoclusterAlgorithm'
    );
    $this->alterInfo('geocluster_geocluster_algorithm_info');
    $this->setCacheBackend($cache_backend, 'geocluster_geocluster_algorithm_plugins');
  }

}
