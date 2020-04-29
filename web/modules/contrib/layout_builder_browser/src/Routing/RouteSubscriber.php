<?php
namespace Drupal\layout_builder_browser\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Override the layout builder choose block controller with ours.
    if ($route = $collection->get('layout_builder.choose_block')) {
      $route->setDefault('_controller', '\Drupal\layout_builder_browser\Controller\BrowserController::browse');
    }
  }
}
