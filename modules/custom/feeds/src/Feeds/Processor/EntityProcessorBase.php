<?php

namespace Drupal\feeds\Feeds\Processor;

use Doctrine\Common\Inflector\Inflector;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Entity\FeedType;
use Drupal\feeds\Exception\EntityAccessException;
use Drupal\feeds\Exception\ValidationException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\ItemInterface;
use Drupal\feeds\Plugin\Type\Processor\EntityProcessorInterface;
use Drupal\feeds\StateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\Entity\User;

/**
 * Defines a base entity processor.
 *
 * Creates entities from feed items.
 */
abstract class EntityProcessorBase extends ProcessorBase implements EntityProcessorInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity storage controller for the entity type being processed.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storageController;

  /**
   * The entity info for the selected entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The entity query factory object.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Flag indicating that this processor is locked.
   *
   * @var bool
   */
  protected $isLocked;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs an EntityProcessorBase object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityTypeManagerInterface $entity_type_manager, QueryFactory $query_factory, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityType = $entity_type_manager->getDefinition($plugin_definition['entity_type']);
    $this->storageController = $entity_type_manager->getStorage($plugin_definition['entity_type']);
    $this->queryFactory = $query_factory;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function process(FeedInterface $feed, ItemInterface $item, StateInterface $state) {
    $existing_entity_id = $this->existingEntityId($feed, $item);
    $skip_existing = $this->configuration['update_existing'] == static::SKIP_EXISTING;

    // Bulk load existing entities to save on db queries.
    if ($skip_existing && $existing_entity_id) {
      return;
    }

    // Delay building a new entity until necessary.
    if ($existing_entity_id) {
      $entity = $this->storageController->load($existing_entity_id);
    }

    $hash = $this->hash($item);
    $changed = $existing_entity_id && ($hash !== $entity->get('feeds_item')->hash);

    // Do not proceed if the item exists, has not changed, and we're not
    // forcing the update.
    if ($existing_entity_id && !$changed && !$this->configuration['skip_hash_check']) {
      return;
    }

    // Build a new entity.
    if (!$existing_entity_id) {
      $entity = $this->newEntity($feed);
    }

    try {
      // Set field values.
      $this->map($feed, $entity, $item);
      $this->entityValidate($entity);

      // This will throw an exception on failure.
      $this->entitySaveAccess($entity);
      // Set the values that we absolutely need.
      $entity->get('feeds_item')->target_id = $feed->id();
      $entity->get('feeds_item')->hash = $hash;
      $entity->get('feeds_item')->imported = REQUEST_TIME;

      // And... Save! We made it.
      $this->storageController->save($entity);

      // Track progress.
      $existing_entity_id ? $state->updated++ : $state->created++;
    }

    // Something bad happened, log it.
    catch (\Exception $e) {
      $state->failed++;
      $state->setMessage($e->getMessage(), 'warning');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clear(FeedInterface $feed, StateInterface $state) {
    // Build base select statement.
    $query = $this->queryFactory->get($this->entityType())
      ->condition('feeds_item.target_id', $feed->id());

    // If there is no total, query it.
    if (!$state->total) {
      $count_query = clone $query;
      $state->total = (int) $count_query->count()->execute();
    }

    // Delete a batch of entities.
    $entity_ids = $query->range(0, 10)->execute();

    if ($entity_ids) {
      $this->entityDeleteMultiple($entity_ids);
      $state->deleted += count($entity_ids);
      $state->progress($state->total, $state->deleted);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function entityType() {
    return $this->pluginDefinition['entity_type'];
  }

  /**
   * The entity's bundle key.
   *
   * @return string|null
   *   The bundle type this processor operates on, or null if it is undefined.
   */
  public function bundleKey() {
    return $this->entityType->getKey('bundle');
  }

  /**
   * Bundle type this processor operates on.
   *
   * Defaults to the entity type for entities that do not define bundles.
   *
   * @return string|null
   *   The bundle type this processor operates on, or null if it is undefined.
   *
   * @todo We should be more careful about missing bundles.
   */
  public function bundle() {
    if (!$bundle_key = $this->entityType->getKey('bundle')) {
      return $this->entityType();
    }
    if (isset($this->configuration['values'][$bundle_key])) {
      return $this->configuration['values'][$bundle_key];
    }
  }

  /**
   * Returns the bundle label for the entity being processed.
   *
   * @return string
   *   The bundle label.
   */
  public function bundleLabel() {
    if ($label = $this->entityType->getBundleLabel()) {
      return $label;
    }
    return $this->t('Bundle');
  }

  /**
   * Provides a list of bundle options for use in select lists.
   *
   * @return array
   *   A keyed array of bundle => label.
   */
  public function bundleOptions() {
    $options = [];
    foreach ($this->entityTypeBundleInfo->getBundleInfo($this->entityType()) as $bundle => $info) {
      if (!empty($info['label'])) {
        $options[$bundle] = $info['label'];
      }
      else {
        $options[$bundle] = $bundle;
      }
    }

    return $options;
  }

  /**
   * Returns the label of the entity type being processed.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The label of the entity type.
   */
  public function entityTypeLabel() {
    return $this->entityType->getLabel();
  }

  /**
   * Returns the plural label of the entity type being processed.
   *
   * @return string
   *   The plural label of the entity type.
   */
  public function entityTypeLabelPlural() {
    return Inflector::pluralize((string) $this->entityTypeLabel());
  }

  /**
   * Returns the label for items being created, updated, or deleted.
   *
   * @return string
   *   The item label.
   */
  public function getItemLabel() {
    if (!$this->entityType->getKey('bundle')) {
      return $this->entityTypeLabel();
    }
    $storage = $this->entityTypeManager->getStorage($this->entityType->getBundleEntityType());
    return $storage->load($this->configuration['values'][$this->entityType->getKey('bundle')])->label();
  }

  /**
   * Returns the plural label for items being created, updated, or deleted.
   *
   * @return string
   *   The plural item label.
   */
  public function getItemLabelPlural() {
    return Inflector::pluralize($this->getItemLabel());
  }

  /**
   * {@inheritdoc}
   */
  protected function newEntity(FeedInterface $feed) {
    $values = $this->configuration['values'];
    $entity = $this->storageController->create($values);
    $entity->enforceIsNew();

    if ($entity instanceof EntityOwnerInterface) {
      if ($this->configuration['owner_feed_author']) {
        $entity->setOwnerId($feed->getOwnerId());
      }
      else {
        $entity->setOwnerId($this->configuration['owner_id']);
      }
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function entityValidate(EntityInterface $entity) {
    $violations = $entity->validate();
    if (!count($violations)) {
      return;
    }

    $errors = [];

    foreach ($violations as $violation) {
      $error = $violation->getMessage();

      // Try to add more context to the message.
      // @todo if an exception occurred because of a different bundle, add more
      // context to the message.
      $invalid_value = $violation->getInvalidValue();
      if ($invalid_value instanceof FieldItemListInterface) {
        // The invalid value is a field. Get more information about this field.
        $error = new FormattableMarkup('@name (@property_name): @error', [
          '@name' => $invalid_value->getFieldDefinition()->getLabel(),
          '@property_name' => $violation->getPropertyPath(),
          '@error' => $error,
        ]);
      }
      else {
        $error = new FormattableMarkup('@property_name: @error', [
          '@property_name' => $violation->getPropertyPath(),
          '@error' => $error,
        ]);
      }

      $errors[] = $error;
    }

    $element = [
      '#theme' => 'item_list',
      '#items' => $errors,
    ];

    // Compose error message. If available, use the entity label to indicate
    // which item failed. Fallback to the GUID value (if available) or else
    // no indication.
    $label = $entity->label();
    $guid = $entity->get('feeds_item')->guid;

    $messages = [];
    $args = [
      '@entity' => Unicode::strtolower($this->entityTypeLabel()),
      '%label' => $label,
      '%guid' => $guid,
      '@errors' => \Drupal::service('renderer')->render($element),
      ':url' => $this->url('entity.feeds_feed_type.mapping', ['feeds_feed_type' => $this->feedType->id()]),
    ];
    if ($label || $label === '0' || $label === 0) {
      $messages[] = $this->t('The @entity %label failed to validate with the following errors: @errors', $args);
    }
    elseif ($guid || $guid === '0' || $guid === 0) {
      $messages[] = $this->t('The @entity with GUID %guid failed to validate with the following errors: @errors', $args);
    }
    else {
      $messages[] = $this->t('An entity of type "@entity" failed to validate with the following errors: @errors', $args);
    }
    $messages[] = $this->t('Please check your <a href=":url">mappings</a>.', $args);

    // Concatenate strings as markup to mark them as safe.
    $message_element = [
      '#markup' => implode("\n", $messages),
    ];
    $message = \Drupal::service('renderer')->render($message_element);

    throw new ValidationException($message);
  }

  /**
   * {@inheritdoc}
   */
  protected function entitySaveAccess(EntityInterface $entity) {
    // No need to authorize.
    if (!$this->configuration['authorize'] || !$entity instanceof EntityOwnerInterface) {
      return;
    }

    // If the uid was mapped directly, rather than by email or username, it
    // could be invalid.
    if (!$account = $entity->getOwner()) {
      throw new EntityAccessException($this->t('Invalid user mapped to %label.', ['%label' => $entity->label()]));
    }

    // We don't check access for anonymous users.
    if ($account->isAnonymous()) {
      return;
    }

    $op = $entity->isNew() ? 'create' : 'update';

    // Access granted.
    if ($entity->access($op, $account)) {
      return;
    }

    $args = [
      '%name' => $account->getUsername(),
      '@op' => $op,
      '@bundle' => $this->getItemLabelPlural(),
    ];
    throw new EntityAccessException($this->t('User %name is not authorized to @op @bundle.', $args));
  }

  /**
   * {@inheritdoc}
   */
  protected function entityDeleteMultiple(array $entity_ids) {
    $entities = $this->storageController->loadMultiple($entity_ids);
    $this->storageController->delete($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = [
      'update_existing' => static::SKIP_EXISTING,
      'skip_hash_check' => FALSE,
      'values' => [$this->entityType->getKey('bundle') => NULL],
      'authorize' => $this->entityType->isSubclassOf('Drupal\user\EntityOwnerInterface'),
      'expire' => static::EXPIRE_NEVER,
      'owner_id' => 0,
      'owner_feed_author' => 0,
    ];

    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function onFeedTypeSave($update = TRUE) {
    $this->prepareFeedsItemField();
  }

  /**
   * {@inheritdoc}
   */
  public function onFeedTypeDelete() {
    $this->removeFeedItemField();
  }

  /**
   * Prepares the feeds_item field.
   *
   * @todo How does ::load() behave for deleted fields?
   */
  protected function prepareFeedsItemField() {
    // Create field if it doesn't exist.
    if (!FieldStorageConfig::loadByName($this->entityType(), 'feeds_item')) {
      FieldStorageConfig::create([
        'field_name' => 'feeds_item',
        'entity_type' => $this->entityType(),
        'type' => 'feeds_item',
        'translatable' => FALSE,
      ])->save();
    }
    // Create field instance if it doesn't exist.
    if (!FieldConfig::loadByName($this->entityType(), $this->bundle(), 'feeds_item')) {
      FieldConfig::create([
        'label' => 'Feeds item',
        'description' => '',
        'field_name' => 'feeds_item',
        'entity_type' => $this->entityType(),
        'bundle' => $this->bundle(),
      ])->save();
    }
  }

  /**
   * Deletes the feeds_item field.
   */
  protected function removeFeedItemField() {
    $storage_in_use = FALSE;
    $instance_in_use = FALSE;

    foreach (FeedType::loadMultiple() as $feed_type) {
      if ($feed_type->id() === $this->feedType->id()) {
        continue;
      }
      $processor = $feed_type->getProcessor();
      if (!$processor instanceof EntityProcessorInterface) {
        continue;
      }

      if ($processor->entityType() === $this->entityType()) {
        $storage_in_use = TRUE;

        if ($processor->bundle() === $this->bundle()) {
          $instance_in_use = TRUE;
          break;
        }
      }
    }

    if ($instance_in_use) {
      return;
    }

    // Delete the field instance.
    if ($config = FieldConfig::loadByName($this->entityType(), $this->bundle(), 'feeds_item')) {
      $config->delete();
    }

    if ($storage_in_use) {
      return;
    }

    // Delte the field storage.
    if ($storage = FieldStorageConfig::loadByName($this->entityType(), 'feeds_item')) {
      $storage->delete();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function expiryTime() {
    return $this->configuration['expire'];
  }

  /**
   * {@inheritdoc}
   */
  public function getExpiredIds(FeedInterface $feed, $time = NULL) {
    if ($time === NULL) {
      $time = $this->expiryTime();
    }
    if ($time == static::EXPIRE_NEVER) {
      return;
    }

    return $this->queryFactory->get($this->entityType())
      ->condition('feeds_item.target_id', $feed->id())
      // ->condition('feeds_item.imported', REQUEST_TIME -1, '<')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function expireItem(FeedInterface $feed, $item_id, StateInterface $state) {
    $this->entityDeleteMultiple([$item_id]);
    $state->total++;
  }

  /**
   * {@inheritdoc}
   */
  public function getItemCount(FeedInterface $feed) {
    return $this->queryFactory->get($this->entityType())
      ->condition('feeds_item.target_id', $feed->id())
      ->count()
      ->execute();
  }

  /**
   * Returns an existing entity id.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed being processed.
   * @param \Drupal\feeds\Feeds\Item\ItemInterface $item
   *   The item to find existing ids for.
   *
   * @return int|false
   *   The integer of the entity, or false if not found.
   */
  protected function existingEntityId(FeedInterface $feed, ItemInterface $item) {
    foreach ($this->feedType->getMappings() as $delta => $mapping) {
      if (empty($mapping['unique'])) {
        continue;
      }

      foreach ($mapping['unique'] as $key => $true) {
        $plugin = $this->feedType->getTargetPlugin($delta);
        $entity_id = $plugin->getUniqueValue($feed, $mapping['target'], $key, $item->get($mapping['map'][$key]));
        if ($entity_id) {
          return $entity_id;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildAdvancedForm(array $form, FormStateInterface $form_state) {
    if ($bundle_key = $this->entityType->getKey('bundle')) {
      $form['values'][$bundle_key] = [
        '#type' => 'select',
        '#options' => $this->bundleOptions(),
        '#title' => $this->bundleLabel(),
        '#required' => TRUE,
        '#default_value' => $this->bundle() ?: key($this->bundleOptions()),
        '#disabled' => $this->isLocked(),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    if ($this->isLocked === NULL) {
      // Look for feeds.
      $this->isLocked = (bool) $this->queryFactory->get('feeds_feed')
        ->condition('type', $this->feedType->id())
        ->range(0, 1)
        ->execute();
    }

    return $this->isLocked;
  }

  /**
   * Creates an MD5 hash of an item.
   *
   * Includes mappings so that items will be updated if the mapping
   * configuration has changed.
   *
   * @param \Drupal\feeds\Feeds\Item\ItemInterface $item
   *   The item to hash.
   *
   * @return string
   *   An MD5 hash.
   */
  protected function hash(ItemInterface $item) {
    return hash('md5', serialize($item) . serialize($this->feedType->getMappings()));
  }

  /**
   * Execute mapping on an item.
   *
   * This method encapsulates the central mapping functionality. When an item is
   * processed, it is passed through map() where the properties of $source_item
   * are mapped onto $target_item following the processor's mapping
   * configuration.
   */
  protected function map(FeedInterface $feed, EntityInterface $entity, ItemInterface $item) {
    $mappings = $this->feedType->getMappings();

    // Mappers add to existing fields rather than replacing them. Hence we need
    // to clear target elements of each item before mapping in case we are
    // mapping on a prepopulated item such as an existing node.
    foreach ($mappings as $mapping) {
      unset($entity->{$mapping['target']});
    }

    // Gather all of the values for this item.
    $source_values = [];
    foreach ($mappings as $mapping) {
      $target = $mapping['target'];

      foreach ($mapping['map'] as $column => $source) {

        if (!isset($source_values[$target][$column])) {
          $source_values[$target][$column] = [];
        }

        $value = $item->get($source);
        if (!is_array($value)) {
          $source_values[$target][$column][] = $value;
        }
        else {
          $source_values[$target][$column] = array_merge($source_values[$target][$column], $value);
        }
      }
    }

    // Rearrange values into Drupal's field structure.
    $field_values = [];
    foreach ($source_values as $field => $field_value) {
      $field_values[$field] = [];
      foreach ($field_value as $column => $values) {
        // Use array_values() here to keep our $delta clean.
        foreach (array_values($values) as $delta => $value) {
          $field_values[$field][$delta][$column] = $value;
        }
      }
    }

    // Set target values.
    foreach ($mappings as $delta => $mapping) {
      $plugin = $this->feedType->getTargetPlugin($delta);
      $plugin->setTarget($feed, $entity, $mapping['target'], $field_values[$mapping['target']]);
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Sort this out so that we aren't calling \Drupal::database()->delete()
   * here.
   */
  public function onFeedDeleteMultiple(array $feeds) {
    $fids = [];
    foreach ($feeds as $feed) {
      $fids[] = $feed->id();
    }
    $table = $this->entityType() . '__feeds_item';
    \Drupal::database()->delete($table)
      ->condition('feeds_item_target_id', $fids, 'IN')
      ->execute();
  }

}
