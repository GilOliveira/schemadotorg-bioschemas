<?php

namespace Drupal\schemadotorg;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Schema.org entity type manager.
 */
class SchemaDotOrgEntityTypeManager implements SchemaDotOrgEntityTypeManagerInterface {
  use StringTranslationTrait;

  /**
   * The Schema.org config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Schema.org schema type manager.
   *
   * @var \Drupal\schemadotorg\SchemaDotOrgSchemaTypeManagerInterface
   */
  protected $schemaTypeManager;

  /**
   * Constructs a SchemaDotOrgEntityTypeManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\schemadotorg\SchemaDotOrgSchemaTypeManagerInterface $schema_type_manager
   *   The Schema.org schema type manager.
   */
  public function __construct(
    ConfigFactoryInterface $config,
    EntityTypeManagerInterface $entity_type_manager,
    SchemaDotOrgSchemaTypeManagerInterface $schema_type_manager) {
    $this->config = $config->get('schemadotorg.settings');
    $this->entityTypeManager = $entity_type_manager;
    $this->schemaTypeManager = $schema_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypes() {
    return array_keys($this->config->get('entity_types'));
  }

  /**
   * Get default bundle for an entity type and Schema.org type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $type
   *   The Schema.org type.
   *
   * @return string|null
   *   The default bundle for an entity type and Schema.org type.
   */
  public function getDefaultSchemaTypeBundle($entity_type_id, $type) {
    $schema_types = $this->config->get("entity_types.$entity_type_id.default_schema_types") ?: [];
    $bundles = array_flip($schema_types);
    return $bundles[$type] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultSchemaType($entity_type_id, $bundle) {
    return $this->config->get("entity_types.$entity_type_id.default_schema_types.$bundle");
  }

  /**
   * {@inheritdoc}
   */
  public function getCommonSchemaTypes($entity_type_id) {
    return $this->config->get("entity_types.$entity_type_id.schema_types") ?: [];
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFieldNames($entity_type_id) {
    $fields = $this->config->get("entity_types.$entity_type_id.base_fields") ?: [];
    return array_combine($fields, $fields);
  }

  /**
   * {@inheritdoc}
   */
  public function getSchemaPropertyDefaults($entity_type_id) {
    $properties = $this->config->get("entity_types.$entity_type_id.default_schema_properties") ?: [];
    return array_combine($properties, $properties);
  }

  /**
   * {@inheritdoc}
   */
  public function getSchemaPropertyUnlimited($entity_type_id) {
    $unlimited = [];
    $entity_default_unlimited = $this->config->get("entity_types.$entity_type_id.default_unlimited") ?: [];
    if ($entity_default_unlimited) {
      $unlimited += array_combine($entity_default_unlimited, $entity_default_unlimited);
    }
    $default_unlimited = $this->config->get('schema_properties.default_unlimited') ?: [];
    if ($default_unlimited) {
      $unlimited += array_combine($default_unlimited, $default_unlimited);
    }
    return $unlimited;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchemaPropertyFieldTypes($property) {
    $property_mappings = $this->config->get('schema_properties.default_field_types');
    $type_mappings = $this->config->get('schema_types.default_field_types');

    $property_definition = $this->schemaTypeManager->getProperty($property);

    // Set property specific field types.
    $field_types = [];
    if (isset($property_mappings[$property])) {
      $field_types += array_combine($property_mappings[$property], $property_mappings[$property]);
    }

    // Set range include field types.
    $range_includes = $this->schemaTypeManager->parseIds($property_definition['range_includes']);

    // Prioritize enumerations and types (not data types).
    foreach ($range_includes as $range_include) {
      if ($this->schemaTypeManager->isEnumerationType($range_include)) {
        $field_types['field_ui:entity_reference:taxonomy_term'] = 'field_ui:entity_reference:taxonomy_term';
        break;
      }
      if (isset($type_mappings[$range_include]) && !$this->schemaTypeManager->isDataType($range_include)) {
        $field_types += array_combine($type_mappings[$range_include], $type_mappings[$range_include]);
      }
      // @see \Drupal\schemadotorg\SchemaDotOrgEntityTypeBuilder::alterFieldValues
      $allowed_values_function = 'schemadotorg_allowed_values_' . strtolower($range_include);
      if (function_exists($allowed_values_function)) {
        $field_types['list_string'] = 'list_string';
      }
    }

    // Set default data type related field types.
    if (!$field_types) {
      foreach ($range_includes as $range_include) {
        if (isset($type_mappings[$range_include]) && $this->schemaTypeManager->isDataType($range_include)) {
          $field_types += array_combine($type_mappings[$range_include], $type_mappings[$range_include]);
        }
      }
    }

    // Set a default field type to an entity reference and string (a.k.a. name).
    if (!$field_types) {
      $entity_reference_field_type = $this->getDefaultEntityReferenceFieldType($range_includes);
      $field_types += [
        $entity_reference_field_type => $entity_reference_field_type,
        'string' => 'string',
      ];
    }

    return $field_types;
  }

  /**
   * Get the entity reference field type based on an array Schema.org types.
   *
   * @param array $types
   *   Schema.org types, extracted from a property's range includes.
   *
   * @return string
   *   The entity reference field type.
   */
  protected function getDefaultEntityReferenceFieldType(array $types) {
    $sub_types = $this->schemaTypeManager->getAllSubTypes($types);
    if (empty($sub_types)) {
      return 'field_ui:entity_reference:node';
    }

    $schemadotorg_mapping_storage = $this->entityTypeManager->getStorage('schemadotorg_mapping');
    $entity_ids = $schemadotorg_mapping_storage->getQuery()
      ->condition('type', $sub_types, 'IN')
      ->execute();
    if (empty($entity_ids)) {
      return 'field_ui:entity_reference:node';
    }

    /** @var \Drupal\schemadotorg\SchemaDotOrgMappingInterface[] $schemadotorg_mappings */
    $schemadotorg_mappings = $schemadotorg_mapping_storage->loadMultiple($entity_ids);

    // Define the default order for found entity types.
    $entity_types = [
      'paragraph' => NULL,
      'block_content' => NULL,
      'media' => NULL,
      'node' => NULL,
      'user' => NULL,
    ];
    foreach ($schemadotorg_mappings as $schemadotorg_mapping) {
      $entity_types[$schemadotorg_mapping->getTargetEntityTypeId()] = $schemadotorg_mapping->getTargetEntityTypeId();
    }

    // Filter the entity types so that only found entity types are included.
    $entity_types = array_filter($entity_types);

    // Get first entity type.
    $entity_type = reset($entity_types);

    return ($entity_type === 'paragraph')
      ? 'field_ui:entity_reference_revisions:paragraph'
      : "field_ui:entity_reference:$entity_type";
  }

}

