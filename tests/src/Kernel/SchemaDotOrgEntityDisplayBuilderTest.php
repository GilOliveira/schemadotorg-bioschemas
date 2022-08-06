<?php

namespace Drupal\Tests\schemadotorg\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\schemadotorg\Entity\SchemaDotOrgMappingType;

/**
 * Tests the Schema.org entity display builder service.
 *
 * @coversClass \Drupal\schemadotorg\SchemaDotOrgEntityDisplayBuilder
 * @group schemadotorg
 */
class SchemaDotOrgEntityDisplayBuilderTest extends SchemaDotOrgKernelEntityTestBase {

  /**
   * Modules to install.
   *
   * @var string[]
   */
  protected static $modules = ['field_group'];

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The Schema.org schema type manager.
   *
   * @var \Drupal\schemadotorg\SchemaDotOrgEntityDisplayBuilderInterface
   */
  protected $schemaEntityDisplayBuilder;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityDisplayRepository = $this->container->get('entity_display.repository');
    $this->schemaEntityDisplayBuilder = $this->container->get('schemadotorg.entity_display_builder');
  }

  /**
   * Test Schema.org entity display builder.
   */
  public function testEntityDisplayBuilder() {
    // Allow Schema.org Thing to have default properties.
    $this->config('schemadotorg.settings')
      ->set('schema_types.default_properties.Thing', ['name', 'alternateName'])
      ->save();

    // Update field weights.
    $mapping_type = SchemaDotOrgMappingType::load('node');
    $mapping_type->set('default_field_weights', ['name', 'alternateName', 'description']);
    $mapping_type->set('default_field_groups', []);
    $mapping_type->save();

    // Create node.thing.
    $mapping = $this->createSchemaEntity('node', 'Thing');

    // Check that default view display is created for Thing.
    $view_display = $this->entityDisplayRepository->getViewDisplay('node', 'thing', 'default');

    $field_group = $view_display->getThirdPartySettings('field_group');
    $this->assertEquals(['schema_alternate_name'], $field_group['group_thing']['children']);
    $this->assertEquals('Thing information', $field_group['group_thing']['label']);
    $this->assertEquals('fieldset', $field_group['group_thing']['format_type']);

    $component = $view_display->getComponent('schema_alternate_name');
    $this->assertEquals('string', $component['type']);
    $this->assertEquals('above', $component['label']);
    $this->assertEquals(2, $component['weight']);

    $component = $view_display->getComponent('links');
    $this->assertEquals(100, $component['weight']);

    // Check that default form display is created for Thing.
    $form_display = $this->entityDisplayRepository->getFormDisplay('node', 'thing', 'default');

    $field_group = $form_display->getThirdPartySettings('field_group');
    $this->assertEquals(['schema_alternate_name'], $field_group['group_thing']['children']);
    $this->assertEquals('Thing information', $field_group['group_thing']['label']);
    $this->assertEquals('details', $field_group['group_thing']['format_type']);

    $component = $form_display->getComponent('schema_alternate_name');
    $this->assertEquals('string_textfield', $component['type']);
    $this->assertEquals(2, $component['weight']);

    $component = $form_display->getComponent('status');
    $this->assertEquals(120, $component['weight']);

    // Add body field to node.thing.
    // @see node_add_body_field()
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'thing',
      'label' => 'Body',
      'settings' => ['display_summary' => TRUE],
    ]);
    $field->save();
    $mapping
      ->setSchemaPropertyMapping('body', 'description')
      ->save();

    // Check settings entity displays for a field.
    $field_values = [
      'field_name' => 'body',
      'entity_type' => 'node',
      'bundle' => 'thing',
      'label' => 'Description',
    ];
    $widget_id = 'text_textarea_with_summary';
    $widget_settings = [
      'placeholder' => 'This is a placeholder',
      'show_summary' => TRUE,
    ];
    $formatter_id = 'text_default';
    $formatter_settings = [];
    $this->schemaEntityDisplayBuilder->setFieldDisplays($field_values, $widget_id, $widget_settings, $formatter_id, $formatter_settings);

    $view_display = $this->entityDisplayRepository->getViewDisplay('node', 'thing', 'default');
    $component = $view_display->getComponent('body');
    $this->assertEquals('text_default', $component['type']);
    // Check that fields added to an existing view display is appended last
    // (after the links component).
    // @see \Drupal\schemadotorg\SchemaDotOrgMappingManager::saveMapping
    $this->assertEquals(101, $component['weight']);

    $form_display = $this->entityDisplayRepository->getFormDisplay('node', 'thing', 'default');
    $component = $form_display->getComponent('body');
    $this->assertEquals('text_textarea_with_summary', $component['type']);
    $this->assertEquals('This is a placeholder', $component['settings']['placeholder']);
    $this->assertTrue($component['settings']['show_summary']);
    // Check that fields added to an existing form display is appended last
    // (after the status component).
    // @see \Drupal\schemadotorg\SchemaDotOrgMappingManager::saveMapping
    $this->assertEquals(121, $component['weight']);

    // Check (re)settings entity display field weights for Schema.org properties.
    $this->schemaEntityDisplayBuilder->setFieldWeights(
      'node',
      'thing',
      $mapping->getSchemaProperties()
    );
    $view_display = $this->entityDisplayRepository->getViewDisplay('node', 'thing', 'default');
    $this->assertEquals(2, $view_display->getComponent('schema_alternate_name')['weight']);
    $this->assertEquals(3, $view_display->getComponent('body')['weight']);
    $form_display = $this->entityDisplayRepository->getFormDisplay('node', 'thing', 'default');
    $this->assertEquals(2, $form_display->getComponent('schema_alternate_name')['weight']);
    $this->assertEquals(3, $form_display->getComponent('body')['weight']);

    // Check settings entity display field groups for Schema.org properties.
    $mapping_type->set('default_field_groups', [
      'general' => [
        'label' => 'General information',
        // Note: Switching the order of alterateName and description.
        'properties' => ['title', 'description', 'alternateName'],
      ],
    ]);
    $mapping_type->save();
    $this->schemaEntityDisplayBuilder->setFieldGroups(
      'node',
      'thing',
      'Thing',
      $mapping->getSchemaProperties()
    );

    $view_display = $this->entityDisplayRepository->getViewDisplay('node', 'thing', 'default');
    $this->assertEquals(2, $view_display->getComponent('schema_alternate_name')['weight']);
    $this->assertEquals(1, $view_display->getComponent('body')['weight']);

    $form_display = $this->entityDisplayRepository->getFormDisplay('node', 'thing', 'default');
    $this->assertEquals(2, $form_display->getComponent('schema_alternate_name')['weight']);
    $this->assertEquals(1, $form_display->getComponent('body')['weight']);

    $field_group = $view_display->getThirdPartySettings('field_group');
    $this->assertEquals([], $field_group['group_thing']['children']);
    $this->assertEquals(['schema_alternate_name', 'body'], $field_group['group_general']['children']);
    $this->assertEquals('General information', $field_group['group_general']['label']);
    $this->assertEquals('fieldset', $field_group['group_general']['format_type']);
  }

}
