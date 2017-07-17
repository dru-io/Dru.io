<?php

namespace Drupal\feeds\Feeds\Fetcher\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The configuration form for the upload fetcher.
 */
class UploadFetcherForm extends ExternalPluginFormBase implements ContainerInjectionInterface {

  /**
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * Constructs a DirectoryFetcherForm object.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   The stream wrapper manager.
   */
  public function __construct(StreamWrapperManagerInterface $stream_wrapper_manager) {
    $this->streamWrapperManager = $stream_wrapper_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('stream_wrapper_manager'));
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

    $form['directory'] = [
      '#type' => 'feeds_uri',
      '#title' => $this->t('Upload directory'),
      '#description' => $this->t('Directory where uploaded files get stored. Prefix the path with a scheme. Available schemes: @schemes.', ['@schemes' => implode(', ', $this->getSchemes())]),
      '#default_value' => $this->plugin->getConfiguration('directory'),
      '#required' => TRUE,
      '#allowed_schemes' => $this->getSchemes(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values =& $form_state->getValues();

    $values['allowed_extensions'] = preg_replace('/\s+/', ' ', trim($values['allowed_extensions']));

    // Ensure that the upload directory exists.
    if (!empty($form['directory']) && !file_prepare_directory($values['directory'], FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      $form_state->setError($form['directory'], $this->t('The chosen directory does not exist and attempts to create it failed.'));
    }
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

  /**
   * Returns available schemes.
   *
   * @return string[]
   *   The available schemes.
   */
  protected function getSchemes() {
    return array_keys($this->streamWrapperManager->getWrappers(StreamWrapperInterface::WRITE_VISIBLE));
  }

}
