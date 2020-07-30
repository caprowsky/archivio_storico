<?php

namespace Drupal\geocluster\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * An InstallBatch controller.
 */
class InstallBatch extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Construct the InstallBatch object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity_field_manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, Connection $connection) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->connection = $connection;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('database')
    );
  }

  /**
   * Generates the batch to add geocluster indexes properly and starts it.
   */
  public function startBatch() {
    // From https://gist.github.com/JPustkuchen/ce53d40303a51ca5f17ce7f48c363b9b
    $fieldType = 'geofield';

    // As it could take long, use the batch api/.
    $batch = [
      'title' => t('Generate geocluster indexes'),
      'operations' => [],

    ];

    // Add the columns in the schema.
    for ($i = 1; $i <= 12; $i++) {
      $name = 'geocluster_index_' . $i;
      $this->geoclusterFieldDefinitionAddHelper($fieldType, $name, $batch);
    }

    $entityFieldMap = $this->entityFieldManager->getFieldMapByFieldType($fieldType);
    // Populate the values.
    foreach ($entityFieldMap as $entityTypeId => $fieldMap) {
      $entityStorage = $this->entityTypeManager->getStorage($entityTypeId);
      $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
      $fieldStorageDefinitions = $this->entityFieldManager->getFieldStorageDefinitions($entityTypeId);
      /** @var Drupal\Core\Entity\Sql\DefaultTableMapping $tableMapping */
      $tableMapping = $entityStorage->getTableMapping($fieldStorageDefinitions);
      // Only need field storage definitions of address fields.
      /** @var \Drupal\Core\Field\FieldStorageDefinitionInterface $fieldStorageDefinition */
      foreach (array_intersect_key($fieldStorageDefinitions, $fieldMap) as $fieldStorageDefinition) {
        $fieldName = $fieldStorageDefinition->getName();
        $tables = [];
        try {
          $table = $tableMapping->getFieldTableName($fieldName);
          $tables[] = $table;
        }
        catch (SqlContentEntityStorageException $e) {
          // Custom storage? Broken site? No matter what, if there is no table
          // or column, there's little we can do.
          continue;
        }
        // See if the field has a revision table.
        $revisionTable = NULL;
        if ($entityType->isRevisionable() && $fieldStorageDefinition->isRevisionable()) {
          if ($tableMapping->requiresDedicatedTableStorage($fieldStorageDefinition)) {
            $revisionTable = $tableMapping->getDedicatedRevisionTableName($fieldStorageDefinition);
            $tables[] = $revisionTable;
          }
          elseif ($tableMapping->allowsSharedTableStorage($fieldStorageDefinition)) {
            $revisionTable = $entityType->getRevisionDataTable() ?: $entityType->getRevisionTable();
            $tables[] = $revisionTable;
          }
        }

        $existingData = [];
        foreach ($tables as $table) {
          // Get the old data.
          $existingData[$table] = $this->connection->select($table)
            ->fields($table)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

          // Wipe it.
          $batch['operations'][] = ['\Drupal\geocluster\Controller\InstallBatch::geoclusterTruncate', [$table]];
        }

        $batch['operations'][] = [
          '\Drupal\geocluster\Controller\InstallBatch::updateFieldStorageDefinition',
          [
            $fieldName,
            $entityTypeId,
          ],
        ];

        // Restore the data.
        foreach ($tables as $table) {
          if ($existingData[$table]) {
            foreach ($existingData[$table] as $index => $row) {
              // Initialize the geocluster indexes.
              $geohash = $row[$fieldName . '_geohash'] ?? NULL;

              if ($geohash) {
                for ($length = 12; $length > 0; $length--) {
                  $columnName = $fieldName . '_geocluster_index_' . $length;
                  $geoclusterHash = substr($geohash, 0, min($length, strlen($geohash)));
                  $existingData[$table][$index][$columnName] = $geoclusterHash;
                }
              }
            }

            // Inserts the data through batch.
            $insertQuery = $this->connection
              ->insert($table)
              ->fields(array_keys(end($existingData[$table])));

            $batchMax = 100;
            $inBatch = 0;
            foreach ($existingData[$table] as $row) {

              if ($inBatch >= $batchMax) {
                // Set the batch and recreate a new one.
                $batch['operations'][] = ['\Drupal\geocluster\Controller\InstallBatch::geoclusterIndexesUpdate', [$insertQuery]];
                $insertQuery = $this->connection
                  ->insert($table)
                  ->fields(array_keys(end($existingData[$table])));
                $inBatch = 0;
              }

              $insertQuery->values(array_values($row));
              $inBatch++;

            }
            $batch['operations'][] = ['\Drupal\geocluster\Controller\InstallBatch::geoclusterIndexesUpdate', [$insertQuery]];
          }
        }
      }
    }
    // Run the batch.
    batch_set($batch);
    return batch_process('/admin/config/geocluster');
  }

  /**
   * Add a batch operation to new DB column for all instances of fieldType.
   *
   * @param string $fieldType
   *   The fielType name, here geofield.
   * @param string $newProperty
   *   Geocluster index column name.
   * @param array $batch
   *   The batch definition array.
   */
  private function geoclusterFieldDefinitionAddHelper($fieldType, $newProperty, array &$batch) {
    // From https://gist.github.com/JPustkuchen/ce53d40303a51ca5f17ce7f48c363b9b
    $manager = \Drupal::entityDefinitionUpdateManager();
    $fieldMap = $this->entityFieldManager->getFieldMapByFieldType($fieldType);

    foreach ($fieldMap as $entityTypeId => $fields) {
      foreach (array_keys($fields) as $fieldName) {
        $fieldStorageDefinition = $manager->getFieldStorageDefinition($fieldName, $entityTypeId);
        $storage = $this->entityTypeManager->getStorage($entityTypeId);

        $tableMapping = $storage->getTableMapping([$fieldName => $fieldStorageDefinition]);
        $tableNames = $tableMapping->getDedicatedTableNames();
        $columns = $tableMapping->getColumnNames($fieldName);

        foreach ($tableNames as $tableName) {
          $fieldSchema = $fieldStorageDefinition->getSchema();
          $schema = $this->connection->schema();
          $fieldExists = $schema->fieldExists($tableName, $columns[$newProperty]);
          $tableExists = $schema->tableExists($tableName);
          if (!$fieldExists && $tableExists) {
            $batch['operations'][] = [
              '\Drupal\geocluster\Controller\InstallBatch::geoclusterAddField',
              [
                $tableName,
                $columns[$newProperty],
                $fieldSchema['columns'][$newProperty],
              ],
            ];
          }
        }
      }
    }
  }

  /**
   * Add a new field in the DB to the table.
   *
   * @param string $tableName
   *   The name of the table.
   * @param string $specification
   *   The field specification array, as taken from a schema definition.
   * @param array $keysNew
   *   Keys and indexes specification to be created on the table.
   * @param array $context
   *   The batch context array.
   */
  public static function geoclusterAddField($tableName, $specification, array $keysNew, array &$context) {
    $message = 'Adding geocluster columns in schema to geofield fields...';
    $context['message'] = $message;

    $schema = \Drupal::database()->schema();
    $schema->addField($tableName, $specification, $keysNew);
  }

  /**
   * Update the field storage definition.
   *
   * @param string $fieldName
   *   The name of the field.
   * @param string $entityTypeId
   *   The entity type id.
   * @param array $context
   *   The batch context array.
   */
  public static function updateFieldStorageDefinition($fieldName, $entityTypeId, array &$context) {
    $message = 'Updating field schema definition...';
    $context['message'] = $message;

    $manager = \Drupal::entityDefinitionUpdateManager();
    $manager->updateFieldStorageDefinition($manager->getFieldStorageDefinition($fieldName, $entityTypeId));
  }

  /**
   * Runs the insert query.
   *
   * @param object $insertQuery
   *   The insertQuery object.
   * @param array $context
   *   The batch context array.
   */
  public static function geoclusterIndexesUpdate($insertQuery, array &$context) {
    $message = 'Generating geocluster indexes...';
    $context['message'] = $message;

    $insertQuery->execute();
  }

  /**
   * Runs the truncate table query.
   *
   * @param string $table
   *   The name of the table to truncate.
   * @param array $context
   *   The batch context array.
   */
  public static function geoclusterTruncate($table, array &$context) {
    $message = 'Recreating table...';
    $context['message'] = $message;

    $database = \Drupal::database();
    $database->truncate($table)->execute();
  }

  /**
   * Removes the geocluster indexes columns.
   */
  public function removeGeoclusterFields() {
    $fieldType = 'geofield';

    for ($i = 1; $i <= 12; $i++) {
      $name = 'geocluster_index_' . $i;
      $this->geoclusterFieldDefinitionDeleteHelper($fieldType, $name);
    }
  }

  /**
   * Remove a column of fieldType.
   *
   * @param string $fieldType
   *   FieldTypeId in your definition.
   * @param string $property
   *   Column name.
   */
  private function geoclusterFieldDefinitionDeleteHelper($fieldType, $property) {
    $fieldMap = $this->entityFieldManager->getFieldMapByFieldType($fieldType);
    foreach ($fieldMap as $entityTypeId => $fields) {
      foreach (array_keys($fields) as $fieldName) {
        $this->geoclusterFieldPropertyDefinitionDelete($entityTypeId, $fieldName, $property);
      }
    }
  }

  /**
   * Inner function, called by _geoclusterFieldDefinitionDeleteHelper.
   *
   * @param string $entityTypeId
   *   EntityTypeID in your definition.
   * @param string $fieldName
   *   Field name in your definition.
   * @param string $property
   *   Column name.
   */
  private function geoclusterFieldPropertyDefinitionDelete($entityTypeId, $fieldName, $property) {
    $entityUpdateManager = \Drupal::entityDefinitionUpdateManager();
    $entityStorageSchemaSql = \Drupal::keyValue('entity.storage_schema.sql');

    $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
    $fieldStorageDefinition = $entityUpdateManager->getFieldStorageDefinition($fieldName, $entityTypeId);
    $entityStorage = $this->entityTypeManager()->getStorage($entityTypeId);
    /** @var Drupal\Core\Entity\Sql\DefaultTableMapping $tableMapping */
    $tableMapping = $entityStorage->getTableMapping([$fieldName => $fieldStorageDefinition]);

    // Load the installed field schema so that it can be updated.
    $schemaKey = "$entityTypeId.field_schema_data.$fieldName";
    $fieldSchemaData = $entityStorageSchemaSql->get($schemaKey);

    // Get table name and revision table name, getFieldTableName DO NOT WORK
    // So we use getDedicatedDataTableName.
    $table = $tableMapping->getDedicatedDataTableName($fieldStorageDefinition);
    // try/catch.
    $revisionTable = NULL;
    if ($entityType->isRevisionable() && $fieldStorageDefinition->isRevisionable()) {
      if ($tableMapping->requiresDedicatedTableStorage($fieldStorageDefinition)) {
        $revisionTable = $tableMapping->getDedicatedRevisionTableName($fieldStorageDefinition);
      }
      elseif ($tableMapping->allowsSharedTableStorage($fieldStorageDefinition)) {
        $revisionTable = $entityType->getRevisionDataTable() ?: $entityType->getRevisionTable();
      }
    }

    // Save changes to the installed field schema.
    if ($fieldSchemaData) {
      $column = $tableMapping->getFieldColumnName($fieldStorageDefinition, $property);
      // Update schema definition in database.
      unset($fieldSchemaData[$table]['fields'][$column]);
      unset($fieldSchemaData[$table]['indexes'][$column]);
      if ($revisionTable) {
        unset($fieldSchemaData[$revisionTable]['fields'][$column]);
        unset($fieldSchemaData[$revisionTable]['indexes'][$column]);
      }
      $entityStorageSchemaSql->set($schemaKey, $fieldSchemaData);
      // Try to drop field data.
      $this->connection->schema()->dropField($table, $column);
    }
  }

}
