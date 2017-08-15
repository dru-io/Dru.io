<?php

namespace Drupal\feeds\Feeds\Processor\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Drupal\feeds\Plugin\Type\Processor\ProcessorInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\Entity\User;

/**
 * The configuration form for the CSV parser.
 */
class DefaultEntityProcessorForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $tokens = [
      '@entity' => Unicode::strtolower($this->plugin->entityTypeLabel()),
      '@entities' => Unicode::strtolower($this->plugin->entityTypeLabelPlural()),
    ];

    $form['update_existing'] = [
      '#type' => 'radios',
      '#title' => $this->t('Update existing @entities', $tokens),
      '#description' => $this->t('Existing @entities will be determined using mappings that are <strong>unique</strong>.', $tokens),
      '#options' => [
        ProcessorInterface::SKIP_EXISTING => $this->t('Do not update existing @entities', $tokens),
        ProcessorInterface::REPLACE_EXISTING => $this->t('Replace existing @entities', $tokens),
        ProcessorInterface::UPDATE_EXISTING => $this->t('Update existing @entities', $tokens),
      ],
      '#default_value' => $this->plugin->getConfiguration('update_existing'),
    ];

    $times = [ProcessorInterface::EXPIRE_NEVER, 3600, 10800, 21600, 43200, 86400, 259200, 604800, 2592000, 2592000 * 3, 2592000 * 6, 31536000];
    $period = array_map([$this, 'formatExpire'], array_combine($times, $times));

    $form['expire'] = [
      '#type' => 'select',
      '#title' => $this->t('Expire @entities', $tokens),
      '#options' => $period,
      '#description' => $this->t('Select after how much time @entities should be deleted.', $tokens),
      '#default_value' => $this->plugin->getConfiguration('expire'),
    ];

    // @todo Remove hack.
    $entity_type = \Drupal::entityTypeManager()->getDefinition($this->plugin->entityType());

    if ($entity_type->isSubclassOf(EntityOwnerInterface::class)) {
      $form['owner_feed_author'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Owner: Feed author'),
        '#description' => $this->t('Use the feed author as the owner of the entities to be created.'),
        '#default_value' => $this->plugin->getConfiguration('owner_feed_author'),
      ];

      $form['owner_id'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Owner'),
        '#description' => $this->t('Select the owner of the entities to be created. Leave blank for %anonymous.', ['%anonymous' => \Drupal::config('user.settings')->get('anonymous')]),
        '#target_type' => 'user',
        '#default_value' => User::load($this->plugin->getConfiguration('owner_id')),
        '#states' => [
          'invisible' => [
            'input[name="processor_configuration[owner_feed_author]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    $form['advanced'] = [
      '#title' => $this->t('Advanced settings'),
      '#type' => 'details',
      '#collapsed' => TRUE,
      '#collapsible' => TRUE,
      '#weight' => 10,
    ];

    if ($entity_type->isSubclassOf(EntityOwnerInterface::class)) {
      $form['advanced']['authorize'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Authorize'),
        '#description' => $this->t('Check that the author has permission to create the @entity.', $tokens),
        '#default_value' => $this->plugin->getConfiguration('authorize'),
        '#parents' => ['processor_configuration', 'authorize'],
      ];
    }

    $form['advanced']['skip_hash_check'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Force update'),
      '#description' => $this->t('Forces the update of items even if the feed did not change.'),
      '#default_value' => $this->plugin->getConfiguration('skip_hash_check'),
      '#parents' => ['processor_configuration', 'skip_hash_check'],
      '#states' => [
        'visible' => [
          'input[name="processor_configuration[update_existing]"]' => [
            'value' => ProcessorInterface::UPDATE_EXISTING,
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('owner_id', (int) $form_state->getValue('owner_id', 0));
  }

  /**
   * Formats UNIX timestamps to readable strings.
   *
   * @param int $timestamp
   *   A UNIX timestamp.
   *
   * @return string
   *   A string in the format, "After (time)" or "Never."
   */
  public function formatExpire($timestamp) {
    if ($timestamp == ProcessorInterface::EXPIRE_NEVER) {
      return $this->t('Never');
    }

    return $this->t('after @time', ['@time' => \Drupal::service('date.formatter')->formatInterval($timestamp)]);
  }

}
