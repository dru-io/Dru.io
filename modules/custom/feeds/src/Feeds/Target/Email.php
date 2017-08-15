<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\Plugin\Type\Target\ConfigurableTargetInterface;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;

/**
 * Defines a email field mapper.
 *
 * @FeedsTarget(
 *   id = "email",
 *   field_types = {"email"}
 * )
 */
class Email extends FieldTargetBase implements ConfigurableTargetInterface {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('value')
      ->markPropertyUnique('value');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $values['value'] = trim($values['value']);
    if (!filter_var($values['value'], FILTER_VALIDATE_EMAIL)) {
      $values['value'] = '';
    }
    if ($this->configuration['defuse'] && $values['value']) {
      $values['value'] .= '_test';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['defuse' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['defuse'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Defuse e-mail addresses'),
      '#default_value' => $this->configuration['defuse'],
      '#description' => $this->t('This appends _test to all imported e-mail addresses to ensure they cannot be used as recipients.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return $this->configuration['defuse'] ?
      $this->t('Addresses <strong>will</strong> be defused.') :
      $this->t('Addresses will not be defused.');
  }

}
