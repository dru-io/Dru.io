<?php

namespace Drupal\feeds;

/**
 * Interface for describing target data.
 */
interface TargetDefinitionInterface {

  /**
   * Helper factory method.
   *
   * @return \Drupal\feeds\TargetDefinitionInterface
   *   A new target definition.
   */
  public static function create();

  /**
   * Returns the target plugin id.
   *
   * @return string
   *   The target plugin id.
   */
  public function getPluginId();

  /**
   * Returns the target label.
   *
   * @return string
   *   The target label.
   */
  public function getLabel();

  /**
   * Returns the target description.
   *
   * @return string
   *   The target description.
   */
  public function getDescription();

  /**
   * Returns whether this target has a given property.
   *
   * @param string
   *   The property to check for.
   *
   * @return bool
   *   Returns true if the property exists, and false if not.
   */
  public function hasProperty($property);

  /**
   * Returns the list of properties.
   *
   * @return string[]
   *   A list of property keys.
   */
  public function getProperties();

  /**
   * Returns the label for a given property.
   *
   * @param string $property
   *   The property to get a label for.
   *
   * @return string
   *   The label for a property.
   */
  public function getPropertyLabel($property);

  /**
   * Returns the description for a given property.
   *
   * @param string $property
   *   The property to get a description for.
   *
   * @return string
   *   The description for a property.
   */
  public function getPropertyDescription($property);

  /**
   * Retuns whether a property is unique.
   *
   * @param string $property
   *   The property to check.
   *
   * @return bool
   *   Returns true if the property is unique, and false if not.
   */
  public function isUnique($property);

}
