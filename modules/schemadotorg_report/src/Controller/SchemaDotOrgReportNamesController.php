<?php

namespace Drupal\schemadotorg_report\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Schema.org report names routes.
 */
class SchemaDotOrgReportNamesController extends SchemaDotOrgReportControllerBase {

  /**
   * The Schema.org Names service.
   *
   * @var \Drupal\schemadotorg\SchemaDotOrgNamesInterface
   */
  protected $schemaDotOrgNames;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->schemaDotOrgNames = $container->get('schemadotorg.names');
    return $instance;
  }

  /**
   * Builds the Schema.org names overview or table.
   *
   * @return array
   *   A renderable array containing Schema.org names overview or table.
   */
  public function index($display) {
    if ($display === 'overview') {
      return $this->overview();
    }
    else {
      return $this->table($display);
    }
  }

  /**
   * Builds the Schema.org names overview.
   *
   * @return array
   *   A renderable array containing Schema.org names overview.
   */
  public function overview() {
    $types_query = $this->database->select('schemadotorg_types', 't')->fields('t', ['label']);
    $properties_query = $this->database->select('schemadotorg_properties', 'p')->fields('p', ['label']);
    $labels = $types_query->union($properties_query)->orderBy('label')->execute()->fetchCol();

    $prefixes = [];
    $suffixes = [];
    $words = [];
    foreach ($labels as $label) {
      $name = $this->schemaDotOrgNames->camelCaseToSnakeCase($label);
      $name_parts = explode('_', $name);

      $suffix = end($name_parts);
      $suffixes += [$suffix => 0];
      $suffixes[$suffix]++;

      $prefix = reset($name_parts);
      $prefixes += [$prefix => 0];
      $prefixes[$prefix]++;

      foreach ($name_parts as $name_part) {
        $words += [$name_part => 0];
        $words[$name_part]++;
      }
    }

    $build = [];

    $build['summary'] = [
      '#type' => 'details',
      '#title' => $this->t('Usage summary'),
      '#open' => TRUE,
    ];

    $build['summary']['words'] = $this->buildWordUsage(
      $this->t('Words'),
      $this->t('Word'),
      $words,
      $this->schemaDotOrgNames->getNameAbbreviations()
    );

    $build['summary']['prefixes'] = $this->buildWordUsage(
      $this->t('Prefixes'),
      $this->t('Prefix'),
      $prefixes,
      $this->schemaDotOrgNames->getNamePrefixes()
    );

    $build['summary']['suffixes'] = $this->buildWordUsage(
      $this->t('Suffixes'),
      $this->t('Suffix'),
      $prefixes,
      $this->schemaDotOrgNames->getNameSuffixes()
    );

    return $build;
  }

  protected function buildWordUsage($title, $label, array $words, array $abbreviations) {
    // Remove words that are less than 5 characters.
    $words = array_filter($words, function ($word) {
      return strlen($word) > 5;
    }, ARRAY_FILTER_USE_KEY);
    // Remove words that are only used once.
    $words = array_filter($words, function ($usage) {
      return $usage > 1;
    });
    // Sort by usage.
    asort($words, SORT_NUMERIC);
    $words = array_reverse($words);

    // Header.
    $header = [
      'word' => $label,
      'word_usage' => $this->t('Used'),
      'abbreviation' => $this->t('Abbreviation'),
    ];

    // Rows.
    $rows = [];
    foreach ($words as $word => $usage) {
      $row = [];
      $row['word'] = $word;
      $row['word_usage'] = $usage;
      $row['abbreviation'] = $abbreviations[$word] ?? '';
      $rows[] = $row;
    }

    $build = [
      '#type' => 'details',
      '#title' => $title,
    ];
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
    return $build;
  }

  /**
   * Builds the Schema.org names table.
   *
   * @return array
   *   A renderable array containing Schema.org names table.
   */
  public function table($display) {
    $tables = ['types', 'properties'];
    $is_schema_item = in_array($display, $tables);

    $header = [
      'schema_item' => [
        'data' => $this->t('Schema.org item'),
      ],
      'schema_id' => [
        'data' => $this->t('Schema.org ID'),
      ],
      'schema_label' => [
        'data' => $this->t('Schema.org label'),
      ],
      'original_name' => [
        'data' => $this->t('Original name'),
      ],
      'original_name_length' => [
        'data' => $this->t('#'),
      ],
      'drupal_name' => [
        'data' => $this->t('Drupal name'),
      ],
      'drupal_name_length' => [
        'data' => $this->t('#'),
      ],
    ];

    if ($is_schema_item) {
      $tables = [$display];
      unset($header['schema_item']);
    }

    $rows = [];
    foreach ($tables as $table) {
      $max_length = ($table === 'types') ? 32 : 25;
      $schema_ids = $this->database->select('schemadotorg_' . $table, $table)
        ->fields($table, ['label'])
        ->orderBy('label')
        ->execute()
        ->fetchCol();
      foreach ($schema_ids as $schema_id) {
        $schema_item = ($table === 'types') ? $this->t('Type') : $this->t('Properties');
        $schema_label = $this->schemaDotOrgNames->camelCaseToTitleCase($schema_id);
        $original_name = $this->schemaDotOrgNames->camelCaseToSnakeCase($schema_id);
        $original_name_length = strlen($original_name);
        $drupal_name = $this->schemaDotOrgNames->toDrupalName($schema_id, $max_length);
        $drupal_name_length = strlen($drupal_name);

        $row = [];
        if (!$is_schema_item) {
          $row['schema_item'] = $schema_item;
        }
        $row['schema_id'] = [
          'data' => [
            '#type' => 'link',
            '#title' => $schema_id,
            '#url' => $this->getItemUrl($schema_id),
          ],
        ];
        $row['schema_label'] = $schema_label;
        $row['original_name'] = $original_name;
        $row['original_name_length'] = $original_name_length;
        $row['drupal_name'] = $drupal_name;
        $row['drupal_name_length'] = $drupal_name_length;

        if ($drupal_name_length > $max_length) {
          $class = ['color-error'];
        }
        elseif ($original_name !== $drupal_name) {
          $class = ['color-warning'];
        }
        else {
          $class = [];
        }
        if ($display !== 'warnings' || $class) {
          $rows[$schema_id] = ['data' => $row];
          $rows[$schema_id]['class'] = $class;
        }
      }
    }
    ksort($rows);

    $build = [];
    $build['info'] = $this->buildInfo($display, count($rows));
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
    return $build;
  }

}
