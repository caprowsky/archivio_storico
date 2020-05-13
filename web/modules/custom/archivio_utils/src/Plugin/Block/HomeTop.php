<?php

namespace Drupal\archivio_utils\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with the current project.
 *
 * @Block(
 *   id = "home_top",
 *   admin_label = @Translation("Homepage Top Block"),
 * )
 */
class HomeTop extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = '';
    $base_url = \Drupal::request()->getSchemeAndHttpHost();
    return [
      '#markup' => $base_url,
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
