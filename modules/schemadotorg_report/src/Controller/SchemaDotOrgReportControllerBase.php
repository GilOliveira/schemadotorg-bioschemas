<?php

namespace Drupal\schemadotorg_report\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;
use Drupal\schemadotorg\SchemaDotOrgManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Schema.org report routes.
 */
abstract class SchemaDotOrgReportControllerBase extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The Schema.org manager service.
   *
   * @var \Drupal\schemadotorg\SchemaDotOrgManagerInterface
   */
  protected $schemaDotOrgManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    $instance->formBuilder = $container->get('form_builder');
    $instance->schemaDotOrgManager = $container->get('schemadotorg.manager');
    return $instance;
  }

  /* ************************************************************************ */
  // Build methods.
  /* ************************************************************************ */

  /**
   * Get Schema.org types or properties filter form.
   *
   * @param string $table
   *   Types or properties table name.
   * @param string $id
   *   Type or property to filter by.
   *
   * @return array
   *   The form array.
   */
  protected function getFilterForm($table, $id = '') {
    return $this->formBuilder->getForm('\Drupal\schemadotorg_report\Form\SchemaDotOrgReportFilterForm', $table, $id);
  }

  /**
   * Build info.
   *
   * @param string $type
   *   Type of info being displayed.
   * @param int $count
   *   The item count to display.
   *
   * @return array
   *   A renderable array containing info.
   */
  protected function buildInfo($type, $count) {
    switch ($type) {
      case 'Thing':
        $info = $this->formatPlural($count, '@count thing', '@count things');
        break;

      case 'Intangible':
        $info = $this->formatPlural($count, '@count intangible', '@count intangibles');
        break;

      case 'Enumeration':
        $info = $this->formatPlural($count, '@count enumeration', '@count enumerations');
        break;

      case 'StructuredValue':
        $info = $this->formatPlural($count, '@count structured value', '@count structured values');
        break;

      case 'DataTypes':
        $info = $this->formatPlural($count, '@count data type', '@count data types');
        break;

      case 'warnings':
        $info = $this->formatPlural($count, '@count warning', '@count warnings');
        break;

      case 'types':
        $info = $this->formatPlural($count, '@count type', '@count types');
        break;

      case 'properties':
        $info = $this->formatPlural($count, '@count property', '@count properties');
        break;

      default:
        $info = $this->formatPlural($count, '@count item', '@count items');
    }
    return [
      '#markup' => $info,
      '#prefix' => '<div>',
      '#suffix' => '</div>',
    ];
  }

  /**
   * Build a table cell.
   *
   * @param string $name
   *   Table cell name.
   * @param string $value
   *   Table cell value.
   *
   * @return array[]|string
   *   A renderable array containing a table cell.
   */
  protected function buildTableCell($name, $value) {
    switch ($name) {
      case 'drupal_name':
      case 'drupal_label':
        return $value;

      case 'comment':
        return ['data' => ['#markup' => $this->formatComment($value)]];

      default:
        $links = $this->getLinks($value);
        if (count($links) > 20) {
          return [
            'data' => [
              '#type' => 'details',
              '#title' => $this->t('@count items', ['@count' => count($links)]),
              'content' => $links,
            ],
          ];
        }
        else {
          return ['data' => $links];
        }
    }
  }

  /**
   * Build Schema.org type as an item list recursively.
   *
   * @param array $ids
   *   An array of Schema.org type ids.
   * @param array $ignored_types
   *   An array of ignored Schema.org type ids.
   *
   * @return array
   *   A renderable array containing Schema.org type as an item list.
   *
   * @see \Drupal\schemadotorg\SchemaDotOrgManager::getTypesChildrenRecursive
   */
  protected function buildItemsRecursive(array $ids, array $ignored_types = []) {
    if (empty($ids)) {
      return [];
    }

    // We must make sure the type is not deprecated or does not exist.
    // @see https://schema.org/docs/attic.home.html
    $types = $this->database->select('schemadotorg_types', 'types')
      ->fields('types', ['label'])
      ->condition('label', $ids, 'IN')
      ->orderBy('label')
      ->execute()
      ->fetchCol();

    $items = [];
    foreach ($types as $type) {
      $items[$type] = [
        '#type' => 'link',
        '#title' => $type,
        '#url' => $this->getItemUrl($type),
      ];

      $children = $this->schemaDotOrgManager->getTypeChildren($type);
      if ($ignored_types) {
        $children = array_diff_key($children, $ignored_types);
      }
      if ($children) {
        $items[$type]['children'] = $this->buildItemsRecursive($children, $ignored_types);
      }
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
  }

  /**
   * Get Schema.org type or property URL.
   *
   * @param string $id
   *   Type or property ID.
   *
   * @return \Drupal\Core\Url
   *   Schema.org type or property URL.
   */
  protected function getItemUrl($id) {
    return Url::fromRoute('schemadotorg_reports', ['id' => $id]);
  }

  /**
   * Format Schema.org type or property comment.
   *
   * @param string $comment
   *   A comment.
   *
   * @return string
   *   Formatted Schema.org type or property comment with links to details.
   */
  protected function formatComment($comment) {
    if (strpos($comment, 'href="/') === FALSE) {
      return $comment;
    }
    $dom = Html::load($comment);
    $a_nodes = $dom->getElementsByTagName('a');
    foreach ($a_nodes as $a_node) {
      $href = $a_node->getAttribute('href');
      if (preg_match('#^/([0-9A-Za-z]+)$#', $href, $match)) {
        $url = $this->getItemUrl($match[1]);
        $a_node->setAttribute('href', $url->toString());
      }
    }
    return Html::serialize($dom);
  }

  /**
   * Get links for Schema.org items (types or properties).
   *
   * @param string $text
   *   A string of comma delimited items (types or properties).
   *
   * @return array
   *   An array of links for Schema.org items (types or properties).
   */
  protected function getLinks($text) {
    $ids = $this->schemaDotOrgManager->parseIds($text);

    $links = [];
    foreach ($ids as $id) {
      $prefix = ($links) ? ', ' : '';
      if ($this->schemaDotOrgManager->isItem($id)) {
        $links[] = [
          '#type' => 'link',
          '#title' => $id,
          '#url' => $this->getItemUrl($id),
          '#prefix' => $prefix,
        ];
      }
      else {
        $links[] = ['#plain_text' => $id, '#prefix' => $prefix];
      }
    }
    return $links;
  }

}
