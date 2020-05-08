<?php

namespace Drupal\migration_boilerplate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateException;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Looks up multiple paragraph results.
 * The migration_lookup plugin only returns the first result it finds.
 * WE NEED MORE POWER
 *
 * @MigrateProcessPlugin(
 *   id = "multiple_migration_lookup"
 * )
 *
 * To use this do the following:
 *
 * @code
 *  paragraph_field:
 *    -
 *      plugin: multiple_migration_lookup
 *      source: nid
 *      migration:
 *        - upgrade_d7_paragraph_one
 *        - upgrade_d7_paragraph_two
 *        - upgrade_d7_paragraph_three
 * @endcode
 *
 */
class MultipleMigrationLookup extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a MultipleMigrationLookup object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!isset($this->configuration['migration'])) {
      throw new MigrateException('migration field must be set.');
    }
    if (!isset($this->configuration['source'])) {
      throw new MigrateException('source field must be set.');
    }

    // Grab all our results.
    $results = [];
    foreach ($this->configuration['migration'] as $migration_id) {
      $table = 'migrate_map_' . $migration_id;
      $results[] = $this->connection->select($table, 'm')
        ->fields('m', ['destid1', 'destid2'])
        ->condition('sourceid1', $row->getSourceProperty($this->configuration['source']))
        ->isNotNull('m.destid1')
        ->execute()
        ->fetchAll();
    }

    // Flatten the results for the nodes to ingest.
    $paragraphs = [];
    foreach ($results as $result) {
      foreach ($result as $res) {
        $paragraphs[] = [
          'target_id' => $res->destid1,
          'target_revision_id' => $res->destid2,
        ];
      }
    }

    return $paragraphs;
  }
}
