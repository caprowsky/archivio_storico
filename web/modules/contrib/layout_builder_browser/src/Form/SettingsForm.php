<?php

namespace Drupal\layout_builder_browser\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_builder_browser_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['layout_builder_browser.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $block_id = NULL) {

    $config = $this->config('layout_builder_browser.settings');

    $options = [
      'defaults' => 'Defaults<br>The layout builder mainly used by site builders, on the global entity settings.',
      'overrides' => 'Overrides<br>When a user edits a specific entity layout, this will trigger. You need to enable overrides for this on the entity view mode.',
    ];
    $form['enabled_section_storages'] = [
      '#title' => $this->t('Enable layout builder browser on'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $config->get('enabled_section_storages'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('layout_builder_browser.settings');
    $config->set('enabled_section_storages', array_filter($form_state->getValue('enabled_section_storages')));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
