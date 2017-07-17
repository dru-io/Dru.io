<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\Plugin\Type\Target\ConfigurableTargetInterface;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines an entity reference mapper.
 *
 * @FeedsTarget(
 *   id = "entity_reference",
 *   field_types = {"entity_reference"},
 *   arguments = {"@entity_type.manager", "@entity.query", "@entity_field.manager", "@entity.repository"}
 * )
 */
class EntityReference extends FieldTargetBase implements ConfigurableTargetInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity query factory object.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs an EntityReference object.
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
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityTypeManagerInterface $entity_type_manager, QueryFactory $query_factory, EntityFieldManagerInterface $entity_field_manager, EntityRepositoryInterface $entity_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->queryFactory = $query_factory;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityRepository = $entity_repository;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    // Only reference content entities. Configuration entities will need custom
    // targets.
    $type = $field_definition->getSetting('target_type');
    if (!\Drupal::entityTypeManager()->getDefinition($type)->entityClassImplements('\Drupal\Core\Entity\ContentEntityInterface')) {
      return;
    }

    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('target_id');
  }

  protected function getPotentialFields() {
    $field_definitions = $this->entityFieldManager->getBaseFieldDefinitions($this->getEntityType());
    $field_definitions = array_filter($field_definitions, [$this, 'filterFieldTypes']);
    $options = [];
    foreach ($field_definitions as $id => $definition) {
      $options[$id] = Html::escape($definition->getLabel());
    }

    return $options;
  }

  /**
   * Callback for the potential field filter.
   *
   * Checks whether the provided field is available to be used as reference.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field
   *   The field to check.
   *
   * @return bool
   *   TRUE if the field can be used as reference otherwise FALSE.
   *
   * @see ::getPotentialFields()
   */
  protected function filterFieldTypes(FieldDefinitionInterface $field) {
    if ($field->isComputed()) {
      return FALSE;
    }

    switch ($field->getType()) {
      case 'integer':
      case 'string':
      case 'text_long':
      case 'path':
      case 'uuid':
        return TRUE;

      default:
        return FALSE;
    }
  }

  protected function getEntityType() {
    return $this->settings['target_type'];
  }

  protected function getBundles() {
    return $this->settings['handler_settings']['target_bundles'];
  }

  protected function getBundleKey() {
    return $this->entityTypeManager->getDefinition($this->getEntityType())->getKey('bundle');
  }

  protected function getLabelKey() {
    return $this->entityTypeManager->getDefinition($this->getEntityType())->getKey('label');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    if ($target_id = $this->findEntity($values['target_id'], $this->configuration['reference_by'])) {
      $values['target_id'] = $target_id;
      return;
    }

    throw new EmptyFeedException();
  }

  /**
   * Searches for an entity by entity key.
   *
   * @param string $value
   *   The value to search for.
   *
   * @return int|bool
   *   The entity id, or false, if not found.
   */
  protected function findEntity($value, $field) {
    // When referencing by UUID, use the EntityRepository service.
    if ($this->configuration['reference_by'] === 'uuid') {
      if (NULL !== ($entity = $this->entityRepository->loadEntityByUuid($this->getEntityType(), $value))) {
        return $entity->id();
      }
    }
    else {
      $query = $this->queryFactory->get($this->getEntityType());

      if ($bundles = $this->getBundles()) {
        $query->condition($this->getBundleKey(), $bundles, 'IN');
      }

      $ids = array_filter($query->condition($field, $value)->range(0, 1)->execute());
      if ($ids) {
        return reset($ids);
      }
    }

    if ($this->configuration['autocreate'] && $this->configuration['reference_by'] === $this->getLabelKey()) {
      return $this->createEntity($value);
    }

    return FALSE;
  }

  protected function createEntity($value) {
    if (!strlen(trim($value))) {
      return FALSE;
    }

    $bundles = $this->getBundles();

    $entity = $this->entityTypeManager->getStorage($this->getEntityType())->create([
      $this->getLabelKey() => $value,
      $this->getBundleKey() => reset($bundles),
    ]);
    $entity->save();

    return $entity->id();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'reference_by' => $this->getLabelKey(),
      'autocreate' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = $this->getPotentialFields();

    // Hack to find out the target delta.
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, 'target-settings-') === 0) {
        list(, , $delta) = explode('-', $key);
        break;
      }
    }

    $form['reference_by'] = [
      '#type' => 'select',
      '#title' => $this->t('Reference by'),
      '#options' => $options,
      '#default_value' => $this->configuration['reference_by'],
    ];

    $form['autocreate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autocreate entity'),
      '#default_value' => $this->configuration['autocreate'],
      '#states' => [
        'visible' => [
          ':input[name="mappings[' . $delta . '][settings][reference_by]"]' => [
            'value' => $this->getLabelKey(),
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $options = $this->getPotentialFields();

    $summary = [];

    if ($this->configuration['reference_by'] && isset($options[$this->configuration['reference_by']])) {
      $summary[] = $this->t('Reference by: %message', ['%message' => $options[$this->configuration['reference_by']]]);
    }
    else {
      $summary[] = $this->t('Please select a field to reference by.');
    }

    if ($this->configuration['reference_by'] === $this->getLabelKey()) {
      $create = $this->configuration['autocreate'] ? $this->t('Yes') : $this->t('No');
      $summary[] = $this->t('Autocreate terms: %create', ['%create' => $create]);
    }

    return implode('<br>', $summary);
  }

}
