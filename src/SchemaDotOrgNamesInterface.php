<?php

namespace Drupal\schemadotorg;

/**
 * Schema.org names interface.
 */
interface SchemaDotOrgNamesInterface {

  /**
   * Get the max length for Schema.org type or property.
   *
   * Drupal limits type and field names to 32 characters.
   * Schema.org fields are prefixed with 'schema_' which limits
   * the name to 25 characters.
   *
   * @param string $table
   *   Schema.org type or property table name.
   *
   * @return int
   *   The max length for Schema.org type (32 characters)
   *   or property (25 characters).
   */
  public function getNameMaxLength($table);

  /**
   * Convert camel case (camelCase) to snake case (snake_case).
   *
   * @param string $string
   *   A camel case string.
   *
   * @return string
   *   The camel case string converted to snake case.
   */
  public function camelCaseToSnakeCase($string);

  /**
   * Convert camel case (camelCase) to title case (Title Case).
   *
   * @param string $string
   *   A camel case string.
   *
   * @return string
   *   The camel case string converted to title case.
   */
  public function camelCaseToTitleCase($string);

  /**
   * Convert camel case (camelCase) to sentence case (Sentence ase).
   *
   * @param string $string
   *   A camel case string.
   *
   * @return string
   *   The camel case string converted to sentence case.
   */
  public function camelCaseToSentenceCase($string);

  /**
   * Convert Schema.org type or property to Drupal label.
   *
   * @param string $table
   *   A Schema.org table.
   * @param string $string
   *   A Schema.org type or property.
   *
   * @return string
   *   Schema.org type or property coverted to a Drupal label.
   */
  public function toDrupalLabel($table, $string);

  /**
   * Convert Schema.org type or property to Drupal machine name.
   *
   * @param string $table
   *   A Schema.org table.
   * @param string $string
   *   A Schema.org type or property.
   *
   * @return string
   *   Schema.org type or property converted to Drupal machine name.
   */
  public function toDrupalName($table, $label);

  /**
   * Get name prefixes.
   *
   * @return string[]
   *   An associative array of name prefixes.
   */
  public function getNamePrefixes();

  /**
   * Get name suffixes.
   *
   * @return string[]
   *   An associative array of name suffixes.
   */
  public function getNameSuffixes();

  /**
   * Get custom abbreviations.
   *
   * @return string[]
   *   An associative array of custom abbreviation.
   */
  public function getNameAbbreviations();

  /**
   * Get custom name abbreviations.
   *
   * @return string[]
   *   An associative array of custom name abbreviation.
   */
  public function getCustomNames();

  /**
   * Get custom titles.
   *
   * @return string[]
   *   An associative array of custom title.
   */
  public function getCustomTitles();

  /**
   * Get acronyms.
   *
   * @return string[]
   *   An array of acronyms.
   */
  public function getAcronyms();

  /**
   * Get minor words.
   *
   * @return string[]
   *   An array of minor words.
   */
  public function getMinorWords();

}
