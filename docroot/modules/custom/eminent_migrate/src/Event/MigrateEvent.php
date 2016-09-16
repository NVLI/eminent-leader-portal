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
    $directory = $row->getSourceProperty('directory');
    $file_name = $row->getSourceProperty('file_name');
    $extension = $row->getSourceProperty('extension');
    $source_path = "public://" . $directory . '/' . $file_name . '.' . $extension;
    $row->setSourceProperty('source_path', $source_path);
    if ($extension == "pdf") {
      $row->setSourceProperty('media_bundle', 'document');
    }
    else {
      $row->setSourceProperty('media_bundle', 'image');
    }
  }
}
