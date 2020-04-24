<?php

namespace Drupal\archivio_utils\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 *
 * @SearchApiProcessor(
 *   id = "periodo",
 *   label = @Translation("Periodo"),
 *   description = @Translation("Periodo"),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class Periodo extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Periodo'),
        'description' => $this->t('Periodo'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_persona_periodo'] = new ProcessorProperty($definition);
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
        ->filterForPropertyPath($item->getFields(), NULL, 'search_api_persona_periodo');
      foreach ($fields as $field) {
        if (!$field->getDatasourceId()) {
          $laureato = array();
          if (!$entity->field_link_carriera->isEmpty()) {
            $carriere = $entity->get('field_link_carriera')->referencedEntities();
            foreach ($carriere as $carriera) {
              $periodi = array();
              if (!$carriera->field_data_inizio_carriera->isEmpty()) {
                $value_original = $entity->get('field_data_inizio_carriera')->getValue();
                $explode = explode('-', $value_original[0]['value']);
                $anno_inizio = $explode[0];
                  $periodi[] = $this->getPeriodo($anno_inizio);
              }
            }

            if (count($periodi) > 0) {
              $field->addValue($periodi);
            }
          }
        }
      }
    }
  }

  private function getPeriodo($anno) {
    if ($anno > 1499 && $anno < 1550) {
      $periodo = 'Prima metà XVI secolo';
    }

    if ($anno > 1549 && $anno < 1600) {
      $periodo = 'Seconda metà XVI secolo';
    }

    if ($anno > 1599 && $anno < 1650) {
      $periodo = 'Prima metà XVII secolo';
    }

    if ($anno > 1649 && $anno < 1700) {
      $periodo = 'Seconda metà XVII secolo';
    }

    if ($anno > 1699 && $anno < 1750) {
      $periodo = 'Prima metà XVIII secolo';
    }

    if ($anno > 1749 && $anno < 1800) {
      $periodo = 'Seconda metà XVIII secolo';
    }

    if ($anno > 1799 && $anno < 1850) {
      $periodo = 'Prima metà XIX secolo';
    }

    if ($anno > 1849 && $anno < 1900) {
      $periodo = 'Seconda metà XIX secolo';
    }

    if ($anno > 1899 && $anno < 1950) {
      $periodo = 'Prima metà XX secolo';
    }

    if ($anno > 1949 && $anno < 2000) {
      $periodo = 'Seconda metà XX secolo';
    }

    if ($anno > 1999 && $anno < 2050) {
      $periodo = 'Prima metà XXI secolo';
    }

    if ($anno > 2049 && $anno < 20100) {
      $periodo = 'Seconda metà XXI secolo';
    }

    return $periodo;
  }
}


