<?php

namespace Drupal\feeds\Feeds\Fetcher\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Drupal\feeds\Utility\File;

/**
 * Provides a form on the feed edit page for the DirectoryFetcher.
 */
class DirectoryFetcherFeedForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $args = ['%schemes' => implode(', ', $this->plugin->getConfiguration('allowed_schemes'))];

    $form['source'] = [
      '#title' => $this->t('Server file or directory path'),
      '#type' => 'feeds_uri',
      '#default_value' => $feed->getSource(),
      '#allowed_schemes' => $this->plugin->getConfiguration('allowed_schemes'),
      '#description' => $this->t('The allowed schemes are: %schemes', $args),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $source = $form_state->getValue('source');

    if (!is_readable($source) || (!is_dir($source) && !is_file($source))) {
      $form_state->setError($form['source'], $this->t('%source is not a readable directory or file.', ['%source' => $source]));
      return;
    }

    if (is_dir($source)) {
      return;
    }

    $allowed = $this->plugin->getConfiguration('allowed_extensions');

    // Validate a single file.
    if (!File::validateExtension($source, $allowed)) {
      $form_state->setError($form['source'], $this->t('%source has an invalid file extension.', ['%source' => $source]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $feed->setSource($form_state->getValue('source'));
  }

}
