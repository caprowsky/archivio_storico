<?php

namespace Drupal\archivio_utils\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 *
 * @SearchApiProcessor(
 *   id = "backoffice_carriere",
 *   label = @Translation("Backoffice carriere"),
 *   description = @Translation("Backoffice carriere"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class BackofficeCarriere extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Backoffice carriere'),
        'description' => $this->t('Backoffice carriere'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_backoffice_carriere'] = new ProcessorProperty($definition);
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
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_backoffice_carriere');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {

          $fields = [
            'field_ambiti_di_ricerca',
            'field_persona',
            'field_biografia',
            'field_carriera_extra_acca',
            'field_corso',
            'field_data_fine_carriera',
            'field_data_inizio_carriera',
            'field_data_licenza',
            'field_facolta',
            'field_file_tesi_laurea',
            'field_fine_mandato',
            'field_inizio_mandato',
            'field_insegnamenti',
            'field_riferimenti_archivistici',
            'field_riferimenti_bibliografici',
            'field_segnatura_arch_tesi_laurea',
            'field_segnatura_arch_tesi_licenz',
            'field_titolo_tesi_laurea',
            'field_titolo_tesi_licenza',
            'field_valutazione_laurea',
          ];

          foreach ($fields as $single_field) {
            if ($entity->get($single_field)->isEmpty()) {
              $field_definition = $entity->get($single_field)->getFieldDefinition();
              $label = $field_definition->label();
              $value = '"' . $label . '" vuoto';
              $field->addValue($value);
            }
          }
        }
      }
    }
  }
}


