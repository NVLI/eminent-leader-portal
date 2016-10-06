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
    $extension = $row->getSourceProperty('format');
    $source_path = "public://" . $file_name;
    $row->setSourceProperty('source_path', $source_path);
    if ($extension == "pdf" || $extension == "PDF" || $extension == "docx") {
      $row->setSourceProperty('media_bundle', 'document');
    }
    else if ($extension == "jpg" || $extension == "png" || $extension == "jpeg") {
      $row->setSourceProperty('media_bundle', 'image');
    }
    else if ($extension == "mp3" || $extension == "wav") {
      $row->setSourceProperty('media_bundle', 'audio');
    }
    else if ($extension == "mp4" || $extension == "mpeg") {
      $row->setSourceProperty('media_bundle', 'video');
    }
  }
}
