<?php

namespace Drupal\eminent_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Example on how to migrate an image from any place in Drupal.
 *
 * @MigrateProcessPlugin(
 *   id = "taxonomy_import"
 * )
 */
class TaxonomyImport extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $vocabulary = "subject";

    if (empty($value)) {
      $value = "sardar";
    }
    if ($terms = taxonomy_term_load_multiple_by_name($value, 'subject')) {
      $term = reset($terms);
    }
    else {
      $term = Term::create([
        'parent' => array(),
        'name' => $value,
        'vid' => 'subject',
      ]);
      $term->save();
    }

    return $term->id();
  }

}
