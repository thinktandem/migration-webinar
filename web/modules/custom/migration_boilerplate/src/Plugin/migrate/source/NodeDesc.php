<?php

namespace Drupal\migration_boilerplate\Plugin\migrate\source;

use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Extends the Drupal 7 node source to run the migration in descending order.
 *
 * @MigrateSource(
 *   id = "node_desc",
 *   source_module = "node"
 * )
 */
class NodeDesc extends Node {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->orderBy('nid', 'DESC');
    return $query;
  }
}
