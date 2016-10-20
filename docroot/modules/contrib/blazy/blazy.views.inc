<?php

/**
 * @file
 * Provides views data for blazy.module.
 */

/**
 * Implements hook_views_data_alter().
 */
function blazy_views_data_alter(&$data) {
  $data['file_managed']['blazy_file'] = [
    'title' => t('Blazy'),
    'help' => t('Displays a preview of a File using Blazy, if applicable.'),
    'field' => [
      'id' => 'blazy_file',
      'click sortable' => FALSE,
    ],
  ];

  if (function_exists('video_embed_media_media_bundle_insert')) {
    $data['media_field_data']['blazy_media'] = [
      'title' => t('Blazy'),
      'help' => t('Displays a preview of a Media using Blazy, if applicable.'),
      'field' => [
        'id' => 'blazy_media',
        'click sortable' => FALSE,
      ],
    ];
  }
}
