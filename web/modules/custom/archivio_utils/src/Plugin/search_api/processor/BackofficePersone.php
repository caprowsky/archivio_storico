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
class BackofficePersone extends ProcessorPluginBase {

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
          if (!$entity->field_link_carriera->isEmpty()) {
            $carriere = $entity->get('field_link_carriera')->referencedEntities();
            foreach ($carriere as $carriera) {
              $a = 1;
              /*$periodi = array();
              if (!$carriera->field_data_inizio_carriera->isEmpty()) {
                $value_original = $carriera->get('field_data_inizio_carriera')->getValue();
                $explode = explode('-', $value_original[0]['value']);
                $anno_inizio = $explode[0];
                  $periodo = $this->getPeriodo($anno_inizio);
                $field->addValue($periodo);
              }*/
            }
          }
        }
      }
    }
  }
}


