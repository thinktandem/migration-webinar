<?php

namespace Drupal\migration_boilerplate\Plugin\migrate\source;

use Drupal\Core\Database\Query\Condition;
use Drupal\file\Plugin\migrate\source\d7\File;

/**
 * Drupal 7 file source from database that filters off of filemime.
 *
 * @MigrateSource(
 *   id = "d7_file_type",
 *   source_module = "file"
 * )
 *
 * Adds an additional file_type that can be used as an array in the source.
 *
 * As a single value:
 *
 * @code
 * source:
 *   plugin: d7_file_type
 *   scheme: public
 *   file_type: image
 * @endcode
 *
 * As an array:
 *
 * @code
 * source:
 *   plugin: d7_file_type
 *   scheme: public
 *    file_type:
 *      - image
 *      - text
 * @endcode
 */
class FileTypeDecide extends File {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    if (!isset($this->configuration['file_type'])) {
      return $query;
    }

    $config = is_array($this->configuration['file_type'])
      ? $this->configuration['file_type']
      : [$this->configuration['file_type']];
    $types = array_map([$this->getDatabase(), 'escapeLike'], $config);

    // Add conditions.
    $conditions = new Condition('OR');
    foreach ($types as $type) {
      $conditions->condition('filemime', $type . '%', 'LIKE');
    }
    $query->condition($conditions);

    return $query;
  }
}
