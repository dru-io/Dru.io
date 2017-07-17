<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\Type\Target\ConfigurableTargetInterface;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines a datetime field mapper.
 *
 * @FeedsTarget(
 *   id = "datetime",
 *   field_types = {"datetime"}
 * )
 */
class DateTime extends FieldTargetBase implements ConfigurableTargetInterface {

  /**
   * The datetime storage format.
   *
   * @var string
   */
  protected $storageFormat;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storageFormat = $this->settings['datetime_type'] === 'date' ? DATETIME_DATE_STORAGE_FORMAT : DATETIME_DATETIME_STORAGE_FORMAT;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $values['value'] = $this->prepareDateValue($values['value']);
  }

  /**
   * Prepares a date value.
   *
   * @param string $value
   *   The value to convert to a date.
   *
   * @return string
   *   A formatted date, in UTC time.
   */
  protected function prepareDateValue($value) {
    $value = trim($value);

    // This is a year value.
    if (ctype_digit($value) && strlen($value) === 4) {
      $value = 'January ' . $value;
    }

    if (is_numeric($value)) {
      $date = DrupalDateTime::createFromTimestamp($value, $this->configuration['timezone']);
    }
    elseif (strtotime($value)) {
      $date = new DrupalDateTime($value, $this->configuration['timezone']);
    }

    if (isset($date) && !$date->hasErrors()) {
      return $date->format($this->storageFormat, [
        'timezone' => DATETIME_STORAGE_TIMEZONE,
      ]);
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['timezone' => DATETIME_STORAGE_TIMEZONE];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Timezone handling'),
      '#options' => $this->getTimezoneOptions(),
      '#default_value' => $this->configuration['timezone'],
      '#description' => $this->t('This value will only be used if the timezone is missing.'),
    ];

    return $form;
  }

  /**
   * Returns the timezone options.
   *
   * @return []
   *   A map of timezone options.
   */
  public function getTimezoneOptions() {
    return [
      '__SITE__' => $this->t('Site default'),
    ] + system_time_zones();
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $options = $this->getTimezoneOptions();

    return $this->t('Default timezone: %zone', [
      '%zone' => $options[$this->configuration['timezone']],
    ]);
  }

}
