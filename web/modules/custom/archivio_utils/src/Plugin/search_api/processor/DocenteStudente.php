<?php

namespace Drupal\archivio_utils\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 *
 * @SearchApiProcessor(
 *   id = "docentestudente",
 *   label = @Translation("Docente Studente"),
 *   description = @Translation("Docente Studente"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class DocenteStudente extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Docente o studente'),
        'description' => $this->t('Docente o studente'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_persona_docente_studente'] = new ProcessorProperty($definition);
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
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_persona_docente_studente');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {
          if (!$entity->field_link_carriera->isEmpty()) {
            $carriere = $entity->get('field_link_carriera')->referencedEntities();
            foreach ($carriere as $carriera) {
              $tipologia = $carriera->get('field_tipologia_carriera')->getValue();
              if ($tipologia[0]['value'] == 's') {
                $field->addValue('studente');
              }
              if ($tipologia[0]['value'] == 'd') {
                $field->addValue('docente');
              }
              if ($tipologia[0]['value'] == 'r') {
                $field->addValue('rettore');
              }
              if ($tipologia[0]['value'] == 'h') {
                $field->addValue('laaurea honoris causae');
              }
            }
          }
        }
      }
    }
  }
}
