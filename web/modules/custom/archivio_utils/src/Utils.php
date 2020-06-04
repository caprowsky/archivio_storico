<?php

namespace Drupal\archivio_utils;


class Utils {

  /**
   * @param  \Drupal\node\Entity\Node $entity
   * @param $field_name string
   */
  public function implodeValue($entity, $field_name) {
    $values = $entity->get($field_name)->getValue();
    foreach ($values as $value) {
      $valori[] = $value['value'];
    }
    return implode(", ", $valori);
  }

  public function checkFigliDalPadre($term_id) {
    $query = \Drupal::entityQuery('taxonomy_term')->condition('parent', $term_id);
    return $query->execute();
  }

  public function checkPadreDalFiglio($term) {
    $padre = $term->get('parent')->getValue();
    return $padre[0]['target_id'];
  }
}

