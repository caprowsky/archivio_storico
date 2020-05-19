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

}
