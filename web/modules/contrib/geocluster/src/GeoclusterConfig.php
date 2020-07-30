<?php

namespace Drupal\geocluster;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Encapsulates the geocluster config.
 */
class GeoclusterConfig implements GeoclusterConfigBackendInterface {
  use StringTranslationTrait;

  /**
   * Default cluster distance.
   */
  const GEOCLUSTER_DEFAULT_DISTANCE = 65;

  /**
   * Default geocluster algorithm.
   */
  const GEOCLUSTER_DEFAULT_ALGORITHM = 'mysql_algorithm';

  /**
   * Storing the backend configuration object.
   *
   * @var \Drupal\geocluster\GeoclusterConfigBackendInterface
   */
  public $configBackend;

  /**
   * {@inheritdoc}
   */
  public function __construct($config_backend) {
    $this->configBackend = $config_backend;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, &$form_state) {
    $cluster_field_options = $this->getClusterFieldOptions();
    if (count($cluster_field_options) == 1) {
      $more_form['error'] = [
        '#markup' => $this->t('To enable geocluster, please add at least 1 geofield to the view'),
      ];
    }
    else {
      // Add a checkbox to enable clustering.
      $more_form['geocluster_enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable geocluster for this search.'),
        '#default_value' => $this->getOption('geocluster_enabled'),
      ];

      // An additional fieldset provides additional options.
      $geocluster_options = $this->getOption('geocluster_options');
      $more_form['geocluster_options'] = [
        '#type' => 'fieldset',
        '#title' => 'Geocluster options',
        '#tree' => TRUE,
        '#states' => [
          'visible' => [
            ':input[name="geocluster_enabled"]' => ['checked' => TRUE],
          ],
        ],
      ];
      $algorithm_options = $this->getAlgorithmOptions();
      $more_form['geocluster_options']['algorithm'] = [
        '#type' => 'select',
        '#title' => $this->t('Clustering algorithm'),
        '#description' => $this->t('Select a geocluster algorithm to be used.'),
        '#options' => $algorithm_options,
        '#default_value' => $geocluster_options['algorithm'] ?? self::GEOCLUSTER_DEFAULT_ALGORITHM,
      ];
      $more_form['geocluster_options']['cluster_field'] = [
        '#type' => 'select',
        '#title' => $this->t('Cluster field'),
        '#description' => $this->t('Select the geofield to be used for clustering.?'),
        '#options' => $cluster_field_options,
        '#default_value' => $geocluster_options['cluster_field'] ?? '',
      ];
      $more_form['geocluster_options']['cluster_distance'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Cluster distance'),
        '#default_value' => $geocluster_options['cluster_distance'] ?? self::GEOCLUSTER_DEFAULT_DISTANCE,
        '#description' => $this->t('Specify the cluster distance.'),
      ];
      $more_form['geocluster_options']['enable_bbox_support'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable bbox support'),
        '#default_value' => !empty($geocluster_options['enable_bbox_support']),
        '#description' => $this->t('If enabled available Views GeoJSON bbox support will be enhanced.'),
      ];

      $more_form['geocluster_options']['advanced'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Advanced'),
        '#collapsed' => TRUE,
        '#collapsible' => TRUE,
      ];
      $more_form['geocluster_options']['advanced']['accept_parameter']['cluster_distance'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Accept URL parameter to set cluster distance'),
        '#default_value' => !isset($geocluster_options['advanced']['accept_parameter']['cluster_distance']) || !empty($geocluster_options['advanced']['accept_parameter']['cluster_distance']),
        '#description' => $this->t('If enabled the GET parameter "cluster_distance" will be used to set the cluster distance.'),
      ];
      $more_form['geocluster_options']['advanced']['accept_parameter']['zoom'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Accept URL parameter to set zoom level'),
        '#default_value' => !isset($geocluster_options['advanced']['accept_parameter']['zoom']) || !empty($geocluster_options['advanced']['accept_parameter']['zoom']),
        '#description' => $this->t('If enabled the GET parameter "zoom" will be used to set the current zoom level.'),
      ];

      $cluster_distance_per_zoom_level = '';
      if (!empty($geocluster_options['advanced']['cluster_distance_per_zoom_level']) && is_array($geocluster_options['advanced']['cluster_distance_per_zoom_level'])) {
        $cluster_distance_per_zoom_level = $geocluster_options['advanced']['cluster_distance_per_zoom_level'];
        array_walk($cluster_distance_per_zoom_level, function (&$val, $key) {
            $val = $key . '|' . $val;
        });
        $cluster_distance_per_zoom_level = implode("\n", $cluster_distance_per_zoom_level);
      }
      $more_form['geocluster_options']['advanced']['cluster_distance_per_zoom_level'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Accept URL parameter to set zoom level'),
        '#default_value' => $cluster_distance_per_zoom_level,
        '#description' => $this->t('Define a zoom level and a cluster distance per line. Format: zoomLevel|Distance e.g. 12|65. Fallback is the default cluster distance. Set distance to -1 to disable clustering.'),
      ];
    }
    $form = $more_form + $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, &$form_state) {

    $this->setOption('geocluster_enabled', !empty($form_state->getValue('geocluster_enabled')));

    $geocluster_options = $form_state->getValue('geocluster_options');
    if ($geocluster_options) {
      // Handle the cluster_distance_per_zoom_level option. Split it into an
      // array, with the zoom level as key and the distance as value.
      $cluster_distance_per_zoom_level_keys = preg_replace('/\|.+$/im', '', $geocluster_options['advanced']['cluster_distance_per_zoom_level']);
      $cluster_distance_per_zoom_level_distances = preg_replace('/^.+?\|/mi', '', $geocluster_options['advanced']['cluster_distance_per_zoom_level']);
      $cluster_distance_per_zoom_level_keys = array_map('trim', explode("\n", $cluster_distance_per_zoom_level_keys));
      $cluster_distance_per_zoom_level_distances = array_map('trim', explode("\n", $cluster_distance_per_zoom_level_distances));
      $geocluster_options['advanced']['cluster_distance_per_zoom_level'] = array_combine($cluster_distance_per_zoom_level_keys, $cluster_distance_per_zoom_level_distances);

      $this->setOption('geocluster_options', $geocluster_options);

      // If geocluster is enabled make sure the aggregation settings are set
      // properly.
      if ($this->getOption('geocluster_enabled')) {
        if ($geocluster_options['algorithm'] == 'mysql_algorithm') {
          if (!$this->getOption('group_by')) {
            $this->setOption('group_by', TRUE);
            \Drupal::messenger()->addMessage(t('The <strong>use aggregation</strong> setting has been <em>enabled</em> as a requirement by the MySQL-based geocluster algorithm.'));
          }
        }
        elseif ($geocluster_options['algorithm'] == 'php_algorithm') {
          if ($this->getOption('group_by')) {
            $this->setOption('group_by', FALSE);
            \Drupal::messenger()->addMessage(t('The <strong>use aggregation</strong> setting has been <em>disabled</em> as a requirement by the PHP-based geocluster algorithm.'));
          }
        }
      }
      else {
        // Ensure aggregation is disabled if it's not supported by this query
        // handler. It can happen that a display doesn't return a query object.
        $query = $this->getDisplay()->query();
        if (!empty($query) && $query->get_aggregation_info() === NULL &&  $this->getOption('group_by')) {
          $this->setOption('group_by', FALSE);
        }
      }
    }
  }

  /**
   * Find all fields of type 'geofield' associated to that display.
   *
   * @return array
   *   An array of geofield fields
   */
  public function getClusterFieldOptions() {
    $cluster_field_options = [
      '' => '<none>',
    ];

    /* @var \Drupal\views\Plugin\views\ViewsHandlerInterface $handler */
    foreach ($this->getDisplay()->getHandlers('field') as $field_id => $handler) {
      $label = $handler->adminLabel() ?: $field_id;
      if (is_a($handler, 'Drupal\views\Plugin\views\field\EntityField')) {
        /* @var \Drupal\views\Plugin\views\field\EntityField $handler */
        $field_storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($handler->getEntityType());
        $field_storage_definition = $field_storage_definitions[$handler->definition['field_name']];

        $type = $field_storage_definition->getType();
        $definition = \Drupal::service('plugin.manager.field.field_type')->getDefinition($type);
        if (is_a($definition['class'], '\Drupal\geofield\Plugin\Field\FieldType\GeofieldItem', TRUE)) {
          $cluster_field_options[$field_id] = $label;
        }
      }
    }

    return $cluster_field_options;
  }

  /**
   * Provide a list of available geocluster algorithm options.
   *
   * @return array
   *   An array of algorithm names
   */
  protected function getAlgorithmOptions() {
    $options = [];

    $type = \Drupal::service('plugin.manager.geocluster_algorithm');
    $algorithms = $type->getDefinitions();

    foreach ($algorithms as $id => $algorithm) {
      $options[$id] = $algorithm['adminLabel']->__toString();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getOption($option) {
    return $this->configBackend->getOption($option);
  }

  /**
   * {@inheritdoc}
   */
  public function setOption($option, $value) {
    $this->configBackend->setOption($option, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getView() {
    return $this->configBackend->getView();
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplay() {
    // Return $this->configBackend->view->display_handler;.
    return $this->configBackend->getDisplay();
  }

}
