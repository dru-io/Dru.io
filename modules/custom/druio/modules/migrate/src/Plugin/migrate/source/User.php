<?php

namespace Drupal\druio_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "druio_user"
 * )
 */
class User extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('users', 'u')
      ->fields('u', [
        'uid',
        'name',
        'pass',
        'mail',
        'created',
        'access',
        'login',
        'status',
        'timezone',
        'init',
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'uid' => $this->t('Account ID'),
      'name' => $this->t('Unique user name'),
      'pass' => $this->t('User’s password (hashed)'),
      'mail' => $this->t('User’s e-mail address'),
      'created' => $this->t('Timestamp for when user was created'),
      'access' => $this->t('Timestamp for previous time user accessed the site'),
      'login' => $this->t('Timestamp for user’s last login'),
      'status' => $this->t('Whether the user is active(1) or blocked(0)'),
      'timezone' => $this->t('User’s time zone'),
      'init' => $this->t('E-mail address used for initial account creation'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
        'alias' => 'u',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    return parent::prepareRow($row);
  }

}
