<?php

namespace Drupal\feeds\Feeds\Target;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Exception\TargetValidationException;
use Drupal\feeds\FieldTargetDefinition;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Utility\Token;

/**
 * Defines a file field mapper.
 *
 * @FeedsTarget(
 *   id = "file",
 *   field_types = {"file"},
 *   arguments = {"@entity_type.manager", "@entity.query", "@http_client", "@token", "@entity_field.manager", "@entity.repository"}
 * )
 */
class File extends EntityReference {

  /**
   * The http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The list of allowed file extensions.
   *
   * @var string[]
   */
  protected $fileExtensions;

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a File object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \GuzzleHttp\ClientInterface $client
   *   The http client.
   * @param \Drupal\Core\Utility\Token $token
   * The tokens.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityTypeManagerInterface $entity_type_manager, QueryFactory $query_factory, ClientInterface $client, Token $token, EntityFieldManagerInterface $entity_field_manager, EntityRepositoryInterface $entity_repository) {
    $this->client = $client;
    $this->token = $token;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $query_factory, $entity_field_manager, $entity_repository);
    $this->fileExtensions = array_filter(explode(' ', $this->settings['file_extensions']));
  }

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    return FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('target_id')
      ->addProperty('description');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    foreach ($values as $column => $value) {
      switch ($column) {
        case 'description':
          $values[$column] = (string) $value;
          break;

        case 'target_id':
          $values[$column] = $this->getFile($value);
          break;
      }
    }

    $values['display'] = (int) $this->settings['display_default'];
  }

  /**
   * {@inheritdoc}
   *
   * Filesize and MIME-type aren't sensible fields to match on so these are
   * filtered out.
   */
  protected function filterFieldTypes(FieldDefinitionInterface $field) {
    $ignore_fields = [
      'filesize',
      'filemime',
    ];

    return in_array($field->getName(), $ignore_fields) ? FALSE : parent::filterFieldTypes($field);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityType() {
    return 'file';
  }

  /**
   * {@inheritdoc}
   *
   * The file entity doesn't support any bundles. Providing an empty array here
   * will prevent the bundle check from being added in the find entity query.
   */
  protected function getBundles() {
    return [];
  }

  /**
   * Returns a file id given a url.
   *
   * @param string $value
   *   A URL file object.
   *
   * @return int
   *   The file id.
   */
  protected function getFile($value) {
    // Perform a lookup agains the value using the configured reference method.
    if (FALSE !== ($fid = $this->findEntity($value, $this->configuration['reference_by']))) {
      return $fid;
    }

    // Prepare destination directory.
    $destination = $this->token->replace($this->settings['uri_scheme'] . '://' . trim($this->settings['file_directory'], '/'));
    file_prepare_directory($destination, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY);
    $filepath = $destination . '/' . $this->getFileName($value);

    switch ($this->configuration['existing']) {
      case FILE_EXISTS_ERROR:
        if (file_exists($filepath) && $fid = $this->findEntity($filepath, 'uri')) {
          return $fid;
        }
        if ($file = file_save_data($this->getContent($value), $filepath, FILE_EXISTS_REPLACE)) {
          return $file->id();
        }
        break;

      default:
        if ($file = file_save_data($this->getContent($value), $filepath, $this->configuration['existing'])) {
          return $file->id();
        }
    }

    // Something bad happened while trying to save the file to the database. We
    // need to throw an exception so that we don't save an incomplete field
    // value.
    throw new TargetValidationException('There was an error saving the file: %file', ['%file' => $filepath]);
  }

  protected function getFileName($url) {
    $filename = trim(drupal_basename($url), " \t\n\r\0\x0B.");
    $extension = substr($filename, strrpos($filename, '.') + 1);

    if (!in_array($extension, $this->fileExtensions)) {
      throw new TargetValidationException('The file, %url, failed to save because the extension, %ext, is invalid.', ['%url' => $url, '%ext' => $extension]);
    }

    return $filename;
  }

  protected function getContent($url) {
    $response = $this->client->get($url);

    if ($response->getStatusCode() >= 400) {
      $args = [
        '%url' => $url,
        '@code' => $response->getStatusCode(),
      ];
      throw new TargetValidationException('Download of %url failed with code @code.', $args);
    }

    return (string) $response->getBody();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['existing' => FILE_EXISTS_ERROR] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   *
   * @todo Inject $user.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $options = [
      FILE_EXISTS_REPLACE => $this->t('Replace'),
      FILE_EXISTS_RENAME => $this->t('Rename'),
      FILE_EXISTS_ERROR => $this->t('Ignore'),
    ];

    $form['existing'] = [
      '#type' => 'select',
      '#title' => $this->t('Handle existing files'),
      '#options' => $options,
      '#default_value' => $this->configuration['existing'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summary = parent::getSummary();

    switch ($this->configuration['existing']) {
      case FILE_EXISTS_REPLACE:
        $message = 'Replace';
        break;

      case FILE_EXISTS_RENAME:
        $message = 'Rename';
        break;

      case FILE_EXISTS_ERROR:
        $message = 'Ignore';
        break;
    }

    return $summary . '<br>' . $this->t('Exsting files: %existing', ['%existing' => $message]);
  }

  /**
   * {@inheritdoc}
   */
  protected function createEntity($value) {
    if (!strlen(trim($value))) {
      return FALSE;
    }

    $bundles = $this->getBundles();

    $entity = $this->entityTypeManager->getStorage($this->getEntityType())->create([
      $this->getLabelKey() => $value,
      $this->getBundleKey() => reset($bundles),
      'uri' => $value,
    ]);
    $entity->save();

    return $entity->id();
  }

}
