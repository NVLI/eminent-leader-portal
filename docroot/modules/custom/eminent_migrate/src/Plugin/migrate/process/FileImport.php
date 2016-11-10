<?php

namespace Drupal\eminent_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Example on how to migrate an image from any place in Drupal.
 *
 * @MigrateProcessPlugin(
 *   id = "file_import"
 * )
 */
class FileImport extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $user = \Drupal::currentUser();
    $file = \Drupal::entityTypeManager()->getStorage('file')->create(['uri' => $value]);
    $extension = $row->getSourceProperty('format');
    if ($extension == "pdf" || $extension == "PDF") {
      $file->setMimeType('application/pdf');
    }
    $file->save();

    return $file->id();
  }

}
