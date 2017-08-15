<?php

namespace Drupal\feeds\Feeds\Parser\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\Type\ExternalPluginFormBase;

/**
 * The configuration form for the CSV parser.
 */
class CsvParserForm extends ExternalPluginFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['delimiter'] = [
      '#type' => 'select',
      '#title' => $this->t('Default delimiter'),
      '#description' => $this->t('Default field delimiter.'),
      '#options' => [
        ',' => ',',
        ';' => ';',
        'TAB' => 'TAB',
        '|' => '|',
        '+' => '+',
      ],
      '#default_value' => $this->plugin->getConfiguration('delimiter'),
    ];
    $form['no_headers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('No headers'),
      '#description' => $this->t("Check if the imported CSV file does not start with a header row. If checked, mapping sources must be named '0', '1', '2' etc."),
      '#default_value' => $this->plugin->getConfiguration('no_headers'),
    ];

    return $form;
  }

}
