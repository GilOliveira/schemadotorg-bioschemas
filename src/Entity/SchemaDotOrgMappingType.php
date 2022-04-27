<?php

namespace Drupal\schemadotorg\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\schemadotorg\SchemaDotOrgMappingTypeInterface;

/**
 * Defines the Schema.org mapping type entity type.
 *
 * @ConfigEntityType(
 *   id = "schemadotorg_mapping_type",
 *   label = @Translation("Schema.org mapping type"),
 *   label_collection = @Translation("Schema.org mapping types"),
 *   label_singular = @Translation("Schema.org mapping type"),
 *   label_plural = @Translation("Schema.org mapping types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Schema.org mapping type",
 *     plural = "@count Schema.org mapping types",
 *   ),
 *   handlers = {
 *     "storage" = "\Drupal\schemadotorg\SchemaDotOrgMappingTypeStorage",
 *     "list_builder" = "Drupal\schemadotorg\SchemaDotOrgMappingTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\schemadotorg\Form\SchemaDotOrgMappingTypeForm",
 *       "edit" = "Drupal\schemadotorg\Form\SchemaDotOrgMappingTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "schemadotorg_mapping_type",
 *   admin_permission = "administer schemadotorg",
 *   links = {
 *     "collection" = "/admin/structure/schemadotorg-mapping/type",
 *     "add-form" = "/admin/structure/schemadotorg-mapping/type/add",
 *     "edit-form" = "/admin/structure/schemadotorg-mapping/type/{schemadotorg_mapping_type}",
 *     "delete-form" = "/admin/structure/schemadotorg-mapping/type/{schemadotorg_mapping_type}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   config_export = {
 *     "id",
 *     "target_entity_type_id",
 *     "default_schema_types",
 *     "default_base_fields",
 *     "default_field_weights",
 *     "default_field_groups",
 *     "default_field_group_label_suffix",
 *     "default_field_group_form_type",
 *     "default_field_group_view_type",
 *     "recommended_schema_types",
 *     "default_schema_type_properties",
 *     "default_schema_type_subtypes",
 *   }
 * )
 */
class SchemaDotOrgMappingType extends ConfigEntityBase implements SchemaDotOrgMappingTypeInterface {

  /**
   * Unique ID for the config entity.
   *
   * @var string
   */
  protected $id;

  /**
   * Entity type to be displayed.
   *
   * @var string
   */
  protected $target_entity_type_id;

  /**
   * An associative array of default Schema.org types.
   *
   * @var array
   */
  protected $default_schema_types = [];

  /**
   * An associative array of default Schema.org type properties.
   *
   * @var array
   */
  protected $default_schema_type_properties = [];

  /**
   * An associative array of Schema.org types that support subtyping.
   *
   * @var array
   */
  protected $default_schema_type_subtypes = [];

  /**
   * An associative array of base field mappings.
   *
   * @var array
   */
  protected $default_base_fields = [];

  /**
   * An array of default field weights for Schema.org properties.
   *
   * @var array
   */
  protected $default_field_weights = [];

  /**
   * An associative array of default field groups for Schema.org properties.
   *
   * @var array
   */
  protected $default_field_groups = [];

  /**
   * Default field group label suffix.
   *
   * @var string
   */
  protected $default_field_group_label_suffix = '';

  /**
   * Default field group form type.
   *
   * @var string
   */
  protected $default_field_group_form_type = '';

  /**
   * Default field group view type.
   *
   * @var string
   */
  protected $default_field_group_view_type = '';

  /**
   * An associative array of grouped recommended Schema.org types.
   *
   * @var array
   */
  protected $recommended_schema_types = [];


  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->target_entity_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $entity_type_manager = \Drupal::entityTypeManager();
    return $entity_type_manager->hasDefinition($this->id())
      ? $entity_type_manager->getDefinition($this->id())->getLabel()
      : $this->id();
  }

}
