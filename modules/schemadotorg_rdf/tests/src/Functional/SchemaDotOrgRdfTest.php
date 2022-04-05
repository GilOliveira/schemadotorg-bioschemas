<?php

namespace Drupal\Tests\schemadotorg_rdf\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\schemadotorg\Entity\SchemaDotOrgMapping;
use Drupal\Tests\schemadotorg\Functional\SchemaDotOrgBrowserTestBase;

/**
 * Tests for Schema.org RDF.
 *
 * @group schemadotorg
 */
class SchemaDotOrgRdfTest extends SchemaDotOrgBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['schemadotorg_rdf'];

  /**
   * A test node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * The test node's Schema.org mapping.
   *
   * @var \Drupal\schemadotorg\Entity\SchemaDotOrgMapping
   */
  protected $nodeMapping;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create Event node with field.
    $this->drupalCreateContentType([
      'type' => 'event',
      'name' => 'Event',
    ]);
    $this->createField('node', 'event');
    $this->createSubTypeField('node', 'event');
    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');
    $display_repository->getViewDisplay('node', 'event')
      ->setComponent('schema_alternate_name')
      ->setComponent('schema_type')->save();

    // Create Event with mapping.
    $node_mapping = SchemaDotOrgMapping::create([
      'target_entity_type_id' => 'node',
      'target_bundle' => 'event',
      'type' => 'Event',
      'subtype' => TRUE,
      'properties' => [
        'title' => ['property' => 'name'],
        'schema_alternate_name' => ['property' => 'alternateName'],
      ],
    ]);
    $node_mapping->save();
    $this->nodeMapping = $node_mapping;

    // Create a node.
    $this->node = $this->drupalCreateNode([
      'type' => 'event',
      'title' => 'A event',
      'schema_alternate_name' => ['value' => 'Another event'],
    ]);
  }

  /**
   * Test Schema.org RDF(a) support.
   */
  public function testRdf() {
    // Check that the Schema.org mapping is sync'd with the RDF mapping.
    $this->drupalGet('/node/' . $this->node->id());
    $this->assertSession()->responseContains('typeof="schema:Event"');
    $this->assertSession()->responseContains('<span property="schema:name">A event</span>');
    $this->assertSession()->responseContains('<span property="schema:name" content="A event" class="hidden"></span>');
    $this->assertSession()->responseContains('<div property="schema:alternateName">Another event</div>');

    // Set the subtype.
    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('schema_type.value', 'BusinessEvent')
      ->execute();
    $this->node->schema_type->target_id = reset($tids);
    $this->node->save();

    // Check replacing the RDF Schema.org type with the Schema.org subtype.
    // @see schemadotorg_rdf_preprocess_node
    $this->drupalGet('/node/' . $this->node->id());
    $this->assertSession()->responseNotContains('typeof="schema:Event"');
    $this->assertSession()->responseContains('typeof="schema:BusinessEvent"');

    // Delete the Schema.org mapping.
    $this->nodeMapping->delete();
    // @todo Determine why the deleted RDF mapping is not clearing the page cache.
    drupal_flush_all_caches();

    // Check that the RDF mapping is removed when Schema.org mapping is deleted.
    $this->drupalGet('/node/' . $this->node->id());
    $this->assertSession()->responseNotContains('<span property="schema:name">A event</span>');
    $this->assertSession()->responseNotContains('<span property="schema:name" content="A event" class="hidden"></span>');
    $this->assertSession()->responseNotContains('<div property="schema:alternateName">Another event</div>');
  }

}
