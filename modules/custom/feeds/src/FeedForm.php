<?php

namespace Drupal\feeds;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Plugin\PluginFormFactory;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Form controller for the feed edit forms.
 */
class FeedForm extends ContentEntityForm {

  /**
   * The form factory.
   *
   * @var \Drupal\feeds\Plugin\PluginFormFactory
   */
  protected $formFactory;

  /**
   * Constructs an FeedTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\feeds\Plugin\PluginFormFactory $factory
   *   The form factory.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityManagerInterface $entity_manager, PluginFormFactory $factory, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->formFactory = $factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Compatibility with Drupal 8.2.x.
    try {
      $datetime = $container->get('datetime.time');
    }
    catch (ServiceNotFoundException $e) {
      $datetime = NULL;
    }

    return new static(
      $container->get('entity.manager'),
      $container->get('feeds_plugin_form_factory'),
      $container->get('entity_type.bundle.info'),
      $datetime
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $feed = $this->entity;

    $feed_type = $feed->getType();

    $form['advanced'] = [
      '#type' => 'vertical_tabs',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];
    $form = parent::form($form, $form_state);

    $form['plugin']['#tree'] = TRUE;
    foreach ($feed_type->getPlugins() as $type => $plugin) {
      if ($this->pluginHasForm($plugin, 'feed')) {
        $feed_form = $this->formFactory->createInstance($plugin, 'feed');

        $plugin_state = (new FormState())->setValues($form_state->getValue(['plugin', $type], []));

        $form['plugin'][$type] = $feed_form->buildConfigurationForm([], $plugin_state, $feed);
        $form['plugin'][$type]['#tree'] = TRUE;

        $form_state->setValue(['plugin', $type], $plugin_state->getValues());
      }
    }

    $form['author'] = [
      '#type' => 'details',
      '#title' => $this->t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => ['class' => ['feeds-feed-form-author']],
      '#weight' => 90,
      '#optional' => TRUE,
    ];
    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }
    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    // Feed options for administrators.
    $form['options'] = [
      '#type' => 'details',
      '#access' => $this->currentUser()->hasPermission('administer feeds'),
      '#title' => $this->t('Import options'),
      '#collapsed' => TRUE,
      '#group' => 'advanced',
    ];

    $form['options']['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#default_value' => $feed->isActive(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    // Add an "Import" button.
    if ($this->entity->access('import')) {
      $element['submit']['#dropbutton'] = 'save';
      $element['import'] = $element['submit'];
      $element['import']['#dropbutton'] = 'save';
      $element['import']['#value'] = t('Save and import');
      $element['import']['#weight'] = 0;
      $element['import']['#submit'][] = '::import';
    }

    $element['delete']['#access'] = $this->entity->access('delete');

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Don't call buildEntity() here.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getErrors()) {
      return;
    }
    $feed = $this->buildEntity($form, $form_state);

    foreach ($feed->getType()->getPlugins() as $type => $plugin) {
      if (!$this->pluginHasForm($plugin, 'feed')) {
        continue;
      }

      $feed_form = $this->formFactory->createInstance($plugin, 'feed');

      $plugin_state = (new FormState())->setValues($form_state->getValue(['plugin', $type], []));
      $feed_form->validateConfigurationForm($form['plugin'][$type], $plugin_state, $feed);

      $form_state->setValue(['plugin', $type], $plugin_state->getValues());

      foreach ($plugin_state->getErrors() as $name => $error) {
        // Remove duplicate error messages.
        if (!empty($_SESSION['messages']['error'])) {
          foreach ($_SESSION['messages']['error'] as $delta => $message) {
            if ($message['message'] === $error) {
              unset($_SESSION['messages']['error'][$delta]);
              break;
            }
          }
        }
        $form_state->setErrorByName($name, $error);
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Build the feed object from the submitted values.
    parent::submitForm($form, $form_state);
    $feed = $this->entity;

    foreach ($feed->getType()->getPlugins() as $type => $plugin) {
      if ($this->pluginHasForm($plugin, 'feed')) {
        $feed_form = $this->formFactory->createInstance($plugin, 'feed');

        $plugin_state = (new FormState())->setValues($form_state->getValue(['plugin', $type], []));

        $feed_form->submitConfigurationForm($form['plugin'][$type], $plugin_state, $feed);

        $form_state->setValue(['plugin', $type], $plugin_state->getValues());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $feed = $this->entity;
    $insert = $feed->isNew();
    $feed->save();

    $context = ['@type' => $feed->bundle(), '%title' => $feed->label()];
    $t_args = [
      '@type' => $feed->getType()->label(),
      '%title' => $feed->label(),
    ];

    if ($insert) {
      $this->logger('feeds')->notice('@type: added %title.', $context);
      drupal_set_message($this->t('%title has been created.', $t_args));
    }
    else {
      $this->logger('feeds')->notice('@type: updated %title.', $context);
      drupal_set_message($this->t('%title has been updated.', $t_args));
    }

    if (!$feed->id()) {
      // In the unlikely case something went wrong on save, the feed will be
      // rebuilt and feed form redisplayed the same way as in preview.
      drupal_set_message($this->t('The feed could not be saved.'), 'error');
      $form_state->setRebuild();
      return;
    }

    if ($feed->access('view')) {
      $form_state->setRedirect('entity.feeds_feed.canonical', ['feeds_feed' => $feed->id()]);
    }
    else {
      $form_state->setRedirect('<front>');
    }
  }

  /**
   * Form submission handler for the 'import' action.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function import(array $form, FormStateInterface $form_state) {
    $feed = $this->entity;
    $feed->startBatchImport();
    return $feed;
  }

  protected function pluginHasForm(FeedsPluginInterface $plugin, $operation) {
    return $this->formFactory->hasForm($plugin, $operation);
  }

}
