<?php

/**
 * @file
 * Hooks for Views integration.
 */

/**
 * Adds custom field handlers for clustered data.
 *
 * Implements hook_views_data_alter().
 */
function geocluster_views_data_alter(&$data) {

  $field_type = 'geofield';
  $field_map = \Drupal::service('entity_field.manager')->getFieldMapByFieldType($field_type);

  foreach ($field_map as $entity_type_id => $fields) {
    foreach ($fields as $field_name => $field) {
      $table_name = $entity_type_id . '__' . $field_name;

      // We could add the field name as prefix
      // but we don't for handler simplicity.
      $geocluster_field = 'geocluster';
      $data[$table_name][$geocluster_field . '_ids'] = [
        'group' => 'Content',
        'title' => 'Geocluster ids (' . $field_name . ')',
        'help' => 'Adds Geocluster ids from a clustered result',
        'field' => [
          'table' => $table_name,
          'id' => 'geocluster_handler_field',
        ],
      ];
      $data[$table_name][$geocluster_field . '_count'] = [
        'group' => 'Content',
        'title' => 'Geocluster result count (' . $field_name . ')',
        'help' => 'Adds Geocluster result count from a clustered result',
        'field' => [
          'table' => $table_name,
          'id' => 'geocluster_handler_field',
        ],
      ];
      $data[$table_name][$geocluster_field . '_lat'] = [
        'real field' => $field_name . '_lat',
        'group' => 'Content',
        'title' => 'Geocluster lat (' . $field_name . ')',
        'help' => 'Adds Geocluster lat from a clustered result',
        'field' => [
          'table' => $table_name,
          'id' => 'geocluster_handler_field',
        ],
      ];
      $data[$table_name][$geocluster_field . '_lon'] = [
        'real field' => $field_name . '_lon',
        'group' => 'Content',
        'title' => 'Geocluster lon (' . $field_name . ')',
        'help' => 'Adds Geocluster lon from a clustered result',
        'field' => [
          'table' => $table_name,
          'id' => 'geocluster_handler_field',
        ],
      ];

      // Adding proximity filter from geofield/geofield.views.inc.
      $data[$table_name][$field_name . '_proximity'] = [
        'group' => 'Content',
        'title' => t('@label Proximity (@field_name)', [
          '@label' => $field_name,
          '@field_name' => $field_name,
        ]),
        'help' => 'Proximity filter for Geofield',
        'field' => [
          'id' => 'geofield_proximity_field',
          'real field' => $field_name,
          'float' => TRUE,
          'click sortable' => TRUE,
          'additional fields' => [
            'latitude' => [
              'field' => $field_name . '_lat',
            ],
            'longitude' => [
              'field' => $field_name . '_lon',
            ],
          ],
        ],
        'argument' => [
          'id' => 'geofield_proximity_argument',
          'table' => $table_name,
          'entity_type' => $entity_type_id,
          'field_name' => $field_name . '_proximity',
          'real field' => $field_name,
          'label' => t('Distance to !field_name', [$field_name]),
          'empty field name' => '- No value -',
          'additional fields' => [
            $field_name . 'value',
            $field_name . '_lat',
            $field_name . '_lon',
          ],
        ],
        'filter' => [
          'id' => 'geofield_proximity_filter',
          'real field' => $field_name,
        ],
        'sort' => [
          'id' => 'geofield_proximity_sort',
          'real field' => $field_name,
        ],
      ];
    }
  }
}
