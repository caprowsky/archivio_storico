<?php

namespace Drupal\archivio_utils\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 *
 * @SearchApiProcessor(
 *   id = "persona_insegnamenti",
 *   label = @Translation("Gli insegnamenti di una persona"),
 *   description = @Translation("Gli insegnamenti di una persona"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class PersonaInsegnamenti extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Insegnamenti per persona'),
        'description' => $this->t('Insegnamenti per persona'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_persona_insegnamenti'] = new ProcessorProperty($definition);
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
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_persona_insegnamenti');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {
          if (!$entity->field_link_carriera->isEmpty()) {
            $nomi_insegnamenti = array();
            $carriere = $entity->get('field_link_carriera')->referencedEntities();
            foreach ($carriere as $carriera) {
              if ($carriera->isPublished()) {
                if (!$carriera->get('field_insegnamenti')->isEmpty()) {
                  $insegnamenti = $carriera->get('field_insegnamenti')->getValue();
                  foreach ($insegnamenti as $insegnamento) {
                    $nomi_insegnamenti[] = $insegnamento['value'];
                  }
                }
              }
            }

            if (count($nomi_insegnamenti) > 0) {
              $value = implode(', ', $nomi_insegnamenti);
              $field->addValue($value);;
            }

          }
        }
      }
    }
  }
}
