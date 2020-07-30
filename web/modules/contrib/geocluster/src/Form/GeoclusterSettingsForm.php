<?php

namespace Drupal\geocluster\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration settings for geocluster.
 */
class GeoclusterSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'geocluster.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'geocluster_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $indexesStatus = $this->checkGeoclusterIndexesExist();
    if (!($indexesStatus)) {
      $warning = $this->t('Geocluster indexes do not seem to be set, please click on the button below');
    }
    else {
      $warning = $this->t('Geocluster indexes seem to be set, there should not be any need to click on the button below');
    }

    $form['geocluster_warning'] = [
      '#type' => 'item',
      '#title' => $warning,
    ];

    $form['schema_geocluster_columns_add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add geocluster indexes to the schema'),
      '#submit' => [[$this, 'addMissingColumns']],
    ];

    return $form;
  }

  /**
   * Checking that geocluster_index_12 exists on all tables.
   *
   * If it does, then it means that probably the batch was ran.
   */
  private function checkGeoclusterIndexesExist() {
    $entityTypeManager = \Drupal::entityTypeManager();
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $database = \Drupal::database();
    $manager = \Drupal::entityDefinitionUpdateManager();

    $fieldMap = $entityFieldManager->getFieldMapByFieldType('geofield');

    foreach ($fieldMap as $entityTypeId => $fields) {
      foreach (array_keys($fields) as $fieldName) {
        $fieldStorageDefinition = $manager->getFieldStorageDefinition($fieldName, $entityTypeId);
        $storage = $entityTypeManager->getStorage($entityTypeId);

        $tableMapping = $storage->getTableMapping([$fieldName => $fieldStorageDefinition]);
        $tableNames = $tableMapping->getDedicatedTableNames();
        $columns = $tableMapping->getColumnNames($fieldName);

        foreach ($tableNames as $tableName) {
          $schema = $database->schema();
          $fieldExists = $schema->fieldExists($tableName, $columns['geocluster_index_12']);
          $tableExists = $schema->tableExists($tableName);
          if (!$fieldExists && $tableExists) {
            return FALSE;
          }
        }
      }
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function addMissingColumns(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('geocluster.trigger_batch');
  }

}
