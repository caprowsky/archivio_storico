<?php

namespace Drupal\archivio_utils\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 *
 * @SearchApiProcessor(
 *   id = "backoffice_persone",
 *   label = @Translation("Backoffice persone"),
 *   description = @Translation("Backoffice persone"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class BackofficePersone extends ProcessorPluginBase
{

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Backoffice persone'),
        'description' => $this->t('Backoffice persone'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_backoffice_persone'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();
    /* @var \Drupal\node\Entity\Node $entity */
    /* @var \Drupal\node\Entity\Node $carriera */
    if ($entity->bundle() == 'persona') {
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_backoffice_persone');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {
          $fields = [
            'field_data_di_morte',
            'field_data_nascita',
            'field_link_carriera',
            'field_foto',
            'field_geofiled',
            'field_luogo_di_morte',
            'field_indirizzo',
          ];

          foreach ($fields as $single_field) {
            if ($entity->get($single_field)->isEmpty()) {
              $field_definition = $entity->get($single_field)->getFieldDefinition();
              $label = $field_definition->label();
              $value = $label . ' vuoto';
              $field->addValue($value);
            }
          }
        }
      }
    }
  }
}


