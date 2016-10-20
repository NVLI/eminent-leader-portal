/**
 * @file
 * Provides Photobox integration for Image and Media fields.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.blazy = Drupal.blazy || {};

  Drupal.behaviors.blazyPhotobox = {
    attach: function (context) {
      $('div[data-blazy], .slick--photobox', context).once('blazy-photobox').each(function () {
        $(this).photobox('a[data-photobox]', {thumb: '> [data-thumb]', thumbAttr: 'data-thumb'}, Drupal.blazy.photobox);
      });
    }
  };

  /**
   * Callback for custom captions.
   */
  Drupal.blazy.photobox = function () {
    var $caption = $(this).next('.litebox-caption');

    if ($caption.length) {
      $('#pbCaption .title').html($caption.html());
    }
  };

}(jQuery, Drupal));
