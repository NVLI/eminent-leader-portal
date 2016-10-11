<?php
/**
 * @file
 * Contains \Drupal\migrate\Event\MigrateMapDeleteEvent.
 */


namespace Drupal\eminent_migrate\Event;

use Drupal\migrate_plus\Event\MigrateEvents;
use Drupal\migrate_plus\Event\MigratePrepareRowEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MigrateEvent implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[MigrateEvents::PREPARE_ROW][] = array('onPrepareRow', 0);
    return $events;
  }

  /**
   * React to a new row.
   *
   * @param \Drupal\migrate_plus\Event\MigratePrepareRowEvent $event
   *   The prepare-row event.
   */
  public function onPrepareRow(MigratePrepareRowEvent $event) {
    $row = $event->getRow();
    $file_name = $row->getSourceProperty('file_name');
    $id = $row->getSourceProperty('identifier');
    $extension = $row->getSourceProperty('format');
    $source_path = "public://" . $file_name;
    $row->setSourceProperty('source_path', $source_path);
    if ($extension == "pdf" || $extension == "PDF" || $extension == "docx") {
      $row->setSourceProperty('media_bundle', 'document');
    }
    else if ($extension == "jpg" || $extension == "png" || $extension == "jpeg" || $extension == "gif" || $extension == "Jpg" || $extension == "JPG") {
      $row->setSourceProperty('media_bundle', 'image');
    }
    else if ($extension == "mp3" || $extension == "wav") {
      $row->setSourceProperty('media_bundle', 'audio');
    }
    else if ($extension == "mp4" || $extension == "mpeg" || $extension == "mpg") {
      $row->setSourceProperty('media_bundle', 'video');
    }
    $sha1 = $row->getSourceProperty('sha1');
    // check whether the sha1 exists. if exists skip the csv row.
    $media_id = \Drupal::entityQuery('media')
    ->condition('field_filehash', $sha1)
    ->execute();
    if (!empty($media_id)) {
      $message = "File " . $file_name . " already exists. aborting the import of csv item " . $id;
      \Drupal::logger('eminent_migration')->error($message);
      return FALSE;
    }
  }
}
