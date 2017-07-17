<?php

namespace Drupal\feeds\Feeds\Fetcher\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;

/**
 * The configuration form for http fetchers.
 */
class HttpFetcherForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['auto_detect_feeds'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto detect feeds'),
      '#description' => $this->t('If the supplied URL does not point to a feed but an HTML document, attempt to extract a feed URL from the document.'),
      '#default_value' => $this->plugin->getConfiguration('auto_detect_feeds'),
    ];
    $form['use_pubsubhubbub'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use PubSubHubbub'),
      '#description' => $this->t('Attempt to use a <a href="http://en.wikipedia.org/wiki/PubSubHubbub">PubSubHubbub</a> subscription if available.'),
      '#default_value' => $this->plugin->getConfiguration('use_pubsubhubbub'),
    ];
    $form['fallback_hub'] = [
      '#type' => 'url',
      '#title' => $this->t('Fallback hub'),
      '#description' => $this->t('Enter the URL of a fallback hub. <a href="https://pubsubhubbub.superfeedr.com">Superfeedr</a> is a good choice. If given, this hub will be used if a hub for the feed could not be found.'),
      '#default_value' => $this->plugin->getConfiguration('fallback_hub'),
      '#states' => [
        'visible' => [
          'input[name="fetcher_configuration[use_pubsubhubbub]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    // Per feed type override of global http request timeout setting.
    $form['request_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Request timeout'),
      '#description' => $this->t('Timeout in seconds to wait for an HTTP request to finish.'),
      '#default_value' => $this->plugin->getConfiguration('request_timeout'),
      '#min' => 0,
    ];

    return $form;
  }

}
