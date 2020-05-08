<?php

namespace Drupal\migration_boilerplate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Sets up the Super Trill Table Migration.
 *
 * @MigrateSource(
 *   id = "super_trill"
 * )
 */
class SuperTrillTable extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $fields = [
      'id',
      'tronic',
      'timestamp',
    ];
    return $this->select('super_trill_d7', 's')
      ->fields('s', $fields);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => $this->t('Primary Key'),
      'tronic' => $this->t('Words and stuff'),
      'timestamp' => $this->t('Unix timestamp of when trill occurred'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return ['id' => ['type' => 'integer']];
  }

}
