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
 *   id = "facolta_link",
 *   admin_label = @Translation("Link alle persone per facoltà"),
 * )
 */
class FacoltaLink extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {

    $request = \Drupal::routeMatch();
    $entity = $request->getParameters();
    $term = $entity->get('taxonomy_term');

    $url = Url::fromUserInput('/persone', array('query' => array('f[0]' => 'facolta:' . $term->id())))->toString();

    // persone?f[0]=facolta%3A1

    $base_url = \Drupal::request()->getSchemeAndHttpHost();
    return [
      '#markup' => '<a class="link-persone-facolta" href="' . $url . '">Visualizza le persone per questa facoltà</a>',
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
