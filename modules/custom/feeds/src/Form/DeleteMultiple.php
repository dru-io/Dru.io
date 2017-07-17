<?php

namespace Drupal\feeds\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\feeds\FeedStorageInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a feed deletion confirmation form.
 */
class DeleteMultiple extends ConfirmFormBase {

  /**
   * The array of feeds to delete.
   *
   * @var array
   */
  protected $feeds = [];

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The feed storage.
   *
   * @var \Drupal\feeds\FeedStorageInterface
   */
  protected $storage;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\feeds\FeedStorageInterface $storage
   *   The feed storage.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, FeedStorageInterface $storage, AccountInterface $user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->storage = $storage;
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('entity_type.manager')->getStorage('feeds_feed'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'feeds_feed_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->feeds), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('feeds.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->feeds = $this->tempStoreFactory->get('feeds_feed_multiple_delete_confirm')->get($this->user->id());
    if (empty($this->feeds)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }

    $form['feeds'] = [
      '#theme' => 'item_list',
      '#items' => array_map(function ($feed) {
        return Html::escape($feed->label());
      }, $this->feeds),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->feeds)) {
      $this->storage->delete($this->feeds);
      $this->tempStoreFactory->get('feeds_feed_multiple_delete_confirm')->delete($this->user->id());
      $count = count($this->feeds);
      $this->logger('feeds')->notice('Deleted @count feeds.', ['@count' => $count]);
      drupal_set_message($this->formatPlural($count, 'Deleted 1 feed.', 'Deleted @count posts.'));
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
