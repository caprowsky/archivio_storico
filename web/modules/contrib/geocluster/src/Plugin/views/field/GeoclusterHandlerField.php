<?php

namespace Drupal\geocluster\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Default Field handler .
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("geocluster_handler_field")
 */
class GeoclusterHandlerField extends FieldPluginBase {

  /**
   * Leave empty to avoid a query on this field.
   *
   * @{inheritdoc}
   */
  public function query() {
  }

  /**
   * Returns the field name or empty if no values.
   *
   * @{inheritdoc}
   *
   * @return string
   *   Returns the field name or empty if no values.
   */
  public function render(ResultRow $values) {
    $field_name = $this->field;
    if (isset($values->$field_name)) {
      return $values->$field_name;
    }
    return "";
  }

}
