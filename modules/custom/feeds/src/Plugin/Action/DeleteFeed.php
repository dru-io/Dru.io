<?php

namespace Drupal\feeds\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Redirects to a feed deletion form.
 *
 * @Action(
 *   id = "feeds_feed_delete_action",
 *   label = @Translation("Delete selected feeds"),
 *   type = "feeds_feed",
 *   confirm_form_route_name = "feeds.multiple_delete_confirm"
 * )
 */
class DeleteFeed extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The tempstore object.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $tempStore;

  /**
   * Constructs a DeleteFeed object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\PrivateTempStore $temp_store
   *   The tempstore factory.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, PrivateTempStore $temp_store, AccountInterface $user) {
    $this->tempStore = $temp_store;
    $this->user = $user;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $temp_store = $container->get('user.private_tempstore')->get('feeds_feed_multiple_delete_confirm');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $temp_store,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $this->tempStore->set($this->user->id(), $entities);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return TRUE;
  }

}
