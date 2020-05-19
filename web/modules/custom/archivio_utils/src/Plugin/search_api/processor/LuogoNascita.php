<?php

namespace Drupal\archivio_utils\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 *
 * @SearchApiProcessor(
 *   id = "luogo_nascita",
 *   label = @Translation("Luogo di nascita fake"),
 *   description = @Translation("Luogo di nascita fake"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class LuogoNascita extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Luogo di nascita fake'),
        'description' => $this->t('Luogo di nascita fake'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_luogo_nascita'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();
    /* @var \Drupal\node\Entity\Node $entity*/
    /* @var \Drupal\node\Entity\Node $carriera*/
    if ($entity->bundle() == 'persona') {
      $fields = $this->getFieldsHelper()
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_luogo_nascita');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {
          if (!$entity->get('field_indirizzo')->isEmpty()) {
            $indirizzo = $entity->get('field_indirizzo')->getValue();
            if (!empty($indirizzo[0]['locality'])) {
              $field->addValue($indirizzo[0]['locality']);
            }
          }
        }
      }
    }
  }
}
