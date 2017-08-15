<?php

namespace Drupal\feeds\Feeds\Parser\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;

/**
 * Provides a form on the feed edit page for the CsvParser.
 */
class CsvParserFeedForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FeedInterface $feed = NULL) {
    $feed_config = $feed->getConfigurationFor($this->plugin);

    $form['parser']['#tree'] = TRUE;
    $form['parser']['#weight'] = -10;

    $form['parser']['delimiter'] = [
      '#type' => 'select',
      '#title' => $this->t('Delimiter'),
      '#description' => $this->t('The character that delimits fields in the CSV file.'),
      '#options'  => [
        ',' => ',',
        ';' => ';',
        'TAB' => 'TAB',
        '|' => '|',
        '+' => '+',
      ],
      '#default_value' => $feed_config['delimiter'],
    ];

    $form['parser']['no_headers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('No Headers'),
      '#description' => $this->t("Check if the imported CSV file does not start with a header row. If checked, mapping sources must be named '0', '1', '2' etc."),
      '#default_value' => $feed_config['no_headers'],
    ];

    return $form;
  }

}
