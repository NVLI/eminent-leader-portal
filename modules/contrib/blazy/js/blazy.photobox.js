/**
 * @file
 * Provides Photobox integration for Image and Media fields.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.blazyPhotobox = {
    attach: function (context) {
      $('div[data-blazy], .slick--photobox', context).once('blazy-photobox').each(function () {
        $(this).photobox('a[data-photobox]', {thumb: '> [data-thumb]', thumbAttr: 'data-thumb'});
      });
    }
  };

}(jQuery, Drupal));
