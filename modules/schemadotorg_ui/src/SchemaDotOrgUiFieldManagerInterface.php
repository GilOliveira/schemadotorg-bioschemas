<?php

namespace Drupal\schemadotorg_ui;

/**
 * Schema.org UI field manager interface.
 */
interface SchemaDotOrgUiFieldManagerInterface {

  /**
   * Add new field mapping option.
   */
  const ADD_FIELD = '_add_';

  /**
   * Determine if a field exists.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The name of the bundle.
   * @param string $field_name
   *   A field name.
   *
   * @return bool
   *   TRUE if a field exists.
   */
  public function fieldExists($entity_type_id, $bundle, $field_name);

  /**
   * Determine if a field storage exists.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $field_name
   *   A field name.
   *
   * @return bool
   *   TRUE if a field storage exists\.
   */
  public function fieldStorageExists($entity_type_id, $field_name);

  /**
   * Gets an existing field instance.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $field_name
   *   A field name.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   An existing field instance.
   */
  public function getField($entity_type_id, $field_name);

  /**
   * Gets a Schema.org type's property's available field types as options.
   *
   * @param string $type
   *   The Schema.org type.
   * @param string $property
   *   The Schema.org property.
   *
   * @return array[]
   *   A property's available field types as options.
   */
  public function getPropertyFieldTypeOptions($type, $property);

  /**
   * Gets available fields as options.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The name of the bundle.
   *
   * @return array
   *   Available fields as options.
   */
  public function getFieldOptions($entity_type_id, $bundle);

  /**
   * Gets field types for Schema.org property.
   *
   * Field types are determined via the following order.
   * - Schema.org property specific field types.
   * - Schema.org entity reference.
   * - Schema.org enumerations.
   * - Drupal allowed values.
   * - Schema.org (data) type  specific field types.
   * - String or entity reference.
   * - Alter field types.
   *
   * @param string $type
   *   The Schema.org type.
   * @param string $property
   *   The Schema.org property.
   *
   * @return array
   *   Field types for Schema.org property.
   *
   * @see hook_schemadotorg_property_field_type_alter()
   */
  public function getSchemaPropertyFieldTypes($type, $property);

}
