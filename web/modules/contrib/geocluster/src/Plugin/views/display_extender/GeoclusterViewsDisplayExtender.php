<?php

namespace Drupal\geocluster\Plugin\views\display_extender;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geocluster\GeoclusterConfigBackendInterface;

/**
 * Display extender class that integrates geocluster config with views.
 *
 * @ingroup views_display_extender_plugins
 *
 * @ViewsDisplayExtender(
 *   id = "geocluster_views_display_extender",
 *   title = @Translation("Cluster results"),
 *   help = @Translation("Cluster results on aggregation."),
 *   no_ui = FALSE,
 * )
 */
class GeoclusterViewsDisplayExtender extends DisplayExtenderPluginBase implements GeoclusterConfigBackendInterface {

  /**
   * Location of the configuration.
   */
  const GEOCLUSTER_VIEWS_SECTION = 'style_options';

  /**
   * The GeoclusterConfigBackendInterface config object.
   *
   * @var GeoclusterConfig
   */
  public $config;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, ?array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->config = geocluster_init_config($this);
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    return [
      'geocluster_enabled' => ['default' => FALSE],
      'geocluster_options' => ['default' => []],
    ] + parent::defineOptions();
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    if ($storage['section'] == self::GEOCLUSTER_VIEWS_SECTION) {
      $this->config->buildOptionsForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    $this->config->validateOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    $this->config->submitOptionsForm($form, $form_state);
  }

  /**
   * Set up any variables on the view prior to execution.
   */
  public function preExecute() {
    if ($this->getOption('geocluster_enabled')) {
      if ($algorithm = geocluster_init_algorithm($this->config)) {
        $algorithm->beforePreExecute();
      }
    }
  }

  /**
   * Inject anything into the query that the display_extender handler needs.
   */
  public function query() {
    if ($this->getOption('geocluster_enabled')) {
      if ($algorithm = geocluster_init_algorithm($this->config)) {
        $algorithm->preExecute();
      }
    }
  }

  /**
   * List which sections are defaultable and what items each section contains.
   */
  public function defaultableSections(&$sections, $section = NULL) {}

  /**
   * Identify whether or not the current display has custom metadata defined.
   *
   * @return mixed
   *   Returns a display option value or NULL if not set.
   */
  public function getOption($option) {
    if (isset($this->options[$option])) {
      return $this->options[$option];
    }
    return NULL;
  }

  /**
   * Sets an option value.
   */
  public function setOption($option, $value) {
    $this->options[$option] = $value;

  }

  /**
   * Get the appropriate option display handler (default or overridden).
   *
   * @return views_display
   */
  /*
  protected function &get_option_handler() {
  if ($this->getDisplay()->is_defaulted(GEOCLUSTER_VIEWS_SECTION)
  && isset($this->view->display['default'])) {
  return $this->view->display['default']->handler;
  }
  // Else.
  return $this->display;
  }
   */

  /**
   * {@inheritdoc}
   */
  public function getView() {
    return $this->view;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplay() {
    return $this->view->display_handler;
  }

}
