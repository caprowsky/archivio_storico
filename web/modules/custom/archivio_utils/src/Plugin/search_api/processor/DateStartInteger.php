<?php

namespace Drupal\archivio_utils\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 *
 * @SearchApiProcessor(
 *   id = "date_start_integer",
 *   label = @Translation("Data inizio carriera intero"),
 *   description = @Translation("La data dell'inizio della carriera in formato intero"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class DateStartInteger extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Data inizio carriera intero'),
        'description' => $this->t('Data inizio carriera intero'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_date_start_integer'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();
    /* @var \Drupal\node\Entity\Node $entity*/
    if ($entity->bundle() == 'carriera') {
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_date_start_integer');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {
          if (!$entity->field_data_inizio_carriera->isEmpty()) {
            $value_original = $entity->get('field_data_inizio_carriera')->getValue();
            $explode = explode('-', $value_original[0]['value']);
            //$new_date = date('Y') . substr($value_original[0]['value'], 4) . ' 02:01:00';
            $field->addValue($explode[0]);
          }
        }
      }
    }
  }
}
