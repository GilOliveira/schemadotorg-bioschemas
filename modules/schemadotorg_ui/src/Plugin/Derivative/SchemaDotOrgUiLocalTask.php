<?php

namespace Drupal\schemadotorg_ui\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all entity bundles.
 */
class SchemaDotOrgUiLocalTask extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Schema.org entity type manager.
   *
   * @var \Drupal\schemadotorg\SchemaDotOrgEntityTypeManagerInterface
   */
  protected $schemaDotOrgEntityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $instance = new static();
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->schemaDotOrgEntityTypeManager = $container->get('schemadotorg.entity_type_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    $entity_types = $this->schemaDotOrgEntityTypeManager->getEntityTypes();
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if (!in_array($entity_type_id, $entity_types)) {
        continue;
      }

      if ($entity_type->get('field_ui_base_route')) {
        $this->derivatives["overview_$entity_type_id"] = [
          'route_name' => "entity.$entity_type_id.field_ui_fields",
          'title' => $this->t('Fields'),
          'parent_id' => "field_ui.fields:overview_$entity_type_id",
        ] + $base_plugin_definition;
        $this->derivatives["schemadotorg_$entity_type_id"] = [
          'route_name' => "entity.$entity_type_id.schemadotorg_fields",
          'title' => $this->t('Schema.org'),
          'parent_id' => "field_ui.fields:overview_$entity_type_id",
        ] + $base_plugin_definition;
      }
    }

    return $this->derivatives;
  }

}
