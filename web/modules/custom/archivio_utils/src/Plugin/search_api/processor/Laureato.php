<?php

namespace Drupal\archivio_utils\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 *
 * @SearchApiProcessor(
 *   id = "laureato",
 *   label = @Translation("Laureato sì no"),
 *   description = @Translation("Laureato sì no"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class Laureato extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Laureato si no'),
        'description' => $this->t('Laureato sì no'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_persona_laureata'] = new ProcessorProperty($definition);
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
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_persona_laureata');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {
          $laureato = array();
          if (!$entity->field_link_carriera->isEmpty()) {

            $carriere = $entity->get('field_link_carriera')->referencedEntities();
            foreach ($carriere as $carriera) {
              $tipologia = $carriera->get('field_tipologia_carriera')->getValue();

              if ($tipologia[0]['value'] == 's') {
                if (!$carriera->get('field_data_fine_carriere')->isEmpty()) {
                  $laureato[] = TRUE;
                }
              }
            }
          }

          if (count($laureato) > 0) {
            $field->addValue('laureata');
          } else {
            $field->addValue('non laureata');
          }
        }
      }
    }
  }
}
