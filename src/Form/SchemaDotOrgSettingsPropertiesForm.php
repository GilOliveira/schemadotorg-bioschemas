<?php

namespace Drupal\schemadotorg\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\schemadotorg\Element\SchemaDotOrgSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Schema.org properties settings for this site.
 */
class SchemaDotOrgSettingsPropertiesForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'schemadotorg_properties_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['schemadotorg.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('schemadotorg.settings');

    $form['schema_properties'] = [
      '#tree' => TRUE,
    ];
    $form['schema_properties']['field_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Schema.org property field prefix'),
      '#description' => $this->t('Enter the field prefix to be prepended to a Schema.org property when added to an entity type.')
      . ' '
      . $this->t('Schema.org property field prefix cannot be updated after mappings have been created.'),
      '#default_value' => $config->get('field_prefix'),
      '#parents' => ['field_prefix'],
    ];
    if ($this->entityTypeManager->getStorage('schemadotorg_mapping')->loadMultiple()) {
      $form['schema_properties']['field_prefix']['#disabled'] = TRUE;
      $form['schema_properties']['field_prefix']['#value'] = $config->get('field_prefix');
    }
    $form['schema_properties']['custom_labels'] = [
      '#type' => 'schemadotorg_settings',
      '#settings_type' => SchemaDotOrgSettings::ASSOCIATIVE,
      '#settings_format' => 'SchemaType--propertyName|Custom label or propertyName|Custom label',
      '#title' => $this->t('Schema.org property custom labels'),
      '#description' => $this->t('Enter custom labels for Schema.org types and properties.')
      . ' '
      . $this->t('Schema.org type and property names are case-sensitive and must be an exact match.'),
      '#default_value' => $config->get('schema_properties.custom_labels'),
    ];
    $form['schema_properties']['range_includes'] = [
      '#type' => 'schemadotorg_settings',
      '#settings_type' => SchemaDotOrgSettings::INDEXED_GROUPED,
      '#settings_format' => 'TypeName--propertyName|Type01,Type02,Type03',
      '#title' => $this->t('Schema.org type/property custom range includes'),
      '#description' => $this->t('Enter custom range includes for Schema.org types/properties.'),
      '#default_value' => $config->get('schema_properties.range_includes'),
    ];

    $form['schema_properties']['ignored_properties'] = [
      '#type' => 'schemadotorg_settings',
      '#settings_type' => SchemaDotOrgSettings::INDEXED,
      '#title' => $this->t('Ignored Schema.org properties'),
      '#description' => $this->t('Enter Schema.org properties that should ignored and not displayed on the Schema.org mapping form and simplifies the user experience.'),
      '#default_value' => $config->get('schema_properties.ignored_properties'),
    ];
    $form['schema_properties']['default_field_types'] = [
      '#type' => 'schemadotorg_settings',
      '#settings_type' => SchemaDotOrgSettings::INDEXED_GROUPED,
      '#settings_format' => 'propertyName|field_type_01,field_type_02,field_type_03',
      '#title' => $this->t('Default Schema.org property field types'),
      '#description' => $this->t('Enter default Schema.org property field types used when adding Schema.org properties to an entity type.'),
      '#default_value' => $config->get('schema_properties.default_field_types'),
    ];
    $form['schema_properties']['default_unlimited_fields'] = [
      '#type' => 'schemadotorg_settings',
      '#settings_type' => SchemaDotOrgSettings::INDEXED,
      '#title' => $this->t('Default unlimited Schema.org properties'),
      '#description' => $this->t('Enter Schema.org properties that should default to supporting unlimited values.'),
      '#default_value' => $config->get('schema_properties.default_unlimited_fields'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('schemadotorg.settings')
      ->set('field_prefix', $form_state->getValue('field_prefix'))
      ->set('schema_properties', $form_state->getValue('schema_properties'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
