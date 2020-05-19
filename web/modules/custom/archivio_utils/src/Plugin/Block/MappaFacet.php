<?php

namespace Drupal\archivio_utils\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Provides a block with the current project.
 *
 * @Block(
 *   id = "mappa_facet",
 *   admin_label = @Translation("Facet per la mappa"),
 * )
 */
class MappaFacet extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
  }
}
