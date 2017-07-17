<?php

namespace Drupal\feeds\Feeds\Fetcher\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a directory fetcher.
 */
class DirectoryFetcherForm extends ExternalPluginFormBase implements ContainerInjectionInterface {

  /**
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected $streamWrapperManager;

  /**
   * Constructs a DirectoryFetcherForm object.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   *   The stream wrapper manager.
   */
  public function __construct(StreamWrapperManager $stream_wrapper_manager) {
    $this->streamWrapperManager = $stream_wrapper_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('stream_wrapper_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['allowed_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions'),
      '#description' => $this->t('Allowed file extensions for upload.'),
      '#default_value' => $this->plugin->getConfiguration('allowed_extensions'),
    ];

    $form['allowed_schemes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed schemes'),
      '#default_value' => $this->plugin->getConfiguration('allowed_schemes'),
      '#options' => $this->getSchemeOptions(),
      '#description' => $this->t('Select the schemes you want to allow for direct upload.'),
    ];

    $form['recursive_scan'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Search recursively'),
      '#default_value' => $this->plugin->getConfiguration('recursive_scan'),
      '#description' => $this->t('Search through sub-directories.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('allowed_schemes', array_filter($form_state->getValue('allowed_schemes', [])));

    $extensions = preg_replace('/\s+/', ' ', trim($form_state->getValue('allowed_extensions', '')));

    $form_state->setValue('allowed_extensions', $extensions);
  }

  /**
   * Returns available scheme options for use in checkboxes or select list.
   *
   * @return array
   *   The available scheme array keyed scheme => description.
   */
  protected function getSchemeOptions() {
    $options = [];
    foreach ($this->streamWrapperManager->getDescriptions(StreamWrapperInterface::WRITE_VISIBLE) as $scheme => $description) {
      $options[$scheme] = Html::escape($scheme . ': ' . $description);
    }

    return $options;
  }

}
